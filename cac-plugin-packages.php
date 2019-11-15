<?php
/*
Plugin Name: Plugin Packages
Plugin URI: https://dev.commons.gc.cuny.edu
Description: View and install packages of plugins easily. Multisite-only.
Version: 0.1-alpha
Author: CUNY Academic Commons
Author URI: http://commons.gc.cuny.edu
Licence: GPLv3
Network: true
*/

// Some pertinent defines.
define( 'CAC_PLUGIN_PACKAGES_URL', plugins_url( basename( __DIR__ ) ) . '/' );

// Sub-site admin code.
add_action( 'admin_menu', function() {
	/**
	 * Should we load the admin code for Plugin Packages?
	 *
	 * TODO: Maybe set to false if a site isn't OER-enabled. Whatever that means...
	 *
	 * @since 0.1.0
	 *
	 * @param  bool $retval Defaults to true.
	 * @return bool
	 */
	$should_load = apply_filters( 'cac_plugin_package_load_admin', true );

	if ( $should_load ) {
		require __DIR__ . '/includes/admin.php';
	}
} );