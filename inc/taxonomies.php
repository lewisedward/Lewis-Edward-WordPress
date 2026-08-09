<?php
/**
 * Custom taxonomies.
 *
 * Categories for Work and Journal so archives can be filtered, mirroring the
 * category/tag filtering present on the React site.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper to register a hierarchical or flat taxonomy.
 *
 * @param string       $key           Taxonomy key.
 * @param array|string $object_types  CPT keys.
 * @param string       $singular      Singular label.
 * @param string       $plural        Plural label.
 * @param array        $args          Overrides.
 */
function le_register_tax( $key, $object_types, $singular, $plural, $args = array() ) {
	$labels = array(
		'name'          => $plural,
		'singular_name' => $singular,
		'search_items'  => sprintf( __( 'Search %s', 'lewisedward' ), $plural ),
		'all_items'     => sprintf( __( 'All %s', 'lewisedward' ), $plural ),
		'edit_item'     => sprintf( __( 'Edit %s', 'lewisedward' ), $singular ),
		'update_item'   => sprintf( __( 'Update %s', 'lewisedward' ), $singular ),
		'add_new_item'  => sprintf( __( 'Add New %s', 'lewisedward' ), $singular ),
		'new_item_name' => sprintf( __( 'New %s Name', 'lewisedward' ), $singular ),
		'menu_name'     => $plural,
	);

	$defaults = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => $key, 'with_front' => false ),
	);

	register_taxonomy( $key, $object_types, wp_parse_args( $args, $defaults ) );
}

/**
 * Register theme taxonomies.
 */
function le_register_taxonomies() {

	// Work categories (e.g. web, branding, WordPress).
	le_register_tax( 'work_category', array( 'work' ), __( 'Category', 'lewisedward' ), __( 'Work Categories', 'lewisedward' ), array(
		'rewrite' => array( 'slug' => 'work-category', 'with_front' => false ),
	) );

	// Work tags (technologies / capabilities) — flat.
	le_register_tax( 'work_tag', array( 'work' ), __( 'Tag', 'lewisedward' ), __( 'Work Tags', 'lewisedward' ), array(
		'hierarchical' => false,
		'rewrite'      => array( 'slug' => 'work-tag', 'with_front' => false ),
	) );

	// Journal uses the built-in "category" taxonomy (Posts relabelled to Journal).

	// Service categories (grouping of the 18 services).
	le_register_tax( 'service_category', array( 'service' ), __( 'Category', 'lewisedward' ), __( 'Service Categories', 'lewisedward' ), array(
		'rewrite' => array( 'slug' => 'service-category', 'with_front' => false ),
	) );
}
add_action( 'init', 'le_register_taxonomies' );
