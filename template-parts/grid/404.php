<?php
/**
 * Grid skin — 404.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<main id="main" class="site-main site-main--404 g-main">
	<section class="g-row g-404" aria-label="<?php esc_attr_e( 'Page not found', 'lewisedward' ); ?>">
		<div class="g-cell g-lime g-phero__main">
			<div class="g-phero__top"><span class="g-label g-mono"><?php esc_html_e( 'Error', 'lewisedward' ); ?></span><span class="g-mono"><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></span></div>
			<p class="g-404__code" aria-hidden="true">404</p>
		</div>
		<div class="g-cell g-phero__main">
			<div class="g-phero__top"><span class="g-mono"><?php esc_html_e( 'Not found', 'lewisedward' ); ?></span></div>
			<h1 class="g-phero__title"><?php echo le_grid_tone_title( __( 'Page not found', 'lewisedward' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
			<div class="g-phero__foot">
				<p class="g-phero__lede"><?php esc_html_e( "The page you're after has moved or never existed.", 'lewisedward' ); ?></p>
			</div>
			<div class="g-phero__tags" style="gap:12px">
				<a class="g-btn g-mono" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span><?php esc_html_e( 'Back to home', 'lewisedward' ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<a class="g-btn g-mono" href="<?php echo esc_url( home_url( '/work' ) ); ?>"><span><?php esc_html_e( 'View our work', 'lewisedward' ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</div>
		</div>
	</section>
</main>
