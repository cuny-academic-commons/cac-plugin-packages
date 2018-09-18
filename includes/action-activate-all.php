<?php
/**
 * Activate all plugins routine.
 *
 * Loaded when an admin clicks on the "Activate all" text-link.
 *
 * @package plugin-packages
 */

check_admin_referer( 'cac-package-activate-all' );

$inactive = cac_get_inactive_plugins_for_package( $_GET['package'] );

if ( ! empty( $inactive ) ) {
	// Activate all plugins.
	activate_plugins( cac_get_plugin_loaders_for_package( $_GET['package'] ) );

	// Notice.
	$notice = sprintf( __( 'The following plugins were successfully activated for the <strong>%1$s</strong> package: %2$s', 'cac-plugin-packages' ), esc_attr( cac_get_plugin_package_prop( 'name', $_GET['package'] ) ), wp_sprintf_l( '%l', wp_list_pluck( $inactive, 'name' ) ) );

	add_action( 'admin_notices', function() use ( $notice ) {
		echo '<div class="updated"><p>' . $notice . '</p></div>';
	} );

// @todo Error notice.
} else {
}