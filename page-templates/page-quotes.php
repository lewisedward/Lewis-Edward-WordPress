<?php
/**
 * Template Name: Quotes
 *
 * The Request-a-Quote page — same two-column layout as Contact (field group
 * "Quotes Page"), with its own copy and a different Gravity Forms form. A sticky
 * left rail (lime eyebrow, big H1, lead, and a "few questions?" card linking to
 * Contact) beside the quote-request form rendered from an ACF shortcode.
 *
 * SEO: the left rail carries the single <h1>; the side-card title is <h2>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$qt_url = le_field( 'quotes_card_url' );
$qt_url = ( 0 === strpos( (string) $qt_url, 'http' ) ) ? $qt_url : home_url( $qt_url );

$qt_form = le_field( 'quotes_form_shortcode' );
?>

<main id="main" class="site-main site-main--quotes le-form-page">

	<section class="section section--quotes" aria-label="<?php esc_attr_e( 'Request a quote', 'lewisedward' ); ?>">
		<div class="section__inner">
			<div class="contact-grid">

				<?php /* ================= LEFT: sticky intro + card ================= */ ?>
				<div class="contact-intro" data-reveal>
					<span class="eyebrow contact-intro__eyebrow"><?php echo esc_html( le_field( 'quotes_eyebrow' ) ); ?></span>
					<h1 class="contact-intro__title"><?php echo nl2br( esc_html( le_field( 'quotes_h1' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_html run before nl2br. ?></h1>
					<p class="contact-intro__lead"><?php echo esc_html( le_field( 'quotes_lead' ) ); ?></p>

					<span class="contact-intro__rule" aria-hidden="true"></span>

					<?php
					$qt_c_title = le_field( 'quotes_card_title' );
					if ( $qt_c_title ) :
						?>
						<a class="contact-quote glass" href="<?php echo esc_url( $qt_url ); ?>" data-cursor="Contact">
							<span class="contact-quote__glow contact-quote__glow--a" aria-hidden="true"></span>
							<span class="contact-quote__glow contact-quote__glow--b" aria-hidden="true"></span>
							<div class="contact-quote__body">
								<?php $qt_c_badge = le_field( 'quotes_card_badge' ); ?>
								<?php if ( $qt_c_badge ) : ?>
									<span class="contact-quote__badge">
										<span class="contact-quote__dot" aria-hidden="true"></span>
										<?php echo esc_html( $qt_c_badge ); ?>
									</span>
								<?php endif; ?>
								<h2 class="contact-quote__title"><?php echo esc_html( $qt_c_title ); ?></h2>
								<p class="contact-quote__desc"><?php echo esc_html( le_field( 'quotes_card_desc' ) ); ?></p>
							</div>
							<span class="contact-quote__cta">
								<?php echo esc_html( le_field( 'quotes_card_btn' ) ); ?>
								<span class="contact-quote__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</span>
						</a>
					<?php endif; ?>
				</div>

				<?php /* ================= RIGHT: Gravity Forms ================= */ ?>
				<div class="contact-form" data-reveal>
					<?php
					if ( $qt_form ) {
						echo do_shortcode( $qt_form ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Gravity Forms shortcode output.
					}
					?>
				</div>

			</div>
		</div>
	</section>

</main>

<?php
wp_enqueue_script( 'le-form-groups' );
get_footer();
