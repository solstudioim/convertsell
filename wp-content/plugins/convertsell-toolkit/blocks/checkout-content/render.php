<?php
/**
 * Native Checkout hydration placeholder for sanitized merchant content.
 *
 * @package ConvertSellToolkit
 * @since 0.8.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div <?php // Core escapes every attribute value before returning the attribute list.
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo get_block_wrapper_attributes( array( 'data-block-name' => 'purple-optimize/checkout-content' ) ); ?>></div>
