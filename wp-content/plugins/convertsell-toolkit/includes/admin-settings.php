<?php
/**
 * WooCommerce workspace settings and guided setup.
 *
 * @package ConvertSellToolkit
 * @since 0.10.0
 */

defined( 'ABSPATH' ) || exit;

/** Stable section keys are also the save allowlist. */
function pot_settings_sections(): array {
	return array(
		'overview'   => __( 'Overview', 'convertsell-toolkit' ),
		'storefront' => __( 'Storefront', 'convertsell-toolkit' ),
		'products'   => __( 'Product pages', 'convertsell-toolkit' ),
		'checkout'   => __( 'Cart & checkout', 'convertsell-toolkit' ),
		'offers'     => __( 'Offers', 'convertsell-toolkit' ),
		'privacy'    => __( 'Social proof', 'convertsell-toolkit' ),
	);
}

/** Return settings owned by one section; absent controls in other sections must survive. */
function pot_settings_section_keys( string $section ): array {
	$keys = array(
		'storefront' => array( 'promo_enabled', 'promo_text', 'promo_code', 'promo_background', 'instant_search', 'category_navigation', 'footer_policies', 'wishlist' ),
		'products'   => array( 'product_guidance', 'sticky_cart', 'sale_percentage', 'stock_threshold', 'reassurance_one', 'reassurance_two', 'reassurance_three' ),
		'checkout'   => array( 'checkout_labels', 'checkout_trust', 'post_purchase_account', 'mobile_sticky_checkout', 'free_shipping_threshold' ),
		'offers'     => array( 'offer_funnel', 'offer_placement', 'upsell_product_id', 'upsell_discount', 'upsell_countdown', 'downsell_product_id', 'downsell_discount', 'downsell_countdown' ),
		'privacy'    => array( 'recent_sales', 'social_proof_show_names', 'social_proof_days' ),
	);
	return $keys[ $section ] ?? array();
}

