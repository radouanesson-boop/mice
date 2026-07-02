# Design reference — Claude Design handoff

The visual source of truth, exported from Claude Design and committed here:

| File | What it is |
|---|---|
| `Marrakech Convention Bureau.dc.html` | The design file — 4 boards: **1A** Homepage "Editorial Luxe" (implemented), 1B Structured alternative, **1C** Mobile concept (implemented as the responsive behaviour), **1D** Design system (source of the theme tokens) |
| `support.js` | Claude Design runtime + the design's EN/FR content data |
| `IDENTITE-VISUELLE.pdf` | Visit Marrakech · Bahja Spirit brand identity |

Original share link:
https://claude.ai/design/p/56affde8-2e1a-4ca4-af43-fb3642776490?file=Marrakech+Convention+Bureau.dc.html&via=share

## How the design maps into the theme

| Design (board) | Theme implementation |
|---|---|
| 1D · 02 Colour (Deep Green #284236, Sage #7D985B, Ochre #B9793A, Ink #22321F, Paper #FAF9F4, Tint #EEF1E8, Line #E8E7DE…) | `assets/scss/abstracts/_tokens.scss` |
| 1D · 03 Typography (Jost 300/400/500/600 + Newsreader italic accent) | `assets/scss/base/_typography.scss` + self-hosted variable fonts in `assets/fonts/` |
| 1D · 04 Components (pill buttons, hairline cards, chips, inputs, stats) | `components/_buttons.scss`, `_cards.scss`, `_sections.scss`, `_forms.scss` |
| 1A Nav + mega menu | `template-parts/header/site-header.php`, `components/_nav.scss`, mega walker |
| 1A Hero (split, frosted stats card) | `template-parts/hero/hero-home.php`, `components/_hero.scss`, MCB Hero widget |
| 1A Trust strip | `template-parts/sections/section-trust.php` (`mcb/trust_items` filter) |
| 1A Why Marrakech + strengths | `sections/section-why.php` (`mcb/strengths`, `mcb/why_chips`) |
| 1A Convention Bureau services | `sections/section-services.php` (`mcb/services`) |
| 1A Venue cards | `cards/card-venue.php` + `card-listing.php` |
| 1A Experience overlay cards | `cards/card-experience.php` |
| 1A Success stats + pull-quote | `sections/section-stats.php` + `section-testimonials.php`, MCB Stats widget |
| 1A Insights cards | `sections/section-news.php` + `cards/card-news.php` |
| 1A Partners strip | `sections/section-partners.php` (`mcb/partners`) |
| 1A Final CTA | `sections/section-cta.php` |
| 1A Footer (stone, 4 cols, newsletter) | `template-parts/footer/site-footer.php`, `layout/_footer.scss` |
| 1C Mobile (full-screen green nav, stacked hero) | `components/_mobile-nav.scss`, hero/nav responsive rules |

## Intentional adaptations (prototype → production)

- The hero "positioning concepts" pill picker (boards control, 8 headline
  variants) is a proposal-review tool, not a production element. The default
  headline is concept 01 ("Where the world meets *Marrakech*"); all 8 are
  available by editing the MCB Hero widget fields.
- The mega menu's promo image card is not generated from the WP menu; add it
  later via a Routiz/Elementor template override if desired.
- Board 1B (Structured direction) is not implemented — 1A is the chosen full
  homepage; 1B's RFP toolkit can be built as an Elementor form section reusing
  the existing input/button styles.
- Unsplash imagery in the prototype is placeholder; upload real photography
  (see board 1D · 06 do/avoid guidance) via the Customizer/Elementor controls.
