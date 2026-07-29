# Category Merch Manager

OpenCart 4.x module to make your category menu smarter.

**Version:** 0.3.3
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

---

## Installation

1. Download the latest `category_merch.ocmod.zip` from the [Releases](../../releases) page
2. OpenCart Admin → **Extensions → Installer** → upload the zip
3. Admin → **Extensions → Modules** → find *Category Merch Manager* → click **Install** then **Edit**
4. Configure the settings and click **Save**

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
│   ├── model/module/category_merch.php
│   ├── language/{en-gb,fr-fr,es-es}/module/category_merch.php
│   └── view/template/module/category_merch.twig
└── catalog/
    ├── controller/events.php         # Menu-filter event handler
    └── model/module/category_merch.php
```

Namespace: `Opencart\{Admin|Catalog}\{Controller|Model}\Extension\CategoryMerch\Module`.

---

## Development

Repo layout mirrors the OpenCart install path exactly, so the working tree can be
zipped directly for distribution:

```bash
zip -r category_merch.ocmod.zip . -x ".git/*" "*.DS_Store" "README.md" ".gitignore" "LICENSE" "release.sh"
```

---

## Changelog

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
