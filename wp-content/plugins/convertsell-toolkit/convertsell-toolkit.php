<?php
/**
 * Plugin Name: ConvertSell Toolkit
 * Description: Add product guidance, wishlists, checkout add-ons, and optional upsell offers to WooCommerce.
 * Version: 0.12.2
 * Requires at least: 7.1
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 11.1
 * Plugin URI: https://github.com/solstudioim/convertsell
 * Author: Sol J
 * Author URI: https://github.com/solstudioim
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: convertsell-toolkit
 *
 * @package ConvertSellToolkit
 */

defined( 'ABSPATH' ) || exit;

// Both distributions use the same runtime and saved data. Never load them twice.
if ( defined( 'POT_VERSION' ) ) {
	register_activation_hook( __FILE__, static function () {
		wp_die( esc_html__( 'Deactivate Purple Optimize Toolkit before activating ConvertSell Toolkit. Your saved settings are retained.', 'convertsell-toolkit' ) );
	} );
	return;
}

define( 'POT_VERSION', '0.12.2' );
define( 'POT_FILE', __FILE__ );
define( 'POT_PATH', plugin_dir_path( __FILE__ ) );
define( 'POT_URL', plugin_dir_url( __FILE__ ) );

/** Keep missing or unsupported WooCommerce installs out of storefront callbacks. */
function convertsell_toolkit_dependency_ready(): bool {
	return class_exists( 'WooCommerce' ) && defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '11.1', '>=' );
}

/** Explain the dependency without blocking access to WordPress administration. */
function convertsell_toolkit_dependency_notice(): void {
	if ( current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'ConvertSell Toolkit needs WooCommerce 11.1 or newer. Install or update WooCommerce to enable toolkit features. Your settings are retained.', 'convertsell-toolkit' ) . '</p></div>';
	}
}

/** Wait until all plugin files load, regardless of alphabetical plugin order. */
function convertsell_toolkit_load(): void {
	if ( ! convertsell_toolkit_dependency_ready() ) {
		add_action( 'admin_notices', 'convertsell_toolkit_dependency_notice' );
		return;
	}
	require_once POT_PATH . 'includes/runtime.php';
}
// WooCommerce loads its Blocks package at plugins_loaded -1; register before it.
add_action( 'plugins_loaded', 'convertsell_toolkit_load', -20 );

// WordPress loads a newly activated plugin after plugins_loaded has already run.
if ( did_action( 'plugins_loaded' ) ) {
	convertsell_toolkit_load();
}

/** Validate dependencies during activation, then seed defaults without replacing data. */
function convertsell_toolkit_activate(): void {
	if ( ! convertsell_toolkit_dependency_ready() ) {
		wp_die( esc_html__( 'Install and activate WooCommerce 11.1 or newer before activating ConvertSell Toolkit.', 'convertsell-toolkit' ) );
	}
	convertsell_toolkit_load();
	pot_activate();
}
register_activation_hook( __FILE__, 'convertsell_toolkit_activate' );
