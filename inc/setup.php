<?php
/**
 * Theme setup — supports, menus, image sizes.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme feature support.
 */
function le_setup() {
	load_theme_textdomain( 'lewisedward', LE_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	// Menus mirror the React site's navigation surfaces.
	register_nav_menus( array(
		'primary'             => __( 'Primary Navigation', 'lewisedward' ),
		'footer_discover'     => __( 'Footer — Discover', 'lewisedward' ),
		'footer_services'     => __( 'Footer — Services', 'lewisedward' ),
		'footer_take_action'  => __( 'Footer — Take Action', 'lewisedward' ),
	) );

	// Image sizes used by cards and hero art.
	add_image_size( 'le_card', 800, 600, true );
	add_image_size( 'le_card_wide', 1200, 800, true );
	add_image_size( 'le_hero', 2000, 1200, true );
	add_image_size( 'le_thumb', 480, 360, true );
	add_image_size( 'le_portrait', 600, 800, array( 'center', 'top' ) ); // 3:4 — team cards, crop from top (keeps faces)
	add_image_size( 'le_avatar', 240, 240, array( 'center', 'top' ) );   // 1:1 — round avatars, crop from top
}
add_action( 'after_setup_theme', 'le_setup' );

/**
 * Content width for embeds.
 */
function le_content_width() {
	$GLOBALS['content_width'] = 1600;
}
add_action( 'after_setup_theme', 'le_content_width', 0 );

/**
 * Register widget areas (footer columns), kept minimal.
 */
function le_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Footer', 'lewisedward' ),
		'id'            => 'footer-1',
		'description'   => __( 'Optional footer widget area.', 'lewisedward' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
}
add_action( 'widgets_init', 'le_widgets_init' );
