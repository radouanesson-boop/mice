# Installation guide

## Prerequisites

| Requirement | Version |
|---|---|
| WordPress | 6.4+ |
| PHP | 7.4+ (8.1+ recommended) |
| Brikk theme (licensed) | 3.x |
| Routiz plugin (bundled with Brikk) | matching Brikk version |
| Elementor | latest (Pro recommended for header/footer builder) |

## 1. Install the parent stack

1. Install and activate the **Brikk** theme from your ThemeForest purchase.
2. Install the **Routiz** core plugin when prompted (Appearance → Install Plugins) —
   it provides listings, search, filters, maps, booking and the dashboard.
3. Install **Elementor** (and Elementor Pro if available).
4. Run Brikk's demo import once if you want starter listing types
   (venues/hotels/experiences/events can be created manually too).

## 2. Install the child theme

1. Copy `wp-content/themes/marrakech-convention-bureau-child/` from this
   repository into your site's `wp-content/themes/` (or zip it and upload via
   Appearance → Themes → Add New → Upload).
   > The committed `assets/css/main.css` is production-ready — Node is NOT
   > required on the server.
2. Activate **Marrakech Convention Bureau — Brikk Child**.
3. Verify the site loads with the parent's functionality intact (search,
   listing archives, dashboard).

## 3. Configure Brikk theme options

Appearance → Brikk options:

- **Colors**: set the primary color to `#284236` (Deep Green) and secondary to
  `#7d985b` (Sage) so plugin-rendered UI matches the design tokens
  (design board 1D · Colour).
- Disable Brikk's Google Fonts if it loads any (the child self-hosts
  Jost + Newsreader italic) — or list the parent's font style handle in the
  `mcb/dequeue_styles` filter.

## 4. Menus (Appearance → Menus)

| Location | Content |
|---|---|
| MCB — Primary | Main nav. Add CSS class `mcb-mega` to any top-level item to turn its submenu into a mega panel (2nd level = column headings, 3rd level = links; the "Description" field renders as link subtitles). |
| MCB — Mobile drawer | Optional; falls back to Primary |
| MCB — Top bar quick links | Contact / press / language links |
| MCB — Footer column 1–3 | Footer nav columns |
| MCB — Footer legal bar | Privacy, terms, cookies |

Enable "CSS Classes" and "Description" under Screen Options in the menu editor.

## 5. Homepage

Option A (recommended): create a page, edit with Elementor, compose with the
**Marrakech CB** widget category (Hero → Stats → Section Heading + Listings
Grid × 4 → CTA). Set it as the static front page.

Option B: create a page, assign the **MCB Homepage** page template, set as
front page. Sections render dynamically from published listings.

## 6. Site identity & widgets

- Logo: Customizer → Site Identity (SVG or PNG, ~48 px tall).
- Header CTA: Customizer theme mods `mcb_header_cta_text` / `mcb_header_cta_url`,
  or the `mcb/header_cta` filter in a small mu-plugin.
- Footer: fill the **MCB Footer** widget areas (brand column + 3 columns +
  pre-footer CTA strip).

## 7. Content mapping

Follow [CONTENT-MAPPING.md](CONTENT-MAPPING.md) to map your Routiz listing
fields (capacity, rooms, stars, duration, event date…) to the card
components — one filter, no template edits.

## 8. Routiz template overrides (optional, structural changes only)

See `wp-content/themes/marrakech-convention-bureau-child/templates/routiz/README.md`.
Remember: after adding/editing an override, enable `WP_DEBUG` once to flush
Routiz's compiled Blade cache.
