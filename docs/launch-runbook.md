# Launch (plan P11) — the runbook

> Written 2026-09-12 at the start of Stage 13, from a read-only look at
> production, staging, GitHub and Search Console. Nothing in it has been run.
> The parts marked **Eric** are clicks in Site Tools, wp-admin, Instagram or
> Search Console; the parts marked **Claude** are git, GitHub Actions and
> checks over HTTP.

## What "launch" is

Two things move, by two different routes:

1. **Code** — the `oomphtravel` theme and the `oomph-travel-core` plugin — goes
   to production when `develop` is merged into `main`. The deploy copies the
   files and purges the cache; it does **not** activate the theme. On its own
   this changes nothing a visitor sees: the redirects in the plugin wait until
   the new theme is active (`class-redirects.php`).
2. **Content** — every destination, tour, operator, the four "ways" pages,
   Start planning, the menus, the SCF field groups, the media — lives only in
   the staging database. It goes to production with SiteGround's
   **Deploy Staging to Live**, which copies staging's database (and files)
   over production's. That one click is the actual launch, and it is the
   irreversible-feeling one: whatever production's database held that staging
   does not is gone afterwards.

Order: code first, push second, a few minutes apart. The push flips the
active theme (staging's database says `oomphtravel`), and the theme files are
already there because step 1 put them there.

## State on 2026-09-12

| | production (oomphtravel.com) | staging (staging2) |
|---|---|---|
| theme | `kadence-oomph-child` active | `oomphtravel` 0.8.6 active |
| plugin | 1.0.0 | 1.8.9 |
| `main` vs `develop` | 225 commits behind | — |
| GA4 (Site Kit) / Clarity | present | absent (Site Kit not connected; Clarity is production-gated) |
| robots | `index, follow` | `noindex, nofollow` (Settings → Reading, "Discourage search engines") |
| Fluent Forms | active on `/discovery-call/` | not loaded |
| Journal posts | 10 (nine cruise + `10-day-united-kingdom-itinerary`) | 0 published |
| sitemap | 21 URLs, submitted in Search Console (valid, 8 Sep) | 50 URLs incl. the stale `oomph_region` sitemap |
| pages last edited | 16 Aug (bulk), posts to 29 Aug | 11 Sep |

## 0. Go / no-go — must be true before launch day

Content on staging, all **Eric**:

- [ ] **Homepage "How to travel" cards have their four photos.** Today they are
      grey placeholders and the content guard fails on them. Plan §6.1 says
      "photo above" each card.
- [ ] **Operators and the three launch tours are published** (Globus, Tauck,
      Insight, plus their operator pages). Today every tour and operator is a
      draft, so `/escorted-tours/` opens with no tours in it.
- [ ] **`10-day-united-kingdom-itinerary` exists on staging.** It is live on
      production (published 16 Jun) and the removals button deliberately keeps
      it, yet staging returns 404 for it and has no published posts at all.
      Posts → Trash on staging first; if it is there, Restore. If not, copy
      it from production by hand (open both editors, copy the blocks, set the
      same slug and featured image). Without this the post vanishes from the
      live site while Google still lists it.

Before production loses anything, all **Eric** unless noted:

- [ ] **The nine cruise posts are imported into CruiseOomph while production
      still serves them.** Staging no longer has them; after the push neither
      will production. As of today none of the nine exist on cruiseoomph.com.
      The import reads them from oomphtravel.com's REST API — see
      `docs/stage-2-runbook.md` step 1 (Claude can run the CruiseOomph side once
      its command or button exists there).
- [ ] **Export Fluent Forms entries from production** (`docs/stage-2-runbook.md`
      step 2). The push replaces production's database, Discovery Call entries
      included.
- [ ] **Anything added on production since staging was copied** is re-done on
      staging: a photo uploaded to the live Media Library, an edit to About or
      Client stories. As far as the public site shows, nothing on production
      has changed since 29 Aug except the cruise posts, but only wp-admin can
      confirm the Media Library.

Decisions that are not blockers but are easiest before the push:

- [ ] `/services/` — a published page on staging that is not in the handoff. Delete or keep?
- [ ] The old cruise region and trip-style terms and the itinerary records (hidden since Stage 12; deleting is a decision).
- [ ] Rank Math → Redirections on staging: delete rows that repeat the code's four moves (`docs/stage-2-runbook.md` step 5).

Code, **Claude**: the live suite green against staging with the content guard
clean, once the three content items above are done.

## 1. The day before

1. **Eric** — stop editing production wp-admin from now until after the push.
2. **Eric** — Site Tools → Security → Backups → **Create** on the live site.
   Name it "pre-launch". (SiteGround also offers a backup in the staging
   deploy dialog; take this one regardless.)
3. **Claude** — run the live suite and the audits against staging one last time:
   `npx playwright test`, `npm run audit:a11y`, `npm run audit:lh` with
   `OOMPH_BASE_URL=https://staging2.oomphtravel.com`.

## 2. Launch day, in order (about 30 minutes, together)

**A. Code to production — Claude**

```bash
gh pr create --repo sonicgoo-Dev/oomph-site --base main --head develop --title "Launch: the OomphTravel rebuild (plan P11)"
gh pr merge <n> --repo sonicgoo-Dev/oomph-site --squash
gh run watch <deploy run id> --repo sonicgoo-Dev/oomph-site --exit-status
```

Check: the old site still serves normally (`/`, `/discovery-call/` 200, no
redirect yet). The new files are on the server; nothing is active.

**B. The push — Eric, Site Tools**

Site Tools → WordPress → Staging → find `staging2` → **Deploy** → choose
**Full** (files and database). Confirm. Wait for the green tick; it can take a
few minutes. SiteGround rewrites `staging2.oomphtravel.com` to
`oomphtravel.com` throughout the database as part of this.

Why Full and not database-only: the theme and plugin files on staging are the
same commit `main` now holds, and the next code deploy overwrites those two
folders anyway. Full also carries the Media Library uploads staging has that
production does not (the destination heroes, Amy's portrait).

**C. Settings that come across from staging and must be reversed — Eric, production wp-admin**

1. Settings → Reading → untick **Discourage search engines from indexing this site** → Save. Until this is done every page is `noindex`.
2. Settings → Permalinks → **Save Changes** (nothing to change; this rebuilds the URL rules).
3. Site Kit → if the dashboard says it needs reconnecting, click through the Google sign-in for the same account as before. GA4 and Search Console come back with it. (Staging never carried Site Kit's connection, so expect this.)
4. Rank Math → General Settings → check the site still shows as connected to your Rank Math account; Rank Math → Analytics → reconnect Search Console if asked.
5. Speed Optimizer → **Purge SG Cache** (the deploy already did, but the push came after it).

**D. Smoke test — Claude, over HTTP, within minutes of C**

- All fourteen page types return 200 with `index, follow`, a self-canonical, one H1, the JSON-LD types from `tests/e2e/fixtures/routes.ts`.
- `gtag/js` (Site Kit) and `clarity.ms` present on the homepage.
- `/discovery-call/` → 301 → `/start-planning/`; `/custom-italy-travel/` → `/destinations/italy/`; `/luxury-cruise-planning/` → `/cruise-planning/`; `/group-cruises/anything/` → cruiseoomph.com/cruises/ with the UTM tag.
- `/trip-quiz/`, `/cruise-travel-trends/`, a cruise post slug → the theme's 404 page.
- `/sitemap_index.xml` lists the page and destination sitemaps only.
- Then the suites: `gh workflow run e2e.yml --repo sonicgoo-Dev/oomph-site -f target=production` (runs from Eric's PC), `npm run audit:a11y`, `npm run audit:lh` (both default to production).

**E. The two real sends — Eric**

1. `/start-planning/` → fill it in as yourself → send. Expect the receipt page with Calendly, the email at hello@, and the record under Inquiries in wp-admin.
2. Footer newsletter → your own address → expect PlainSend's confirmation. (Now `Environment` reads production, so this hits the real list, not `newsletter-staging`.)
3. Clarity → the project shows a live session; GA4 → Realtime shows one user.

Only after E1 works: Plugins → **Deactivate** Fluent Forms (D31). Leave it installed a week, then delete.

**F. Search Console — Eric (or Claude with the Search Console tool)**

Sitemaps → `sitemap_index.xml` is already submitted; open it and choose
resubmit. URL inspection → `https://oomphtravel.com/` → Request indexing; same
for `/destinations/italy/` and `/start-planning/`. Keep the property; watch
Pages → "Not found (404)" for a month — the sixteen old cruise addresses will
appear there and that is expected (D02).

**G. Instagram — Eric**

Instagram app → Edit profile → Links → set the website to
`https://oomphtravel.com/links/`.

**H. CruiseOomph points back — Claude, separate PR in `luxury-cruise-companion`**

Its header falls back to four links (Find a cruise, Cruise styles, Journal,
Meet Eric) in `patterns/header.php`, and uses the `primary` menu when one is
saved in wp-admin. Add one item linking to `https://oomphtravel.com/` with the
label Eric chooses ("Land trips" / "Beyond cruises" / "Oomph Travel"), then
the same item in the saved menu on CruiseOomph if one exists. This is the
reverse of its plan's step 11.5.

## 3. If something is wrong

- **A page is broken but the site is up:** fix forward on `develop`, merge to `main`; the deploy takes about two minutes.
- **The push itself went wrong:** Site Tools → Security → Backups → the "pre-launch" backup → **Restore** (database, or files and database). That returns the old site in full. Then `git revert` the launch merge on `main` so the code deploy matches, and start again another day.

## 4. The week after

- Watch Search Console → Core Web Vitals and Pages weekly; lab Lighthouse only as a tie-breaker (plan 8.6).
- Delete Fluent Forms; deactivate and, after a clean week, delete ACF Pro and cancel its licence (SCF replaced it in Stage 4).
- Retire `kadence-oomph-child`: delete the theme in wp-admin, then a PR removes it from the repo and from `deploy.yml`.
- Delete the old region and trip-style terms and the itinerary records if Eric decides so; the Rank Math redirect duplicates likewise.
- Publish the launch Journal articles as they are ready; the homepage shows the Journal once three are live.
