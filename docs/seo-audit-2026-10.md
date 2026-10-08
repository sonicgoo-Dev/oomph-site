# SEO audit and plan — oomphtravel.com, 8 October 2026

Three weeks after the rebuild went live (2026-09-15). Sources: Google Search
Console (90 days), GA4 (90 days, bot traffic separated), Rank Math PRO
through the WordPress connector (read-only), a crawl of all 41 sitemap pages,
and Lighthouse mobile runs on three pages. Nothing on the live site was
changed.

## The headline

The site is technically sound: every page has one H1, a self-canonical, a
unique title and description, the right robots tags, JSON-LD that validates,
and a clean sitemap with zero errors. Rank Math scores the site 80/100.

Three things are holding it back, in order:

1. **Google has indexed only 17 of the 41 pages.** All 12 journal posts, six
   of the eleven destination pages, three tour pages, two operator pages and
   Custom journeys are either "Discovered, currently not indexed" or "URL is
   unknown to Google". The content that should carry the new positioning is
   invisible.
2. **The pages Google does rank still describe the cruise business.** The
   homepage, About and Client Stories titles, descriptions and schema all say
   "luxury cruise" and "discovery call". Ten of eleven destination pages
   carry a bare default title ("France - Oomph Travel") and an auto-cut
   description.
3. **The measurement is not trustworthy yet.** 85% of GA4 sessions are
   automated (including Port Angeles, which is Eric's own machine and the
   Playwright runner). Only one of the inquiry events is marked as a key
   event, so GA4 reports almost no conversions.

Fix those three and the rest of this plan has something to build on.

## What the numbers say

### Search Console, last 90 days

| Metric | Value |
|---|---|
| Clicks | 28 |
| Impressions | 2,790 |
| Average position | 11.0 |
| Clicks since launch (22 days) | 13 |

- Almost all impressions came from the old cruise pages (group cruise
  sailings and the Virgin Voyages, Fjords, Japan and Slow Cruise posts).
  Those now 301 to cruiseoomph.com, which is correct (D01/D02), so
  impressions are falling as Google recrawls them. Expect the graph to keep
  dropping before it rises.
- The only non-cruise queries with any volume: "custom travel italy" (56
  impressions, position 48, now pointed at the Italy page), "multi
  generational travel planner" (13, position 55), "eric hempel" (25
  impressions, position 3 to 6, zero clicks).
- Devices: mobile 841 impressions at 1.9% CTR; desktop 1,912 at 0.6%.
- Countries: USA 58% of impressions, then UK, Canada, Australia.
- Sitemap: 41 URLs, 0 errors, last read by Google 2 October.

### GA4, last 90 days, real visitors only

| Metric | Value |
|---|---|
| Visitors | 156 |
| Sessions | 170 |
| Organic search sessions | 20 (Google), 2 (Bing), 1 (Yahoo) |
| AI assistants (ChatGPT, Perplexity) | 2 sessions |
| Form submits | 4 |
| `generate_lead` events | 1 |

- Top landing pages: home (41 sessions), /links/ (22, the Instagram page,
  7 seconds average), the old /discovery-call/ (17), /group-cruises/ (13),
  the deleted /trip-quiz/ (8 sessions, zero engagement: something still
  links to it).
- Reported sessions were 1,111; 941 of those came from data-centre
  locations (Port Angeles 461, Des Moines 148, San Jose 115, Phoenix 88).

### Rank Math site audit

Score 80 ("good"), 26 passes, 6 fails. The fails: images without alt (the
decorative ticker stamps), no OpenGraph image on the homepage, three theme
scripts not minified, the mobile speed test could not run, 26 records with
no focus keyword, and two titles missing their keyword. Response time 1.09 s
on Rank Math's test; my own measurements were 0.23 to 0.57 s to first byte.

## Findings

Severity: **A** blocks results, **B** costs results, **C** tidy-up.

### A1. Indexing: 24 of 41 pages are not in Google

| Page group | In sitemap | Indexed | Not indexed |
|---|---|---|---|
| Journal posts | 12 | 0 | 12 |
| Destination pages | 11 | 5 (Italy, France, Greece, Portugal, UK & Ireland) | 6 (Africa, Caribbean, Croatia, Hawaii, Mexico, Spain) |
| Ways to travel | 4 | 3 | 1 (Custom journeys) |
| Operators | 3 | 1 (Tauck) | 2 (Globus, Insight) |
| Tours | 3 | 0 | 3 |
| Other pages | 8 | 8 | 0 |

