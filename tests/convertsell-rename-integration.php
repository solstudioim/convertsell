<?php
/** Verify real saved-data behavior on the isolated combined WordPress stack. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { exit; }
$expect = static function ( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } };
$expect( 'convertsell' === get_stylesheet() && 'purple' === get_template(), 'Activate ConvertSell with Purple.' );
$expect( is_plugin_active( 'convertsell-toolkit/convertsell-toolkit.php' ), 'Activate ConvertSell Toolkit.' );
$keys = array( 'pot_settings', 'pot_checkout_settings', 'pot_checkout_content_id', 'pot_wishlist_page_id' );
$original = array();
foreach ( $keys as $key ) { $original[ $key ] = get_option( $key, null ); }
$user = get_current_user_id();
wp_set_current_user( 0 ); // Keep the opt-in page provisioner out of this activation check.
try {
	$settings = pot_settings();
	$settings['promo_text'] = 'Existing merchant promotion';
	$settings['wishlist'] = 0;
	$settings['upsell_discount'] = 17;
	update_option( 'pot_settings', $settings );
	$checkout = array( 'upsells_enabled' => 1, 'content_enabled' => 1, 'upsells' => array( array( 'product_id' => 123, 'discount' => 9 ) ) );
	update_option( 'pot_checkout_settings', $checkout );
	update_option( 'pot_checkout_content_id', 456 );
	update_option( 'pot_wishlist_page_id', 789 );
	$before = array();
	foreach ( $keys as $key ) { $before[ $key ] = get_option( $key ); }
	pot_activate();
	foreach ( $before as $key => $value ) { $expect( $value === get_option( $key ), 'Activation changed saved ' . $key ); }
	$expect( 'Existing merchant promotion' === pot_settings()['promo_text'], 'Runtime must read legacy saved settings.' );
	$expect( 17 === pot_settings()['upsell_discount'], 'Saved offer pricing must survive.' );
	$expect( do_shortcode( '[convertsell_wishlist]' ) === do_shortcode( '[purple_optimize_wishlist]' ), 'Wishlist aliases must render identically.' );
	$expect( pot_has_wishlist_shortcode( '[convertsell_wishlist]' ) && pot_has_wishlist_shortcode( '[purple_optimize_wishlist]' ), 'Recognize both wishlist identities.' );
	$expect( shortcode_exists( 'convertsell_reviews' ) && shortcode_exists( 'purple_optimize_reviews' ), 'Register new and legacy review aliases.' );
	$shortcode = pot_reviews_build_shortcode( pot_reviews_config( array() ) );
	$expect( 0 === strpos( $shortcode, '[convertsell_reviews' ), 'Generate the new public shortcode.' );
	foreach ( array( 'checkout-content', 'checkout-upsells' ) as $block ) {
		$expect( WP_Block_Type_Registry::get_instance()->is_registered( 'purple-optimize/' . $block ), 'Saved checkout block must remain registered: ' . $block );
	}
	WP_CLI::success( 'ConvertSell activation preserves saved promotion, offer and checkout data; new and legacy shortcodes and saved block identities resolve.' );
} finally {
	foreach ( $original as $key => $value ) {
		if ( null === $value ) { delete_option( $key ); } else { update_option( $key, $value ); }
	}
	wp_set_current_user( $user );
}
