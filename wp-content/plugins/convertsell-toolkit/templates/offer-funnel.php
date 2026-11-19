<?php
/**
 * Standalone pre-checkout offer template.
 *
 * @package ConvertSellToolkit
 */

defined( 'ABSPATH' ) || exit;

$context = $GLOBALS['pot_offer_context'] ?? array();
$product = $context['product'] ?? false;
if ( ! $product instanceof WC_Product ) {
	wp_safe_redirect( wc_get_checkout_url() );
	exit;
}

$step       = (string) $context['step'];
$discount   = (int) $context['discount'];
$is_downsell = 'downsell' === $step;
$is_post_purchase = 'post_purchase' === ( $context['context'] ?? '' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'pot-offer-funnel-page' ); ?>>
	<?php wp_body_open(); ?>
	<header class="pot-offer-header">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
		<span><?php echo $is_post_purchase ? esc_html__( 'Order complete', 'convertsell-toolkit' ) : esc_html( sprintf( /* translators: %d: cart item count. */ _n( '%d item in your cart', '%d items in your cart', WC()->cart->get_cart_contents_count(), 'convertsell-toolkit' ), WC()->cart->get_cart_contents_count() ) ); ?></span>
	</header>
	<main class="pot-offer-shell">
		<section class="pot-offer-card" aria-labelledby="pot-offer-title">
			<div class="pot-offer-media"><?php echo wp_kses_post( $product->get_image( 'woocommerce_single', array( 'loading' => 'eager' ) ) ); ?></div>
			<div class="pot-offer-copy">
				<span class="pot-offer-eyebrow"><?php echo esc_html( $is_downsell ? __( 'One last option', 'convertsell-toolkit' ) : ( $is_post_purchase ? __( 'Post-purchase offer', 'convertsell-toolkit' ) : __( 'Checkout-only offer', 'convertsell-toolkit' ) ) ); ?></span>
				<h1 id="pot-offer-title"><?php echo esc_html( $is_downsell ? __( 'Would you prefer this offer?', 'convertsell-toolkit' ) : __( 'Would you like to add this?', 'convertsell-toolkit' ) ); ?></h1>
				<h2><?php echo esc_html( $product->get_name() ); ?></h2>
				<div class="pot-offer-description"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
				<p class="pot-offer-price">
					<del><?php echo wp_kses_post( wc_price( (float) $context['original_price'] ) ); ?></del>
					<ins><?php echo wp_kses_post( wc_price( (float) $context['discounted_price'] ) ); ?></ins>
					<strong><?php echo esc_html( sprintf( /* translators: %d: offer discount percentage. */ __( '%d%% off on this page', 'convertsell-toolkit' ), $discount ) ); ?></strong>
				</p>
				<?php if ( ! empty( $context['expiry'] ) ) : ?>
				<p class="pot-offer-timer" data-offer-expiry="<?php echo esc_attr( (string) ( (int) $context['expiry'] * 1000 ) ); ?>">
					<span><?php esc_html_e( 'This offer ends in', 'convertsell-toolkit' ); ?></span>
					<strong data-offer-countdown role="timer"></strong>
				</p>
				<?php endif; ?>
				<?php wc_print_notices(); ?>
				<form class="pot-offer-actions" method="post">
					<?php wp_nonce_field( 'pot_offer_' . $step, 'pot_offer_nonce' ); ?>
					<button class="pot-offer-accept wp-element-button" type="submit" name="pot_offer_action" value="accept"><?php echo esc_html( $is_post_purchase ? __( 'Yes, add this in a new order', 'convertsell-toolkit' ) : ( $is_downsell ? __( 'Yes, add this and continue', 'convertsell-toolkit' ) : __( 'Yes, add this to my order', 'convertsell-toolkit' ) ) ); ?></button>
					<button class="pot-offer-reject wp-element-button" type="submit" name="pot_offer_action" value="reject"><?php echo esc_html( $is_post_purchase ? __( 'No thanks, view my receipt', 'convertsell-toolkit' ) : ( $is_downsell ? __( 'No thanks, continue to checkout', 'convertsell-toolkit' ) : __( 'No thanks', 'convertsell-toolkit' ) ) ); ?></button>
				</form>
				<p class="pot-offer-fine-print"><?php echo esc_html( $is_post_purchase ? __( 'Your completed order is unchanged. Accepting starts a separate checkout and creates a separate order.', 'convertsell-toolkit' ) : __( 'Your existing cart is unchanged unless you accept. Payment is completed securely on the next checkout page.', 'convertsell-toolkit' ) ); ?></p>
			</div>
		</section>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
