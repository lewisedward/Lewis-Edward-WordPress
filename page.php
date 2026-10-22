<?php
/**
 * Default page template.
 *
 * Generic pages without a dedicated page-template fall back here.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( le_grid_skin() ) {
	while ( have_posts() ) {
		the_post();
		get_template_part( 'template-parts/grid/legal' );
	}
	get_footer();
	return;
}
?>

<main id="main" class="site-main site-main--page">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'page' ); ?>>
			<header class="page__header container">
				<?php le_breadcrumbs(); ?>
				<h1 class="page__title"><?php the_title(); ?></h1>
			</header>

			<div class="page__content container prose">
				<?php the_content(); ?>
				<?php
				wp_link_pages( array(
					'before' => '<nav class="page-links">',
					'after'  => '</nav>',
				) );
				?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();
