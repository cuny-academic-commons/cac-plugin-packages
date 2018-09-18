<?php
/**
 * Code hooked to admin "Plugins" page.
 *
 * @package plugin-packages
 */

// Displays our package tags in the plugin list table.
add_filter( 'plugin_row_meta', function( $plugin_meta, $plugin_file, $plugin_data, $status ) {
	if ( ! empty( $plugin_data['slug'] ) ) {
		$slug = $plugin_data['slug'];
	} else {
		$parts = explode( '/', $plugin_file );
		$slug  = $parts[0];
	}

	foreach ( cac_get_plugin_packages_for_plugin( $slug ) as $package_id ) {
		$plugin_meta[] = cac_get_plugin_package_tag( $package_id );
	}

	return $plugin_meta;
}, 10, 4 );
