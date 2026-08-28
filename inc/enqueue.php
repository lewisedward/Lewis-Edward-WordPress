<?php
/**
 * Styles & scripts.
 *
 * The theme ships a single compiled stylesheet (design tokens + base) plus
 * modular JS. Heavier front-end (Three.js hero, Framer-Motion-style
 * animations) will be enqueued from assets/js/vendor as those page parts land.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache-busting version based on file mtime in dev, LE_VERSION in prod.
 *
 * @param string $rel_path Path relative to the theme root.
 * @return string
 */
function le_asset_ver( $rel_path ) {
	$abs = LE_DIR . '/' . ltrim( $rel_path, '/' );
	if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) && file_exists( $abs ) ) {
		return (string) filemtime( $abs );
	}
	return LE_VERSION;
}

/**
 * Enqueue front-end assets.
 */
function le_enqueue_assets() {
	// Main compiled stylesheet: design tokens, @font-face (Chillax), base.
	wp_enqueue_style(
		'le-theme',
		LE_URI . '/assets/css/theme.css',
		array(),
		le_asset_ver( 'assets/css/theme.css' )
	);

	/*
	 * The root style.css carries only the WordPress theme header - no rules.
	 * It is deliberately NOT enqueued: WordPress requires the file to exist,
	 * not to be in the queue, and as a render-blocking <link> it cost ~490ms
	 * of first paint on mobile for zero bytes of actual CSS.
	 */

	// Smooth scroll (lightweight vanilla replacement for Lenis wrapper).
	wp_enqueue_script(
		'le-smooth-scroll',
		LE_URI . '/assets/js/smooth-scroll.js',
		array(),
		le_asset_ver( 'assets/js/smooth-scroll.js' ),
		true
	);

	// Custom cursor.
	wp_enqueue_script(
		'le-cursor',
		LE_URI . '/assets/js/cursor.js',
		array(),
		le_asset_ver( 'assets/js/cursor.js' ),
		true
	);

	// Main behaviours (nav, reveal-on-scroll, counters).
	wp_enqueue_script(
		'le-main',
		LE_URI . '/assets/js/main.js',
		array(),
		le_asset_ver( 'assets/js/main.js' ),
		true
	);

	// Hero dot-sphere canvas — registered only; enqueued by the hero part.
	wp_register_script(
		'le-hero',
		LE_URI . '/assets/js/hero.js',
		array(),
		le_asset_ver( 'assets/js/hero.js' ),
		true
	);

	// Recent Work carousel — registered only; enqueued by the recent-work part.
	wp_register_script(
		'le-recent-work',
		LE_URI . '/assets/js/recent-work.js',
		array(),
		le_asset_ver( 'assets/js/recent-work.js' ),
		true
	);

	// Text/Image Overlap clip + parallax — enqueued by that part.
	wp_register_script(
		'le-overlap',
		LE_URI . '/assets/js/overlap.js',
		array(),
		le_asset_ver( 'assets/js/overlap.js' ),
		true
	);

	// Testimonials slider — enqueued by that part.
	wp_register_script(
		'le-testimonials',
		LE_URI . '/assets/js/testimonials.js',
		array(),
		le_asset_ver( 'assets/js/testimonials.js' ),
		true
	);

	// Contact CTA hover flicker + clip reveal — enqueued by that part.
	wp_register_script(
		'le-contact-cta',
		LE_URI . '/assets/js/contact-cta.js',
		array(),
		le_asset_ver( 'assets/js/contact-cta.js' ),
		true
	);

	// Work archive category filter — enqueued by the Work page.
	wp_register_script(
		'le-work-filter',
		LE_URI . '/assets/js/work-filter.js',
		array(),
		le_asset_ver( 'assets/js/work-filter.js' ),
		true
	);

	// Single Work "Explore features" slider — enqueued by single-work.php.
	wp_register_script(
		'le-work-features',
		LE_URI . '/assets/js/work-features.js',
		array(),
		le_asset_ver( 'assets/js/work-features.js' ),
		true
	);

	// Single Service "Featured work" slider — enqueued by single-service.php.
	wp_register_script(
		'le-service-work',
		LE_URI . '/assets/js/service-related-work.js',
		array(),
		le_asset_ver( 'assets/js/service-related-work.js' ),
		true
	);

	// Journal archive category filter — enqueued by the Journal page.
	wp_register_script(
		'le-journal-filter',
		LE_URI . '/assets/js/journal-filter.js',
		array(),
		le_asset_ver( 'assets/js/journal-filter.js' ),
		true
	);

	// Form section cards (wraps GF Section groups) — enqueued by form pages.
	wp_register_script(
		'le-form-groups',
		LE_URI . '/assets/js/le-form-groups.js',
		array(),
		le_asset_ver( 'assets/js/le-form-groups.js' ),
		true
	);

	// Expose useful values to JS.
	wp_localize_script( 'le-main', 'LEData', array(
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'restUrl'  => esc_url_raw( rest_url() ),
		'themeUri' => LE_URI,
		'nonce'    => wp_create_nonce( 'le_nonce' ),
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'le_enqueue_assets' );

/**
 * Preload the above-the-fold Chillax weights.
 *
 * The @font-face URLs live inside theme.css, so the browser cannot discover
 * them until that stylesheet has downloaded AND parsed - three round trips
 * deep. With the Google Fonts request gone the page now paints at ~1.7s,
 * which is *before* the fonts arrive, so the swap reflows the hero after
 * first paint (CLS 0.133, attributed by Lighthouse to these four files).
 *
 * Preloading starts them in parallel with the stylesheet instead, so they are
 * in place by first paint. All four weights are used above the fold (300 lede,
 * 400 headings, 500 eyebrows, 600 strong eyebrows), so this changes the order
 * these bytes are requested, not how many.
 *
 * crossorigin is required even same-origin: fonts are always fetched in CORS
 * mode, and without it the preload will not match and the file downloads twice.
 */
function le_preload_fonts() {
	foreach ( array( 'Chillax-Light', 'Chillax-Regular', 'Chillax-Medium', 'Chillax-Semibold' ) as $le_font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( LE_URI . '/assets/fonts/' . $le_font . '.woff2' )
		);
	}
}
add_action( 'wp_head', 'le_preload_fonts', 1 );

/**
 * Editor styles — light editor stylesheet (theme font only).
 *
 * Deliberately NOT theme.css: that file paints the dark charcoal background on
 * <body>, which TinyMCE applied to the WYSIWYG content area (black editor box).
 * editor.css only sets the Chillax typeface on a normal white surface.
 */
function le_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'le_editor_styles' );
