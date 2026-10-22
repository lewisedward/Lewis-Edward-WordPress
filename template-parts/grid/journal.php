<?php
/**
 * Grid skin — Journal index (the Posts page, home.php) and category archives
 * (archive.php, $args['archive'] = true). Uses the main query so pagination and
 * SEO behave exactly as before; copy comes from the Journal page's ACF fields.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;
$jr_archive = ! empty( $args['archive'] );
$pp         = le_journal_page_id();
$paged      = max( 1, (int) get_query_var( 'paged' ) );
$per        = (int) get_option( 'posts_per_page' );
$base       = ( $paged - 1 ) * $per;
$total      = (int) $wp_query->found_posts;
$queried    = get_queried_object();
$active     = ( $queried instanceof WP_Term && 'category' === $queried->taxonomy ) ? $queried->slug : '';
$arch_name  = $jr_archive ? ( single_term_title( '', false ) ?: wp_strip_all_tags( get_the_archive_title() ) ) : '';
$arch_lead  = is_author() ? __( 'Written by', 'lewisedward' ) : __( 'Filed under', 'lewisedward' );
if ( is_author() ) {
	$arch_name = get_the_author_meta( 'display_name', (int) get_queried_object_id() );
}

$fid = 0;
if ( ! $jr_archive && 1 === $paged ) {
	$f   = $pp ? le_field( 'journalp_featured', $pp ) : '';
	$fid = is_object( $f ) ? (int) $f->ID : (int) $f;
	if ( ! $fid ) {
		$latest = get_posts( array( 'posts_per_page' => 1, 'no_found_rows' => true, 'fields' => 'ids' ) );
		$fid    = $latest ? (int) $latest[0] : 0;
	}
}
$jr_cats = static function ( $id ) {
	$c = le_post_cats( $id );
	return $c ? reset( $c ) : null;
};
$all_url   = $pp ? get_permalink( $pp ) : home_url( '/' );
$all_label = ( $pp ? le_field( 'journalp_all_label', $pp ) : '' ) ?: __( 'All entries', 'lewisedward' );
$terms     = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC' ) );
$terms     = is_array( $terms ) ? array_filter( $terms, 'le_is_real_category' ) : array();
?>
<main id="main" class="site-main site-main--journal g-main">

	<?php if ( ! $jr_archive ) : ?>
	<section class="g-row g-phero" aria-label="<?php esc_attr_e( 'Journal', 'lewisedward' ); ?>">
		<div class="g-cell g-phero__main">
			<div class="g-phero__top">
				<span class="g-label g-mono"><?php echo esc_html( le_field( 'journalp_eyebrow', $pp ) ); ?><?php if ( $total ) : ?> <span class="g-sec__count"><?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></span><?php endif; ?></span>
				<span class="g-mono"><?php echo esc_html( le_field( 'journalp_right_label', $pp ) ); ?></span>
			</div>
			<h1 class="g-phero__title"><?php echo le_grid_heading( array( array( le_field( 'journalp_h1_1', $pp ) ), array( le_field( 'journalp_h1_2', $pp ), 'muted' ), array( le_field( 'journalp_h1_3', $pp ), 'accent' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
			<div class="g-phero__foot">
				<p class="g-phero__lede"><?php echo esc_html( le_field( 'journalp_lead', $pp ) ); ?></p>
				<a class="g-line-link g-mono" href="#entries"><span><?php echo esc_html( le_field( 'journalp_archive_eyebrow', $pp ) ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</div>
		</div>
		<?php if ( $fid && has_post_thumbnail( $fid ) ) : $fc = $jr_cats( $fid ); ?>
		<a class="g-cell g-ph g-feature" href="<?php echo esc_url( get_permalink( $fid ) ); ?>">
			<?php echo get_the_post_thumbnail( $fid, 'large', array( 'alt' => le_img_alt( get_post_thumbnail_id( $fid ), get_the_title( $fid ) ), 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
			<span class="g-feature__label g-mono"><?php echo esc_html( le_field( 'journalp_featured_label', $pp ) ); ?></span>
			<span class="g-feature__foot">
				<span><span class="g-phero__meta g-mono"><?php if ( $fc ) : ?><span><?php echo esc_html( $fc->name ); ?></span><?php endif; ?><span><?php echo esc_html( get_the_date( 'M Y', $fid ) ); ?></span><span><?php echo esc_html( le_reading_time( $fid ) . ' ' . __( 'min read', 'lewisedward' ) ); ?></span></span><h2 class="g-feature__title" style="margin-top:12px"><?php echo esc_html( get_the_title( $fid ) ); ?></h2><p class="g-feature__desc"><?php echo esc_html( get_the_excerpt( $fid ) ); ?></p></span>
				<?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
		</a>
		<?php endif; ?>
	</section>
	<?php endif; ?>

	<section id="entries" class="g-row g-sec" aria-label="<?php echo esc_attr( $jr_archive ? $arch_name : __( 'Journal archive', 'lewisedward' ) ); ?>">
		<div class="g-cell g-sec__intro g-sec__sticky"><div class="g-sec__in">
			<div class="g-sec__top">
				<?php if ( $jr_archive ) : ?><a class="g-label g-mono" href="<?php echo esc_url( $all_url ); ?>" style="text-decoration:none"><?php esc_html_e( 'Journal', 'lewisedward' ); ?></a><?php else : ?><span class="g-label g-mono"><?php echo esc_html( le_field( 'journalp_archive_eyebrow', $pp ) ); ?></span><?php endif; ?>
				<span class="g-mono g-sec__count"><?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?> <?php echo esc_html( le_field( 'journalp_count_label', $pp, __( 'entries', 'lewisedward' ) ) ); ?></span>
			</div>
			<?php if ( $jr_archive ) : ?>
			<h1 class="g-sec__title"><?php echo le_grid_heading( array( array( $arch_lead ), array( $arch_name . '.', 'accent' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
			<?php else : ?>
			<h2 class="g-sec__title"><?php echo le_grid_heading( array( array( le_field( 'journalp_archive_h1', $pp ) ), array( le_field( 'journalp_archive_accent', $pp ), 'muted' ), array( le_field( 'journalp_archive_h3', $pp ), 'accent' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
			<?php endif; ?>
			<nav class="g-filters g-filters--stack" aria-label="<?php esc_attr_e( 'Categories', 'lewisedward' ); ?>">
				<a class="g-filter<?php echo '' === $active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $all_url ); ?>"><?php echo esc_html( $all_label ); ?></a>
				<?php foreach ( $terms as $t ) : ?><a class="g-filter<?php echo $active === $t->slug ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a><?php endforeach; ?>
			</nav>
		</div></div>
		<div class="g-cell g-dark g-sec__body">
			<?php
			if ( have_posts() ) :
				$num = $base;
				while ( have_posts() ) :
					the_post();
					$eid = get_the_ID();
					if ( $fid && $eid === $fid ) {
						continue;
					}
					$num++;
					$ec = $jr_cats( $eid );
					?>
			<a class="g-li" href="<?php the_permalink(); ?>">
				<span class="g-li__num g-mono"><?php echo esc_html( str_pad( (string) $num, 2, '0', STR_PAD_LEFT ) ); ?></span>
				<span><h3 class="g-li__title"><?php the_title(); ?></h3><span class="g-li__sub g-mono"><?php echo esc_html( ( $ec ? $ec->name . ' · ' : '' ) . get_the_date( 'M Y' ) . ' · ' . le_reading_time( $eid ) . ' ' . __( 'min read', 'lewisedward' ) ); ?></span></span>
				<p class="g-li__desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<span class="g-li__go"><?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</a>
					<?php
				endwhile;
				$links = paginate_links( array( 'type' => 'array', 'prev_text' => '&larr;', 'next_text' => '&rarr;' ) );
				if ( $links ) :
					echo '<nav class="g-pager" aria-label="' . esc_attr__( 'Pagination', 'lewisedward' ) . '">' . wp_kses_post( implode( '', $links ) ) . '</nav>';
				endif;
			else :
				?>
			<p class="g-li"><span class="g-li__num g-mono">00</span><span class="g-li__desc"><?php echo esc_html( le_field( 'journalp_empty', $pp, __( 'Nothing here yet.', 'lewisedward' ) ) ); ?></span></p>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/grid/cta' ); ?>

</main>
