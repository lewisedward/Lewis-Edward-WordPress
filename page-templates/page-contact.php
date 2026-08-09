<?php
/**
 * Template Name: Contact
 *
 * The Contact page — fully ACF-driven (field group "Contact Page"). No fallback
 * content. Two columns: a sticky left rail (eyebrow, H1, lead, divider and a
 * "Need a quote?" card linking to /quotes) and a right column that renders the
 * Gravity Forms form from a shortcode the client pastes into ACF.
 *
 * SEO: the left rail carries the single <h1>; the quote card title is <h2>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$le_q_url = le_field( 'contact_quote_url' );
$le_q_url = ( 0 === strpos( (string) $le_q_url, 'http' ) ) ? $le_q_url : home_url( $le_q_url );

$le_form = le_field( 'contact_form_shortcode' );
?>

<main id="main" class="site-main site-main--contact le-form-page">

	<section class="section section--contact" aria-label="<?php esc_attr_e( 'Contact', 'lewisedward' ); ?>">
		<div class="section__inner">
			<div class="contact-grid">

				<?php /* ================= LEFT: sticky intro + quote card ================= */ ?>
				<div class="contact-intro" data-reveal>
					<span class="eyebrow contact-intro__eyebrow"><?php echo esc_html( le_field( 'contact_eyebrow' ) ); ?></span>
					<h1 class="contact-intro__title"><?php echo esc_html( le_field( 'contact_h1' ) ); ?></h1>
					<p class="contact-intro__lead"><?php echo esc_html( le_field( 'contact_lead' ) ); ?></p>

					<span class="contact-intro__rule" aria-hidden="true"></span>

					<?php
					$le_q_title = le_field( 'contact_quote_title' );
					if ( $le_q_title ) :
						?>
						<a class="contact-quote glass" href="<?php echo esc_url( $le_q_url ); ?>" data-cursor="Quote">
							<span class="contact-quote__glow contact-quote__glow--a" aria-hidden="true"></span>
							<span class="contact-quote__glow contact-quote__glow--b" aria-hidden="true"></span>
							<div class="contact-quote__body">
								<?php $le_q_badge = le_field( 'contact_quote_badge' ); ?>
								<?php if ( $le_q_badge ) : ?>
									<span class="contact-quote__badge">
										<span class="contact-quote__dot" aria-hidden="true"></span>
										<?php echo esc_html( $le_q_badge ); ?>
									</span>
								<?php endif; ?>
								<h2 class="contact-quote__title"><?php echo esc_html( $le_q_title ); ?></h2>
								<p class="contact-quote__desc"><?php echo esc_html( le_field( 'contact_quote_desc' ) ); ?></p>
							</div>
							<span class="contact-quote__cta">
								<?php echo esc_html( le_field( 'contact_quote_btn' ) ); ?>
								<span class="contact-quote__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</span>
						</a>
					<?php endif; ?>
				</div>

				<?php /* ================= RIGHT: Gravity Forms ================= */ ?>
				<div class="contact-form" data-reveal>
					<?php
					if ( $le_form ) {
						echo do_shortcode( $le_form ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Gravity Forms shortcode output.
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
