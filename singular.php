<?php
/**
 * Generic singular fallback (single posts / any CPT without a dedicated single).
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="site-main site-main--singular">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'entry' ); ?>>
			<header class="entry__header container">
				<?php le_breadcrumbs(); ?>
				<h1 class="entry__title"><?php the_title(); ?></h1>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="entry__media container">
					<?php the_post_thumbnail( 'le_hero' ); ?>
				</figure>
			<?php endif; ?>

			<div class="entry__content container prose">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();
