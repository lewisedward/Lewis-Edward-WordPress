<?php
/**
 * Grid skin — single Journal entry. Same outline as single.php (h1 title;
 * editor headings in the body; h3 per "keep reading" card).
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$jr_id   = get_the_ID();
$jr_cats = get_the_category( $jr_id );
$jr_cat  = ( ! empty( $jr_cats ) && $jr_cats[0] instanceof WP_Term ) ? $jr_cats[0] : null;
$jr_read = le_reading_time( $jr_id ) . ' ' . __( 'min read', 'lewisedward' );
$jr_more = array();
foreach ( array( get_previous_post(), get_next_post() ) as $adj ) {
	if ( $adj instanceof WP_Post ) {
		$jr_more[ $adj->ID ] = $adj;
	}
}
if ( count( $jr_more ) < 2 ) {
	foreach ( get_posts( array( 'post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => array_merge( array( $jr_id ), array_keys( $jr_more ) ), 'no_found_rows' => true ) ) as $f ) {
		if ( count( $jr_more ) >= 2 ) { break; }
		$jr_more[ $f->ID ] = $f;
	}
}
$jr_more = array_slice( $jr_more, 0, 2, true );
$pp      = le_journal_page_id();
?>
<main id="main" class="site-main site-main--journal-single g-main">

	<section class="g-row g-phero g-phero--post" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
		<div class="g-cell g-phero__main">
			<div class="g-phero__top">
				<nav class="g-phero__crumbs g-mono" aria-label="<?php esc_attr_e( 'Breadcrumb', 'lewisedward' ); ?>"><a href="<?php echo esc_url( $pp ? get_permalink( $pp ) : home_url( '/journal' ) ); ?>"><?php esc_html_e( 'Journal', 'lewisedward' ); ?></a><?php if ( $jr_cat ) : ?><span>/</span><a href="<?php echo esc_url( get_category_link( $jr_cat ) ); ?>"><?php echo esc_html( $jr_cat->name ); ?></a><?php endif; ?></nav>
				<span class="g-mono"><?php echo esc_html( get_the_date( 'M Y' ) . ' · ' . $jr_read ); ?></span>
			</div>
			<h1 class="g-phero__title g-phero__title--long"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><div class="g-phero__foot"><p class="g-phero__lede"><?php echo esc_html( get_the_excerpt() ); ?></p></div><?php endif; ?>
		</div>
		<?php if ( has_post_thumbnail() ) : ?>
		<div class="g-cell g-ph"><?php the_post_thumbnail( 'large', array( 'alt' => le_img_alt( get_post_thumbnail_id(), get_the_title() ), 'loading' => 'eager', 'decoding' => 'async', 'fetchpriority' => 'high' ) ); ?></div>
		<?php endif; ?>
	</section>

	<section class="g-row g-sec" aria-label="<?php esc_attr_e( 'Article', 'lewisedward' ); ?>">
		<div class="g-cell g-sec__intro g-sec__sticky"><div class="g-sec__in">
			<div class="g-sec__top"><span class="g-label g-mono"><?php esc_html_e( 'Read', 'lewisedward' ); ?></span><span class="g-mono g-sec__count" data-g-read-pct>0%</span></div>
			<div class="g-progress" data-g-read aria-hidden="true"><span data-g-read-fill></span></div>
			<div class="g-sec__text"><span class="g-mono" style="display:block;color:hsl(var(--foreground)/0.5);margin-bottom:8px"><?php esc_html_e( 'Published', 'lewisedward' ); ?></span><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time><?php if ( $jr_cat ) : ?><br><span class="g-mono" style="display:block;color:hsl(var(--foreground)/0.5);margin:20px 0 8px"><?php esc_html_e( 'Filed under', 'lewisedward' ); ?></span><a href="<?php echo esc_url( get_category_link( $jr_cat ) ); ?>" style="color:inherit"><?php echo esc_html( $jr_cat->name ); ?></a><?php endif; ?></div>
			<a class="g-line-link g-mono g-sec__link" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><span><?php esc_html_e( 'Talk to Lewis', 'lewisedward' ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			<?php
			$sh_url   = get_permalink();
			$sh_title = get_the_title();
			$sh_links = array(
				'LinkedIn' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $sh_url ),
				'X'        => 'https://twitter.com/intent/tweet?url=' . rawurlencode( $sh_url ) . '&text=' . rawurlencode( $sh_title ),
				'Facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $sh_url ),
				'WhatsApp' => 'https://wa.me/?text=' . rawurlencode( $sh_title . ' ' . $sh_url ),
				'Email'    => 'mailto:?subject=' . rawurlencode( $sh_title ) . '&body=' . rawurlencode( $sh_url ),
			);
			?>
			<div class="g-share">
				<span class="g-mono g-share__label"><?php esc_html_e( 'Share', 'lewisedward' ); ?></span>
				<ul class="g-share__list">
					<?php foreach ( $sh_links as $sh_name => $sh_href ) : ?>
					<li><a class="g-share__btn g-mono" href="<?php echo esc_url( $sh_href ); ?>"<?php echo 'Email' === $sh_name ? '' : ' target="_blank" rel="noopener"'; ?>><?php echo esc_html( $sh_name ); ?><span class="g-sr"><?php echo 'Email' === $sh_name ? esc_html__( ' (opens your email app)', 'lewisedward' ) : esc_html__( ' (opens in a new tab)', 'lewisedward' ); ?></span></a></li>
					<?php endforeach; ?>
					<li><button type="button" class="g-share__btn g-mono" data-g-copy="<?php echo esc_url( $sh_url ); ?>" data-g-copied="<?php esc_attr_e( 'Copied', 'lewisedward' ); ?>"><?php esc_html_e( 'Copy link', 'lewisedward' ); ?></button></li>
				</ul>
			</div>
		</div></div>
		<article class="g-cell g-dark g-prose__cell">
			<div class="g-prose jr-body__prose"><?php the_content(); ?></div>
		</article>
	</section>

	<?php if ( $jr_more ) : ?>
	<section class="g-row g-sec" aria-label="<?php esc_attr_e( 'More from the journal', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => __( 'Continue', 'lewisedward' ), 'count' => '02', 'title' => le_grid_heading( array( array( __( 'Keep', 'lewisedward' ) ), array( __( 'reading', 'lewisedward' ), 'muted' ), array( __( 'the journal.', 'lewisedward' ), 'accent' ) ) ), 'link' => array( 'url' => $pp ? get_permalink( $pp ) : home_url( '/journal' ), 'title' => __( 'All articles', 'lewisedward' ) ) ) ); ?>
		<div class="g-cell g-dark g-sec__body">
			<?php $n = 0; foreach ( $jr_more as $m ) : $mid = $m->ID; $n++; $mc = get_the_category( $mid ); ?>
			<a class="g-li" href="<?php echo esc_url( get_permalink( $mid ) ); ?>">
				<span class="g-li__num g-mono"><?php echo esc_html( str_pad( (string) $n, 2, '0', STR_PAD_LEFT ) ); ?></span>
				<span><h3 class="g-li__title"><?php echo esc_html( get_the_title( $mid ) ); ?></h3><span class="g-li__sub g-mono"><?php echo esc_html( ( ! empty( $mc ) ? $mc[0]->name . ' · ' : '' ) . get_the_date( 'M Y', $mid ) ); ?></span></span>
				<p class="g-li__desc"><?php echo esc_html( get_the_excerpt( $mid ) ); ?></p>
				<span class="g-li__go"><?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/grid/cta' ); ?>

</main>
