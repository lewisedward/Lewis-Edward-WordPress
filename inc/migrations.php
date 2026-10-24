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
	// Held until the rename is launched on its own (after the redesign), so the two
	// changes can be measured separately. Turn on with define( 'LE_AGENCY_RENAME', true ).
	if ( ! ( defined( 'LE_AGENCY_RENAME' ) && LE_AGENCY_RENAME ) ) {
		return;
	}
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

/**
 * Copy an image shipped with the theme into the media library once, so plugin
 * settings (e.g. the Yoast organisation logo) can reference an attachment.
 *
 * @param string $rel   Path relative to the theme root.
 * @param string $title Attachment title and alt text.
 * @return int Attachment ID, 0 on failure.
 */
function le_import_theme_image( $rel, $title ) {
	$src = LE_DIR . '/' . ltrim( $rel, '/' );
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'any', 'meta_key' => '_le_theme_source', 'meta_value' => $rel, 'fields' => 'ids', 'numberposts' => 1 ) );
	if ( $existing ) {
		return (int) $existing[0];
	}
	$bits = wp_upload_bits( basename( $src ), null, file_get_contents( $src ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! empty( $bits['error'] ) ) {
		return 0;
	}
	$type = wp_check_filetype( $bits['file'] );
	$id   = wp_insert_attachment( array( 'post_mime_type' => $type['type'], 'post_title' => $title, 'post_status' => 'inherit' ), $bits['file'] );
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $bits['file'] ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	update_post_meta( $id, '_le_theme_source', $rel );
	return (int) $id;
}

/**
 * Homepage portrait alt text: the media-library alt still said "agency"; the
 * site is positioned as a London WordPress web designer & developer.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_portrait_alt' ) || ! function_exists( 'get_field' ) ) {
		return;
	}
	$front = (int) get_option( 'page_on_front' );
	$raw   = $front ? get_field( 'home_about_portrait', $front, false ) : 0;
	$id    = is_array( $raw ) ? (int) ( $raw['ID'] ?? $raw['id'] ?? 0 ) : (int) $raw;
	if ( ! $id ) {
		update_option( 'le_migration_portrait_alt', 'nothing-to-do', false );
		return;
	}
	$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
	if ( '' === trim( $alt ) || false !== stripos( $alt, 'agency' ) ) {
		update_post_meta( $id, '_wp_attachment_image_alt', 'Lewis Edward, London WordPress web designer & developer' );
	}
	update_option( 'le_migration_portrait_alt', gmdate( 'c' ), false );
}, 20 );

/**
 * Yoast "site represents": an organisation called Lewis Edward with the brand
 * mark as its logo, so Yoast's own graph carries the business entity that the
 * theme's ProfessionalService node then extends (inc/schema.php). Leaves any
 * organisation name already set in Yoast alone.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_yoast_entity' ) || ! class_exists( 'WPSEO_Options' ) || ! method_exists( 'WPSEO_Options', 'set' ) ) {
		return;
	}
	if ( '' !== trim( (string) WPSEO_Options::get( 'company_name', '' ) ) ) {
		update_option( 'le_migration_yoast_entity', 'already-set', false );
		return;
	}
	$logo_id = le_import_theme_image( 'assets/images/brand/le-mark-lime-1024.png', 'Lewis Edward logo' );
	WPSEO_Options::set( 'company_or_person', 'company' );
	WPSEO_Options::set( 'company_name', 'Lewis Edward' );
	WPSEO_Options::set( 'company_alternate_name', 'Lewis Edward — London WordPress web designer & developer' );
	if ( $logo_id ) {
		WPSEO_Options::set( 'company_logo', (string) wp_get_attachment_url( $logo_id ) );
		WPSEO_Options::set( 'company_logo_id', $logo_id );
	}
	update_option( 'le_migration_yoast_entity', gmdate( 'c' ), false );
}, 30 );

/**
 * 2026-10-23: the logo changed to "L." (Geist 500). Point Yoast's organisation
 * logo at a fresh copy of the new mark so the schema and knowledge panel use it.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_yoast_logo_ldot' ) || ! class_exists( 'WPSEO_Options' ) || ! method_exists( 'WPSEO_Options', 'set' ) ) {
		return;
	}
	$logo_id = le_import_theme_image( 'assets/images/brand/le-logo-1024.png', 'Lewis Edward logo' );
	if ( ! $logo_id ) {
		return;
	}
	WPSEO_Options::set( 'company_logo', (string) wp_get_attachment_url( $logo_id ) );
	WPSEO_Options::set( 'company_logo_id', $logo_id );
	WPSEO_Options::set( 'company_logo_meta', false );
	update_option( 'le_migration_yoast_logo_ldot', gmdate( 'c' ), false );
}, 31 );

/**
 * One-off: remove duplicate ACF field definitions. The site move left some
 * fields in the database twice under the same key (two "Clients: industries
 * list" repeaters, two journal post pickers…), and ACF reads whichever comes
 * first, so new sub-fields never showed in the editor. Keep the newest copy of
 * each key, delete the rest with their sub-fields, then let the JSON sync below
 * re-import every group from the theme. Saved values are untouched.
 */
