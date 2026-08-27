<?php
/**
 * Home section: Contact CTA.
 *
 * Fully ACF-driven. Text prefilled via ACF defaults; the hover-preview
 * thumbnails come from the `home_cta_previews` Relationship (Work posts'
 * featured images). SEO: section heading is the single <h2>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// This CTA is shared across pages; its content lives on the homepage settings,
// so source every field from the front page (falls back to current post only
// if no static front page is set).
$le_cta_pid  = (int) get_option( 'page_on_front' );
$le_cta_pid  = $le_cta_pid ? $le_cta_pid : false;

$le_line1    = le_field( 'home_cta_line1', $le_cta_pid );
$le_line2p   = le_field( 'home_cta_line2_pre', $le_cta_pid );
$le_word     = le_field( 'home_cta_word', $le_cta_pid );
$le_ring     = le_field( 'home_cta_ring', $le_cta_pid );
$le_cta_url  = le_field( 'home_cta_url', $le_cta_pid );
$le_cta_url  = ( 0 === strpos( (string) $le_cta_url, 'http' ) ) ? $le_cta_url : home_url( $le_cta_url );

// Hover-preview images from curated Work projects' featured images.
$le_previews = le_field( 'home_cta_previews', $le_cta_pid );
$le_previews = is_array( $le_previews ) ? $le_previews : array();
$le_prev_ids = array();
foreach ( $le_previews as $p ) {
	$pid = is_object( $p ) ? $p->ID : (int) $p;
	if ( has_post_thumbnail( $pid ) ) {
		$le_prev_ids[] = get_post_thumbnail_id( $pid );
	}
}
?>
<section class="section section--contact-cta" aria-label="<?php esc_attr_e( 'Get a quote', 'lewisedward' ); ?>">
	<div class="section__inner">
		<div class="cta-card glass" data-reveal>

			<div class="cta-card__row">
				<div class="cta-card__heading">
					<h2 class="cta-card__title" data-reveal-clip>
						<span class="cta-card__line"><?php echo esc_html( $le_line1 ); ?></span>
						<span class="cta-card__line">
							<?php echo esc_html( $le_line2p ); ?>
							<span class="cta-card__project text-primary">
								<?php echo esc_html( $le_word ); ?>
							</span>
						</span>
					</h2>
				</div>

				<div class="cta-card__badge-wrap">
					<a class="cta-badge" href="<?php echo esc_url( $le_cta_url ); ?>" aria-label="<?php esc_attr_e( 'Request a quote', 'lewisedward' ); ?>">
						<span class="cta-badge__glass glass" aria-hidden="true"></span>
						<svg class="cta-badge__ring" viewBox="0 0 164 164" width="164" height="164" aria-hidden="true">
							<defs>
								<path id="le-cta-circle" d="M 82,82 m -64,0 a 64,64 0 1,1 128,0 a 64,64 0 1,1 -128,0" />
							</defs>
							<text class="cta-badge__ring-text">
								<?php // Append a separator so the two repeated phrases are divided at the seam too. ?>
								<textPath href="#le-cta-circle"><?php echo esc_html ($le_ring ); ?></textPath>
							</text>
						</svg>
						<span class="cta-badge__core" aria-hidden="true" data-cursor-invert>
							<svg width="32" height="26" viewBox="0 0 36 30" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21.7909 0.707691L35.2909 14.2077C35.3976 14.3093 35.4825 14.4314 35.5405 14.5668C35.5985 14.7022 35.6284 14.8479 35.6284 14.9952C35.6284 15.1425 35.5985 15.2882 35.5405 15.4236C35.4825 15.5589 35.3976 15.6811 35.2909 15.7827L21.7909 29.2827C21.5821 29.4916 21.2988 29.6089 21.0034 29.6089C20.708 29.6089 20.4248 29.4916 20.2159 29.2827C20.0071 29.0738 19.8897 28.7906 19.8897 28.4952C19.8897 28.1998 20.0071 27.9166 20.2159 27.7077L31.7847 16.1202H1.50342C1.20505 16.1202 0.918901 16.0017 0.707923 15.7907C0.496944 15.5797 0.378418 15.2936 0.378418 14.9952C0.378418 14.6968 0.496944 14.4107 0.707923 14.1997C0.918901 13.9887 1.20505 13.8702 1.50342 13.8702H31.7847L20.2159 2.28269C20.0071 2.07383 19.8897 1.79056 19.8897 1.49519C19.8897 1.19982 20.0071 0.91655 20.2159 0.707691C20.4248 0.498835 20.708 0.381498 21.0034 0.381498C21.2988 0.381498 21.5821 0.498835 21.7909 0.707691Z" fill="currentColor"/></svg>
						</span>
					</a>
				</div>
			</div>

		</div>
	</div>
</section>
<?php
wp_enqueue_script( 'le-contact-cta' );
