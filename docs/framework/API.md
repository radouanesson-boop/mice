# DMF — Developer API reference

## Procedural API (stable)

```php
dmf(): DMF\Plugin                          // kernel; dmf()->module('listings')
dmf_template( string $name, array $args )  // render theme-overridable template
dmf_get_field( string $key, ?int $id )     // read a listing field (_dmf_{key})
dmf_listing_types(): string[]              // active listing post types
dmf_listing_kind( ?int $id ): string       // venue|hotel|restaurant|experience|event|supplier|generic
dmf_get_card_fields( int $id ): array      // the 2-3 card meta rows for a listing
dmf_listing_query( array $args ): WP_Query // grids/sections query
dmf_breadcrumbs(): array                   // schema-ready trail [{name,url}]
```

## REST API — `dmf/v1`

### `GET /search` (public)
Params: `q`, `type` (csv), any taxonomy (`dmf_category=congress`),
`meta[capacity][min|max|is]`, `near=lat,lng`, `radius` (km),
`orderby=relevance|date|title|distance`, `page`, `per_page`, `facets=1`.
Returns `{ items[], total, total_pages, page, facets? }` with lean card
payloads `{ id, type, kind, title, url, excerpt, image, terms[], fields[], geo }`.

### `GET /geojson` (public)
`?type=` optional csv. RFC 7946 FeatureCollection of geolocated listings.

### `GET /events/upcoming` (public)
`?limit=`. Chronological upcoming `dmf_event` items.

### `GET|POST /types`
GET public: active types. POST (`manage_options`): create/update a listing
type from a JSON config (same schema as `config/listing-types.php` entries).

### `POST /inquiries` (public, hardened)
Headers: `X-DMF-Nonce` (`wp_create_nonce('dmf_inquiry')`). Body: `name*`,
`email*`, `company`, `event_type`, `delegates`, `dates`, `message`,
`listing_id`, `website` (honeypot — leave empty). 429 after 5/10min/IP.

### `GET /favorites` · `POST /favorites/{id}` (logged-in)
Read ids / toggle. Returns `{ id, saved, count }`.

### `GET /me` (logged-in)
Member dashboard payload: profile, own listings with statuses, favorites.

### `GET /status` (`manage_options`)
Versions + published counts per type.

## Key hooks

| Hook | Type | Purpose |
|---|---|---|
| `dmf/modules` | filter | Add/replace/remove framework modules |
| `dmf/listing_types` | filter | Final listing type config map |
| `dmf/taxonomies` | filter | Shared taxonomy definitions |
| `dmf/template` | filter | Resolved template path |
| `dmf/search_query_args` / `dmf/search_item` | filter | Tune search |
| `dmf/card_fields_map` | filter | kind → card meta field keys |
| `dmf/listing_query_args` | filter | Grid/section queries |
| `dmf/jsonld` / `dmf/breadcrumbs` | filter | SEO output |
| `dmf/option/{key}` | filter | Force settings per environment |
| `dmf/listing_saved` | action | After field save |
| `dmf/inquiry_created` | action | After RFP stored+mailed (CRM bridge point) |
| `dmf/favorite_toggled` | action | Bookmark analytics |
| `dmf/member_profile` | filter | Extend the /me payload |
| `dmf/booted` | action | Framework ready |

## WP-CLI

```
wp dmf types          # table of active listing types
wp dmf seed --count=5 # demo listings per type
wp dmf export-types   # JSON of all type configs (versioning/migration)
```
