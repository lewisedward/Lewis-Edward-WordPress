<?php
/**
 * Template Name: Work
 *
 * The Work listing page — fully ACF-driven (field group "Work Page"). No
 * fallback content. Sections: hero (featured project tile + dot-sphere), a
 * filterable archive grid (Relationship → Work; filter pills built from each
 * project's Work Categories), then the shared Contact CTA.
 *
 * SEO: hero carries the single <h1>; the featured tile title and section
 * heading are <h2>; grid card titles are <h3>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$wp_featured = le_field( 'workp_featured' );
$wp_fid      = is_object( $wp_featured ) ? (int) $wp_featured->ID : (int) $wp_featured;

$wp_items = le_field( 'work_items' );
$wp_items = is_array( $wp_items ) ? $wp_items : array();
$wp_count = count( $wp_items );

/** Normalise a relationship entry to an ID. */
$wp_id = function ( $item ) {
	return is_object( $item ) ? (int) $item->ID : (int) $item;
};

/**
 * Get a project's real Work Category terms.
 *
 * Uses wp_get_post_terms() and hard-validates each result is a WP_Term, so any
 * non-term objects injected by hook-profiling/debug plugins are ignored.
 *
 * @param int $id Post ID.
 * @return WP_Term[]
 */
function le_work_terms( $id ) {
	$terms = wp_get_post_terms( (int) $id, 'work_category' );
	if ( ! is_array( $terms ) ) {
		return array();
	}
	return array_filter( $terms, 'le_is_real_work_term' );
}

/**
 * True only for a genuine work_category term (rejects the fake objects some
 * hook-profiling/debug plugins inject, which always have a numeric name).
 *
 * @param mixed $t Candidate term.
 * @return bool
 */
function le_is_real_work_term( $t ) {
	return ( $t instanceof WP_Term )
		&& 'work_category' === $t->taxonomy
		&& (int) $t->term_id > 0
		&& '' !== trim( (string) $t->name )
		&& ! is_numeric( trim( (string) $t->name ) );
}

/** Render a project's Work Categories as pills. */
$wp_cats = function ( $id, $class = 'work-tag' ) {
	foreach ( le_work_terms( $id ) as $t ) {
		echo '<span class="' . esc_attr( $class ) . '">' . esc_html( $t->name ) . '</span>';
	}
};

// Build the filter set directly from the taxonomy (only real WP_Term objects).
$wp_filters = array();
$wp_all_terms = get_terms( array(
	'taxonomy'   => 'work_category',
	'hide_empty' => false,
	'orderby'    => 'name',
	'order'      => 'ASC',
) );
if ( is_array( $wp_all_terms ) ) {
	foreach ( $wp_all_terms as $t ) {
		if ( le_is_real_work_term( $t ) ) {
			$wp_filters[ $t->slug ] = $t->name;
		}
	}
}
?>

