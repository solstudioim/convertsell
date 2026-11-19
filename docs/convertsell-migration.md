# ConvertSell identity and migration

The project is now **ConvertSell**. The child theme is **ConvertSell**
(`convertsell`), and the optional plugin is **ConvertSell Toolkit**
(`convertsell-toolkit/convertsell-toolkit.php`). Purple remains the parent;
`Template: purple` and its upstream attribution are unchanged.

Repository: https://github.com/solstudioim/convertsell

## Fresh installations

Install Purple, install the `convertsell` theme ZIP, then install the separate
`convertsell-toolkit` plugin ZIP. Activate the theme and toolkit. Open
**WooCommerce → ConvertSell** to review optional conversion features.

## Existing Purple Optimize installations

Changing a theme or plugin directory is a migration, not an in-place ZIP update.
Prepare and verify it on a staging copy before updating a live store.

1. Back up the database, old theme/plugin files, and Site Editor customizations.
2. Install the new packages separately. Deactivate **Purple Optimize Toolkit**
   before activating **ConvertSell Toolkit**; do not run both copies together.
   The new toolkit rejects activation when the legacy runtime is already loaded.
3. Existing toolkit options, checkout content IDs, offers, carts, browser wishlist
   storage, and published shortcodes continue to use their original identifiers.
   Do not delete or rename database options or rewrite saved shopper state.
4. Before activating the renamed theme, account for theme-specific Site Editor
   templates, template parts, global styles, menu assignments and theme mods.
   WordPress associates these with a theme slug. This release does not copy or
   reassign those records automatically. Export and transfer the intended
   customizations to ConvertSell on staging, then verify them before rollout.
   The originals remain attached to the old theme for rollback.
5. Activate ConvertSell after preparing those customizations. Verify promotion,
   wishlist, search, mini-cart, product forms, cart, checkout content/add-ons and
   all enabled offer placements for guest and signed-in shoppers.
6. Update bookmarks to `wp-admin/admin.php?page=convertsell` and setup links to
   `wp-admin/themes.php?page=convertsell-setup`.

The child setup recognizes the old plugin and asks for a ZIP update instead of
silently installing a second toolkit. It does not migrate an active installation.
Keep the original packages available for rollback.

## Compatibility identifiers

New content can use `[convertsell_wishlist]` and `[convertsell_reviews]`.
The original `[purple_optimize_wishlist]` and `[purple_optimize_reviews]`
remain aliases, including the reviews allowed inside helpful checkout content.
Existing wishlist pages are reused rather than replaced.

The `pot_` PHP/data prefix, `POT_` constants, `pot-*` DOM hooks, checkout extension
namespace, browser storage keys and `purple-optimize/checkout-*` block names are
stable compatibility identifiers. They are deliberately not renamed. The new
text domains are `convertsell` and `convertsell-toolkit`.

Old design plans and measurement captures remain historical records; their old
names, URLs and local paths do not identify the current distribution. The local
repository directory and existing `.test` hostname may also keep their old names.
No source rename deploys a merchant store or publishes a release ZIP.
