# Next session: launch (plan P11)

> Rewritten 2026-09-15. The earlier version of this file (the `/links/` LCP
> notes from August) is obsolete: that site and its footer form are gone in
> the rebuild. The paste-in prompt for the launch session is at the bottom.

## Where things stand

- `develop` holds the whole rebuild: the `oomphtravel` block theme (0.8.8)
  and `oomph-travel-core` (1.9.2). Staging (`staging2.oomphtravel.com`) runs
  it. `main` is 239 commits behind, and production still runs
  `kadence-oomph-child` with plugin 1.0.0.
- **The plan is `docs/launch-runbook.md`.** Code goes live by merging
  `develop` into `main`, which activates nothing. Content goes live only when
  Eric clicks Site Tools → WordPress → Staging → Deploy (Full). That click is
  the launch.
- Last PRs merged: #122 made the whole tour card open the tour page; #124 and #125 fixed the sitemap and the homepage tours row; #126 this handoff.
- Opened by the day-before checks on 2026-09-15, waiting for Eric's merge:
  #127 (live tours spec), #128 (a filtered tours view printed robots
  `content="1, 1"` instead of noindex), #129 (Journal card contrast on
  `/links/`, theme 0.8.9), #130 (homepage "How to travel" photos no longer
  compete with the hero).

## Staging, checked over HTTP on 2026-09-15 (re-checked in the launch session)

| Item | State |
|---|---|
| Destinations | All eleven published |
| Tours published | Tauck Italy: Rome to the Lakes, Globus Italian Treasures, Insight Best of Italy |
| Operator pages | 200 at `/escorted-tours/globus/`, `/escorted-tours/tauck/` and `/escorted-tours/insight-vacations/` |
| UK itinerary post | Restored and retitled "Ten Days in the United Kingdom, the Way I'd Plan It", with a featured image. Its body is still the old copy: ten No List words (content guard fails) |
| Homepage "How to travel" photos | Done |
| Nine cruise posts | Already on cruiseoomph.com; never list as open |
| `/services/` | Still published and in the page sitemap; Eric's decision |
| Sitemap | Posts (1), pages (11), destinations (12, `/destinations/` first), operators (3), tours (4, `/escorted-tours/` first); no `oomph_region`, no `/links/` |
| Homepage featured tours | Tauck, Globus and Insight |
| Production | Unchanged since 29 Aug by its sitemap dates: kadence child, 10 posts, GA4 and Clarity present, indexable. Its index still lists six stale `oomph_cruise` sitemaps (five 404); the push replaces them |

## Open before launch day

1. **Done 2026-09-15: sitemap.** PRs #124 and #125 (plugin 1.9.2). The
   index now lists posts, pages, destinations, operators and tours. The tour
   sitemap opens with `/escorted-tours/`, and `/links/` is out.
2. **Done 2026-09-15: homepage featured tours.** Globus Italian Treasures
   and Insight Best of Italy are flagged featured, so the row shows all
   three published tours.
3. **Eric's items:** merge #127, #128, #129 and #130; reword the ten No List
   words in the UK itinerary post on staging (the exact swaps are in the
   launch session's go/no-go list); export Fluent Forms entries from
   production, take the "pre-launch" backup, stop editing production
   wp-admin, pick the launch date.
4. **Eric's decisions, easiest before the push:** `/services/`; the legacy
   cruise region, trip-style and itinerary records; the Rank Math redirect
   duplicates; the operator logo slot; the Hawaii Stays name.
5. **Claude, the day before — run 2026-09-15 against staging:**
   `npx playwright test` 41 passed, 2 failed (the tours spec, fixed by
   #127 + #128; the content guard, the UK post wording); `npm run audit:a11y`
   28/28 pages, two serious contrast finds on `/links/` (fixed by #129);
   `npm run audit:lh` (mobile, median of 5): Perf 94–100 and Best Practices
   100 on all fourteen; A11y 100 except `/links/` 96 (#129); SEO 63–66
   everywhere only because staging is noindex with no canonical; CLS ≤ 0.007;
   LCP 1.5–2.4 s on thirteen pages, **3.1 s on the homepage** (#130: an eager
   card photo downloaded alongside the hero). Re-run the suite and Lighthouse
   on `/` once the four PRs are on staging and the UK post is reworded.

## After launch

Runbook §2 C–G and §4. Then merge `main` back into `develop`, because
releases are squash-merged.

## Prompt for the launch session

Paste everything inside the block into a new Claude Code session opened in `C:\Projects\oomph-site`.

```text
This session is the OomphTravel launch (plan P11). Read CLAUDE.md, NEXT-SESSION.md and docs/launch-runbook.md first, then your memory files, before doing anything.

Work in this order:
1. Pull develop and confirm the state in NEXT-SESSION.md over HTTP against staging2.oomphtravel.com and oomphtravel.com. Anything that no longer matches, correct in the file. Do not list as open anything the memory says I already did.
2. The sitemap and the homepage featured tours row were fixed on 2026-09-15. Confirm they still hold on staging. Confirm #127–#130 are merged and on staging, and the UK post no longer trips the content guard.
3. Run the day-before checks against staging: the live Playwright suite, npm run audit:a11y and npm run audit:lh. Report results in a short table.
4. Give me a one-page go/no-go list of what is left for me, in plain language, with the clicks.
5. Stop there. Do not open the develop to main release PR, and do not touch production, until I say "launch". On that word, follow runbook §2 A to G with me step by step, including the smoke test in D, and merge main back into develop afterwards.

Rules that stay in force: branch off develop, one PR per change, squash-merge. Ask before deleting anything, changing a decision in the decisions log, adding a plugin or dependency, or anything touching the live site. Staging shares SSH credentials with production, so treat it with production caution. Never ask me to paste a credential into chat. Do not use the Rank Math MCP; it points at the live site. I am not technical: when I must act, give me clicks, not terminal commands.
```
