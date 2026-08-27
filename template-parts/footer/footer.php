<?php
/**
 * Footer — faithful port of the React Footer.
 *
 * A glass "pill" with four columns (brand + social, then three WP-menu columns:
 * Discover, Services, Take Action) and a bottom glass bar with copyright,
 * privacy link, live London time and a back-to-top control.
 *
 * The three link columns are driven by standard WP menus assigned to the
 * footer_discover / footer_services / footer_take_action locations
 * (Appearance → Menus). Brand, tagline and social are Theme Options / brand.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$le_logo     = le_option( 'site_logo' );
$le_logo_url = ( is_array( $le_logo ) && ! empty( $le_logo['url'] ) ) ? $le_logo['url'] : LE_URI . '/assets/images/lewis-edward-logo.webp';
$le_tagline  = le_option( 'footer_tagline', __( 'Web design, development and support agency based in London.', 'lewisedward' ) );
$le_linkedin = le_option( 'social_linkedin', 'https://www.linkedin.com/in/lewis-edward/' );
$le_instagram = le_option( 'social_instagram', 'https://www.instagram.com/lewisedwardcom/' );

// Shared args for the three footer menus: no container, hover-underline links.
$le_footer_menu = function ( $location ) {
	if ( ! has_nav_menu( $location ) ) {
		return;
	}
	wp_nav_menu( array(
		'theme_location' => $location,
		'container'      => false,
		'menu_class'     => 'footer-menu',
		'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
		'depth'          => 1,
		'link_before'    => '<span class="footer-link__text">',
		'link_after'     => '</span><span class="footer-link__underline" aria-hidden="true"></span>',
		'fallback_cb'    => false,
	) );
};
?>
<footer class="site-footer">
	<div class="site-footer__inner">

		<div class="footer-pill glass">
			<div class="footer-grid">

				<div class="footer-brand">
					<a class="footer-brand__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Lewis Edward — home', 'lewisedward' ); ?>">
						<img src="<?php echo esc_url( $le_logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="95" height="29" />
					</a>
					<p class="footer-brand__tagline"><?php echo esc_html( $le_tagline ); ?></p>
					<div class="footer-social">
						<?php if ( $le_linkedin ) : ?>
							<a class="footer-social__link" href="<?php echo esc_url( $le_linkedin ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'LinkedIn', 'lewisedward' ); ?>">
								<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
							</a>
						<?php endif; ?>
						<?php if ( $le_instagram ) : ?>
							<a class="footer-social__link" href="<?php echo esc_url( $le_instagram ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Instagram', 'lewisedward' ); ?>">
								<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C16.67.014 16.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
							</a>
						<?php endif; ?>
					</div>
				</div>

				<div class="footer-col">
					<p class="footer-col__title"><?php echo esc_html( le_option( 'footer_col_discover', __( 'Discover', 'lewisedward' ) ) ); ?></p>
					<?php $le_footer_menu( 'footer_discover' ); ?>
				</div>

				<div class="footer-col">
					<p class="footer-col__title"><?php echo esc_html( le_option( 'footer_col_services', __( 'Services', 'lewisedward' ) ) ); ?></p>
					<?php $le_footer_menu( 'footer_services' ); ?>
				</div>

				<div class="footer-col">
					<p class="footer-col__title"><?php echo esc_html( le_option( 'footer_col_take_action', __( 'Take Action', 'lewisedward' ) ) ); ?></p>
					<?php $le_footer_menu( 'footer_take_action' ); ?>
				</div>

			</div>
		</div>

		<div class="footer-bottom glass">
			<div class="footer-bottom__left">
				<span class="footer-bottom__copy">&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php echo esc_html( le_option( 'footer_copyright_name', __( 'Lewis Edward Ltd', 'lewisedward' ) ) ); ?></span>
				<?php $footer_privacy = le_link( 'footer_privacy_link', '/privacy', __( 'Privacy Policy', 'lewisedward' ), 'option' ); ?>
				<a class="footer-link footer-bottom__privacy" href="<?php echo esc_url( $footer_privacy['url'] ); ?>"<?php le_link_target_attr( $footer_privacy ); ?>><span class="footer-link__text"><?php echo esc_html( $footer_privacy['title'] ); ?></span><span class="footer-link__underline" aria-hidden="true"></span></a>
			</div>

			<div class="footer-bottom__right">
				<span class="footer-bottom__loc">
					<img src="<?php echo esc_url( LE_URI . '/assets/images/united-kingdom.svg' ); ?>" alt="UK" width="14" height="10" />
					<span><?php echo esc_html( le_option( 'footer_location', __( 'London', 'lewisedward' ) ) ); ?></span>
				</span>
				<span class="footer-bottom__sep" aria-hidden="true"></span>
				<time class="footer-bottom__clock" data-london-clock aria-label="<?php esc_attr_e( 'Current time in London', 'lewisedward' ); ?>">--:--</time>
				<span class="footer-bottom__sep" aria-hidden="true"></span>
				<button class="footer-top" type="button" data-scroll-top aria-label="<?php esc_attr_e( 'Back to top', 'lewisedward' ); ?>">
					<span class="footer-top__label"><?php echo esc_html( le_option( 'footer_backtotop', __( 'Back to top', 'lewisedward' ) ) ); ?></span>
					<span class="footer-top__icon" aria-hidden="true">
						<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
					</span>
				</button>
			</div>
		</div>

	</div>
</footer>
