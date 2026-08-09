<?php
/**
 * Template Name: Journal
 *
 * The Journal listing page — hero (featured entry tile) + editorial archive with
 * a category filter. Powered by the built-in Posts type (relabelled "Journal").
 * Hero/archive copy is ACF-driven (field group "Journal Page"); the entry list
 * is a live query of published posts. Then the shared Contact CTA.
 *
 * SEO: hero carries the single <h1>; featured tile title and archive heading are
 * <h2>; list entry titles are <h3>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// NOTE: The Journal listing is normally rendered by home.php (the WordPress
// "Posts page"). This page template is kept only as a fallback; le_is_real_category()
// and le_post_cats() live in inc/journal.php so they are shared, not redeclared.

// Featured entry: ACF override, else the most recent post.
$jr_featured = le_field( 'journalp_featured' );
$jr_fid      = is_object( $jr_featured ) ? (int) $jr_featured->ID : (int) $jr_featured;
if ( ! $jr_fid ) {
	$jr_latest = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 1, 'no_found_rows' => true ) );
	$jr_fid    = ! empty( $jr_latest ) ? (int) $jr_latest[0]->ID : 0;
}

// Archive entries: every published post except the featured one, newest first.
$jr_entries = get_posts( array(
	'post_type'      => 'post',
	'posts_per_page' => -1,
	'post__not_in'   => $jr_fid ? array( $jr_fid ) : array(),
	'orderby'        => 'date',
	'order'          => 'DESC',
	'no_found_rows'  => true,
) );
$jr_count = count( $jr_entries );

// Filter pills from the categories actually used by the listed entries.
$jr_filters = array();
foreach ( $jr_entries as $e ) {
	foreach ( le_post_cats( $e->ID ) as $t ) {
		$jr_filters[ $t->slug ] = $t->name;
	}
}
asort( $jr_filters );
?>

<main id="main" class="site-main site-main--journal">

	<?php /* ================= HERO / FEATURED ================= */ ?>
	<section class="section section--jr-lead" aria-label="<?php esc_attr_e( 'Journal', 'lewisedward' ); ?>">
		<div class="section__inner">
			<div class="jr-lead glass" data-reveal>
				<div class="jr-lead__sphere" aria-hidden="true" data-hero-sphere>
					<canvas class="hero-sphere__canvas"></canvas>
				</div>

				<div class="jr-lead__eyebrow-row">
					<span class="eyebrow"><?php echo esc_html( le_field( 'journalp_eyebrow' ) ); ?><?php if ( $jr_count ) : ?><sup class="jr-lead__sup"><?php echo esc_html( str_pad( (string) ( $jr_count + ( $jr_fid ? 1 : 0 ) ), 2, '0', STR_PAD_LEFT ) ); ?></sup><?php endif; ?></span>
					<span class="jr-lead__rule" aria-hidden="true"></span>
					<span class="eyebrow jr-lead__right"><?php echo esc_html( le_field( 'journalp_right_label' ) ); ?></span>
					<span class="pulse-dot" aria-hidden="true"></span>
				</div>

				<h1 class="jr-lead__title">
					<span class="jr-lead__title-1"><?php echo esc_html( le_field( 'journalp_h1_1' ) ); ?></span>
					<span class="about-muted"><?php echo esc_html( le_field( 'journalp_h1_2' ) ); ?></span>
					<span class="text-primary"><?php echo esc_html( le_field( 'journalp_h1_3' ) ); ?></span>
				</h1>

				<p class="jr-lead__lead"><?php echo esc_html( le_field( 'journalp_lead' ) ); ?></p>

				<?php if ( $jr_fid && has_post_thumbnail( $jr_fid ) ) : ?>
					<a class="jr-featured" href="<?php echo esc_url( get_permalink( $jr_fid ) ); ?>" data-cursor="Read">
						<div class="jr-featured__media">
							<?php echo get_the_post_thumbnail( $jr_fid, 'le_hero', array( 'alt' => esc_attr( get_the_title( $jr_fid ) ), 'loading' => 'eager', 'decoding' => 'async' ) ); ?>
							<span class="jr-featured__grad" aria-hidden="true"></span>
							<span class="jr-featured__label">
								<span class="jr-featured__dot" aria-hidden="true"></span>
								<span class="eyebrow"><?php echo esc_html( le_field( 'journalp_featured_label' ) ); ?></span>
							</span>
							<div class="jr-featured__foot">
								<div class="jr-featured__text">
									<div class="jr-featured__meta">
										<?php $fcats = le_post_cats( $jr_fid ); if ( ! empty( $fcats ) ) : ?><span><?php echo esc_html( reset( $fcats )->name ); ?></span><span class="jr-dot" aria-hidden="true"></span><?php endif; ?>
										<span><?php echo esc_html( get_the_date( 'M Y', $jr_fid ) ); ?></span>
										<span class="jr-dot" aria-hidden="true"></span>
										<span><?php echo esc_html( le_reading_time( $jr_fid ) . ' ' . __( 'min read', 'lewisedward' ) ); ?></span>
									</div>
									<h2 class="jr-featured__title"><?php echo esc_html( get_the_title( $jr_fid ) ); ?></h2>
									<p class="jr-featured__excerpt"><?php echo esc_html( get_the_excerpt( $jr_fid ) ); ?></p>
								</div>
								<span class="jr-featured__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</div>
						</div>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php /* ================= ARCHIVE ================= */ ?>
	<?php if ( $jr_count ) : ?>
		<section class="section section--jr-archive" aria-label="<?php esc_attr_e( 'Journal archive', 'lewisedward' ); ?>">
			<div class="section__inner">
				<div class="jr-archive glass" data-reveal>
					<div class="jr-archive__head">
						<span class="eyebrow"><?php echo esc_html( le_field( 'journalp_archive_eyebrow' ) ); ?><sup class="jr-archive__count" data-jr-count><?php echo esc_html( str_pad( (string) $jr_count, 2, '0', STR_PAD_LEFT ) ); ?></sup></span>
						<span class="jr-archive__rule" aria-hidden="true"></span>
						<span class="pulse-dot" aria-hidden="true"></span>
					</div>

					<h2 class="jr-archive__title"><?php echo esc_html( le_field( 'journalp_archive_h1' ) ); ?> <span class="about-muted"><?php echo esc_html( le_field( 'journalp_archive_accent' ) ); ?></span> <span class="text-primary"><?php echo esc_html( le_field( 'journalp_archive_h3' ) ); ?></span></h2>

					<div class="jr-filters" data-jr-filters>
						<button class="jr-filter is-active" type="button" data-filter="all"><?php echo esc_html( le_field( 'journalp_all_label' ) ); ?></button>
						<?php foreach ( $jr_filters as $slug => $name ) : ?>
							<button class="jr-filter" type="button" data-filter="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></button>
						<?php endforeach; ?>
						<span class="eyebrow jr-filters__count"><span data-jr-visible><?php echo esc_html( str_pad( (string) $jr_count, 2, '0', STR_PAD_LEFT ) ); ?></span> <?php esc_html_e( 'entries', 'lewisedward' ); ?></span>
					</div>

					<ul class="jr-list" data-jr-list>
						<?php
						foreach ( $jr_entries as $i => $e ) :
							$eid   = $e->ID;
							$ecats = le_post_cats( $eid );
							$ecat  = ! empty( $ecats ) ? reset( $ecats )->name : '';
							$slugs = implode( ' ', wp_list_pluck( $ecats, 'slug' ) );
							?>
							<li class="jr-item" data-categories="<?php echo esc_attr( $slugs ); ?>">
								<a class="jr-item__link" href="<?php echo esc_url( get_permalink( $eid ) ); ?>" data-cursor="Read">
									<span class="jr-item__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
									<span class="jr-item__main">
										<h3 class="jr-item__title"><?php echo esc_html( get_the_title( $eid ) ); ?></h3>
										<span class="jr-item__meta-m">
											<?php if ( $ecat ) : ?><span><?php echo esc_html( $ecat ); ?></span><span class="jr-dot" aria-hidden="true"></span><?php endif; ?>
											<span><?php echo esc_html( get_the_date( 'M Y', $eid ) ); ?></span>
										</span>
										<p class="jr-item__excerpt"><?php echo esc_html( get_the_excerpt( $eid ) ); ?></p>
									</span>
									<?php if ( $ecat ) : ?><span class="eyebrow jr-item__cat"><?php echo esc_html( $ecat ); ?></span><?php endif; ?>
									<span class="eyebrow jr-item__date"><?php echo esc_html( get_the_date( 'M Y', $eid ) . ' · ' . le_reading_time( $eid ) . ' ' . __( 'min', 'lewisedward' ) ); ?></span>
									<span class="jr-item__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									<span class="jr-item__underline" aria-hidden="true"></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>

					<p class="jr-empty" data-jr-empty hidden><?php echo esc_html( le_field( 'journalp_empty' ) ); ?></p>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/home/contact-cta' ); ?>

</main>

<?php
wp_enqueue_script( 'le-hero' );
if ( ! empty( $jr_filters ) ) {
	wp_enqueue_script( 'le-journal-filter' );
}
get_footer();
