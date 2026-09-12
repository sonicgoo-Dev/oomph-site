import { test, expect } from '@playwright/test';

/**
 * Link-in-bio page — /links/ (oomphtravel theme, patterns/links.php).
 *
 * The single destination the Instagram profile link points at. This spec
 * pins the page-specific contract against a live environment:
 *
 *   - the template actually bound (rendered .ot-links, not the blank
 *     default page template)
 *   - noindex, follow on every robots tag. Two robots tags is the measured
 *     reality on both environments — WP core's wp_robots output plus Rank
 *     Math's own tag. The directives agree, so the invariant asserted here
 *     is "no tag permits indexing" rather than an exact tag count.
 *   - the featured Journal card (auto-follows the newest post)
 *   - the one primary button, pointing at /start-planning/ (plan §6.15)
 *   - every internal row and text link resolves — this page exists for one
 *     referrer and a dead row is a dead end for exactly the audience Eric
 *     sent here
 *
 * Production keeps the retiring kadence-oomph-child markup until the theme
 * switch, so against production this spec is expected to fail until then.
 */
test.describe('/links/ link-in-bio', () => {
  test('template binds and renders the link stack', async ({ page }) => {
    const response = await page.goto('/links/', { waitUntil: 'domcontentloaded' });
    expect(response!.status()).toBe(200);

    await expect(page.locator('.ot-links')).toBeVisible();
    await expect(page.locator('a.ot-links__row')).toHaveCount(4);
    await expect(page.locator('.ot-links__more a')).toHaveCount(5);
    await expect(page.locator('.ot-links a[href]')).toHaveCount(11);
  });

  test('is noindex, follow on every robots tag', async ({ page }) => {
    await page.goto('/links/', { waitUntil: 'domcontentloaded' });

    const robots = page.locator('meta[name="robots"]');
    const count = await robots.count();
    expect(count, 'at least one robots meta').toBeGreaterThan(0);

    for (let i = 0; i < count; i++) {
      const content = (await robots.nth(i).getAttribute('content')) ?? '';
      expect(content, `robots tag ${i + 1}/${count}: "${content}"`).toMatch(/\bnoindex\b/);
      expect(content, `robots tag ${i + 1}/${count}: "${content}"`).toMatch(/\bfollow\b/);
    }
  });

  test('features the newest Journal post as a card', async ({ page }) => {
    await page.goto('/links/', { waitUntil: 'domcontentloaded' });

    const feature = page.locator('.ot-links__feature .ot-card-journal');
    if ((await feature.count()) === 0) {
      test.skip(true, 'No published Journal post to feature.');
    }

    await expect(feature).toBeVisible();
    const link = feature.locator('.ot-card-journal__link');
    await expect(link).toHaveAttribute('href', /.+/);
  });

  test('carries the one primary button to /start-planning/', async ({ page }) => {
    await page.goto('/links/', { waitUntil: 'domcontentloaded' });

    const cta = page.locator('.ot-links__cta .ot-btn--primary');
    await expect(cta).toBeVisible();
    await expect(cta).toHaveAttribute('href', /\/start-planning\/?$/);
    await expect(cta).toContainText(/start planning/i);
    await expect(page.locator('main .ot-btn--primary')).toHaveCount(1);
  });

  test('two links leave for CruiseOomph, tagged', async ({ page }) => {
    await page.goto('/links/', { waitUntil: 'domcontentloaded' });
    const out = page.locator('.ot-links a[href*="cruiseoomph.com"]');
    await expect(out).toHaveCount(2);
    for (const href of await out.evaluateAll((els) => els.map((el) => (el as HTMLAnchorElement).href))) {
      expect(href).toContain('utm_source=oomphtravel');
      expect(href).toContain('utm_campaign=links');
    }
  });

  test('every internal link resolves', async ({ page }) => {
    await page.goto('/links/', { waitUntil: 'domcontentloaded' });

    const hrefs = await page
      .locator('.ot-links a[href]:not([href*="cruiseoomph.com"])')
      .evaluateAll((els) => els.map((el) => (el as HTMLAnchorElement).href));
    expect(hrefs.length).toBe(9);

    // Serially, and through the page's request context (shares the anti-bot
    // clearance cookies) — see the SiteGround note in playwright.config.ts.
    for (const href of hrefs) {
      const res = await page.request.get(href);
      expect(res.status(), `${href} resolves`).toBeLessThan(400);
    }
  });
});
