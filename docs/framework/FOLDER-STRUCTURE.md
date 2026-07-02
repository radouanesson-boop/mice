# DMF — Folder structure & naming conventions

## Repository

```
wp-content/
├── plugins/dmf-core/                  ← the framework (destination logic)
│   ├── dmf-core.php                   Bootstrap: constants, guards, autoload, lifecycle
│   ├── composer.json                  PSR-4 (DMF\ → src/), wpcs + phpstan dev deps
│   ├── uninstall.php                  Opt-in data removal only
│   ├── config/
│   │   └── listing-types.php          Destination configuration (Marrakech reference)
│   ├── src/
│   │   ├── Autoloader.php             Bundled PSR-4 fallback (zip installs)
│   │   ├── Plugin.php                 Kernel: container + module lifecycle
│   │   ├── functions.php              Public procedural API (dmf(), dmf_get_field()…)
│   │   ├── Contracts/Module.php       Module interface
│   │   ├── Support/                   Container, Template engine, Options
│   │   └── Modules/{Area}/            One folder per module
│   │       └── {Area}Module.php       Entry class implementing Module
│   ├── templates/                     Theme-overridable frontend templates
│   │   ├── archive-listing.php · single-listing.php
│   │   ├── cards/listing.php
│   │   └── blocks/{listings-grid, search-bar, inquiry-form, events-list}.php
│   ├── assets/admin/                  Admin-only CSS/JS
│   └── languages/                     dmf.pot + translations
│
└── themes/visit-marrakech/            ← the brand (presentation only)
    ├── style.css · functions.php      Theme header + slim bootstrap
    ├── inc/                           helpers, setup, enqueue, menus-widgets, customizer
    ├── template-parts/                header/ footer/ hero/ sections/ cards/ components/
    ├── dmf/                           DMF template overrides (card, archive, single)
    ├── assets/                        scss/ (tokens-driven design system) css/ js/ svg/ fonts/
    └── languages/                     vm.pot + translations
```

## Naming conventions

| Thing | Convention | Example |
|---|---|---|
| PHP namespace | `DMF\Modules\{Area}` | `DMF\Modules\Search\SearchModule` |
| Class files | PSR-4, StudlyCase | `src/Modules/Listings/TypeRegistry.php` |
| Public functions | `dmf_snake_case()` | `dmf_get_field()` |
| Hooks | `dmf/{area}_{event}` or `dmf/{noun}` | `dmf/inquiry_created`, `dmf/listing_types` |
| Post types | `dmf_{singular}` ≤ 20 chars | `dmf_venue` |
| Taxonomies | `dmf_{noun}` | `dmf_category` |
| Post meta | `_dmf_{field_key}` (protected) | `_dmf_capacity` |
| Options | `dmf_{noun}` | `dmf_settings`, `dmf_listing_types` |
| REST | `dmf/v1/{resource}` | `dmf/v1/search` |
| CSS (framework structural) | `dmf-block__element--modifier` | `dmf-search__input` |
| CSS (theme design system) | `mcb-block__element--modifier` | `mcb-card--venue` |
| Theme functions/hooks | `vm_*` / `vm/{noun}` | `vm_rfp_cta()`, `vm/strengths` |
| Text domains | plugin `dmf`, theme `vm` | — |
