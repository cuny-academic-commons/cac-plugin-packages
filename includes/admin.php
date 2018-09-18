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
		require __DIR__ . '/functions.php';
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

/**
 * Admin page renderer function callback.
 *
 * @since 0.1.0
 */
function cac_plugin_packages_admin_page() {
	require __DIR__ . '/admin-page.php';
}