Nine of the 24 are "URL is unknown to Google", the rest "Discovered,
currently not indexed". Meanwhile Google still holds the dead /trip-quiz/
and the redirected cruise posts in its index. This is normal for a
three-week-old site with no external links, but it will not clear on its
own quickly. Two levers: ask Google directly (Search Console's Request
indexing, ten URLs a day), and give the pages more internal links from the
pages that are indexed (see B4).

### A2. The indexed pages still sell cruises

| Page | Live title | Live description (opening) |
|---|---|---|
| Home | Luxury Cruise & Italy Travel Advisor - Oomph Travel | "Premium and luxury cruises and custom Italy journeys… Book a free discovery call." |
| About | Eric Hempel, Cruise & Italy Advisor - Oomph Travel | "travel advisor and Silversea Ultra-Luxury Specialist… I plan premium cruises" |
| Client Stories | Client Reviews – Cruise & Custom Travel - Oomph Travel | "Silversea, Oceania, NCL… Book a free 30-min call." |

The same wording sits in Rank Math's homepage settings and in the plugin's
Organization and Person schema: the Organization description is "Premium
and luxury cruises, and custom European journeys", `knowsAbout` opens with
"Luxury cruises", the Person node says "Luxury Travel Advisor" and
"Expedition cruising", and neither node has a `sameAs` link to LinkedIn or
Instagram. The About description also claims the Silversea specialist
credential, which the brand rule keeps off this site.

The "eric hempel" query gets 25 impressions at position 3 to 6 and no
clicks: the About title is what searchers see.

### A3. Conversion measurement

- Only `form_submit` is a GA4 key event. The Start planning form fires
  `inquiry_started`, `inquiry_step2`, `inquiry_sent` and `generate_lead`
  (the theme scripts do this correctly; rule R11 holds); none of those is
  marked as a key event, and `generate_lead` fired once in 90 days because
  there was one real inquiry, not because the event is missing.
- Eric's own visits and the Playwright runner in Port Angeles count as
  sessions. GA4 has no internal-traffic filter defined.
- Cloud crawlers that run JavaScript (Des Moines, San Jose, Phoenix, Flint
  Hill) add ~440 sessions. GA4's built-in bot filter does not catch them.

### B1. Destination, tour and operator titles are defaults

| Page | Title (chars) | Description |
|---|---|---|
| France | France - Oomph Travel (21) | 57 chars |
| Spain | Spain - Oomph Travel (20) | 103 chars |
| Greece | Greece - Oomph Travel (21) | 91 chars |
| Caribbean | Caribbean - Oomph Travel (24) | 72 chars |
| Croatia | Croatia & the Adriatic - Oomph Travel (37) | 118 chars |
| Africa, Hawaii, Mexico, Portugal | bare name | auto-cut mid-sentence with "…" |
| UK & Ireland | UK & Ireland - Oomph Travel (27) | 152 chars, no keyword |
| Italy | Custom Italy Trips, Planned Region by Region – Oomph Travel (59) | written, 162 chars |

Italy is the only destination with a written title, description and focus
keyword, and it is the only one with any impressions. The three tour pages
omit the operator from the title ("Best of Italy - Oomph Travel"). Rank
Math shows 26 records with no focus keyword; its scores of 4 to 12 on these
pages are a symptom of that, not a ranking signal, but setting keywords
makes its on-page checks useful.

Two house-style inconsistencies: the separator is "-" on pages, "–" on
Italy, and absent on journal posts (which carry no "| Oomph Travel" suffix
at all); rule R5 asks for " | Oomph Travel".

### B2. No social sharing image on 15 pages

Home, About, Client Stories, the four Ways pages, the Journal index, the
three operator pages, Start planning and Travel trends emit no `og:image`.
Rank Math has no default share image set. Destination pages, posts and
tours are fine. Anything shared to Facebook, LinkedIn or iMessage from those
15 pages shows as a bare link.

### B3. Performance: lab LCP is 2 to 3 times the target

Lighthouse, mobile, simulated slow 4G:

| Page | Performance | LCP | CLS | TBT |
|---|---|---|---|---|
| Home | 73 | 7.2 s | 0 | 120 ms |
| Journal post (Rome) | 75 | 5.7 s | 0 | 100 ms |
| Italy | 74 | 5.6 s | 0 | 120 ms |

Rule R9 asks for LCP under 2.5 s. No field data exists yet (the site is too
small for Chrome UX Report), so these lab figures are what we have.
Contributors, largest first:

- The Google tag container from Site Kit is the heaviest file on every page
  (177 KB, 65 to 69 KB of it unused).
- The combined stylesheet is render-blocking; there is no critical CSS.
- Destination heroes are the original 2560-pixel JPEG (`-scaled.jpg`) with
  `sizes="100vw"` and JPEG fallbacks in the srcset. Lighthouse did not flag
  the format, which suggests Speed Optimizer serves WebP on the fly, but
  that is worth confirming in Site Tools.
- Home serves 768-pixel card images into slots a third of that size (161 KB
  of savings reported) and 22 ticker stamps as 150-pixel JPEGs.
- Fraunces is preloaded at low priority and the italic cut (70 KB) loads on
  the homepage.
- Clarity's script is served with a 1-day cache.
- The theme's three scripts are unminified (about 6 KB in total; cosmetic).

Accessibility 96 to 97 (one colour-contrast finding per page, the muted
eyebrow text). Best-practices 79 on every page, entirely third-party
cookies from the Google tag and Clarity.

### B4. Internal linking and thin pages

- Journal posts are strong: 2,000 to 3,300 words, written focus keywords,
  FAQ, five or six contextual internal links each, Rank Math 83 to 88.
- Destination pages (1,100 to 1,300 words) do not link to their own journal
  posts: the Italy page links to /journal/ once and to none of the five
  Italy posts. The cluster links up to the pillar; the pillar does not link
  down (rule R17).
- Operator pages are 207 to 227 words; the Tauck tour page is 268 words;
  the /destinations/ hub is 107 words. "Discovered, not indexed" is the
  usual verdict on pages this thin.
- The multi-generational page is 706 words for a query family Google
  already shows it for at position 33 to 75. Rule R16 asks for 3,000+ on a
  pillar.
- Rank Math's link counter reports 28 records whose body has no internal
  links (the destination, tour and operator records; their links live in
  the templates, which the counter does not see).

### B5. 404s with real traffic, and a missing privacy page

| Address | Evidence | Suggested target |
|---|---|---|
| /cruise-travel-trends/ | 12 hits in the 404 log, 24 page views, 48 impressions, ranks for "cruise trends" | cruiseoomph.com's trends page if one exists, else /travel-trends/ |
| /trip-quiz/ | 6 hits, 64 page views, 8 landing sessions, still indexed | /start-planning/ (an exception to D02, worth making) |
| /privacy-policy/ | 1 hit; the footer has no privacy or terms link at all | a new /privacy/ page, linked from the footer |
| /services, /blog, /faq, /newsletter, /terms | 1 to 3 hits each, old Weebly addresses | /custom-journeys/, /journal/, /start-planning/, /travel-trends/; or leave as 404 |

The privacy page is the one that matters: the site collects email addresses
on every page (newsletter, Travel trends, Start planning) and rule R42 and
the cookie banner both presume a policy to link to.

### B6. Schema and rich results

- Google detects Breadcrumbs on every indexed page and Review snippets on
  Client Stories. Self-serving reviews (a business rating itself on its own
  page) are not shown as rich results, so the AggregateRating on the
  TravelAgency node earns nothing; harmless, but do not expect stars.
- FAQPage markup is present on destinations, posts and ways pages. Google
  stopped showing FAQ rich results for commercial sites in 2023; keep it
  for AI answers, expect nothing in the SERP.
- /destinations/ and /escorted-tours/ emit no BreadcrumbList and a shorter
  robots tag than other pages (a different template path).
- Client Stories emits two TravelAgency nodes with the same `@id`.
- The homepage carries a one-item BreadcrumbList.
- Person: no `sameAs`, job titles "Luxury Travel Advisor" and "Physician",
  a medical credential; worth a deliberate decision on what Google should
  know about Eric.

### C. Tidy-ups

