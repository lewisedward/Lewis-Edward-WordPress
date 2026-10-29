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
	$host    = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	$staging = ( 0 === strpos( $host, 'staging.' ) );
	$q       = isset( $_GET['skin'] ) ? sanitize_key( wp_unslash( $_GET['skin'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	// Off staging, ?skin= is a preview switch for logged-in users only, so the
	// public never sees (or search engines index) a second version of a page.
	if ( ! $staging && ! ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) ) {
		$q = '';
	}
	if ( 'grid' === $q ) {
		$on = true;
	} elseif ( 'classic' === $q ) {
		$on = false;
	} else {
		// Launched 2026-10-22: the grid design is the default everywhere; ?skin=classic (logged in) previews the old one.
		$on = apply_filters( 'le_grid_skin_default', true );
	}
	return $on;
}

// A ?skin= preview is never cached or indexed.
add_action( 'send_headers', function () {
	if ( isset( $_GET['skin'] ) && ! headers_sent() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		header( 'X-Robots-Tag: noindex, nofollow', false );
		header( 'Cache-Control: private, no-store' );
	}
} );

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
	// Metric-matched stand-in for Geist (Arial scaled to Geist's width and line metrics), so text
	// doesn't reflow when Geist swaps in. Only referenced by the mobile font stack in grid.css.
	// Arial, Helvetica, Roboto (Android), Liberation Sans and Arimo (Google's Linux, i.e. PageSpeed) share Arial's widths; DejaVu Sans is wider, so it gets its own face.
	$css .= "@font-face{font-family:'Geist Fallback';src:local('Arial'),local('ArialMT'),local('Helvetica'),local('Roboto'),local('Roboto-Regular'),local('Liberation Sans'),local('LiberationSans'),local('Arimo'),local('Arimo-Regular');size-adjust:102.7%;ascent-override:97.86%;descent-override:28.72%;line-gap-override:0%}\n";
	$css .= "@font-face{font-family:'Geist Fallback DV';src:local('DejaVu Sans'),local('DejaVuSans');size-adjust:90.82%;ascent-override:110.66%;descent-override:32.48%;line-gap-override:0%}\n";
	// Phones only (media attribute): fetch Geist up front so it is usually in before first paint and nothing swaps.
	$geist = 'assets/fonts/geist/geist-latin-wght-normal.woff2';
	echo '<link rel="preload" href="' . esc_url( LE_URI . '/' . $geist . '?ver=' . le_asset_ver( $geist ) ) . '" as="font" type="font/woff2" crossorigin media="(max-width: 720px)">' . "\n";
	echo "<style id=\"le-grid-fonts\">\n" . $css . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}, 3 );

/**
 * Phones: fetch the hero video poster (the mobile LCP image) at high priority.
 * The media attribute keeps desktop from requesting it this way.
 */
add_action( 'wp_head', function () {
	if ( ! le_grid_skin() || ! is_front_page() ) {
		return;
	}
	$rel    = 'assets/videos/macbook-easyjet-poster.jpg';
	$poster = LE_URI . '/' . $rel . '?ver=' . le_asset_ver( $rel );
	$ov     = function_exists( 'le_field' ) ? le_field( 'grid_hero_media_poster', (int) get_option( 'page_on_front' ) ) : null;
	if ( is_array( $ov ) && ! empty( $ov['url'] ) ) {
		$poster = $ov['url'];
	}
	echo '<link rel="preload" as="image" href="' . esc_url( $poster ) . '" media="(max-width: 720px)" fetchpriority="high">' . "\n";
}, 2 );

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
/**
 * The large "L." arrow: the logo's L bent into an arrow, with a hairline copy
 * sitting off-register that slides into line on hover. Used on the "All work"
 * tile and the "Get a quote" panel.
 */
