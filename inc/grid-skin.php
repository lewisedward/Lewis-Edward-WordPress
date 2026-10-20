<?php
/**
 * Grid skin — a trial homepage layout in the hairline-grid style.
 *
 * Staging shows it by default; everywhere else it is off. ?skin=grid turns it
 * on for a request, ?skin=classic turns it off, so both can be compared on the
 * same host. When active the header, footer and front page swap to the parts
 * in template-parts/grid/, and assets/css/grid.css is inlined after theme.css.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is the grid skin active for this request?
 *
 * @return bool
 */
function le_grid_skin() {
	static $on = null;
	if ( null !== $on ) {
		return $on;
	}
	$q = isset( $_GET['skin'] ) ? sanitize_key( wp_unslash( $_GET['skin'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( 'grid' === $q ) {
		$on = true;
	} elseif ( 'classic' === $q ) {
		$on = false;
	} else {
		$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$on   = ( 0 === strpos( $host, 'staging.' ) );
	}
	return $on;
}

add_filter( 'body_class', function ( $classes ) {
	if ( le_grid_skin() ) {
		$classes[] = 'skin-grid';
	}
	return $classes;
} );

/**
 * Inline the skin stylesheet straight after theme.css (which prints at priority 2).
 */
add_action( 'wp_head', function () {
	if ( ! le_grid_skin() ) {
		return;
	}
	$path = LE_DIR . '/assets/css/grid.css';
	if ( ! file_exists( $path ) ) {
		return;
	}
	$css = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$css = preg_replace_callback(
		'#url\((\'|"|)\.\./([^\'")]+)\1\)#',
		static function ( $m ) {
			$rel = 'assets/' . $m[2];
			return 'url(' . $m[1] . LE_URI . '/' . $rel . '?ver=' . le_asset_ver( $rel ) . $m[1] . ')';
		},
		$css
	);
	echo "<style id=\"le-grid-css\">\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}, 3 );

/**
 * Small helpers shared by the grid parts.
 */
function le_grid_arrow() {
	return '<svg class="g-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
}
function le_grid_arrow_diag() {
	return '<svg class="g-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>';
}
