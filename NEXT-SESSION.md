# Next session: after the SEO audit (October 2026)

> Rewritten 2026-09-15, after the launch; the 2026-10-08 section and the
> Open list are current. The launch-day handoff this file used to hold is in
> git history (#126, #131, #133).

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
  on these two pages only. Recorded as D44 in the decisions log (Eric, Sep 16).
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

## 2026-10-08: the SEO audit and its first phase

- **The audit** is `docs/seo-audit-2026-10.md` (#183): 17 of 41 sitemap
  pages indexed and no journal post; titles, descriptions and schema still
  reading as the cruise site; GA4 about 85% bots. Four phases; Phase 1 is
  below, Phase 2 is the next session's work.
- **Phase 1 shipped on `develop`** (theme 0.10.7 → 0.10.10, plugin 1.9.6 →
  1.9.8), one PR each:
  - #184 metadata and schema: destination and tour search titles and
    descriptions come from code while Rank Math's fields are empty
    (`inc/destination.php`, `inc/tours.php`); focus keywords, the About
    and Client stories fields and the Journal wording fixes are applied by
    `wp oomph seed pages --corrections-only`, which the production deploy
    now runs; the Organization node describes the land business and
    points at the real Facebook and Instagram; the Person node carries
    both job titles and LinkedIn.
  - #185 "From the Journal" on every destination page: the posts tagged or
    categorised with the destination's slug, newest three; #193 adds the
    fallback that matters on the live site, where posts carry topic
    categories and no place tags: the newest posts whose title names the
    place (`oomphtravel_destination_place_names()`).
  - #186 share images: `og:image` and `twitter:image` for the home page,
    the ways pages, destinations and the Journal index, with
    `assets/img/share-varenna-1200x630.jpg` as the fallback
    (`inc/share-image.php`). Resolving #187's version conflict with the
    branch's `functions.php` dropped the `require` for that file, so
    nothing in it ran on staging from 0.10.10; #192 and most of #194
    chased Rank Math before that was found. #194 restores the require and
    makes the design stand without Rank Math: its image filter records
    whether it added an image, and a late `wp_head` hook prints the
    theme's own `og:image` and `twitter:image` when none was. Lesson, in
    memory: resolve version conflicts on the version line only.
  - #187 two redirects, `/trip-quiz/` → `/start-planning/` and
    `/cruise-travel-trends/` → `/travel-trends/` (Eric amended D02 for these
    two on 2026-10-08; recorded in the PR and in `class-redirects.php`, not
    in the decisions log), and the privacy page, `patterns/privacy-policy.php`
    at `/privacy-policy/`. Off production the seed publishes WordPress's
    own privacy draft under the title "Privacy"; on production Eric does.
  - #188 breadcrumbs on `/destinations/` and `/tours/` and none on the home
    page; the Client stories reviews sit on the one Organization node.
- **Phase 2 began the same night:** #190 (theme 0.10.11; 0.10.14 after
  #192, #193 and #194) puts intros on
  the `/destinations/` hub, four questions on every operator page
  (`oomphtravel_operator_questions()` in `inc/tours.php`) and records
  Tauck's Lake Como to Rome tour in `content/tours/` for the Add a tour
  button.
- **Performance (B3), what the numbers mean:** Lighthouse's 5 to 7 s LCP is
  the simulated model counting every request made before first paint (the
  Google tag and three fonts); observed LCP on the same runs was 1.4 to
  2.3 s. The images are already WebP and sized. Worth doing, in order:
  the Site Kit tag loading later, the italic Fraunces cut off the home
  page's first paint, then critical CSS; measure after each.
- **Rank Math, done and not done.** The title separator is now "|" (set in
  wp-admin, 2026-10-08). The homepage title "Travel advisor for custom
  trips & tours | Oomph Travel", its description and the focus keyword are
  typed into the Home page's Rank Math snippet editor in Eric's Chrome and
  **not saved**: live-site writes from this machine are refused by the
  tooling, so Eric clicks Save on that tab, then Purge SG Cache. Still to
  set: author sitemap off; IndexNow for destinations, tours and operators;
  a default OpenGraph image (optional now that the theme sends one).
  SiteGround's captcha blocked the connector all night.
- **The audit's tooling notes** (connectors, the captcha, Lighthouse) are in
  the audit's Appendix C.

- **The live suite was red for two days, and it was the audit's doing.** The
  10-08 crawl put this PC's home IP on SiteGround's all-requests challenge
  (HTTP 202 on every path of prod, staging and cruiseoomph.com), and the
  runner is this PC. The suite's warm-up took the challenge page's own
  `<h1>` as proof the site had cleared, saved a cookie-less session, and
  every test started walled. Fixed in #201 (judge clearance on the
  `SG-Captcha` header and the page), #202 (browse as the test device; the
  clearance is per user agent; log a self-check) and #203 (the Home route
  no longer expects a BreadcrumbList, after #188). Staging run 37979848011:
  40 passed. The nightly run uses **main's** specs, so it stays red until
  the next release. Every curl or browser-pane request from this machine
  counts against the runner; keep live checks in Eric's Chrome.

## Open

1. **Eric, after this release:** publish the privacy page (Pages → Drafts →
   "Privacy Policy", title to "Privacy", Publish; or Add New with the slug
   `privacy-policy`); the Rank Math settings above; request indexing for
   the 24 addresses in the audit's Appendix A, ten a day; GA4's internal
   traffic filter and key events; confirm the Google Business Profile.
2. **Phase 2 (audit §plan):** performance (WebP heroes with `sizes`, card
   and ticker image sizes, font preload order, SG Optimizer minify JS,
   critical CSS); operator pages to 600–800 words; the Tauck tour page;
   an intro on `/destinations/`; the multi-generational pillar; keep Site
   Kit's tag off the Playwright runner; Kadence Blocks and the child theme
   deleted (ask first).
3. **Done 2026-09-15:** newsletter confirmation arrived from a fresh
   address; Clarity and GA4 Realtime show visits.
4. **Week after (runbook §4):** delete Fluent Forms; delete the
   `kadence-oomph-child` theme in wp-admin, then a PR removes it from the
   repo and from `deploy.yml`.
5. **ACF Pro is still the live fields plugin.** Secure Custom Fields was
   never installed (checked 2026-09-15: `advanced-custom-fields-pro/` present,
   `secure-custom-fields/` absent). Keep ACF Pro until Eric chooses the swap
   in `docs/stage-4-fields-runbook.md`, staging first; only then cancel the
   licence.
6. **Search Console, weekly for a month:** Pages → Not found (404) will list
   the old cruise addresses; that is expected (D02). Watch Core Web Vitals.
7. **Eric's decisions still open:** operator logos (add or hide the slot);
   the Hawaii Stays name. Kept by decision: the hidden cruise regions,
   trip styles and itinerary records; the Rank Math redirect duplicates.
