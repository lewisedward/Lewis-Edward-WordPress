<?php
/**
 * Template Name: About
 *
 * The About page — fully ACF-driven (field group "About Page"). No fallback
 * content: every value comes from ACF and each block only renders when it has
 * content. Sections: hero + founder bio, "why choose us" cards, Team grid
 * (Team CPT via Relationship), Core Values, and the shared Contact CTA.
 *
 * SEO: the hero carries the single <h1>; section headings are <h2>; team member
 * and value titles are <h3>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// -------- Hero --------
$ab_portrait   = le_field( 'about_portrait' );
$ab_portrait_u = ( is_array( $ab_portrait ) && ! empty( $ab_portrait['url'] ) ) ? $ab_portrait['url'] : '';
$ab_portrait_a = ( is_array( $ab_portrait ) && ! empty( $ab_portrait['alt'] ) ) ? $ab_portrait['alt'] : '';
$ab_why        = le_field( 'about_why' );
$ab_why        = is_array( $ab_why ) ? $ab_why : array();

// -------- Team --------
$ab_team = le_field( 'about_team_members' );
$ab_team = is_array( $ab_team ) ? $ab_team : array();

// -------- Core values --------
$ab_values = le_field( 'about_values' );
$ab_values = is_array( $ab_values ) ? $ab_values : array();
?>

<main id="main" class="site-main site-main--about">

	<?php /* ================= HERO ================= */ ?>
	<section class="section section--about-hero" aria-label="<?php esc_attr_e( 'About Lewis Edward', 'lewisedward' ); ?>">
		<div class="section__inner">
			<div class="about-hero glass" data-reveal>

				<div class="about-hero__eyebrow-row">
					<span class="eyebrow"><?php echo esc_html( le_field( 'about_eyebrow' ) ); ?><?php $sup = le_field( 'about_eyebrow_sup' ); if ( $sup ) : ?><sup class="about-hero__sup"><?php echo esc_html( $sup ); ?></sup><?php endif; ?></span>
					<span class="about-hero__rule" aria-hidden="true"></span>
					<span class="eyebrow about-hero__loc"><?php echo esc_html( le_field( 'about_location' ) ); ?></span>
					<span class="pulse-dot" aria-hidden="true"></span>
				</div>

				<div class="about-hero__sphere" aria-hidden="true" data-hero-sphere>
					<canvas class="hero-sphere__canvas"></canvas>
				</div>

				<h1 class="about-hero__title">
					<span><?php echo esc_html( le_field( 'about_h1_1' ) ); ?></span>
					<span class="about-hero__title-muted"><?php echo esc_html( le_field( 'about_h1_2' ) ); ?></span>
					<span class="text-primary"><?php echo esc_html( le_field( 'about_h1_3' ) ); ?></span>
				</h1>

				<p class="about-hero__lead"><?php echo esc_html( le_field( 'about_lead' ) ); ?></p>

				<div class="about-hero__composition">
					<div class="about-hero__portrait">
						<?php if ( $ab_portrait_u ) : ?>
							<img src="<?php echo esc_url( $ab_portrait_u ); ?>" alt="<?php echo esc_attr( $ab_portrait_a ); ?>" loading="eager" decoding="async" />
							<span class="about-hero__portrait-fade" aria-hidden="true"></span>
						<?php endif; ?>
					</div>

					<div class="about-bio glass">
						<blockquote class="about-bio__quote">
							<span class="about-bio__bar" aria-hidden="true"></span>
							<span class="about-bio__mark" aria-hidden="true">
								<svg width="32" height="24" viewBox="0 0 32 24" fill="none" aria-hidden="true"><path d="M0 24V11.1241C0 7.3931 1.05655 4.3931 3.16966 2.12414C5.35172 0.708276 8.35172 0 12.1697 0V5.21379C9.89793 5.21379 8.38897 5.7931 7.64276 6.95172C6.96552 8.04138 6.6269 9.38759 6.62621 10.9903H12.1697V24H0ZM19.8303 24V11.1241C19.8303 7.3931 20.8869 4.3931 22.9993 2.12414C25.1821 0.708276 28.1821 0 32 0V5.21379C29.7283 5.21379 28.2193 5.7931 27.4731 6.95172C26.7959 8.04138 26.4572 9.38759 26.4566 10.9903H32V24H19.8303Z" fill="currentColor"/></svg>
							</span>
							<p class="about-bio__heading"><?php echo esc_html( le_field( 'about_bio_heading' ) ); ?> <span class="about-bio__heading-muted"><?php echo esc_html( le_field( 'about_bio_heading_muted' ) ); ?></span></p>

							<div class="about-bio__body"><?php echo wp_kses_post( le_field( 'about_bio' ) ); ?></div>

							<footer class="about-bio__footer">
								<span class="about-bio__sig">
									<span class="about-bio__sig-line" aria-hidden="true"></span>
									<span class="about-bio__sig-text">
										<cite class="about-bio__name"><?php echo esc_html( le_field( 'about_bio_name' ) ); ?></cite>
										<span class="about-bio__role"><?php echo esc_html( le_field( 'about_bio_role' ) ); ?></span>
									</span>
								</span>
								<a class="arrow-link about-bio__cta" href="<?php echo esc_url( home_url( '/contact' ) ); ?>" aria-label="<?php esc_attr_e( 'Get in touch', 'lewisedward' ); ?>">
									<span class="arrow-link__label"><?php esc_html_e( 'Get in touch', 'lewisedward' ); ?></span>
									<span class="arrow-link__badge" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								</a>
							</footer>
						</blockquote>
					</div>
				</div>

				<?php if ( ! empty( $ab_why ) ) : ?>
					<div class="about-why">
						<?php foreach ( $ab_why as $item ) : ?>
							<div class="about-why__card glass">
								<span class="about-why__icon"><?php echo le_about_icon( isset( $item['icon'] ) ? $item['icon'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<span class="eyebrow about-why__label"><?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>
		</div>
	</section>

	<?php /* ================= TEAM ================= */ ?>
	<?php if ( ! empty( $ab_team ) ) : ?>
		<section class="section section--about-team" aria-label="<?php esc_attr_e( 'Team', 'lewisedward' ); ?>">
			<div class="section__inner">
				<div class="team-card glass" data-reveal>
					<div class="team-card__head">
						<span class="eyebrow"><?php echo esc_html( le_field( 'about_team_eyebrow' ) ); ?><sup class="team-card__count"><?php echo esc_html( str_pad( (string) count( $ab_team ), 2, '0', STR_PAD_LEFT ) ); ?></sup></span>
						<span class="team-card__rule" aria-hidden="true"></span>
						<span class="pulse-dot" aria-hidden="true"></span>
					</div>

					<h2 class="team-card__title"><?php echo esc_html( le_field( 'about_team_h1' ) ); ?> <span class="text-primary"><?php echo esc_html( le_field( 'about_team_h_accent' ) ); ?></span> <span class="about-muted"><?php echo esc_html( le_field( 'about_team_h_muted' ) ); ?></span></h2>

					<div class="team-grid">
						<?php
						foreach ( $ab_team as $member ) :
							$mid = is_object( $member ) ? $member->ID : (int) $member;
							?>
							<div class="team-member glass">
								<div class="team-member__photo">
									<?php if ( has_post_thumbnail( $mid ) ) { echo get_the_post_thumbnail( $mid, 'le_portrait', array( 'alt' => esc_attr( get_the_title( $mid ) ), 'loading' => 'lazy', 'decoding' => 'async' ) ); } ?>
									<span class="team-member__fade" aria-hidden="true"></span>
								</div>
								<h3 class="team-member__name"><?php echo esc_html( get_the_title( $mid ) ); ?></h3>
								<p class="eyebrow team-member__role"><?php echo esc_html( le_field( 'team_role', $mid ) ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ================= CORE VALUES ================= */ ?>
	<?php if ( ! empty( $ab_values ) ) : ?>
		<section class="section section--about-values" aria-label="<?php esc_attr_e( 'Core Values', 'lewisedward' ); ?>">
			<div class="section__inner">
				<div class="values-card glass" data-reveal>
					<div class="values-card__head">
						<span class="eyebrow"><?php echo esc_html( le_field( 'about_values_eyebrow' ) ); ?><sup class="values-card__count"><?php echo esc_html( str_pad( (string) count( $ab_values ), 2, '0', STR_PAD_LEFT ) ); ?></sup></span>
						<span class="values-card__rule" aria-hidden="true"></span>
						<span class="pulse-dot" aria-hidden="true"></span>
					</div>

					<h2 class="values-card__title"><?php echo esc_html( le_field( 'about_values_h1' ) ); ?> <span class="text-primary"><?php echo esc_html( le_field( 'about_values_h_accent' ) ); ?></span> <span class="about-muted"><?php echo esc_html( le_field( 'about_values_h_muted' ) ); ?></span></h2>

					<div class="values-grid">
						<?php foreach ( $ab_values as $i => $value ) : ?>
							<div class="value-card glass">
								<span class="eyebrow value-card__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								<h3 class="value-card__title"><?php echo esc_html( isset( $value['title'] ) ? $value['title'] : '' ); ?></h3>
								<p class="value-card__desc"><?php echo esc_html( isset( $value['description'] ) ? $value['description'] : '' ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ================= CONTACT CTA (shared) ================= */ ?>
	<?php get_template_part( 'template-parts/home/contact-cta' ); ?>

</main>

<?php
wp_enqueue_script( 'le-hero' ); // dot-sphere in the hero
get_footer();
