<?php
/**
 * Lewis Edward — theme bootstrap.
 *
 * Loads the modular includes that make up the theme. Each concern lives in its
 * own file under /inc so this bootstrap stays a simple, ordered manifest.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Theme constants.
 */
define( 'LE_VERSION', '1.0.0' );
define( 'LE_DIR', get_template_directory() );
define( 'LE_URI', get_template_directory_uri() );

/**
 * Load a theme include from /inc, failing loudly in debug if it is missing.
 *
 * @param string $file Relative path inside /inc without extension.
 */
function le_require( $file ) {
	$path = LE_DIR . '/inc/' . $file . '.php';
	if ( file_exists( $path ) ) {
		require_once $path;
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		trigger_error( sprintf( 'Lewis Edward: missing include %s', esc_html( $file ) ), E_USER_WARNING );
	}
}

/**
 * Ordered manifest of theme modules.
 */
le_require( 'setup' );        // Theme supports, menus, image sizes.
le_require( 'enqueue' );      // Styles & scripts.
le_require( 'post-types' );   // Custom post types.
le_require( 'taxonomies' );   // Custom taxonomies.
le_require( 'nav-walker' );   // Mega-menu walker for the primary nav.
le_require( 'menu-seed' );    // One-time default Primary menu seeding.
le_require( 'acf' );          // ACF JSON sync, options page, helpers.
le_require( 'template-tags' );// Presentation helpers used across templates.
le_require( 'journal' );      // Journal (Posts) index/archive helpers.
le_require( 'about-icons' );  // About page inline SVG icon set.
le_require( 'svg-support' );  // Safe SVG uploads (sanitised).
le_require( 'redirects' );    // Legacy 301 redirects (SEO preservation).
