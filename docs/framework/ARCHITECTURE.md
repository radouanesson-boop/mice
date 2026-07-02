# DMF — Architecture

## The one-paragraph version

DMF is a **core plugin** (`dmf-core`) that turns WordPress into a
destination platform, plus a **brand theme** (`visit-marrakech`) that is
pure presentation. The plugin's kernel boots a filterable map of
**modules** through a small DI container; every capability (listings,
search, maps, events, members, favorites, reviews, forms, SEO, blocks,
admin, i18n, performance, CLI) is one module that can be replaced or
removed with one filter. Listing types are **configuration, not code** —
a new destination is a new config file + theme, never a rewrite.

## Layer diagram

```
┌─────────────────────────────────────────────────────────┐
│ Brand theme (visit-marrakech)                           │
│  design system (SCSS tokens) · template parts ·         │
│  dmf/ template overrides · vanilla JS behaviours        │
├─────────────────────────────────────────────────────────┤
│ DMF Core plugin                                         │
│  ┌──────────┐ ┌────────┐ ┌──────┐ ┌────────┐ ┌───────┐  │
│  │ Listings │ │ Search │ │ Maps │ │ Events │ │ Forms │… │ ← modules
│  └──────────┘ └────────┘ └──────┘ └────────┘ └───────┘  │
│  Kernel: Plugin · Container · Module contract            │
│  Support: Template engine · Options · functions.php API  │
├─────────────────────────────────────────────────────────┤
│ WordPress core (CPTs, taxonomies, meta, REST, users,     │
│ comments, capabilities, i18n)                            │
└─────────────────────────────────────────────────────────┘
```

## Kernel

- `DMF\Plugin` — singleton kernel. `boot()` resolves the module map
  (`dmf/modules` filter), instantiates each module, runs `register()`
  on all, then `booted()` on all (two-phase so cross-module wiring never
  races), then fires `dmf/booted`.
- `DMF\Support\Container` — explicit bind/singleton/instance container.
  No reflection autowiring: everything greppable.
- `DMF\Contracts\Module` — `register()`, `booted()`, `install()`
  (idempotent activation work).

## The listing engine

Three configuration layers, later wins on slug collision:

1. `config/listing-types.php` — framework/destination defaults.
2. `dmf_listing_types` option — types created in the admin UI or over REST.
3. `dmf/listing_types` filter — code (add-ons, mu-plugins).

A `ListingType` value object describes slug, labels, rewrite, admin icon,
**kind** (venue|hotel|restaurant|experience|event|supplier|generic — drives
card design and schema.org type), taxonomies, and **field groups**. Fields
are typed (`text`, `number`, `select`, `multiselect`, `geo`, `gallery`,
`date`, `hours`, `social`, …), each with its own sanitizer, stored as
`_dmf_{key}` post meta and registered via `register_post_meta()` so the
REST API and block editor see them.

Shared taxonomies (`dmf_category`, `dmf_area`, `dmf_amenity`, `dmf_tag`)
attach per type; extra taxonomies via the `dmf/taxonomies` filter.

## Template engine

`DMF\Support\Template` resolves `{name}` through:

1. `{child-theme}/dmf/{name}.php`
2. `{parent-theme}/dmf/{name}.php`
3. `{plugin}/templates/{name}.php`

Blocks, shortcodes and the archive/single routing all render the same
templates — one markup source. `template_include` routing respects a
theme's native `archive-{cpt}.php` / `single-{cpt}.php` when present.

## REST surface (`dmf/v1`)

| Endpoint | Method | Auth | Module |
|---|---|---|---|
| `/search` | GET | public | Search |
| `/geojson` | GET | public | Maps |
| `/events/upcoming` | GET | public | Events |
| `/types` | GET | public | API |
| `/types` | POST | `manage_options` | API |
| `/status` | GET | `manage_options` | API |
| `/favorites`, `/favorites/{id}` | GET/POST | logged-in | Favorites |
| `/inquiries` | POST | public (nonce + honeypot + rate limit) | Forms |
| `/me` | GET | logged-in | Members |

## Security model

- Custom capability set (`dmf_listing` + `map_meta_cap`) — editors manage
  all listings, the `dmf_partner` role only their own.
- Every write path: nonce → capability → per-field sanitize. Every output:
  escaped at the template. REST args carry sanitize callbacks; public
  write endpoints add honeypot + transient rate limiting.
- Uninstall deletes data only behind an explicit
  `DMF_REMOVE_DATA_ON_UNINSTALL` constant.

## Extension points (primary)

`dmf/modules`, `dmf/listing_types`, `dmf/taxonomies`,
`dmf/search_query_args`, `dmf/search_item`, `dmf/card_fields_map`,
`dmf/get_field`, `dmf/listing_query_args`, `dmf/jsonld`,
`dmf/breadcrumbs`, `dmf/template`, `dmf/option/{key}`,
`dmf/listing_saved`, `dmf/inquiry_created`, `dmf/favorite_toggled`,
`dmf/member_profile`, `dmf/booted`.
