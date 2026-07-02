# Marrakech Convention Bureau — Destination Management Framework

An **open, modular, from-scratch WordPress framework for Destination
Marketing Organizations, Convention Bureaus and Tourism Boards** — an
operating system for destinations — with **Marrakech Convention Bureau**
(https://mice.visitmarrakech.com) as its first implementation, designed
around the Visit Marrakech · Bahja Spirit identity.

## What's in this repository

| Path | What it is |
|---|---|
| `wp-content/plugins/dmf-core/` | **DMF Core** — the framework plugin: kernel + 17 modules (listings, search, maps, events, members, favorites, reviews, forms/RFP, SEO, API, blocks, shortcodes, admin, i18n, performance, CLI). PSR-4, no page builder, no jQuery, no commercial dependencies. |
| `wp-content/themes/visit-marrakech/` | **Visit Marrakech** — standalone theme implementing the Claude Design prototype (boards 1A/1C/1D) on the framework. Pure presentation: token-driven SCSS design system, template parts, `dmf/` template overrides, vanilla JS. |
| `docs/framework/` | Architecture, database schema + ER diagram, folder structure & naming, coding standards, API reference, developer guide (add-ons, child themes, new destinations), user guide, deployment, maintenance, versioning, roadmap. |
| `design-reference/` | The Claude Design export (`.dc.html`), design data, and the Visit Marrakech brand identity PDF, with a design→code map. |
| `wp-content/themes/marrakech-convention-bureau-child/` + `docs/*.md` | The earlier **Brikk child theme** track (kept for reference; superseded by the framework). |

## Quick start

```bash
# WordPress ≥ 6.4, PHP ≥ 8.0
wp plugin activate dmf-core
wp theme activate visit-marrakech
wp option update permalink_structure '/%postname%/' && wp rewrite flush
wp dmf seed --count=4        # optional demo listings
```

Then: Destination → Settings (maps, inquiry email) · Appearance → Menus ·
Customize → Homepage content. Full steps in
[`docs/framework/DEPLOYMENT.md`](docs/framework/DEPLOYMENT.md).

## Design principles

- **Modules, not monolith** — every capability is a swappable module
  behind the `dmf/modules` filter.
- **Configuration, not code** — unlimited listing types defined as data
  (config file, admin UI, or REST); new destinations = new config + theme
  tokens, never a rewrite.
- **One markup source** — blocks, shortcodes and PHP archives render the
  same theme-overridable templates.
- **Framework ≠ brand** — DMF Core outputs neutral `dmf-` structure; the
  theme owns the `mcb-` design system (deep green / sage / Jost +
  Newsreader italic, white premium editorial interface).
- **Enterprise discipline** — WPCS + PHPStan, nonce/sanitize/escape
  everywhere, capability model with a partner role, WCAG AA patterns,
  native i18n with RTL, no security shortcuts.

## Long-term vision

Agadir, Essaouira, Casablanca, Rabat or any international destination
should ship by changing **branding, content and configuration** — see
[`docs/framework/ROADMAP.md`](docs/framework/ROADMAP.md) for the path to
v1.0.
