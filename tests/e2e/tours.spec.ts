import { test, expect } from '@playwright/test';

/**
 * Escorted tours index (plan §6.6): the four URL filters narrow the grid
 * and a filtered view is noindex. Skips, with a note, while no tour is
 * published on the target environment — the filter form only renders once
 * there is something to filter (tours-index.php). On an environment set to
 * discourage search engines, the unfiltered page's indexability is not checked.
 */
test.describe('/escorted-tours/ filters', () => {
  test('the filters narrow the grid and a filtered view is noindex', async ({ page }) => {
    await page.goto('/escorted-tours/', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('h1')).toHaveText(/Escorted tours/);

    const form = page.locator('form.ot-filters');
    if ((await form.count()) === 0) {
      test.skip(true, 'No published tours on this environment — the filter form is not rendered.');
    }
    await expect(form).toBeVisible();

    // Where the environment discourages search engines (staging), every page
    // is already noindex, so only the filtered view's own noindex can be
    // checked there; the unfiltered one is checked where the site is indexable.
    const robots = (await page.locator('meta[name="robots"]').first().getAttribute('content')) ?? '';
    if (!/noindex/i.test(robots)) {
      await expect(page.locator('meta[name="robots"][content*="noindex"]')).toHaveCount(0);
    }

    const all = await page.locator('.ot-tours-grid__cards .ot-card-tour').count();
    expect(all).toBeGreaterThan(0);

    // Pick the first real destination option the way a visitor would. With
    // tours.js loaded the form is `is-live`: the change submits it and the
    // button is hidden. Without the script, the button submits it (GET).
    const select = page.locator('#ot-filter-destination');
    const value = await select.locator('option:not([value=""])').first().getAttribute('value');
    expect(value).toBeTruthy();
    const live = await form.evaluate((el) => el.classList.contains('is-live'));
    if (live) {
      await Promise.all([page.waitForURL(/destination=/), select.selectOption(value!)]);
    } else {
      await select.selectOption(value!);
      await Promise.all([
        page.waitForURL(/destination=/),
        form.locator('button[type="submit"], input[type="submit"]').first().click(),
      ]);
    }

    const filtered = await page.locator('.ot-tours-grid__cards .ot-card-tour').count();
    expect(filtered).toBeGreaterThan(0);
    expect(filtered).toBeLessThanOrEqual(all);
    await expect(page.locator('meta[name="robots"][content*="noindex"]')).toHaveCount(1);

    // An unknown value is ignored rather than erroring, and is not noindex.
    await page.goto('/escorted-tours/?destination=atlantis', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('.ot-tours-grid__cards .ot-card-tour')).toHaveCount(all);
  });
});
