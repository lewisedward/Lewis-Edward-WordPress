<?php
/**
 * Single Journal entry (built-in Posts, relabelled "Journal") — /journal/{slug}.
 *
 * Ported from the React JournalDetailPage. The article body is the standard
 * editor (the_content); meta (category, date, read time) come from native
 * fields and the auto reading-time helper. Sections: hero, article body,
 * next/prev, then the shared Contact CTA.
 *
 * SEO: the entry title is the single <h1>; body headings from the editor are
 * <h2>/<h3>; section labels are <h2>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$jr_id   = get_the_ID();
	$jr_cats = get_the_category( $jr_id );
	$jr_cat  = ( ! empty( $jr_cats ) && $jr_cats[0] instanceof WP_Term ) ? $jr_cats[0]->name : '';
	$jr_date = get_the_date( 'M Y' );
	$jr_read = le_reading_time( $jr_id ) . ' ' . __( 'min read', 'lewisedward' );

	// Build up to two "continue reading" entries: previous + next, else recents.
	$jr_more = array();
	$prev    = get_previous_post();
	$next    = get_next_post();
	foreach ( array( $prev, $next ) as $adj ) {
		if ( $adj instanceof WP_Post ) {
			$jr_more[ $adj->ID ] = $adj;
		}
	}
	if ( count( $jr_more ) < 2 ) {
		$fill = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'post__not_in'   => array_merge( array( $jr_id ), array_keys( $jr_more ) ),
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		) );
		foreach ( $fill as $f ) {
			if ( count( $jr_more ) >= 2 ) { break; }
			$jr_more[ $f->ID ] = $f;
		}
	}
	$jr_more = array_slice( $jr_more, 0, 2, true );
	?>

	<main id="main" class="site-main site-main--journal-single">

		<?php /* ================= HERO ================= */ ?>
		<section class="section section--jr-hero" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
			<div class="section__inner">
				<div class="jr-hero glass" data-reveal>
					<div class="jr-hero__eyebrow-row">
						<a class="eyebrow jr-hero__back" href="<?php echo esc_url( home_url( '/journal' ) ); ?>"><?php esc_html_e( 'Journal', 'lewisedward' ); ?></a>
						<span class="jr-hero__rule" aria-hidden="true"></span>
						<?php if ( $jr_cat ) : ?><span class="eyebrow jr-hero__cat"><?php echo esc_html( $jr_cat ); ?></span><?php endif; ?>
						<span class="pulse-dot" aria-hidden="true"></span>
					</div>

					<h1 class="jr-hero__title"><?php the_title(); ?></h1>

					<div class="jr-hero__meta">
						<span class="jr-hero__date"><?php echo esc_html( $jr_date ); ?></span>
						<span class="jr-hero__sep" aria-hidden="true"></span>
						<span class="jr-hero__read"><?php echo esc_html( $jr_read ); ?></span>
					</div>

					<?php if ( has_post_thumbnail() ) : ?>
						<div class="jr-hero__media">
							<?php the_post_thumbnail( 'le_hero', array( 'alt' => esc_attr( get_the_title() ), 'loading' => 'eager', 'decoding' => 'async', 'fetchpriority' => 'high' ) ); ?>
							<span class="jr-hero__grad" aria-hidden="true"></span>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<?php /* ================= ARTICLE BODY ================= */ ?>
		<section class="section section--jr-body" aria-label="<?php esc_attr_e( 'Article', 'lewisedward' ); ?>">
			<div class="section__inner">
				<div class="jr-body glass" data-reveal>
					<div class="jr-body__inner">
						<div class="jr-body__head">
							<span class="eyebrow"><?php esc_html_e( 'Read', 'lewisedward' ); ?><sup class="jr-body__count">01</sup></span>
							<span class="jr-body__rule" aria-hidden="true"></span>
							<span class="pulse-dot" aria-hidden="true"></span>
						</div>

						<?php if ( has_excerpt() ) : ?>
							<p class="jr-body__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>

						<div class="jr-body__prose">
							<?php the_content(); ?>
						</div>
					</div>
				</div>
			</div>
		</section>

		<?php /* ================= CONTINUE ================= */ ?>
		<?php if ( ! empty( $jr_more ) ) : ?>
			<section class="section section--jr-next" aria-label="<?php esc_attr_e( 'More from the journal', 'lewisedward' ); ?>">
				<div class="section__inner">
					<div class="jr-next glass" data-reveal>
						<div class="jr-next__head">
							<span class="eyebrow"><?php esc_html_e( 'Continue', 'lewisedward' ); ?><sup class="jr-next__count">02</sup></span>
							<span class="jr-next__rule" aria-hidden="true"></span>
							<span class="eyebrow jr-next__note"><?php esc_html_e( 'More from the journal', 'lewisedward' ); ?></span>
							<span class="pulse-dot" aria-hidden="true"></span>
						</div>

						<h2 class="jr-next__title"><?php esc_html_e( 'Keep', 'lewisedward' ); ?> <span class="about-muted"><?php esc_html_e( 'reading', 'lewisedward' ); ?></span> <span class="text-primary"><?php esc_html_e( 'the journal.', 'lewisedward' ); ?></span></h2>

						<div class="jr-next__grid">
							<?php foreach ( $jr_more as $m ) : $mid = $m->ID; ?>
								<a class="jr-next__card" href="<?php echo esc_url( get_permalink( $mid ) ); ?>" data-cursor="Read">
									<?php if ( has_post_thumbnail( $mid ) ) : ?>
										<div class="jr-next__media">
											<?php echo get_the_post_thumbnail( $mid, 'le_card_wide', array( 'alt' => esc_attr( get_the_title( $mid ) ), 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
											<span class="jr-next__card-grad" aria-hidden="true"></span>
										</div>
									<?php endif; ?>
									<div class="jr-next__body">
										<div class="jr-next__text">
											<?php $mcats = get_the_category( $mid ); $mcat = ( ! empty( $mcats ) ) ? $mcats[0]->name : ''; ?>
											<?php if ( $mcat ) : ?><span class="eyebrow jr-next__card-cat"><?php echo esc_html( $mcat ); ?></span><?php endif; ?>
											<h3 class="jr-next__card-title"><?php echo esc_html( get_the_title( $mid ) ); ?></h3>
										</div>
										<span class="jr-next__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									</div>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/home/contact-cta' ); ?>

	</main>

	<?php
endwhile;

get_footer();
