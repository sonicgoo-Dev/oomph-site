# ACF Field Groups — Oomph Travel

Source-of-truth document for the structured fields that drive page content. **Define each group via the ACF Pro UI in WP admin**; the JSON sync writes the saved configuration to `wp-content/plugins/oomph-travel-core/acf-json/`, which is version-controlled and deployed via the plugin rsync.

**Sync target redirected:** `plugins/oomph-travel-core/includes/class-acf-config.php` filters `acf/settings/save_json` and `acf/settings/load_json` so the JSON lives with the CPTs it describes, not in the theme. Don't put `acf-json/` inside the theme — the filter overrides theme-based detection.

> **Resurfacing 2026-09 (Stage 4).** The rebuild's groups — Destination,
> Operator, Tour, Inquiry (Groups 5–8 below) — are **written as JSON in the
> repo first**, not built in the admin (plan §8.1). The JSON is the source of
> truth; the admin's "Sync available" notice is how each environment adopts
> it. The plan names **Secure Custom Fields** (the free WordPress.org fork)
> as the fields plugin; ACF Pro reads the same files. Which one is installed
> is Eric's decision and is not made by code. Groups 3 and 4 (Group Cruise,
> Ship Library) were deleted in Stage 2 (D02); their sections are kept below
> as history only.

---

## Workflow

1. Install ACF Pro on Local first (then staging + production).
2. WP admin → **ACF → Field Groups → Add New**.
3. Add the fields per spec below.
4. Set the Location rules per spec.
5. Save. ACF auto-writes `group_<id>.json` into `plugins/oomph-travel-core/acf-json/`.
6. `git add` the new JSON files, commit, push develop → staging → main → prod.
7. Repeat for each environment: after deploy, ACF detects the JSON and offers to sync. Sync to write the field groups into the local DB.

After sync, editing fields in the admin UI on any environment regenerates the JSON. **Treat the JSON as authoritative** — if it disagrees with the DB on staging or prod, sync from JSON.

---

## Group 1 — Page Hero

**Purpose:** structured hero content for any Page (Home, About, service hubs, Discovery Call, etc.). The template reads these fields and falls back to defaults when absent.

**Location rules:** `Post Type` is equal to `Page`.

**Position:** High (after title).
**Style:** Default.
**Label placement:** Top.

