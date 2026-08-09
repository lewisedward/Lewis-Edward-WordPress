<?php
/**
 * Home section: Testimonials.
 *
 * Fully ACF-driven. The slides come from the `home_tst_items` Relationship
 * (Testimonial posts, reading the testimonial_* fields). The section only
 * renders when at least one testimonial is curated. SEO: heading <h2>; quotes
 * are <blockquote>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Shared across pages; content lives on the homepage settings.
$le_tst_pid = (int) get_option( 'page_on_front' );
$le_tst_pid = $le_tst_pid ? $le_tst_pid : false;

$le_items = le_field( 'home_tst_items', $le_tst_pid );
$le_items = is_array( $le_items ) ? $le_items : array();
$le_total = count( $le_items );

if ( ! $le_total ) {
	return; // Nothing curated yet — don't render an empty slider.
}
?>
<section class="section section--testimonials" aria-label="<?php esc_attr_e( 'Client feedback', 'lewisedward' ); ?>">
	<div class="section__inner">
		<div class="tst-card glass" data-reveal data-testimonials data-interval="7000">

			<div class="tst-card__head">
				<span class="eyebrow">
					<?php echo esc_html( le_field( 'home_tst_eyebrow', $le_tst_pid ) ); ?><sup class="tst-card__count"><?php echo esc_html( (string) $le_total ); ?></sup>
				</span>
				<span class="tst-card__rule" aria-hidden="true"></span>
				<span class="pulse-dot" aria-hidden="true"></span>
			</div>

			<div class="tst-card__stage" aria-live="polite">
				<?php
				foreach ( $le_items as $i => $item ) :
					$tid     = is_object( $item ) ? $item->ID : (int) $item;
					$quote   = le_field( 'testimonial_quote', $tid );
					$author  = le_field( 'testimonial_author', $tid );
					$role    = le_field( 'testimonial_role', $tid );
					$company = le_field( 'testimonial_company', $tid );
					$meta    = $role;
					if ( $company ) {
						$meta = $meta ? $meta . ' — ' . $company : $company;
					}
					?>
					<figure class="tst-slide<?php echo 0 === $i ? ' is-active' : ''; ?>" data-tst-slide="<?php echo esc_attr( (string) $i ); ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
						<blockquote class="tst-slide__quote"><p><?php echo esc_html( $quote ); ?></p></blockquote>
						<figcaption class="tst-slide__attr">
							<span class="tst-slide__author"><?php echo esc_html( $author ); ?></span>
							<?php if ( $meta ) : ?><span class="eyebrow tst-slide__meta"><?php echo esc_html( $meta ); ?></span><?php endif; ?>
						</figcaption>
					</figure>
					<?php
				endforeach;
				?>
			</div>

			<div class="tst-card__footer">
				<div class="tst-card__nav">
					<button class="tst-btn" type="button" data-tst-prev aria-label="<?php esc_attr_e( 'Previous testimonial', 'lewisedward' ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
					</button>
					<button class="tst-btn" type="button" data-tst-toggle aria-label="<?php esc_attr_e( 'Pause autoplay', 'lewisedward' ); ?>" aria-pressed="false">
						<svg class="tst-btn__pause" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
						<svg class="tst-btn__play" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
					</button>
					<button class="tst-btn" type="button" data-tst-next aria-label="<?php esc_attr_e( 'Next testimonial', 'lewisedward' ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
					</button>
				</div>
				<div class="tst-card__progress"><span class="tst-card__progress-bar" data-tst-progress></span></div>
				<div class="eyebrow tst-card__counter">
					<span class="text-primary" data-tst-current>01</span><span>/</span><span data-tst-total><?php echo esc_html( str_pad( (string) $le_total, 2, '0', STR_PAD_LEFT ) ); ?></span>
				</div>
			</div>

		</div>
	</div>
</section>
<?php
wp_enqueue_script( 'le-testimonials' );
