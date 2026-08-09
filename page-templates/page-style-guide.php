<?php
/**
 * Template Name: Style Guide
 *
 * Scaffold page template. Section content is built in Phase 2 from the
 * matching React page and wired to ACF fields.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="site-main site-main--style-guide">

	<header class="page__header container">
		<?php le_breadcrumbs(); ?>
		<p class="page__eyebrow"><?php esc_html_e( 'Design system', 'lewisedward' ); ?></p>
		<h1 class="page__title"><?php the_title(); ?></h1>
	</header>

	<div class="page__content container prose">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
		<!-- TODO(Phase 2): build "Style Guide" sections here. -->
	</div>

</main>

<?php
get_footer();
