# DMF — Versioning & release strategy

## Semantic versioning

`MAJOR.MINOR.PATCH` for both dmf-core and the theme (independent numbers).

- **PATCH** — fixes, no API change.
- **MINOR** — new modules/endpoints/hooks; existing behaviour preserved.
- **MAJOR** — breaking change to the public surface.

## What is "public API" (compatibility promise)

1. Procedural functions in `src/functions.php`.
2. Documented hooks (`docs/framework/API.md`).
3. REST `dmf/v1` request/response shapes (breaking REST changes ship as
   `dmf/v2` alongside v1 for one MAJOR cycle).
4. Template names + their `$args` contracts.
5. Listing type / field config schema.

Everything else (class internals, module wiring) may change in minors —
add-ons should code against hooks, not classes.

## Releases

- `dmf_version` option is stamped on activation; future migrations run
  from `CoreModule::install()` comparing stored vs current version
  (idempotent steps only).
- Changelogs per component in `CHANGELOG.md` (keep-a-changelog format).
- Git tags: `dmf-core@X.Y.Z`, `visit-marrakech@X.Y.Z`.

## Roadmap → see ROADMAP.md