function le_acf_delete_field_tree( $id ) {
	$n    = 0;
	$kids = get_posts( array( 'post_type' => 'acf-field', 'post_parent' => $id, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );
	foreach ( $kids as $kid ) {
		$n += le_acf_delete_field_tree( (int) $kid );
	}
	wp_delete_post( $id, true );
	return $n + 1;
}
add_action( 'acf/init', function () {
	if ( get_option( 'le_migration_acf_dedupe' ) ) {
		return;
	}
	global $wpdb;
	$keys    = $wpdb->get_col( "SELECT post_name FROM {$wpdb->posts} WHERE post_type = 'acf-field' GROUP BY post_name HAVING COUNT(*) > 1" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$deleted = 0;
	foreach ( $keys as $key ) {
		$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'acf-field' AND post_name = %s ORDER BY ID DESC", $key ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		array_shift( $ids );
		foreach ( $ids as $id ) {
			if ( get_post( (int) $id ) ) {
				$deleted += le_acf_delete_field_tree( (int) $id );
			}
		}
	}
	if ( function_exists( 'acf_get_store' ) ) {
		foreach ( array( 'fields', 'field-groups' ) as $store ) {
			$st = acf_get_store( $store );
			if ( $st && method_exists( $st, 'reset' ) ) {
				$st->reset();
			}
		}
	}
	delete_option( 'le_acf_json_hashes' );
	delete_option( 'le_acf_json_sync_sig' );
	update_option( 'le_migration_acf_dedupe', gmdate( 'c' ) . ' keys:' . count( $keys ) . ' deleted:' . $deleted, false );
}, 15 );

/**
 * ACF local JSON sync without the admin button. The JSON in the theme is the
 * source of truth: whenever a group's JSON content (ignoring its "modified"
 * stamp) differs from what was last imported, import it. Comparing content
 * rather than timestamps means a database copy saved more recently (a site
 * migration, a server clock ahead of the repo) can't block a deployed change.
 */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_get_local_json_files' ) || ! function_exists( 'acf_import_field_group' ) || ! function_exists( 'acf_get_field_group_post' ) ) {
		return;
	}
	$files = acf_get_local_json_files();
	if ( ! is_array( $files ) || ! $files ) {
		return;
	}
	$sig = md5( wp_json_encode( array_map( 'filemtime', $files ) ) );
	if ( get_option( 'le_acf_json_sync_sig' ) === $sig ) {
		return;
	}
	$hashes = get_option( 'le_acf_json_hashes', array() );
	$hashes = is_array( $hashes ) ? $hashes : array();
	$hash   = function ( $file ) {
		$j = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! is_array( $j ) ) {
			return '';
		}
		unset( $j['modified'], $j['ID'] );
		return md5( wp_json_encode( $j ) );
	};
	foreach ( $files as $key => $file ) {
		$json = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! is_array( $json ) || empty( $json['key'] ) ) {
			continue;
		}
		$post = acf_get_field_group_post( $json['key'] );
		$h    = $hash( $file );
		if ( $post && isset( $hashes[ $json['key'] ] ) && $hashes[ $json['key'] ] === $h ) {
			continue;
		}
		if ( $post ) {
			$json['ID'] = $post->ID;
		}
		acf_import_field_group( $json );
		clearstatcache( true, $file );
		$hashes[ $json['key'] ] = $hash( $file ); // ACF rewrites the file from the database on import.
	}
	update_option( 'le_acf_json_hashes', $hashes, false );
	clearstatcache();
	update_option( 'le_acf_json_sync_sig', md5( wp_json_encode( array_map( 'filemtime', acf_get_local_json_files() ) ) ), false );
}, 20 );

