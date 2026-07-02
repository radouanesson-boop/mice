# Marrakech Convention Bureau — WordPress (Brikk child theme)

WordPress implementation of the new Marrakech Convention Bureau website,
built as a **child theme of Brikk** (Directory & Listing theme by Utillz).
Brikk and its Routiz core plugin remain the application framework — listings,
venues, hotels, experiences, events, search, filters, maps, taxonomies,
booking, user dashboard, Elementor and SEO all keep working. Only the
presentation layer is redesigned.

## Repository layout

```
wp-content/themes/marrakech-convention-bureau-child/   ← the deliverable (see its README)
docs/
├── INSTALLATION.md        Full setup: parent stack, child theme, menus, homepage
├── DEPLOYMENT.md          Build, rollout, safe Brikk/Routiz updates, perf checklist
├── CONTENT-MAPPING.md     Dynamic data mapping (Routiz fields → components)
└── ELEMENTOR-GUIDE.md     Widget catalogue + homepage composition for editors
design-reference/          Drop the Claude Design export here + token sync guide
```

## Quick start

```bash
cd wp-content/themes/marrakech-convention-bureau-child
npm install && npm run build     # compiled main.css is also committed
```

Copy the theme folder to your WordPress `wp-content/themes/`, activate it
(Brikk must be installed), then follow `docs/INSTALLATION.md`.

## Principles

- **Never modify Brikk core.** Child theme + hooks + filters + the official
  `templates/routiz/` override mechanism only — everything survives updates.
- **Design tokens.** One source of truth (`assets/scss/abstracts/_tokens.scss`)
  drives colors, type, spacing, radii and shadows everywhere.
- **Elementor-editable.** Header/footer locations + the "Marrakech CB" widget
  set make every homepage section client-editable without code.
- **Dynamic everything.** No hardcoded content; listings, taxonomies,
  featured images, custom fields, menus, widgets and theme mods feed the UI.
- **Performance & SEO.** One stylesheet, one deferred vanilla JS file,
  self-hosted fonts, native lazy-loading, srcset image sizes, semantic HTML5,
  SEO-plugin-delegated breadcrumbs/schema.
