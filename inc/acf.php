<?php
/**
 * ACF integration.
 *
 * - Points ACF's local JSON save & load at /acf-json so every field group is
 *   version-controlled and importable. This is how new pages get wired: build
 *   template -> create field group -> it is written here as JSON -> commit.
 * - Registers a global Theme Options page (header/footer/CTA/contact details).
 * - Fails gracefully if ACF Pro is not active.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save ACF field groups as JSON into the theme's acf-json folder.
 *
 * @param string $path Default save path.
 * @return string
 */
function le_acf_json_save_point( $path ) {
	return LE_DIR . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'le_acf_json_save_point' );

/**
 * Load ACF field groups from the theme's acf-json folder.
 *
 * @param array $paths Existing load paths.
 * @return array
 */
function le_acf_json_load_point( $paths ) {
	// Replace the default so we only load our version-controlled groups here,
	// while still allowing other plugins to add their own paths.
	$paths[] = LE_DIR . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'le_acf_json_load_point' );

/**
 * Register a global Theme Options page for site-wide content.
 */
function le_acf_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page( array(
		'page_title' => __( 'Theme Options', 'lewisedward' ),
		'menu_title' => __( 'Theme Options', 'lewisedward' ),
		'menu_slug'  => 'le-theme-options',
		'capability' => 'edit_theme_options',
		'position'   => 59,
		'icon_url'   => 'dashicons-admin-customizer',
		'redirect'   => true,
	) );

	// Sub pages keep the global content organised.
	foreach ( array(
		'Header'   => 'le-options-header',
		'Footer'   => 'le-options-footer',
		'Global CTA' => 'le-options-cta',
		'Contact Details' => 'le-options-contact',
		'Homepage' => 'le-options-home',
	) as $title => $slug ) {
		acf_add_options_sub_page( array(
			'page_title'  => sprintf( __( '%s', 'lewisedward' ), $title ),
			'menu_title'  => $title,
			'parent_slug' => 'le-theme-options',
			'menu_slug'   => $slug,
			'capability'  => 'edit_theme_options',
		) );
	}
}
add_action( 'acf/init', 'le_acf_options_page' );

/**
 * Admin notice if ACF Pro is missing — this theme depends on it.
 */
function le_acf_dependency_notice() {
	if ( function_exists( 'acf' ) ) {
		return;
	}
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p>';
	echo esc_html__( 'Lewis Edward theme: ACF Pro is required for custom fields and Theme Options. Please install and activate it.', 'lewisedward' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'le_acf_dependency_notice' );

/**
 * Convenience wrapper so templates can call le_field() whether or not ACF is
 * active (returns '' instead of fatal-erroring during early setup).
 *
 * @param string     $selector Field name.
 * @param mixed      $post_id  Post ID / 'option'.
 * @param mixed      $default  Fallback value.
 * @return mixed
 */
function le_field( $selector, $post_id = false, $default = '' ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $selector, $post_id );
		return ( null === $value || false === $value || '' === $value ) ? $default : $value;
	}
	return $default;
}

/**
 * Same as le_field() but for options-page values.
 *
 * @param string $selector Field name.
 * @param mixed  $default  Fallback.
 * @return mixed
 */
function le_option( $selector, $default = '' ) {
	return le_field( $selector, 'option', $default );
}

/**
 * Null-safe wrapper for an ACF Link field.
 *
 * Returns a normalised array of url/title/target. When the field is empty the
 * supplied defaults are used, so the current hard-coded link keeps working
 * until the client fills the field on the dashboard. A site-relative default
 * (e.g. "/contact") is resolved against the site root; absolute URLs picked in
 * the ACF link picker are left untouched.
 *
 * @param string $selector      Field name.
 * @param string $default_url   Fallback URL (absolute, or "/path" for internal).
 * @param string $default_title Fallback link text.
 * @param mixed  $post_id       Post ID / 'option' / false for current post.
 * @return array{url:string,title:string,target:string}
 */
function le_link( $selector, $default_url = '', $default_title = '', $post_id = false ) {
	$link   = function_exists( 'get_field' ) ? get_field( $selector, $post_id ) : null;
	$url    = ( is_array( $link ) && ! empty( $link['url'] ) ) ? $link['url'] : $default_url;
	$title  = ( is_array( $link ) && ! empty( $link['title'] ) ) ? $link['title'] : $default_title;
	$target = ( is_array( $link ) && ! empty( $link['target'] ) ) ? $link['target'] : '';

	// Resolve a site-relative default ("/contact") against the site root. An
	// absolute URL (http(s)://, mailto:, tel:, #anchor) is returned as-is.
	if ( '' !== $url && '/' === $url[0] ) {
		$url = home_url( $url );
	}

	return array(
		'url'    => $url,
		'title'  => $title,
		'target' => $target,
	);
}

/**
 * Echo the target/rel attributes for a le_link() result.
 *
 * @param array $link Result from le_link().
 */
function le_link_target_attr( $link ) {
	if ( ! empty( $link['target'] ) ) {
		echo ' target="' . esc_attr( $link['target'] ) . '" rel="noopener noreferrer"';
	}
}
