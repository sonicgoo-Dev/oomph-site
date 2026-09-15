import { test, expect } from '@playwright/test';

/**
 * Start planning (plan §6.15, D31) — the money path, on a live environment.
 *
 * Render and validation only. The form is never sent: these tests stop at
 * the step-2 Send button, so no inquiry record is made and no email leaves
 * (the full walk to the receipt lives in tests/ci/start-planning.spec.ts,
 * against the throwaway wp-env site).
 */

// The header shrinks as the page scrolls, so targets below it shift while
// Playwright waits for stillness. Forced clicks skip that wait.
const FORCE = { force: true };

test.describe('/start-planning/', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/start-planning/', { waitUntil: 'domcontentloaded' });
  });

  test('renders step 1 with one visible primary button', async ({ page }) => {
    await expect(page.locator('h1')).toHaveText('What are you imagining?');
    await expect(page.locator('form[data-ot-form]')).toBeVisible();
    await expect(page.locator('[data-ot-panel="1"]')).toBeVisible();
    await expect(page.locator('[data-ot-panel="2"]')).toBeHidden();
    await expect(page.locator('.ot-plan .ot-btn--primary:visible')).toHaveCount(1);
    // Never indexed as a filtered/receipt page; the form page itself is fine.
    await expect(page.locator('form[data-ot-form]')).not.toHaveAttribute('action', /plainsend/i);
  });

  test('step 1 validates: refuses to continue until trip type and budget are chosen', async ({ page }) => {
    await page.locator('[data-ot-continue]').click(FORCE);
    await expect(page.locator('[data-ot-error="trip_type"]')).toBeVisible();
    await expect(page.locator('[data-ot-error="budget"]')).toBeVisible();
    await expect(page.locator('[data-ot-panel="1"]')).toBeVisible();
    await expect(page.locator('[data-ot-panel="2"]')).toBeHidden();
  });

  test('the cruise choice hands off to CruiseOomph with the UTM tag', async ({ page }) => {
    await page.locator('label[for="ot-plan-trip_type-cruise"]').click(FORCE);
    const note = page.locator('[data-ot-cruise]');
    await expect(note).toBeVisible();
    await expect(page.locator('[data-ot-continue]')).toBeDisabled();
    const href = (await note.locator('a').getAttribute('href')) ?? '';
    expect(href).toContain('cruiseoomph.com/plan/');
    expect(href).toContain('utm_source=oomphtravel');
    expect(href).toContain('utm_medium=site');
    expect(href).toContain('utm_campaign=start-planning');
  });

  test('step 2 reaches the Send button and stops there (never submits)', async ({ page }) => {
    await page.locator('label[for="ot-plan-trip_type-custom"]').click(FORCE);
    await page.locator('label[for="ot-plan-destinations-italy"]').click(FORCE);
    await page.locator('label[for="ot-plan-budget-20k-40k"]').click(FORCE);
    await page.locator('[data-ot-continue]').click(FORCE);

    await expect(page.locator('h1')).toHaveText('How should I reach you?');
    await expect(page.locator('[data-ot-panel="2"]')).toBeVisible();
    await expect(page.locator('[data-ot-recap-list] li').first()).toContainText(/custom/i);
    await expect(page.locator('[data-ot-send]')).toBeVisible();
    await expect(page.locator('.ot-plan .ot-btn--primary:visible')).toHaveCount(1);
    // Deliberately no click on [data-ot-send].
    expect(page.url()).not.toMatch(/\/received\//);
  });
});
