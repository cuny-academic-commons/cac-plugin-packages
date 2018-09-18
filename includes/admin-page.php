<?php
/**
 * Admin page output.
 *
 * @package plugin-packages
 */
?>

<div class="wrap">
	<h1><?php _e( 'Plugin Packages', 'cac-plugin-packages' ); ?></h1>

	<p>Lorem ipsum dolor sit amet, ornatus deterruisset cu per, te qui eros facilis.</p>

	<form method="post" action="<?php echo self_admin_url( 'plugins.php?page=cac-plugin-packages' ); ?>">
		<?php wp_nonce_field( 'cac_plugin_package_select' ); ?>

		<div class="wp-list-table widefat"><div id="the-list">

		<?php foreach ( cac_get_valid_plugin_packages() as $package => $args ) : ?>

			<div id="cac-plugin-package-<?php echo sanitize_html_class( $package ); ?>" class="plugin-card cac-plugin-package">
				<div class="plugin-card-top">
					<div class="name column-name">

						<h3><?php echo esc_attr( $args['name'] ); ?>

							<?php if ( 0 === strpos( $args['icon_url'], 'dashicons-' ) ) : ?>

								<span class="plugin-icon dashicons <?php echo esc_attr( $args['icon_url'] ); ?>" <?php echo $args['icon_background'] ? 'style="background-color:' . esc_attr( $args['icon_background'] ) . '"' : ''; ?>"></span>

							<?php else : ?>

								<img src="<?php echo ! empty( $args['icon_url'] ) ? esc_url( $args['icon_url'] ) : 'https://ui-avatars.com/api/?size=128&amp;font-size=0.75&amp;length=1&amp;name=' . $package; ?>" class="plugin-icon" alt="" />

							<?php endif; ?>
						</h3>
					</div>

					<div class="action-links">
						<ul class="plugin-action-buttons">
							<li><?php printf( '<button class="button" type="submit" name="package" value="%1$s" %2$s>%3$s</button>',
								esc_attr( $package ),
								cac_is_plugin_package_active( $package ) ? ' disabled="disabled"' : '',
																cac_is_plugin_package_active( $package ) ? esc_html__( 'Active', 'cac-plugin-packages' ) : esc_html__( 'Select', 'cac-plugin-packages' ) ); ?></li>

							<!--<li><a href="" class="thickbox open-plugin-details-modal" aria-label="<?php printf( esc_attr__( 'More information about %s', 'cac-plugin-packages' ), $args['name'] ); ?>" data-title="<?php echo esc_attr( $args['name'] ); ?>"><?php esc_html_e( 'More Details', 'cac-plugin-packages' ); ?></a></li>-->
						</ul>
					</div>

					<div class="desc column-description">
						<?php echo wp_kses_post( $args['description'] ); ?>

						<h4><?php esc_attr_e( 'Included Plugins', 'cac-plugin-packages' ); ?></h4>

						<ul>

						<?php foreach ( cac_get_plugins_for_package( $package ) as $slug => $r ) :
							// Custom documentation URL.
							if ( ! empty( $r['documentation_url' ] ) ) {
								$doc_url = $r['documentation_url'];
							// Use wp.org plugin data.
							} else {
								$doc_url = network_admin_url( 'plugin-install.php?tab=plugin-information&cac-pp=1&plugin=' . $slug );
							}

							// Show documentation in a thickbox modal.
							$doc_url = add_query_arg( array(
								'TB_iframe' => true,
								'width'     => 600,
								'height'    => 550
							), $doc_url );
						?>

							<li><strong><a class="thickbox open-plugin-details-modal" href="<?php echo $doc_url; ?>" data-title="<?php esc_html_e( $r['name'] ); ?>"><?php esc_html_e( $r['name'] ); ?></a></strong>
							<?php echo ! empty( $r['description'] ) ? ' &mdash; ' . wp_kses_post( $r['description'] ) : ''; ?>
							</li>

						<?php endforeach; ?>

						</ul>
					</div>
				</div>

				<div class="plugin-card-bottom">

					<?php if ( cac_is_plugin_package_active( $package ) && $inactive = cac_get_inactive_plugins_for_package( $package ) ) : ?>

						<div class="column-updated">
							<span class="update-now plugins-inactive">
								<?php printf( esc_html__( 'The following plugins are not currently active: %s.', 'cac-plugin-packages' ), wp_sprintf_l( '%l', wp_list_pluck( $inactive, 'name' ) ) ); ?>
								<?php printf( '<a href="%1$s">%2$s</a>', wp_nonce_url( self_admin_url( 'plugins.php?page=cac-plugin-packages&amp;activate-all=1&amp;package=' . $package ), 'cac-package-activate-all' ), esc_html__( '(Activate all)', 'cac-plugin-packages' ) ); ?>
							</span>
						</div>

					<?php endif; ?>

					<div class="column-compatibility">

						<?php if ( cac_is_plugin_package_active( $package ) ) : ?>

							<span class="compatibility-compatible">
								<?php if ( function_exists( 'bp_blogs_update_blogmeta' ) ) : ?>
									<?php printf( __( 'Activated on %s', 'cac-plugin-packages' ), date_i18n( get_option( 'date_format' ), (int) bp_blogs_get_blogmeta( get_current_blog_id(), "activated_plugin_package_{$package}" ) ) ); ?>
								<?php else : ?>
									<?php esc_html_e( 'Activated', 'cac-plugin-packages' ); ?>
								<?php endif; ?>
							</span>

						<?php else : ?>

							<span class="compatibility-untested">
								<?php esc_html_e( 'Available for activation.', 'cac-plugin-packages' ); ?>
							</span>

						<?php endif; ?>

					</div>
				</div>

			</div>

		<?php endforeach; ?>

		</div></div>
	</form>

</div>