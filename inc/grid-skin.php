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
 * Geist + Geist Mono (SIL Open Font License, variable 100–900), served from
 * the theme. Printed just before grid.css, which sets the families.
 */
add_action( 'wp_head', function () {
	if ( ! le_grid_skin() ) {
		return;
	}
	$css = '';
	foreach ( array( 'Geist' => 'geist', 'Geist Mono' => 'geist-mono' ) as $fam => $file ) {
		$rel  = 'assets/fonts/geist/' . $file . '-latin-wght-normal.woff2';
		$css .= "@font-face{font-family:'$fam';src:url('" . esc_url( LE_URI . '/' . $rel . '?ver=' . le_asset_ver( $rel ) ) . "') format('woff2');font-weight:100 900;font-style:normal;font-display:swap}\n";
	}
	echo "<style id=\"le-grid-fonts\">\n" . $css . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}, 3 );

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
 * Grid skin behaviours (reveal, counters, in-view video).
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! le_grid_skin() ) {
		return;
	}
	wp_enqueue_script( 'le-grid', LE_URI . '/assets/js/grid.js', array(), le_asset_ver( 'assets/js/grid.js' ), true );
}, 20 );

/**
 * Small helpers shared by the grid parts.
 */
function le_grid_arrow() {
	return '<svg class="g-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
}
function le_grid_arrow_diag() {
	return '<svg class="g-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>';
}

/**
 * A client logo URL for the grid skin: when the theme carries a hand-made mono
 * version (assets/images/clients/<file>-mono.svg), use that instead of the
 * uploaded original, which may not survive the one-colour filter.
 *
 * @param string $url Uploaded logo URL.
 * @return string
 */
function le_grid_logo( $url ) {
	$base = pathinfo( (string) wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_FILENAME );
	$base = preg_replace( '/-(scaled|\d+x\d+)$/', '', strtolower( $base ) );
	$rel  = 'assets/images/clients/' . $base . '-mono.svg';
	if ( $base && file_exists( LE_DIR . '/' . $rel ) ) {
		return LE_URI . '/' . $rel . '?ver=' . le_asset_ver( $rel );
	}
	return $url;
}

/**
 * A line-drawn graphic for a service card, picked from the service's slug and
 * title (falling back to the card's position). Hairline strokes, no fills,
 * 120 x 120 — the same vocabulary as the hero blooms and halftone.
 *
 * @param string $hint Slug + title to match on.
 * @param int    $i    Card index, used when nothing matches.
 * @return string SVG markup.
 */
function le_grid_wire( $hint, $i = 0 ) {
	$hint = strtolower( (string) $hint );
	$keys = array( 'ai-' => 'lattice', ' ai' => 'lattice', 'intelligen' => 'lattice', 'partner' => 'overlap', 'agency' => 'overlap', 'commerce' => 'stack', 'shop' => 'stack', 'support' => 'monitor', 'maint' => 'monitor', 'design' => 'compose', 'develop' => 'build' ); // first match wins, so the specific ones go first
	$kind = '';
	foreach ( $keys as $needle => $k ) {
		if ( false !== strpos( ' ' . $hint, $needle ) ) {
			$kind = $k;
			break;
		}
	}
	$order = array( 'compose', 'build', 'monitor', 'overlap', 'stack', 'lattice' );
	if ( ! $kind ) {
		$kind = $order[ (int) $i % count( $order ) ];
	}
	$open = '<svg class="g-wire g-wire--' . $kind . '" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">';
	switch ( $kind ) {
		case 'compose': // layout grid with a circle and a diagonal: composition.
			$body = '<rect x="10.5" y="10.5" width="99" height="99"/><path d="M43.5 10.5v99M76.5 10.5v99M10.5 43.5h99M10.5 76.5h99"/><circle cx="60" cy="60" r="33"/><path d="M10.5 109.5 109.5 10.5"/><circle class="g-wire__dot" cx="76.5" cy="43.5" r="2.5"/>';
			break;
		case 'build': // isometric wireframe cube with an inset cube: built from scratch.
			$body = '<path d="M60 12 102 36v48L60 108 18 84V36z"/><path d="M60 60 18 36M60 60l42-24M60 60v48"/><path d="M60 36 81 48v24L60 84 39 72V48z"/><path d="M60 60 39 48M60 60l21-12M60 60v24"/><circle class="g-wire__dot" cx="60" cy="12" r="2.5"/>';
			break;
		case 'monitor': // uptime pulse inside a ring: proactive support.
			$ticks = '';
			for ( $t = 0; $t < 36; $t++ ) {
				$a  = $t * 10;
				$r1 = ( 0 === $t % 9 ) ? 44 : 47.5;
				$ticks .= sprintf( 'M%.1f %.1fL%.1f %.1f', 60 + $r1 * cos( deg2rad( $a ) ), 60 + $r1 * sin( deg2rad( $a ) ), 60 + 50 * cos( deg2rad( $a ) ), 60 + 50 * sin( deg2rad( $a ) ) );
			}
			$body = '<circle cx="60" cy="60" r="50"/><path d="' . $ticks . '" stroke-opacity="0.7"/><path d="M14 60h22" stroke-dasharray="1 3"/><path d="M84 60h22" stroke-dasharray="1 3"/><path class="g-wire--pulse" d="M36 60h8l6-18 8 36 6-18h20"/><circle class="g-wire__dot" cx="84" cy="60" r="2.5"/>';
			break;
		case 'overlap': // two rings sharing a hatched lens: partnership.
			$hatch = '';
			for ( $x = 44; $x <= 76; $x += 4 ) {
				$hatch .= sprintf( 'M%d 20v80', $x );
			}
			$body = '<defs><clipPath id="g-lens"><circle cx="42" cy="60" r="36"/></clipPath></defs><circle cx="42" cy="60" r="36"/><circle cx="78" cy="60" r="36"/><g clip-path="url(#g-lens)"><circle cx="78" cy="60" r="36"/><path d="' . $hatch . '" clip-path="url(#g-lens)"/></g><path d="M6 60h108"/><circle class="g-wire__dot" cx="60" cy="60" r="2.5"/>';
			break;
		case 'stack': // nested rotated squares with a tag: e-commerce.
			$body = '<path d="M60 8 112 60 60 112 8 60z"/><path d="M60 24 96 60 60 96 24 60z"/><path d="M60 40 80 60 60 80 40 60z"/><path d="M8 60h104M60 8v104"/><circle class="g-wire__dot" cx="60" cy="60" r="2.5"/>';
			break;
		default: // lattice of nodes inside a ring: AI.
			$pts   = array( array( 60, 22 ), array( 94, 42 ), array( 94, 78 ), array( 60, 98 ), array( 26, 78 ), array( 26, 42 ), array( 60, 60 ) );
			$lines = '';
			foreach ( $pts as $a => $pa ) {
				foreach ( $pts as $b => $pb ) {
					if ( $b > $a && ( 6 === $b || 1 === ( $b - $a ) || ( 0 === $a && 5 === $b ) ) ) {
						$lines .= sprintf( 'M%d %dL%d %d', $pa[0], $pa[1], $pb[0], $pb[1] );
					}
				}
			}
			$dots = '';
			foreach ( $pts as $p ) {
				$dots .= sprintf( '<circle cx="%d" cy="%d" r="3"/>', $p[0], $p[1] );
			}
			$body = '<circle cx="60" cy="60" r="50"/><path d="' . $lines . '"/>' . $dots . '<circle class="g-wire__dot" cx="60" cy="60" r="2.5"/>';
	}
	return $open . $body . '</svg>';
}
