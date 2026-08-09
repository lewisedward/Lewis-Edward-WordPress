<?php
/**
 * One-time seeding of the Primary menu.
 *
 * So the header renders immediately after activation, this creates a "Primary
 * Navigation" menu with Services (+ its six children, each carrying a
 * description for the mega dropdown) and the top-level links, then assigns it
 * to the "primary" location. Runs once, guarded by an option, and never
 * overwrites an existing assignment or menu content.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The default menu blueprint (mirrors the React nav).
 *
 * @return array
 */
function le_default_menu_blueprint() {
	return array(
		'parent' => array(
			'title' => 'Services',
			'url'   => home_url( '/services' ),
		),
		'children' => array(
			array( 'Web Design', '/services/web-design', 'Enterprise-level WordPress design with creativity and precision.' ),
			array( 'Web Development', '/services/web-development', 'Custom WordPress websites built from scratch.' ),
			array( 'Website Support', '/services/website-support', 'Proactive and reliable ongoing website maintenance.' ),
			array( 'E-commerce', '/services/ecommerce', 'WordPress shops, bookings and payment gateways.' ),
			array( 'Creative Agency Partner', '/services/agency-web-developer', 'Development partner for agencies and designers.' ),
			array( 'AI Development', '/services/ai-development', 'AI-powered websites, web apps and tools by top 5% AI developers.' ),
		),
		'top_level' => array(
			array( 'Work', '/work' ),
			array( 'About', '/about' ),
			array( 'Journal', '/journal' ),
			array( 'Contact', '/contact' ),
		),
	);
}

/**
 * Create and assign the Primary menu if none is set.
 */
function le_seed_primary_menu() {
	// Menu functions live in admin includes.
	if ( ! function_exists( 'wp_update_nav_menu_item' ) ) {
		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
	}
	if ( ! function_exists( 'wp_update_nav_menu_item' ) ) {
		return; // Still unavailable — bail safely.
	}

	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['primary'] ) && is_nav_menu( $locations['primary'] ) ) {
		return; // Something is already assigned — respect it.
	}

	$menu_name = 'Primary Navigation';
	$menu_obj  = wp_get_nav_menu_object( $menu_name );

	if ( $menu_obj ) {
		$menu_id = (int) $menu_obj->term_id;
	} else {
		$menu_id = wp_create_nav_menu( $menu_name );
	}
	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	// Only populate if the menu is empty.
	$existing = wp_get_nav_menu_items( $menu_id );
	if ( empty( $existing ) ) {
		$bp = le_default_menu_blueprint();

		$services_id = wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'  => $bp['parent']['title'],
			'menu-item-url'    => $bp['parent']['url'],
			'menu-item-status' => 'publish',
			'menu-item-type'   => 'custom',
		) );

		if ( ! is_wp_error( $services_id ) ) {
			foreach ( $bp['children'] as $child ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'       => $child[0],
					'menu-item-url'         => home_url( $child[1] ),
					'menu-item-description' => $child[2],
					'menu-item-parent-id'   => $services_id,
					'menu-item-status'      => 'publish',
					'menu-item-type'        => 'custom',
				) );
			}
		}

		foreach ( $bp['top_level'] as $link ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'  => $link[0],
				'menu-item-url'    => home_url( $link[1] ),
				'menu-item-status' => 'publish',
				'menu-item-type'   => 'custom',
			) );
		}
	}

	// Assign to the primary location.
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Seed on activation.
 */
add_action( 'after_switch_theme', 'le_seed_primary_menu' );

/**
 * Also seed once for an already-active install (guarded by an option), so the
 * header populates without needing a re-activation.
 */
function le_maybe_seed_primary_menu() {
	if ( get_option( 'le_menu_seeded' ) ) {
		return;
	}
	le_seed_primary_menu();
	update_option( 'le_menu_seeded', 1 );
}
add_action( 'admin_init', 'le_maybe_seed_primary_menu' );

/**
 * Footer menu blueprints, keyed by location.
 *
 * @return array
 */
function le_footer_menu_blueprints() {
	return array(
		'footer_discover' => array(
			'name'  => 'Footer — Discover',
			'items' => array(
				array( 'title' => 'About',    'url' => home_url( '/about' ) ),
				array( 'title' => 'Work',     'url' => home_url( '/work' ) ),
				array( 'title' => 'Journal',  'url' => home_url( '/journal' ) ),
				array( 'title' => 'FAQs',     'url' => home_url( '/faq' ) ),
				array( 'title' => 'Sitetally', 'url' => 'https://sitetally.com', 'target' => '_blank' ),
			),
		),
		'footer_services' => array(
			'name'  => 'Footer — Services',
			'items' => array(
				array( 'title' => 'Web Design',              'url' => home_url( '/services/web-design' ) ),
				array( 'title' => 'Web Development',         'url' => home_url( '/services/web-development' ) ),
				array( 'title' => 'Website Support',         'url' => home_url( '/services/website-support' ) ),
				array( 'title' => 'E-commerce',              'url' => home_url( '/services/ecommerce' ) ),
				array( 'title' => 'Creative Agency Partner', 'url' => home_url( '/services/agency-web-developer' ) ),
				array( 'title' => 'AI Development',          'url' => home_url( '/services/ai-development' ) ),
			),
		),
		'footer_take_action' => array(
			'name'  => 'Footer — Take Action',
			'items' => array(
				array( 'title' => 'Start a conversation', 'url' => home_url( '/contact' ) ),
				array( 'title' => 'Request a quote',      'url' => home_url( '/quotes' ) ),
			),
		),
	);
}

/**
 * Create + assign the three footer menus if their locations are empty.
 */
function le_seed_footer_menus() {
	if ( ! function_exists( 'wp_update_nav_menu_item' ) ) {
		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
	}
	if ( ! function_exists( 'wp_update_nav_menu_item' ) ) {
		return;
	}

	$locations = get_nav_menu_locations();

	foreach ( le_footer_menu_blueprints() as $location => $bp ) {
		if ( ! empty( $locations[ $location ] ) && is_nav_menu( $locations[ $location ] ) ) {
			continue; // Respect an existing assignment.
		}

		$menu_obj = wp_get_nav_menu_object( $bp['name'] );
		$menu_id  = $menu_obj ? (int) $menu_obj->term_id : wp_create_nav_menu( $bp['name'] );
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}

		$existing = wp_get_nav_menu_items( $menu_id );
		if ( empty( $existing ) ) {
			foreach ( $bp['items'] as $item ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'  => $item['title'],
					'menu-item-url'    => $item['url'],
					'menu-item-status' => 'publish',
					'menu-item-type'   => 'custom',
					'menu-item-target' => isset( $item['target'] ) ? $item['target'] : '',
				) );
			}
		}

		$locations[ $location ] = $menu_id;
	}

	set_theme_mod( 'nav_menu_locations', $locations );
}
add_action( 'after_switch_theme', 'le_seed_footer_menus' );

/**
 * Seed footer menus once on an already-active install.
 */
function le_maybe_seed_footer_menus() {
	if ( get_option( 'le_footer_menus_seeded' ) ) {
		return;
	}
	le_seed_footer_menus();
	update_option( 'le_footer_menus_seeded', 1 );
}
add_action( 'admin_init', 'le_maybe_seed_footer_menus' );