- Rank Math IndexNow (Bing) is set to posts and pages only; destinations,
  tours and operators are not pushed.
- The author sitemap is enabled while author archives are noindex (the
  plugin already hides it; turn the setting off).
- The knowledge graph is typed LocalBusiness with 9 to 5 opening hours
  seven days a week; the plugin overrides Rank Math's graph so nothing
  leaks, but the setting is wrong.
- Kadence Blocks is still active (the theme no longer uses it; two Kadence
  references remain in the homepage HTML). Pairs with the child-theme
  deletion in the runbook.
- Speed Optimizer 7.8.3 has an update to 7.8.4.
- External links to cruiseoomph.com open in the same tab without
  `rel="noopener"` (rule R60). A sister site in the same tab is a fair
  choice; just make it a decision.
- `/journal/?topic=…` is index,follow with a canonical to /journal/. That
  works; the pattern comment says noindex, so one of them should change.
- Rank Math says the mobile speed test "could not run"; this is PageSpeed's
  daily quota, not a site fault.
- This connector cannot read the cruiseoomph.com Search Console property
  (403), although it lists it. Worth a look at the Google account's
  permissions when the CruiseOomph audit comes up.

## The plan

Owner key: **Eric** = clicks in a dashboard, **PR** = I ship it on a branch
for review, **Ask** = needs Eric's yes first (live site, plugin, decision).

### Phase 0 — this week, dashboards only (about an hour of clicks)

1. **Eric: request indexing** in Search Console for the 24 unindexed URLs,
   ten a day. List in the appendix. (A1)
2. **Eric: GA4 internal traffic.** Admin → Data streams → Configure tag
   settings → Define internal traffic: add the home IP. Then Admin → Data
   filters → set the Internal traffic filter to Active. (A3)
3. **Eric: mark key events.** Admin → Events → toggle `inquiry_sent` and the
   newsletter confirmation event as key events. (A3)
4. **Ask, then Eric or me via the connector: Rank Math settings.** Default
   OpenGraph thumbnail; IndexNow post types add destinations, tours,
   operators; author sitemap off; title separator "|"; knowledge-graph
   opening hours. (B2, C)
5. **Eric: Google Business Profile.** Confirm one exists for Oomph Travel
   LLC, Port Angeles, with the site linked; it is the shortest path to
   "travel advisor port angeles" and to the "eric hempel" clicks. (R32)

### Phase 1 — one PR each, this week and next

6. **Rewrite the cruise-era metadata** (A2): homepage title and description
   (Rank Math homepage setting, Ask), About and Client Stories titles and
   descriptions, and the Organization and Person literals in
   `class-schema.php` (description, `knowsAbout`, job title, `sameAs`).
   Draft copy in the appendix.
7. **Write the ten destination titles and descriptions, three tour titles,
   and focus keywords** (B1). Ship through the plugin's seed corrections so
   they survive a database push, same mechanism as the post corrections.
   Draft copy in the appendix.
8. **Destination pages link to their journal posts** (B4): a "From the
   Journal" block on the destination template listing posts tagged with that
   destination, three at a time. Closes the pillar-to-cluster gap for all
   eleven destinations at once.
9. **Share images** (B2): the theme emits `og:image` from each template's
   hero; Rank Math's default image covers the rest.
10. **Redirects and the privacy page** (B5): `/trip-quiz/` and
    `/cruise-travel-trends/` in `class-redirects.php` (Ask: targets),
    a `/privacy/` page with the PlainSend and Clarity disclosures, and a
    footer legal link.
11. **Analytics hygiene** (A3): both forms already fire `generate_lead`
    with `lead_source` (checked in the theme scripts; R11 holds), so the
    gap is item 3 above, not code. The code part is blocking the Site Kit
    and Clarity tags for the Playwright runner so e2e runs stop counting
    as visits.
12. **Schema tidy** (B6): BreadcrumbList on the two hub pages, one
    TravelAgency node on Client Stories, no breadcrumb on the homepage.

### Phase 2 — weeks three and four

13. **Performance** (B3), in order of payoff: destination heroes regenerated
    as WebP at 1280 and 1920 with real `sizes`; card and ticker images at
    the size of their slot; Fraunces regular preloaded at high priority and
    the italic cut dropped from the homepage critical path; Speed Optimizer
    "Minify JavaScript" on (Ask); then a small inline critical-CSS block for
    the header and hero. Re-run Lighthouse after each. The Google tag
    container stays; Site Kit controls it, and GA4 needs it.
