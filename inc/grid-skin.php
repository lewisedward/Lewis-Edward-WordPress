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
	// Inner pages carry a second sheet (page heroes, lists, accordions, prose, forms).
	if ( ! is_front_page() ) {
		$pages = LE_DIR . '/assets/css/grid-pages.css';
		if ( file_exists( $pages ) ) {
			$css .= "\n" . (string) file_get_contents( $pages ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}
	}
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
 * Favicon set for the grid skin (the L mark on lime). Replaces the Customizer
 * site icon while the skin is active.
 */
add_action( 'wp_head', function () {
	if ( ! le_grid_skin() ) {
		return;
	}
	remove_action( 'wp_head', 'wp_site_icon', 99 );
	$u = static function ( $f ) {
		return esc_url( LE_URI . '/assets/images/favicon/' . $f . '?ver=' . le_asset_ver( 'assets/images/favicon/' . $f ) );
	};
	echo '<link rel="icon" href="' . $u( 'favicon.ico' ) . '" sizes="any">' . "\n";
	echo '<link rel="icon" type="image/svg+xml" href="' . $u( 'favicon.svg' ) . '">' . "\n";
	echo '<link rel="icon" type="image/png" sizes="32x32" href="' . $u( 'favicon-32.png' ) . '">' . "\n";
	echo '<link rel="apple-touch-icon" sizes="180x180" href="' . $u( 'apple-touch-icon.png' ) . '">' . "\n";
	echo '<meta name="theme-color" content="#121413">' . "\n";
}, 1 );

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
 * Version attachment image URLs by the attachment's modified time.
 *
 * Several work images were re-uploaded under the same filenames, and the image
 * CDN still served its old WebP conversions for those paths. A query string per
 * revision gives the CDN a fresh cache key.
 */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment ) {
	if ( ! le_grid_skin() || ! $attachment instanceof WP_Post ) {
		return $attr;
	}
	// Key on the file itself: a re-upload that overwrites the same filename may
	// leave the attachment's post_modified untouched, but the file's mtime moves.
	$file = get_attached_file( $attachment->ID );
	$v    = ( $file && file_exists( $file ) ) ? (int) filemtime( $file ) : 0;
	if ( ! $v ) {
		$v = strtotime( $attachment->post_modified_gmt ) ?: $attachment->ID;
	}
	$bump = static function ( $url ) use ( $v ) {
		return add_query_arg( 'v', $v, $url );
	};
	if ( ! empty( $attr['src'] ) ) {
		$attr['src'] = $bump( $attr['src'] );
	}
	if ( ! empty( $attr['srcset'] ) ) {
		$attr['srcset'] = implode( ', ', array_map( static function ( $c ) use ( $bump ) {
			$parts = preg_split( '/\s+/', trim( $c ), 2 );
			return $bump( $parts[0] ) . ( isset( $parts[1] ) ? ' ' . $parts[1] : '' );
		}, explode( ',', $attr['srcset'] ) ) );
	}
	return $attr;
}, 20, 2 );

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

/**
 * A work post's homepage/tile image: its "Homepage tile image" if set, else
 * the featured image. Returns the <img> markup or ''.
 *
 * @param int    $wid   Work post ID.
 * @param string $size  Image size.
 * @param array  $attrs Extra attributes.
 * @return string
 */
function le_grid_work_img( $wid, $size = 'large', $attrs = array() ) {
	$img = le_field( 'work_home_image', $wid );
	$img = is_array( $img ) ? (int) ( $img['ID'] ?? $img['id'] ?? 0 ) : (int) $img;
	if ( $img && wp_attachment_is_image( $img ) ) {
		$attrs['alt'] = $attrs['alt'] ?? get_the_title( $wid );
		return (string) wp_get_attachment_image( $img, $size, false, $attrs );
	}
	return (string) get_the_post_thumbnail( $wid, $size, $attrs );
}

/**
 * One work tile (image over a name / platform · year strip) — the homepage
 * work-grid cell, reused by the Work page, case studies and service pages.
 *
 * @param int   $wid  Work post ID.
 * @param array $args 'size' (image size), 'class' (extra classes), 'eager' (bool), 'data' (raw attribute string).
 */
