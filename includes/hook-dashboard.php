<?php
/**
 * Code hooked to admin dashboard page.
 *
 * @package plugin-packages
 */

/**
 * Add a "Plugin Packages" widget to the dashboard.
 *
 * This widget only shows if at least one plugin package is activated.
 *
 * @since 0.1.0
 */
function cac_add_plugin_packages_dashboard_widget() {
	$packages = cac_get_activated_plugin_packages();
	if ( empty( $packages ) ) {
		return;
	}

	wp_add_dashboard_widget(
                 'cac_plugin_packages',
                 __( 'Plugin Packages', 'cac-plugin-packages' ),
                 'cac_plugin_packages_dashboard_widget'
        );
}
add_action( 'wp_dashboard_setup', 'cac_add_plugin_packages_dashboard_widget' );

/**
 * Output dashboard widget contents.
 *
 * @since 0.1.0
 */
function cac_plugin_packages_dashboard_widget() {
	esc_html_e( 'The following plugin packages are active on this site: ', 'cac-plugin-packages' );

	$packages      = cac_get_activated_plugin_packages();
	$inactive_flag = false;

	foreach ( $packages as $package_id ) {
		echo cac_get_plugin_package_tag( $package_id );

		// Check for inactive plugins.
		if ( false === $inactive_flag && cac_get_inactive_plugins_for_package( $package_id ) ) {
			$inactive_flag = true;
		}
	}

	if ( $inactive_flag ) {
		printf( '<p>%s</p>', esc_html__( 'Some plugins are inactive. Please click on a package tag above to review these plugins.', 'cac-plugin-packages' ) );
	}
}