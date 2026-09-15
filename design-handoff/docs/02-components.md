# Component inventory — 21 components

Exported from Figma page `03 Components` on 10 September 2026. Every fill and stroke in these is bound to a token in `tokens/tokens.css`; nothing carries a hardcoded hex.

Sizes are the Figma frame size, not a hard constraint — they tell you the intended proportions.

## Foundations

### Logo / Symbol · 487 × 518
Eric's luggage symbol (D22). Vector redraw of his own artwork. Fills bound to `logo/teal`, `logo/coral`, `logo/ink`, `surface/white`. **Aspect 487:518 — scale by width and let height follow.** Used alone as favicon, social avatar and the 404 mark; beside the Fraunces wordmark in the header. The coral appears nowhere else on the site.

### Button · variant set, 4 variants
`Style=Primary|Ghost` × `Ground=Light|Navy`. Text property: `Label`.
Primary is a teal fill with a white label. Ghost is a 1px stroke. `Ground=Navy` is for dark bands, where the stroke and label lighten to `accent/teal-on-navy`.
**One primary button per section, never two.** The arrow nudges 4px right on hover (D23).

### Eyebrow · 434 × 14
Inter Semi Bold 12, all caps, +0.12em, `accent/teal`. On dark bands swap the fill to `accent/teal-on-navy`. Text property: `Text`.

### Section heading · 354 × 72
The eyebrow with its static contrail mark, then the Fraunces H2. The contrail is the still form of the motion motif (D23) and appears once per section. Properties: `Eyebrow text`, `Heading text`.

### Link / Underlined · 129 × 18
Ink label on a 1px teal underline. The tertiary action — used where a section needs an exit but not a button. Property: `Text`.

### Pill / On photo · 114 × 36
White pill for use over a photograph, where a teal button would fight the image. Card CTAs only. Property: `Label`.

### Credential strip · 1440 × 50
Mist-deep strip, muted-slate caps. On the homepage the place-name ticker takes this position and the credentials move into the advisor block (D23); this component stays for inner pages.

## Cards

### Card / Destination · 280 × 400
Tall 4:5 portrait. Name above the pill, both bottom-left, over a gradient scrim so type stays legible whatever the image. Lifts 6px and the photo eases to 105% on hover (D23). Property: `Name`.

### Card / Way to travel · 300 × 537
Photo above, Fraunces title, two lines of body, one underlined link. Used in the four-up "How to travel" row and anywhere a service needs a door. Properties: `Title`, `Body`.

### Card / Tour · 400 × 552
One Tour record. Meta line is operator · nights · destination. The price is always "from", per person.
**The card carries a fixed caption reading "Dates and availability confirmed on request." — this is part of the component, not per-instance copy, and it must survive into the block (D34).**
The Copy block inside is a fixed 178px so price rows bottom-align across a row. "Ask Eric" opens Start planning with the tour pre-filled. Properties: `Meta`, `Title`, `Blurb`, `Price`.

### Card / Operator · variant set, 2 variants
`Kind=Escorted|FIT supplier`. Escorted shows a tour count and links to the operator page; FIT supplier shows the trip types it supports and links to Custom journeys or Resorts & villas. Seven at launch: five escorted, two FIT (D25).

### Card / Journal · 360 × 428
Category eyebrow in **muted slate, not teal** — teal is reserved for actions, and a category is not one. Properties: `Category`, `Title`, plus a Blurb text node.

## Chrome

### Header / Desktop · variant set, 2 variants
`State=Default|Scrolled`. Thin white bar, symbol + Fraunces wordmark left, four nav items and one teal button right. Scrolled drops to 64px, shrinks the symbol and wordmark, trades the hairline for a soft shadow. No utility bar and no phone number — those live in the footer.
Nav: Destinations ▾ · Ways to travel ▾ · Journal · About · [Start planning →]

### Header / Mobile · 390 × 64
Symbol and wordmark left; a plain "Start planning" text link and the menu button right. The teal button is dropped at this width — a full-width button in a 64px bar crowds the wordmark.

### Footer · 1440 × 714
Deep navy. The signup row posts straight to PlainSend (D30) — email, honeypot and timing field in the build; double opt-in means a confirmation email follows. Four columns, then the OomphTravel / CruiseOomph family lockup.
**Destinations column now includes Africa**, between "Croatia & the Adriatic" and "Hawaii · Mexico · Caribbean".

## Bands

### Band / Statement · 1440 × 548
Mist ground, centred, one idea. Heading measure capped at 820, body at 720. **Ghost button, never primary** — the primary belongs to the hero and the closing band.

### Band / Process · 1440 × 508
Discover, Design, Depart. Mist-deep ground so it separates from the Mist statement band. Numerals are Fraunces Light in teal — the only place a numeral carries the accent.
Each step has three named text nodes: `Number`, `Title`, `Body`. **Address them by name, never by sibling index.**

### Band / Ticker · 1440 × 60
Homepage only. Place names separated by a small outlined plane, one loop per 70 seconds, pausing on hover (D23). Takes the position the credential strip used to hold.

### Quotation · 560 × 76
A client story. Fraunces Light Italic on a 2px teal rule, attribution in muted slate. **No star ratings and no photographs of strangers** — the words carry it. Properties: `Quote`, `Attribution`.

### Band / CruiseOomph · 1440 × 499
The hand-off to the sister site. Marine navy. Two actions plus one tertiary link to the Cruise planning page. **Every outbound link carries the UTM tag** `?utm_source=oomphtravel&utm_medium=site&utm_campaign={page}` so CruiseOomph can see what this site sends.

### Band / Closing invitation · 1440 × 503
Every page ends here. Marine navy, one question, one button. The only other primary button on a page is in the hero.

---

## Two things that bite

**Arrows.** Fraunces has no arrow glyphs. Any `→ ← ↗` must be set in Inter or it falls back to emoji presentation. The Button component keeps its arrow in a separate node for exactly this reason.

**The tour card height.** `Card / Tour` is 552px, not 515 — it grew when the availability caption was added. Its `Body` frame hugs vertically and its `Copy` frame is fixed at 178px. Change either and the price rows stop bottom-aligning across a row.
