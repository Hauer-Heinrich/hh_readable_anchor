# hh_readable_anchor – readable anchors for TYPO3

Adds the field **"Anchor (jump mark)"** to **all** content elements (tt_content)
and replaces the ID `c123` with a readable ID.

## Logic

| Field "Anchor"  | Header                                | Result              |
|-----------------|---------------------------------------|---------------------|
| `Our Team!`     | (any)                                 | `#our-team`         |
| empty           | `Über uns & mehr`                     | `#ueber-uns-mehr`   |
| empty           | present, but layout "Hidden" (100)    | `#c123` (default)   |
| empty           | empty                                 | `#c123` (default)   |

- Conversion uses the core `SlugHelper` (umlauts → ae/oe/ue, ß → ss, spaces → `-`).
- The editor's input is already converted to a URL-safe value when saving.
- The old anchor `c123` is additionally kept as `<span id="c123"></span>` →
  existing links (e.g. from the RTE link browser) keep working.
- Duplicate IDs on a page are numbered (`contact`, `contact-2`).
  For permanently stable links, editors should fill in the field explicitly.
- Translations get their anchor from their translated header or their own field.

## Installation

**Composer (recommended):** Place the folder e.g. in `packages/hh_readable_anchor`, then run

```bash
composer config repositories.local path 'packages/*'
composer require hauerheinrich/hh-readable-anchor:@dev
vendor/bin/typo3 extension:setup      # creates the database field
vendor/bin/typo3 cache:flush
```

**Classic mode:** Copy the folder to `typo3conf/ext/hh_readable_anchor`, activate it in the
Extension Manager and run "Analyze Database Structure".

**Include TypoScript (after fluid_styled_content):**
- TYPO3 12: Static template "Readable Anchor (lesbare Sprungmarken)" in the template record.
- TYPO3 13: Add the site set `hauerheinrich/hh-readable-anchor` in the site configuration
  (or use the static template as well).

## Custom layouts / site package

The bundled layout only overrides the core layout of fluid_styled_content
(`layoutRootPaths.5`). If your site package has its own `Layouts/Default.html`,
that one stays active – simply replace `c{data.uid}` there:

```html
<div id="{ra:anchor(data: data)}" class="frame ...">
    <span id="c{data.uid}"></span> <!-- optional: keep supporting old links -->
```

The `ra` namespace is registered globally, so no `xmlns` declaration is needed.

## Section menu (menu_section / menu_section_pages)

The core menus still link to `#c123` – this works thanks to the additional
anchor. For readable links, replace `#c{element.data.uid}` in the menu partial
of your site package with:

```html
<a href="{page.link}#{ra:anchor(data: element.data)}">...</a>
```

## Extension configuration

Admin Tools → Settings → Extension Configuration → `hh_readable_anchor`

- **prefix** – optional prefix for all anchors (e.g. `sec-`)
- **reservedIds** – IDs used by your template itself (`navigation`, `main`, …);
  colliding anchors get a suffix (`navigation-2`)
- **maxLength** – maximum length of an anchor (default 80)

#### Preview images:
![example html frontend code output](.github/images/readable_anchor-example-code.jpg?raw=true "readable-anchor-code-example")
