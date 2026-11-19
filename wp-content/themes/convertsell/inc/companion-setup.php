<?php
/**
 * Explicit companion setup and read-only environment checks.
 *
 * @package ConvertSell
 * @since 0.5.14
 */

defined( 'ABSPATH' ) || exit;

/** Recognize an existing legacy installation instead of installing a second copy. */
function convertsell_companion_file(): string {
	$plugins = get_plugins();
	$legacy  = 'purple-optimize-toolkit/purple-optimize-toolkit.php';
	if ( is_plugin_active( $legacy ) || ( isset( $plugins[ $legacy ] ) && ! isset( $plugins['convertsell-toolkit/convertsell-toolkit.php'] ) ) ) {
		return $legacy;
	}
	return 'convertsell-toolkit/convertsell-toolkit.php';
}

/** Return the tested minimums and installed versions without contacting a service. */
function convertsell_environment_checks(): array {
	$parent = wp_get_theme( get_template() );
	$child  = wp_get_theme();
	$checks = array(
		'wordpress' => array( 'label' => __( 'WordPress', 'convertsell' ), 'version' => get_bloginfo( 'version' ), 'minimum' => '7.1' ),
		'php'       => array( 'label' => __( 'PHP', 'convertsell' ), 'version' => PHP_VERSION, 'minimum' => '7.4' ),
		'parent'    => array( 'label' => __( 'Purple parent', 'convertsell' ), 'version' => $parent->get( 'Version' ), 'minimum' => '1.0.3', 'required' => false ),
		'child'     => array( 'label' => __( 'ConvertSell', 'convertsell' ), 'version' => $child->get( 'Version' ), 'minimum' => '0.5.14' ),
		'woo'       => array( 'label' => __( 'WooCommerce', 'convertsell' ), 'version' => defined( 'WC_VERSION' ) ? WC_VERSION : '', 'minimum' => '11.1' ),
	);
	foreach ( $checks as &$check ) {
		$check['ok'] = '' !== $check['version'] && version_compare( $check['version'], $check['minimum'], '>=' );
	}
	unset( $check );
	return $checks;
}

/** Only required checks block setup; the parent version is an advisory baseline. */
function convertsell_environment_ready( array $checks ): bool {
	foreach ( $checks as $check ) {
		if ( ( $check['required'] ?? true ) && ! $check['ok'] ) {
			return false;
		}
	}
	return true;
}

/** Register a setup destination even when the optional plugin is absent. */
function convertsell_setup_menu(): void {
	add_theme_page( __( 'ConvertSell setup', 'convertsell' ), __( 'ConvertSell setup', 'convertsell' ), 'edit_theme_options', 'convertsell-setup', 'convertsell_setup_page' );
}
add_action( 'admin_menu', 'convertsell_setup_menu' );

/** Show one persistent, dismissible-by-choice invitation until setup is visited. */
function convertsell_setup_notice(): void {
	if ( ! current_user_can( 'edit_theme_options' ) || get_user_meta( get_current_user_id(), 'purple_optimize_setup_seen', true ) ) {
		return;
	}
	printf( '<div class="notice notice-info"><p>%s <a href="%s">%s</a></p></div>', esc_html__( 'Complete your ConvertSell setup to check requirements and add optional conversion features.', 'convertsell' ), esc_url( admin_url( 'themes.php?page=convertsell-setup' ) ), esc_html__( 'Review setup', 'convertsell' ) );
}
add_action( 'admin_notices', 'convertsell_setup_notice' );

