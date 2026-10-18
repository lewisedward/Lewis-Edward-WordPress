<?php
/**
 * Temporary brand-gradient trial switcher — STAGING ONLY.
 *
 * Appends ?grad=violet|mint to any front-end URL so
 * palette options can be compared on the real pages (hero CTA tile, services
 * intro card, About stats) without a deploy per variant.
 *
 * The staging default is lime -> petrol teal with a corner bloom, so
 * ?grad=teal is a no-op; ?grad=violet and ?grad=mint restore the other
 * palettes for comparison.
 *
 * Delete this file and its le_require() line once a palette is chosen.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Only ever run on the staging host. Never on production or the temporary
 * skipdns live site.
 *
 * @return bool
 */
function le_gradient_trial_enabled() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : '';
	return ( 0 === strpos( $host, 'staging.' ) );
}

/**
 * Print a :root override after the inlined theme CSS (wp_head priority 2).
 */
function le_gradient_trial_css() {
	if ( ! le_gradient_trial_enabled() ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display toggle.
	$key = isset( $_GET['grad'] ) ? sanitize_key( wp_unslash( $_GET['grad'] ) ) : '';
	if ( '' === $key ) {
		return;
	}

	// Lime base, shared by the lime variants.
	$lime = '--primary: 75 100% 50%; --primary-foreground: 168 4% 9%; --accent: 75 100% 50%;'
		. ' --accent-foreground: 168 4% 9%; --ring: 75 100% 50%; --cursor-accent: 191 255 0;'
		. '';

	$variants = array(
		// Lime -> violet. Travels through spring green and cyan so the midpoint
		// keeps its chroma instead of going olive-grey.
		'violet' => $lime . ' --primary-deep: 258 60% 30%; --brand-gradient: linear-gradient(135deg,'
			. ' hsl(75 100% 50%) 0%, hsl(140 80% 62%) 26%, hsl(190 85% 58%) 56%, hsl(258 90% 66%) 100%);',

		// 'teal' is the staging default (see theme.css), so nothing to override.
		'teal'   => '',

		// The previous mint palette, for a like-for-like A/B against the default.
		'mint'   => '--primary: 163 100% 45%; --primary-foreground: 168 4% 9%;'
			. ' --accent: 163 100% 45%; --accent-foreground: 168 4% 9%;'
			. ' --primary-deep: 168 100% 20%; --cursor-accent: 0 232 166;'
			. ' --brand-gradient: linear-gradient(135deg, hsl(182 100% 28%) 0%, hsl(174 100% 34%) 32%,'
			. ' hsl(164 100% 43%) 66%, hsl(156 100% 64%) 100%);',
	);

	if ( ! isset( $variants[ $key ] ) || '' === $variants[ $key ] ) {
		return;
	}

	printf(
		"<style id=\"le-gradient-trial\">:root{%s}</style>\n",
		$variants[ $key ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hard-coded CSS above.
	);
}
add_action( 'wp_head', 'le_gradient_trial_css', 3 );

/**
 * Mono typeface trial - STAGING ONLY.
 *
 * ?mono=chivo|plex|dm|space|martian swaps --font-mono, which currently drives
 * the section eyebrows and code. The stylesheet is injected here rather than
 * enqueued so it only ever loads when the parameter is present.
 *
 * Delete with the rest of this module once a face is chosen.
 */
function le_mono_trial_css() {
	if ( ! le_gradient_trial_enabled() ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display toggle.
	$key = isset( $_GET['mono'] ) ? sanitize_key( wp_unslash( $_GET['mono'] ) ) : '';
	if ( '' === $key ) {
		return;
	}

	$faces = array(
		// The face on turionspace.com, the reference that prompted this.
		'chivo'   => array( 'Chivo+Mono:wght@300..700', "'Chivo Mono'" ),
		// Humanist and warm; the least "terminal" of the set.
		'plex'    => array( 'IBM+Plex+Mono:wght@300;400;500;600', "'IBM Plex Mono'" ),
		// Low contrast and slightly narrow, so it crowds less at small sizes.
		'dm'      => array( 'DM+Mono:wght@300;400;500', "'DM Mono'" ),
		// Characterful and a little retro; the most opinionated option.
		'space'   => array( 'Space+Mono:wght@400;700', "'Space Mono'" ),
		// Very wide and technical; strong at tiny sizes, heavy in quantity.
		'martian' => array( 'Martian+Mono:wght@300..700', "'Martian Mono'" ),
	);

	if ( ! isset( $faces[ $key ] ) ) {
		return;
	}

	list( $family, $stack ) = $faces[ $key ];

	printf(
		'<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=%s&display=swap">'
			. "<style id=\"le-mono-trial\">:root{--font-mono: %s, ui-monospace, SFMono-Regular, Menlo, monospace;}</style>\n",
		esc_attr( $family ),
		$stack // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hard-coded above.
	);
}
add_action( 'wp_head', 'le_mono_trial_css', 4 );
