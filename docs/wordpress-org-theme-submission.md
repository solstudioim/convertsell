# WordPress.org theme submission preparation

## Package scope

Submit only the `convertsell` child-theme directory. Do not include the
Purple parent theme, ConvertSell Toolkit, demo tools, repository metadata,
performance captures, or environment files.

Build the ZIP from a committed release after creating and checking the resolved
`/Users/sol/Codex/artifacts/convertsell/` destination:

```sh
git archive --format=zip \
  --prefix=convertsell/ \
  -o /Users/sol/Codex/artifacts/convertsell/convertsell-0.7.0.zip \
  HEAD:wp-content/themes/convertsell
```

## Upstream and licensing

- Parent: [Woo Purple](https://github.com/woocommerce/woocommerce/tree/trunk/themes/purple)
- Parent author: Automattic
- Parent license: GPLv2 or later
- Child license: GPLv2 or later
- The child theme does not bundle parent assets or the companion plugin.
- The screenshot is 1200 by 900 pixels and is declared CC0 in `readme.txt`.

## Automated and manual checks

- Confirm `style.css`, `templates/index.html`, `theme.json`, `readme.txt`, and
  `screenshot.png` are present in the ZIP.
- Confirm the ZIP has one top-level `convertsell/` directory.
- Run the current Theme Check plugin against the release candidate.
- Test with the official Theme Unit Test content.
- Test activation with Purple installed and with WooCommerce both active and
  inactive; the admin must not show PHP errors or warnings.
- Verify storefront, Site Editor, keyboard navigation, narrow layouts, RTL, and
  translation strings.
- Confirm the WordPress.org contributor username in `readme.txt` before upload.

## Directory submission checks

- Parent Purple is listed at https://wordpress.org/themes/purple/.
- Submit under the distinct name **ConvertSell**, slug/text domain `convertsell`.
  Keep `Template: purple` and parent attribution intact.
- ConvertSell name/slug acceptance and trademark clearance are not established by
  this repository rename. Check availability and similar marks before submission.
- The review handbook's draft guidance discourages the parent's name in a child
  name; the required naming rules leave similarity decisions to the Themes Team.
- Do not claim Theme Directory approval until the actual package is accepted.

The current GitHub distribution includes an explicit companion installer. Before
Theme Directory submission, replace that recommendation/installer with the
approved WordPress.org plugin slug. Keep the plugin outside the child-theme ZIP.
