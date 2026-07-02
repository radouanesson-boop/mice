# DMF — Coding standards

## PHP

- **WordPress Coding Standards** (`composer lint` runs WPCS) + PHPStan
  level 6 (`composer analyse`). PHP 8.0 minimum; typed properties,
  readonly value objects, match expressions, arrow functions welcome.
- Every input sanitized on write (per-field sanitizers in
  `Fields\Field::sanitize()`), every output escaped at the template
  (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`).
- Every state-changing request: nonce + capability check. REST args always
  declare `sanitize_callback`/`type`; public write endpoints add honeypot
  and rate limiting.
- No direct SQL in v0.x — `WP_Query`, meta and options APIs only. If a
  custom table lands (geo index), it gets its own repository class and
  schema migration.
- Modules never call each other's internals — cross-module communication
  goes through hooks or the public `dmf_*` functions.
- Translatable strings only (`__( '…', 'dmf' )`); no string concatenation
  inside translations; placeholders documented with translator comments.

## JavaScript

- Vanilla ES2017+, no jQuery, no build step required for the theme JS.
- Feature modules are isolated IIFEs that exit quietly when their markup
  is absent; behaviour attaches via `data-*` hooks (`data-dmf-search`),
  never via style classes.
- Progressive enhancement: every JS-enhanced component must work without
  JS (native form POST, native `<details>`, CSS hover menus).

## CSS/SCSS

- Design tokens are the single source of truth
  (`assets/scss/abstracts/_tokens.scss`) — components consume custom
  properties, never raw hex (the audited exceptions are annotated).
- BEM naming, one component per partial, mobile-first `@include mq()`.
- Elevation on hover only; motion respects `prefers-reduced-motion`.

## Accessibility (WCAG 2.1 AA)

- Landmarks + skip link; one `h1` per page; focus visible everywhere
  (`focus-ring` mixin); 44px minimum targets; `aria-expanded`/`aria-modal`
  on disclosure widgets; forms fully labelled; live regions for async
  feedback (`role="status"` on inquiry feedback).

## Git

- Conventional, imperative commit subjects; one logical change per commit.
- `main` is releasable; feature branches per module/feature.
