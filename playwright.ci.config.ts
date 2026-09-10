import { defineConfig, devices } from '@playwright/test';

/**
 * CI config — drives the WordPress that `wp-env` runs inside the CI job.
 *
 * Deliberately separate from playwright.config.ts, which points at the live
 * or staging site and carries the anti-bot warm-up that only SiteGround's
 * edge needs. There is no edge in front of the container, so this config has
 * no global setup, no stored cookies and no retries: a failure here is a real
 * failure in our theme or plugin, not a challenge page.
 */
const BASE_URL = process.env.OOMPH_BASE_URL ?? 'http://localhost:8888';

export default defineConfig({
  testDir: './tests/ci',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: 0,
  reporter: [ [ 'list' ], [ 'html', { open: 'never', outputFolder: 'playwright-report-ci' } ] ],

  use: {
    baseURL: BASE_URL,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    {
      name: 'desktop-chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'mobile-chrome',
      use: { ...devices['Pixel 5'] },
      grep: /@mobile/,
    },
  ],
});
