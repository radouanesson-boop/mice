# Dynamic content mapping

Everything the design shows is dynamic. This document maps each UI component
to its WordPress data source and shows the one-time configuration needed on a
real install.

## Listing cards ↔ Routiz fields

Routiz stores listing fields as post meta with keys you define in its field
builder. The theme reads **logical keys** and you map them once:

```php
// mu-plugins/mcb-config.php
add_filter( 'mcb/listing_meta_map', function ( $map ) {
	return $map + array(
		// logical key  =>  your Routiz meta key
		'capacity'   => 'routiz_field_capacity',
		'location'   => 'routiz_field_district',
		'stars'      => 'routiz_field_star_rating',
		'rooms'      => 'routiz_field_rooms',
		'duration'   => 'routiz_field_duration',
		'group_size' => 'routiz_field_group_size',
		'event_date' => 'routiz_field_event_date',
		'event_venue'=> 'routiz_field_venue_name',
	);
} );
```

| Component | Data source |
|---|---|
| Card image | Featured image (`mcb-card*` sizes, srcset + lazy) |
| Card badge | First hierarchical taxonomy term (`mcb_listing_primary_term()`) |
| Card rating | Review meta — key list filterable via `mcb/rating_meta_keys` |
| Venue card meta | `capacity`, `location` |
| Hotel card meta | `stars`, `rooms`, `location` |
| Experience card meta | `duration`, `group_size` |
| Event card date block | `event_date` (timestamp or parseable date string) |
| Card excerpt | Post excerpt |

## Card kind detection

`mcb_listing_kind()` inspects the listing's taxonomy terms for
venue/hotel/experience/event keywords. If your Routiz listing types use other
slugs (e.g. French), map them explicitly:

```php
add_filter( 'mcb/listing_kind', function ( $kind, $post ) {
	if ( has_term( 'lieux-evenementiels', 'listing_type', $post ) ) return 'venue';
	if ( has_term( 'hebergement', 'listing_type', $post ) )         return 'hotel';
	return $kind;
}, 10, 2 );
```

## Homepage sections

| Section | Source | Client edits via |
|---|---|---|
| Hero | MCB Hero widget settings (or theme mods) | Elementor |
| Search bar | Routiz search shortcode (`mcb/search_shortcode` filter) | Routiz search form builder |
| Stats | MCB Stats widget repeater | Elementor |
| Venues / Hotels / Experiences / Events | Live `WP_Query` on listing post types + taxonomy filter | Elementor widget controls |
| Testimonials | `testimonial` post type (filterable) | Posts UI |
| News | Latest `post`s | Posts UI |
| Partners | `mcb/partners` filter or Elementor image carousel | Media library |
| CTA | Theme mods `mcb_cta_*` or Elementor | Customizer / Elementor |

## Navigation

| Element | Source |
|---|---|
| Mega menu columns | WP menu structure (3 levels) + `mcb-mega` class on the parent item |
| Link subtitles in dropdowns | The menu item "Description" field |
| Header CTA | `mcb/header_cta` filter / theme mods |
| Footer columns | Widget areas → menus fallback |

## Elementor dynamic tags

All MCB widget text/image/link controls have `dynamic => active`, so
Elementor Pro users can bind them to ACF fields, site data, or Routiz post
meta via the native Dynamic Tags UI.
