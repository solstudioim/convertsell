=== ConvertSell Toolkit ===
Contributors: solstudioim
Tags: woocommerce, checkout, upsell, wishlist
Requires at least: 7.1
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 0.12.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add product guidance, wishlists, checkout add-ons, and optional upsell offers to WooCommerce.

== Description ==

ConvertSell Toolkit helps WooCommerce merchants make products easier to evaluate
and add relevant offers throughout the shopping journey. Manage product guidance,
browser wishlists, checkout add-ons, customer reviews, and optional upsell/downsell
offers from one settings workspace under WooCommerce > ConvertSell.

Features include:

* Storefront promotion notices, product search, and category navigation.
* Browser-local wishlists and saved-item counts.
* Product guidance, sale-price percentages, and scheduled-sale countdowns.
* Cart and checkout helpers, optional checkout add-ons, and merchant-authored helpful content.
* Optional upsell/downsell offers before checkout, within checkout, or after purchase.
* Approved WooCommerce product reviews in static lists or rotating displays.
* Optional recent-purchase proof, disabled by default.

Requires WordPress 7.1+, PHP 7.4+, and WooCommerce 11.1+.
Verified with Purple 1.1.0 on WooCommerce 11.2.1 and Gutenberg 24.3.0, both with
and without the optional ConvertSell 0.7.0 child theme, on WordPress 7.1.4.
The original baseline pairing is Purple 1.0.3 with ConvertSell 0.7.0.
These are tested versions, not a guarantee for all newer versions.
The child theme is not required to activate the plugin or access its settings.
Other themes require their own storefront placement and styling checks.

Feature switches let merchants configure each part of the shopping journey.
Fresh installations retain the existing feature defaults; offers and recent-purchase
proof are off by default. Existing saved choices are preserved during updates.
After-purchase offer acceptance starts a separate checkout and order; it never
silently recharges an existing order. No conversion-rate increase is guaranteed.

== Installation ==

1. Install and activate WooCommerce 11.1+ on WordPress 7.1+ and PHP 7.4+.
2. Upload the ConvertSell Toolkit ZIP under Plugins > Add New > Upload Plugin.
3. Activate the plugin, then open WooCommerce > ConvertSell.
4. Review the compatibility checks and settings before enabling optional offers.
5. Test product, wishlist, cart, checkout, and offer journeys with your active theme.

The plugin is packaged separately from the optional ConvertSell child theme.
Deactivate the legacy Purple Optimize Toolkit before activating ConvertSell Toolkit;
existing settings and published legacy shortcodes remain supported.

== Frequently Asked Questions ==

= Do I need the ConvertSell child theme? =
No. WooCommerce is the plugin dependency. Purple 1.1.0 has been tested with and
without the optional ConvertSell 0.7.0 child theme. Other themes require testing.

= Does it automatically install a theme or another plugin? =
No. Install WooCommerce separately. ConvertSell Toolkit does not install themes,
download executable code, or include a third-party background updater.

= Do version checks prove full compatibility? =
No. They check installed minimum versions locally. Verify your actual theme,
WooCommerce extensions, and shopper journeys before deploying changes.

= How are settings saved? =
Each section has an explicit save action; checkout add-ons use their own labeled
Save checkout features action. The offer enable/disable switch saves immediately
and restores the previous state if saving fails. Settings in other sections are retained.

= What happens to my data when I deactivate or delete the plugin? =
Settings and plugin-created content are retained. Wishlist items remain in the
shopper's browser storage until that storage is cleared. Deactivation stops plugin
features; deletion does not automatically remove settings or authored content.

== Screenshots ==

1. Example storefront using Purple and ConvertSell Toolkit, with the toolkit promotion notice, category navigation, and wishlist access. Store layout and catalog images come from the theme and demo content.
2. Product page with toolkit wishlist and product guidance. Purple supplies the layout; product reviews and purchase controls use WooCommerce.
3. Optional checkout add-ons and helpful checkout content, shown with Purple. Products are only added when the shopper chooses them.

== Privacy ==

The plugin does not send telemetry to an external analytics service. Settings,
checkout content, and wishlist-page references are stored in the site's database.
Enabling the wishlist creates or reuses a shortcode page without replacing
merchant-authored pages. Wishlist selections stay in the shopper's browser storage.
WooCommerce cart sessions retain offer decisions and accepted-offer information;
accepted products and discounts may also be recorded in WooCommerce orders.