/** Scope only authenticated Settings API submissions; normal programmatic updates stay unchanged. */
function pot_scope_settings_input( array $input ): array {
	if ( ! isset( $_POST['pot_settings_section'] ) ) {
		return $input;
	}
	$section = is_string( $_POST['pot_settings_section'] ) ? sanitize_key( wp_unslash( $_POST['pot_settings_section'] ) ) : '';
	$nonce   = isset( $_POST['_wpnonce'] ) && is_string( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	$keys    = pot_settings_section_keys( $section );
	if ( ! $keys || ! current_user_can( 'manage_woocommerce' ) || ! wp_verify_nonce( $nonce, 'pot_settings_group-options' ) ) {
		add_settings_error( 'pot_settings', 'invalid_section', __( 'Settings were not saved. Reload the section and try again.', 'convertsell-toolkit' ) );
		return pot_settings();
	}
	$current = pot_settings();
	foreach ( $keys as $key ) {
		// Each key is rendered by this section; a missing checkbox means off.
		$current[ $key ] = $input[ $key ] ?? 0;
	}
	return $current;
}

/** Current, local requirements; never present a version check as a full compatibility certification. */
function pot_environment_checks(): array {
	if ( function_exists( 'convertsell_environment_checks' ) ) {
		$checks = convertsell_environment_checks();
	} else {
		$checks = array(
			'wordpress' => array( 'label' => __( 'WordPress', 'convertsell-toolkit' ), 'version' => get_bloginfo( 'version' ), 'minimum' => '7.1' ),
			'php'       => array( 'label' => __( 'PHP', 'convertsell-toolkit' ), 'version' => PHP_VERSION, 'minimum' => '7.4' ),
			'woo'       => array( 'label' => __( 'WooCommerce', 'convertsell-toolkit' ), 'version' => defined( 'WC_VERSION' ) ? WC_VERSION : '', 'minimum' => '11.1' ),
		);
	}
	$checks['toolkit'] = array( 'label' => __( 'Companion toolkit', 'convertsell-toolkit' ), 'version' => POT_VERSION, 'minimum' => '0.10.0' );
	foreach ( $checks as &$check ) {
		$check['ok'] = '' !== $check['version'] && version_compare( $check['version'], $check['minimum'], '>=' );
	}
	unset( $check );
	return $checks;
}

/** Build a bookmarkable section link. */
function pot_section_url( string $section ): string {
	return add_query_arg( array( 'page' => 'convertsell', 'section' => $section ), admin_url( 'admin.php' ) );
}

/** Render checks and setup actions using observed site state. */
function pot_render_overview(): void {
	$settings = pot_settings();
	?>
	<section class="pot-settings-card"><h2><?php esc_html_e( 'Compatibility checks', 'convertsell-toolkit' ); ?></h2>
	<p><?php esc_html_e( 'Checked automatically on each visit. Purple 1.0.3 is the tested baseline; an older parent does not block companion installation. Verify storefront behavior when testing another parent version.', 'convertsell-toolkit' ); ?></p>
	<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Component', 'convertsell-toolkit' ); ?></th><th><?php esc_html_e( 'Installed', 'convertsell-toolkit' ); ?></th><th><?php esc_html_e( 'Required / recommended', 'convertsell-toolkit' ); ?></th><th><?php esc_html_e( 'Status', 'convertsell-toolkit' ); ?></th></tr></thead><tbody>
	<?php foreach ( pot_environment_checks() as $check ) : ?>
	<tr><th scope="row"><?php echo esc_html( $check['label'] ); ?></th><td><?php echo esc_html( $check['version'] ?: __( 'Not active', 'convertsell-toolkit' ) ); ?></td><td><?php echo esc_html( $check['minimum'] . ( false === ( $check['required'] ?? true ) ? __( ' (recommended)', 'convertsell-toolkit' ) : '' ) ); ?></td><td><?php echo esc_html( $check['ok'] ? __( 'Meets minimum', 'convertsell-toolkit' ) : ( false === ( $check['required'] ?? true ) ? __( 'Older parent — testing allowed', 'convertsell-toolkit' ) : __( 'Needs attention', 'convertsell-toolkit' ) ) ); ?></td></tr>
	<?php endforeach; ?>
	</tbody></table><p><a class="button" href="<?php echo esc_url( pot_section_url( 'overview' ) ); ?>"><?php esc_html_e( 'Run checks again', 'convertsell-toolkit' ); ?></a></p>
	<?php if ( 'convertsell' !== get_stylesheet() ) : ?><p class="notice notice-warning inline"><?php esc_html_e( 'ConvertSell is not the active theme. Toolkit settings are retained; verify storefront behavior with your active theme.', 'convertsell-toolkit' ); ?></p><?php endif; ?>
	</section>
	<section class="pot-settings-card"><h2><?php esc_html_e( 'Your setup', 'convertsell-toolkit' ); ?></h2><p><?php esc_html_e( 'Review the essentials first. Offers and recent-purchase proof are optional.', 'convertsell-toolkit' ); ?></p>
	<?php foreach ( array( 'storefront' => __( 'Review promotion, search and policy links', 'convertsell-toolkit' ), 'products' => __( 'Review product guidance and purchase controls', 'convertsell-toolkit' ), 'checkout' => __( 'Review cart and checkout features', 'convertsell-toolkit' ), 'offers' => __( 'Configure optional upsell and downsell offers', 'convertsell-toolkit' ), 'privacy' => __( 'Configure recent purchases and review testimonials', 'convertsell-toolkit' ) ) as $key => $label ) : ?>
	<div class="pot-setup-row"><span><?php echo esc_html( $label ); ?></span><a class="button" href="<?php echo esc_url( pot_section_url( $key ) ); ?>"><?php esc_html_e( 'Review settings', 'convertsell-toolkit' ); ?></a></div>
	<?php endforeach; ?>
	<p class="description"><?php echo esc_html( ! empty( $settings['offer_funnel'] ) ? __( 'Offers are enabled. Review product availability before sharing your store.', 'convertsell-toolkit' ) : __( 'Offers are off. Enabling them is a separate choice.', 'convertsell-toolkit' ) ); ?></p></section>
	<?php
}

/** Render a standard labeled text or number field with native browser validation. */
function pot_workspace_field( string $key, string $label, array $settings, string $type = 'text', string $help = '', string $max = '' ): void {
	?>
	<tr><th scope="row"><label for="pot-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th><td><input class="regular-text" type="<?php echo esc_attr( $type ); ?>" id="pot-<?php echo esc_attr( $key ); ?>" name="pot_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $settings[ $key ] ); ?>" <?php if ( 'number' === $type ) : ?>min="0" step="<?php echo 'free_shipping_threshold' === $key ? '0.01' : '1'; ?>"<?php endif; ?> <?php if ( '' !== $max ) : ?>max="<?php echo esc_attr( $max ); ?>"<?php endif; ?> aria-describedby="pot-<?php echo esc_attr( $key ); ?>-help"><p class="description" id="pot-<?php echo esc_attr( $key ); ?>-help"><?php echo esc_html( $help ); ?></p></td></tr>
	<?php
}