/**
 * One-off: company logos for the testimonials that had none (The Physios, Tide
 * Insurance, Zena's Kitchen). Downloads each client's own header SVG into the
 * Media Library (sanitised like a normal SVG upload) and sets the testimonial's
 * "Company logo" field. Runs for an admin in wp-admin, so SVG uploads are allowed.
 * The grid skin shows every logo white, so the source colours don't matter.
 */
add_action( 'admin_init', function () {
	if ( get_option( 'le_migration_testimonial_logos' ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$logos = array(
		'the-physios'    => array( 'https://thephysios.com/wp-content/uploads/2020/11/white.svg', 'the-physios-logo.svg', 'The Physios logo' ),
		'tide-insurance' => array( 'https://tideinsurance.com/wp-content/uploads/2024/09/logo-new-white.svg', 'tide-insurance-logo.svg', 'Tide Insurance logo' ),
		'zenas-kitchen'  => array( 'https://zenaskitchen.com/wp-content/uploads/2022/01/Logo-Open-Sans.svg', 'zenas-kitchen-logo.svg', "Zena's Kitchen logo" ),
	);
	$log = array();
	foreach ( get_posts( array( 'post_type' => 'testimonial', 'post_status' => 'any', 'numberposts' => -1 ) ) as $t ) {
		$slug = sanitize_title( (string) get_field( 'testimonial_company', $t->ID ) );
		if ( ! isset( $logos[ $slug ] ) ) {
			continue;
		}
		$cur = get_field( 'testimonial_logo', $t->ID );
		if ( ! empty( $cur ) ) {
			$log[] = $slug . ':kept';
			continue;
		}
		list( $url, $name, $alt ) = $logos[ $slug ];
		$tmp = download_url( $url, 20 );
		if ( is_wp_error( $tmp ) ) {
			$log[] = $slug . ':download-failed';
			continue;
		}
		$raw   = (string) file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$clean = function_exists( 'le_sanitize_svg_string' ) ? le_sanitize_svg_string( $raw ) : '';
		if ( ! $clean || false === stripos( $clean, '<svg' ) ) {
			wp_delete_file( $tmp );
			$log[] = $slug . ':not-svg';
			continue;
		}
		file_put_contents( $tmp, $clean ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$id = media_handle_sideload( array( 'name' => $name, 'tmp_name' => $tmp ), 0, $alt );
		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );
			$log[] = $slug . ':' . $id->get_error_code();
			continue;
		}
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		update_field( 'field_ts_logo', $id, $t->ID );
		$log[] = $slug . ':' . $id;
	}
	update_option( 'le_migration_testimonial_logos', gmdate( 'c' ) . ' ' . implode( ' ', $log ), false );
} );

/**
 * 2026-10-24 SEO audit follow-ups.
 *
 * 1. Yoast's cached service sitemap still listed /services/creative-agency-partner/
 *    (now a 301) and omitted /services/wordpress-agency-partnership/. Clearing the
 *    sitemap cache makes Yoast rebuild every sub-sitemap from current slugs.
 * 2. Two service titles retargeted at terms people search for: "WordPress SEO
 *    agency" (the easiest term in the agency cluster) and "white-label WordPress
 *    developer for creative agencies" (the partnership positioning). Only set
 *    where the post exists; Yoast's %%sitename%% suffix is kept via the template.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_seo_audit_2026_10' ) ) {
		return;
	}
	$log = array();

	if ( class_exists( 'WPSEO_Sitemaps_Cache' ) && method_exists( 'WPSEO_Sitemaps_Cache', 'clear' ) ) {
		WPSEO_Sitemaps_Cache::clear();
		$log[] = 'sitemap-cache-cleared';
	}

	$titles = array(
		'seo'                          => 'WordPress SEO Agency London | %%sitename%%',
		'wordpress-agency-partnership' => 'White-Label WordPress Developer for Agencies | %%sitename%%',
	);
	foreach ( $titles as $slug => $title ) {
		$post = get_page_by_path( $slug, OBJECT, 'service' );
		if ( ! $post ) {
			$log[] = $slug . ':missing';
			continue;
		}
		update_post_meta( $post->ID, '_yoast_wpseo_title', $title );
		$log[] = $slug . ':' . $post->ID;
	}

	update_option( 'le_migration_seo_audit_2026_10', gmdate( 'c' ) . ' ' . implode( ' ', $log ), false );
}, 20 );
