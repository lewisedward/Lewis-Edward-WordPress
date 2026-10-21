<?php
/**
 * Grid skin — Contact and Request-a-quote pages (same layout, different field
 * prefix). Intro + cross-link card on the left, the Gravity Forms form on the
 * right. h1 in the intro; the card title is h2.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cf_quotes = ( isset( $args['variant'] ) && 'quotes' === $args['variant'] );
$p         = $cf_quotes ? 'quotes_' : 'contact_';
$cf_url    = le_field( $cf_quotes ? 'quotes_card_url' : 'contact_quote_url' );
$cf_url    = ( 0 === strpos( (string) $cf_url, 'http' ) ) ? $cf_url : home_url( $cf_url );
$cf_form   = le_field( $p . 'form_shortcode' );
$cf_title  = le_field( $cf_quotes ? 'quotes_card_title' : 'contact_quote_title' );
$cf_badge  = le_field( $cf_quotes ? 'quotes_card_badge' : 'contact_quote_badge' );
$cf_desc   = le_field( $cf_quotes ? 'quotes_card_desc' : 'contact_quote_desc' );
$cf_btn    = le_field( $cf_quotes ? 'quotes_card_btn' : 'contact_quote_btn' );
?>
<main id="main" class="site-main site-main--contact g-main">

	<section class="g-row g-sec g-sec--form" aria-label="<?php echo esc_attr( $cf_quotes ? __( 'Request a quote', 'lewisedward' ) : __( 'Contact', 'lewisedward' ) ); ?>">
		<div class="g-cell g-sec__intro g-sec__sticky"><div class="g-sec__in">
			<div class="g-sec__top"><span class="g-label g-mono"><?php echo esc_html( le_field( $p . 'eyebrow' ) ); ?></span></div>
			<h1 class="g-phero__title g-phero__title--long"><?php echo nl2br( esc_html( le_field( $p . 'h1' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
			<p class="g-phero__lede"><?php echo esc_html( le_field( $p . 'lead' ) ); ?></p>
			<?php if ( $cf_title ) : ?>
			<a class="g-cell g-lime g-side g-side--card" href="<?php echo esc_url( $cf_url ); ?>">
				<?php if ( $cf_badge ) : ?><span class="g-label g-mono"><?php echo esc_html( $cf_badge ); ?></span><?php endif; ?>
				<h2><?php echo esc_html( $cf_title ); ?></h2>
				<p><?php echo esc_html( $cf_desc ); ?></p>
				<span class="g-line-link g-mono"><span><?php echo esc_html( $cf_btn ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</a>
			<?php endif; ?>
		</div></div>
		<div class="g-cell g-dark g-form">
			<?php if ( $cf_form ) { echo do_shortcode( $cf_form ); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</section>

</main>
