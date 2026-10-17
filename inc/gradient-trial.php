<?php
/**
 * Temporary brand-gradient trial switcher — STAGING ONLY.
 *
 * Appends ?grad=violet|teal|mint to any front-end URL so palette options can be
 * compared on the real pages (hero CTA tile, services intro card, About stats)
 * without a deploy per variant.
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

	// Lime base, shared by both lime variants.
	$lime = '--primary: 75 100% 50%; --primary-foreground: 168 4% 9%; --accent: 75 100% 50%;'
		. ' --accent-foreground: 168 4% 9%; --ring: 75 100% 50%; --cursor-accent: 191 255 0;';

	$variants = array(
		// Lime -> violet. Travels through spring green and cyan so the midpoint
		// keeps its chroma instead of going olive-grey.
		'violet' => $lime . ' --primary-deep: 258 60% 30%; --brand-gradient: linear-gradient(135deg,'
			. ' hsl(75 100% 50%) 0%, hsl(140 80% 62%) 26%, hsl(190 85% 58%) 56%, hsl(258 90% 66%) 100%);',

		// Lime -> petrol teal. Short hue path, one family, no transition risk.
		'teal'   => $lime . ' --primary-deep: 188 80% 18%; --brand-gradient: linear-gradient(135deg,'
			. ' hsl(75 100% 50%) 0%, hsl(150 75% 52%) 34%, hsl(176 80% 40%) 70%, hsl(188 78% 34%) 100%);',

		// Oil-slick: four offset radial washes over a long linear ramp, so the
		// surface shifts hue by region rather than along one axis. Every stop is
		// held light enough for the near-black ink to stay legible.
		'iridescent' => $lime . ' --primary-deep: 255 60% 32%; --brand-gradient:'
			. ' radial-gradient(130% 150% at 8% 0%, hsl(72 100% 56%) 0%, hsl(72 100% 56% / 0) 48%),'
			. ' radial-gradient(120% 130% at 98% 6%, hsl(308 95% 78% / 0.7) 0%, hsl(308 95% 78% / 0) 46%),'
			. ' radial-gradient(140% 140% at 90% 100%, hsl(255 95% 74%) 0%, hsl(255 95% 74% / 0) 58%),'
			. ' radial-gradient(120% 120% at 18% 96%, hsl(168 95% 60%) 0%, hsl(168 95% 60% / 0) 52%),'
			. ' linear-gradient(130deg, hsl(80 100% 52%) 0%, hsl(160 88% 62%) 30%, hsl(192 92% 66%) 55%,'
			. ' hsl(248 92% 74%) 80%, hsl(300 88% 78%) 100%);',

		// The current staging mint, for a like-for-like A/B.
		'mint'   => '',
	);

	if ( 'iridescent-anim' === $key ) {
		$variants['iridescent-anim'] = $variants['iridescent'];
	}

	if ( ! isset( $variants[ $key ] ) || '' === $variants[ $key ] ) {
		return;
	}

	// The animated option reuses the oil-slick ramp and drifts it.
	$panels = '.hero-tile--cta, .services-intro, .about-stat';
	$extra  = '';

	if ( 'iridescent-anim' === $key ) {
		$extra = sprintf(
			'%1$s{background-size:230%% 230%%;animation:le-irid 26s ease-in-out infinite;}'
			. '@keyframes le-irid{0%%,100%%{background-position:0%% 50%%;}'
			. '33%%{background-position:100%% 0%%;}66%%{background-position:50%% 100%%;}}'
			. '@media (prefers-reduced-motion: reduce){%1$s{animation:none;background-size:auto;}}',
			$panels
		);
	}

	printf(
		"<style id=\"le-gradient-trial\">:root{%s}%s</style>\n",
		$variants[ $key ], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hard-coded CSS above.
		$extra // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hard-coded CSS above.
	);
}
add_action( 'wp_head', 'le_gradient_trial_css', 3 );
