<?php
/**
 * Code hooked to admin "Plugins" page.
 *
 * @package plugin-packages
 */

// Set up package tags for display.
add_action( 'after_plugin_row', function( $plugin_file, $plugin_data ) {
	if ( ! empty( $plugin_data['slug'] ) ) {
		$slug = $plugin_data['slug'];
	} else {
		$parts = explode( '/', $plugin_file );
		$slug  = $parts[0];
	}

	$tags = array();
	foreach ( cac_get_plugin_packages_for_plugin( $slug ) as $package_id ) {
		if ( empty( $tags[$slug] ) ) {
			$tags[$slug] = array();
		}
		$tags[$slug][] = cac_get_plugin_package_tag( $package_id );
	}

	if ( ! empty( $tags ) ) {
		foreach ( $tags as $pslug => $tag ) {
			echo sprintf( '<div id="%1$s" class="plugin-packages-row" style="display:none">%2$s</div>', $pslug, sprintf( __( 'Plugin packages: %s', 'cac-plugin-packages' ), implode( ' ', $tag ) ) );
		}
	}
}, 10, 2 );

// Append our package tags row inside the plugin description's container.
add_action( 'pre_current_active_plugins', function() {
	$retval = <<<VAL

<script>
jQuery( function($) {
	$('.plugin-packages-row').each(function(i) {
		var slug = $(this).attr('id');
		$('tr[data-plugin^="' + slug + '/"] .column-description').append( $(this).show() );
	});
} )
</script>

VAL;

	echo $retval;
} );