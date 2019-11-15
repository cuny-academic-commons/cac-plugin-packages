<?php
/**
 * Admin code.
 *
 * @package plugin-packages
 */

// Register the "Plugin Packages" page.
$page = add_plugins_page(
	__( 'Plugin Packages', 'cac-plugin-packages' ),
	__( 'Plugin Packages', 'cac-plugin-packages' ),
	'activate_plugins',
	'cac-plugin-packages',
	'cac_plugin_packages_admin_page'
);

// Load required functions only on certain pages.
foreach ( array( $page, 'plugins.php', 'index.php' ) as $p ) {
	add_action( "load-{$p}", function() {
		require_once __DIR__ . '/functions.php';
	} );
}

// Save action routine.
add_action( "load-{$page}", function() {
	// Save package routine.
	if ( ! empty( $_POST ) ) {
		require __DIR__ . '/action-save.php';
	
	// Activate all routine.
	} elseif ( ! empty( $_GET['activate-all'] ) ) {
		require __DIR__ . '/action-activate-all.php';
	}
} );

// Hook into dashboard page for custom widget.
add_action( 'load-index.php', function() {
	require __DIR__ . '/hook-dashboard.php';
} );

// Hook into main "Plugins" page for package tag support.
add_action( 'load-plugins.php', function() {
	require __DIR__ . '/hook-plugins.php';
} );

// Admin page enqueues.
add_action( 'admin_enqueue_scripts', function( $hook ) use ( $page ) {
	// Custom CSS for package tags.
	if ( $hook === 'plugins.php' || $hook === 'index.php' ) {
		wp_enqueue_style( 'cac-plugin-packages-tags', CAC_PLUGIN_PACKAGES_URL . 'assets/package-tags.css', array(), '20180806' );
	}

	if ( $hook !== $page ) {
		return;
	}

	// Admin page assets.
	wp_enqueue_script( 'plugin-install' );
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );
	wp_enqueue_style( 'cac-plugin-packages-page', CAC_PLUGIN_PACKAGES_URL . 'assets/admin-page.css', array(), '20180806' );
} );

// Custom plugin modal. Piggybacks off install_plugin_information().
add_action( 'admin_init', function() {
	// Bail if our GET parameters are missing.
	if ( empty( $_GET['cac-pp'] ) || empty( $_GET['plugin'] ) || empty( $_GET['package'] ) || ! is_user_logged_in() ) {
		return;
	}

	// Require our functions.
	require_once __DIR__ . '/functions.php';

	/*
	 * Check to see if the requested plugin is a part of the package.
	 *
	 * This prevents snooping on plugins outside of our package spec.
	 */
	$package  = $_GET['package'];
	$plugin   = $_GET['plugin'];
	$packages = cac_get_plugin_packages();
	if ( ! isset( $packages[ $package ] ) || ! isset( $packages[ $package ]['plugins'][ $plugin ] ) ) {
		wp_die( 'Invalid plugin' );
	}

	// Enqueue our CSS.
	add_action( 'admin_enqueue_scripts', function() {
		wp_enqueue_style( 'cac-plugin-packages-modal', CAC_PLUGIN_PACKAGES_URL . 'assets/plugin-modal.css', array(), '20180806' );
	} );

	// iframe_header() requires these globals set.
	$GLOBALS['tab'] = $GLOBALS['body_id'] = 'plugin-information';

	// Require our needed function.
	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

	install_plugin_information( $plugin );
	die();
} );

/**
 * Admin page renderer function callback.
 *
 * @since 0.1.0
 */
function cac_plugin_packages_admin_page() {
	require __DIR__ . '/admin-page.php';
}
