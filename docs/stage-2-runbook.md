# Removing the cruise content (D02, D43) — the runbook

> **Rewritten for Stage 11 (Sep 11).** The original Stage 2 version of this
> file was a list of `wp @stage …` / `wp @prod …` commands to type by hand.
> Steps 3–5 and 7 are now done by code: a one-click GitHub Actions job for
> the deletions, and the redirects served by the plugin. What is left for a
> person is copying the nine posts to CruiseOomph first, a Fluent Forms
> export, two Rank Math clean-ups and Search Console.

The Stage 2 pull request removed the *code*: templates, the Distinctive
Voyages importer and its cron, the ship library, the cabin quiz, the
enrichment payloads and their skill, and the e2e specs for all of it. The
*content* lives in the WordPress database and is removed with the steps
below — on **staging first**, then production.

Nothing here is redirected (D02) except the courtesy line D43: any old
`/group-cruises/…` address goes to `https://cruiseoomph.com/cruises/` with
the UTM tag. Every other deleted address lands on the 404 page (plan §6.16),
which has a search field and the three doors.

## 0. Before anything

Take a backup from Site Tools (Site Tools → Security → Backups → Create) on
the environment you are about to run against. On production, also check the
active plugin list has not quietly changed:

```bash
wp @prod plugin list --status=active --fields=name,version
```

Expected active: `advanced-custom-fields-pro`, `fluentform`, `fluentformpro`,
`kadence-blocks`, `oomph-travel-core`, `seo-by-rank-math`,
`seo-by-rank-math-pro`, `google-site-kit`, `sg-cachepress`, `insert-headers-and-footers`
(WPCode), `siteground-ai-agent`. Anything missing from that list has been
silently deactivated — stop and ask before continuing.

## 1. Copy the nine cruise posts to CruiseOomph — BEFORE removing them

Run from the CruiseOomph install. Its importer reads the posts straight from
oomphtravel.com's REST API, so the posts must still be published here when it
runs. Slugs and topics come from CruiseOomph `docs/BUILD-PLAN.md` step 9.2:

```bash
wp @co-prod cruiseoomph journal list
wp @co-prod cruiseoomph journal import norwegian-fjords-vs-baltic --topic=destinations --regions="Northern Europe" --status=publish
wp @co-prod cruiseoomph journal import avoid-cruise-ship-crowds-in-the-mediterranean --topic=destinations --regions=Mediterranean --status=publish
wp @co-prod cruiseoomph journal import japan-by-sea-summer-festivals-cruise --topic=destinations --regions=Japan --status=publish
wp @co-prod cruiseoomph journal import silversea-vs-regent --topic=ships-lines --regions=Mediterranean --status=publish
wp @co-prod cruiseoomph journal import virgin-voyages-rockstar-suite-worth-it --topic=planning-guides --regions=Caribbean --status=publish
wp @co-prod cruiseoomph journal import barcelona-before-your-cruise --topic=planning-guides --regions=Mediterranean --status=publish
wp @co-prod cruiseoomph journal import fly-the-drake-passage-or-sail --topic=cruise-styles --regions=Antarctica --status=publish
wp @co-prod cruiseoomph journal import first-premium-cruise the-slow-cruise --topic=cruise-styles --status=publish
wp @co-prod cruiseoomph journal list   # all nine should now show as present
```

Each imported story carries `co_source_url` pointing back here (rel=canonical).
Once the Oomph copies are gone (step 3 with **journal** ticked), clear that
field on each of the nine in CruiseOomph so the canonical points at itself.

The removal job refuses to touch these nine unless you tick **journal**, so
forgetting this step costs nothing.

## 2. Fluent Forms — export first

In Fluent Forms → Entries, export **Cruise Travel Trends** and **Cabin Quiz**
to CSV (the Trends form holds people who downloaded the guide and are not in
PlainSend — plan §8.3 asks whether to invite them). Then delete those two
forms. Fluent Forms itself goes with D31 once Start planning has been live a
while; the removal report shows a line while it is still active.

