<?php
/**
 * Template Name: Services
 *
 * The Services page — fully ACF-driven (field group "Services Page"). No
 * fallback content (except the explicit service_excerpt -> service_short_description
 * fallback on the core cards). Sections: hero (quick-links rail, Explore link,
 * status bar, dot-sphere), core services grid (Relationship → Service, with a
 * feature-tags repeater), a process timeline, a specialised-services index, then
 * the shared Testimonials and Contact CTA.
 *
 * SEO: hero carries the single <h1>; section headings <h2>; card/step/list <h3>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$sp_hero  = le_field( 'services_hero_items' );      // hero rail
$sp_hero  = is_array( $sp_hero ) ? $sp_hero : array();

$sp_items = le_field( 'services_items' );            // core grid
$sp_items = is_array( $sp_items ) ? $sp_items : array();
$sp_count = count( $sp_items );

$sp_steps = le_field( 'services_process_steps' );
$sp_steps = is_array( $sp_steps ) ? $sp_steps : array();

$sp_sub   = le_field( 'services_sub_items' );
$sp_sub   = is_array( $sp_sub ) ? $sp_sub : array();

/** Resolve a relationship entry to an ID. */
$sp_id = function ( $item ) {
	return is_object( $item ) ? (int) $item->ID : (int) $item;
};
?>

