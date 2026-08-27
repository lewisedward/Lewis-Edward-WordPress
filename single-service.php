<?php
/**
 * Single Service — /services/{slug}.
 *
 * Fully ACF-driven (field group "Service — Details"). Each block only renders
 * when it has content. Sections: hero (breadcrumb, subtitle, headline, intro,
 * CTA, dot-sphere, status bar), "What's included" capability list, process
 * timeline, benefits, FAQ accordion, related services, then the shared
 * Testimonials and Contact CTA.
 *
 * SEO: the service title/headline is the single <h1>; section headings are <h2>,
 * capability/step/question titles are <h3>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render an ACF headline string: lines separated by newlines; a word wrapped in
 * [[double brackets]] becomes lime. Mirrors the React ServiceDetailHero logic —
 * on a multi-line headline the first line is foreground and the rest lime,
 * unless explicit [[markers]] are used.
 *
 * @param string $headline Raw headline.
 */
function le_service_headline( $headline ) {
	$headline = trim( (string) $headline );
	if ( '' === $headline ) {
		return;
	}
	$lines = preg_split( '/\r\n|\r|\n|\\\\n/', $headline );
	$lines = array_values( array_filter( array_map( 'trim', $lines ), 'strlen' ) );
	$total = count( $lines );

	foreach ( $lines as $i => $line ) {
		$has_marker = ( false !== strpos( $line, '[[' ) );
		echo '<span class="svc-d-hero__line">';
		if ( $has_marker ) {
			$parts = preg_split( '/(\[\[.*?\]\])/', $line, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
			foreach ( $parts as $seg ) {
				if ( '[[' === substr( $seg, 0, 2 ) && ']]' === substr( $seg, -2 ) ) {
					echo '<span class="text-primary">' . esc_html( substr( $seg, 2, -2 ) ) . '</span>';
				} else {
					echo wp_kses( $seg, array( 'span' => array( 'class' => array(), 'style' => array() ) ) );
				}
			}
		} else {
			$cls = ( $total > 1 && 0 === $i ) ? '' : 'text-primary';
			// Allow an inline <span> in the field so specific words can be coloured.
			echo '<span class="' . esc_attr( $cls ) . '">' . wp_kses( $line, array( 'span' => array( 'class' => array(), 'style' => array() ) ) ) . '</span>';
		}
		echo '</span>';
	}
}

get_header();

while ( have_posts() ) :
	the_post();
	$sv_id       = get_the_ID();
	$sv_subtitle = le_field( 'service_subtitle', $sv_id );
	$sv_headline = le_field( 'service_headline', $sv_id );
	$sv_intro    = le_field( 'service_intro', $sv_id );
	$whats_included_title    = le_field( 'whats_included_title', $sv_id );


	$sv_feats = le_field( 'service_detail_features', $sv_id );
	$sv_feats = is_array( $sv_feats ) ? $sv_feats : array();
	$sv_proc  = le_field( 'service_process', $sv_id );
	$sv_proc  = is_array( $sv_proc ) ? $sv_proc : array();
	$sv_bens  = le_field( 'service_benefits', $sv_id );
	$sv_bens  = is_array( $sv_bens ) ? $sv_bens : array();
	$sv_faq   = le_field( 'service_faq', $sv_id );
	$sv_faq   = is_array( $sv_faq ) ? $sv_faq : array();
	$sv_relwk = le_field( 'service_related_work', $sv_id );
	$sv_relwk = is_array( $sv_relwk ) ? $sv_relwk : array();
	$sv_rel   = le_field( 'service_related', $sv_id );
	$sv_rel   = is_array( $sv_rel ) ? $sv_rel : array();
	?>

	<main id="main" class="site-main site-main--service-single">

		<?php /* ================= HERO ================= */ ?>
		<section class="section section--svc-d-hero" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
			<div class="section__inner">
				<div class="svc-d-hero glass" data-reveal>

					<div class="svc-d-hero__bar">
						<nav class="svc-d-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'lewisedward' ); ?>">
							<?php $svc_crumb = le_link( 'svc_hero_crumb', '/services', __( 'Services', 'lewisedward' ), $sv_id ); ?>
							<a href="<?php echo esc_url( $svc_crumb['url'] ); ?>" class="eyebrow"<?php le_link_target_attr( $svc_crumb ); ?>><?php echo esc_html( $svc_crumb['title'] ); ?></a>
							<span class="eyebrow svc-d-hero__crumb-current">/ <?php echo esc_html( strtolower( get_the_title() ) ); ?></span>
						</nav>
						<span class="svc-d-hero__rule" aria-hidden="true"></span>
						<?php if ( $sv_subtitle ) : ?><span class="eyebrow svc-d-hero__subtitle"><?php echo esc_html( $sv_subtitle ); ?></span><?php endif; ?>
					</div>

					<div class="svc-d-hero__grid">
						<div class="svc-d-hero__headline-col">
							<div class="svc-d-hero__sphere" aria-hidden="true" data-hero-sphere>
								<canvas class="hero-sphere__canvas"></canvas>
							</div>
							<h1 class="svc-d-hero__headline">
								<?php
								if ( $sv_headline ) {
									le_service_headline( $sv_headline );
								} else {
									echo '<span class="svc-d-hero__line">' . esc_html( get_the_title() ) . '</span>';
								}
								?>
							</h1>
						</div>

						<div class="svc-d-hero__aside">
							<?php if ( $sv_intro ) : ?><p class="svc-d-hero__intro"><?php echo esc_html( $sv_intro ); ?></p><?php endif; ?>
							<?php $svc_hero_cta = le_link( 'svc_hero_cta', '/contact', __( 'Start a project', 'lewisedward' ), $sv_id ); ?>
							<a class="svc-d-hero__cta" href="<?php echo esc_url( $svc_hero_cta['url'] ); ?>"<?php le_link_target_attr( $svc_hero_cta ); ?>>
								<span class="svc-d-hero__cta-badge" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<span class="eyebrow"><?php echo esc_html( $svc_hero_cta['title'] ); ?></span>
							</a>
						</div>
					</div>

					<div class="svc-d-hero__status">
						<span class="svc-d-hero__loc"><span class="pulse-dot" aria-hidden="true"></span> <span class="eyebrow"><?php echo esc_html( le_field( 'svc_hero_status', $sv_id, __( 'LDN / WordPress / 51.5074° N', 'lewisedward' ) ) ); ?></span></span>
						<span class="svc-d-hero__scroll"><span class="eyebrow text-primary"><?php echo esc_html( le_field( 'svc_hero_scroll', $sv_id, __( 'Scroll to explore', 'lewisedward' ) ) ); ?></span><span class="svc-d-hero__scroll-bar" aria-hidden="true"><span class="svc-d-hero__scroll-fill"></span></span></span>
					</div>

				</div>
			</div>
		</section>

		<?php /* ================= WHAT'S INCLUDED ================= */ ?>
		<?php if ( ! empty( $sv_feats ) ) : ?>
			<section class="section section--svc-d-features" aria-label="<?php esc_attr_e( 'What is included', 'lewisedward' ); ?>">
				<div class="section__inner">
					<div class="svc-d-block glass" data-reveal>
						<div class="svc-d-split">
							<div class="svc-d-split__head">
								<div class="svc-d-eyebrow-row">
									<span class="eyebrow"><?php echo esc_html( le_field( 'svc_included_eyebrow', $sv_id, __( "What's included", 'lewisedward' ) ) ); ?></span>
									<span class="eyebrow text-primary"><?php echo esc_html( str_pad( (string) count( $sv_feats ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								</div>
								<h2 class="svc-d-title"><?php echo $whats_included_title; ?></h2>
							</div>
							<div class="svc-d-caps">
								<?php foreach ( $sv_feats as $i => $row ) : $t = isset( $row['title'] ) ? trim( $row['title'] ) : ''; if ( '' === $t ) { continue; } ?>
									<div class="svc-d-cap">
										<span class="svc-d-cap__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
										<div class="svc-d-cap__body">
											<h3 class="svc-d-cap__title"><?php echo esc_html( $t ); ?></h3>
											<?php if ( ! empty( $row['description'] ) ) : ?><p class="svc-d-cap__desc"><?php echo esc_html( $row['description'] ); ?></p><?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php /* ================= PROCESS ================= */ ?>
		<?php if ( ! empty( $sv_proc ) ) : ?>
			<section class="section section--svc-d-process" aria-label="<?php esc_attr_e( 'Our approach', 'lewisedward' ); ?>">
				<div class="section__inner">
					<div class="svc-d-block glass" data-reveal>
						<div class="svc-d-bar">
							<div>
								<div class="svc-d-eyebrow-row">
									<span class="eyebrow"><?php echo esc_html( le_field( 'svc_process_eyebrow', $sv_id, __( 'Our approach', 'lewisedward' ) ) ); ?></span>
									<span class="eyebrow text-primary"><?php echo esc_html( str_pad( (string) count( $sv_proc ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								</div>
								<h2 class="svc-d-title"><?php echo esc_html( le_field( 'svc_process_title', $sv_id, __( 'How we deliver.', 'lewisedward' ) ) ); ?></h2>
							</div>
							<span class="eyebrow eyebrow--faint svc-d-bar__note"><?php echo esc_html( le_field( 'svc_process_note', $sv_id, __( 'Step by step', 'lewisedward' ) ) ); ?></span>
						</div>
						<div class="svc-d-steps">
							<?php foreach ( $sv_proc as $i => $row ) : $st = isset( $row['title'] ) ? trim( $row['title'] ) : ''; if ( '' === $st ) { continue; } ?>
								<div class="svc-d-step">
									<span class="svc-d-step__num"><?php echo esc_html( ! empty( $row['step'] ) ? $row['step'] : str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
									<h3 class="svc-d-step__title"><?php echo esc_html( $st ); ?></h3>
									<?php if ( ! empty( $row['description'] ) ) : ?><p class="svc-d-step__desc"><?php echo esc_html( $row['description'] ); ?></p><?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php /* ================= BENEFITS ================= */ ?>
		<?php if ( ! empty( $sv_bens ) ) : ?>
			<section class="section section--svc-d-benefits" aria-label="<?php esc_attr_e( 'Why work with us', 'lewisedward' ); ?>">
				<div class="section__inner">
					<div class="svc-d-block glass" data-reveal>
						<div class="svc-d-split svc-d-split--wide">
							<div class="svc-d-split__head">
								<div class="svc-d-eyebrow-row">
									<span class="eyebrow"><?php echo esc_html( le_field( 'svc_benefits_eyebrow', $sv_id, __( 'Why work with us', 'lewisedward' ) ) ); ?></span>
									<span class="pulse-dot" aria-hidden="true"></span>
								</div>
								<h2 class="svc-d-title"><?php echo esc_html( le_field( 'svc_benefits_title', $sv_id, __( 'The benefits of partnering with us.', 'lewisedward' ) ) ); ?></h2>
								<p class="svc-d-split__intro"><?php echo esc_html( le_field( 'svc_benefits_intro', $sv_id, __( 'We pair deep WordPress expertise with a transparent, collaborative approach — so every project lands with clarity and confidence.', 'lewisedward' ) ) ); ?></p>
							</div>
							<div class="svc-d-benefits">
								<?php foreach ( $sv_bens as $row ) : $b = isset( $row['benefit'] ) ? trim( $row['benefit'] ) : ''; if ( '' === $b ) { continue; } ?>
									<div class="svc-d-benefit">
										<span class="svc-d-benefit__dot" aria-hidden="true"></span>
										<span class="svc-d-benefit__text"><?php echo esc_html( $b ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php /* ================= FAQ ================= */ ?>
		<?php if ( ! empty( $sv_faq ) ) : ?>
			<section class="section section--svc-d-faq" aria-label="<?php esc_attr_e( 'Frequently asked questions', 'lewisedward' ); ?>">
				<div class="section__inner">
					<div class="svc-d-block glass" data-reveal>
						<div class="svc-d-split">
							<div class="svc-d-split__head">
								<div class="svc-d-eyebrow-row">
									<span class="eyebrow"><?php echo esc_html( le_field( 'svc_faq_eyebrow', $sv_id, __( 'Common questions', 'lewisedward' ) ) ); ?></span>
									<span class="eyebrow text-primary"><?php echo esc_html( str_pad( (string) count( $sv_faq ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								</div>
								<h2 class="svc-d-title"><?php echo esc_html( le_field( 'svc_faq_title', $sv_id, __( 'Frequently asked.', 'lewisedward' ) ) ); ?></h2>
								<p class="svc-d-split__intro"><?php echo esc_html( le_field( 'svc_faq_intro', $sv_id, __( 'Quick answers to the things we get asked most. Still need clarity? Drop us a line.', 'lewisedward' ) ) ); ?></p>
							</div>
							<div class="svc-d-faq">
								<?php foreach ( $sv_faq as $i => $row ) : $q = isset( $row['question'] ) ? trim( $row['question'] ) : ''; if ( '' === $q ) { continue; } ?>
									<details class="svc-d-faq__item">
										<summary class="svc-d-faq__q">
											<span class="svc-d-faq__q-inner">
												<span class="svc-d-faq__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
												<span><?php echo esc_html( $q ); ?></span>
											</span>
											<span class="svc-d-faq__chevron" aria-hidden="true">
												<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
											</span>
										</summary>
										<?php if ( ! empty( $row['answer'] ) ) : ?><div class="svc-d-faq__a"><p><?php echo esc_html( $row['answer'] ); ?></p></div><?php endif; ?>
									</details>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php /* ================= FEATURED WORK ================= */ ?>
		<?php
		$sv_relwk = array_values( array_filter( $sv_relwk, function ( $w ) {
			$wp = is_object( $w ) ? $w : get_post( (int) $w );
			return $wp && has_post_thumbnail( $wp->ID );
		} ) );
		if ( ! empty( $sv_relwk ) ) :
			?>
			<section class="section section--svc-work" aria-label="<?php esc_attr_e( 'Featured work', 'lewisedward' ); ?>">
				<div class="section__inner">
					<div class="svc-d-block glass" data-reveal data-svc-work>
						<div class="svc-work__head">
							<div>
								<span class="eyebrow eyebrow--faint"><?php echo esc_html( le_field( 'svc_work_eyebrow', $sv_id, __( 'Case studies', 'lewisedward' ) ) ); ?><sup class="svc-work__count"><?php echo esc_html( (string) count( $sv_relwk ) ); ?></sup></span>
								<h2 class="svc-d-title svc-work__title"><?php echo esc_html( le_field( 'svc_work_title', $sv_id, __( 'Featured work', 'lewisedward' ) ) ); ?></h2>
							</div>
							<div class="svc-work__nav">
								<button class="svc-work__arrow" type="button" data-svc-work-prev aria-label="<?php esc_attr_e( 'Previous project', 'lewisedward' ); ?>">
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
								</button>
								<button class="svc-work__arrow" type="button" data-svc-work-next aria-label="<?php esc_attr_e( 'Next project', 'lewisedward' ); ?>">
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
								</button>
							</div>
						</div>

						<div class="svc-work__viewall">
							<?php $svc_work_viewall = le_link( 'svc_work_viewall', '/work', __( 'View all', 'lewisedward' ), $sv_id ); ?>
							<a class="svc-work__viewall-link" href="<?php echo esc_url( $svc_work_viewall['url'] ); ?>"<?php le_link_target_attr( $svc_work_viewall ); ?>>
								<span class="svc-work__viewall-badge" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<span class="eyebrow"><?php echo esc_html( $svc_work_viewall['title'] ); ?></span>
							</a>
						</div>

						<div class="svc-work__track" data-svc-work-track>
							<?php
							foreach ( $sv_relwk as $w ) :
								$wp  = is_object( $w ) ? $w : get_post( (int) $w );
								$wid = $wp->ID;
								?>
								<a class="svc-work__card" href="<?php echo esc_url( get_permalink( $wid ) ); ?>" data-cursor="View">
									<div class="svc-work__media">
										<?php echo get_the_post_thumbnail( $wid, 'le_card_wide', array( 'alt' => esc_attr( get_the_title( $wid ) ), 'loading' => 'lazy', 'decoding' => 'async', 'draggable' => 'false' ) ); ?>
										<div class="svc-work__overlay">
											<div class="svc-work__panel glass">
												<h3 class="svc-work__name"><?php echo esc_html( get_the_title( $wid ) ); ?></h3>
												<?php $wd = le_field( 'work_card_description', $wid ); ?>
												<?php if ( $wd ) : ?><p class="svc-work__desc"><?php echo esc_html( $wd ); ?></p><?php endif; ?>
												<span class="svc-work__discover">
													<span class="svc-work__discover-badge" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
													<span class="eyebrow"><?php echo esc_html( le_field( 'svc_work_discover', $sv_id, __( 'Discover', 'lewisedward' ) ) ); ?></span>
												</span>
											</div>
										</div>
									</div>
								</a>
							<?php endforeach; ?>
						</div>

						<div class="svc-work__progress"><span class="svc-work__progress-fill" data-svc-work-progress></span></div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php /* ================= RELATED SERVICES ================= */ ?>
		<?php if ( ! empty( $sv_rel ) ) : ?>
			<section class="section section--svc-d-related" aria-label="<?php esc_attr_e( 'Related services', 'lewisedward' ); ?>">
				<div class="section__inner">
					<div class="svc-d-block glass" data-reveal>
						<div class="svc-d-bar">
							<div>
								<div class="svc-d-eyebrow-row">
									<span class="eyebrow"><?php echo esc_html( le_field( 'svc_related_eyebrow', $sv_id, __( 'Related services', 'lewisedward' ) ) ); ?></span>
									<span class="pulse-dot" aria-hidden="true"></span>
								</div>
								<h2 class="svc-d-title"><?php echo esc_html( le_field( 'svc_related_title', $sv_id, __( 'You might also need.', 'lewisedward' ) ) ); ?></h2>
							</div>
							<?php $svc_related_all = le_link( 'svc_related_all', '/services', __( 'All services', 'lewisedward' ), $sv_id ); ?>
								<a class="eyebrow svc-d-related__all" href="<?php echo esc_url( $svc_related_all['url'] ); ?>"<?php le_link_target_attr( $svc_related_all ); ?>><?php echo esc_html( $svc_related_all['title'] ); ?> &rarr;</a>
						</div>
						<div class="svc-d-related__grid">
							<?php
							foreach ( $sv_rel as $r ) :
								$rp = is_object( $r ) ? $r : get_post( (int) $r );
								if ( ! $rp ) { continue; }
								$rid = $rp->ID;
								?>
								<a class="svc-d-rel" href="<?php echo esc_url( get_permalink( $rid ) ); ?>" data-cursor="View">
									<div class="svc-d-rel__top">
										<?php $rs = le_field( 'service_subtitle', $rid ); ?>
										<?php if ( $rs ) : ?><span class="eyebrow svc-d-rel__sub"><?php echo esc_html( $rs ); ?></span><?php endif; ?>
										<h3 class="svc-d-rel__title"><?php echo esc_html( get_the_title( $rid ) ); ?></h3>
									</div>
									<div class="svc-d-rel__foot">
										<span class="eyebrow eyebrow--faint"><?php echo esc_html( le_field( 'svc_related_explore', $sv_id, __( 'Explore', 'lewisedward' ) ) ); ?></span>
										<span class="svc-d-rel__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									</div>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/home/testimonials' ); ?>
		<?php get_template_part( 'template-parts/home/contact-cta' ); ?>

	</main>

	<?php
	if ( ! empty( $sv_relwk ) ) {
		wp_enqueue_script( 'le-service-work' );
	}
endwhile;

wp_enqueue_script( 'le-hero' );
get_footer();
