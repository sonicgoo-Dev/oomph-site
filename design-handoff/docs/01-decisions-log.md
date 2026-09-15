# Decisions log — D01 to D43

Exported from Figma page `01 Decisions log` on 10 September 2026.

Every approval and change has a row. If one of these has to change, change it on purpose, say so, and add a new row rather than editing an old one.

| # | Decision | Source |
|---|---|---|
| **D01** | Focus: custom journeys (FIT), escorted tours, resorts & villas | Eric, Sep 9 |
| **D02** | Group cruise archive, sailings, cabin quiz, cruise guide and nine cruise posts deleted, not redirected | Eric, Sep 9 |
| **D03** | Cruise Planning page stays: how Eric plans cruises, pre/post-cruise land, links to CruiseOomph | Eric, Sep 9 |
| **D04** | Escorted tours = operator departures Eric sells, presented by operator with fit notes | Eric, Sep 9 |
| **D05** | Tours are records (itinerary, from-price, details) entered via an admin form; no feed | Eric, Sep 9 |
| **D06** | Hotels & resorts = resort vacations, villas & private homes, hotels inside custom trips | Eric, Sep 9 |
| **D07** | Destinations: Italy, UK & Ireland, France, Spain, Portugal, Greece, Croatia & Adriatic, Hawaii, Mexico, Caribbean | Eric, Sep 9 |
| **D08** | Homepage speaks first to couples marking a milestone | Eric, Sep 9 |
| **D09** | Audley Travel is the visual benchmark | Eric, Sep 9 |
| **D10** | Same family, different mood: shared type/buttons/header/footer with CruiseOomph; Oomph uses navy/ink/slate + deep teal | Eric, Sep 9 |
| **D11** | Hero: full-width photo, place is the subject, short headline, one button | Eric, Sep 9 |
| **D12** | Logo: wordmark + standalone symbol (superseded by D22) | Eric, Sep 9 |
| **D13** | Stock photography only, to the photography rule | Eric, Sep 9 |
| **D14** | Primary action: two-step Start Planning form; Calendly on the receipt page | Eric, Sep 9 |
| **D15** | Amy featured on About; Eric leads | Eric, Sep 9 |
| **D16** | Journal stays, refocused on destinations | Eric, Sep 9 |
| **D17** | Lead magnet: yearly Travel Trends guide | Eric, Sep 9 |
| **D18** | PlainSend.net replaces Flodesk for all email capture | Eric, Sep 9 |
| **D19** | Prototype in Figma; every page designed and approved before code | Eric, Sep 9 |
| **D20** | Carried forward: no planning fee; Fraunces + Inter; WCAG 2.2 AA; real-visitor CWV | Eric, prior |
| **D21** | Palette: Direction B Deep Teal. Warm Bone and Old Brass retired. Navy #17212E, Deep Navy #0F1620, Ink #1A202C, Slate #2D3748, Muted #64707D, Mist #EFF3F4, Mist deep #E3EBEC, White; accent #1F6F78, #7FC7CE on dark | Eric, Sep 9 |
| **D22** | Logo: Eric's luggage symbol in full colour beside a Fraunces wordmark; symbol alone = favicon/avatar/404; coral appears nowhere else | Eric, Sep 9 |
| **D23** | Motion motif: contrail under H1 once per page + static contrail beside section labels; hero drift; 3-step fade-up; homepage place-name ticker; card lift; arrow nudge; all off under reduced-motion | Eric, Sep 9 |
| **D24** | The Oomph Travel Homepage canvas (desktop 1440 + mobile 390, Direction B) is the approved reference; the Figma file matches it | Eric, Sep 9 |
| **D25** | Escorted tour operators: Globus, Tauck, Insight Vacations, Abercrombie & Kent, National Geographic Expeditions. FIT suppliers: Classic Vacations, Avanti Destinations. All named publicly on the site. | Eric, Sep 9 |
| **D26** | First three tour cards built from real Insight, Globus and Tauck trips supplied by Eric. | Eric, Sep 9 |
| **D27** | Villas run through HVN (Havens) — written as vetting and access, never perk language. Hotels through Internova SELECT. | Eric, Sep 9 |
| **D28** | Amy featured on the About page, portrait and bio carried across from CruiseOomph. | Eric, Sep 9 |
| **D29** | PlainSend replaces Flodesk. Eric built it, so there is no third-party API — the signup posts to his own endpoint. | Eric, Sep 9 |
| **D30** | Start planning is custom-built in three steps; the trends guide signs up through PlainSend; the cabin quiz is deleted. | Eric, Sep 9 |
| **D31** | With no forms left, Fluent Forms is removed entirely — 43 KB of CSS off every page. | Eric, Sep 9 |
| **D32** | National Geographic appears on OomphTravel as land expeditions only. Its Lindblad-operated sea voyages route to CruiseOomph. Land stays here, cruises go there. | Eric, Sep 9 — CONFIRMED |
| **D33** | Africa becomes an eleventh destination with its own page, written as guided-through-operators rather than custom-first. | Eric, Sep 9 |
| **D34** | Tours are a small hand-entered set per operator — three representative trips Eric enters and maintains himself — not a catalogue. Every tour card reads: Dates and availability confirmed on request. | Eric, Sep 9 |
| **D35** | The per-tour departures table is removed. Tour pages say dates move and that Eric checks them live, with a button. Nothing on a tour page needs routine updating. | Eric, Sep 9 |
| **D36** | Prices shown are standard operator from-prices only. Promotional and last-minute rates are mentioned in conversation, never printed on the site. | Eric, Sep 9 |
| **D37** | Client stories come from the four real reviews on oomphtravel.com. Pages with no matching real story carry a visible bracketed prompt instead of written copy. | Eric, Sep 9 |
| **D38** | All four client reviews verified word for word against Eric's Travel Leaders agent-profiler originals (Apr 2025). Trimmed for length only; no wording changed. Reviewer email addresses are never published. | Eric, Sep 10 |
| **D39** | travellover52 on the Client stories page is Amy Hempel, who also appears on the About page as associate advisor. Raised with Eric; his decision is that it stays as a client review. Do not re-raise, and do not quietly remove it. | Eric, Sep 10 |
| **D40** | SELECT confirmed live on Eric's account. Travel Leaders runs four hotel programmes — SELECT, SELECT Villas, CURATED, WORLDWIDE — and only the first two carry guest amenities, so perk copy is valid only for a property badged SELECT or CURATED. | Verified, Sep 10 |
| **D41** | Theme approach: fork the CruiseOomph theme into a new OomphTravel theme and reskin it. kadence-oomph-child is retired at the end of the rebuild. | Eric, Sep 10 |
| **D42** | First Claude Code session is scoped to stages 1-3 only, with a pull request per stage and a stop for review before any page is rebuilt. | Eric, Sep 10 |
| **D43** | Old `/group-cruises/…` addresses redirect to `https://cruiseoomph.com/cruises/` with the UTM tag — the courtesy line plan §8.4 left open. Narrows D02: the archive is still deleted here, not kept; the visitor is sent to the site that sells it instead of a 404. Everything else in D02 (cabin quiz, cruise guide, nine posts, sailings) still lands on the 404. | Eric, Sep 11 |
