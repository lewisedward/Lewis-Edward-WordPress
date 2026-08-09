<?php
/**
 * Single content: work.
 *
 * Scaffold body for a single work. ACF-driven sections (hero, meta,
 * galleries, related) are layered on in Phase 2 once the field groups exist.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'single single--work' ); ?>>

	<header class="single__header container">
		<?php le_breadcrumbs(); ?>
		<h1 class="single__title"><?php the_title(); ?></h1>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="single__media container">
			<?php the_post_thumbnail( 'le_hero' ); ?>
		</figure>
	<?php endif; ?>

	<div class="single__content container prose">
		<?php the_content(); ?>
	</div>

</article>
