<?php
/**
 * One-off content migrations, run once per site and remembered in an option.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Creative Agency Partner" became "Agency Partnership" at
 * /services/wordpress-agency-partnership/. The redirects and menu seed were
 * updated in code; this renames the service post and any menu items that point
 * at it, so the change does not depend on an admin edit on each environment.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_agency_partnership' ) ) {
		return;
	}
	$post = get_page_by_path( 'creative-agency-partner', OBJECT, 'service' );
	if ( ! $post ) {
		$post = get_page_by_path( 'agency-partner', OBJECT, 'service' );
	}
	if ( ! $post ) {
		$found = get_posts( array( 'post_type' => 'service', 'title' => 'Creative Agency Partner', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
		$post  = $found ? get_post( $found[0] ) : null;
	}
	if ( ! $post ) {
		update_option( 'le_migration_agency_partnership', 'nothing-to-do', false );
		return;
	}
	$old_slug = $post->post_name;
	wp_update_post( array( 'ID' => $post->ID, 'post_title' => 'Agency Partnership', 'post_name' => 'wordpress-agency-partnership' ) );

	foreach ( wp_get_nav_menus() as $menu ) {
		foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $item ) {
			if ( 'post_type' === $item->type && 'service' === $item->object && (int) $item->object_id === (int) $post->ID ) {
				if ( '' === trim( (string) get_post_field( 'post_title', $item->ID ) ) || 'Creative Agency Partner' === trim( (string) get_post_field( 'post_title', $item->ID ) ) ) {
					wp_update_post( array( 'ID' => $item->ID, 'post_title' => 'Agency Partnership' ) );
				}
			} elseif ( 'custom' === $item->type && false !== strpos( (string) $item->url, '/services/' . $old_slug ) ) {
				update_post_meta( $item->ID, '_menu_item_url', str_replace( '/services/' . $old_slug, '/services/wordpress-agency-partnership', (string) $item->url ) );
				if ( 'Creative Agency Partner' === trim( (string) $item->title ) ) {
					wp_update_post( array( 'ID' => $item->ID, 'post_title' => 'Agency Partnership' ) );
				}
			}
		}
	}
	update_option( 'le_migration_agency_partnership', gmdate( 'c' ), false );
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
}, 20 );
