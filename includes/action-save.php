<?php
/**
 * Save package routine.
 *
 * Loaded when a POST submission occurs from our main admin page.
 *
 * @package plugin-packages
 */

check_admin_referer( 'cac_plugin_package_select' );

$plugins = cac_get_plugin_loaders_for_package( $_POST['package'] );

if ( ! empty( $plugins ) ) {
	$package_id = $_POST['package'];

	// Activate plugins.
	activate_plugins( $plugins );

	// Save marker.
	$packages = get_option( 'cac_plugin_packages', array() );
	if ( ! in_array( $package_id, $packages ) ) {
		$packages[] = $package_id;
		update_option( 'cac_plugin_packages', $packages, 'no' );

		// Site-wide package tracking. BuddyPress-only.
		if ( function_exists( 'bp_blogs_update_blogmeta' ) ) {
			bp_blogs_update_blogmeta( get_current_blog_id(), "activated_plugin_package_{$package_id}", time() );
		}

		/**
		 * Do something when a package is added to a site.
		 *
		 * @since 0.1.0
		 *
		 * @param string $package_id Package ID.
		 */
		do_action( 'cac_plugin_package_added', $package_id );
	}

	// Notice.
	$notice = sprintf( __( 'Plugins successfully activated for the <strong>%1$s</strong> package', 'cac-plugin-packages' ), esc_attr( cac_get_plugin_package_prop( 'name', $package_id ) ) );

	add_action( 'admin_notices', function() use ( $notice ) {
		echo '<div class="updated"><p>' . $notice . '</p></div>';
	} );

// @todo Error notice.
} else {
}