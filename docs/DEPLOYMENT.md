# Deployment guide

## What gets deployed

Only the child theme directory:

```
wp-content/themes/marrakech-convention-bureau-child/
```

The committed `assets/css/main.css` (+ sourcemap) is the production build —
**no Node.js is needed on the server**. Exclude development files if you want
a lean artifact:

```
node_modules/
assets/scss/        # optional: keep for reference, not needed at runtime
package.json
package-lock.json
```

## Standard rollout

1. **Build locally / in CI**
   ```bash
   cd wp-content/themes/marrakech-convention-bureau-child
   npm ci && npm run build
   ```
2. **Upload** the theme folder (rsync/SFTP/deploy pipeline):
   ```bash
   rsync -avz --delete \
     --exclude node_modules --exclude '*.map.free' \
     wp-content/themes/marrakech-convention-bureau-child/ \
     user@host:/var/www/site/wp-content/themes/marrakech-convention-bureau-child/
   ```
3. **Activate** (first deploy only): Appearance → Themes, or
   `wp theme activate marrakech-convention-bureau-child`.
4. **Flush caches**: page cache, object cache, and Elementor CSS
   (Elementor → Tools → Regenerate CSS & Data).
5. If Routiz overrides changed: enable `WP_DEBUG` once, load a listing page
   (flushes compiled Blade templates), then disable it.

## Cache busting

Asset versions come from `filemtime()` (`mcb_asset_version()`), so every
deploy invalidates browser caches automatically — no manual version bumps.

## Updating Brikk / Routiz safely

1. Snapshot/backup, update **on staging first**.
2. Update the Brikk theme and Routiz plugin (child theme is untouched).
3. Diff any files you copied into `templates/routiz/` against the new plugin
   versions and port structural changes:
   ```bash
   git diff --no-index wp-content/plugins/routiz/templates/x.blade.php \
     wp-content/themes/marrakech-convention-bureau-child/templates/routiz/x.blade.php
   ```
4. Smoke-test: search, filters, map, single listing, booking flow, dashboard,
   Elementor editing.
5. Promote to production.

Because all child code is guarded (`function_exists`, `has_action`,
runtime post-type detection), a parent update degrades gracefully instead of
fataling even if hook names change.

## Performance checklist (post-deploy)

- [ ] Lighthouse ≥ 90 on Home / archive / single listing (mobile).
- [ ] Hero image served as AVIF/WebP ≤ 250 KB (use an optimizer plugin).
- [ ] Only one MCB stylesheet and one MCB script in the waterfall.
- [ ] Fonts served from the theme (2 files, ~62 KB) with `font-display: swap`.
- [ ] Audit Brikk assets: list redundant handles in `mcb/dequeue_styles`.
