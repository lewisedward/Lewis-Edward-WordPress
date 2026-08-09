<?php
/**
 * Home section: About.
 *
 * Fully ACF-driven. Text prefilled via ACF defaults; portrait is an ACF image
 * field and the stat tiles come from the `home_about_stats` repeater (empty
 * until populated). SEO: section heading is the single <h2>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$le_portrait   = le_field( 'home_about_portrait' );
$le_portrait_u = ( is_array( $le_portrait ) && ! empty( $le_portrait['url'] ) ) ? $le_portrait['url'] : '';
$le_portrait_a = ( is_array( $le_portrait ) && ! empty( $le_portrait['alt'] ) ) ? $le_portrait['alt'] : '';
$le_stats      = le_field( 'home_about_stats' );
$le_stats      = is_array( $le_stats ) ? $le_stats : array();
?>
<section class="section section--about" aria-label="<?php esc_attr_e( 'About Lewis Edward', 'lewisedward' ); ?>">
	<div class="section__inner">
		<div class="about-card glass" data-reveal>

			<div class="about-card__head">
				<span class="eyebrow">
					<?php echo esc_html( le_field( 'home_about_eyebrow' ) ); ?><sup class="about-card__count"><?php echo esc_html( le_field( 'home_about_year' ) ); ?></sup>
				</span>
				<span class="about-card__rule" aria-hidden="true"></span>
				<span class="pulse-dot" aria-hidden="true"></span>
			</div>

			<div class="about-card__composition">
				<div class="about-card__portrait">
					<?php if ( $le_portrait_u ) : ?>
						<img src="<?php echo esc_url( $le_portrait_u ); ?>" alt="<?php echo esc_attr( $le_portrait_a ); ?>" loading="lazy" data-parallax-img />
					<?php endif; ?>
				</div>
				<div class="about-card__headline-wrap">
					<h2 class="about-card__headline">
						<?php echo esc_html( le_field( 'home_about_headline_1' ) ); ?>
						<span class="about-card__muted"><?php echo esc_html( le_field( 'home_about_headline_muted' ) ); ?> <span class="text-primary"><?php echo esc_html( le_field( 'home_about_headline_since' ) ); ?></span></span>
						<span class="text-primary"><?php echo esc_html( le_field( 'home_about_headline_year' ) ); ?></span>
					</h2>
				</div>
			</div>

			<div class="about-card__body">
				<p class="about-card__lede"><?php echo esc_html( le_field( 'home_about_lede' ) ); ?></p>
				<a class="arrow-link about-card__more" href="<?php echo esc_url( home_url( '/about' ) ); ?>" aria-label="<?php esc_attr_e( 'More about us', 'lewisedward' ); ?>">
					<span class="arrow-link__badge" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="arrow-link__label"><?php esc_html_e( 'More about us', 'lewisedward' ); ?></span>
				</a>
			</div>

			<?php if ( ! empty( $le_stats ) ) : ?>
				<div class="about-card__stats">
					<?php foreach ( $le_stats as $stat ) : ?>
						<?php
						$s_num    = isset( $stat['num'] ) ? $stat['num'] : '';
						$s_val    = isset( $stat['value'] ) ? $stat['value'] : '';
						$s_suffix = isset( $stat['suffix'] ) ? $stat['suffix'] : '';
						$s_label  = isset( $stat['label'] ) ? $stat['label'] : '';
						?>
						<div class="about-stat">
							<span class="about-stat__num"><?php echo esc_html( $s_num ); ?></span>
							<span class="about-stat__value" data-counter="<?php echo esc_attr( (string) $s_val ); ?>" data-counter-suffix="<?php echo esc_attr( $s_suffix ); ?>" data-counter-duration="1800"><?php echo esc_html( $s_val . $s_suffix ); ?></span>
							<span class="about-stat__label"><?php echo esc_html( $s_label ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
