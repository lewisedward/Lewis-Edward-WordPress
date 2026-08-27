<?php
/**
 * Home section: Services.
 *
 * Fully ACF-driven. Intro text prefilled via ACF defaults; the cards come from
 * the `home_services_items` Relationship (Service posts). Empty until curated.
 * SEO: section heading <h2>, each card title <h3>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$le_services = le_field( 'home_services_items' );
$le_services = is_array( $le_services ) ? $le_services : array();
$le_count    = count( $le_services );
?>
<section class="section section--services" id="services" aria-label="<?php esc_attr_e( 'Services', 'lewisedward' ); ?>">
	<div class="section__inner">
		<div class="services__grid">

			<?php /* ---- Sticky intro tile ---- */ ?>
			<div class="services-intro" data-surface="light" data-reveal>
				<div class="services-intro__head">
					<p class="eyebrow eyebrow--strong services-intro__eyebrow">
						<?php echo esc_html( le_field( 'home_services_eyebrow' ) ); ?><?php if ( $le_count ) : ?><sup class="services-intro__count"><?php echo esc_html( (string) $le_count ); ?></sup><?php endif; ?>
					</p>
					<span class="radar-dot" aria-hidden="true">
						<span class="radar-dot__ring"></span>
						<span class="radar-dot__ring radar-dot__ring--delay"></span>
						<span class="radar-dot__core"></span>
					</span>
				</div>

				<h2 class="services-intro__title"><?php echo esc_html( le_field( 'home_services_title' ) ); ?></h2>

				<p class="services-intro__lede"><?php echo esc_html( le_field( 'home_services_lede' ) ); ?></p>

				<a class="services-intro__all" href="<?php echo esc_url( home_url( '/services' ) ); ?>" aria-label="<?php esc_attr_e( 'See all services', 'lewisedward' ); ?>">
					<span class="services-intro__badge" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="eyebrow services-intro__all-label"><?php esc_html_e( 'All services', 'lewisedward' ); ?></span>
				</a>
			</div>

			<?php /* ---- Stacked service cards ---- */ ?>
			<?php if ( $le_count ) : ?>
				<div class="services-list">
					<?php
					foreach ( $le_services as $i => $svc ) :
						$sid = is_object( $svc ) ? $svc->ID : (int) $svc;
						?>
						<a class="service-card glass" href="<?php echo esc_url( get_permalink( $sid ) ); ?>" data-cursor-ignore data-reveal>
							<div class="service-card__head">
								<span class="eyebrow"><?php echo esc_html( sprintf( __( 'Service / %s', 'lewisedward' ), str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ) ); ?></span>
								<span class="service-card__rule" aria-hidden="true"></span>
								<span class="eyebrow service-card__explore"><?php esc_html_e( 'Explore', 'lewisedward' ); ?></span>
							</div>
							<div class="service-card__body">
								<h3 class="service-card__title"><?php echo esc_html( get_the_title( $sid ) ); ?></h3>
								<p class="service-card__desc"><?php echo esc_html( le_field( 'service_short_description', $sid ) ); ?></p>
							</div>
						</a>
						<?php
					endforeach;
					?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
