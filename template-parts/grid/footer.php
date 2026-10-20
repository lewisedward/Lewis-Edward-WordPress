<?php
/**
 * Grid skin — footer.
 *
 * Three rows of cells: a line and the footer menus with the London clock; the
 * email and LinkedIn cells; then the wordmark on black beside the lime
 * "start a conversation" cell.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$le_linkedin  = le_option( 'social_linkedin', 'https://www.linkedin.com/in/lewis-edward/' );
$le_cta_pid   = (int) get_option( 'page_on_front' );
$le_cta_url   = le_field( 'home_cta_url', $le_cta_pid ? $le_cta_pid : false, '/quotes' );
$le_cta_url   = ( 0 === strpos( (string) $le_cta_url, 'http' ) ) ? $le_cta_url : home_url( $le_cta_url );
$le_menu      = function ( $location, $fallback ) {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu( array( 'theme_location' => $location, 'container' => false, 'items_wrap' => '<ul class="g-footer__menu">%3$s</ul>', 'depth' => 1, 'fallback_cb' => false ) );
		return;
	}
	echo '<ul class="g-footer__menu">';
	foreach ( $fallback as $f ) {
		echo '<li><a href="' . esc_url( home_url( $f[1] ) ) . '">' . esc_html( $f[0] ) . '</a></li>';
	}
	echo '</ul>';
};
?>
<footer class="g-footer">
	<div class="g-row g-footer__top">
		<div class="g-cell g-footer__line">
			<span class="g-label g-mono"><?php esc_html_e( 'Est. 2011', 'lewisedward' ); ?> &mdash; <?php echo esc_html( le_option( 'footer_location', __( 'London', 'lewisedward' ) ) ); ?></span>
			<span class="g-footer__line-text"><?php echo esc_html( le_option( 'grid_footer_line', __( 'Raise your game online', 'lewisedward' ) ) ); ?></span>
		</div>
		<div class="g-cell g-footer__nav"><span class="g-footer__heading g-mono"><?php esc_html_e( 'Explore', 'lewisedward' ); ?></span><?php $le_menu( 'footer_discover', array( array( 'Services', '/services' ), array( 'Work', '/work' ), array( 'About', '/about' ), array( 'Journal', '/journal' ) ) ); ?></div>
		<div class="g-cell g-footer__nav"><span class="g-footer__heading g-mono"><?php esc_html_e( 'Services', 'lewisedward' ); ?></span><?php $le_menu( 'footer_services', array( array( 'Web Design', '/services/web-design' ), array( 'Web Development', '/services/web-development' ), array( 'Website Support', '/services/website-support' ) ) ); ?></div>
		<div class="g-cell g-footer__clock">
			<div class="g-footer__time"><span class="g-footer__clockface" data-london-clockface aria-hidden="true"><span></span></span><span><?php echo esc_html( le_option( 'footer_location', __( 'London', 'lewisedward' ) ) ); ?><span class="g-mono" data-london-clock>--:--</span></span></div>
			<div class="g-footer__status g-mono"><i class="g-footer__pulse" aria-hidden="true"></i><?php esc_html_e( 'Available for new projects', 'lewisedward' ); ?></div>
		</div>
	</div>
	<div class="g-row g-footer__mid">
		<div class="g-cell g-footer__legalcell"><span class="g-footer__legal g-mono"><span>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php echo esc_html( le_option( 'footer_copyright_name', __( 'Lewis Edward Ltd', 'lewisedward' ) ) ); ?></span><span><?php echo esc_html( le_option( 'footer_location', __( 'London', 'lewisedward' ) ) ); ?>, UK</span></span></div>
		<a class="g-cell" href="mailto:hello@lewisedward.com">hello@lewisedward.com</a>
		<a class="g-cell" href="<?php echo esc_url( $le_linkedin ); ?>" target="_blank" rel="noopener noreferrer"><span>LinkedIn</span><?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		<div class="g-cell g-cell--split"><a href="<?php echo esc_url( home_url( '/privacy' ) ); ?>"><?php esc_html_e( 'Privacy policy', 'lewisedward' ); ?></a><button class="g-footer__cookies" type="button" data-consent-open><?php esc_html_e( 'Cookie settings', 'lewisedward' ); ?></button></div>
	</div>
</footer>
