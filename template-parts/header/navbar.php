<?php
/**
 * Navbar — floating glass pill (desktop) + compact pill & drawer (mobile).
 *
 * Faithful port of the React Navbar. The nav links come from the standard WP
 * menu (location "Primary") rendered with LE_Mega_Walker, which turns any
 * parent-with-children into the mega dropdown (as with Services). Logo, status
 * widget and CTA are theme chrome, editable via Theme Options.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Logo: Theme Options image if set, otherwise the bundled brand logo.
$le_logo     = le_option( 'site_logo' );
$le_logo_url = ( is_array( $le_logo ) && ! empty( $le_logo['url'] ) ) ? $le_logo['url'] : LE_URI . '/assets/images/lewis-edward-logo.webp';
$le_logo_alt = get_bloginfo( 'name' ) . ' — London WordPress & AI developers logo';

// CTA (Theme Options -> Header). Single ACF Link field, current default kept
// when empty. $le_cta_url / $le_cta_label are derived for the markup below.
$le_cta       = le_link( 'header_cta', '/quotes', __( 'Get a Quote', 'lewisedward' ), 'option' );
$le_cta_url   = $le_cta['url'];
$le_cta_label = $le_cta['title'];

// Availability flag for the status widget.
$le_available = le_option( 'header_available', true );

$le_menu_args = array(
	'theme_location' => 'primary',
	'container'      => false,
	'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
	'menu_class'     => 'primary-menu',
	'fallback_cb'    => 'le_primary_menu_fallback',
	'walker'         => new LE_Mega_Walker(),
	'depth'          => 2,
);
?>
<header class="site-header" data-header role="banner">
	<div class="site-header__container">

		<?php /* ---------- Desktop pill ---------- */ ?>
		<nav class="navbar navbar--desktop glass" aria-label="<?php esc_attr_e( 'Primary', 'lewisedward' ); ?>">

			<a class="navbar__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Lewis Edward — home', 'lewisedward' ); ?>">
				<img src="<?php echo esc_url( $le_logo_url ); ?>" alt="<?php echo esc_attr( $le_logo_alt ); ?>" width="95" height="29" />
			</a>

			<span class="navbar__divider" aria-hidden="true"></span>

			<div class="navbar__links">
				<?php wp_nav_menu( $le_menu_args ); ?>
			</div>

			<span class="navbar__divider navbar__divider--push" aria-hidden="true"></span>

			<?php if ( $le_available ) : ?>
			<div class="status">
				<span class="status__group">
					<span class="status__pulse" aria-hidden="true"><span class="status__ping"></span><span class="status__dot"></span></span>
					<span class="status__available"><?php echo esc_html( le_option( 'header_available_label', __( 'Available', 'lewisedward' ) ) ); ?></span>
				</span>
				<span class="status__sep" aria-hidden="true"></span>
				<span class="status__group status__group--loc">
					<img class="status__flag" src="<?php echo esc_url( LE_URI . '/assets/images/united-kingdom.svg' ); ?>" alt="UK" width="14" height="10" />
					<span class="status__city"><?php echo esc_html( le_option( 'header_status_city', __( 'London', 'lewisedward' ) ) ); ?></span>
					<span class="status__sep" aria-hidden="true"></span>
					<time class="status__clock" data-london-clock aria-label="<?php esc_attr_e( 'Current time in London', 'lewisedward' ); ?>">--:--</time>
				</span>
			</div>
			<?php endif; ?>

			<a class="btn-cta" href="<?php echo esc_url( $le_cta_url ); ?>"<?php le_link_target_attr( $le_cta ); ?>>
				<?php echo esc_html( $le_cta_label ); ?><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</nav>

		<?php /* ---------- Mobile pill ---------- */ ?>
		<nav class="navbar navbar--mobile glass" aria-label="<?php esc_attr_e( 'Primary', 'lewisedward' ); ?>">
			<a class="navbar__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Lewis Edward — home', 'lewisedward' ); ?>">
				<img src="<?php echo esc_url( $le_logo_url ); ?>" alt="<?php echo esc_attr( $le_logo_alt ); ?>" width="95" height="29" />
			</a>
			<button class="nav-burger" type="button" data-nav-toggle aria-expanded="false" aria-controls="mobile-drawer" aria-label="<?php esc_attr_e( 'Toggle menu', 'lewisedward' ); ?>">
				<span class="nav-burger__lines"><span></span><span></span><span></span></span>
			</button>
		</nav>

	</div>

	<?php /* ---------- Mobile drawer ---------- */ ?>
	<div id="mobile-drawer" class="mobile-drawer glass" data-mobile-nav hidden>
		<div class="mobile-drawer__inner">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'menu_class'     => 'primary-menu primary-menu--mobile',
				'fallback_cb'    => 'le_primary_menu_fallback',
				'walker'         => new LE_Mega_Walker(),
				'depth'          => 2,
			) );
			?>
			<span class="mobile-drawer__sep" aria-hidden="true"></span>
			<a class="btn-cta btn-cta--mobile" href="<?php echo esc_url( $le_cta_url ); ?>"<?php le_link_target_attr( $le_cta ); ?>>
				<?php echo esc_html( $le_cta_label ); ?><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>
	</div>
</header>
<?php
/**
 * Fallback if no menu is assigned to the "primary" location yet.
 * Renders the core pages so the header is never empty.
 */
if ( ! function_exists( 'le_primary_menu_fallback' ) ) :
function le_primary_menu_fallback() {
	$items = array(
		array( 'Services', home_url( '/services' ) ),
		array( 'Work', home_url( '/work' ) ),
		array( 'About', home_url( '/about' ) ),
		array( 'Journal', home_url( '/journal' ) ),
		array( 'Contact', home_url( '/contact' ) ),
	);
	echo '<ul class="primary-menu">';
	foreach ( $items as $it ) {
		printf(
			'<li class="menu-item nav-item"><a class="nav-link" href="%s">%s</a></li>',
			esc_url( $it[1] ),
			esc_html( $it[0] )
		);
	}
	echo '</ul>';
}
endif;
