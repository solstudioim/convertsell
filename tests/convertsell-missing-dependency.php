<?php
/** Exercise the real bootstrap when WooCommerce is absent. */
define( 'ABSPATH', __DIR__ . '/' );
$hooks = array();
$activation = null;
function plugin_dir_path( $file ) { return dirname( $file ) . '/'; }
function plugin_dir_url( $file ) { return 'https://example.test/plugin/'; }
function add_action( $hook, $callback, $priority = 10 ) { $GLOBALS['hooks'][ $hook ][] = $callback; }
function did_action( $hook ) { return 0; }
function register_activation_hook( $file, $callback ) { $GLOBALS['activation'] = $callback; }
function current_user_can( $capability ) { return true; }
function esc_html__( $text, $domain ) { return htmlspecialchars( $text ); }
function wp_die( $message ) { throw new RuntimeException( $message ); }
require dirname( __DIR__ ) . '/wp-content/plugins/convertsell-toolkit/convertsell-toolkit.php';
convertsell_toolkit_load();
if ( function_exists( 'pot_activate' ) || isset( $hooks['wp_footer'] ) || convertsell_toolkit_dependency_ready() ) {
	throw new RuntimeException( 'Missing WooCommerce must not load storefront callbacks.' );
}
ob_start();
convertsell_toolkit_dependency_notice();
$notice = ob_get_clean();
if ( false === strpos( $notice, 'Your settings are retained' ) ) { throw new RuntimeException( 'Explain recovery and retention.' ); }
try { $activation(); } catch ( RuntimeException $error ) {
	if ( false !== strpos( $error->getMessage(), 'Install and activate WooCommerce' ) ) {
		echo "PASS: missing dependency keeps storefront callbacks unloaded, explains recovery and rejects activation.\n";
		return;
	}
	throw $error;
}
throw new RuntimeException( 'Activation without WooCommerce must fail.' );
