<?php
/**
 * Single Work — case study (/work/{slug}).
 *
 * Fully ACF-driven (field group "Work — Details"). Each block only renders when
 * it has content. Sections: hero (title, description, tags, featured image),
 * "at a glance" (year/duration/platform/services + tech stack + live link),
 * challenge & solution, results, a gallery, and next projects. Then the shared
 * Contact CTA.
 *
 * SEO: the project title is the single <h1>; section headings are <h2>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$wk_id       = get_the_ID();
	$wk_desc     = le_field( 'work_card_description', $wk_id );
	$wk_hero_desc = le_field( 'work_hero_description', $wk_id );
	$wk_year     = le_field( 'work_year', $wk_id );
	$wk_dur      = le_field( 'work_duration', $wk_id );
	$wk_plat     = le_field( 'work_platform', $wk_id );
	$wk_live     = le_field( 'work_live_url', $wk_id );
	$wk_services = le_field( 'work_services', $wk_id );
	$wk_services = is_array( $wk_services ) ? $wk_services : array();
	$wk_tech     = le_field( 'work_technologies', $wk_id );
	$wk_tech     = is_array( $wk_tech ) ? $wk_tech : array();
	$wk_chall    = le_field( 'work_challenge', $wk_id );
	$wk_sol      = le_field( 'work_solution', $wk_id );
	$wk_res      = le_field( 'work_results', $wk_id );
	$wk_res      = is_array( $wk_res ) ? $wk_res : array();
	$wk_feats    = le_field( 'work_features', $wk_id );
	$wk_feats    = is_array( $wk_feats ) ? $wk_feats : array();

	/** Normalise a Services relationship entry to a WP_Post. */
	$wk_service_post = function ( $s ) {
		return is_object( $s ) ? $s : get_post( (int) $s );
	};

	$has_glance = ( $wk_year || $wk_dur || $wk_plat || ! empty( $wk_services ) || ! empty( $wk_tech ) || $wk_live );
	?>

	<main id="main" class="site-main site-main--work-single">

		<?php /* ================= HERO ================= */ ?>
		<section class="section section--wk-hero" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
			<div class="section__inner">
				<div class="wk-hero glass" data-reveal>
					<div class="wk-hero__sphere" aria-hidden="true" data-hero-sphere>
						<canvas class="hero-sphere__canvas"></canvas>
					</div>
					<div class="wk-hero__eyebrow-row">
						<?php $wk_back = le_link( 'wk_hero_back', '/work', __( 'All Projects', 'lewisedward' ), $wk_id ); ?>
						<a class="eyebrow wk-hero__back" href="<?php echo esc_url( $wk_back['url'] ); ?>"<?php le_link_target_attr( $wk_back ); ?>>
							<span aria-hidden="true">&larr;</span> <?php echo esc_html( $wk_back['title'] ); ?>
						</a>
						<span class="wk-hero__rule" aria-hidden="true"></span>
						<?php if ( $wk_year ) : ?><span class="eyebrow wk-hero__year"><?php echo esc_html( $wk_year ); ?></span><?php endif; ?>
						<span class="pulse-dot" aria-hidden="true"></span>
					</div>

					<?php if ( ! empty( $wk_services ) ) : ?>
						<div class="wk-hero__tags">
							<?php
							foreach ( $wk_services as $s ) :
								$sp = $wk_service_post( $s );
								if ( ! $sp ) { continue; }
								?>
								<a class="work-tag work-tag--glass" href="<?php echo esc_url( get_permalink( $sp ) ); ?>"><?php echo esc_html( get_the_title( $sp ) ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<h1 class="wk-hero__title"><?php the_title(); ?></h1>

					<?php if ( $wk_hero_desc ) : ?><p class="wk-hero__desc"><?php echo esc_html( $wk_hero_desc ); ?></p><?php endif; ?>

					<?php if ( has_post_thumbnail() ) : ?>
						<div class="wk-hero__media">
							<?php the_post_thumbnail( 'le_hero', array( 'alt' => esc_attr( get_the_title() ), 'loading' => 'eager', 'decoding' => 'async' ) ); ?>
							<span class="wk-hero__grad" aria-hidden="true"></span>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<?php /* ================= AT A GLANCE ================= */ ?>
		<?php if ( $has_glance ) : ?>
			<section class="section section--wk-glance" aria-label="<?php esc_attr_e( 'Project details', 'lewisedward' ); ?>">
				<div class="section__inner">
					<div class="wk-glance glass" data-reveal>
						<div class="wk-glance__head">
							<span class="eyebrow"><?php echo esc_html( le_field( 'wk_glance_eyebrow', $wk_id, __( 'At a glance', 'lewisedward' ) ) ); ?></span>
							<span class="wk-glance__rule" aria-hidden="true"></span>
							<span class="pulse-dot" aria-hidden="true"></span>
						</div>

						<div class="wk-glance__grid">
							<?php if ( $wk_year ) : ?><div class="wk-glance__cell"><span class="eyebrow"><?php echo esc_html( le_field( 'wk_glance_year', $wk_id, __( 'Year', 'lewisedward' ) ) ); ?></span><p><?php echo esc_html( $wk_year ); ?></p></div><?php endif; ?>
							<?php if ( $wk_dur ) : ?><div class="wk-glance__cell"><span class="eyebrow"><?php echo esc_html( le_field( 'wk_glance_duration', $wk_id, __( 'Duration', 'lewisedward' ) ) ); ?></span><p><?php echo esc_html( $wk_dur ); ?></p></div><?php endif; ?>
							<?php if ( $wk_plat ) : ?><div class="wk-glance__cell"><span class="eyebrow"><?php echo esc_html( le_field( 'wk_glance_platform', $wk_id, __( 'Platform', 'lewisedward' ) ) ); ?></span><p><?php echo esc_html( $wk_plat ); ?></p></div><?php endif; ?>
							<?php if ( ! empty( $wk_services ) ) : ?>
								<div class="wk-glance__cell">
									<span class="eyebrow"><?php echo esc_html( le_field( 'wk_glance_services', $wk_id, __( 'Services', 'lewisedward' ) ) ); ?></span>
									<p>
										<?php
										$wk_svc_links = array();
										foreach ( $wk_services as $s ) {
											$sp = $wk_service_post( $s );
											if ( ! $sp ) { continue; }
											$wk_svc_links[] = '<a href="' . esc_url( get_permalink( $sp ) ) . '">' . esc_html( get_the_title( $sp ) ) . '</a>';
										}
										echo wp_kses_post( implode( ', ', $wk_svc_links ) );
										?>
									</p>
								</div>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $wk_tech ) ) : ?>
							<div class="wk-glance__stack">
								<span class="eyebrow"><?php echo esc_html( le_field( 'wk_glance_stack', $wk_id, __( 'Stack', 'lewisedward' ) ) ); ?><sup class="wk-glance__stack-count"><?php echo esc_html( str_pad( (string) count( $wk_tech ), 2, '0', STR_PAD_LEFT ) ); ?></sup></span>
								<div class="wk-glance__tech">
									<?php foreach ( $wk_tech as $row ) : $t = isset( $row['name'] ) ? trim( $row['name'] ) : ''; if ( '' === $t ) { continue; } ?>
										<span class="work-tag work-tag--glass"><?php echo esc_html( $t ); ?></span>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( $wk_live ) : ?>
							<div class="wk-glance__live">
								<a class="arrow-link" href="<?php echo esc_url( $wk_live ); ?>" target="_blank" rel="noopener noreferrer" data-cursor="Visit">
									<span class="arrow-link__badge" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									<span class="arrow-link__label"><?php echo esc_html( le_field( 'wk_visit_label', $wk_id, __( 'Visit website', 'lewisedward' ) ) ); ?></span>
								</a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php /* ================= CHALLENGE & SOLUTION ================= */ ?>
		<?php if ( $wk_chall || $wk_sol ) : ?>
			<section class="section section--wk-brief" aria-label="<?php esc_attr_e( 'Challenge and solution', 'lewisedward' ); ?>">
				<div class="section__inner">
					<div class="wk-brief glass" data-reveal>
						<div class="wk-brief__head">
							<span class="eyebrow"><?php echo esc_html( le_field( 'wk_brief_eyebrow', $wk_id, __( 'Brief', 'lewisedward' ) ) ); ?><sup class="wk-brief__count">02</sup></span>
							<span class="wk-brief__rule" aria-hidden="true"></span>
							<span class="pulse-dot" aria-hidden="true"></span>
						</div>
						<h2 class="wk-brief__title"><?php echo esc_html( le_field( 'wk_brief_title_pre', $wk_id, __( 'The', 'lewisedward' ) ) ); ?> <span class="about-muted"><?php echo esc_html( le_field( 'wk_brief_title_mid', $wk_id, __( 'challenge', 'lewisedward' ) ) ); ?></span> <span class="text-primary"><?php echo esc_html( le_field( 'wk_brief_title_end', $wk_id, __( '& the solution.', 'lewisedward' ) ) ); ?></span></h2>
						<div class="wk-brief__grid">
							<?php if ( $wk_chall ) : ?>
								<div class="wk-brief__block">
									<span class="eyebrow wk-brief__label"><?php echo esc_html( le_field( 'wk_challenge_label', $wk_id, __( 'The Challenge', 'lewisedward' ) ) ); ?></span>
									<p><?php echo esc_html( $wk_chall ); ?></p>
								</div>
							<?php endif; ?>
							<?php if ( $wk_sol ) : ?>
								<div class="wk-brief__block">
									<span class="eyebrow wk-brief__label"><?php echo esc_html( le_field( 'wk_solution_label', $wk_id, __( 'The Solution', 'lewisedward' ) ) ); ?></span>
									<p><?php echo esc_html( $wk_sol ); ?></p>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php /* ================= RESULTS ================= */ ?>
		<?php if ( ! empty( $wk_res ) ) : ?>
			<section class="section section--wk-results" aria-label="<?php esc_attr_e( 'Results', 'lewisedward' ); ?>">
				<div class="section__inner">
					<div class="wk-results glass" data-reveal>
						<div class="wk-results__head">
							<span class="eyebrow"><?php echo esc_html( le_field( 'wk_results_eyebrow', $wk_id, __( 'Results', 'lewisedward' ) ) ); ?><sup class="wk-results__count"><?php echo esc_html( str_pad( (string) count( $wk_res ), 2, '0', STR_PAD_LEFT ) ); ?></sup></span>
							<span class="wk-results__rule" aria-hidden="true"></span>
							<span class="pulse-dot" aria-hidden="true"></span>
						</div>
						<h2 class="wk-results__title"><?php echo esc_html( le_field( 'wk_results_title_pre', $wk_id, __( 'What we', 'lewisedward' ) ) ); ?> <span class="about-muted"><?php echo esc_html( le_field( 'wk_results_title_mid', $wk_id, __( 'delivered for', 'lewisedward' ) ) ); ?></span> <span class="text-primary"><?php echo esc_html( get_the_title() ); ?>.</span></h2>
						<div class="wk-results__grid">
							<?php foreach ( $wk_res as $row ) : $r = isset( $row['result'] ) ? trim( $row['result'] ) : ''; if ( '' === $r ) { continue; } ?>
								<div class="wk-result">
									<span class="wk-result__check" aria-hidden="true">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
									</span>
									<p class="wk-result__text"><?php echo esc_html( $r ); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php /* ================= EXPLORE FEATURES (visual journey) ================= */ ?>
		<?php
		// Keep only rows that actually have an image.
		$wk_feats = array_values( array_filter( $wk_feats, function ( $row ) {
			return ! empty( $row['image']['url'] );
		} ) );
		if ( ! empty( $wk_feats ) ) :
			$wk_feat_n = count( $wk_feats );
			?>
			<section class="section section--wk-features" aria-label="<?php esc_attr_e( 'Visual journey', 'lewisedward' ); ?>">
				<div class="section__inner">
					<div class="wk-features glass" data-reveal data-features>
						<div class="wk-features__head">
							<span class="eyebrow"><?php echo esc_html( le_field( 'wk_features_eyebrow', $wk_id, __( 'Visual journey', 'lewisedward' ) ) ); ?><sup class="wk-features__count"><?php echo esc_html( str_pad( (string) $wk_feat_n, 2, '0', STR_PAD_LEFT ) ); ?></sup></span>
							<span class="wk-features__rule" aria-hidden="true"></span>
							<span class="pulse-dot" aria-hidden="true"></span>
						</div>

						<div class="wk-features__bar">
							<h2 class="wk-features__title"><?php echo esc_html( le_field( 'wk_features_title_pre', $wk_id, __( 'Explore', 'lewisedward' ) ) ); ?> <span class="about-muted"><?php echo esc_html( le_field( 'wk_features_title_mid', $wk_id, __( 'some', 'lewisedward' ) ) ); ?></span> <span class="text-primary"><?php echo esc_html( le_field( 'wk_features_title_end', $wk_id, __( 'features.', 'lewisedward' ) ) ); ?></span></h2>
							<div class="wk-features__nav">
								<button class="wk-features__arrow" type="button" data-features-prev aria-label="<?php esc_attr_e( 'Previous', 'lewisedward' ); ?>" disabled>
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
								</button>
								<button class="wk-features__arrow" type="button" data-features-next aria-label="<?php esc_attr_e( 'Next', 'lewisedward' ); ?>">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
								</button>
							</div>
						</div>

						<div class="wk-features__track" data-features-track>
							<?php foreach ( $wk_feats as $i => $row ) : $img = $row['image']; ?>
								<div class="wk-feature">
									<div class="wk-feature__media">
										<img src="<?php echo esc_url( isset( $img['sizes']['large'] ) ? $img['sizes']['large'] : $img['url'] ); ?>" alt="<?php echo esc_attr( ! empty( $img['alt'] ) ? $img['alt'] : ( isset( $row['description'] ) ? $row['description'] : get_the_title() ) ); ?>" loading="lazy" decoding="async" draggable="false" />
									</div>
									<?php if ( ! empty( $row['description'] ) ) : ?>
										<div class="wk-feature__caption">
											<span class="wk-feature__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
											<p class="wk-feature__text"><?php echo esc_html( $row['description'] ); ?></p>
										</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php /* ================= NEXT PROJECTS ================= */ ?>
		<?php
		$wk_next = new WP_Query( array(
			'post_type'           => 'work',
			'posts_per_page'      => 2,
			'post__not_in'        => array( $wk_id ),
			'orderby'             => 'menu_order date',
			'order'               => 'ASC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		) );
		if ( $wk_next->have_posts() ) :
			?>
			<section class="section section--wk-next" aria-label="<?php esc_attr_e( 'More projects', 'lewisedward' ); ?>">
				<div class="section__inner">
					<div class="wk-next glass" data-reveal>
						<div class="wk-next__head">
							<span class="eyebrow"><?php echo esc_html( le_field( 'wk_next_eyebrow', $wk_id, __( 'More projects', 'lewisedward' ) ) ); ?></span>
							<span class="wk-next__rule" aria-hidden="true"></span>
							<span class="pulse-dot" aria-hidden="true"></span>
						</div>
						<h2 class="wk-next__title"><?php echo esc_html( le_field( 'wk_next_title_pre', $wk_id, __( 'Keep exploring', 'lewisedward' ) ) ); ?> <span class="text-primary"><?php echo esc_html( le_field( 'wk_next_title_end', $wk_id, __( 'our work.', 'lewisedward' ) ) ); ?></span></h2>
						<div class="wk-next__grid">
							<?php
							while ( $wk_next->have_posts() ) :
								$wk_next->the_post();
								$nid = get_the_ID();
								?>
								<a class="work-item" href="<?php the_permalink(); ?>" data-cursor="View">
									<?php if ( has_post_thumbnail() ) : ?>
										<div class="work-item__media">
											<?php the_post_thumbnail( 'le_card_wider_w2', array( 'alt' => esc_attr( get_the_title() ), 'loading' => 'lazy' ) ); ?>
											<span class="work-item__grad" aria-hidden="true"></span>
										</div>
									<?php endif; ?>
									<div class="work-item__body">
										<div class="work-item__text">
											<h3 class="work-item__title"><?php the_title(); ?></h3>
											<p class="work-item__desc"><?php echo esc_html( le_field( 'work_card_description', $nid ) ); ?></p>
										</div>
										<span class="work-item__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									</div>
								</a>
								<?php
							endwhile;
							wp_reset_postdata();
							?>
						</div>
					</div>
				</div>
			</section>
			<?php
		endif;
		?>

		<?php get_template_part( 'template-parts/home/contact-cta' ); ?>

	</main>

	<?php
	if ( ! empty( $wk_feats ) ) {
		wp_enqueue_script( 'le-work-features' );
	}
	wp_enqueue_script( 'le-hero' ); // dot-sphere in the hero
endwhile;

get_footer();
