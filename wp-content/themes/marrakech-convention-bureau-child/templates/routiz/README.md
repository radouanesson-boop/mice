# Routiz template overrides

Brikk's listing engine is the **Routiz** core plugin. This directory is the
official, update-safe override location for its Blade templates:

```
Plugin source:   wp-content/plugins/routiz/templates/<template-name>
Child override:  wp-content/themes/marrakech-convention-bureau-child/templates/routiz/<template-name>
```

## Workflow

1. Locate the template you want to restyle inside the **installed** Routiz
   plugin (`wp-content/plugins/routiz/templates/…`). Typical targets:
   - the single-listing layout (gallery, meta, booking sidebar),
   - the archive/search results loop item,
   - search & filter form partials,
   - dashboard views.
2. Copy the file **unchanged** into this folder, mirroring the exact
   relative path and file name.
3. Edit the copy only. Wrap your changes in MCB classes (`mcb-…`) so the
   compiled stylesheet styles them — prefer adding classes over rewriting
   the plugin's logic.
4. Enable `WP_DEBUG` once and reload: Routiz flushes its compiled Blade
   template cache only in debug mode. Then turn debug off again.
5. After every Routiz update, diff your overrides against the new plugin
   templates (`git diff --no-index`) and port any structural changes.

## Rules

- **Never** edit files inside the `routiz` plugin or the `brikk` parent
  theme — both are overwritten by updates.
- Keep overrides minimal: the MCB stylesheet already restyles Routiz's
  default markup (see `assets/scss/vendors/_brikk-overrides.scss`), so an
  override is only needed when markup structure must change, not colors or
  spacing.
- Every file you add here must carry a header comment noting the Routiz
  version it was copied from, e.g. `{{-- Overrides routiz v3.1.0 --}}`.

> Why is this folder almost empty in the repo? The Routiz plugin is a
> licensed product and its templates cannot be redistributed here. The
> overrides are created on the installed site by copying from your licensed
> plugin, following the steps above.
