<?php
/**
 * Homepage (front page).
 *
 * Scaffold that assembles the homepage from modular section parts. Each part
 * is a stub for now and gets built out in Phase 2 (Home). The section order
 * mirrors the React Index page: Hero -> Stats -> Services -> Showcase/Recent
 * Work -> Process -> Testimonials -> Contact CTA.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="site-main site-main--home">

	<?php
	// Section order mirrors the React Index page.
	get_template_part( 'template-parts/home/hero' );
	get_template_part( 'template-parts/home/client-logos' );
	get_template_part( 'template-parts/home/recent-work' );
	get_template_part( 'template-parts/home/services' );
	get_template_part( 'template-parts/home/text-image-overlap' );
	get_template_part( 'template-parts/home/about' );
	get_template_part( 'template-parts/home/testimonials' );
	get_template_part( 'template-parts/home/contact-cta' );
	?>

</main>

<?php
get_footer();
