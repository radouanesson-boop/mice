# DMF — Data model & ER diagram

DMF deliberately uses **native WordPress storage** — no custom tables in
v0.x. This buys free compatibility with every backup, migration, cache,
search and multilingual tool. A geo/search index table is planned as an
opt-in Performance add-on for >10k-listing deployments (see ROADMAP).

## Storage map

| Data | Where | Key(s) |
|---|---|---|
| Listings | `wp_posts` | one CPT per listing type (`dmf_venue`, `dmf_hotel`, …) |
| Listing fields | `wp_postmeta` | `_dmf_{field_key}` (typed + sanitized) |
| Geo | `wp_postmeta` | `_dmf_geo` = `{lat, lng}` |
| Ratings (denormalized) | `wp_postmeta` | `_dmf_rating`, `_dmf_rating_count` |
| Categories/areas/amenities/tags | `wp_terms` + `wp_term_taxonomy` | `dmf_category`, `dmf_area`, `dmf_amenity`, `dmf_tag` |
| Reviews | `wp_comments` | `comment_type = dmf_review` + `dmf_rating` comment meta |
| Inquiries (RFP pipeline) | `wp_posts` | private CPT `dmf_inquiry` + `_dmf_inq_*` meta |
| Favorites | `wp_usermeta` | `dmf_favorites` (int[]) |
| Member org profile | `wp_usermeta` | `dmf_organization`, `dmf_org_role` |
| Site-created listing types | `wp_options` | `dmf_listing_types` |
| Settings | `wp_options` | `dmf_settings` (single autoloaded array) |

## ER diagram

```mermaid
erDiagram
    LISTING_TYPE ||--o{ LISTING : "defines (config)"
    LISTING ||--o{ FIELD_VALUE : "has (_dmf_* postmeta)"
    LISTING }o--o{ TERM : "classified by (dmf_category/area/amenity/tag)"
    LISTING ||--o{ REVIEW : "receives (dmf_review comments)"
    LISTING ||--o{ INQUIRY : "referenced by"
    USER ||--o{ LISTING : "authors (dmf_partner)"
    USER ||--o{ FAVORITE : "saves (usermeta dmf_favorites)"
    FAVORITE }o--|| LISTING : "points to"
    USER ||--o{ REVIEW : "writes"
    INQUIRY }o--|| USER : "handled by (bureau staff)"

    LISTING_TYPE {
        string slug PK "dmf_venue"
        string kind "venue|hotel|event|..."
        json field_groups
        string[] taxonomies
    }
    LISTING {
        int ID PK
        string post_type FK "listing type slug"
        string post_status
        int post_author FK
    }
    FIELD_VALUE {
        string meta_key "_dmf_capacity"
        mixed meta_value "sanitized per field type"
    }
    REVIEW {
        int comment_ID PK
        int rating "1-5 (comment meta)"
    }
    INQUIRY {
        int ID PK
        string status "private CPT"
        int listing_id FK
    }
```

## Query hot paths

- Cards read: title + thumbnail + 2–3 metas (`dmf_get_card_fields()`),
  averages pre-denormalized — no COUNT queries at render time.
- Search: one `WP_Query` with tax/meta clauses; radius applied post-query
  (haversine) at destination scale; `no_found_rows` unless paginated.
- GeoJSON: single meta-keyed query, cacheable, 500-feature cap per call.
