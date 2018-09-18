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

// Custom CSS for plugin modal. This has to be hooked early.
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( $hook === 'plugin-install.php' && ! empty( $_GET['cac-pp'] ) ) {
		wp_enqueue_style( 'cac-plugin-packages-modal', CAC_PLUGIN_PACKAGES_URL . 'assets/plugin-modal.css', array(), '20180806' );
	}
} );