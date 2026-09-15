# Testing — the quality gate

Plain-language guide to the automated checks that watch over the site
(plan P10 / 8.6). Two suites and two audits; none of them ever sends a form.

## 1. The CI suite (`npm run test:ci`)

Runs against a throwaway WordPress in a container (`wp-env`), seeded from the
plugin's own seed command, so it can walk the Start planning form all the way
to the receipt and still touch nothing real. It is the "does the code work"
suite: every template, the destination fields, the tour filters, the redirects,
the 404 page, the placeholder and No List guards on seeded copy. Runs on GitHub
Actions (`ci.yml`) when dispatched at a milestone.

## 2. The live suite (`npm run test:e2e`)

Runs against a deployed site — **staging** (`staging2.oomphtravel.com`) by
default; set `OOMPH_BASE_URL` to retarget. It is the "is the site up and
right" suite, and it is meant to be green on staging before every merge to
`main`.

- **Every page type loads** (`pages.smoke.spec.ts`, routes in
  `fixtures/routes.ts`): 200, exactly one H1, a title, a self-canonical when
  the site is indexable, and the one header *Start planning* button. On a
  phone: the header text link and the menu.
- **Schema is present** (`schema.spec.ts`): TravelAgency + Person everywhere;
  Service + FAQPage on the four ways pages; TouristDestination + FAQPage on a
  destination; Review + AggregateRating on Client stories; BlogPosting on the
  newest Journal post.
- **Start planning validates but never submits** (`start-planning.spec.ts`):
  step 1 refuses to continue without a trip type and budget, the cruise choice
  hands off to CruiseOomph with the UTM tag, step 2 reaches the Send button
  and stops.
- **Tour filters work** (`tours.spec.ts`): a destination filter narrows the
  grid and the filtered view is noindex. Skips while no tour is published.
- **Published-content guard** (`content-guard.spec.ts`): walks every URL in
  the live sitemap and fails on a square-bracket placeholder, a `$X,XXX`, a
  No List word outside a client quotation, or a grey placeholder image. This
  is the readiness doc's "build a check" line.
- **Link-in-bio** (`links.spec.ts`): the `/links/` page contract.

## 3. Accessibility audit (`npm run audit:a11y`)

axe-core, WCAG 2.1 A/AA, on every route in the fixture. Results land in
`scripts/audit/out/a11y/`. The bar is zero serious or critical violations.

## 4. Lighthouse (`npm run audit:lh`)

Mobile profile, every route in the fixture plus the newest Journal post.
`LH_RUNS=3` for a median. Bars: LCP under 2.5 s, CLS under 0.1, Accessibility
100. Lab numbers are a tie-breaker; the real measure is Core Web Vitals in
Search Console for four weeks after launch.

## What they deliberately DON'T do

**No form is ever actually submitted on a live site.** The live suite stops at
the Send button. Only the CI suite, against the disposable container, walks to
the receipt.

## Known limitation: SiteGround's anti-bot protection

Both sites sit behind SiteGround's Anti-Bot system, which sometimes challenges
datacenter IPs with an HTTP 202 challenge page. The live suite warms up once
(`global-setup.ts`) and runs one worker at a time in CI; from a home
connection it runs in parallel. The e2e workflow (`e2e.yml`) therefore runs on
the self-hosted runner on Eric's PC, not on GitHub's machines.

## Running locally

```bash
npm ci
npx playwright install chromium
npm run test:e2e                     # live suite against staging
npm run audit:a11y                   # axe against staging
LH_RUNS=3 npm run audit:lh           # Lighthouse mobile against staging
OOMPH_BASE_URL=https://oomphtravel.com npm run test:e2e   # against production (read-only)
```
