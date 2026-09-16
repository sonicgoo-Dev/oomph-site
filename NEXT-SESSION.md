# Next session: the week after launch (runbook §4)

> Rewritten 2026-09-15, after the launch. The launch-day handoff this file
> used to hold is in git history (#126, #131, #133).

## Where things stand

- **oomphtravel.com runs the rebuild** since 2026-09-15: the `oomphtravel`
  block theme (0.8.9) and `oomph-travel-core` (1.9.3). Release PR #134 was
  squash-merged to `main` and deployed; Eric pushed staging to live in Site
  Tools (the staging copy is labelled **Site-rebuild**); `main` was merged
  back into `develop` (#136), so the next release merges cleanly.
- #135 reconciled `main`'s workflow history first: `e2e.yml` is the `cmd`
  version the residential runner needs, and `deploy.yml` keeps the
  ssh-keyscan retry and one multiplexed SSH connection.

## What launch day found, and what was done

| Found | Done |
|---|---|
| First Full Deploy refused: WordPress 7.1 live, 7.0 on staging | Eric updated staging to 7.1, then deployed |
| Site Kit did not come across (staging never had it) | Eric reinstalled and reconnected it; the GA4 tag is back |
| Site emails went to spam: SPF allows only Google, DMARC `p=quarantine` | Eric installed FluentSMTP (Google Workspace SMTP, app password); form emails reach the inbox |
| Staging's display name (the email address) showed as the advisor's name | Eric set the display name to Eric Hempel |
| A static `/llms.txt` from May claimed a cruise certification | Removals button (apply, production) deleted it; the generated one serves |
| Fluent Forms still active | Deactivated after the Start planning send worked (D31) |

Smoke test over HTTP: seventeen pages 200 with `index, follow`, a
self-canonical, one H1 and the expected schema; the five redirects; the
theme 404 for removed cruise pages; sitemap index with posts, pages,
destinations, operators and tours. Live suite 43/43 against production.
Search Console: sitemap resubmitted; indexing requested for `/`,
`/destinations/italy/` and `/start-planning/`. Instagram points at `/links/`.

## 2026-09-16: the Journal restyle and a site-wide heading fix

- **Journal index and article are laid out like CruiseOomph's Cruise
  Journal** (Eric's request; #150, theme 0.10.0): navy hero beside the
  newest post's photo, pill topic chips, panelled cards, story hero beside
  its picture, author box, the advisor's invitation card. Rounded corners
  on these two pages only. Departs from the Figma Journal frames; a D44
  entry was proposed to Eric and not yet added.
- **Every heading had rendered at body size since the rebuild** (#151,
  0.10.1): WordPress emits `--wp--custom--type--desktop--h-2--size` (kebab
  of `h2`) and the theme asked for `--h2--`. Fixed by renaming references.
- The UK itinerary post was trashed by Eric (its five body images 404ed
  after the push); `/journal/10-day-united-kingdom-itinerary/` 301s to
  `/journal/` (#146, plugin 1.9.5). New posts start fresh.
- The four ways-page heroes had been lazy-loaded by the content-image
  filter (0.9.1); fixed in 0.9.3 (#147).
- **After every release, merge `main` back into `develop` at once** (a
  merge-commit PR, `-s ours`); every release PR this week was unmergeable
  until that was done.

## Open

1. **Done 2026-09-15:** newsletter confirmation arrived from a fresh
   address; Clarity and GA4 Realtime show visits.
2. **Week after (runbook §4):** delete Fluent Forms; delete the
   `kadence-oomph-child` theme in wp-admin, then a PR removes it from the
   repo and from `deploy.yml`.
3. **ACF Pro is still the live fields plugin.** Secure Custom Fields was
   never installed (checked 2026-09-15: `advanced-custom-fields-pro/` present,
   `secure-custom-fields/` absent). Keep ACF Pro until Eric chooses the swap
   in `docs/stage-4-fields-runbook.md`, staging first; only then cancel the
   licence.
4. **Search Console, weekly for a month:** Pages → Not found (404) will list
   the old cruise addresses; that is expected (D02). Watch Core Web Vitals.
5. **Eric's decisions still open:** operator logos (add or hide the slot);
   the Hawaii Stays name. Kept by decision: the hidden cruise regions,
   trip styles and itinerary records; the Rank Math redirect duplicates.