<main id="main" class="site-main site-main--work">

	<?php /* ================= HERO ================= */ ?>
	<section class="section section--work-hero" aria-label="<?php esc_attr_e( 'Selected work', 'lewisedward' ); ?>">
		<div class="section__inner">
			<div class="work-hero glass" data-reveal>
				<div class="work-hero__sphere" aria-hidden="true" data-hero-sphere>
					<canvas class="hero-sphere__canvas"></canvas>
				</div>

				<div class="work-hero__eyebrow-row">
					<span class="eyebrow"><?php echo esc_html( le_field( 'workp_eyebrow' ) ); ?><?php if ( $wp_count ) : ?><sup class="work-hero__sup"><?php echo esc_html( str_pad( (string) $wp_count, 2, '0', STR_PAD_LEFT ) ); ?></sup><?php endif; ?></span>
					<span class="work-hero__rule" aria-hidden="true"></span>
					<span class="eyebrow work-hero__right"><?php echo esc_html( le_field( 'workp_right_label' ) ); ?></span>
					<span class="pulse-dot" aria-hidden="true"></span>
				</div>

				<h1 class="work-hero__title">
					<span class="work-hero__title-1"><?php echo esc_html( le_field( 'workp_h1_1' ) ); ?></span>
					<span class="about-muted"><?php echo esc_html( le_field( 'workp_h1_2' ) ); ?></span>
					<span class="text-primary"><?php echo esc_html( le_field( 'workp_h1_3' ) ); ?></span>
				</h1>

				<p class="work-hero__lead"><?php echo esc_html( le_field( 'workp_lead' ) ); ?></p>

				<?php if ( $wp_fid && has_post_thumbnail( $wp_fid ) ) : ?>
					<a class="work-featured" href="<?php echo esc_url( get_permalink( $wp_fid ) ); ?>" data-cursor="View">
						<div class="work-featured__media">
							<?php echo get_the_post_thumbnail( $wp_fid, 'le_hero', array( 'alt' => esc_attr( get_the_title( $wp_fid ) ), 'loading' => 'eager', 'decoding' => 'async' ) ); ?>
							<span class="work-featured__grad" aria-hidden="true"></span>
							<span class="work-featured__label">
								<span class="work-featured__dot" aria-hidden="true"></span>
								<span class="eyebrow"><?php echo esc_html( le_field( 'workp_featured_label' ) ); ?></span>
							</span>
							<div class="work-featured__foot">
								<div class="work-featured__text">
									<h2 class="work-featured__title"><?php echo esc_html( get_the_title( $wp_fid ) ); ?></h2>
									<p class="work-featured__desc"><?php echo esc_html( le_field( 'work_card_description', $wp_fid ) ); ?></p>
								</div>
								<div class="work-featured__meta">
									<div class="work-featured__tags"><?php $wp_cats( $wp_fid, 'work-tag work-tag--glass' ); ?></div>
									<span class="work-featured__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								</div>
							</div>
						</div>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php /* ================= ARCHIVE ================= */ ?>
	<?php if ( $wp_count ) : ?>
		<section class="section section--work-archive" aria-label="<?php esc_attr_e( 'Project archive', 'lewisedward' ); ?>">
			<div class="section__inner">
				<div class="work-archive glass" data-reveal>
					<div class="work-archive__head">
						<span class="eyebrow"><?php echo esc_html( le_field( 'workp_archive_eyebrow' ) ); ?><sup class="work-archive__count" data-work-count><?php echo esc_html( str_pad( (string) $wp_count, 2, '0', STR_PAD_LEFT ) ); ?></sup></span>
						<span class="work-archive__rule" aria-hidden="true"></span>
						<span class="pulse-dot" aria-hidden="true"></span>
					</div>

					<h2 class="work-archive__title"><?php echo esc_html( le_field( 'workp_archive_h1' ) ); ?> <span class="text-primary"><?php echo esc_html( le_field( 'workp_archive_accent' ) ); ?></span> <?php echo esc_html( le_field( 'workp_archive_h3' ) ); ?></h2>

					<?php if ( ! empty( $wp_filters ) ) : ?>
						<div class="work-filters" data-work-filters>
							<button class="work-filter is-active" type="button" data-filter="all"><?php esc_html_e( 'All', 'lewisedward' ); ?></button>
							<?php foreach ( $wp_filters as $slug => $name ) : ?>
								<button class="work-filter" type="button" data-filter="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></button>
							<?php endforeach; ?>
							<span class="eyebrow work-filters__count"><span data-work-visible><?php echo esc_html( str_pad( (string) $wp_count, 2, '0', STR_PAD_LEFT ) ); ?></span> <?php esc_html_e( 'projects', 'lewisedward' ); ?></span>
						</div>
					<?php endif; ?>

					<div class="work-grid" data-work-grid>
						<?php
						foreach ( $wp_items as $item ) :
							$id   = $wp_id( $item );
							$cats = implode( ' ', wp_list_pluck( le_work_terms( $id ), 'slug' ) );
							?>
							<a class="work-item" href="<?php echo esc_url( get_permalink( $id ) ); ?>" data-cursor="View" data-categories="<?php echo esc_attr( $cats ); ?>">
								<?php if ( has_post_thumbnail( $id ) ) : ?>
									<div class="work-item__media">
										<?php echo get_the_post_thumbnail( $id, 'le_hero', array( 'alt' => esc_attr( get_the_title( $id ) ), 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
										<span class="work-item__grad" aria-hidden="true"></span>
									</div>
								<?php endif; ?>
								<div class="work-item__body">
									<div class="work-item__text">
										<h3 class="work-item__title"><?php echo esc_html( get_the_title( $id ) ); ?></h3>
										<p class="work-item__desc"><?php echo esc_html( le_field( 'work_card_description', $id ) ); ?></p>
										<div class="work-item__tags"><?php $wp_cats( $id ); ?></div>
									</div>
									<span class="work-item__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
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
wp_enqueue_script( 'le-hero' );
if ( ! empty( $wp_filters ) ) {
	wp_enqueue_script( 'le-work-filter' );
}
get_footer();
