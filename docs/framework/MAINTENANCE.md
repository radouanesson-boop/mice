# DMF — Maintenance guide

## Routine (monthly)

- WordPress core/plugins updates on staging → smoke test → production.
  Smoke test = home, one archive, one single listing, live search,
  inquiry submission, wp-admin listing edit.
- Review Destination → Inquiries pipeline and spam level (raise the rate
  limit window in `FormsModule` via a small filter PR if needed).
- `GET /dmf/v1/status` (admin) — versions + published counts; wire it to
  your uptime monitor.

## Content hygiene

- Listings without GPS won't appear on maps or radius search — audit with
  a saved WP admin filter or `wp post list --post_type=dmf_venue
  --meta_key=_dmf_geo --format=count`.
- Ratings recount automatically on review (un)approval; if imports touch
  review data, recount via a one-off loop calling
  `ReviewsModule::recount()`.

## Translations

- POT files live in each `languages/` dir; regenerate with
  `wp i18n make-pot`. Arabic ships RTL automatically (`dmf-rtl` body
  class + logical CSS properties).

## Troubleshooting

| Symptom | Fix |
|---|---|
| 404s on listing URLs | Settings → Permalinks → Save |
| New type invisible | Check slug starts with `dmf_` (≤ 20 chars); flush permalinks |
| Search returns nothing | Confirm listings are `publish`; check `type` param values |
| Inquiry form 403 | Page cache serving stale nonce — exclude the form page or lower cache TTL |
| Admin type manager JSON error | Validate the fields JSON (see config/listing-types.php for the schema) |
