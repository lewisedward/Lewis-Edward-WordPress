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
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-JPJ8G70E66"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'G-JPJ8G70E66');
	</script>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="scroll-progress" aria-hidden="true"><span class="scroll-progress__bar" data-scroll-progress></span></div>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'lewisedward' ); ?></a>

<div class="site-wrapper" data-lenis-wrapper>

	<?php get_template_part( 'template-parts/header/navbar' ); ?>

	<div id="content" class="site-content">
