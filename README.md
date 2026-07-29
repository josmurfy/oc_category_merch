# Category Merch Manager

OpenCart 4.x module to make your category menu smarter.

**Version:** 0.4.0
**Compatibility:** OpenCart 4.x
**Languages:** English, Français, Español
**License:** MIT (see [LICENSE](LICENSE))

> The GitHub repo keeps the `oc_category_merch` name for continuity with existing
> installs/links, but the extension's internal code, folder and namespace were
> renamed to `category_merch` (dropped the `oc_` prefix) in 0.3.3.

---

## Features

- **Hide Empty Categories** — top-level categories with 0 active products are hidden from the front menu
- **Hide Empty Subcategories** — same, but only inside their parent (independent toggle)
- **Sort by Score** — categories ordered by product volume (recursive subtree count)
- **Per-category Overrides** — force show or force hide any category regardless of the auto rules
- **Dashboard** — Chart.js horizontal bar graph of category activity
- **Auto-updater** — one-click update from the admin panel (manifest-driven or GitHub Releases)
- **i18n** — full EN / FR / ES translations
- **Performance** — server-side paged tree (300/page), cached totals, tuned for 60k+ categories
- **SEO-aware** — resolves category IDs from SEO URLs across all storefront languages
- **Category page filter** — hides empty subcategories on the category page's own listing, not just the top menu
- **Related Categories widget** — auto-appended to category/product pages, pointing buyers to sibling/child categories with stock
- **Category Showcase** — optional homepage block ("Our Best Collections") with the top categories by active product count, auto-selected or manually curated

---

## Installation

1. Download the latest `category_merch.ocmod.zip` from the [Releases](../../releases) page
2. OpenCart Admin → **Extensions → Installer** → upload the zip
3. Admin → **Extensions → Modules** → find *Category Merch Manager* → click **Install** then **Edit**
4. Configure the settings and click **Save**
5. Optional: same page, find *Category Showcase* → **Install** → **Edit** to enable the homepage "Best Collections" block

---

## Configuration

| Setting | Default | Description |
|---|---|---|
| Status | Disabled | Master switch — enables the menu filter event |
| Hide Empty Categories | Enabled | Hide top-level categories with 0 active products |
| Hide Empty Subcategories | Enabled | Hide subcategories with 0 active products |
| Sort by Score | Enabled | Sort by product subtree count (descending) |
| Weight — Volume | 100 | Relative weight of product count in the score |
| Cache TTL | 300 s | Menu cache lifetime |

### Overrides tab

Force-show or force-hide any specific category. Overrides always win over the automatic rules.

### Updates tab

Same UX/logic as the Debug Logger extension's Updates tab:

- Auto-checks GitHub Releases as soon as the tab is opened (plus a manual **Refresh** button), 6h server-side cache
- Shows current vs. latest version, changelog, and full **version history** (with re-install/downgrade per version)
- **Install update** — downloads the release asset, verifies it, extracts over the current install

---

## Uninstall

Admin → Extensions → Modules → **Category Merch Manager** → Uninstall.
This removes the settings row and the frontend `catalog/view/common/menu/before` event.
The extension folder can then be deleted via Extensions → Installer → Installed.

---

## Architecture

```
extension/category_merch/
├── install.json                      # Marketplace metadata (code: category_merch)
├── admin/
│   ├── controller/module/category_merch.php
│   ├── controller/module/category_showcase.php   # 2nd module: homepage showcase settings
│   ├── model/module/category_merch.php
│   ├── language/{en-gb,fr-fr,es-es}/module/{category_merch,category_showcase}.php
│   └── view/template/module/{category_merch,category_showcase}.twig
└── catalog/
    ├── controller/events.php         # All event handlers: menu filter, category-page
    │                                 # filter, related categories, homepage showcase
    ├── model/module/category_merch.php
    └── language/{en-gb,fr-fr,es-es}/module/category_merch.php
```

Namespace: `Opencart\{Admin|Catalog}\{Controller|Model}\Extension\CategoryMerch\Module`.

---

## Development

Repo layout mirrors the OpenCart install path exactly, so the working tree can be
zipped directly for distribution:

```bash
zip -r category_merch.ocmod.zip . -x ".git/*" "*.DS_Store" "README.md" ".gitignore" "release.sh"
```

---

## Changelog

### 0.4.0
- New: **Dashboard drill-down tree** — lazy-loaded expandable category tree (Dashboard tab) shows the score at any depth (sub, sub-sub, etc.), not just top-level totals.
- New: **Category page filter** — empty subcategories are now also hidden on the category page's own listing (previously only the top menu was filtered).
- New: **Related Categories widget** — auto-appended on category/product pages (siblings or children with stock), togglable.
- New: **Category Showcase** — second module bundled in this extension, auto-injects a "best collections" tile grid on the homepage when enabled (single toggle, no Design > Layout placement needed), auto-selected by score or manually curated by category ID.
- Perf: catalog-side category totals are now cached (5 min), since more storefront events now read them per page view.

### 0.3.3
- **Breaking (internal):** renamed extension folder/namespace/settings from `oc_category_merch` to `category_merch` (dropped the `oc_` prefix), matching the Debug Logger extension's convention. GitHub repo name unchanged for continuity.
- Fix: `check_updates`/`install_update`/`overrides_url` links were built with `Url::link()`'s default HTML-escaped `&amp;`, which silently broke the query string when used inside `fetch()` — the `user_token` param was never actually received by the server, so every AJAX call looked like a logged-out request. Now generated with the JS-safe (`$js = true`) raw `&`.
- New: Updates tab rebuilt to match Debug Logger's UX — auto-check on tab open, 6h cache, full version history with per-version changelog/downgrade.
- Added: MIT LICENSE.

### 0.3.1
- New: **Hide Empty Subcategories** independent toggle
- New: Auto-updater (manifest URL + GitHub Releases fallback)
- Fix: Twig 3 syntax compatibility

### 0.3.0
- Rename `oc4_category_merch` → `oc_category_merch`, namespace `OcCategoryMerch`
- Twig 3 template fixes, reverse-range guard

### 0.2.x
- SEO URL resolution for FR/ES menus
- `__total` key defensive access
- Server-side paged Overrides tab (300/page + search)
- Cached totals + tree, keyed by cache version + language

### 0.1.0
- Initial release: hide empty, sort by score, per-category overrides
