<?php
/**
 * Site header — opens the document and renders the navbar.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php // Marks JS as available before first paint, so reveal animations don't hide content for no-JS users and don't flash. ?>
	<script>document.documentElement.classList.add('js-anim');</script>
	<?php wp_head(); ?>
	<?php
	/*
	 * Google tag (gtag.js) - deferred until the visitor engages.
	 *
	 * Loading it in <head> cost ~261ms of main-thread time and produced the
	 * only two long tasks on the page, which pushed desktop Total Blocking
	 * Time over the 150ms mark and made the Lighthouse score swing between
	 * 89 and 100 run to run.
	 *
	 * The measurement stub below is Google's own: gtag() pushes onto
	 * dataLayer, and the library replays that queue when it eventually loads,
	 * so the 'js' and 'config' calls are not lost. Only the 155KB library
	 * download and its execution are postponed - to the first real
	 * interaction, or 10s, whichever comes first.
	 *
	 * Known trade: a visit that leaves within 10s without scrolling, tapping
	 * or typing is never recorded, so reported sessions will step down and
	 * will not line up with history from before this change.
	 */
	?>
	<!-- Google tag (gtag.js) -->
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'G-JPJ8G70E66');

		(function () {
			var loaded = false;
			var timer;
			var opts = { once: true, passive: true, capture: true };
			var events = ['pointerdown', 'keydown', 'scroll', 'touchstart'];

			function loadGtag() {
				if (loaded) { return; }
				loaded = true;
				clearTimeout(timer);
				events.forEach(function (evt) { window.removeEventListener(evt, loadGtag, opts); });

				var s = document.createElement('script');
				s.async = true;
				s.src = 'https://www.googletagmanager.com/gtag/js?id=G-JPJ8G70E66';
				document.head.appendChild(s);
			}

			events.forEach(function (evt) { window.addEventListener(evt, loadGtag, opts); });
			timer = setTimeout(loadGtag, 10000);
		})();
	</script>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="scroll-progress" aria-hidden="true"><span class="scroll-progress__bar" data-scroll-progress></span></div>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'lewisedward' ); ?></a>

<div class="site-wrapper" data-lenis-wrapper>

	<?php get_template_part( 'template-parts/header/navbar' ); ?>

	<div id="content" class="site-content">
