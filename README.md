# Oomph Travel

The WordPress site for oomphtravel.com, Eric Hempel's travel advisory, hosted on SiteGround.

The September 2026 rebuild replaces the Kadence child theme with a block theme. `design-handoff/` is the authority for design and decisions, and `CLAUDE.md` holds the working rules.

| Path | What it is |
|---|---|
| `wp-content/themes/oomphtravel/` | The block theme: presentation, patterns, templates |
| `wp-content/plugins/oomph-travel-core/` | Data layer: record types, fields, schema, redirects, WP-CLI commands |
| `wp-content/themes/kadence-oomph-child/` | The old theme, retired after launch |
| `content/tours/` | Tour records the "Add a tour" button imports |
| `tests/` | Playwright: `ci/` runs in GitHub Actions, `e2e/` runs against a live site |
| `docs/launch-runbook.md` | The launch plan |
| `NEXT-SESSION.md` | Current state and what is open |

## Deploys

- A push to `develop` deploys to staging at `staging2.oomphtravel.com`.
- A pull request from `develop` into `main`, squash-merged, deploys to production.
- The deploy copies the theme and plugin files only. Content moves from staging to production through SiteGround's Deploy Staging to Live.

## Checks

```bash
npm ci
npx playwright test
```

The live suite defaults to staging. Set `OOMPH_BASE_URL` to point it elsewhere. See `docs/testing.md`.
