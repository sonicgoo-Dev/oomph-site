# Stage 2 runbook — removing the cruise content (D02)

The Stage 2 pull request removes the *code*: templates, the Distinctive
Voyages importer and its cron, the ship library, the cabin quiz, the
enrichment payloads and their skill, and the e2e specs for all of it. The
*content* lives in the WordPress database and is removed with the steps
below — on **staging first**, then production. Staging shares SSH
credentials with production, so every `wp` call here is addressed
explicitly (`wp @stage …`, `wp @prod …`); never run one without the alias.

Nothing here is redirected (D02). The three internal redirects in step 7 are
not cruise pages.

## 0. Before anything

```bash
wp @prod db export ~/backups/oomphtravel-pre-stage2-$(date +%F).sql
wp @prod plugin list --status=active --fields=name,version
```

Expected active: `advanced-custom-fields-pro`, `fluentform`, `fluentformpro`,
`kadence-blocks`, `oomph-travel-core`, `seo-by-rank-math`,
`seo-by-rank-math-pro`, `google-site-kit`, `sg-cachepress`, `insert-headers-and-footers`
(WPCode), `siteground-ai-agent`. Anything missing from that list has been
silently deactivated — stop and ask before continuing. (Kadence Pro is *not*
on the live list as of 10 Sep; CLAUDE.md says the site runs on it. Decide
whether that is a lapse or a deliberate drop before Stage 4.)

## 1. Copy the nine cruise posts to CruiseOomph — BEFORE deleting them

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
Once the Oomph copies are deleted (step 3), clear that field on each of the
nine in CruiseOomph so the canonical points at itself.

## 2. Deploy the Stage 2 code (merge → `develop`)

Deploy runs automatically. The plugin's one-time cleanup then clears the
orphaned `oomph_retire_unbookable_sailings` cron and flushes rewrite rules on
the first request. Confirm:

```bash
wp @stage cron event list --fields=hook | grep -c retire_unbookable   # expect 0
wp @stage option get oomph_core_cruise_cleanup_done                    # expect 1
```

## 3. Delete the nine cruise Journal posts

```bash
for slug in norwegian-fjords-vs-baltic virgin-voyages-rockstar-suite-worth-it silversea-vs-regent \
  avoid-cruise-ship-crowds-in-the-mediterranean barcelona-before-your-cruise fly-the-drake-passage-or-sail \
  japan-by-sea-summer-festivals-cruise first-premium-cruise the-slow-cruise; do
  wp @stage post delete $(wp @stage post list --post_type=post --name=$slug --field=ID) --force
done
```

`10-day-united-kingdom-itinerary` **stays** (retag to UK & Ireland in Stage 4).

## 4. Delete the sailing and ship records

The post types are no longer registered, so `wp post list --post_type=` needs
`--post_type=any`-style raw queries:

```bash
wp @stage post list --post_type=oomph_cruise --post_status=any --format=ids | xargs -r wp @stage post delete --force
wp @stage post list --post_type=oomph_ship   --post_status=any --format=ids | xargs -r wp @stage post delete --force
wp @stage term list oomph_region --format=count      # regions/trip styles stay (destinations use them)
```

If `post list` refuses an unregistered type, use
`wp @stage db query "SELECT ID FROM wp_posts WHERE post_type IN ('oomph_cruise','oomph_ship')"`
and delete by ID. Media attached to sailings (Distinctive Voyages ship art)
can go too: `wp @stage post list --post_type=attachment --post_parent=<id>`.

## 5. Delete the two pages

```bash
wp @stage post delete $(wp @stage post list --post_type=page --name=trip-quiz --field=ID) --force
wp @stage post delete $(wp @stage post list --post_type=page --name=cruise-travel-trends --field=ID) --force
```

Then remove the "Group Cruises" and "Cabin Quiz" items from Appearance → Menus
(Primary Navigation). The footer and /links/ pick up the change from code.

## 6. Fluent Forms — export, then delete two forms; the plugin stays for now

```bash
# Export entries first: the Trends form holds people who downloaded the guide
# and are not in PlainSend (plan §8.3 asks whether to invite them).
```

In Fluent Forms → Entries, export **Cruise Travel Trends** and **Cabin Quiz**
to CSV, then delete those two forms. **Do not deactivate Fluent Forms**:
`/discovery-call/` still runs on it until Start planning ships (plan §6.15,
P7). The theme already dequeues its assets on every other page.

## 7. Redirects (Rank Math → Redirections)

Only the three internal ones, and only once their targets exist — a redirect to
a page that is not built yet is a 404 with extra steps:

| From | To | Create when |
|---|---|---|
| `/custom-italy-travel/` | `/destinations/italy/` | Stage 4 (destination template) |
| `/luxury-cruise-planning/` | `/cruise-planning/` | Stage 6 (Cruise planning page) |
| `/discovery-call/` | `/start-planning/` | Stage 7 (Start planning form) |

While you are in there: the existing `/contact` redirect points at
`https://oomphtravel.com/Www.oomphtravel.com` (131 hits). It should point at
`/discovery-call/` today and `/start-planning/` after Stage 7.

## 8. Search Console and sitemap

```bash
wp @prod rank-math sitemap generate   # or Rank Math → Sitemap Settings → save
```

Submit the sitemap in Search Console; keep the property and watch the 404
report for a month (plan §8.4). Expect ~20 clicks/90 days to fall away.

## 9. Repeat 3–8 on production, then verify

```bash
wp @prod oomph status
curl -sI https://oomphtravel.com/group-cruises/ | head -1        # 404
curl -sI https://oomphtravel.com/trip-quiz/ | head -1            # 404
curl -s  https://oomphtravel.com/llms.txt | grep -ci cruise       # 1 (the Cruise Planning page only)
npm ci && OOMPH_BASE_URL=https://oomphtravel.com npx playwright test
```
