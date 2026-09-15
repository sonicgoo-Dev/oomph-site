# Tour records

One JSON file per escorted tour. A file here is the source of record for what
`wp oomph import-tour` writes onto the site; the site copy is a **draft** until
Eric adds the hero photo, checks the price and publishes it from the edit
screen. Nothing here is deployed by rsync — `deploy.yml` ignores `content/**` —
the file reaches the site through the **"Add a tour"** Actions button
(`.github/workflows/import-tour.yml`).

How a tour gets here: the `/add-tour` skill (`.claude/skills/add-tour/`) reads
an operator's itinerary PDF or web page, writes the record in Eric's voice,
checks it with `node scripts/tours/validate.mjs`, opens the pull request and
runs the button.

## The record

Field names are the tour's ACF field names (`acf-json/group_oomph_tour.json`).
Only `slug`, `title`, `operator`, `destination` and `nights` are required;
everything else may be left out and filled in the admin later.

```json
{
  "slug": "globus-italian-treasures",
  "title": "Italian Treasures",
  "operator": "globus",
  "destination": "italy",
  "nights": 10,
  "start_city": "Rome",
  "end_city": "Rome",
  "months": [4, 5, 6, 9, 10],
  "from_price": 3899,
  "price_note": "per person, two sharing, land only",
  "blurb": "Rome, the Amalfi Coast, Tuscany and Venice in ten nights, with the driving done for you.",
  "group_size": "Up to about 40",
  "pace": "Full days, with a free afternoon in Florence and Venice",
  "inclusions": "Nine breakfasts and four dinners\nTour director throughout\nVatican Museums and Sistine Chapel entry\nBoat to Capri\nCoach travel and luggage handling",
  "brochure_link": "https://www.globusjourneys.com/tour/italian-treasures/",
  "erics_note": "<p>Why I sell this one, who it suits, and who should ask me about something else.</p>",
  "featured": false,
  "itinerary": [
    { "day": 1, "title": "Arrive in Rome", "overnight": "Rome", "text": "Transfer to the hotel; the evening is yours." },
    { "day": 2, "title": "Rome", "overnight": "Rome", "text": "A guided morning in the Vatican Museums, then the Colosseum." }
  ]
}
```

| Key | Type | Notes |
|---|---|---|
| `slug` | string | Lower-case, hyphens: `<operator>-<tour name>`. Becomes `/tours/<slug>/`. |
| `title` | string | The operator's tour name, without the operator's own name in it. |
| `operator` | slug | One of: `globus` `tauck` `insight-vacations` `abercrombie-kent` `national-geographic-expeditions` `classic-vacations` `avanti-destinations`. |
| `destination` | slug | One of: `italy` `uk-ireland` `france` `spain` `portugal` `greece` `croatia` `hawaii` `mexico` `caribbean` `africa`. |
| `nights` | integer | Nights, not days. A "12-day" tour is 11 nights. |
| `start_city` `end_city` | string | As the operator lists them. |
| `months` | integers 1–12 | The months the operator publishes departures in. Drives the month filter. |
| `from_price` | integer | The operator's **standard** from-price per person in whole US dollars (D36). Never a sale, early-booking or last-minute rate. Leave it out when unsure; Eric checks it before publishing. |
| `price_note` | string | e.g. "per person, two sharing, land only". |
| `blurb` | string | Two lines on the tour card. Specifics beat adjectives. |
| `group_size` | string | e.g. "Up to about 40", "Small group, 24 at most". |
| `pace` | string | Relaxed, moderate, full days — in Eric's words or the operator's. |
| `inclusions` | string | One per line (`\n`). |
| `brochure_link` | URL | The operator's own page for this trip. |
| `erics_note` | HTML | Why this tour, who it suits. Required before a tour goes live. First person, Eric's voice. |
| `featured` | boolean | Eligible for the homepage featured row. Default false. |
| `itinerary` | rows | `day` (integer), `title`, `overnight` (city), `text`. One row per day. |

Not in the record, set in WordPress: the featured image (the tour's hero
photo), the gallery, and the publish click.

## Rules the importer enforces

- Unknown operator or destination slug: rejected.
- Any No List word (`docs/voice-guide.md`) in the title, blurb, note,
  inclusions, pace, group size or itinerary: rejected. Operators love
  "iconic", "breathtaking" and "unforgettable"; rewrite in plain words.
- A placeholder such as `[TBD]` or `$X,XXX`: rejected.
- A slug that already exists is left alone unless the button is run with
  **update** ticked, and then only the fields present in the file are
  rewritten. Status and photo are never touched.
- Nothing is ever published by the importer.

## Running it

- **Check first:** Actions → "Add a tour" → slug, mode = `check`. The record
  is validated on the server and nothing is written.
- **Import:** same, mode = `import`. The summary links to the edit screen.
- **From a terminal on the server:** `wp oomph import-tour --file=<path> [--dry-run] [--update]`.
- **Locally, before the pull request:** `node scripts/tours/validate.mjs content/tours/<slug>.json`.
