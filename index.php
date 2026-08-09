<?php
/**
 * Fallback template.
 *
 * WordPress uses this when no more specific template matches. Individual
 * views (front-page, single-*, archive-*, page-templates) override it.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="site-main">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="post-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'post-list__item' ); ?>>
						<h2 class="post-list__title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>
						<div class="post-list__excerpt"><?php the_excerpt(); ?></div>
					</article>
					<?php
				endwhile;

				the_posts_pagination();
				?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing found.', 'lewisedward' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
