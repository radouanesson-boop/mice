# DMF — Developer guide

## Local setup

```bash
# WordPress ≥ 6.4, PHP ≥ 8.0
wp plugin activate dmf-core
wp theme activate visit-marrakech
wp dmf seed --count=4        # demo content
cd wp-content/plugins/dmf-core && composer install   # dev tools (optional)
cd wp-content/themes/visit-marrakech && npm i && npm run build
```

## Recipe: add a listing type (three ways)

**1. No code** — Destination → Listing Types → fill the form (fields as JSON).

**2. Config** — add an entry to your destination's `config/listing-types.php`:

```php
array(
    'slug'     => 'dmf_golf',
    'singular' => 'Golf Course',
    'plural'   => 'Golf Courses',
    'rewrite'  => 'golf',
    'kind'     => 'experience',
    'fields'   => array(
        'golf' => array(
            'label'  => 'Course',
            'fields' => array(
                array( 'key' => 'holes', 'label' => 'Holes', 'type' => 'number', 'searchable' => true ),
                array( 'key' => 'par', 'label' => 'Par', 'type' => 'number' ),
            ),
        ),
    ),
),
```

**3. Code** — from a mu-plugin/add-on:

```php
add_filter( 'dmf/listing_types', function ( array $types ): array {
    $types['dmf_golf'] = array( /* same config array */ );
    return $types;
} );
```

Flush permalinks once (Settings → Permalinks) after adding types.

## Recipe: write a module (add-on plugin)

```php
namespace Acme\Crm;

final class CrmModule extends \DMF\Modules\AbstractModule {
    public function register(): void {
        add_action( 'dmf/inquiry_created', array( $this, 'push_to_crm' ), 10, 2 );
    }
    public function push_to_crm( int $inquiry_id, \WP_REST_Request $request ): void {
        // wp_remote_post() to your CRM…
    }
}

add_filter( 'dmf/modules', fn( array $m ) => $m + array( 'crm' => CrmModule::class ) );
```

## Recipe: override a framework template

Copy `plugins/dmf-core/templates/cards/listing.php` to
`themes/{your-theme}/dmf/cards/listing.php` and edit. Every block,
shortcode and archive rendering that card now uses yours. (See
`visit-marrakech/dmf/` for three real overrides.)

## Recipe: front-end search UI

`GET /wp-json/dmf/v1/search?q=palais&type=dmf_venue&facets=1` — see
`API.md`. The theme's `assets/js/main.js` contains a reference
implementation (debounced live search + results dropdown).

## Theme development (child themes & new destinations)

The Visit Marrakech theme supports child themes natively (standard WP):

1. `Template: visit-marrakech` in the child `style.css`.
2. Override any `template-parts/**` file by shipping the same path.
3. Re-skin by overriding `assets/scss/abstracts/_tokens.scss` values —
   the entire design reads tokens, so a child theme for another
   destination is usually: new tokens + new fonts + new imagery.
4. DMF overrides also chain: `{child}/dmf/x.php` beats `{parent}/dmf/x.php`.

A **new destination** (Agadir, Essaouira…) =
`config/listing-types.php` (its inventory) + a token-level reskin +
content. Framework untouched.
