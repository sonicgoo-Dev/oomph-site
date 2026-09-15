# OomphTravel rebuild — Claude Code kickoff (v2)

Paste everything below the line into a new Claude Code session, from the repo root, after the `design-handoff/` folder is committed.

---

You are rebuilding **OomphTravel.com**. A full design prototype already exists and is approved. Your job in this session is the first three stages of the build only, and then to stop for review.

## Where you are

- **This repo:** `sonicgoo-Dev/oomph-site` — the OomphTravel WordPress site.
- **Work on `develop`, not `main`.** As of 10 September, `develop` is **140 commits ahead of `main`** and 9 behind. Its checks pass; `main`'s last check is failing. Do not try to reconcile that in this session — note it and carry on. There are no open pull requests; the leftover `claude/*` branches are from closed ones.
- **The CruiseOomph repo you are forking from** is cloned beside this one at `../cruiseoomph`. If you cannot read it from here, ask to be restarted one directory up.

### One discrepancy to resolve before Stage 2

This repo's README says the theme is `wp-content/themes/kadence-oomph-child/` and that local dev runs at `~/Local Sites/oomph-local/`. On Eric's machine the Local site is at `~/Local Sites/oomph-travel/` and its active theme folder is called `oomph-travel`, not `kadence-oomph-child`.

Either the README is stale or the local install has drifted. **Work out which is actually running before you retire anything** — retiring the wrong theme is not a recoverable mistake on a live site. Report what you find.

## Everything you need is in this repo

Read `design-handoff/` first, in this order:

1. `design-handoff/docs/03-rules-and-readiness.md` — the rules, what is placeholder, what is still missing. **Read this before anything else.**
2. `design-handoff/docs/01-decisions-log.md` — D01 to D43, every decision and why.
3. `design-handoff/docs/02-components.md` — the 21 components, with the two details that are easy to lose.
4. `design-handoff/tokens/` — `tokens.css`, `type.css`, `tokens.json`. **These are the source of truth for colour and type.** They were exported from Figma and verified; do not re-derive them from anywhere else.
5. `design-handoff/OomphTravel-Resurfacing-Plan.docx` — the full plan. Sections 6 (page specs), 8.1 (data model) and 8.5 (build phases) are the ones you will keep coming back to.

Do not go looking for a Figma file or a claude.ai project. The Figma file exists and is complete — file key `xFlJbvIRQ0S9hrhZWCbvQs`, seven pages — but it is a **reference for humans**, not a dependency for you. If your Figma connection shows you fewer than seven pages, it is showing you a stale view; ignore it and use the exported files, which are authoritative. Everything you need for stages 1 to 3 is in this repo.

Then read the current codebase, and the CruiseOomph codebase, because you are going to fork it.

## What this site is now

OomphTravel is being repositioned away from cruise. It now sells **custom journeys (FIT)**, **escorted tours** through five operators, and **resorts and villas**. Cruise sells at CruiseOomph.com, a separate live site, and every cruise route on OomphTravel hands off to it.

One advisor, Eric Hempel, writing in the first person. The copy names operators, admits what does not suit, and points people elsewhere when that is the honest answer. That candour is the positioning, not a stylistic quirk — preserve it exactly as written.

## The theme decision (made, not open)

**Fork the CruiseOomph theme into a new OomphTravel theme and reskin it** (D41). Do not keep evolving `kadence-oomph-child`; it is retired at the end of this rebuild.

The two sites are meant to read as siblings — shared bones, different mood. CruiseOomph already solved the layout system, header and footer structure, card patterns and form handling, and its fixes should carry across for free.

Before you copy anything, read the CruiseOomph theme properly and report back: what carries over cleanly, what is cruise-specific and must be stripped, and anything you would do differently. Then proceed. Note the one real risk recorded in the plan (§8.2): the `oomph-travel-core` plugin must be reviewed for theme dependencies before the switch.

## Scope for this session — stages 1 to 3 only

Nothing visitor-facing changes shape in this session. Open a pull request at the end of each stage.

### Stage 1 — Tokens and type

Port `design-handoff/tokens/tokens.css` and `type.css` into the new theme's `theme.json` and stylesheet.

- **18 colour variables** and **13 spacing / radius / size variables**. Keep the Figma naming structure (`navy/marine`, `text/ink`, `accent/teal`, `surface/mist`).
- **22 text styles** across `Desktop/` and `Mobile/` prefixes.
- Self-host **Fraunces** and **Inter** as subsets. Font subsetting already shipped on the old site — carry the approach over. Fraunces Display uses optical size 144 for the hero only.
- Nothing in the built theme should carry a hardcoded hex. If you find yourself typing a `#`, you are missing a token.

### Stage 2 — Deletions and Fluent Forms

- Delete the group cruise pages and all sailing records, the cabin quiz, `/cruise-travel-trends/`, and the nine cruise journal posts. **Delete, do not redirect** (D02) — roughly 20 Google clicks in 90 days, no equity worth protecting. One courtesy exception (D43): `/group-cruises/…` redirects to cruiseoomph.com/cruises/.
- **Copy the nine cruise posts to CruiseOomph before deleting them here.** Its build plan expects to import them.
- Keep three internal redirects, which are not cruise pages: `/custom-italy-travel/` → `/destinations/italy/`, `/luxury-cruise-planning/` → `/cruise-planning/`, `/discovery-call/` → `/start-planning/`.
- Once the last form is gone, **remove Fluent Forms entirely** (D31). It is 43 KB of CSS loading on every page including pages with no form, and it is the single biggest performance win available on this site.
- Also remove the Distinctive Voyages importer and its scheduled sync, and the ship library records if nothing else uses them.
- Check no required plugin has been silently deactivated. One was off for five weeks before anyone noticed.

### Stage 3 — Components

Build the 21 components in `docs/02-components.md` as blocks or template parts, bound to the Stage 1 tokens.

Two details that carry meaning and are easy to lose:

- **`Card / Tour` includes a caption reading "Dates and availability confirmed on request."** It is part of the component, not per-instance copy.
- **Fraunces has no arrow glyphs.** Any `→ ← ↗` set in Fraunces falls back to emoji presentation. Keep arrows in Inter.

### Then stop

Do not start Stage 4. Open the pull requests, summarise what you did and what surprised you, and wait.

## How to work

- **Branch off `develop`.** One pull request per stage.
- **Do not touch production.** SiteGround staging shares SSH credentials with production, so treat staging changes with production caution.
- **Eric opens the pull requests.** Claude's GitHub identity is not a repo collaborator.
- **Measure real-visitor Core Web Vitals, not lab scores.** Standing decision.
- There is a **latent seeder ordering bug** that skips SEO metadata on a brand-new page slug. It has not bitten yet and it will, the next time a page is created. Fix it if you are in that code anyway; do not go hunting for it this session.

## Ask before you do any of these

- Deleting anything not on the Stage 2 list
- Changing a decision in the decisions log
- Adding a plugin, a dependency or a build step
- Anything that touches the live site
- Anything where the honest answer is "the handoff doesn't say"

Guessing and moving on is the one thing that will cost real time here. Ask.
