import { test, expect } from '@playwright/test';
import { ROUTES } from './fixtures/routes';
import { getJsonLdTypes, expectSchemaTypes } from './helpers/schema';

/**
 * Schema presence per page type. The plugin is the sole JSON-LD source
 * (Rank Math's graph is emptied), so every page must carry TravelAgency +
 * Person, plus the per-type nodes declared in the route fixture.
 */
test.describe('JSON-LD schema', () => {
  for (const route of ROUTES) {
    test(`${route.name} carries expected schema`, async ({ page }) => {
      await page.goto(route.path, { waitUntil: 'domcontentloaded' });

      // Sitewide identity graph.
      await expectSchemaTypes(page, ['TravelAgency', 'Person']);

      // Per-type expectations from the fixture.
      if (route.types.length) {
        await expectSchemaTypes(page, route.types);
      }
    });
  }

  test('newest journal post carries BlogPosting', async ({ page }) => {
    await page.goto('/journal/', { waitUntil: 'domcontentloaded' });
    // Journal post cards are clickable media cards linking to each post.
    const firstPost = page.locator('main a.oomph-card--clickable').first();

    if ((await firstPost.count()) === 0) {
      test.skip(true, 'No journal posts published to assert against.');
    }

    await firstPost.click();
    await page.waitForLoadState('domcontentloaded');
    await expectSchemaTypes(page, ['BlogPosting', 'TravelAgency', 'Person']);
  });

});
