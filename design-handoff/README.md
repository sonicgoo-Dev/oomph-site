# design-handoff

Everything needed to build the new OomphTravel.com, exported from the approved Figma prototype and the resurfacing plan on **10 September 2026**.

Commit this whole folder to the repo. It is self-contained — nothing here requires a Figma connection or access to a claude.ai project.

```
design-handoff/
├── KICKOFF.md                      the prompt to paste into Claude Code
├── tokens/
│   ├── tokens.css                  18 colours + 13 spacing/radius/size vars
│   ├── type.css                    22 text styles
│   └── tokens.json                 the same, machine-readable
├── docs/
│   ├── 01-decisions-log.md         D01–D42
│   ├── 02-components.md            the 21 components
│   └── 03-rules-and-readiness.md   rules, placeholders, what's missing
└── OomphTravel-Resurfacing-Plan.docx    the full plan
```

## Read order

1. `docs/03-rules-and-readiness.md`
2. `docs/01-decisions-log.md`
3. `docs/02-components.md`
4. `tokens/`
5. The plan — sections 6, 8.1 and 8.5

## The Figma file

"OomphTravel Redesign", file key `xFlJbvIRQ0S9hrhZWCbvQs` — seven pages, 20 desktop screens, 3 mobile, 21 components, 31 variables, 22 text styles.

It is the **visual reference for humans**. It is not a build dependency. The tokens here were exported from it and verified, so if a tool ever shows you a Figma file that disagrees with these files, the tool is wrong.

## If a value here looks wrong

Fix it in Figma, re-export, and commit the new files. Do not hand-edit a hex in `tokens.css` — the next export will silently overwrite it.
