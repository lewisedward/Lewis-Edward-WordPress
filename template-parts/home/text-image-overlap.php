<?php
/**
 * Home section: Text / Image Overlap (Partnership + additional services).
 *
 * Fully ACF-driven. Text prefilled via ACF defaults; the video/poster are ACF
 * media fields and the service items come from the `home_overlap_items`
 * Relationship (Service posts). Video and item grids only render when set.
 * SEO: intro sentence is <h2>; each service card title is <h3>; the "Services"
 * wordmark is decorative.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$le_items    = le_field( 'home_overlap_items' );
$le_items    = is_array( $le_items ) ? $le_items : array();
$le_count    = count( $le_items );
$le_video    = le_field( 'home_overlap_video' );
$le_poster   = le_field( 'home_overlap_poster' );
$le_poster_u = ( is_array( $le_poster ) && ! empty( $le_poster['url'] ) ) ? $le_poster['url'] : '';
$le_wordmark = le_field( 'home_overlap_wordmark' );

/**
 * Render one service card (used in both mobile and desktop grids).
 *
 * @param int    $sid     Service post ID.
 * @param int    $i       Index (for numbering).
 * @param string $variant 'stacked' | 'column'.
 */
function le_overlap_card( $sid, $i, $variant ) {
	$num = str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT );
	$cls = 'overlap-svc overlap-svc--' . $variant . ' glass';
	echo '<a class="' . esc_attr( $cls ) . '" href="' . esc_url( get_permalink( $sid ) ) . '" data-cursor="hover">';
	echo '<span class="overlap-svc__num">' . esc_html( $num ) . '</span>';
	echo '<span class="overlap-svc__body">';
	echo '<h3 class="overlap-svc__title">' . esc_html( get_the_title( $sid ) ) . '</h3>';
	echo '<p class="overlap-svc__desc">' . esc_html( le_field( 'service_short_description', $sid ) ) . '</p>';
	echo '</span>';
	echo '</a>';
}

/**
 * Normalise a relationship entry to a post ID.
 *
 * @param mixed $item Post object or ID.
 * @return int
 */
function le_rel_id( $item ) {
	return is_object( $item ) ? (int) $item->ID : (int) $item;
}
?>
<section class="section section--overlap" aria-label="<?php esc_attr_e( 'Partnership and additional services', 'lewisedward' ); ?>">
	<div class="section__inner">
		<div class="overlap-card glass" data-reveal>

			<div class="overlap-card__head">
				<span class="eyebrow overlap-card__eyebrow">
					<?php echo esc_html( le_field( 'home_overlap_eyebrow' ) ); ?><?php if ( $le_count ) : ?><sup class="overlap-card__count"><?php echo esc_html( (string) $le_count ); ?></sup><?php endif; ?>
				</span>
				<span class="overlap-card__rule" aria-hidden="true"></span>
				<span class="overlap-card__dot" aria-hidden="true"></span>
			</div>

			<div class="overlap-card__intro">
				<h2 class="overlap-card__title"><?php echo le_field( 'home_overlap_title' ); ?></h2>
				<div class="overlap-card__aside">
					<p class="overlap-card__lede"><?php echo esc_html( le_field( 'home_overlap_lede' ) ); ?></p>
					<a class="arrow-link overlap-card__all" href="<?php echo esc_url( home_url( '/services' ) ); ?>" aria-label="<?php esc_attr_e( 'See all services', 'lewisedward' ); ?>">
						<span class="arrow-link__badge" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="arrow-link__label"><?php esc_html_e( 'More services', 'lewisedward' ); ?></span>
					</a>
				</div>
			</div>

			<?php /* ---- Mobile stacked layout ---- */ ?>
			<div class="overlap-mobile">
				<span class="overlap-wordmark" aria-hidden="true"><?php echo esc_html( $le_wordmark ); ?></span>
				<?php if ( $le_video ) : ?>
					<div class="overlap-video">
						<video autoplay loop muted playsinline preload="none"<?php echo $le_poster_u ? ' poster="' . esc_url( $le_poster_u ) . '"' : ''; ?>>
							<source src="<?php echo esc_url( $le_video ); ?>" type="video/mp4" />
						</video>
					</div>
				<?php endif; ?>
				<?php if ( $le_count ) : ?>
					<div class="overlap-grid overlap-grid--stacked">
						<?php foreach ( $le_items as $i => $item ) { le_overlap_card( le_rel_id( $item ), $i, 'stacked' ); } ?>
					</div>
				<?php endif; ?>
			</div>

			<?php /* ---- Desktop overlapping layout ---- */ ?>
			<div class="overlap-desktop" data-overlap>
				<?php if ( $le_video ) : ?>
					<div class="overlap-desktop__video" data-overlap-image>
						<video autoplay loop muted playsinline preload="none"<?php echo $le_poster_u ? ' poster="' . esc_url( $le_poster_u ) . '"' : ''; ?>>
							<source src="<?php echo esc_url( $le_video ); ?>" type="video/mp4" />
						</video>
					</div>
				<?php endif; ?>

				<?php if ( $le_count ) : ?>
					<div class="overlap-desktop__grid">
						<?php foreach ( $le_items as $i => $item ) { le_overlap_card( le_rel_id( $item ), $i, 'column' ); } ?>
					</div>
				<?php endif; ?>

				<div class="overlap-desktop__wordmark" data-overlap-heading>
					<span class="overlap-wordmark overlap-wordmark--base"><?php echo esc_html( $le_wordmark ); ?></span>
					<span class="overlap-wordmark overlap-wordmark--clip" data-overlap-clip aria-hidden="true"><?php echo esc_html( $le_wordmark ); ?></span>
				</div>
			</div>

		</div>
	</div>
</section>
<?php
wp_enqueue_script( 'le-overlap' );
