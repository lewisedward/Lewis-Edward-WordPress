<?php
/**
 * Grid skin — About page. Same fields and outline as page-templates/page-about.php
 * (h1 in the hero; h2 for bio, team and values; h3 per person / value).
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ab_portrait   = le_field( 'about_portrait' );
$ab_portrait_u = ( is_array( $ab_portrait ) && ! empty( $ab_portrait['url'] ) ) ? $ab_portrait['url'] : '';
$ab_portrait_a = ( is_array( $ab_portrait ) && ! empty( $ab_portrait['alt'] ) ) ? $ab_portrait['alt'] : le_field( 'about_bio_name' );
$ab_why        = le_field( 'about_why' );
$ab_why        = is_array( $ab_why ) ? $ab_why : array();
$ab_team       = le_field( 'about_team_members' );
$ab_team       = is_array( $ab_team ) ? $ab_team : array();
$ab_values     = le_field( 'about_values' );
$ab_values     = is_array( $ab_values ) ? $ab_values : array();
$ab_sup        = le_field( 'about_eyebrow_sup' );
$about_bio_cta = le_link( 'about_bio_cta', '/contact', __( 'Get in touch', 'lewisedward' ), false );
?>
<main id="main" class="site-main site-main--about g-main">

	<section class="g-row g-phero" aria-label="<?php esc_attr_e( 'About Lewis Edward', 'lewisedward' ); ?>">
		<div class="g-cell g-phero__main">
			<div class="g-phero__top">
				<span class="g-label g-mono"><?php echo esc_html( le_field( 'about_eyebrow' ) ); ?><?php if ( $ab_sup ) : ?> <span class="g-sec__count"><?php echo esc_html( $ab_sup ); ?></span><?php endif; ?></span>
				<span class="g-mono"><?php echo esc_html( le_field( 'about_location' ) ); ?></span>
			</div>
			<?php $ab_h1_len = mb_strlen( trim( le_field( 'about_h1_1' ) . ' ' . le_field( 'about_h1_2' ) . ' ' . le_field( 'about_h1_3' ) ) ); ?>
			<h1 class="g-phero__title<?php echo $ab_h1_len > 40 ? ' g-phero__title--wide' : ''; ?>"><?php echo le_grid_heading( array( array( le_field( 'about_h1_1' ) ), array( le_field( 'about_h1_2' ), 'muted' ), array( le_field( 'about_h1_3' ), 'accent' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
			<div class="g-phero__foot">
				<p class="g-phero__lede"><?php echo esc_html( le_field( 'about_lead' ) ); ?></p>
			</div>
		</div>
		<?php if ( $ab_portrait_u ) : ?>
		<div class="g-cell g-ph g-ph--mono g-phero__portrait"><?php echo le_grid_img( $ab_portrait, $ab_portrait_a, '(max-width: 1100px) 100vw, 40vw', array( 'fetchpriority' => 'high', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="g-cap g-mono"><?php echo esc_html( le_field( 'about_bio_name' ) ); ?></span></div>
		<?php endif; ?>
	</section>

	<?php if ( $ab_why ) : ?>
	<section class="g-row g-strip" style="--n: <?php echo (int) count( $ab_why ); ?>" aria-label="<?php esc_attr_e( 'Why choose us', 'lewisedward' ); ?>">
		<?php foreach ( $ab_why as $item ) : ?>
		<div class="g-cell g-strip__item"><span class="g-strip__icon" aria-hidden="true"><?php echo le_about_icon( $item['icon'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span class="g-mono"><?php echo esc_html( $item['label'] ?? '' ); ?></span></div>
		<?php endforeach; ?>
	</section>
	<?php endif; ?>

	<section id="bio" class="g-row g-sec" aria-label="<?php esc_attr_e( 'Founder', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'about_bio_role' ), 'title' => le_grid_heading( array( array( le_field( 'about_bio_heading' ) ), array( le_field( 'about_bio_heading_muted' ), 'muted' ) ) ), 'class' => 'g-sec__sticky' ) ); ?>
		<div class="g-cell g-dark g-prose__cell">
			<div class="g-prose"><?php echo wp_kses_post( le_field( 'about_bio' ) ); ?></div>
			<div class="g-sig">
				<span><cite><?php echo esc_html( le_field( 'about_bio_name' ) ); ?></cite><span class="g-mono"><?php echo esc_html( le_field( 'about_bio_role' ) ); ?></span></span>
				<a class="g-line-link g-mono" href="<?php echo esc_url( $about_bio_cta['url'] ); ?>"<?php le_link_target_attr( $about_bio_cta ); ?>><span><?php echo esc_html( $about_bio_cta['title'] ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</div>
		</div>
	</section>

	<?php if ( $ab_team ) : ?>
	<?php $ab_cols = ( 0 === ( count( $ab_team ) + 1 ) % 3 ) ? 3 : 4; ?>
	<section class="g-row g-people g-people--<?php echo (int) $ab_cols; ?>" aria-label="<?php esc_attr_e( 'Team', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'about_team_eyebrow' ), 'count' => str_pad( (string) count( $ab_team ), 2, '0', STR_PAD_LEFT ), 'title' => le_grid_heading( array( array( le_field( 'about_team_h1' ) ), array( le_field( 'about_team_h_accent' ), 'accent' ), array( le_field( 'about_team_h_muted' ), 'muted' ) ) ) ) ); ?>
		<?php foreach ( $ab_team as $member ) : $mid = is_object( $member ) ? (int) $member->ID : (int) $member; ?>
		<div class="g-cell g-person">
			<span class="g-ph g-ph--mono"><?php if ( has_post_thumbnail( $mid ) ) { echo get_the_post_thumbnail( $mid, 'le_portrait', array( 'alt' => esc_attr( get_the_title( $mid ) ), 'loading' => 'lazy', 'decoding' => 'async' ) ); } ?></span>
			<div class="g-person__meta"><h3><?php echo esc_html( get_the_title( $mid ) ); ?></h3><span class="g-mono"><?php echo esc_html( le_field( 'team_role', $mid ) ); ?></span></div>
		</div>
		<?php endforeach; ?>
	</section>
	<?php endif; ?>

	<?php if ( $ab_values ) : ?>
	<section class="g-row g-sec" aria-label="<?php esc_attr_e( 'Core Values', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'about_values_eyebrow' ), 'count' => str_pad( (string) count( $ab_values ), 2, '0', STR_PAD_LEFT ), 'title' => le_grid_heading( array( array( le_field( 'about_values_h1' ) ), array( le_field( 'about_values_h_accent' ), 'accent' ), array( wp_strip_all_tags( (string) le_field( 'about_values_h_muted' ) ), 'muted' ) ) ), 'class' => 'g-sec__sticky' ) ); ?>
		<div class="g-cell <?php echo esc_attr( le_grid_cells_class( count( $ab_values ) ) ); ?>">
			<?php foreach ( $ab_values as $i => $value ) : ?>
			<div class="g-step"><span class="g-step__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><h3 class="g-step__title"><?php echo esc_html( $value['title'] ?? '' ); ?></h3><p class="g-step__desc"><?php echo esc_html( $value['description'] ?? '' ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/grid/cta' ); ?>

</main>
