import { test, expect, type APIRequestContext } from '@playwright/test';

/**
 * Published-content guard (design-handoff/docs/03-rules-and-readiness.md,
 * "Build a check that fails if a square bracket, an $X,XXX, or a known
 * placeholder image appears in published content"; plan 8.6, the No List).
 *
 * Walks every URL in the live sitemap — pages, destinations, posts, whatever
 * is published — and reads the <main> of each. Three checks per page:
 *
 *   1. no placeholder marker: `[Client name]`-style brackets or `$X,XXX`
 *   2. no No List word (docs/voice-guide.md) outside a client quotation —
 *      the four verified reviews are verbatim and exempt (D37, D38)
 *   3. no grey placeholder media (.ot-media--empty) — the rectangle that
 *      stands in for a missing hero photo or vendor logo
 *
 * Requests run one at a time with a short pause so SiteGround's anti-bot
 * layer sees a reader, not a crawler.
 */

const NO_LIST = [
  'bespoke', 'wanderlust', 'magical', 'breathtaking', 'curated', 'jaw-dropping',
  'paradise', 'bucket list', 'hidden gem', 'iconic', 'stunning', 'ultimate',
  'epic', 'unforgettable', 'escape', 'getaway', 'luxe', '5-star', 'world-class',
  'indulge', 'pampered', 'transformative', 'white-glove', 'foodie', 'vibes',
  'once in a lifetime', 'dream destination', 'bestie', 'slay', 'obsessed',
  'hubby', 'adventure of a lifetime', 'pop of color', 'main character energy',
  'the best',
];
const NO_LIST_RE = new RegExp(`\\b(${NO_LIST.map((w) => w.replace(/[-\s]/g, '[\\s-]')).join('|')})\\b`, 'gi');
const PLACEHOLDER_RE = /\[[A-Z][^\]]{2,}\]|\$X,XXX/g;

async function sitemapUrls(request: APIRequestContext, baseURL: string): Promise<string[]> {
  const index = await request.get('/sitemap_index.xml');
  expect(index.status(), 'sitemap index').toBe(200);
  const subs = [...(await index.text()).matchAll(/<loc>([^<]+)<\/loc>/g)].map((m) => m[1]);
  const urls: string[] = [];
  for (const sub of subs) {
    const res = await request.get(sub);
    expect(res.status(), sub).toBe(200);
    urls.push(...[...(await res.text()).matchAll(/<loc>([^<]+)<\/loc>/g)].map((m) => m[1]));
  }
  // Same-origin only, as paths.
  return [...new Set(urls)]
    .filter((u) => u.startsWith(baseURL.replace(/\/$/, '')))
    .map((u) => new URL(u).pathname);
}

function mainText(html: string, keepQuotes: boolean): string {
  const main = html.match(/<main\b[\s\S]*?<\/main>/i)?.[0] ?? html;
  let body = main.replace(/<script[\s\S]*?<\/script>|<style[\s\S]*?<\/style>/gi, ' ');
  if (!keepQuotes) {
    body = body.replace(/<figure class="ot-quote"[\s\S]*?<\/figure>|<blockquote[\s\S]*?<\/blockquote>/gi, ' ');
  }
  return body
    .replace(/<[^>]+>/g, ' ')
    .replace(/&amp;/g, '&').replace(/&#8217;|&rsquo;/g, '’').replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ');
}

test.describe('published content guard', () => {
  test.describe.configure({ mode: 'serial' });
  test.setTimeout(10 * 60 * 1000);

  test('no placeholder, No List word or grey placeholder image on any published page', async ({ request, baseURL }) => {
    const paths = await sitemapUrls(request, baseURL!);
    expect(paths.length, 'sitemap has URLs').toBeGreaterThan(0);

    const problems: string[] = [];
    for (const path of paths) {
      const res = await request.get(path);
      if (res.status() !== 200) {
        problems.push(`${path}: HTTP ${res.status()}`);
        continue;
      }
      const html = await res.text();
      const text = mainText(html, true);
      const prose = mainText(html, false);

      const placeholders = text.match(PLACEHOLDER_RE) ?? [];
      if (placeholders.length) problems.push(`${path}: placeholder ${[...new Set(placeholders)].join(' ')}`);

      const words = [...new Set((prose.match(NO_LIST_RE) ?? []).map((w) => w.toLowerCase()))];
      if (words.length) problems.push(`${path}: No List ${words.join(', ')}`);

      const empties = (html.match(/<main\b[\s\S]*?<\/main>/i)?.[0] ?? '').match(/ot-media--empty/g)?.length ?? 0;
      if (empties) problems.push(`${path}: ${empties} grey placeholder image(s)`);

      await new Promise((r) => setTimeout(r, 400));
    }

    expect(problems, `${paths.length} pages checked\n` + problems.join('\n')).toEqual([]);
  });
});
