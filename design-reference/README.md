# Design reference — Claude Design export

The visual source of truth for this project is the Claude Design prototype:

> https://claude.ai/design/p/56affde8-2e1a-4ca4-af43-fb3642776490?file=Marrakech+Convention+Bureau.dc.html&via=share

⚠ **That share link requires an interactive claude.ai login and could not be
fetched from the build environment.** The theme was therefore built on a
token-driven design system with a Marrakech-appropriate premium palette
(terracotta / Majorelle blue / saffron gold / sand — see
`assets/scss/abstracts/_tokens.scss`).

## To pixel-sync the theme with the prototype

1. In Claude Design, export/download **`Marrakech Convention Bureau.dc.html`**
   and commit it into this folder.
2. Extract the real values and update **one file** —
   `wp-content/themes/marrakech-convention-bureau-child/assets/scss/abstracts/_tokens.scss`:
   - color palette (primary/secondary/accent/backgrounds/ink),
   - font families (+ replace the woff2 files in `assets/fonts/` if the
     prototype uses different faces),
   - radii, shadows, container width, section spacing.
3. Compare component structure (hero, cards, stats, CTA, footer) against the
   template parts in `template-parts/` and adjust markup/CSS where the
   prototype differs.
4. `npm run build` and review every breakpoint (360 / 640 / 768 / 1024 /
   1280 / 1536).

Because every component consumes the tokens, step 2 alone typically gets the
site ~90% of the way to the prototype.
