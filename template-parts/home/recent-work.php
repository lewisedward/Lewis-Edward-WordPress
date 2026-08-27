<?php
/**
 * Home section: Recent Work.
 *
 * Fully ACF-driven. Text prefilled via ACF defaults; the carousel is built from
 * the `home_work_projects` Relationship (Work posts, in the chosen order),
 * reading each project's featured image + `work_card_description`. The carousel
 * only renders when projects are curated. SEO: heading <h2>, each title <h3>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$le_related = le_field( 'home_work_projects' );
$le_related = is_array( $le_related ) ? $le_related : array();

$le_items = array();
foreach ( $le_related as $le_post ) {
	$pid  = is_object( $le_post ) ? $le_post->ID : (int) $le_post;
	$desc = le_field( 'work_card_description', $pid );
	$le_items[] = array(
		'title'    => get_the_title( $pid ),
		'url'      => get_permalink( $pid ),
		'desc'     => $desc,
		'thumb_id' => get_post_thumbnail_id( $pid ),
	);
}
$le_count = count( $le_items );
?>
<section class="section section--recent-work" aria-label="<?php esc_attr_e( 'Recent work', 'lewisedward' ); ?>">
	<div class="section__inner">
		<div class="work-card glass" data-reveal data-work-carousel>

			<div class="work-card__head">
				<span class="eyebrow">
					<?php esc_html_e( 'Recent Work', 'lewisedward' ); ?><?php if ( $le_count ) : ?><sup class="work-card__count"><?php echo esc_html( (string) $le_count ); ?></sup><?php endif; ?>
				</span>
				<span class="work-card__rule" aria-hidden="true"></span>
				<span class="pulse-dot" aria-hidden="true"></span>
			</div>

			<div class="work-card__intro">
				<h2 class="work-card__title"><?php echo esc_html( le_field( 'home_work_title_pre' ) ); ?> <span class="text-primary"><?php echo esc_html( le_field( 'home_work_title_accent' ) ); ?></span> <span class="text-muted"><?php echo esc_html( le_field( 'home_work_title_post' ) ); ?></span></h2>
				<div class="work-card__aside">
					<p class="work-card__lede"><?php echo esc_html( le_field( 'home_work_lede' ) ); ?></p>
					<a class="arrow-link work-card__all" href="<?php echo esc_url( home_url( '/work' ) ); ?>" aria-label="<?php esc_attr_e( 'View all work', 'lewisedward' ); ?>">
						<span class="arrow-link__badge" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="arrow-link__label"><?php esc_html_e( 'View all work', 'lewisedward' ); ?></span>
					</a>
				</div>
			</div>

			<?php if ( $le_count ) : ?>
				<div class="work-carousel">
					<div class="work-carousel__track" data-work-track data-work-loop>
						<?php foreach ( $le_items as $i => $p ) : ?>
							<div class="work-slide">
								<a class="work-project" href="<?php echo esc_url( $p['url'] ); ?>" data-cursor="View">
									<div class="work-project__media">
										<?php
										if ( $p['thumb_id'] ) {
											echo wp_get_attachment_image( (int) $p['thumb_id'], 'le_hero', false, array( 'alt' => esc_attr( $p['title'] ), 'loading' => 'lazy', 'decoding' => 'async', 'draggable' => 'false' ) );
										}
										?>
										<div class="work-project__overlay">
											<div class="work-project__info glass" data-cursor-ignore>
												<div class="work-project__meta">
													<span class="work-project__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
													<span class="work-project__meta-rule" aria-hidden="true"></span>
													<span class="work-project__meta-label"><?php esc_html_e( 'Case study', 'lewisedward' ); ?></span>
												</div>
												<h3 class="work-project__title"><?php echo esc_html( $p['title'] ); ?></h3>
												<p class="work-project__desc"><?php echo esc_html( $p['desc'] ); ?></p>
												<div class="work-project__cta">
													<span class="work-project__cta-badge" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
													<span class="work-project__cta-label"><?php esc_html_e( 'Discover', 'lewisedward' ); ?></span>
												</div>
											</div>
										</div>
									</div>
								</a>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="work-card__footer">
					<div class="work-card__nav">
						<button class="work-nav-btn" type="button" data-work-prev aria-label="<?php esc_attr_e( 'Previous project', 'lewisedward' ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
						</button>
						<button class="work-nav-btn" type="button" data-work-next aria-label="<?php esc_attr_e( 'Next project', 'lewisedward' ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
						</button>
					</div>
					<div class="work-card__progress"><span class="work-card__progress-bar" data-work-progress></span></div>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
<?php
if ( $le_count ) {
	wp_enqueue_script( 'le-recent-work' );
}
