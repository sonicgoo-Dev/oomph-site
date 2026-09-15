import { test, expect } from '@playwright/test';
import { ROUTES } from './fixtures/routes';

/**
 * Page-level smoke (plan 8.6): every page type loads (200), renders exactly
 * one visible H1, has a title and a self-canonical, and shows the one header
 * "Start planning" button pointing at /start-planning/.
 *
 * The canonical check is skipped where the environment is set to discourage
 * search engines (staging): Rank Math prints no canonical on a noindex page.
 */
test.describe('page smoke', () => {
  for (const route of ROUTES) {
    test(`${route.name} (${route.path}) loads and renders`, async ({ page }) => {
      const response = await page.goto(route.path, { waitUntil: 'domcontentloaded' });
      expect(response, `no response for ${route.path}`).toBeTruthy();
      expect(response!.status(), `HTTP status for ${route.path}`).toBe(200);

      // Exactly one, visible, non-empty H1.
      await expect(page.locator('h1')).toHaveCount(1);
      const h1 = page.locator('h1').first();
      await expect(h1).toBeVisible();
      await expect(h1).not.toBeEmpty();
      if (route.h1) {
        await expect(h1).toContainText(route.h1);
      }

      // A title, and a canonical that points at the page itself when the
      // site is indexable.
      await expect(page).toHaveTitle(/\S/);
      const robots = (await page.locator('meta[name="robots"]').first().getAttribute('content')) ?? '';
      if (!/noindex/i.test(robots)) {
        const canonical = await page.locator('link[rel="canonical"]').getAttribute('href');
        expect(canonical, 'self-canonical').toBeTruthy();
        expect(new URL(canonical!).pathname).toBe(route.path);
      }

      // The one header button (plan §4.1). Desktop project only — the
      // phone header carries a text link instead (see "mobile chrome").
      const cta = page.locator('.ot-header__cta').first();
      await expect(cta).toBeVisible();
      await expect(cta).toHaveAttribute('href', /\/start-planning\/?$/);
    });
  }
});

test.describe('mobile chrome', () => {
  test('@mobile home shows the Start planning link on a phone viewport', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    // Under 1024px the header carries a plain text link (header.php).
    const link = page.locator('.ot-header__text-link').first();
    await expect(link).toBeVisible();
    await expect(link).toHaveAttribute('href', /\/start-planning\/?$/);
  });

  test('@mobile the menu opens and lists the four sections', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('[data-ot-menu-open]').first().click();
    const menu = page.locator('[data-ot-menu]');
    await expect(menu).toBeVisible();
    for (const label of ['Destinations', 'Ways to travel', 'Journal', 'About']) {
      await expect(menu.getByText(label, { exact: true }).first()).toBeVisible();
    }
  });
});
