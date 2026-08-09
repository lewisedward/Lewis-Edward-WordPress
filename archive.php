<?php
/**
 * Journal archives (category, tag, date, author) — same editorial design as the
 * blog index, filtered to the queried term. Category pills mark the active one.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wp_query;
$pp     = le_journal_page_id();
$paged  = max( 1, (int) get_query_var( 'paged' ) );
$per    = (int) get_option( 'posts_per_page' );
$base   = ( $paged - 1 ) * $per;
$total  = (int) $wp_query->found_posts;

$queried      = get_queried_object();
$active_slug  = ( $queried instanceof WP_Term && 'category' === $queried->taxonomy ) ? $queried->slug : '';
$archive_name = single_term_title( '', false );
if ( ! $archive_name ) {
	$archive_name = get_the_archive_title();
}
?>

<main id="main" class="site-main site-main--journal">

	<?php /* ================= ARCHIVE HEAD ================= */ ?>
	<section class="section section--jr-archive" aria-label="<?php echo esc_attr( $archive_name ); ?>">
		<div class="section__inner">
			<div class="jr-archive glass jr-archive--standalone" data-reveal>
				<div class="jr-archive__head">
					<a class="eyebrow jr-archive__back" href="<?php echo esc_url( $pp ? get_permalink( $pp ) : home_url( '/journal' ) ); ?>"><?php esc_html_e( 'Journal', 'lewisedward' ); ?></a>
					<span class="jr-archive__rule" aria-hidden="true"></span>
					<span class="eyebrow"><?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?> <?php esc_html_e( 'entries', 'lewisedward' ); ?></span>
					<span class="pulse-dot" aria-hidden="true"></span>
				</div>

				<h1 class="jr-archive__title"><?php echo esc_html( le_field( 'journalp_archive_h1', $pp ) ); ?> <span class="about-muted"><?php echo esc_html( le_field( 'journalp_archive_accent', $pp ) ); ?></span> <span class="text-primary"><?php echo esc_html( $archive_name ); ?>.</span></h1>

				<?php le_journal_pills( $active_slug ); ?>

				<?php if ( have_posts() ) : ?>
					<ul class="jr-list">
						<?php
						$num = $base;
						while ( have_posts() ) :
							the_post();
							$num++;
							le_journal_row( $num );
						endwhile;
						?>
					</ul>
					<?php le_journal_pagination(); ?>
				<?php else : ?>
					<p class="jr-empty"><?php echo esc_html( le_field( 'journalp_empty', $pp ) ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/home/contact-cta' ); ?>

</main>

<?php
get_footer();
