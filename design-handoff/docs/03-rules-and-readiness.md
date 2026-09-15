# Rules, placeholders and what's still missing

Exported from Figma page `06 Readiness & states` on 10 September 2026. **Read this before writing code.**

## Rules you must not quietly break

Each of these is a deliberate decision with a reason. If one has to change, change it on purpose and say so.

- **Perk language stays conditional.** "Depending on the property and the rate, I can usually add…" — never "you will receive". Breakfast and a resort credit are contractual and they arrive. An upgrade, an early check-in and a late checkout are not — they depend on how full the hotel is the morning the guest turns up.
- **Villas never get perk language.** HVN is vetting and access. The Resorts & villas page says outright that breakfast, upgrades and credits do not apply to a private house. Do not merge those two blocks.
- **Perk copy is only valid for a property badged SELECT or CURATED** (D40). Travel Leaders runs four hotel programmes; WORLDWIDE carries no guest amenities at all.
- **Start planning does not post to PlainSend.** It is a sales enquiry, not a list signup (D30). It stores as a private Inquiry post and emails Eric. The newsletter tick is a separate optional subscribe.
- **The trends guide is double opt-in.** The success notice must say so in plain words — "check your email and press the button" — or signups leak away silently.
- **Cruises go to CruiseOomph, land stays here** (D32). Including National Geographic's Lindblad-operated sea voyages. Do not rebuild cruise search here.
- **Tour pages carry no live data.** No departures table, no per-date price, no availability status (D35).
- **Printed prices are standard operator from-prices** (D36). Never a promotional or last-minute rate, however good it looks today.
- **Client quotes are never reworded** (D38). Trim for length if you must. Changing a client's words — even to fix grammar, or to swap "our travel agent" for Eric's name — is a fabrication, not an edit.
- **One advisor, one voice.** First person singular throughout. "We" only on About, where Amy is introduced.
- **No List.** Never: bespoke, wanderlust, magical, breathtaking, curated, jaw-dropping, paradise, bucket list.

## What is placeholder and must never ship

The prototype deliberately marks unfinished content so it cannot be mistaken for real. If any of these reaches a published page, something has gone wrong.

| Marker | Means |
|---|---|
| Anything in square brackets — `[Client name]`, `[Client story needed: …]`, `[OPERATOR]`, `[TOUR NAME]` | Content Eric has not supplied yet |
| `$X,XXX` | An unpriced tour |
| A grey rectangle where a vendor logo goes | All five operators |
| A stock photo under a named hotel | Two of the three Hawaii resort cards |

**Build a check that fails if a square bracket, an `$X,XXX`, or a known placeholder image ID appears in published content.** That is cheaper than remembering.

## Still blocked on Eric — four items

None of these stop the build starting. All of them stop the site going live.

1. **Sign-off on the three Hawaii resorts** — Fairmont Kea Lani (Wailea), Fairmont Orchid (Kohala Coast) and The Royal Hawaiian (Waikiki) are in, with their real 2026 SELECT benefits. Needed: confirmation these are the three he'd recommend, a line in his own voice under each, and each property's own photography.
2. **Vendor logos** for the five operators. Deferred by Eric for now. Check each operator's brand guidelines before they go on.
3. **Client stories for the land pages.** Four real reviews are in and verified. All four are about cruises, so six land pages carry bracketed prompts.
4. **Three tours per remaining operator**, plus Eric's note under each saying who it suits.

## Open, but not blocking

- The three testimonials on the **live** oomphtravel.com homepage (The Hendersons / M. & R. / D. Patel) are not among the four verified reviews and read as written examples. Raised with Eric twice; unanswered. Do not carry them into the new site until he confirms they're real.
- Insight and Globus day-by-day itineraries and 2027 departure calendars. Both sites require a date selection first.

## Known and accepted

- Eyebrow labels are 11px mobile / 12px desktop, uppercase with wide tracking. Deliberate label style, not body copy — do not "fix" it to meet a minimum font size rule.
- Only three mobile screens are drawn. The rules they establish cover the rest: 24px gutter instead of 80px; card rows stack full-width; destination rows become a horizontal scroller that bleeds off the right edge; form actions become a sticky bottom bar; nav is a full-screen navy drawer with a subtitle under every link.
- Utility pages (links, 404, privacy, accessibility) are specified in plan §6.16 but not drawn. Text templates with no new design decisions.

## Screens drawn — 20 desktop, 3 mobile

Desktop, in Figma page order: Home · All destinations · Destination template (Italy) · Destination resort variant (Hawaii) · **Destination guided variant (Africa)** · Escorted tours index · Operator template (Tauck) · Tour detail template · Custom journeys · Resorts & villas · Multi-generational trips · Cruise planning · About · Client stories · Journal index · Journal article · Travel Trends guide · Start planning step 1 · step 2 · receipt

Mobile: Home · Nav drawer · Start planning step 1

Africa is a **variant** of the destination template, not a separate template: guided-first rather than custom-first, a three-state when-to-go strip instead of two, and an FAQ that defers the malaria question to a doctor rather than answering it.
