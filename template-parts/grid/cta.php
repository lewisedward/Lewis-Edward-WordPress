<?php
/**
 * Grid skin — contact CTA (shared). Reads the homepage's fields so every page shows
 * the same block; the front page is the source of truth in wp-admin.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$g_pid = (int) get_option( 'page_on_front' );
$g_pid = $g_pid ? $g_pid : false;
$g_id  = static function ( $item ) {
	return is_object( $item ) ? (int) $item->ID : (int) $item;
};
$g_cta_url = le_field( 'home_cta_url', $g_pid, '/quotes' );
$g_cta_url = ( 0 === strpos( (string) $g_cta_url, 'http' ) ) ? $g_cta_url : home_url( $g_cta_url );
?>
	<section class="g-row g-cta" aria-label="<?php esc_attr_e( 'Contact', 'lewisedward' ); ?>">
		<a class="g-cell g-lime g-cta__go" href="<?php echo esc_url( $g_cta_url ); ?>">
			<h2><?php echo esc_html( le_field( 'home_cta_line1', $g_pid ) ); ?> <br><?php echo esc_html( le_field( 'home_cta_line2_pre', $g_pid ) ); ?> <em><?php echo esc_html( le_field( 'home_cta_word', $g_pid ) ); ?></em></h2>
			<span class="g-mono"><?php echo esc_html( le_field( 'home_cta_ring', $g_pid, __( 'Request a quote', 'lewisedward' ) ) ); ?></span>
			<svg class="g-cta__arrow" viewBox="0 0 120 120" aria-hidden="true" focusable="false"><path d="M8 8h104v104M112 8 8 112"/></svg>
		</a>
		<div class="g-cell g-cta__previews">
			<?php $g_prev = le_field( 'home_cta_previews', $g_pid ); $g_prev = is_array( $g_prev ) ? $g_prev : array(); foreach ( array_slice( $g_prev, 0, 4 ) as $pv ) : $pvid = $g_id( $pv ); $pvimg = le_grid_work_img( $pvid, 'medium_large', array( 'loading' => 'lazy' ) ); if ( ! $pvimg ) { continue; } ?>
			<a class="g-ph g-cta__preview" href="<?php echo esc_url( get_permalink( $pvid ) ); ?>"><?php echo $pvimg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="g-cap g-mono"><?php echo esc_html( get_the_title( $pvid ) ); ?></span></a>
			<?php endforeach; ?>
		</div>
	</section>