function le_grid_big_arrow( $class ) {
	$d = 'M14 8H26V74H78V56L112 80L78 104V86H14Z';
	return '<svg class="g-bigarrow ' . esc_attr( $class ) . '" viewBox="0 0 120 120" aria-hidden="true" focusable="false"><path class="g-bigarrow__ghost" d="' . $d . '"/><path class="g-bigarrow__solid" d="' . $d . '"/></svg>';
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
	$keys = array( 'audit' => 'audit', 'train' => 'training', 'optimi' => 'gauge', 'speed' => 'gauge', 'secur' => 'shield', 'webflow' => 'flow', 'migrat' => 'migrate', 'consult' => 'consult', 'api' => 'hub', 'integrat' => 'hub', 'seo' => 'rise', 'hosting' => 'server', 'ai-' => 'lattice', ' ai' => 'lattice', 'intelligen' => 'lattice', 'partner' => 'overlap', 'agency' => 'overlap', 'commerce' => 'stack', 'shop' => 'stack', 'support' => 'monitor', 'maint' => 'monitor', 'design' => 'compose', 'develop' => 'build' ); // first match wins, so the specific ones go first
	$kind = '';
	foreach ( $keys as $needle => $k ) {
		if ( false !== strpos( ' ' . $hint, $needle ) ) {
			$kind = $k;
			break;
		}
	}
	$order = array( 'compose', 'build', 'monitor', 'overlap', 'stack', 'lattice', 'audit', 'training', 'gauge', 'shield', 'flow', 'migrate', 'consult', 'hub', 'rise', 'server' );
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
		case 'audit': // a page of lines under a magnifier: audits.
			$body = '<rect x="22.5" y="12.5" width="60" height="80"/><path d="M32 28h40M32 38h40M32 48h28M32 58h34M32 68h22"/><circle cx="78" cy="78" r="20"/><path d="M92.5 92.5 108 108"/><path d="M68 78h20M78 68v20" stroke-opacity="0.5"/><circle class="g-wire__dot" cx="78" cy="78" r="2.5"/>';
			break;
		case 'training': // mortarboard with a hanging tassel: training.
			$body = '<path d="M60 26 106 44 60 62 14 44z"/><path d="M14 44v5l46 18 46-18v-5" stroke-opacity="0.5"/><path d="M32 57v17c0 8 12.5 13 28 13s28-5 28-13V57"/><circle cx="60" cy="44" r="3"/><path d="M60 44 96 56v24"/><path d="M93 84v10M96 84v12M99 84v10" stroke-opacity="0.6"/><circle class="g-wire__dot" cx="96" cy="82" r="2.5"/>';
			break;
		case 'gauge': // speed gauge with ticks and a needle: optimisation.
			$ticks = '';
			for ( $t = 0; $t <= 12; $t++ ) {
				$a      = deg2rad( 210 - $t * 20 );
				$ticks .= sprintf( 'M%.1f %.1fL%.1f %.1f', 60 + 40 * cos( $a ), 66 - 40 * sin( $a ), 60 + 46 * cos( $a ), 66 - 46 * sin( $a ) );
			}
			$body = '<path d="M20.2 89A46 46 0 1 1 99.8 89"/><path d="' . $ticks . '" stroke-opacity="0.7"/><path d="M60 66 84 42"/><circle cx="60" cy="66" r="6"/><path d="M34 104h52"/><circle class="g-wire__dot" cx="60" cy="66" r="2.5"/>';
			break;
		case 'shield': // nested shields with a keyhole: security.
			$body = '<path d="M60 10 98 24v32c0 26-16 44-38 54-22-10-38-28-38-54V24z"/><path d="M60 24 86 34v22c0 18-11 31-26 38-15-7-26-20-26-38V34z"/><circle cx="60" cy="54" r="7"/><path d="M60 61v14"/><circle class="g-wire__dot" cx="60" cy="10" r="2.5"/>';
			break;
		case 'flow': // three waves in a frame: Webflow.
			$body = '<rect x="10.5" y="20.5" width="99" height="79"/><path d="M10.5 44c16-14 33-14 49.5 0s33.5 14 49 0"/><path d="M10.5 60c16-14 33-14 49.5 0s33.5 14 49 0"/><path d="M10.5 76c16-14 33-14 49.5 0s33.5 14 49 0"/><circle class="g-wire__dot" cx="60" cy="60" r="2.5"/>';
			break;
		case 'migrate': // one block arcing across to another: migration.
			$body = '<rect x="8.5" y="62.5" width="38" height="38"/><rect x="73.5" y="19.5" width="38" height="38"/><path d="M73.5 32.5h38M86.5 19.5v38" stroke-opacity="0.5"/><path d="M27.5 62.5C27.5 30 48 38.5 73.5 38.5" stroke-dasharray="3 3"/><path d="M66 32l7.5 6.5L66 45"/><circle class="g-wire__dot" cx="27.5" cy="81.5" r="2.5"/>';
			break;
		case 'consult': // two speech bubbles in conversation: consultancy.
			$body = '<path d="M12.5 18.5h62v40h-36l-12 12v-12h-14z"/><path d="M45.5 52.5h62v40h-14v12l-12-12h-36z"/><path d="M56 66h40M56 76h28" stroke-opacity="0.6"/><circle cx="32" cy="38.5" r="2"/><circle cx="55" cy="38.5" r="2"/><circle class="g-wire__dot" cx="43.5" cy="38.5" r="2.5"/>';
			break;
		case 'hub': // a chip wired out to four endpoints: API & integrations.
			$body = '<rect x="44.5" y="44.5" width="31" height="31"/><path d="M60 44.5V22M60 75.5V98M44.5 60H22M75.5 60H98"/><circle cx="60" cy="16" r="6"/><circle cx="60" cy="104" r="6"/><circle cx="16" cy="60" r="6"/><circle cx="104" cy="60" r="6"/><path d="M52 44.5v-5M68 44.5v-5M52 75.5v5M68 75.5v5M44.5 52h-5M44.5 68h-5M75.5 52h5M75.5 68h5" stroke-opacity="0.6"/><circle class="g-wire__dot" cx="60" cy="60" r="2.5"/>';
			break;
		case 'rise': // bars and a rising trend line: SEO.
			$body = '<path d="M12.5 12.5v95h95"/><path d="M30 107.5v-20M48 107.5v-34M66 107.5v-28M84 107.5v-52M100 107.5v-70" stroke-opacity="0.45"/><path d="M30 80 48 64 66 70 84 44 100 26"/><circle cx="100" cy="26" r="8"/><circle class="g-wire__dot" cx="100" cy="26" r="2.5"/>';
			break;
		case 'server': // a rack of three units: hosting.
			$body = '<rect x="20.5" y="16.5" width="79" height="24"/><rect x="20.5" y="48.5" width="79" height="24"/><rect x="20.5" y="80.5" width="79" height="24"/><path d="M54 28.5h36M54 60.5h36M54 92.5h36" stroke-opacity="0.6"/><circle cx="32" cy="28.5" r="3"/><circle cx="32" cy="92.5" r="3"/><path d="M60 104.5v8M40 112.5h40"/><circle class="g-wire__dot" cx="32" cy="60.5" r="2.5"/>';
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
 * Alt text for an attachment: its own Media Library alt (descriptive, best for SEO), else a fallback.
 *
 * @param int    $att_id   Attachment ID.
 * @param string $fallback Fallback text (e.g. the post title).
 * @return string
 */
function le_img_alt( $att_id, $fallback = '' ) {
	$alt = $att_id ? trim( (string) get_post_meta( (int) $att_id, '_wp_attachment_image_alt', true ) ) : '';
	return '' !== $alt ? $alt : (string) $fallback;
}

/**
 * Responsive <img> for an ACF image array / attachment ID, with a plain <img> fallback for theme files.
 * Gives the browser a srcset so it never downloads more pixels than the slot needs.
 *
 * @param array|int|string $img   ACF image array, attachment ID or URL.
 * @param string           $alt   Alt text (falls back to the attachment's alt).
 * @param string           $sizes sizes attribute.
 * @param array            $attrs Extra attributes (loading, fetchpriority, class…).
 * @return string
 */
function le_grid_img( $img, $alt = '', $sizes = '100vw', $attrs = array() ) {
	$id  = is_array( $img ) ? (int) ( $img['ID'] ?? $img['id'] ?? 0 ) : ( is_numeric( $img ) ? (int) $img : 0 );
	$url = is_array( $img ) ? (string) ( $img['url'] ?? '' ) : ( is_string( $img ) && ! is_numeric( $img ) ? $img : '' );
	if ( '' === $alt && is_array( $img ) && ! empty( $img['alt'] ) ) {
		$alt = (string) $img['alt'];
	}
	$attrs = array_merge( array( 'decoding' => 'async' ), $attrs, array( 'alt' => $alt, 'sizes' => $sizes ) );
	if ( $id && wp_attachment_is_image( $id ) ) {
		$tag = wp_get_attachment_image( $id, 'full', false, $attrs );
		if ( $tag ) {
			return $tag;
		}
	}
	if ( '' === $url ) {
		return '';
	}
	unset( $attrs['sizes'] );
	$out = '<img src="' . esc_url( $url ) . '"';
	foreach ( $attrs as $k => $v ) {
		$out .= ' ' . esc_attr( $k ) . '="' . esc_attr( (string) $v ) . '"';
	}
	return $out . ' />';
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
		$attrs['alt'] = $attrs['alt'] ?? le_img_alt( $img, get_the_title( $wid ) );
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
	$ext   = le_work_showcase_url( $wid );
	if ( $ext && ! $meta ) {
		$meta = array( preg_replace( '/^www\./', '', (string) wp_parse_url( $ext, PHP_URL_HOST ) ) );
	}
	echo '<a class="g-cell g-work__item' . ( $ext ? ' g-work__item--ext' : '' ) . ' ' . esc_attr( $args['class'] ?? '' ) . '"' . le_grid_work_href( $wid ) . ( empty( $args['data'] ) ? '' : ' ' . $args['data'] ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<span class="g-ph g-work__media">' . $img . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<div class="g-work__meta"><h3 class="g-work__name">' . esc_html( get_the_title( $wid ) ) . ( $ext ? '<span class="g-sr"> ' . esc_html__( '(opens the live site in a new tab)', 'lewisedward' ) . '</span>' : '' ) . '</h3><span class="g-mono">' . esc_html( implode( ' · ', $meta ) ) . '</span>' . le_grid_arrow_diag() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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

	// Explicit markers win once a grey {{...}} run is given. Lime-only markers
	// (and the old text-primary spans) date from the two-tone headings, so
	// they are dropped and the title is split into three tones instead.
	if ( false === strpos( implode( ' ', $lines ), '{{' ) ) {
		$lines = array_map( static function ( $l ) {
			return trim( preg_replace( '/\[\[(.*?)\]\]/', '$1', $l ) );
		}, $lines );
	}
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

/**
 * A showcase-only work item: no case study, just a name, an image and a link
 * to the live site (Work → "Showcase only" toggle + Live URL).
 *
 * @param int $wid Work post ID.
 * @return string Live URL when the post is showcase-only, else ''.
 */
function le_work_showcase_url( $wid ) {
	if ( ! function_exists( 'get_field' ) || ! get_field( 'work_showcase', $wid ) ) {
		return '';
	}
	if ( '0' === (string) get_post_meta( $wid, 'work_live_show', true ) ) {
		return ''; // "Show Visit website" switched off: keep the URL but don't link to it.
	}
	return (string) le_field( 'work_live_url', $wid );
}

/**
 * href (and target) attributes for a link to a work item: the case study, or
 * the live site in a new tab for showcase-only items.
 *
 * @param int $wid Work post ID.
 * @return string Attribute string, escaped.
 */
function le_grid_work_href( $wid ) {
	$ext = le_work_showcase_url( $wid );
	if ( $ext ) {
		return ' href="' . esc_url( $ext ) . '" target="_blank" rel="noopener"';
	}
	return ' href="' . esc_url( get_permalink( $wid ) ) . '"';
}

/**
 * IDs of showcase-only work posts (cached per request).
 *
 * @return int[]
 */
function le_work_showcase_ids() {
	static $ids = null;
	if ( null === $ids ) {
		$ids = get_posts( array(
			'post_type'      => 'work',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array( array( 'key' => 'work_showcase', 'value' => '1' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		) );
		$ids = array_map( 'intval', $ids );
	}
	return $ids;
}

// A showcase-only project has no page of its own: send visitors to the live site.
add_action( 'template_redirect', function () {
	if ( is_singular( 'work' ) ) {
		$wid = get_queried_object_id();
		$ext = le_work_showcase_url( $wid );
		if ( $ext ) {
			wp_redirect( $ext, 302 ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit;
		}
		if ( function_exists( 'get_field' ) && get_field( 'work_showcase', $wid ) ) {
			// Showcase item with no live link: there's no case study to show, so send people to its card.
			wp_safe_redirect( home_url( '/work/#more' ), 302 );
			exit;
		}
	}
} );

// …and keep those URLs out of the Yoast sitemap.
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function ( $ids ) {
	return array_values( array_unique( array_merge( (array) $ids, le_work_showcase_ids() ) ) );
} );