14. **Thin pages** (B4): operator pages to 600 to 800 words with first-hand
    notes and the operator's tour list; the Tauck tour page to parity with
    the other two; the /destinations/ hub gains an intro paragraph per
    region.
15. **Multi-generational pillar** (B4): grow the page toward 3,000 words,
    question-form H2s, linking to the Hawaii, Caribbean and Italy posts and
    the Italy destination.
16. **Kadence Blocks and the child theme** (C): Ask, then deactivate and
    delete, then the runbook PR.

### Phase 3 — ongoing

17. **Two journal posts a month**, each tagged with a destination (so item 8
    links it), each with a written focus keyword and a `generate_lead` CTA.
    The twelve existing posts are the template; they are good.
18. **Weekly, five minutes:** Search Console → Pages: watch the "Not
    indexed" count fall and the cruise URLs drop out; Core Web Vitals once
    field data appears.
19. **Monthly:** re-run this audit through the connector (Rank Math site
    audit, GSC top queries, GA4 real-visitor numbers) and compare.
20. **Links from outside:** the Nexion and CLIA advisor directories, the
    Travel Leaders profile, LinkedIn, and a link from cruiseoomph.com's
    About page back here. The site has no measurable backlinks; three or
    four relevant ones change the indexing picture more than anything on
    the page.

### What to expect

Organic search brought 20 sessions in 90 days. Phases 0 and 1 are
prerequisites, not growth: they get the right pages indexed with the right
words on them. The growth comes from the journal posts and destination
pages over the following three to six months, and it will show first as
impressions on "custom trips to …", "… travel advisor" and the comparison
queries the posts answer.

## Appendix A — URLs to request indexing (ten a day)

Day 1
- https://oomphtravel.com/destinations/italy/ (re-request; it is indexed, but it is the hub)
- https://oomphtravel.com/journal/how-many-days-in-rome/
- https://oomphtravel.com/journal/when-to-book-italy-for-summer-2027/
- https://oomphtravel.com/journal/italy-by-train-or-private-driver/
- https://oomphtravel.com/journal/italian-lakes-or-dolomites/
- https://oomphtravel.com/journal/porto-or-lisbon/
- https://oomphtravel.com/journal/globus-vs-insight-vs-tauck/
- https://oomphtravel.com/journal/escorted-tour-vs-independent-travel/
- https://oomphtravel.com/custom-journeys/
- https://oomphtravel.com/destinations/spain/

Day 2
- https://oomphtravel.com/journal/hawaiian-island-for-families/
- https://oomphtravel.com/journal/caribbean-villa-or-resort/
- https://oomphtravel.com/journal/staffed-caribbean-villa-cost/
- https://oomphtravel.com/journal/riviera-maya-vs-caribbean/
- https://oomphtravel.com/journal/escorted-safari-or-private-safari/
- https://oomphtravel.com/destinations/hawaii/
- https://oomphtravel.com/destinations/caribbean/
- https://oomphtravel.com/destinations/mexico/
- https://oomphtravel.com/destinations/croatia/
- https://oomphtravel.com/destinations/africa/

Day 3
- https://oomphtravel.com/escorted-tours/globus/
- https://oomphtravel.com/escorted-tours/insight-vacations/
- https://oomphtravel.com/tours/tauck-italy-rome-to-the-lakes/
- https://oomphtravel.com/tours/globus-italian-treasures/
- https://oomphtravel.com/tours/insight-best-of-italy/

## Appendix B — draft metadata (for review, not shipped)

Titles under 60 characters, descriptions 150 to 160, " | Oomph Travel"
suffix, No List checked.