<main id="main" class="site-main site-main--services">

	<?php /* ================= HERO ================= */ ?>
	<section class="section section--services-hero" aria-label="<?php esc_attr_e( 'WordPress services', 'lewisedward' ); ?>">
		<div class="section__inner">
			<div class="svc-hero" data-reveal>

				<div class="svc-hero__main">
					<div class="svc-hero__sphere" aria-hidden="true" data-hero-sphere>
						<canvas class="hero-sphere__canvas"></canvas>
					</div>

					<div class="svc-hero__eyebrow-row">
						<span class="eyebrow"><?php echo esc_html( le_field( 'services_eyebrow' ) ); ?><?php if ( $sp_count ) : ?><sup class="svc-hero__sup"><?php echo esc_html( str_pad( (string) $sp_count, 2, '0', STR_PAD_LEFT ) ); ?></sup><?php endif; ?></span>
						<span class="svc-hero__rule" aria-hidden="true"></span>
						<span class="eyebrow svc-hero__loc"><?php echo esc_html( le_field( 'services_location' ) ); ?></span>
					</div>

					<h1 class="svc-hero__title">
						<span class="svc-hero__title-1"><?php echo esc_html( le_field( 'services_h1_1' ) ); ?></span>
						<span class="text-primary"><?php echo esc_html( le_field( 'services_h1_2' ) ); ?></span>
						<span class="svc-hero__title-3"><?php echo esc_html( le_field( 'services_h1_3' ) ); ?></span>
					</h1>

					<div class="svc-hero__foot">
						<div class="svc-hero__intro">
							<?php $il = le_field( 'services_intro_label' ); if ( $il ) : ?><p class="eyebrow svc-hero__intro-label"><?php echo esc_html( $il ); ?></p><?php endif; ?>
							<div class="svc-hero__intro-text"><?php echo wp_kses_post( le_field( 'services_intro' ) ); ?></div>
						</div>

						<?php $explore = le_field( 'services_explore_label' ); if ( $explore ) : ?>
							<span class="svc-hero__divider" aria-hidden="true"></span>
							<a class="svc-hero__explore" href="#services" aria-label="<?php esc_attr_e( 'Explore the services', 'lewisedward' ); ?>">
								<span class="svc-hero__explore-label"><?php echo esc_html( $explore ); ?></span>
								<span class="svc-hero__explore-arrow" aria-hidden="true">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
								</span>
							</a>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( ! empty( $sp_hero ) ) : ?>
					<div class="svc-hero__rail">
						<?php
						$rail_total = count( $sp_hero );
						foreach ( $sp_hero as $r => $item ) :
							$rid = $sp_id( $item );
							?>
							<a class="svc-rail__item<?php echo $r < $rail_total - 1 ? ' svc-rail__item--divided' : ''; ?>" href="<?php echo esc_url( get_permalink( $rid ) ); ?>">
								<span class="svc-rail__text">
									<span class="svc-rail__num"><?php echo esc_html( str_pad( (string) ( $r + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
									<span class="svc-rail__title"><?php echo esc_html( get_the_title( $rid ) ); ?></span>
								</span>
								<span class="svc-rail__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 12 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="svc-hero__status">
					<span class="svc-hero__coords">
						<span class="pulse-dot" aria-hidden="true"></span>
						<span class="eyebrow"><?php echo esc_html( le_field( 'services_coords' ) ); ?></span>
					</span>
					<span class="svc-hero__scroll">
						<span class="eyebrow svc-hero__scroll-label"><?php echo esc_html( le_field( 'services_scroll_label' ) ); ?></span>
						<span class="svc-hero__scroll-line" aria-hidden="true"><span class="svc-hero__scroll-dash"></span></span>
					</span>
				</div>

			</div>
		</div>
	</section>

	<?php /* ================= CORE SERVICES ================= */ ?>
	<?php if ( $sp_count ) : ?>
		<section id="services" class="section section--services-core" aria-label="<?php esc_attr_e( 'Core services', 'lewisedward' ); ?>">
			<div class="section__inner">
				<div class="svc-core glass" data-reveal>
					<div class="svc-core__head">
						<span class="eyebrow"><?php echo esc_html( le_field( 'services_core_eyebrow' ) ); ?><sup class="svc-core__count"><?php echo esc_html( str_pad( (string) $sp_count, 2, '0', STR_PAD_LEFT ) ); ?></sup></span>
						<span class="svc-core__rule" aria-hidden="true"></span>
						<span class="eyebrow svc-core__note"><?php echo esc_html( le_field( 'services_core_note' ) ); ?></span>
						<span class="pulse-dot" aria-hidden="true"></span>
					</div>

					<div class="svc-core__grid">
						<?php
						foreach ( $sp_items as $i => $item ) :
							$id      = $sp_id( $item );
							$excerpt = le_field( 'service_excerpt', $id );
							$desc    = ( '' !== $excerpt ) ? $excerpt : le_field( 'service_short_description', $id );
							$tags    = le_field( 'service_features', $id );
							$tags    = is_array( $tags ) ? $tags : array();
							?>
							<a class="svc-card" href="<?php echo esc_url( get_permalink( $id ) ); ?>" data-cursor="View">
								<span class="svc-card__glow" aria-hidden="true"></span>
								<span class="svc-card__top">
									<span class="svc-card__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
									<span class="svc-card__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								</span>
								<h3 class="svc-card__title"><?php echo esc_html( get_the_title( $id ) ); ?></h3>
								<p class="svc-card__desc"><?php echo esc_html( $desc ); ?></p>
								<?php if ( ! empty( $tags ) ) : ?>
									<span class="svc-card__tags">
										<?php foreach ( $tags as $row ) : $tag = isset( $row['tag'] ) ? trim( $row['tag'] ) : ''; if ( '' === $tag ) { continue; } ?>
											<span class="svc-tag"><?php echo esc_html( $tag ); ?></span>
										<?php endforeach; ?>
									</span>
								<?php endif; ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ================= PROCESS ================= */ ?>
	<?php if ( ! empty( $sp_steps ) ) : ?>
		<section class="section section--services-process" aria-label="<?php esc_attr_e( 'Our process', 'lewisedward' ); ?>">
			<div class="section__inner">
				<div class="svc-process glass" data-reveal>
					<div class="svc-process__head">
						<span class="eyebrow"><?php echo esc_html( le_field( 'services_process_eyebrow' ) ); ?><sup class="svc-process__count"><?php echo esc_html( str_pad( (string) count( $sp_steps ), 2, '0', STR_PAD_LEFT ) ); ?></sup></span>
						<span class="svc-process__rule" aria-hidden="true"></span>
						<span class="eyebrow svc-process__note"><?php echo esc_html( le_field( 'services_process_note' ) ); ?></span>
						<span class="pulse-dot" aria-hidden="true"></span>
					</div>

					<div class="svc-process__grid">
						<div class="svc-process__intro">
							<h2 class="svc-process__title"><?php echo esc_html( le_field( 'services_process_h1' ) ); ?> <span class="about-muted"><?php echo esc_html( le_field( 'services_process_h_muted' ) ); ?></span> <span class="text-primary"><?php echo esc_html( le_field( 'services_process_h_accent' ) ); ?></span></h2>
							<p class="svc-process__lede"><?php echo esc_html( le_field( 'services_process_intro' ) ); ?></p>
							<a class="btn btn--ghost svc-process__cta" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Get in touch', 'lewisedward' ); ?><?php echo le_arrow_diagonal_svg( 12 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
						</div>

						<div class="svc-process__steps">
							<?php foreach ( $sp_steps as $s => $step ) : ?>
								<div class="svc-step" data-cursor="hover">
									<span class="svc-step__num"><?php echo esc_html( str_pad( (string) ( $s + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
									<div class="svc-step__body">
										<h3 class="svc-step__title"><?php echo esc_html( isset( $step['title'] ) ? $step['title'] : '' ); ?></h3>
										<p class="svc-step__desc"><?php echo esc_html( isset( $step['description'] ) ? $step['description'] : '' ); ?></p>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ================= MORE / SUB-SERVICES ================= */ ?>
	<?php if ( ! empty( $sp_sub ) ) : ?>
		<section class="section section--services-sub" aria-label="<?php esc_attr_e( 'Specialised services', 'lewisedward' ); ?>">
			<div class="section__inner">
				<div class="svc-sub glass" data-reveal>
					<div class="svc-sub__head">
						<span class="eyebrow"><?php echo esc_html( le_field( 'services_sub_eyebrow' ) ); ?><sup class="svc-sub__count"><?php echo esc_html( str_pad( (string) count( $sp_sub ), 2, '0', STR_PAD_LEFT ) ); ?></sup></span>
						<span class="svc-sub__rule" aria-hidden="true"></span>
						<span class="eyebrow svc-sub__note"><?php echo esc_html( le_field( 'services_sub_note' ) ); ?></span>
						<span class="pulse-dot" aria-hidden="true"></span>
					</div>

					<h2 class="svc-sub__title"><?php echo esc_html( le_field( 'services_sub_h1' ) ); ?> <span class="about-muted"><?php echo esc_html( le_field( 'services_sub_h_muted' ) ); ?></span> <span class="text-primary"><?php echo esc_html( le_field( 'services_sub_h_accent' ) ); ?></span></h2>

					<ul class="svc-sub__list">
						<?php foreach ( $sp_sub as $i => $item ) : $id = $sp_id( $item ); ?>
							<li class="svc-sub__item">
								<a class="svc-sub__link" href="<?php echo esc_url( get_permalink( $id ) ); ?>" data-cursor="View">
									<span class="svc-sub__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
									<span class="svc-sub__text">
										<h3 class="svc-sub__name"><?php echo esc_html( get_the_title( $id ) ); ?></h3>
										<?php $sp_sub_line = le_field( 'service_subtitle', $id ); ?><?php if ( $sp_sub_line ) : ?><p class="svc-sub__sub"><?php echo esc_html( $sp_sub_line ); ?></p><?php endif; ?>
									</span>
									<span class="svc-sub__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									<span class="svc-sub__underline" aria-hidden="true"></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ================= SHARED ================= */ ?>
	<?php get_template_part( 'template-parts/home/testimonials' ); ?>
	<?php get_template_part( 'template-parts/home/contact-cta' ); ?>

</main>

<?php
wp_enqueue_script( 'le-hero' ); // dot-sphere in the hero
get_footer();