/** Render the persistent sidebar and only the selected section's settings. */
function pot_render_workspace(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	$sections = pot_settings_sections();
	$section  = isset( $_GET['section'] ) && is_string( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : 'overview';
	$section  = isset( $sections[ $section ] ) ? $section : 'overview';
	$settings = pot_settings();
	?>
	<div class="wrap pot-admin-workspace"><header class="pot-workspace-header"><h1><?php esc_html_e( 'ConvertSell', 'convertsell-toolkit' ); ?></h1><p><?php esc_html_e( 'Conversion features for your WooCommerce store.', 'convertsell-toolkit' ); ?></p></header>
	<?php settings_errors(); ?>
	<div class="pot-workspace-layout"><nav class="pot-workspace-nav" aria-label="<?php esc_attr_e( 'ConvertSell settings', 'convertsell-toolkit' ); ?>">
	<?php foreach ( $sections as $key => $label ) : ?><a href="<?php echo esc_url( pot_section_url( $key ) ); ?>" <?php if ( $section === $key ) : ?>aria-current="page"<?php endif; ?>><?php echo esc_html( $label ); ?></a><?php endforeach; ?>
	<?php if ( current_user_can( 'edit_theme_options' ) ) : ?><a href="<?php echo esc_url( admin_url( 'site-editor.php' ) ); ?>"><?php esc_html_e( 'Edit store design ↗', 'convertsell-toolkit' ); ?></a><?php endif; ?></nav>
	<main class="pot-workspace-main" id="pot-workspace-main"><h2 class="pot-section-title"><?php echo esc_html( $sections[ $section ] ); ?></h2>
	<?php if ( 'overview' === $section ) : pot_render_overview(); ?>
	<?php elseif ( ! function_exists( 'wc_get_product' ) ) : ?><div class="notice notice-warning inline"><p><?php esc_html_e( 'Activate WooCommerce to configure storefront features. Your saved settings have been kept.', 'convertsell-toolkit' ); ?></p></div>
	<?php else : ?>
	<div class="pot-editor-layout<?php echo 'offers' === $section ? ' pot-has-preview' : ''; ?>"><div>
	<form method="post" action="options.php" id="pot-section-settings" data-pot-dirty-form>
	<?php settings_fields( 'pot_settings_group' ); ?>
	<input type="hidden" name="pot_settings_section" value="<?php echo esc_attr( $section ); ?>">
	<section class="pot-settings-card"><h3><?php echo esc_html( 'privacy' === $section ? __( 'Recent purchases', 'convertsell-toolkit' ) : $sections[ $section ] ); ?></h3><table class="form-table" role="presentation">
	<?php
	switch ( $section ) {
		case 'storefront':
			foreach ( array( 'promo_enabled' => __( 'Promotion strip', 'convertsell-toolkit' ), 'instant_search' => __( 'Instant product search', 'convertsell-toolkit' ), 'category_navigation' => __( 'Shop category navigation', 'convertsell-toolkit' ), 'footer_policies' => __( 'Footer policy links', 'convertsell-toolkit' ), 'wishlist' => __( 'Browser wishlist', 'convertsell-toolkit' ) ) as $key => $label ) { pot_checkbox_row( $key, $label, $settings ); }
			echo '<p class="description">' . esc_html__( 'Promotion strip adds a highlighted store notice. Browser wishlist adds a link beside the native header cart controls. Enabling wishlist creates or reuses a published page; saved items stay in the visitor’s browser.', 'convertsell-toolkit' ) . '</p>';
			?>
			<tr><th scope="row"><label for="pot-promo-background"><?php esc_html_e( 'Promotion background', 'convertsell-toolkit' ); ?></label></th><td>
			<input type="text" id="pot-promo-background" class="pot-color-picker" name="pot_settings[promo_background]" value="<?php echo esc_attr( $settings['promo_background'] ); ?>">
			<p class="description"><?php esc_html_e( 'Choose a custom background, or clear it to follow the parent theme accent. Text color adjusts automatically for contrast.', 'convertsell-toolkit' ); ?></p>
			</td></tr>
			<?php
			pot_workspace_field( 'promo_text', __( 'Promotion text', 'convertsell-toolkit' ), $settings );
			pot_workspace_field( 'promo_code', __( 'Coupon code', 'convertsell-toolkit' ), $settings, 'text', __( 'Optional. Use a real coupon configured in WooCommerce.', 'convertsell-toolkit' ) );
			break;
		case 'products':
			pot_checkbox_row( 'product_guidance', __( 'Product guidance: stock, countdown, reassurance and reviews', 'convertsell-toolkit' ), $settings );
			pot_checkbox_row( 'sticky_cart', __( 'Sticky add to cart', 'convertsell-toolkit' ), $settings );
			pot_checkbox_row( 'sale_percentage', __( 'Percentage sale badge', 'convertsell-toolkit' ), $settings );
			pot_workspace_field( 'stock_threshold', __( 'Low-stock threshold', 'convertsell-toolkit' ), $settings, 'number', __( 'Uses real managed stock. Set to 0 to hide low-stock guidance.', 'convertsell-toolkit' ), '100' );
			foreach ( array( 'reassurance_one', 'reassurance_two', 'reassurance_three' ) as $index => $key ) { pot_workspace_field( $key, sprintf( __( 'Reassurance point %d', 'convertsell-toolkit' ), $index + 1 ), $settings, 'text', __( 'Use accurate delivery, returns or support information.', 'convertsell-toolkit' ) ); }
			break;
		case 'checkout':
			pot_checkbox_row( 'checkout_labels', __( 'Required and optional checkout field labels', 'convertsell-toolkit' ), $settings );
			foreach ( array( 'checkout_trust' => __( 'Cart pre-checkout guidance', 'convertsell-toolkit' ), 'post_purchase_account' => __( 'Post-purchase account invitation', 'convertsell-toolkit' ), 'mobile_sticky_checkout' => __( 'Sticky native checkout actions on mobile', 'convertsell-toolkit' ) ) as $key => $label ) { pot_checkbox_row( $key, $label, $settings ); }
			pot_workspace_field( 'free_shipping_threshold', __( 'Free-shipping threshold', 'convertsell-toolkit' ), $settings, 'number', __( 'Match an actual shipping method. Set to 0 to hide progress; this does not configure shipping rates.', 'convertsell-toolkit' ) );
			break;
		case 'offers':
			require POT_PATH . 'includes/admin-offers.php';
			break;
		case 'privacy':
			pot_checkbox_row( 'recent_sales', __( 'Recent-purchase social proof', 'convertsell-toolkit' ), $settings );
			pot_checkbox_row( 'social_proof_show_names', __( 'Show customer first names', 'convertsell-toolkit' ), $settings );
			pot_workspace_field( 'social_proof_days', __( 'Recent-purchase window (days)', 'convertsell-toolkit' ), $settings, 'number', __( 'Uses processing and completed orders only. Keep names off unless your privacy policy and consent basis permit them.', 'convertsell-toolkit' ), '365' );
			break;
	}
	?>
	</table></section>
	<?php submit_button( __( 'Save changes', 'convertsell-toolkit' ) ); ?><p class="pot-save-status" role="status" aria-live="polite"></p>
	</form>
	<?php if ( 'privacy' === $section ) : require POT_PATH . 'includes/admin-reviews.php'; endif; ?>
	<?php if ( 'checkout' === $section ) : ?><section class="pot-settings-card"><?php pot_render_checkout_settings(); ?></section><?php endif; ?>
	</div>
	<?php if ( 'offers' === $section ) : pot_render_offer_preview( $settings ); endif; ?>
	</div>
	<?php endif; ?>
	</main></div></div>
	<?php
}

/** Catalog-backed preview, explicitly based on saved settings. */
function pot_render_offer_preview( array $settings ): void {
	$id      = absint( $settings['upsell_product_id'] );
	$product = $id ? wc_get_product( $id ) : false;
	$issue   = pot_offer_product_issue( $id, true );
	?>
	<aside class="pot-settings-card pot-offer-preview" aria-label="<?php esc_attr_e( 'Saved offer preview', 'convertsell-toolkit' ); ?>"><h3><?php esc_html_e( 'Saved offer preview', 'convertsell-toolkit' ); ?></h3><p class="description"><?php esc_html_e( 'Save changes to refresh this preview. Storefront layout follows your active theme.', 'convertsell-toolkit' ); ?></p>
	<?php if ( $issue ) : ?><p><?php echo esc_html( pot_offer_product_issue_message( $issue ) ); ?></p>
	<?php elseif ( $product ) : ?>
	<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); ?><h4><?php echo esc_html( $product->get_name() ); ?></h4>
	<p><?php echo wp_kses_post( wc_price( wc_get_price_to_display( $product, array( 'price' => (float) $product->get_price() * ( 1 - (float) $settings['upsell_discount'] / 100 ) ) ) ) ); ?></p>
	<p><?php echo esc_html( ! empty( $settings['offer_funnel'] ) ? __( 'Offers enabled', 'convertsell-toolkit' ) : __( 'Offers off', 'convertsell-toolkit' ) ); ?></p>
	<?php endif; ?></aside>
	<?php
}
