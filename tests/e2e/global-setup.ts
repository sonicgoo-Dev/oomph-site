import { chromium, FullConfig, Page } from '@playwright/test';
import fs from 'fs';
import path from 'path';

/**
 * Warm-up pass before the suite runs.
 *
 * SiteGround's anti-bot layer sometimes greets a client with an HTTP 202
 * "challenge" instead of the page: a one-line document whose meta refresh
 * sends the browser to /.well-known/sgcaptcha/, where a JavaScript
 * proof-of-work runs, after which the browser is sent back to the page it
 * asked for carrying a clearance cookie. From then on that browser gets
 * normal 200s. This setup visits the homepage, waits for that round trip to
 * finish, confirms with a fresh request that the site now answers 200, and
 * saves the browser storage state so every test context (and the `request`
 * fixture) starts with the already-cleared cookies — one challenge for the
 * whole suite instead of one per test.
 *
 * Both the 202 document and the challenge page carry an `SG-Captcha:
 * challenge` response header, and the challenge page serves its own
 * `<h1>oomphtravel.com</h1>` with HTTP 200. An earlier version of this file
 * took "any h1 after five seconds" as proof the site had cleared, so a
 * challenge still computing was saved as a clean session with no cookie and
 * every test then started walled (runs 37804892531 to 37953354957, 2026-10).
 * Clearance is now judged on the header and the page, never on an h1.
 */

export const STORAGE_STATE = path.join(
  __dirname,
  '..',
  '..',
  'playwright',
  '.cache',
  'storage-state.json'
);

const CHALLENGE_PATH = /\/\.well-known\/(sgcaptcha|captcha)\//;
// Total budget for the warm-up. The proof-of-work itself is a few seconds;
// the rest is slack for a slow PC and a second attempt.
const WARMUP_BUDGET_MS = 120_000;

async function onChallengePage(page: Page): Promise<boolean> {
  if (CHALLENGE_PATH.test(page.url())) return true;
  return (await page.locator('#powCaptcha').count()) > 0;
}

export default async function globalSetup(_config: FullConfig): Promise<void> {
  const baseURL = process.env.OOMPH_BASE_URL ?? 'https://staging2.oomphtravel.com';
  const browser = await chromium.launch();
  const context = await browser.newContext();
  const page = await context.newPage();

  const started = Date.now();
  let cleared = false;
  let attempts = 0;
  let last = 'no response';

  while (Date.now() - started < WARMUP_BUDGET_MS) {
    attempts++;
    try {
      const response = await page.goto(baseURL, { waitUntil: 'domcontentloaded' });
      const status = response?.status() ?? 0;
      const challenged = (response?.headers()['sg-captcha'] ?? '') === 'challenge';
      last = `HTTP ${status}${challenged ? ' (SG-Captcha: challenge)' : ''}`;

      if (status === 200 && !challenged && !(await onChallengePage(page))) {
        cleared = true;
        break;
      }

      // The 202 document's meta refresh moves the page onto the challenge
      // URL; the proof-of-work then sends it back. Wait for the departure
      // (quick) and the return (the slow part), then loop to confirm with a
      // fresh request rather than trusting whatever is on screen.
      await page.waitForURL(CHALLENGE_PATH, { timeout: 10_000 }).catch(() => {});
      await page
        .waitForURL((url) => !CHALLENGE_PATH.test(url.href), { timeout: 60_000 })
        .catch(() => {});
      await page.waitForLoadState('domcontentloaded').catch(() => {});
    } catch (error) {
      last = error instanceof Error ? error.message.split('\n')[0] : String(error);
      await page.waitForTimeout(3000);
    }
  }

  fs.mkdirSync(path.dirname(STORAGE_STATE), { recursive: true });
  await context.storageState({ path: STORAGE_STATE });
  await browser.close();

  const seconds = ((Date.now() - started) / 1000).toFixed(1);
  if (cleared) {
    console.log(`[global-setup] ${baseURL} answered a clean 200 after ${attempts} attempt(s), ${seconds}s.`);
  } else {
    // Don't abort — tests still run and report precise failures; this just
    // flags that the bot wall never cleared during warm-up.
    console.warn(
      `[global-setup] Could not get a clean 200 from ${baseURL} in ${seconds}s (${attempts} attempt(s), last: ${last}) — the suite may hit the bot challenge.`
    );
  }
}