| Field name | Type | Required | Default | Notes |
|---|---|---|---|---|
| `hero_eyebrow` | Text | No | — | Max 40 chars. All-caps in the rendered output (Inter 500, tracked +0.08em). |
| `hero_headline` | Text | No | — | Max 80 chars. Renders as H1, Fraunces 300 italic. Leave empty to use the page template's built-in fallback (template never renders blank). |
| `hero_subhead` | Textarea | No | — | Max 200 chars. Fraunces italic 400. |
| `hero_image` | Image | No | — | Return format: Array. 1920×1080 WebP target. If empty, hero uses Bone canvas with no photograph. |
| `hero_cta_label` | Text | No | `Start a conversation →` | Primary CTA copy. Don't change without updating `cro-rules.md` R1. |
| `hero_cta_url` | Text | No | `/discovery-call/` | Primary CTA destination. Accepts relative paths or fully-qualified URLs (Text type, not URL — ACF's URL type rejects relative paths). |
| `hero_trust_strip` | True/False | No | `true` | Whether to show the credentials strip below the hero. |

---

## Group 2 — Service Page

**Purpose:** structured content for the three service hub pages (Luxury Cruise Planning, Custom Italy Travel, Multi-Generational Travel). Drives the consistent section anatomy without per-page template duplication.

**Location rules:** `Post Type` is equal to `Page` AND `Page Template` is equal to `service-page.php` (template lives at `wp-content/themes/kadence-oomph-child/service-page.php` — added in Phase 10.5).

**Position:** Normal.

| Field name | Type | Required | Notes |
|---|---|---|---|
| `service_keyword` | Text | No | Primary SEO keyword. Template falls back to page title when empty. |
| `service_negative_qualifiers` | Repeater | No | 1–4 rows. Each row: `bullet` (Text). Renders as "Who this is NOT for" — pre-qualifies. Template has voice-aligned fallback bullets. |
| `service_what_you_do` | Repeater | No | 0–8 rows. Each row: `headline` (Text), `body` (Textarea). The deliverables grid. Template has voice-aligned fallback that renders when empty. |
| `service_credentials_to_show` | Checkbox | No | Choices: `clia` · `silversea` · `nexion` · `britagent` · `ds_italy`. Contextual credential display per R29. |
| `service_faqs` | Repeater | No (5–8 rows when populated) | Each row: `question` (Text), `answer` (Textarea). Template has fallback FAQs that render visually; FAQPage schema only emits when ACF has real rows. |

**Min/max on `service_faqs`:** ACF min is 0, max is 8. Editorial guidance: when populating, aim for 5–8 rows (below 5 is too thin for FAQPage schema to matter; above 8 reads padded). Empty repeater is allowed (template uses fallback FAQs; schema is suppressed).

---

## Group 3 — Group Cruise

**Purpose:** structured fields for hosted group cruise landing pages. Drives the Event schema, the day-by-day accordion, and the scarcity display.

**Location rules:** `Post Type` is equal to `oomph_cruise` (registered by `oomph-travel-core` plugin).

**Position:** Normal.

| Field name | Type | Required | Notes |
|---|---|---|---|
| `cruise_ship_name` | Text | Yes | e.g., "Silver Nova" |
| `cruise_line` | Text | Yes | e.g., "Silversea" |
| `cruise_region` | Taxonomy | Yes | Field type: Taxonomy. Taxonomy: `oomph_region`. Single value. |
| `cruise_dates_start` | Date Picker | Yes | Display format: F j, Y · Return format: Y-m-d (for schema). |
| `cruise_dates_end` | Date Picker | Yes | Same formats. |
| `cruise_price_per_person` | Number | Yes | USD. No decimals. Drives Event `offers.price`. |
| `cruise_single_supplement` | Number | No | USD. |
| `cruise_cabins_remaining` | Number | Yes | Real scarcity (R53). Update weekly. |
| `cruise_itinerary` | Repeater | Yes (7–21 rows) | Each row: `day` (Number), `port` (Text), `activity` (Textarea). |
| `cruise_inclusions` | Textarea | No | Plain text, line-break separated. |
| `cruise_exclusions` | Textarea | No | Plain text. |
| `cruise_deposit_amount` | Number | No | USD. |
| `cruise_deposit_deadline` | Date Picker | No | |

---

## Group 4 — Ship Library

**Purpose:** per-ship content reused across every sailing of that ship — the gallery band, ship intro, and quick-facts table on Group Cruise pages. The templates match a sailing's `cruise_ship_name` to the Ship post **title**, so titles must be exact ("Silver Nova", not "SILVER NOVA").

**Location rules:** `Post Type` is equal to `oomph_ship` (registered by `oomph-travel-core`, `class-cpt-ship.php`). Not publicly queryable — a ship record has no front-end URL; it only feeds sailing pages.

**Position:** High (after title).

| Field name | Type | Required | Notes |
|---|---|---|---|
| `ship_line` | Text | Yes | e.g., "Silversea". Eyebrow of the ship section. |
| `ship_photo_credit` | Text | No | e.g., "Photos courtesy of Silversea Cruises". Caption under the gallery. Empty when photos are Eric's own. |
| `ship_gallery` | Gallery | No | 6–12 images, licensed supplier media or Eric's own only. WebP, R22 file names. First image leads the band. |
| `ship_facts` | Repeater | No (3–6 rows when populated) | Each row: `fact_label` (Text), `fact_value` (Text). Guests / Crew / Suites / Launched. |

The ship **intro** is the post_content (classic prose, first person, 2–4 sentences — "When I sailed her in March 2025…"). This group ships as hand-authored JSON (`group_oomph_ship.json`); after deploy, ACF admin will show it pending sync — sync it on each environment.

---

## Group 5 — Destination (`group_oomph_destination.json`)

**Location:** `oomph_destination`. One record per destination page (plan §6.3). The hero is the **featured image**. The Destinations taxonomy term is created and renamed from the post automatically (`CPT_Destination::sync_term()`); nobody edits terms.

| Field name | Type | Notes |
|---|---|---|
| `headline` | Text (80) | H1. Empty → "{Destination}, planned by someone who keeps going back." |
| `intro` | WYSIWYG | Three paragraphs at the 720px measure. |
| `variant` | Select | `custom` (default) · `resort` (Hawaii, Mexico, Caribbean) · `guided` (Africa, D33). One template, three variants. |
| `regions` | Repeater | `name`, `blurb`. Max 8. |
| `sample_itinerary` | Repeater | `day`, `title`, `text`. Illustrative, no prices. |
| `stays` | Repeater | `name`, `type` (hotel · resort · villa · private-home), `note`, `perks`. `perks` is hidden for villas and private homes (D27, D40). |
| `best_months` | Checkbox 1–12 | The twelve-month strip. |
| `shoulder_months` | Checkbox 1–12 | Guided variant only: the third state of the strip. |
| `faq` | Repeater | `question`, `answer`. Max 6. FAQPage schema. |
| `related_operators` | Relationship → `oomph_operator` | IDs. |
| `cruiseoomph_region` | Select | Alaska · Antarctica · Caribbean · Japan · Mediterranean · Northern Europe — the values `cruiseoomph.com/cruises/?region=` accepts. Empty → no "by ship" line. |

## Group 6 — Operator (`group_oomph_operator.json`)

**Location:** `oomph_operator`. Public at `/escorted-tours/{slug}/`. Display order is core `menu_order` (Order box); destinations supported is the Destinations taxonomy.

| Field name | Type | Notes |
|---|---|---|
| `kind` | Select, required | `escorted` · `fit` (plan §6.6). |
| `logo` | Image (ID) | Deferred until brand guidelines are checked. |
| `fit_line` | Text (120) | The one line on the card. |
| `fit_note` | WYSIWYG | The operator page body. |
| `group_size`, `price_band` | Text | |
| `inclusions` | Textarea | One per line. |
| `ways` | Checkbox | FIT only: `custom` · `resorts`. Decides the card link. |
| `cruiseoomph_line` | True/false | Sea voyages sold at CruiseOomph (National Geographic, D32). |
| `website` | URL | Internal, never printed. |

## Group 7 — Tour (`group_oomph_tour.json`)

**Location:** `oomph_tour`. Public at `/tours/{slug}/`. Featured image is the hero and card photo; destinations are the taxonomy; Publish/Draft is the status. **No departures, no availability** (D35). Every card reads "Dates and availability confirmed on request." (D34).

| Field name | Type | Notes |
|---|---|---|
| `operator` | Post object → `oomph_operator`, required | ID. |
| `blurb` | Textarea (200) | Card blurb. |
| `nights` | Number, required | |
| `start_city`, `end_city` | Text | |
| `from_price` | Number | Whole USD, standard operator from-price only (D36). Empty = unpriced; `$X,XXX` never ships. |
| `price_note` | Text | Default "per person, double occupancy". |
| `gallery` | Gallery (IDs) | Max 8. |
| `itinerary` | Repeater | `day`, `title`, `overnight`, `text`. |
| `inclusions` | Textarea | One per line. |
| `group_size`, `pace` | Text | |
| `brochure_link` | URL | |
| `erics_note` | WYSIWYG | Required before a tour goes live. |
| `featured` | True/false | Homepage featured row. |

Admin list columns: operator · nights · from price · destinations · featured (`class-admin-columns.php`).

## Group 8 — Inquiry (`group_oomph_inquiry.json`)

**Location:** `oomph_inquiry`. Private post type: not public, not queryable, not in REST; nobody can add one from the admin. Written by the Start planning form (plan §6.15, Stage P7). Destinations chosen are the taxonomy; the timestamp is the post date.

| Field name | Type | Notes |
|---|---|---|
| `trip_type` | Select | `custom` · `escorted` · `resort` · `multigen` · `cruise-land` · `not-sure`. "A cruise" is never stored — it sends to CruiseOomph. |
| `when`, `travelers`, `budget` | Text | As the form labelled them. |
| `notes` | Textarea | |
| `name`, `contact_method` (email · phone · text), `contact_value` | | |
| `consent`, `newsletter_opt_in` | True/false | The subscribe tick is recorded only; PlainSend handles the subscribe. |
| `source_page` | Text | |
| `tour`, `destination` | Post object | Set when the form was opened from a tour or destination page. |
| `assigned_to` | User | Eric or Amy. |

---

## Rebuilding from scratch

If the `acf-json/` directory is ever wiped or corrupted, this document is the recipe to recreate the field groups via UI. Field names are exact; don't rename — the template reads them by name.

---

## Don't define field groups via PHP

ACF supports defining field groups via `acf_add_local_field_group()` in PHP. Tempting, but it breaks JSON sync — fields defined in PHP can't be edited in the UI. We chose the UI + JSON approach because:

- Eric can adjust labels and help text without touching code
- Field group changes ship via standard git diff
- The same JSON file works across local / staging / production
