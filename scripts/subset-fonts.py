#!/usr/bin/env python3
"""
One-off font subsetter for the oomphtravel theme.

Produces the six WOFF2 files in wp-content/themes/oomphtravel/assets/fonts/
from the official Google Fonts variable TTFs. Run once when a font is
updated; the outputs are committed. Nothing at build or runtime calls this.

    python -m pip install fonttools brotli
    python scripts/subset-fonts.py <dir containing Fraunces-VF.ttf,
                                    Fraunces-Italic-VF.ttf, Inter-VF.ttf>

Sources (SIL OFL): github.com/google/fonts/tree/main/ofl/{fraunces,inter}
  Fraunces[SOFT,WONK,opsz,wght].ttf, Fraunces-Italic[...].ttf, Inter[opsz,wght].ttf

What is kept, and why (design-handoff/tokens/type.css):
  Fraunces  wght 300-400 (Light + Regular are the only weights used),
            opsz 9-144 kept as a live axis so the hero can sit at 144,
            SOFT pinned 0 and WONK pinned 1 (the Figma setting CruiseOomph
            applies via font-variation-settings; pinning it in the file is
            smaller and means the CSS only has to speak about opsz).
  Inter     wght 400-600 (body 400, button/nav 500, eyebrow 600),
            opsz pinned at its 14 default (no style sets Inter above 18px).
  Ranges    Google Fonts' latin and latin-ext splits, matching the
            unicodeRange declarations in theme.json, so a page in plain
            English never downloads the -ext files.
"""
import subprocess
import sys
from pathlib import Path

LATIN = "U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD"
LATIN_EXT = "U+0100-02BA,U+02BD-02C5,U+02C7-02CC,U+02CE-02D7,U+02DD-02FF,U+0304,U+0308,U+0329,U+1D00-1DBF,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0,U+2113,U+2C60-2C7F,U+A720-A7FF"

OUT = Path(__file__).resolve().parent.parent / "wp-content/themes/oomphtravel/assets/fonts"

JOBS = [
    # (source, output stem, fontTools instancer axis limits)
    ("Fraunces-VF.ttf",        "fraunces",        "wght=300:400 opsz=9:144 SOFT=0 WONK=1"),
    ("Fraunces-Italic-VF.ttf", "fraunces-italic", "wght=300:400 opsz=9:144 SOFT=0 WONK=1"),
    ("Inter-VF.ttf",           "inter",           "wght=400:600 opsz=14"),
]


def run(*args):
    print("  $", " ".join(str(a) for a in args))
    subprocess.run([str(a) for a in args], check=True)


def main(src_dir: str) -> None:
    src = Path(src_dir)
    OUT.mkdir(parents=True, exist_ok=True)
    for source, stem, axes in JOBS:
        partial = src / f"{stem}-partial.ttf"
        # 1. Restrict the variable axes first (fontTools.varLib.instancer).
        run(sys.executable, "-m", "fontTools.varLib.instancer", src / source, *axes.split(), "-o", partial)
        # 2. Then subset by unicode range into two WOFF2 files.
        for suffix, ranges in (("latin", LATIN), ("latin-ext", LATIN_EXT)):
            run(
                sys.executable, "-m", "fontTools.subset", partial,
                f"--unicodes={ranges}",
                "--flavor=woff2",
                "--layout-features=*",      # keep kerning, ligatures, numerals
                "--no-hinting",
                "--desubroutinize",
                f"--output-file={OUT / f'{stem}-{suffix}.woff2'}",
            )
        partial.unlink()
    for f in sorted(OUT.glob("*.woff2")):
        print(f"{f.stat().st_size:>8,d}  {f.name}")


if __name__ == "__main__":
    if len(sys.argv) != 2:
        sys.exit(__doc__)
    main(sys.argv[1])