/** Render installation, activation, and recovery choices without automatic mutations. */
function convertsell_setup_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	// Visiting setup dismisses the invitation, not any feature or compatibility issue.
	update_user_meta( get_current_user_id(), 'purple_optimize_setup_seen', 1 );
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	$file      = convertsell_companion_file();
	$installed = isset( get_plugins()[ $file ] );
	$active    = is_plugin_active( $file );
	$checks    = convertsell_environment_checks();
	$toolkit_ok = ! $installed || version_compare( get_plugins()[ $file ]['Version'], '0.12.0', '>=' );
	$ready     = convertsell_environment_ready( $checks );
	?>
	<div class="wrap"><h1><?php esc_html_e( 'ConvertSell setup', 'convertsell' ); ?></h1>
	<p><?php esc_html_e( 'The theme provides your store layout. The optional toolkit adds conversion features and can be installed separately.', 'convertsell' ); ?></p>
	<?php if ( isset( $_GET['setup_error'] ) && is_string( $_GET['setup_error'] ) ) : ?>
	<div class="notice notice-error"><p><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['setup_error'] ) ) ); ?></p></div>
	<?php endif; ?>
	<h2><?php esc_html_e( 'Compatibility checks', 'convertsell' ); ?></h2>
	<p><?php esc_html_e( 'Checked automatically when this page loads. Purple 1.0.3 is the tested baseline; an older parent does not block companion installation. Verify storefront behavior when testing another parent version.', 'convertsell' ); ?></p>
	<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Component', 'convertsell' ); ?></th><th><?php esc_html_e( 'Installed', 'convertsell' ); ?></th><th><?php esc_html_e( 'Required / recommended', 'convertsell' ); ?></th><th><?php esc_html_e( 'Status', 'convertsell' ); ?></th></tr></thead><tbody>
	<?php foreach ( $checks as $check ) : ?>
	<tr><th><?php echo esc_html( $check['label'] ); ?></th><td><?php echo esc_html( $check['version'] ?: __( 'Not active', 'convertsell' ) ); ?></td><td><?php echo esc_html( $check['minimum'] . ( false === ( $check['required'] ?? true ) ? __( ' (recommended)', 'convertsell' ) : '' ) ); ?></td><td><?php echo esc_html( $check['ok'] ? __( 'Meets minimum', 'convertsell' ) : ( false === ( $check['required'] ?? true ) ? __( 'Older parent — testing allowed', 'convertsell' ) : __( 'Needs attention', 'convertsell' ) ) ); ?></td></tr>
	<?php endforeach; ?>
	<tr><th><?php esc_html_e( 'Companion toolkit', 'convertsell' ); ?></th><td><?php echo esc_html( $installed ? get_plugins()[ $file ]['Version'] : __( 'Not installed', 'convertsell' ) ); ?></td><td>0.12.0</td><td><?php echo esc_html( ! $toolkit_ok ? __( 'Update needed', 'convertsell' ) : ( $active ? __( 'Active', 'convertsell' ) : __( 'Optional setup', 'convertsell' ) ) ); ?></td></tr>
	</tbody></table>
	<h2><?php esc_html_e( 'Companion toolkit', 'convertsell' ); ?></h2>
	<?php if ( ! $toolkit_ok ) : ?><p class="notice notice-warning inline"><?php esc_html_e( 'Update the installed toolkit to 0.12.0 or newer using its ZIP before continuing. Your settings will be kept.', 'convertsell' ); ?></p>
	<?php elseif ( $active ) : ?>
	<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=convertsell' ) ); ?>"><?php esc_html_e( 'Open toolkit settings', 'convertsell' ); ?></a></p>
	<?php elseif ( $ready && current_user_can( 'activate_plugins' ) && ( $installed || current_user_can( 'install_plugins' ) ) && ! is_multisite() ) : ?>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
	<input type="hidden" name="action" value="convertsell_setup_companion"><?php wp_nonce_field( 'convertsell_setup_companion' ); ?>
	<?php submit_button( $installed ? __( 'Activate companion toolkit', 'convertsell' ) : __( 'Install and activate companion toolkit', 'convertsell' ), 'primary', 'submit', false ); ?>
	</form>
	<?php else : ?>
	<p><?php esc_html_e( 'Resolve the requirements above, or ask an administrator to install and activate the toolkit. On multisite, use Network Admin.', 'convertsell' ); ?></p>
	<?php endif; ?>
	<p><?php esc_html_e( 'For now, installation uses the versioned GitHub release ZIP. No plugin is downloaded until you choose to install it. WordPress.org distribution is planned; it is not listed there yet.', 'convertsell' ); ?></p>
	<p><a href="https://github.com/solstudioim/convertsell/releases"><?php esc_html_e( 'Download packages from GitHub releases', 'convertsell' ); ?></a>
	<?php if ( current_user_can( 'install_plugins' ) ) : ?> · <a href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=upload' ) ); ?>"><?php esc_html_e( 'Upload toolkit ZIP instead', 'convertsell' ); ?></a><?php endif; ?></p>
	<p><?php esc_html_e( 'If no release is published yet, build the separate ZIPs using the repository packaging script. Existing toolkit versions and settings are never overwritten by this setup action; update an installed copy using Plugins → Add New → Upload Plugin.', 'convertsell' ); ?></p></div>
	<?php
}

/** Install only the fixed release package, on an authorized POST, then verify activation. */
function convertsell_setup_companion(): void {
	if ( ! current_user_can( 'edit_theme_options' ) || ! current_user_can( 'activate_plugins' ) || is_multisite() ) {
		wp_die( esc_html__( 'You cannot install or activate the companion here.', 'convertsell' ), '', array( 'response' => 403 ) );
	}
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		wp_die( esc_html__( 'Use the setup form to continue.', 'convertsell' ), '', array( 'response' => 405 ) );
	}
	check_admin_referer( 'convertsell_setup_companion' );
	$redirect = admin_url( 'themes.php?page=convertsell-setup' );
	$checks   = convertsell_environment_checks();
	if ( ! convertsell_environment_ready( $checks ) ) {
		wp_safe_redirect( add_query_arg( 'setup_error', __( 'Resolve the compatibility requirements before activating the toolkit.', 'convertsell' ), $redirect ) );
		exit;
	}
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	$file = convertsell_companion_file();
	if ( ! isset( get_plugins()[ $file ] ) ) {
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_die( esc_html__( 'You cannot install plugins.', 'convertsell' ), '', array( 'response' => 403 ) );
		}
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( 'https://github.com/solstudioim/convertsell/releases/download/v0.7.0/convertsell-toolkit-0.12.0.zip' );
		if ( is_wp_error( $result ) || ! $result || is_wp_error( $skin->result ) || $skin->get_errors()->has_errors() ) {
			wp_safe_redirect( add_query_arg( 'setup_error', __( 'Installation could not finish. Check that the GitHub release package exists, retry, or upload the toolkit ZIP using Plugins → Add New.', 'convertsell' ), $redirect ) );
			exit;
		}
		wp_clean_plugins_cache();
	}
	$plugins = get_plugins();
	if ( isset( $plugins[ $file ] ) && version_compare( $plugins[ $file ]['Version'], '0.12.0', '<' ) ) {
		wp_safe_redirect( add_query_arg( 'setup_error', __( 'Update the installed toolkit ZIP to 0.12.0 or newer before activation.', 'convertsell' ), $redirect ) );
		exit;
	}
	$result = activate_plugin( $file );
	if ( is_wp_error( $result ) || ! is_plugin_active( $file ) ) {
		wp_safe_redirect( add_query_arg( 'setup_error', __( 'Activation did not finish. Review Plugins for details and retry; your settings have been kept.', 'convertsell' ), $redirect ) );
		exit;
	}
	wp_safe_redirect( admin_url( 'admin.php?page=convertsell' ) );
	exit;
}
add_action( 'admin_post_convertsell_setup_companion', 'convertsell_setup_companion' );
