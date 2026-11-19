<?php
/**
 * Plugin Name: ConvertSell Toolkit
 * Description: Lightweight, evidence-based conversion features for WooCommerce and the ConvertSell child theme.
 * Version: 0.12.0
 * Requires at least: 7.1
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 11.1
 * Plugin URI: https://github.com/solstudioim/convertsell
 * Author: Sol J
 * Author URI: https://github.com/solstudioim
 * License: GPL-2.0-or-later
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

define( 'POT_VERSION', '0.12.0' );
define( 'POT_FILE', __FILE__ );
define( 'POT_PATH', plugin_dir_path( __FILE__ ) );
define( 'POT_URL', plugin_dir_url( __FILE__ ) );

require_once POT_PATH . 'includes/runtime.php';