function le_grid_work_tile( $wid, $args = array() ) {
	$size  = $args['size'] ?? 'large';
	$meta  = array_filter( array( le_field( 'work_platform', $wid ), le_field( 'work_year', $wid ) ) );
	$attrs = array( 'loading' => empty( $args['eager'] ) ? 'lazy' : 'eager' );
	$img   = le_grid_work_img( $wid, $size, $attrs );
	echo '<a class="g-cell g-work__item ' . esc_attr( $args['class'] ?? '' ) . '" href="' . esc_url( get_permalink( $wid ) ) . '"' . ( empty( $args['data'] ) ? '' : ' ' . $args['data'] ) . '>';
	echo '<span class="g-ph g-work__media">' . $img . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<div class="g-work__meta"><h3 class="g-work__name">' . esc_html( get_the_title( $wid ) ) . '</h3><span class="g-mono">' . esc_html( implode( ' · ', $meta ) ) . '</span>' . le_grid_arrow_diag() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</a>';
}

/**
 * Compose a heading from the classic three-part fields (plain / muted / accent).
 *
 * @param array $parts Each item: array( text, 'plain'|'muted'|'accent' ).
 * @return string Escaped HTML.
 */
function le_grid_heading( $parts ) {
	$out = array();
	foreach ( $parts as $p ) {
		$t = trim( (string) ( $p[0] ?? '' ) );
		if ( '' === $t ) {
			continue;
		}
		$k = $p[1] ?? 'plain';
		if ( 'accent' === $k ) {
			$out[] = '<span class="g-accent">' . esc_html( $t ) . '</span>';
		} elseif ( 'muted' === $k ) {
			$out[] = '<span class="g-muted">' . esc_html( $t ) . '</span>';
		} else {
			$out[] = esc_html( $t );
		}
	}
	return implode( ' ', $out );
}

/**
 * Section intro cell: label (+ count), heading, optional text and link.
 *
 * @param array $a label, count, title (HTML, already escaped), text, link (array url/title), tag (h2|h1), class.
 */
