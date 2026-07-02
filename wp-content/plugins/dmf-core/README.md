# DMF Core — Destination Management Framework

A from-scratch, modular WordPress framework for **Destination Marketing
Organizations, Convention Bureaus and Tourism Boards** — an operating
system for destinations, not a directory theme.

First implementation: **Marrakech Convention Bureau** (Visit Marrakech theme).

## What's inside (v0.1)

| Module | What it does |
|---|---|
| Core | Capabilities (`dmf_listing` set), Destination Partner role |
| Listings | Unlimited **config-driven listing types** (CPTs + shared taxonomies + typed field groups with sanitization, REST-registered meta), theme-overridable templates |
| Search | `GET /dmf/v1/search` — keyword, taxonomy & meta-range filters, facets, radius (haversine), lean card payloads |
| Maps | Provider-agnostic (`osm`/`google`/`mapbox`), `GET /dmf/v1/geojson` FeatureCollection, `window.dmfMaps` config |
| Events | Chronological archives, upcoming feed `GET /dmf/v1/events/upcoming` |
| Members | Partner profiles + `GET /dmf/v1/me` dashboard payload |
| Favorites | `POST /dmf/v1/favorites/{id}` toggle, user-meta storage |
| Reviews | Comment-type reviews with 1–5 ratings, denormalized averages |
| Forms | RFP/inquiry pipeline: private CPT + email, nonce + honeypot + rate limit |
| SEO | JSON-LD per listing kind, OpenGraph fallback, breadcrumb API — defers to Yoast/RankMath |
| API | `GET/POST /dmf/v1/types` (no-code types over REST), `GET /dmf/v1/status` |
| Blocks | `dmf/listings-grid`, `dmf/search-bar`, `dmf/inquiry-form`, `dmf/events-list` — server-rendered from the same templates as the front end |
| Shortcodes | Classic-editor parity for all blocks |
| Admin | Destination menu: dashboard, listing type manager (no-code), settings, per-type screens |
| I18n | `dmf` textdomain, Polylang/WPML awareness, RTL body class |
| Performance | No front-end JS from core, no jQuery, emoji removal, query hygiene |
| CLI | `wp dmf types`, `wp dmf seed`, `wp dmf export-types` |

## Architecture in one paragraph

`Plugin` (kernel) boots a filterable map of `Module` classes through a tiny
DI `Container`. Each module binds hooks in `register()`, wires cross-module
concerns in `booted()`, installs idempotently in `install()`. Listing types
are **pure configuration** (`config/listing-types.php` → option → filter),
never code. Front-end markup lives in `templates/` and any file can be
overridden at `{theme}/dmf/{name}.php`. The public API is the procedural
layer in `src/functions.php` (`dmf()`, `dmf_template()`, `dmf_get_field()`,
`dmf_listing_query()`…) plus the documented hooks.

Full documentation: see `/docs/framework/` in the repository root.
