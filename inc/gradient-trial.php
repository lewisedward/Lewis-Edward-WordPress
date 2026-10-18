<?php
/**
 * Temporary brand-gradient trial switcher — STAGING ONLY.
 *
 * Appends ?grad=violet|iridescent|iridescent-anim|mint to any front-end URL so
 * palette options can be compared on the real pages (hero CTA tile, services
 * intro card, About stats) without a deploy per variant.
 *
 * The staging default is the animated thin-film surface, so ?grad=iridescent
 * is a no-op. ?grad=still freezes the drift, and ?grad=teal / ?grad=violet /
 * ?grad=mint restore the earlier flat gradients for comparison.
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
	// --brand-film is cleared: the default surface carries an animated thin-film
	// ::before layer, and leaving it on would composite the film over these
	// flat comparison gradients too.
	$lime = '--primary: 75 100% 50%; --primary-foreground: 168 4% 9%; --accent: 75 100% 50%;'
		. ' --accent-foreground: 168 4% 9%; --ring: 75 100% 50%; --cursor-accent: 191 255 0;'
		. ' --brand-film: none;';

	$variants = array(
		// Lime -> violet. Travels through spring green and cyan so the midpoint
		// keeps its chroma instead of going olive-grey.
		'violet' => $lime . ' --primary-deep: 258 60% 30%; --brand-gradient: linear-gradient(135deg,'
			. ' hsl(75 100% 50%) 0%, hsl(140 80% 62%) 26%, hsl(190 85% 58%) 56%, hsl(258 90% 66%) 100%);',

		// Lime -> petrol teal. Short hue path, one family, no transition risk.
		'teal'   => $lime . ' --primary-deep: 188 80% 18%; --brand-gradient: linear-gradient(135deg,'
			. ' hsl(75 100% 50%) 0%, hsl(150 75% 52%) 34%, hsl(176 80% 42%) 70%, hsl(188 78% 38%) 100%);',

		// 'iridescent' is the staging default now (see theme.css), so there is
		// nothing to override. Kept as a no-op so existing links still resolve.
		'iridescent' => '',

		// The previous mint palette, for a like-for-like A/B against the default.
		'mint'   => '--brand-film: none; --primary: 163 100% 45%; --primary-foreground: 168 4% 9%;'
			. ' --accent: 163 100% 45%; --accent-foreground: 168 4% 9%;'
			. ' --primary-deep: 168 100% 20%; --cursor-accent: 0 232 166;'
			. ' --brand-gradient: linear-gradient(135deg, hsl(182 100% 28%) 0%, hsl(174 100% 34%) 32%,'
			. ' hsl(164 100% 43%) 66%, hsl(156 100% 64%) 100%);',
	);

	// ?grad=still freezes the default surface so the animation can be judged
	// against a static version of exactly the same film.
	$anim = in_array( $key, array( 'still', 'iridescent-anim' ), true );

	if ( ! $anim && ( ! isset( $variants[ $key ] ) || '' === $variants[ $key ] ) ) {
		return;
	}

	$extra = '';

	if ( 'still' === $key ) {
		$extra = '.hero-tile--cta::before,.services-intro::before,.about-stat::before'
			. '{animation:none;transform:none;inset:0;}';
	}

	printf(
		"<style id=\"le-gradient-trial\">:root{%s}%s</style>\n",
		$anim ? '' : $variants[ $key ], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hard-coded CSS above.
		$extra // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hard-coded CSS above.
	);
}
add_action( 'wp_head', 'le_gradient_trial_css', 3 );
