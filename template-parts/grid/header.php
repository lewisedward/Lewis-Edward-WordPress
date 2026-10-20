<?php
/**
 * Grid skin — header bar.
 *
 * A full-width hairline bar divided into cells: wordmark, London clock, the
 * primary menu (flat, no mega panel), and the quote CTA as a lime cell.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$le_cta       = le_link( 'header_cta', '/quotes', __( 'Get a Quote', 'lewisedward' ), 'option' );
$le_available = le_option( 'header_available', true );
?>
<header class="g-header" data-header role="banner">
	<a class="g-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Lewis Edward — home', 'lewisedward' ); ?>"><svg class="g-header__mark" viewBox="0 0 64 64" aria-hidden="true" focusable="false"><path d="M12 8h12v32h28v12H12z" fill="currentColor"/><g class="g-header__mark-ring"><circle cx="46" cy="22" r="7" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M46 9v6M46 29v6M33 22h6M53 22h6" stroke="currentColor" stroke-width="1.5"/><circle cx="46" cy="22" r="1.5" fill="currentColor"/></g></svg></a>
	<div class="g-header__meta g-mono">
		<?php if ( $le_available ) : ?><span class="g-header__dot" aria-hidden="true"></span><span><?php echo esc_html( le_option( 'header_available_label', __( 'Available', 'lewisedward' ) ) ); ?></span><span class="g-header__sep" aria-hidden="true">/</span><?php endif; ?>
		<time data-london-clock aria-label="<?php esc_attr_e( 'Current time in London', 'lewisedward' ); ?>">--:--</time> <span>LDN</span>
	</div>
	<nav class="g-header__nav" aria-label="<?php esc_attr_e( 'Primary', 'lewisedward' ); ?>">
		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'items_wrap'     => '<ul class="g-header__menu">%3$s</ul>',
			'depth'          => 1,
			'fallback_cb'    => 'le_primary_menu_fallback',
		) );
		?>
	</nav>
	<a class="g-header__cta" href="<?php echo esc_url( $le_cta['url'] ); ?>"<?php le_link_target_attr( $le_cta ); ?>><span><?php echo esc_html( $le_cta['title'] ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
</header>
