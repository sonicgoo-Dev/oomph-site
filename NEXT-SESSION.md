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
