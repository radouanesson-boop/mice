# DMF — Development roadmap

## v0.1 (this release) — Foundation ✅
Kernel + 17 modules, config-driven listing engine (6 Marrakech types),
REST search with facets/radius, GeoJSON maps layer, events, members,
favorites, reviews, RFP pipeline, SEO, blocks/shortcodes, admin (no-code
types, settings, dashboard), i18n/RTL, CLI, Visit Marrakech theme
implementing the Claude Design boards 1A/1C/1D, documentation suite.

## v0.2 — Editorial & data depth
- Block patterns for every homepage section (full no-code homepage).
- Media module: galleries UI (drag-drop), downloads/resources library,
  press area.
- Import wizard (CSV/JSON → listings with field mapping) + export.
- Saved searches & search history (logged-in), sort options UI.
- Admin analytics cards (inquiries funnel, top listings).

## v0.3 — Members & monetization
- Membership plans + verification workflow for partners.
- Front-end listing submission & edit (partner dashboard pages).
- Notifications module (email digests, in-admin notices).
- Messaging between bureau and partners.

## v0.4 — Events & maps pro
- Recurring events, iCal export, speaker/sponsor/agenda CPT relations,
  ticketing bridge (Woo/tickets add-on).
- Map adapters shipped for Google/Mapbox/Leaflet with clustering,
  polygon/draw-area search, directions deep links.
- Opt-in geo index table for >10k listings + Elastic/Algolia bridge.

## v0.5 — Enterprise
- Multisite "destination network" mode (shared framework, per-city sites).
- Critical CSS pipeline + Vite-based asset builds.
- Full WCAG audit + automated a11y CI.
- Public add-on SDK docs + example add-on repository.

## v1.0 — Stability promise
Frozen public API surface, upgrade guides, reference deployments
(Marrakech + one more destination), performance certification
(Lighthouse ≥ 95 on reference content).
