<?php
/**
 * Custom post types.
 *
 * Content types that back the React site's data files become editable CPTs.
 * Public-facing, URL-bearing types (Service, Work, Journal) mirror the
 * original route slugs. Supporting types (Testimonial, Team, FAQ) are managed
 * in admin and surfaced via ACF relationship/query on templates.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Small factory to reduce boilerplate when registering a CPT.
 *
 * @param string $key      Post type key (max 20 chars).
 * @param string $singular Singular label.
 * @param string $plural   Plural label.
 * @param array  $args     Overrides merged over sane defaults.
 */
function le_register_cpt( $key, $singular, $plural, $args = array() ) {
	$labels = array(
		'name'                  => $plural,
		'singular_name'         => $singular,
		'menu_name'             => $plural,
		'add_new'               => __( 'Add New', 'lewisedward' ),
		/* translators: %s: singular post type name. */
		'add_new_item'          => sprintf( __( 'Add New %s', 'lewisedward' ), $singular ),
		'edit_item'             => sprintf( __( 'Edit %s', 'lewisedward' ), $singular ),
		'new_item'              => sprintf( __( 'New %s', 'lewisedward' ), $singular ),
		'view_item'             => sprintf( __( 'View %s', 'lewisedward' ), $singular ),
		'view_items'            => sprintf( __( 'View %s', 'lewisedward' ), $plural ),
		'search_items'          => sprintf( __( 'Search %s', 'lewisedward' ), $plural ),
		'not_found'             => __( 'None found.', 'lewisedward' ),
		'not_found_in_trash'    => __( 'None found in Trash.', 'lewisedward' ),
		'all_items'             => sprintf( __( 'All %s', 'lewisedward' ), $plural ),
		'featured_image'        => __( 'Featured Image', 'lewisedward' ),
		'set_featured_image'    => __( 'Set featured image', 'lewisedward' ),
		'remove_featured_image' => __( 'Remove featured image', 'lewisedward' ),
	);

	$defaults = array(
		'labels'             => $labels,
		'public'             => true,
		'show_in_rest'       => true, // Gutenberg + REST for ACF headless-ish use.
		'has_archive'        => true,
		'menu_position'      => 20,
		'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => $key, 'with_front' => false ),
	);

	register_post_type( $key, wp_parse_args( $args, $defaults ) );
}

/**
 * Register all theme CPTs.
 */
function le_register_post_types() {

	// Services — single pages at /services/{slug}. The /services listing is a
	// static Page (Services template), so the CPT archive is disabled to avoid
	// a URL clash.
	le_register_cpt( 'service', __( 'Service', 'lewisedward' ), __( 'Services', 'lewisedward' ), array(
		'menu_icon'   => 'dashicons-screenoptions',
		'has_archive' => false,
		'rewrite'     => array( 'slug' => 'services', 'with_front' => false ),
	) );

	// Work / portfolio — single case studies at /work/{slug}. The /work listing
	// is a static Page (Work template), so the CPT archive is disabled.
	le_register_cpt( 'work', __( 'Project', 'lewisedward' ), __( 'Work', 'lewisedward' ), array(
		'menu_icon'   => 'dashicons-portfolio',
		'has_archive' => false,
		'rewrite'     => array( 'slug' => 'work', 'with_front' => false ),
	) );

	// Journal uses the built-in Posts type (relabelled to "Journal" — see
	// le_relabel_posts_as_journal). Its listing is a static Page (Journal
	// template) and single entries use single.php.

	// Testimonials — no public archive; embedded via templates/ACF.
	le_register_cpt( 'testimonial', __( 'Testimonial', 'lewisedward' ), __( 'Testimonials', 'lewisedward' ), array(
		'menu_icon'    => 'dashicons-format-quote',
		'public'       => false,
		'show_ui'      => true,
		'show_in_rest' => true,
		'has_archive'  => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'rewrite'      => false,
	) );

	// Team members — no public archive.
	le_register_cpt( 'team', __( 'Team Member', 'lewisedward' ), __( 'Team', 'lewisedward' ), array(
		'menu_icon'    => 'dashicons-groups',
		'public'       => false,
		'show_ui'      => true,
		'show_in_rest' => true,
		'has_archive'  => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'rewrite'      => false,
	) );

	// FAQ entries live in a question/answer Repeater on the FAQ page (no CPT).
}
add_action( 'init', 'le_register_post_types' );

/**
 * Relabel the built-in Posts type as "Journal" throughout wp-admin.
 *
 * The blog is a Journal, so the default Posts UI is renamed (menu, buttons,
 * labels) without the overhead of a custom post type — keeping native
 * categories, tags, scheduling, RSS and the standard editor.
 */
function le_relabel_posts_as_journal() {
	$obj = get_post_type_object( 'post' );
	if ( ! $obj ) {
		return;
	}
	$l = $obj->labels;
	$l->name               = __( 'Journal', 'lewisedward' );
	$l->singular_name      = __( 'Journal Entry', 'lewisedward' );
	$l->menu_name          = __( 'Journal', 'lewisedward' );
	$l->name_admin_bar     = __( 'Journal Entry', 'lewisedward' );
	$l->add_new            = __( 'Add New', 'lewisedward' );
	$l->add_new_item       = __( 'Add New Entry', 'lewisedward' );
	$l->edit_item          = __( 'Edit Entry', 'lewisedward' );
	$l->new_item           = __( 'New Entry', 'lewisedward' );
	$l->view_item          = __( 'View Entry', 'lewisedward' );
	$l->view_items         = __( 'View Journal', 'lewisedward' );
	$l->search_items       = __( 'Search Journal', 'lewisedward' );
	$l->not_found          = __( 'No entries found.', 'lewisedward' );
	$l->not_found_in_trash = __( 'No entries found in Trash.', 'lewisedward' );
	$l->all_items          = __( 'All Entries', 'lewisedward' );
	$obj->menu_icon        = 'dashicons-edit-large';
}
add_action( 'init', 'le_relabel_posts_as_journal', 20 );
