<?php
/**
 * Archive: Work.
 *
 * Scaffold listing that loops the CPT and renders card parts. Filtering,
 * animations and layout refinements land in Phase 2.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="site-main site-main--archive site-main--work">

	<header class="archive__header container">
		<?php le_breadcrumbs(); ?>
		<p class="archive__eyebrow"><?php esc_html_e( 'Selected projects', 'lewisedward' ); ?></p>
		<h1 class="archive__title"><?php post_type_archive_title(); ?></h1>
	</header>

	<div class="archive__grid container">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/cards/card', 'work' );
			endwhile;
			?>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing here yet.', 'lewisedward' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="archive__pagination container">
		<?php the_posts_pagination(); ?>
	</div>

</main>

<?php
get_footer();
