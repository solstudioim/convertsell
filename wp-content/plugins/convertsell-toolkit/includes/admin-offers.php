<?php
/** Offer controls, rendered inside the workspace Settings API form. @package ConvertSellToolkit */
defined( 'ABSPATH' ) || exit;
?>

				<tr>
					<th scope="row"><label for="pot-offer-enabled"><?php esc_html_e( 'Enable upsell / downsell', 'convertsell-toolkit' ); ?></label></th>
					<td>
						<label><input type="checkbox" id="pot-offer-enabled" name="pot_settings[offer_funnel]" value="1" <?php checked( ! empty( $settings['offer_funnel'] ) ); ?> aria-describedby="pot-offer-toggle-help pot-offer-toggle-status"> <?php esc_html_e( 'Show upsell and downsell offers', 'convertsell-toolkit' ); ?></label>
						<p class="description"><?php esc_html_e( 'Accepted or declined offers do not repeat for an unchanged cart and offer configuration. Changed carts or offers are reconsidered for guests and signed-in shoppers. Products already in the cart are not offered again.', 'convertsell-toolkit' ); ?></p>
						<p class="description" id="pot-offer-toggle-help"><?php esc_html_e( 'Saves immediately when switched on or off. Applies to all offer placements; your products, discounts, and checkout add-ons stay unchanged.', 'convertsell-toolkit' ); ?></p>
						<p id="pot-offer-toggle-status" role="status" aria-live="polite"><?php echo esc_html( ! empty( $settings['offer_funnel'] ) ? __( 'Enabled', 'convertsell-toolkit' ) : __( 'Disabled', 'convertsell-toolkit' ) ); ?></p>
						<noscript><p><?php esc_html_e( 'JavaScript is unavailable. Use Save Changes below to save this checkbox.', 'convertsell-toolkit' ); ?></p></noscript>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="pot-offer-placement"><?php esc_html_e( 'Offer placement', 'convertsell-toolkit' ); ?></label></th>
					<td>
						<select id="pot-offer-placement" name="pot_settings[offer_placement]">
							<option value="pre_checkout" <?php selected( 'pre_checkout', $settings['offer_placement'] ); ?>><?php esc_html_e( 'Full page before checkout', 'convertsell-toolkit' ); ?></option>
							<option value="checkout_inline" <?php selected( 'checkout_inline', $settings['offer_placement'] ); ?>><?php esc_html_e( 'Inline before Place Order', 'convertsell-toolkit' ); ?></option>
							<option value="post_purchase" <?php selected( 'post_purchase', $settings['offer_placement'] ); ?>><?php esc_html_e( 'After purchase follow-up', 'convertsell-toolkit' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'After-purchase acceptance starts a separate checkout and order; it never silently changes or recharges the completed order.', 'convertsell-toolkit' ); ?></p>
					</td>
				</tr>
				<?php pot_product_select_row( 'upsell_product_id', __( 'Upsell product', 'convertsell-toolkit' ), $settings, true ); ?>
				<tr><th scope="row"><label for="pot-upsell-discount"><?php esc_html_e( 'Upsell discount', 'convertsell-toolkit' ); ?></label></th><td><input type="number" min="0" max="100" id="pot-upsell-discount" name="pot_settings[upsell_discount]" value="<?php echo esc_attr( (string) $settings['upsell_discount'] ); ?>">%</td></tr>
				<tr><th scope="row"><label for="pot-upsell-countdown"><?php esc_html_e( 'Upsell countdown', 'convertsell-toolkit' ); ?></label></th><td><input type="number" min="0" max="1440" id="pot-upsell-countdown" name="pot_settings[upsell_countdown]" value="<?php echo esc_attr( (string) $settings['upsell_countdown'] ); ?>"> <?php esc_html_e( 'minutes', 'convertsell-toolkit' ); ?><p class="description"><?php esc_html_e( 'Set to 0 to disable. Refreshing does not restart the timer.', 'convertsell-toolkit' ); ?></p></td></tr>
				<?php pot_product_select_row( 'downsell_product_id', __( 'Downsell product', 'convertsell-toolkit' ), $settings ); ?>
				<tr><th scope="row"><label for="pot-downsell-discount"><?php esc_html_e( 'Downsell discount', 'convertsell-toolkit' ); ?></label></th><td><input type="number" min="0" max="100" id="pot-downsell-discount" name="pot_settings[downsell_discount]" value="<?php echo esc_attr( (string) $settings['downsell_discount'] ); ?>">%</td></tr>
				<tr><th scope="row"><label for="pot-downsell-countdown"><?php esc_html_e( 'Downsell countdown', 'convertsell-toolkit' ); ?></label></th><td><input type="number" min="0" max="1440" id="pot-downsell-countdown" name="pot_settings[downsell_countdown]" value="<?php echo esc_attr( (string) $settings['downsell_countdown'] ); ?>"> <?php esc_html_e( 'minutes', 'convertsell-toolkit' ); ?><p class="description"><?php esc_html_e( 'Set to 0 to disable. Use a duration the store will actually honor.', 'convertsell-toolkit' ); ?></p></td></tr>
