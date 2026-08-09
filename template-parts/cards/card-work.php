<?php
/**
 * Card: work.
 *
 * Used in archives and related listings. Expects to run inside the loop.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'card card--work' ); ?>>
	<a class="card__link" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="card__media">
				<?php the_post_thumbnail( 'le_hero' ); ?>
			</figure>
		<?php endif; ?>
		<div class="card__body">
			<h3 class="card__title"><?php the_title(); ?></h3>
			<div class="card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></div>
		</div>
	</a>
</article>
