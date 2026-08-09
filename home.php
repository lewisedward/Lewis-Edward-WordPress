<?php
/**
 * Journal blog index (the WordPress "Posts page").
 *
 * With the Journal page set as Settings → Reading → Posts page, WordPress
 * renders /journal here using the paginated main query (better SEO than a
 * custom page query). Hero/archive copy is read from the Posts page via ACF
 * (field group "Journal Page"); the featured tile shows on page one only; the
 * category pills link to real category archives (archive.php).
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wp_query;
$pp    = le_journal_page_id();
$paged = max( 1, (int) get_query_var( 'paged' ) );
$per   = (int) get_option( 'posts_per_page' );
$base  = ( $paged - 1 ) * $per;
$total = (int) $wp_query->found_posts;

// Featured entry (first page only): ACF override, else the most recent post.
$fid = 0;
if ( 1 === $paged ) {
	$f   = $pp ? le_field( 'journalp_featured', $pp ) : '';
	$fid = is_object( $f ) ? (int) $f->ID : (int) $f;
	if ( ! $fid ) {
		$latest = get_posts( array( 'posts_per_page' => 1, 'no_found_rows' => true ) );
		$fid    = ! empty( $latest ) ? (int) $latest[0]->ID : 0;
	}
}
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
					<span class="eyebrow"><?php echo esc_html( le_field( 'journalp_eyebrow', $pp ) ); ?><?php if ( $total ) : ?><sup class="jr-lead__sup"><?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></sup><?php endif; ?></span>
					<span class="jr-lead__rule" aria-hidden="true"></span>
					<span class="eyebrow jr-lead__right"><?php echo esc_html( le_field( 'journalp_right_label', $pp ) ); ?></span>
					<span class="pulse-dot" aria-hidden="true"></span>
				</div>

				<h1 class="jr-lead__title">
					<span class="jr-lead__title-1"><?php echo esc_html( le_field( 'journalp_h1_1', $pp ) ); ?></span>
					<span class="about-muted"><?php echo esc_html( le_field( 'journalp_h1_2', $pp ) ); ?></span>
					<span class="text-primary"><?php echo esc_html( le_field( 'journalp_h1_3', $pp ) ); ?></span>
				</h1>

				<p class="jr-lead__lead"><?php echo esc_html( le_field( 'journalp_lead', $pp ) ); ?></p>

				<?php if ( $fid && has_post_thumbnail( $fid ) ) : ?>
					<a class="jr-featured" href="<?php echo esc_url( get_permalink( $fid ) ); ?>" data-cursor="Read">
						<div class="jr-featured__media">
							<?php echo get_the_post_thumbnail( $fid, 'le_hero', array( 'alt' => esc_attr( get_the_title( $fid ) ), 'loading' => 'eager', 'decoding' => 'async' ) ); ?>
							<span class="jr-featured__grad" aria-hidden="true"></span>
							<span class="jr-featured__label">
								<span class="jr-featured__dot" aria-hidden="true"></span>
								<span class="eyebrow"><?php echo esc_html( le_field( 'journalp_featured_label', $pp ) ); ?></span>
							</span>
							<div class="jr-featured__foot">
								<div class="jr-featured__text">
									<div class="jr-featured__meta">
										<?php $fcats = le_post_cats( $fid ); if ( ! empty( $fcats ) ) : ?><span><?php echo esc_html( reset( $fcats )->name ); ?></span><span class="jr-dot" aria-hidden="true"></span><?php endif; ?>
										<span><?php echo esc_html( get_the_date( 'M Y', $fid ) ); ?></span>
										<span class="jr-dot" aria-hidden="true"></span>
										<span><?php echo esc_html( le_reading_time( $fid ) . ' ' . __( 'min read', 'lewisedward' ) ); ?></span>
									</div>
									<h2 class="jr-featured__title"><?php echo esc_html( get_the_title( $fid ) ); ?></h2>
									<p class="jr-featured__excerpt"><?php echo esc_html( get_the_excerpt( $fid ) ); ?></p>
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
	<?php if ( have_posts() ) : ?>
		<section class="section section--jr-archive" aria-label="<?php esc_attr_e( 'Journal archive', 'lewisedward' ); ?>">
			<div class="section__inner">
				<div class="jr-archive glass" data-reveal>
					<div class="jr-archive__head">
						<span class="eyebrow"><?php echo esc_html( le_field( 'journalp_archive_eyebrow', $pp ) ); ?><sup class="jr-archive__count"><?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></sup></span>
						<span class="jr-archive__rule" aria-hidden="true"></span>
						<span class="pulse-dot" aria-hidden="true"></span>
					</div>

					<h2 class="jr-archive__title"><?php echo esc_html( le_field( 'journalp_archive_h1', $pp ) ); ?> <span class="about-muted"><?php echo esc_html( le_field( 'journalp_archive_accent', $pp ) ); ?></span> <span class="text-primary"><?php echo esc_html( le_field( 'journalp_archive_h3', $pp ) ); ?></span></h2>

					<?php le_journal_pills( '' ); ?>

					<ul class="jr-list">
						<?php
						$num = $base;
						while ( have_posts() ) :
							the_post();
							$num++;
							if ( $fid && get_the_ID() === $fid ) {
								continue; // Shown as the featured tile above.
							}
							le_journal_row( $num );
						endwhile;
						?>
					</ul>

					<?php le_journal_pagination(); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/home/contact-cta' ); ?>

</main>

<?php
wp_enqueue_script( 'le-hero' );
get_footer();