| Page | Title | Description |
|---|---|---|
| Home | Travel Advisor for Custom Trips & Tours \| Oomph Travel | Custom journeys, escorted tours and resort or villa stays in Italy, Europe, Hawaii, Mexico and the Caribbean, planned by one named advisor. No planning fee. |
| About | Eric Hempel, Travel Advisor, Port Angeles WA \| Oomph Travel | Eric Hempel, CLIA member and Nexion-affiliated travel advisor in Port Angeles, WA. I plan custom trips, escorted tours and villa stays, one client at a time. |
| Client Stories | Client Reviews: Custom Trips & Tours \| Oomph Travel | What clients say after the trip, in their own words: the planning, the hotels, the pace, and what they would do again. Four reviews, names and dates included. |
| Italy | keep | keep |
| France | Custom Trips to France, Beyond Paris \| Oomph Travel | Custom trips to France planned by one advisor: four nights in Paris, then Provence, the Dordogne, Burgundy or the Loire, with drivers, guides and hotels booked. |
| Spain | Custom Trips to Spain, City by City \| Oomph Travel | Custom trips to Spain planned around its late clock: Madrid, Barcelona, Seville and the Basque coast, with the trains, hotels and tables booked in the right order. |
| Portugal | Custom Trips to Portugal, Lisbon to the Douro \| Oomph Travel | Custom trips to Portugal: Lisbon, Porto, the Douro valley and the Algarve, planned by one advisor who has driven the roads. Ten-day outline and the months to go. |
| Greece | Custom Trips to Greece, Athens to the Islands \| Oomph Travel | Custom trips to Greece planned from Athens outward: which islands, how many, and the ferries and flights between them, with hotels chosen for the view and the walk. |
| Croatia | Custom Trips to Croatia & the Adriatic \| Oomph Travel | Custom trips along the Dalmatian coast: Split, Hvar, Korčula and Dubrovnik by road and ferry, planned by one advisor, with the islands chosen for your pace. |
| UK & Ireland | Custom Trips to Britain & Ireland, by County \| Oomph Travel | Custom trips to England, Scotland, Wales and Ireland planned a county at a time, with real drive times, country hotels and the trains that beat the car. |
| Hawaii | Custom Hawaii Trips, One Island at a Time \| Oomph Travel | Hawaii planned island by island: Oahu, Maui, Kauai or the Big Island, which suits families, couples or three generations, and the resorts worth the rate. |
| Mexico | Custom Mexico Trips from One Good Base \| Oomph Travel | Mexico planned from one good base: Riviera Maya, Los Cabos, Puerto Vallarta or the colonial towns, with the flights, resorts and days out booked by one advisor. |
| Caribbean | Caribbean Trips, Planned Island by Island \| Oomph Travel | The Caribbean planned island by island: which one fits the group, the flight that gets you there, and resort versus staffed villa, with real costs for each. |
| Africa | Safari Planning, with the Right Guide \| Oomph Travel | A first safari planned by one advisor: Kenya, Tanzania, Botswana or South Africa, escorted or private, the camps, the light aircraft and the seasons that matter. |
| Tauck tour | Tauck Italy: Rome to the Lakes, 12 Nights \| Oomph Travel | keep |
| Globus tour | Globus Italian Treasures, 10 Nights \| Oomph Travel | keep |
| Insight tour | Insight Best of Italy, 10 Nights \| Oomph Travel | keep |

Focus keywords to set: "custom trips to france", "custom trips to spain",
"custom trips to portugal", "custom trips to greece", "croatia trip
planner", "uk and ireland trip planner", "hawaii travel advisor", "mexico
travel advisor", "caribbean travel advisor", "safari travel advisor",
"tauck rome to the lakes", "globus italian treasures", "insight best of
italy", "globus tours", "insight vacations", "tauck tours", "custom
journeys", "resorts and villas", "multigenerational travel planner",
"travel advisor port angeles" (About), "travel advisor" (Home).

## Appendix C — how this was measured

- Search Console API: performance, query and page reports, 90 days and the
  22 days since launch; URL inspection of all 41 sitemap pages plus the old
  addresses.
- GA4 Data API: 90 days, with the connector's data-centre filter applied
  for the "real visitors" figures.
- Rank Math PRO abilities (read-only): site audit, settings snapshot,
  system status, link report, sitemap status, 404 log, redirections,
  robots.txt, llms.txt, SEO scores, post metadata and links for sampled
  records.
- Crawl of the 41 sitemap URLs with a browser user agent (SiteGround blocks
  non-browser agents with 403 and rate-limits after about sixty requests
  with a 202 challenge page).
- Lighthouse 12, mobile, simulated throttling, run locally; the PageSpeed
  Insights API's daily quota was exhausted.