function le_grid_intro( $a ) {
	$tag = ( ! empty( $a['tag'] ) && 'h1' === $a['tag'] ) ? 'h1' : 'h2';
	echo '<div class="g-cell g-sec__intro ' . esc_attr( $a['class'] ?? '' ) . '"><div class="g-sec__in">';
	echo '<div class="g-sec__top">';
	if ( ! empty( $a['label'] ) ) {
		echo '<span class="g-label g-mono">' . esc_html( $a['label'] ) . '</span>';
	}
	if ( ! empty( $a['count'] ) ) {
		echo '<span class="g-mono g-sec__count">' . esc_html( $a['count'] ) . '</span>';
	}
	echo '</div>';
	if ( ! empty( $a['big'] ) ) {
		echo '<span class="g-sec__big" aria-hidden="true">' . esc_html( $a['big'] ) . '</span>';
	}
	if ( ! empty( $a['title'] ) ) {
		echo '<' . $tag . ' class="g-sec__title">' . $a['title'] . '</' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( ! empty( $a['text'] ) ) {
		echo '<div class="g-sec__text">' . wp_kses_post( wpautop( $a['text'] ) ) . '</div>';
	}
	if ( ! empty( $a['link'] ) && ! empty( $a['link']['url'] ) ) {
		$tgt = ! empty( $a['link']['target'] ) ? ' target="' . esc_attr( $a['link']['target'] ) . '" rel="noopener"' : '';
		echo '<a class="g-line-link g-mono g-sec__link" href="' . esc_url( $a['link']['url'] ) . '"' . $tgt . '><span>' . esc_html( $a['link']['title'] ) . '</span>' . le_grid_arrow() . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div></div>';
}

/**
 * Column class for a sub-grid of N cards: three across when that divides
 * evenly (or there are many), otherwise two.
 *
 * @param int $n Number of cards.
 * @return string
 */
function le_grid_cells_class( $n ) {
	$n = (int) $n;
	if ( $n >= 3 && ( 0 === $n % 3 || $n > 6 ) ) {
		return 'g-cells g-cells--3';
	}
	return 'g-cells g-cells--2';
}

/**
 * Tone a single-field page title in the three-colour heading style used on the
 * About page: white, then grey, then lime.
 *
 * Editors can choose the split themselves with markers in the field —
 * {{grey words}} and [[lime words]] — (the older <span class="text-primary">
 * markup counts as lime). Without markers the words are split into thirds,
 * with the last third lime. Newlines become separate lines.
 *
 * @param string $text Raw title.
 * @return string Escaped HTML.
 */
function le_grid_tone_title( $text ) {
	$text  = preg_replace( '#<span[^>]*class="[^"]*text-primary[^"]*"[^>]*>(.*?)</span>#i', '[[$1]]', (string) $text );
	$text  = wp_strip_all_tags( $text );
	$lines = preg_split( '/\r\n|\r|\n|\\\\n/', trim( $text ) );
	$lines = array_values( array_filter( array_map( 'trim', (array) $lines ), 'strlen' ) );
	if ( ! $lines ) {
		return '';
	}
	$multi = count( $lines ) > 1;
	$wrap  = static function ( $inner ) use ( $multi ) {
		return $multi ? '<span class="g-phero__line">' . $inner . '</span> ' : $inner;
	};

	// Explicit markers win.
	if ( preg_match( '/\[\[|\{\{/', implode( ' ', $lines ) ) ) {
		$out = '';
		foreach ( $lines as $line ) {
			$h    = esc_html( $line );
			$h    = preg_replace( '/\[\[(.*?)\]\]/', '<span class="g-accent">$1</span>', $h );
			$h    = preg_replace( '/\{\{(.*?)\}\}/', '<span class="g-muted">$1</span>', $h );
			$out .= $wrap( $h );
		}
		return trim( $out );
	}

	// Otherwise: thirds by word count — white, grey, lime.
	$words = array();
	foreach ( $lines as $li => $line ) {
		foreach ( preg_split( '/\s+/', $line ) as $w ) {
			$words[] = array( $li, $w );
		}
	}
	$n = count( $words );
	if ( $n < 2 ) {
		$lime = 0;
		$grey = 0;
	} elseif ( 2 === $n ) {
		$lime = 1;
		$grey = 0;
	} else {
		$lime = (int) ceil( $n / 3 );
		$grey = max( 1, (int) round( ( $n - $lime ) / 2 ) );
	}
	// A lime run should not open on a connective ("& Maintenance"): hand it to the grey run.
	$joiners = array( '&', 'and', 'of', 'for', 'a', 'the', 'to', 'with' );
	while ( $lime > 1 && in_array( strtolower( $words[ $n - $lime ][1] ), $joiners, true ) ) {
		$lime--;
		$grey++;
	}
	$white = $n - $lime - $grey;
	if ( $white < 1 && $n > 2 ) {
		$white = 1;
		$grey  = max( 0, $n - $lime - 1 );
	}

	$by_line = array();
	foreach ( $words as $i => $w ) {
		$tone                  = $i < $white ? '' : ( $i < $white + $grey ? 'g-muted' : 'g-accent' );
		$by_line[ $w[0] ][]    = array( $tone, $w[1] );
	}
	$out = '';
	foreach ( $by_line as $items ) {
		$chunks = array();
		$cur    = null;
		foreach ( $items as $it ) {
			if ( null === $cur || $cur[0] !== $it[0] ) {
				if ( null !== $cur ) {
					$chunks[] = $cur;
				}
				$cur = array( $it[0], array() );
			}
			$cur[1][] = $it[1];
		}
		$chunks[] = $cur;
		$html     = array();
		foreach ( $chunks as $c ) {
			$t      = esc_html( implode( ' ', $c[1] ) );
			$html[] = $c[0] ? '<span class="' . $c[0] . '">' . $t . '</span>' : $t;
		}
		$out .= $wrap( implode( ' ', $html ) );
	}
	return trim( $out );
}
