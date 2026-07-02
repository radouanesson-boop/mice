# DMF — Deployment guide

## What ships

```
wp-content/plugins/dmf-core/        # no build required (bundled autoloader)
wp-content/themes/visit-marrakech/  # assets/css/main.css is committed
```

Node and Composer are **development** tools only; production runs from the
repo as-is.

## First deploy

1. Upload/clone both directories; activate plugin, then theme.
2. Settings → Permalinks → Save (registers listing rewrites).
3. Destination → Settings: map provider + key, inquiry recipient.
4. Create menus (Primary/Mobile/Footer/Legal) and set a static front page.
5. Customize → Homepage content: hero + CTA texts and imagery.
6. Optional: `wp dmf seed` on staging for demo content.

## Environments & config as code

Force settings per environment (no DB drift):

```php
// wp-config.php or mu-plugin
add_filter( 'dmf/option/maps.provider', fn() => 'osm' );
add_filter( 'dmf/option/forms.recipient', fn() => 'rfp@staging.example' );
```

Version listing types: `wp dmf export-types > types.json` and commit; load
on the target via the `dmf/listing_types` filter or the POST /types API.

## Updates

- Plugin/theme updates are plain file replacements (git pull / rsync).
- After a DMF Core update that changes listing types: Settings →
  Permalinks → Save once.
- Cache busting is automatic (filemtime-based asset versions).

## Performance checklist

- Page cache + object cache on; REST search responses are cache-friendly
  (GET, no cookies needed).
- Serve AVIF/WebP via your optimizer; hero ≤ 250 KB.
- Lighthouse targets: ≥ 90 mobile on home / archive / single listing.
- >10k listings? Enable the geo index add-on (roadmap) or filter
  `dmf/search_query_args` to your search service (Elastic/Algolia).

## Security checklist

- Keep `DMF_REMOVE_DATA_ON_UNINSTALL` undefined in production.
- Partner accounts get the `dmf_partner` role only.
- The REST write surface is limited to inquiries (hardened) and
  authenticated favorites; everything else is read-only or admin-gated.
