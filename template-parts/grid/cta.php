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

// Next-step links replace the project previews where projects are already the page's subject (the Work index).
$g_self      = is_singular( 'work' ) ? (int) get_queried_object_id() : 0;
$g_cta_links = array();
if ( is_page_template( 'page-templates/page-work.php' ) || is_post_type_archive( 'work' ) || is_tax( 'work_category' ) ) {
	$g_svc_n     = (int) wp_count_posts( 'service' )->publish;
	$g_cta_links = array(
		array( 'label' => __( 'Services', 'lewisedward' ), 'title' => $g_svc_n ? sprintf( /* translators: %d: number of services */ __( '%d ways we can help', 'lewisedward' ), $g_svc_n ) : __( 'What we do', 'lewisedward' ), 'url' => home_url( '/services/' ) ),
		array( 'label' => __( 'FAQs', 'lewisedward' ), 'title' => __( 'Questions answered', 'lewisedward' ), 'url' => home_url( '/faq/' ) ),
		array( 'label' => __( 'Contact', 'lewisedward' ), 'title' => __( 'Start a conversation', 'lewisedward' ), 'url' => home_url( '/contact/' ) ),
	);
}
$g_cta_links = apply_filters( 'le_grid_cta_links', $g_cta_links );
?>
	<section class="g-row g-cta" aria-label="<?php esc_attr_e( 'Contact', 'lewisedward' ); ?>">
		<a class="g-cell g-lime g-cta__go" href="<?php echo esc_url( $g_cta_url ); ?>">
			<h2><?php echo esc_html( le_field( 'home_cta_line1', $g_pid ) ); ?> <br><?php echo esc_html( le_field( 'home_cta_line2_pre', $g_pid ) ); ?> <em><?php echo esc_html( le_field( 'home_cta_word', $g_pid ) ); ?></em></h2>
			<span class="g-mono"><?php echo esc_html( le_field( 'home_cta_ring', $g_pid, __( 'Request a quote', 'lewisedward' ) ) ); ?></span>
			<?php echo le_grid_big_arrow( 'g-cta__arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
		<?php if ( $g_cta_links ) : ?>
		<nav class="g-cell g-cta__links" aria-label="<?php esc_attr_e( 'Next steps', 'lewisedward' ); ?>">
			<?php foreach ( $g_cta_links as $gi => $gl ) : ?>
			<a class="g-cta__link" href="<?php echo esc_url( $gl['url'] ); ?>"><span class="g-mono"><?php echo esc_html( sprintf( '%02d / %s', $gi + 1, $gl['label'] ) ); ?></span><span class="g-cta__t"><?php echo esc_html( $gl['title'] ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			<?php endforeach; ?>
		</nav>
		<?php else : ?>
		<div class="g-cell g-cta__previews">
			<?php $g_prev = le_field( 'home_cta_previews', $g_pid ); $g_prev = is_array( $g_prev ) ? $g_prev : array(); $g_shown = 0; foreach ( $g_prev as $pv ) : $pvid = $g_id( $pv ); if ( $g_shown >= 4 || $pvid === $g_self ) { continue; } $pvimg = le_grid_work_img( $pvid, 'medium_large', array( 'loading' => 'lazy' ) ); if ( ! $pvimg ) { continue; } $g_shown++; ?>
			<a class="g-ph g-cta__preview"<?php echo le_grid_work_href( $pvid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo $pvimg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="g-cap g-mono"><?php echo esc_html( get_the_title( $pvid ) ); ?></span></a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</section>