Recent-purchase proof reads processing/completed WooCommerce orders. Both this
feature and customer-name display are off by default. Merchants should review
their privacy obligations before enabling customer-name display. Review displays
use approved WooCommerce product reviews and offer an anonymous-author option.

== Development ==

Source code and development tools:
https://github.com/solstudioim/convertsell

The package includes readable PHP, JavaScript, and CSS source files; no JavaScript
build step is required to use the plugin.

== Copyright ==

ConvertSell Toolkit, Copyright (C) 2026 Sol J.
Distributed under the GNU General Public License, version 2 or later.

Listing screenshots use Purple product and pattern images by Automattic,
licensed CC0 1.0 Universal. Source and image credits:
https://themes.svn.wordpress.org/purple/1.1.0/readme.txt
https://creativecommons.org/publicdomain/zero/1.0/

== Changelog ==

= 0.12.2 =
* Search partial SKUs through WooCommerce's product lookup table, retaining category scope.
* Cache review candidate IDs briefly and invalidate on review, rating and product changes.
* Filter the default navigation category after a bounded category fetch.
* Bound public search input length and preserve fresh shopper-specific prices and visibility.

= 0.12.1 =
* Prepare WooCommerce-first directory documentation, privacy details and screenshot captions.
* Sanitize offer inputs, escape product-image output and add translator context.
* Keep storefront callbacks unloaded when WooCommerce is missing or older than 11.1.
* Register the dependency-checked runtime before WooCommerce loads checkout integrations.

= 0.12.0 =
* Rename the companion to ConvertSell Toolkit and its distribution to convertsell-toolkit.
* Add convertsell_wishlist and convertsell_reviews shortcodes; retain published legacy aliases.
* Preserve existing settings, checkout documents, sessions, wishlist storage and checkout block names.
* Reject activation alongside the legacy toolkit to avoid loading the runtime twice.

= 0.11.10 =
* Match add-on card savings labels and regular-price comparisons with native WooCommerce order summaries.
* Use one combined percentage/currency label on add-on cards, with native Store API rounding and unchanged WooCommerce summary rendering.

= 0.11.9 =
* Add optional per-add-on percentage discounts; blank or zero preserves catalog pricing.
* Preserve accepted prices in cart sessions, native totals and order details.

= 0.11.8 =
* Replace the review navigation bar with subtle borderless arrows inside multi-review rotators.
* Keep accessible navigation labels, screen-reader position and optional pause control.

= 0.11.7 =
* Use a borderless, theme-tinted review card inside the existing checkout content frame.
* Retain the standalone review card outline.

= 0.11.6 =
* Rename Privacy to Social proof while retaining existing recent-purchase settings.
* Add native review selection, shortcode generation and preview with static/rotating layouts.
* Support the reviews shortcode in helpful checkout content without enabling unrelated shortcodes.

= 0.11.5 =
* Restore native mini-cart opening after add to cart for the ConvertSell child.
* Preserve explicit block behavior choices without replacing parent templates or styling.

= 0.11.4 =
* Add a native promotion background color picker with clear-to-parent fallback.
* Choose a contrasting text color automatically for custom backgrounds.

= 0.11.3 =
* Move wishlist access from the promotion notice to native header cart controls.
* Place category navigation below the header in the parent wide container.
* Align shipping progress with native cart/checkout containers and update checkout eligibility live.

= 0.11.2 =
* Scope offer decisions to cart/configuration for both guest and signed-in shoppers.
* Clear stale legacy suppression while respecting unchanged-cart decisions.
* Add a focused boxed upsell/downsell card using Purple design tokens.

= 0.11.1 =
* Keep existing CRO feature defaults while inheriting parent presentation.
* Restore wishlist top-bar access and browser-local saved-item count.
* Register checkout extensions before hydration on WooCommerce/Gutenberg stacks.
* Preserve configured add-ons, published helpful content and all offer placements.

= 0.10.0 =
* Add a responsive workspace sidebar and guided setup Overview.
* Add local compatibility checks for the combined theme and toolkit stack.
* Preserve unrelated settings on section saves and retain immediate offer switching.
* Add a catalog-backed saved offer preview and unsaved-edit protection.

= 0.9.0 =
* Add native WordPress block editing for helpful checkout content.
