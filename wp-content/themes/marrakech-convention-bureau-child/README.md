# Marrakech Convention Bureau — Brikk Child Theme

Premium child theme for the [Brikk Directory & Listing theme](https://themeforest.net/item/brikk-directory-listing-wordpress-theme/29105129)
(by Utillz). It replaces the entire **presentation layer** with the Marrakech
Convention Bureau design while preserving **every** Brikk/Routiz feature:
listings, venues, hotels, experiences, events, search, filters, maps,
taxonomies, booking, user dashboard, Elementor and SEO.

**Brikk core is never modified.** Everything happens through the child theme
system, WordPress hooks/filters, and the official Routiz template override
mechanism (`templates/routiz/`).

---

## File tree

```
marrakech-convention-bureau-child/
├── style.css                     Child theme header (Template: brikk) — no CSS here
├── functions.php                 Bootstrap: constants + module loader only
├── header.php / footer.php       Standard WP overrides (keep wp_head/wp_footer)
├── package.json                  SCSS build (npm run build / watch)
│
├── assets/
│   ├── css/main.css              Compiled, compressed output (committed)
│   ├── scss/
│   │   ├── main.scss             Entry point
│   │   ├── abstracts/            _tokens.scss ← DESIGN SYSTEM SOURCE OF TRUTH
│   │   ├── base/                 Element defaults, typography, fonts
│   │   ├── layout/               Container, grid, header, footer
│   │   ├── components/           Buttons, nav, mega menu, mobile nav, hero,
│   │   │                         cards, sections, forms (BEM, mcb- prefix)
│   │   └── vendors/              _brikk-overrides.scss + _elementor.scss
│   ├── js/main.js                Vanilla JS: drawer, mega menu, sticky header,
│   │                             counters, accordion (~2 KB gzip, deferred)
│   ├── svg/                      Inline SVG icon set (currentColor)
│   └── fonts/                    Self-hosted Marcellus + Inter (variable)
│
├── inc/
│   ├── setup/                    Theme supports, enqueue, menus, image sizes, widgets
│   ├── hooks/                    Header/footer render strategy, page hero, breadcrumbs
│   ├── filters/                  Body classes, Brikk presentation filters, performance
│   ├── helpers/                  mcb_part(), icons, Routiz data helpers, mega walker
│   ├── elementor/                Locations, category + 4 MCB widgets
│   └── woocommerce/              Woo supports (guarded; future booking use)
│
├── template-parts/
│   ├── header/                   site-header, nav-mobile
│   ├── footer/                   site-footer
│   ├── hero/                     hero-home, hero-page
│   ├── sections/                 listings, stats, testimonials, news, partners, cta, faq
│   ├── cards/                    card-listing (base) + venue/hotel/experience/event/news
│   └── components/               search-bar, section-heading, breadcrumbs, rating
│
├── templates/routiz/             Official Routiz Blade override location (see its README)
├── page-templates/
│   └── template-homepage.php     "MCB Homepage" PHP fallback + section order reference
└── languages/                    mcb textdomain translations
```

## Key architecture decisions

### 1. Three restyling layers, from safest to most powerful

| Layer | Mechanism | Use for |
|---|---|---|
| CSS re-skin | `vendors/_brikk-overrides.scss` targets Brikk's existing markup | Colors, spacing, radius, typography of Routiz archives/single/dashboard |
| Template parts + hooks | `template-parts/` rendered via `header.php`/`footer.php` overrides, `mcb/before_content` | Header, footer, hero, homepage sections |
| Routiz Blade overrides | Copy plugin template → `templates/routiz/<name>` | Structural changes to listing markup (see `templates/routiz/README.md`) |

### 2. Elementor first, PHP fallback always

Header and footer check the Elementor Pro theme-builder location first
(`mcb_render_header()` / `mcb_render_footer()`), then fall back to the PHP
parts. The homepage template renders Elementor content when present. The MCB
widgets (`MCB Hero`, `MCB Section Heading`, `MCB Stats`, `MCB Listings Grid`)
render **the same template parts** as the PHP pages — one source of markup.

### 3. Design tokens

Every color, font, radius, shadow and spacing step is a CSS custom property
defined once in `assets/scss/abstracts/_tokens.scss`. To re-skin the site
(or pixel-sync with the Claude Design export), edit that file only and run
`npm run build`.

### 4. Nothing hardcoded, everything guarded

- Listing post types are runtime-detected (`mcb_listing_post_types()`),
  overridable via the `mcb/listing_post_types` filter.
- Routiz field keys map through `mcb/listing_meta_map` (one filter, no template edits).
- Search bar prefers the Routiz search shortcode (`mcb/search_shortcode`).
- Every parent-theme touchpoint is `function_exists`/`has_action`-guarded, so
  Brikk and Routiz updates can never fatal the site.

## Theme hook & filter reference

| Hook / filter | Type | Purpose |
|---|---|---|
| `mcb/before_content`, `mcb/after_content` | action | Inject around the main content (page hero uses this) |
| `mcb/listing_post_types` | filter | Which post types are "listings" |
| `mcb/listing_kind` | filter | Map a listing to venue/hotel/experience/event card |
| `mcb/listing_meta_map` | filter | Logical field → Routiz meta key |
| `mcb/listing_query_args` | filter | Tune grid queries |
| `mcb/rating_meta_keys` | filter | Where to read review ratings |
| `mcb/search_shortcode` | filter | Routiz search shortcode for the hero |
| `mcb/header_cta` | filter | Header/mobile CTA text + URL |
| `mcb/stats`, `mcb/partners`, `mcb/faq_items` | filter | Section data sources |
| `mcb/detach_parent_hooks` | filter | Declaratively remove Brikk actions |
| `mcb/dequeue_styles`, `mcb/dequeue_scripts` | filter | Drop redundant parent assets after audit |
| `mcb/archive_per_page` | filter | Listing archive density |

## Build

```bash
npm install
npm run build    # compressed CSS + sourcemap → assets/css/main.css
npm run watch    # development
```

Docs: [Installation](../../../docs/INSTALLATION.md) ·
[Deployment](../../../docs/DEPLOYMENT.md) ·
[Content mapping](../../../docs/CONTENT-MAPPING.md) ·
[Elementor guide](../../../docs/ELEMENTOR-GUIDE.md) ·
[Design reference sync](../../../design-reference/README.md)
