<?php
/** Exercise the real bootstrap with a previously loaded legacy runtime. */
define( 'ABSPATH', __DIR__ . '/' );
define( 'POT_VERSION', '0.11.10' );
$activation = null;
function register_activation_hook( $file, $callback ) { $GLOBALS['activation'] = $callback; }
function esc_html__( $text, $domain ) { return $text; }
function wp_die( $message ) { throw new RuntimeException( $message ); }
require dirname( __DIR__ ) . '/wp-content/plugins/convertsell-toolkit/convertsell-toolkit.php';
if ( '0.11.10' !== POT_VERSION || defined( 'POT_FILE' ) || function_exists( 'pot_activate' ) || ! is_callable( $activation ) ) {
	throw new RuntimeException( 'Duplicate bootstrap must stop before declaring the runtime.' );
}
try {
	$activation();
} catch ( RuntimeException $error ) {
	if ( false !== strpos( $error->getMessage(), 'Deactivate Purple Optimize Toolkit' ) ) {
		echo "PASS: duplicate runtime loading is prevented and activation explains the migration.\n";
		return;
	}
	throw $error;
}
throw new RuntimeException( 'Activating a duplicate runtime must fail.' );
