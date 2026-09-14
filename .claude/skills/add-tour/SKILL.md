---
name: add-tour
description: Turn an escorted-tour itinerary (an operator's PDF or web page Eric shares) into a tour record and put it on the site as a draft. Use when Eric says "add this tour", shares a tour PDF or link, or asks to update a tour's itinerary or price.
---

# Add a tour

Eric hands over an itinerary — a Globus, Tauck or Insight PDF, or the
operator's web page — and gets back a draft tour on the site with every
field filled in his voice, plus a link to the edit screen. What stays his:
the hero photo, a look at the price, and the Publish click.

The pieces: the record is `content/tours/<slug>.json` (shape and rules in
`content/tours/README.md`), the checker is `scripts/tours/validate.mjs`, and
the site side is the **"Add a tour"** Actions button (`import-tour.yml`),
which runs `wp oomph import-tour` on staging over SSH. Nothing here ever
publishes a tour or touches production unless Eric asks for production.

## 1. Read the source

- **PDF:** use the Read tool on the path Eric gave (`pages: "1-10"`, then the
  next range; at most 20 pages per call). Read the whole thing — the price
  grid and the "included" list are usually at the back.
- **URL:** WebFetch the operator page; itinerary and inclusions are usually
  on one page, dates and prices on a "dates & prices" page — fetch that too.
- **Both/neither:** if Eric also typed facts (price, months, why he sells
  it), those win over the PDF.

Do not read Eric's other tours or the seeder for copy to reuse. Each record
is written fresh from its own source.

## 2. Write the record

Save `content/tours/<slug>.json`. Field by field:

| Key | How to fill it |
|---|---|
| `slug` | `<operator>-<tour name>` in lower-case hyphens, e.g. `globus-italian-treasures`. Drop "tour", "journey", "with" and the operator's own name from the tour name. |
| `title` | The operator's tour name as they print it, without the operator's name or a day count. |
| `operator` | One of `globus` `tauck` `insight-vacations` `abercrombie-kent` `national-geographic-expeditions` `classic-vacations` `avanti-destinations`. Read it off the brochure's branding. |
| `destination` | One of `italy` `uk-ireland` `france` `spain` `portugal` `greece` `croatia` `hawaii` `mexico` `caribbean` `africa`. A multi-country tour takes the country with the most nights. |
| `nights` | Days minus one. A "12 days" tour is 11 nights. Count the itinerary rows if the cover disagrees. |
| `start_city` `end_city` | From day 1 and the last day. |
| `months` | Month numbers of the published departure dates, e.g. `[4, 5, 6, 9, 10]`. If the brochure gives none, leave the key out and say so. |
| `from_price` | The operator's **standard** land from-price per person, whole US dollars, no symbol. **Never** a sale, early-booking, "save $X" or last-minute figure (D36). If only a promotional price is shown, leave the key out and tell Eric. |
| `price_note` | What the price covers: "per person, two sharing, land only". |
| `blurb` | Two lines for the tour card. Name the places and the count: "Rome, Sorrento, Florence and Venice in ten nights, with the driving done." No adjectives doing the work. |
| `group_size` | As the operator states it ("Up to about 40", "Small group, 24 at most"). If unstated, leave it out. |
| `pace` | In plain words: "Full days, with a free afternoon in Florence and Venice" or "Relaxed; two and three nights in each place". |
| `inclusions` | One item per line joined with `\n`, in the operator's order, shortened: "Nine breakfasts and four dinners", "Tour director throughout", "Vatican Museums entry". Leave out marketing lines. |
| `brochure_link` | The operator's own page for this exact tour, if you have it. |
| `erics_note` | One or two `<p>` paragraphs, first person, Eric speaking to one client: why he would suggest this tour, who it suits (first trip, couples, pace, mobility), and who should ask him about a different tour or a custom journey instead. Draw only on the itinerary. **Do not invent Eric's own travels** ("when I stayed at…"); he adds those. |
| `featured` | `false`. Eric chooses the homepage row. |
| `itinerary` | One row per day: `day` (integer), `title` (the day's place or theme, 2–6 words), `overnight` (the city; empty string on the departure day), `text` (one to three plain sentences: what the group does, what is guided, what is free time, meals if notable). |

### Voice, without exception

- Rewrite operator prose; never paste it. Their copy runs on No List words
  (`docs/voice-guide.md`): iconic, breathtaking, stunning, unforgettable,
  hidden gem, bucket list, curated, ultimate, world-class, indulge, escape,
  magical, epic, paradise, once-in-a-lifetime, 5-star. Say what the group
  actually does and sees instead.
- No superlatives (best, most, finest, amazing, incredible). No "we" in
  Eric's note — he is one advisor.
- Specifics beat adjectives: the church's name, the number of nights, the
  meal, the boat, the train.
- No square-bracket placeholders and no `$X,XXX`. If a fact is missing,
  leave the key out and list it for Eric at the end.
- Plain punctuation. Curly apostrophes (’) are fine; no em dashes in copy.

## 3. Check it

```bash
node scripts/tours/validate.mjs content/tours/<slug>.json
```

Fix every `problem:` and read every `warning:`; rewrite until it passes.
The plugin re-checks the same rules on the server and refuses the record
if anything slipped through.

## 4. Commit and merge

Eric's standing instruction is to do this without asking. Content under
`content/` does not trigger a deploy.

```bash
git fetch -q origin && git checkout -q -b tour/<slug> origin/develop
git add content/tours/<slug>.json
git commit -q -m "content(tours): <Title> (<Operator>)" -m "Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
git push -q -u origin tour/<slug>
gh pr create --repo sonicgoo-Dev/oomph-site --base develop --title "content(tours): <Title> (<Operator>)" --body "<one paragraph: source, what is filled, what is left out and why>

🤖 Generated with [Claude Code](https://claude.com/claude-code)"
gh pr merge --repo sonicgoo-Dev/oomph-site --squash --delete-branch
git checkout -q develop && git pull -q
```

Updating a tour that already exists: edit its JSON, same commit flow, then
run the button with `update=true`. Only the keys in the file are rewritten;
the tour's status and photo are untouched.

## 5. Run the button

The workflow file lives on `main`; the record is read from `develop`.

```bash
gh workflow run import-tour.yml --repo sonicgoo-Dev/oomph-site --ref main -f tour=<slug> -f mode=check -f environment=staging -f ref=develop
```

Wait ~10 s, find the run and watch it:

```bash
gh run list --repo sonicgoo-Dev/oomph-site --workflow=import-tour.yml --limit 1 --json databaseId,status,conclusion
gh run watch <id> --repo sonicgoo-Dev/oomph-site --exit-status
```

If `check` fails, read the log (`gh run view <id> --repo sonicgoo-Dev/oomph-site --log | grep -i "warning\|error"`), fix the record, go back to step 3.
If it passes, run again with `-f mode=import` (add `-f update=true` when
rewriting an existing tour) and watch it. The log line `Edit: <url>` is the
tour's edit screen; keep it for the report.

Production (only when Eric asks, and only after the same import ran clean
on staging): `-f environment=production`. The production environment's
reviewer gate applies.

## 6. Report to Eric

Short. Lead with the outcome and the edit link. Then three things he does
in WordPress, click by click: set the featured image (the hero photo, a
real photograph of the place, landscape), check the from-price against the
operator's current standard rate, click Publish. Then anything you left
out or were unsure of (price, months, group size), one bullet each. No
terminal commands in his report.