## 3. The button — GitHub → Actions → "Remove the cruise content"

This replaces the old steps 3, 4 and 5. It runs `wp oomph remove-cruise` on
the server over the deploy key, so nobody types a `wp` command.

1. Open `github.com/sonicgoo-Dev/oomph-site` → **Actions** tab.
2. In the left list click **Remove the cruise content**.
3. Click the grey **Run workflow** button on the right. A small form drops down:
   - **Where to run** — `staging` (leave it) or `production`.
   - **mode** — `report` says what would happen and changes nothing.
     `apply` does it.
   - **journal** — tick only after step 1 is done on CruiseOomph.
   - **force** — leave unticked. Unticked, the records go to the trash and
     can be restored for thirty days from Posts → Trash. Ticked, they are
     deleted outright along with the sailings' pictures.
4. Click the green **Run workflow**. Wait for the green tick, click the run,
   and read the **Summary** — it is a table of what was found and what was
   done (or would be done).
5. Run it once in `report`, read the table, then run it again in `apply`.

What the job removes: every `oomph_cruise` sailing and `oomph_ship` record,
the `/trip-quiz/` and `/cruise-travel-trends/` pages, the retired
`oomph_retire_unbookable_sailings` cron, and (with **journal**) the nine
posts. What it only reports: the old Travel Trends PDF(s) in the media
library (delete by hand from Media once nothing links to them) and whether
Fluent Forms is still active.

On production the environment's required reviewer has to approve the run
first, and the job adds the command's own `--production` confirmation flag —
only after the same `apply` has been checked on staging.

`10-day-united-kingdom-itinerary` is not on the list and **stays**.

## 4. Menus

Remove the "Group Cruises" and "Cabin Quiz" items from Appearance → Menus
(Primary Navigation) if they are still there. The footer and /links/ come
from code and need nothing.

## 5. Redirects — in code now; delete the Rank Math duplicates

The plugin serves these itself (`class-redirects.php`), so they hold on any
environment the moment the code deploys, with nothing to configure:

| From | To |
|---|---|
| `/custom-italy-travel/` | `/destinations/italy/` |
| `/luxury-cruise-planning/` | `/cruise-planning/` |
| `/discovery-call/` | `/start-planning/` |
| `/contact/` | `/start-planning/` |
| `/group-cruises/` and anything under it | `https://cruiseoomph.com/cruises/` + UTM (D43) |

In **Rank Math → Redirections**, delete any rule for the same addresses so
the two do not fight. In particular the existing `/contact` rule points at
`https://oomphtravel.com/Www.oomphtravel.com` (131 hits) — delete it; the
code sends `/contact/` to Start planning.

Check from any terminal, or trust `tests/ci/redirects.spec.ts`:

```bash
curl -sI https://staging2.oomphtravel.com/discovery-call/ | grep -i '^location'
curl -sI https://staging2.oomphtravel.com/group-cruises/anything/ | grep -i '^location'
```

## 6. Sitemap and Search Console

Rank Math regenerates the sitemap on its own as records disappear; to force
it, open Rank Math → Sitemap Settings and click **Save Changes**. Then in
Search Console (the oomphtravel.com property) open **Sitemaps**, and submit
`sitemap_index.xml` again. Keep watching the **Pages → Not indexed → Not
found (404)** report for a month (plan §8.4). Expect ~20 clicks/90 days to
fall away.

`/category/uncategorized/` is noindex from Stage 11 and drops out on its own.

## 7. Repeat on production, then verify

Steps 0, 2, 3 (both runs), 4 and 6 again with **Where to run = production**.
Then:

```bash
curl -sI https://oomphtravel.com/group-cruises/ | grep -i '^location'    # cruiseoomph.com/cruises/
curl -sI https://oomphtravel.com/trip-quiz/ | head -1                     # 404
curl -s  https://oomphtravel.com/llms.txt | grep -ci cruise               # the Cruise Planning line and the CruiseOomph mention only
npm ci && OOMPH_BASE_URL=https://oomphtravel.com npx playwright test
```
