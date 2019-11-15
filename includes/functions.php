<?php
/**
 * Core functions.
 *
 * @package plugin-packages
 */

/**
 * Returns an array of activated plugin package IDs for a site.
 *
 * @since 0.1.0
 *
 * @param  int $site_id Site ID to return activated plugin package IDs for. Defaults to current site.
 * @return array
 */
function cac_get_activated_plugin_packages( $site_id = 0 ) {
	return get_blog_option( $site_id, 'cac_plugin_packages', array() );
}

/**
 * Determine if a package is active on a site.
 *
 * @param  string $package_id Package ID to check for.
 * @param  int    $site_id    Site ID to check for. Defaults to current site.
 * @return bool
 */
function cac_is_plugin_package_active( $package_id = '', $site_id = 0 ) {
	if ( empty( $package_id ) ) {
		return false;
	}

	$packages = cac_get_activated_plugin_packages( $site_id );
	if ( in_array( $package_id, $packages ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Whether to only show installed plugins in a package.
 *
 * @since 0.1.0
 *
 * @return bool
 */
function cac_show_installed_package_plugins_only() {
	/**
	 * Whether to only show installed plugins in a package.
	 *
	 * Set this to true if you use a version control system like Git to manage
	 * your WordPress install.  In a future release, when this is set to false,
	 * we'll auto-install missing plugins.
	 *
	 * @since 0.1.0
	 *
	 * @param  bool $retval Defaults to true.
	 * @return bool
	 */
	$installed_only = apply_filters( 'cac_show_installed_package_plugins_only', true );

	return $installed_only;
}

/**
 * Get plugin metadata for a specific package.
 *
 * This doesn't check active plugin status.
 *
 * @since 0.1.0
 *
 * @param  string $package_id Plugin package ID.
 * @return array Array of plugin data for the package.
 */
function cac_get_plugins_for_package( $package_id = '' ) {
	static $packages = array();

	// We've already done this before.
	if ( isset( $packages[ $package_id ] ) ) {
		return $packages[ $package_id ];
	}

	$ps = cac_get_plugin_packages();
	if ( ! isset( $ps[ $package_id ] ) ) {
		return array();
	}

	$plugins = (array) $ps[ $package_id ]['plugins'];

	if ( cac_show_installed_package_plugins_only() ) {
		foreach ( $plugins as $plugin_slug => $data ) {
			if ( ! cac_is_package_plugin_installed( $plugin_slug ) ) {
				unset( $plugins[ $plugin_slug ] );
			}
		}
	}

	// Save for later.
	$packages[ $package_id ] = $plugins;

	return $plugins;
}

/**
 * Returns metadata for inactive plugins in a package.
 *
 * @param  string $package_id Package ID
 * @return array()
 */
function cac_get_inactive_plugins_for_package( $package_id = '' ) {
	$plugins = cac_get_plugins_for_package( $package_id );

	$active_plugins = get_option( 'active_plugins' );
	$active_slugs   = array();
	foreach ( $active_plugins as $plugin ) {
		$active_slugs[] = substr( $plugin, 0, strpos( $plugin, '/' ) );
	}

	foreach ( $plugins as $plugin_slug => $data ) {
		if ( in_array( $plugin_slug, $active_slugs ) ) {
			unset( $plugins[ $plugin_slug ] );
		}
	}

	return $plugins;
}

/**
 * Returns an array of plugin basenames for a package.
 *
 * @param  string $package_id Package ID.
 * @return array
 */
function cac_get_plugin_loaders_for_package( $package_id = '' ) {
	$ps = cac_get_plugin_packages();
	if ( ! isset( $ps[ $package_id ] ) ) {
		return array();
	}

	$all_plugins = get_plugins();
	$plugins = (array) $ps[ $package_id ]['plugins'];
	$retval = array();

	foreach ( $all_plugins as $file => $data ) {
		foreach ( $plugins as $plugin_slug => $d ) {
			if ( 0 === strpos( $file, $plugin_slug . '/' ) ) {
				$retval[] = $file;
			}
		}
	}

	return $retval;
}

/**
 * Return a list of package IDs matching a plugin.
 *
 * @since 0.1.0
 *
 * @param string $plugin_slug Plugin slug to check.
 * @return array
 */
function cac_get_plugin_packages_for_plugin( $plugin_slug = '' ) {
	$matches = array();

	$packages = cac_get_valid_plugin_packages();
	foreach ( $packages as $package_id => $data ) {
		$plugins = cac_get_plugins_for_package( $package_id );

		foreach ( $plugins as $slug => $d ) {
			if ( $slug === $plugin_slug ) {
				$matches[] = $package_id;
			}
		}
	}

	return $matches;
}

/**
 * Check to see if a plugin is installed by plugin slug.
 *
 * @since 0.1.0
 *
 * @param  string $plugin_slug Plugin slug.
 * @return bool
 */
function cac_is_package_plugin_installed( $plugin_slug = '' ) {
	$plugins = get_plugins();
	foreach ( $plugins as $file => $data ) {
		if ( 0 === strpos( $file, $plugin_slug . '/' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get a property for a package.
 *
 * @since 0.1.0
 *
 * @param  string $prop Property name.
 * @param  string $package_id Package ID.
 * @return mixed|bool Boolean false on failure.
 */
function cac_get_plugin_package_prop( $prop = '', $package_id = '' ) {
	$packages = cac_get_valid_plugin_packages();
	if ( ! isset( $packages[ $package_id ] ) || ! isset( $packages[ $package_id ][ $prop ] ) ) {
		return false;
	}

	return $packages[ $package_id ][ $prop ];
}

/**
 * Get valid plugin packages.
 *
 * This function does checks to see if plugin packages are only allowed to
 * show installed plugins. If a package has no valid plugins, then the package
 * is removed. If the installed plugin check is disabled, then this function
 * is essentially the same as {@link cac_get_plugin_packages()}.
 *
 * @since 0.1.0
 *
 * @return array Plugin package data.
 */
function cac_get_valid_plugin_packages() {
	static $packages = null;

	// We've already done this before.
	if ( ! is_null( $packages ) ) {
		return $packages;
	}

	$packages = cac_get_plugin_packages();

	if ( cac_show_installed_package_plugins_only() ) {
		foreach ( $packages as $package_id => $data ) {
			if ( ! cac_get_plugins_for_package( $package_id ) ) {
				unset( $packages[ $package_id ] );
			}
		}
	}

	return $packages;
}

/**
 * Get raw, plugin package data.
 *
 * Hardcoded until I write an API for this.
 *
 * @since 0.1.0
 *
 * @return array
 */
function cac_get_plugin_packages() {
	$packages = array(
		'teaching' => array(
			'name'            => 'Teaching',
			'description'     => 'Contains plugins teachers commonly use to streamline organization and management on their course site.',
			'icon_url'        => 'dashicons-welcome-learn-more',
			'icon_background' => 'green',
			'plugins' => array(
				'authors' => array(
					'name'         => 'Authors Widget',
					'description'  => 'Creates a widget to list users designated as "authors" (students) in the sidebar of the site; when "author name" is clicked, it links to a list of all posts by that author.',
					'download_url' => 'http://downloads.wordpress.org/plugin/authors.zip',
				),
				'category-sticky-post' => array(
					'name'         => 'Category Sticky Post',
					'description'  => 'Allows a single post from a category to "stick" to the top of the category archive.',
					'download_url' => 'http://downloads.wordpress.org/plugin/category-sticky-post.2.10.2.zip',
				),
				'hypothesis' => array(
					'name'         => 'Hypothesis',
					'description'  => 'Create an annotation sidebar on posts, pages, and PDFs (on public sites).',
					'download_url' => 'http://downloads.wordpress.org/plugin/hypothesis.0.5.0.zip',
				),
				'google-docs-shortcode' => array(
					'name'         => 'Google Docs Shortcode',
					'description'  => 'Allows administrators to embed and display Google Drive contents such as docs, sheets, and slides directly on their Commons site.',
					'download_url' => 'http://downloads.wordpress.org/plugin/google-docs-shortcode.0.4.zip',
				),
				'imsanity' => array(
					'name'         => 'Imsanity',
					'description'  => 'Limits the file size for uploaded photos, preserving space on your site.',
					'download_url' => 'http://downloads.wordpress.org/plugin/imsanity.2.4.0.zip',
				),
				'reckoning' => array(
					'name'         => 'Reckoning',
					'description'  => 'Organizes, counts, and displays student posts and comments for assessment.',
					'download_url' => 'http://downloads.wordpress.org/plugin/reckoning.zip',
				),
				'wp-accessibility' => array(
					'name'         => 'WP Accessibility',
					'description'  => 'Helps improve accessibility in your WordPress site.',
					'download_url' => 'http://downloads.wordpress.org/plugin/wp-accessibility.1.6.4.zip',
				),
				'wp-grade-comments' => array(
					'name'         => 'WP Grade Comments',
					'description'  => "Allows instructors to provide private feedback on posts in the form of comments that are visible only to instructor and the student author. Private comments also offer the option to include numerical feedback on students' posts.",
					'download_url' => 'http://downloads.wordpress.org/plugin/wp-grade-comments.1.3.1.zip',
				),
			)
		),
		'multimedia' => array(
			'name'            => 'Multimedia',
			'description'     => '',
			'icon_url'        => 'dashicons-video-alt3',
			'icon_background' => 'red',
			'plugins' => array(
				'nextgen-gallery' => array(
					'name'         => 'NextGEN Gallery',
					'description'  => 'Allows users to collect and display images in a gallery (thumbnail, slideshow, list).',
					'download_url' => 'http://downloads.wordpress.org/plugin/nextgen-gallery.3.0.6.zip',
				),
				'embed-google-map' => array(
					'name'         => 'Embed Google Map',
					'description'  => 'Allows users to display maps created using "My Maps" in Google Maps.',
					'download_url' => 'http://downloads.wordpress.org/plugin/embed-google-map.3.2.zip',
				),
				'imsanity' => array(
					'name'         => 'Imsanity',
					'description'  => 'Limits the file size for uploaded photos, preserving space on your site.',
					'download_url' => 'http://downloads.wordpress.org/plugin/imsanity.2.4.0.zip',
				),
				'knight-lab-timelinejs' => array(
					'name'         => 'Knight Lab Timeline',
					'description'  => 'Allows users to embed <a href="https://timeline.knightlab.com/" target="_blank">Timeline JS</a> timelines into a post on the Commons (must have free but separate TimelineJS/Google account).',
					'download_url' => 'http://downloads.wordpress.org/plugin/knight-lab-timelinejs.3.6.0.0.zip',
				),
				'youtube-embed-plus' => array(
					'name'         => 'YouTube Embed Plus',
					'description'  => 'Enhanced embedding options for YouTube videos.',
					'download_url' => 'http://downloads.wordpress.org/plugin/youtube-embed-plus.12.0.1.zip',
				),
			)
		),
		'digital' => array(
			'name'            => 'Digital Tools',
			'description'     => 'Adds a suite of commonly-used digital tools to your course site.',
			'icon_url'        => 'dashicons-desktop',
			'icon_background' => 'darkblue',
			'plugins' => array(
				'anthologize' => array(
					'name'         => 'Anthologize',
					'description'  => 'Allow users to "export" (by tag or category) and "publish" posts in a book-like format as a PDFs, ePub and other file formats.',
					'download_url' => 'http://downloads.wordpress.org/plugin/nextgen-gallery.3.0.6.zip',
				),
				'feedwordpress' => array(
					'name'         => 'FeedWordPress',
					'description'  => 'Allows admins to collect posts from other sites on the Commons into a singular "motherblog" roll.',
					'download_url' => 'http://downloads.wordpress.org/plugin/feedwordpress.2017.1020.zip',
				),
				'embed-google-map' => array(
					'name'         => 'Embed Google Map',
					'description'  => 'Allows users to display maps created using "My Maps" in Google Maps.',
					'download_url' => 'http://downloads.wordpress.org/plugin/embed-google-map.3.2.zip',
				),
				'google-docs-shortcode' => array(
					'name'         => 'Google Docs Shortcode',
					'description'  => 'Allows administrators to embed and display Google Drive contents such as docs, sheets, and slides directly on their Commons site.',
					'download_url' => 'http://downloads.wordpress.org/plugin/google-docs-shortcode.0.4.zip',
				),
				'hypothesis' => array(
					'name'         => 'Hypothesis',
					'description'  => 'Create an annotation sidebar on posts, pages, and PDFs (on public sites).',
					'download_url' => 'http://downloads.wordpress.org/plugin/hypothesis.0.5.0.zip',
				),
				'knight-lab-timelinejs' => array(
					'name'         => 'Knight Lab Timeline',
					'description'  => 'Allows users to embed <a href="https://timeline.knightlab.com/" target="_blank">Timeline JS</a> timelines into a post on the Commons (must have free but separate TimelineJS/Google account).',
					'download_url' => 'http://downloads.wordpress.org/plugin/knight-lab-timelinejs.3.6.0.0.zip',
				),
			)
		)
	);

	/**
	 * Filters the current plugin packages.
	 *
	 * @since 0.1.0
	 *
	 * @param array $packages
	 */
	$packages = apply_filters( 'cac_plugin_packages', $packages );

	return $packages;
}

/**
 * Returns HTML markup for a package tag.
 *
 * @since 0.1.0
 *
 * @param  string $package_id Package ID.
 * @return string
 */
function cac_get_plugin_package_tag( $package_id = '' ) {
	$icon_background = cac_get_plugin_package_prop( 'icon_background', $package_id );
	if ( empty( $icon_background ) ) {
		return '';
	}

	return sprintf( '<span class="package-tag" style="background-color:%1$s"><a href="%2$s">%3$s</a></span>',
		esc_attr( cac_get_plugin_package_prop( 'icon_background', $package_id ) ),
		self_admin_url( 'plugins.php?page=cac-plugin-packages#cac-plugin-package-' . $package_id ),
		esc_html( cac_get_plugin_package_prop( 'name', $package_id ) )
	);
}
