<?php
/**
 * Grid skin — Privacy policy (ACF body) and the default page template
 * (editor content). Sticky heading beside prose.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lg_privacy = ! empty( $args['privacy'] );
$lg_title   = $lg_privacy ? le_field( 'privacy_h1' ) : get_the_title();
$lg_eyebrow = $lg_privacy ? le_field( 'privacy_eyebrow' ) : '';
$lg_updated = $lg_privacy ? le_field( 'privacy_updated' ) : '';
?>
<main id="main" class="site-main site-main--page g-main">

	<section class="g-row g-sec" aria-label="<?php echo esc_attr( $lg_title ); ?>">
		<div class="g-cell g-sec__intro g-sec__sticky"><div class="g-sec__in">
			<div class="g-sec__top"><?php if ( $lg_eyebrow ) : ?><span class="g-label g-mono"><?php echo esc_html( $lg_eyebrow ); ?></span><?php endif; ?><?php if ( $lg_updated ) : ?><span class="g-mono g-sec__count"><?php esc_html_e( 'Updated', 'lewisedward' ); ?> <?php echo esc_html( $lg_updated ); ?></span><?php endif; ?></div>
			<h1 class="g-phero__title g-phero__title--long"><?php echo le_grid_tone_title( $lg_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
		</div></div>
		<div class="g-cell g-dark g-prose__cell">
			<div class="g-prose">
				<?php
				if ( $lg_privacy ) {
					echo wp_kses_post( le_field( 'privacy_body' ) );
				} else {
					the_content();
					wp_link_pages( array( 'before' => '<nav class="g-pager">', 'after' => '</nav>' ) );
				}
				?>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/grid/cta' ); ?>

</main>
