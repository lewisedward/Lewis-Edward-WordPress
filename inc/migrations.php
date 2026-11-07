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
	wp_update_post( array( 'ID' => $post->ID, 'post_title' => 'Agency partner', 'post_name' => 'wordpress-agency-partnership' ) );

	foreach ( wp_get_nav_menus() as $menu ) {
		foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $item ) {
			if ( 'post_type' === $item->type && 'service' === $item->object && (int) $item->object_id === (int) $post->ID ) {
				if ( '' === trim( (string) get_post_field( 'post_title', $item->ID ) ) || 'Creative Agency Partner' === trim( (string) get_post_field( 'post_title', $item->ID ) ) ) {
					wp_update_post( array( 'ID' => $item->ID, 'post_title' => 'Agency partner' ) );
				}
			} elseif ( 'custom' === $item->type && false !== strpos( (string) $item->url, '/services/' . $old_slug ) ) {
				update_post_meta( $item->ID, '_menu_item_url', str_replace( '/services/' . $old_slug, '/services/wordpress-agency-partnership', (string) $item->url ) );
				if ( 'Creative Agency Partner' === trim( (string) $item->title ) ) {
					wp_update_post( array( 'ID' => $item->ID, 'post_title' => 'Agency partner' ) );
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

/**
 * 2026-10-24 SEO audit follow-ups, part 2.
 *
 * Service H1s brought into line with the retargeted Yoast titles, and the
 * homepage title / meta description test (Search Console CTR was 0.1% on
 * 56k impressions). Previous values are kept in the option for rollback.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_seo_audit_2026_10_b' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	$log = array();

	$headlines = array(
		'seo'                          => 'WordPress SEO agency in London',
		'wordpress-agency-partnership' => 'White-label WordPress developers for creative agencies',
	);
	foreach ( $headlines as $slug => $headline ) {
		$post = get_page_by_path( $slug, OBJECT, 'service' );
		if ( ! $post ) {
			$log[] = $slug . ':missing';
			continue;
		}
		$log[] = $slug . ':was:' . (string) get_field( 'service_headline', $post->ID );
		update_field( 'service_headline', $headline, $post->ID );
	}

	$front = (int) get_option( 'page_on_front' );
	if ( $front ) {
		$log[] = 'home:title-was:' . (string) get_post_meta( $front, '_yoast_wpseo_title', true );
		$log[] = 'home:desc-was:' . (string) get_post_meta( $front, '_yoast_wpseo_metadesc', true );
		update_post_meta( $front, '_yoast_wpseo_title', 'London WordPress Web Designer & Developer - Est. 2011' );
		update_post_meta( $front, '_yoast_wpseo_metadesc', "Bespoke WordPress design, development and support from a small London studio trusted by easyJet, Lord's and the Church of England since 2011. Get a quote." );
	} else {
		$log[] = 'home:no-static-front-page';
	}

	update_option( 'le_migration_seo_audit_2026_10_b', gmdate( 'c' ) . ' ' . implode( ' | ', $log ), false );
}, 20 );

/**
 * 2026-10-26: new "WordPress Agency" service, the hub of the agency keyword
 * cluster (copy approved in the "London WordPress Agency page copy" doc).
 * Creates the service post with its ACF content and Yoast meta, adds it to the
 * Services page core list, the primary Services dropdown and the footer
 * Services menu. Safe to re-run: skips when the post already exists.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_wordpress_agency_service' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	$log = array();
	if ( get_page_by_path( 'wordpress-agency', OBJECT, 'service' ) ) {
		update_option( 'le_migration_wordpress_agency_service', 'already-exists', false );
		return;
	}

	$id = wp_insert_post( array(
		'post_type'   => 'service',
		'post_status' => 'publish',
		'post_title'  => 'WordPress Agency',
		'post_name'   => 'wordpress-agency',
		'post_content' => '',
	), true );
	if ( is_wp_error( $id ) ) {
		update_option( 'le_migration_wordpress_agency_service', 'insert-failed:' . $id->get_error_message(), false );
		return;
	}
	$log[] = 'post:' . $id;

	$by_slug = static function ( $slugs, $type ) {
		$ids = array();
		foreach ( $slugs as $s ) {
			$p = get_page_by_path( $s, OBJECT, $type );
			if ( $p ) {
				$ids[] = $p->ID;
			}
		}
		return $ids;
	};

	update_field( 'field_sv_subtitle', 'Small by design', $id );
	update_field( 'field_sv_desc', 'Bespoke WordPress design, development and support from a small senior team in London.', $id );
	update_field( 'field_sv_excerpt', "The WordPress agency behind sites for easyJet, Lord's Cricket Ground, the Church of England and 50+ other brands - small enough to care, experienced enough to handle the complicated stuff.", $id );
	update_field( 'field_sv_features', array(
		array( 'field_sv_feature_tag' => 'Design' ),
		array( 'field_sv_feature_tag' => 'Development' ),
		array( 'field_sv_feature_tag' => 'Support' ),
	), $id );
	update_field( 'field_sv_headline', 'A London WordPress agency that still answers the phone', $id );
	update_field( 'field_sv_intro', "We're a London WordPress agency of five - designers, developers and, let's face it, geeks - who've been building bespoke WordPress websites since 2011. No account managers, no outsourcing, and no page builders unless you ask for one. You talk to the people who design and build your site, and we stick around to look after it once it's live.", $id );
	update_field( 'field_6a8d88e295d9a', 'Everything a WordPress agency should do, done properly', $id );
	update_field( 'field_sv_dfeatures', array(
		array( 'field_sv_df_title' => 'WordPress web design', 'field_sv_df_desc' => "Bespoke designs built around your brand and your content, not a theme we've used twenty times before. Clean, fast and easy to edit once it's yours." ),
		array( 'field_sv_df_title' => 'WordPress development', 'field_sv_df_desc' => 'Hand-coded themes with Advanced Custom Fields, so 99% of the site is editable from the back end and none of it depends on a page builder.' ),
		array( 'field_sv_df_title' => 'E-commerce', 'field_sv_df_desc' => 'WooCommerce and Shopify builds with the payment, booking and shipping integrations you actually need - and nothing slowing the checkout down.' ),
		array( 'field_sv_df_title' => 'Website support & hosting', 'field_sv_df_desc' => 'Proactive updates, security, backups and fully managed UK hosting on 100% green energy. Answered by us, personally - not a ticket queue.' ),
		array( 'field_sv_df_title' => 'Migrations', 'field_sv_df_desc' => 'From Webflow, Squarespace, Wix, Drupal or an old WordPress build to a new one - with every URL redirected so you keep your rankings.' ),
		array( 'field_sv_df_title' => 'AI & integrations', 'field_sv_df_desc' => 'CRMs, APIs, custom tools and AI features built around the site, from a team ranked in the top 5% of AI developers globally.' ),
	), $id );
	update_field( 'field_sv_process', array(
		array( 'field_sv_ps_step' => '01', 'field_sv_ps_title' => 'Discovery', 'field_sv_ps_desc' => 'A call, a look at your current site and analytics, and a short written scope with a fixed price. No 40-page proposals, no surprises later.' ),
		array( 'field_sv_ps_step' => '02', 'field_sv_ps_title' => 'Design', 'field_sv_ps_desc' => "Figma designs for desktop and mobile, built on your brand. You see real pages, not mood boards, and we iterate until you're happy." ),
		array( 'field_sv_ps_step' => '03', 'field_sv_ps_title' => 'Build', 'field_sv_ps_desc' => 'The design is coded into front-end templates and integrated into WordPress with Advanced Custom Fields. You get a staging link from week one.' ),
		array( 'field_sv_ps_step' => '04', 'field_sv_ps_title' => 'Launch & support', 'field_sv_ps_desc' => "Redirects, SEO checks, speed tuning and a tested launch. Then a month's guarantee, training for your team and an optional support plan." ),
	), $id );
	update_field( 'field_sv_benefits', array(
		array( 'field_sv_b_item' => 'Senior team only - the person on the call is the person writing the code' ),
		array( 'field_sv_b_item' => 'Hand-coded WordPress by default, with fast Core Web Vitals as standard' ),
		array( 'field_sv_b_item' => 'Fixed prices and honest timelines - a brochure site in 4-6 weeks, complex builds in 8-12' ),
		array( 'field_sv_b_item' => "Trusted by easyJet, Lord's, the Church of England, United Airlines and NTT DATA" ),
		array( 'field_sv_b_item' => 'UK-based hosting and support, answered by us, not a ticket queue' ),
		array( 'field_sv_b_item' => '15 years in business, 100+ projects, and 1.5 nervous breakdowns' ),
	), $id );
	update_field( 'field_sv_faq', array(
		array( 'field_sv_faq_q' => 'What does a WordPress agency do?', 'field_sv_faq_a' => 'A WordPress agency designs, builds, hosts and maintains websites on WordPress. At Lewis Edward that means bespoke design, hand-coded development, WooCommerce, migrations, hosting and ongoing support from one small London team, rather than passing you between departments.' ),
		array( 'field_sv_faq_q' => 'How much does a WordPress website cost in the UK?', 'field_sv_faq_a' => 'It depends on the number of templates, integrations and content. A simple brochure site sits at the lower end, e-commerce, membership or multilingual builds at the upper end, and most bespoke agency builds are five-figure projects. We quote a fixed price after a discovery call, so you know the number before any work starts.' ),
		array( 'field_sv_faq_q' => 'How long does a WordPress agency take to build a site?', 'field_sv_faq_a' => 'A brochure site takes 4-6 weeks from signed-off designs; a larger build with custom functionality takes 8-12 weeks. We give you a dated plan up front and a staging link from the first week.' ),
		array( 'field_sv_faq_q' => 'Do you use page builders like Elementor?', 'field_sv_faq_a' => "Not by default. We prefer to hand-code bespoke themes with Advanced Custom Fields for editing, which keeps sites fast, secure and free of plugin bloat. If your agency or client has a preferred CMS, page builder or platform, we're happy to work with it on request." ),
		array( 'field_sv_faq_q' => 'Do you only work with London businesses?', 'field_sv_faq_a' => "We're based in London and most of our clients are UK brands, but we work with organisations across Europe and the US over video calls and shared tools. The process is the same wherever you are." ),
		array( 'field_sv_faq_q' => 'Can you take over a WordPress site another agency built?', 'field_sv_faq_a' => "Yes. We audit it first, tell you honestly what's worth keeping, and then either fix and support it or plan a rebuild. Plenty of our support clients came to us this way." ),
	), $id );
	update_field( 'field_sv_related_work', $by_slug( array( 'lords-cricket-ground', 'easyjet-traveller', 'church-of-england', 'biogen', 'storezar', 'hubba' ), 'work' ), $id );
	update_field( 'field_sv_related', $by_slug( array( 'web-design', 'web-development', 'website-support', 'e-commerce', 'wordpress-agency-partnership', 'ai-development' ), 'service' ), $id );

	update_post_meta( $id, '_yoast_wpseo_title', 'London WordPress Agency - Design, Development & Support' );
	update_post_meta( $id, '_yoast_wpseo_metadesc', "A small, senior London WordPress agency. Bespoke design, hand-coded development and proactive support for brands like easyJet, Lord's and the Church of England." );
	update_post_meta( $id, '_yoast_wpseo_focuskw', 'wordpress agency' );

	// Services page: first item in the core list.
	$services_page = get_page_by_path( 'services' );
	if ( $services_page ) {
		$items = get_field( 'services_items', $services_page->ID, false );
		$items = is_array( $items ) ? array_map( 'intval', $items ) : array();
		array_unshift( $items, (int) $id );
		update_field( 'services_items', array_values( array_unique( $items ) ), $services_page->ID );
		$log[] = 'services-page:' . $services_page->ID;
	}

	// Menus: first child of the primary "Services" item, and appended to the footer Services menu.
	if ( ! function_exists( 'wp_update_nav_menu_item' ) ) {
		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
	}
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['primary'] ) ) {
		$parent = 0;
		foreach ( (array) wp_get_nav_menu_items( $locations['primary'] ) as $item ) {
			if ( 0 === (int) $item->menu_item_parent && 'services' === sanitize_title( $item->title ) ) {
				$parent = (int) $item->ID;
				break;
			}
		}
		if ( $parent ) {
			$mi = wp_update_nav_menu_item( $locations['primary'], 0, array(
				'menu-item-title'       => 'WordPress agency',
				'menu-item-object'      => 'service',
				'menu-item-object-id'   => $id,
				'menu-item-type'        => 'post_type',
				'menu-item-description' => 'Design, development and support under one roof.',
				'menu-item-parent-id'   => $parent,
				'menu-item-position'    => 0,
				'menu-item-status'      => 'publish',
			) );
			$log[] = 'primary-menu:' . ( is_wp_error( $mi ) ? $mi->get_error_message() : $mi );
		}
	}
	if ( ! empty( $locations['footer_services'] ) ) {
		$mi = wp_update_nav_menu_item( $locations['footer_services'], 0, array(
			'menu-item-title'     => 'WordPress agency',
			'menu-item-object'    => 'service',
			'menu-item-object-id' => $id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		) );
		$log[] = 'footer-menu:' . ( is_wp_error( $mi ) ? $mi->get_error_message() : $mi );
	}

	if ( class_exists( 'WPSEO_Sitemaps_Cache' ) && method_exists( 'WPSEO_Sitemaps_Cache', 'clear' ) ) {
		WPSEO_Sitemaps_Cache::clear();
	}
	update_option( 'le_migration_wordpress_agency_service', gmdate( 'c' ) . ' ' . implode( ' ', $log ), false );
}, 20 );

/**
 * 2026-10-26b: Yoast builds a post's indexable on save, so meta written after
 * wp_insert_post() was ignored. Re-save the WordPress Agency service so the
 * title and description take effect.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_wordpress_agency_resave' ) ) {
		return;
	}
	$post = get_page_by_path( 'wordpress-agency', OBJECT, 'service' );
	if ( ! $post ) {
		return;
	}
	update_post_meta( $post->ID, '_yoast_wpseo_title', 'London WordPress Agency - Design, Development & Support' );
	update_post_meta( $post->ID, '_yoast_wpseo_metadesc', "A small, senior London WordPress agency. Bespoke design, hand-coded development and proactive support for brands like easyJet, Lord's and the Church of England." );
	wp_update_post( array( 'ID' => $post->ID, 'post_status' => 'publish' ) );
	if ( class_exists( 'WPSEO_Sitemaps_Cache' ) && method_exists( 'WPSEO_Sitemaps_Cache', 'clear' ) ) {
		WPSEO_Sitemaps_Cache::clear();
	}
	update_option( 'le_migration_wordpress_agency_resave', gmdate( 'c' ), false );
}, 21 );

/**
 * 2026-10-26c: cost FAQ on the WordPress Agency service - Lewis's figure.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_wordpress_agency_cost_faq' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	$post = get_page_by_path( 'wordpress-agency', OBJECT, 'service' );
	if ( ! $post ) {
		return;
	}
	$faq = get_field( 'service_faq', $post->ID );
	if ( is_array( $faq ) ) {
		foreach ( $faq as $i => $row ) {
			if ( isset( $row['question'] ) && false !== stripos( $row['question'], 'cost' ) ) {
				$faq[ $i ]['answer'] = "Our bespoke WordPress websites start from £5,000 and go up from there depending on the number of templates, integrations and content. A simple brochure site sits at the lower end; e-commerce, membership or multilingual builds at the upper end. We quote a fixed price after a discovery call, so you know the number before any work starts.";
			}
		}
		update_field( 'service_faq', $faq, $post->ID );
	}
	update_option( 'le_migration_wordpress_agency_cost_faq', gmdate( 'c' ), false );
}, 22 );

/**
 * 2026-10-26d: "WordPress Development Agency" service, second page of the
 * agency cluster. Same shape as the hub migration; links up to the hub.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_wp_dev_agency_service' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	if ( get_page_by_path( 'wordpress-development-agency', OBJECT, 'service' ) ) {
		update_option( 'le_migration_wp_dev_agency_service', 'already-exists', false );
		return;
	}
	$log = array();
	$title = 'WordPress Development Agency London | Lewis Edward';
	$desc  = 'A London WordPress development agency that hand-codes every build. Bespoke themes, ACF, integrations and headless WordPress for brands, agencies and in-house teams.';

	$id = wp_insert_post( array(
		'post_type'    => 'service',
		'post_status'  => 'publish',
		'post_title'   => 'WordPress Development Agency',
		'post_name'    => 'wordpress-development-agency',
		'post_content' => '',
		'meta_input'   => array(
			'_yoast_wpseo_title'    => $title,
			'_yoast_wpseo_metadesc' => $desc,
			'_yoast_wpseo_focuskw'  => 'wordpress development agency',
		),
	), true );
	if ( is_wp_error( $id ) ) {
		update_option( 'le_migration_wp_dev_agency_service', 'insert-failed:' . $id->get_error_message(), false );
		return;
	}
	$log[] = 'post:' . $id;

	$by_slug = static function ( $slugs, $type ) {
		$ids = array();
		foreach ( $slugs as $s ) {
			$p = get_page_by_path( $s, OBJECT, $type );
			if ( $p ) {
				$ids[] = $p->ID;
			}
		}
		return $ids;
	};

	update_field( 'field_sv_subtitle', 'Built from scratch', $id );
	update_field( 'field_sv_desc', 'Hand-coded WordPress builds, integrations and headless from a senior London team.', $id );
	update_field( 'field_sv_excerpt', "Bespoke WordPress development for brands like easyJet, Lord's and the Church of England - and the agencies that design for them.", $id );
	update_field( 'field_sv_features', array(
		array( 'field_sv_feature_tag' => 'Hand-coded' ),
		array( 'field_sv_feature_tag' => 'Integrations' ),
		array( 'field_sv_feature_tag' => 'Headless' ),
	), $id );
	update_field( 'field_sv_headline', 'A WordPress development agency that writes its own code', $id );
	update_field( 'field_sv_intro', "We're the development side of a small London WordPress agency - senior developers who hand-code every theme, wire up every integration and only reach for a page builder when a client asks for one. Brands come to us for bespoke builds; creative agencies and in-house teams come to us when they need serious WordPress engineering without hiring for it.", $id );
	update_field( 'field_6a8d88e295d9a', 'WordPress development, the way it should be done', $id );
	update_field( 'field_sv_dfeatures', array(
		array( 'field_sv_df_title' => 'Bespoke theme development', 'field_sv_df_desc' => "Every site is coded from your designs (or ours) into a clean, custom theme. No bloated frameworks, no 300 unused options, nothing you don't need." ),
		array( 'field_sv_df_title' => 'Advanced Custom Fields', 'field_sv_df_desc' => 'Structured, editable content for everything - flexible page builders of your own, without the plugin. 99% of the site is editable from the back end.' ),
		array( 'field_sv_df_title' => 'Integrations & APIs', 'field_sv_df_desc' => "CRMs, payment gateways, booking systems, HubSpot, Stripe, Teachable, custom APIs. If it has an endpoint, we've probably connected WordPress to it." ),
		array( 'field_sv_df_title' => 'Headless & performance', 'field_sv_df_desc' => "Headless WordPress with React or Next.js when it's the right call, and relentless Core Web Vitals work when it isn't. Fast either way." ),
		array( 'field_sv_df_title' => 'WooCommerce development', 'field_sv_df_desc' => 'Custom shops, checkout flows and product logic that go well beyond what off-the-shelf WooCommerce does.' ),
		array( 'field_sv_df_title' => 'White-label for agencies', 'field_sv_df_desc' => 'Development capacity for creative agencies: pixel-perfect builds from your Figma files, under your brand if you prefer. See our agency partnership.' ),
	), $id );
	update_field( 'field_sv_process', array(
		array( 'field_sv_ps_step' => '01', 'field_sv_ps_title' => 'Scope & architecture', 'field_sv_ps_desc' => 'We read the designs, the brief and the data, then write a short technical scope: templates, fields, integrations, hosting. Fixed price, dated plan.' ),
		array( 'field_sv_ps_step' => '02', 'field_sv_ps_title' => 'Front-end build', 'field_sv_ps_desc' => 'Designs are coded into responsive front-end templates first, so you see the real thing in a browser early and we iron out the details before WordPress is involved.' ),
		array( 'field_sv_ps_step' => '03', 'field_sv_ps_title' => 'WordPress integration', 'field_sv_ps_desc' => 'Templates become a theme; content becomes ACF fields; integrations are wired and tested. You get a staging site and a login from week one.' ),
		array( 'field_sv_ps_step' => '04', 'field_sv_ps_title' => 'QA, launch & handover', 'field_sv_ps_desc' => "Cross-browser and device testing, performance and SEO checks, redirects, launch. Then a month's bug guarantee, documentation and optional support." ),
	), $id );
	update_field( 'field_sv_benefits', array(
		array( 'field_sv_b_item' => 'Hand-coded themes by default - no theme marketplaces, no technical debt on day one' ),
		array( 'field_sv_b_item' => 'Senior developers only; the person scoping your build is the person building it' ),
		array( 'field_sv_b_item' => 'Integrations as standard: HubSpot, Stripe, WooCommerce, Teachable, custom APIs and AI tools' ),
		array( 'field_sv_b_item' => 'Fast by default - Core Web Vitals treated as a requirement, not a plugin' ),
		array( 'field_sv_b_item' => 'Agency-friendly: Figma in, pixel-perfect WordPress out, white label if you want it' ),
		array( 'field_sv_b_item' => '15 years of WordPress, 100+ builds, clients from easyJet to the Church of England' ),
	), $id );
	update_field( 'field_sv_faq', array(
		array( 'field_sv_faq_q' => 'What is a WordPress development agency?', 'field_sv_faq_a' => 'A WordPress development agency is a team that builds and engineers WordPress websites professionally - custom themes, plugins, integrations and performance - rather than designing them or assembling them from templates. We do the engineering for brands directly and as a white-label partner for creative agencies.' ),
		array( 'field_sv_faq_q' => 'How much does WordPress development cost?', 'field_sv_faq_a' => "Our bespoke WordPress builds start from £5,000 and go up with the number of templates, integrations and custom functionality. Agencies working with us regularly get a day rate or a fixed price per build, whichever suits the project. We quote a fixed figure after scoping." ),
		array( 'field_sv_faq_q' => 'Do you build from our designs?', 'field_sv_faq_a' => "Yes - that's most of what we do for agencies. Send Figma, Adobe XD, Sketch or PDFs and we code them pixel-perfect, including hover states, animations and responsive breakpoints you haven't drawn yet." ),
		array( 'field_sv_faq_q' => 'Can you work with our existing WordPress site?', 'field_sv_faq_a' => "Yes. We audit the codebase first and tell you honestly whether to extend it, refactor it or rebuild. Plenty of our development work starts with someone else's theme." ),
		array( 'field_sv_faq_q' => 'Do you do headless WordPress?', 'field_sv_faq_a' => "When it's the right fit, yes - WordPress as the CMS with a React or Next.js front end. For most brochure and content sites a well-built classic theme is faster to ship and easier to run, and we'll tell you which you need." ),
		array( 'field_sv_faq_q' => 'Who owns the code?', 'field_sv_faq_a' => 'You do. Every build is delivered as a clean, documented theme in a Git repository that you own, with no licence fees or lock-in to us.' ),
	), $id );
	update_field( 'field_sv_related_work', $by_slug( array( 'church-of-england', 'classic-shoe-repairs', 'prism', 'storezar', 'hubba', 'easyjet-traveller' ), 'work' ), $id );
	update_field( 'field_sv_related', $by_slug( array( 'wordpress-agency', 'web-development', 'wordpress-agency-partnership', 'api-integrations', 'webflow', 'migrate-to-wordpress' ), 'service' ), $id );

	// Re-save so Yoast rebuilds the indexable with the meta above.
	wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );

	// Services page core list: directly after the hub.
	$services_page = get_page_by_path( 'services' );
	if ( $services_page ) {
		$items = get_field( 'services_items', $services_page->ID, false );
		$items = is_array( $items ) ? array_map( 'intval', $items ) : array();
		$hub   = get_page_by_path( 'wordpress-agency', OBJECT, 'service' );
		$pos   = $hub ? array_search( (int) $hub->ID, $items, true ) : false;
		if ( false === $pos ) {
			array_unshift( $items, (int) $id );
		} else {
			array_splice( $items, $pos + 1, 0, array( (int) $id ) );
		}
		update_field( 'services_items', array_values( array_unique( $items ) ), $services_page->ID );
		$log[] = 'services-page:' . $services_page->ID;
	}

	if ( ! function_exists( 'wp_update_nav_menu_item' ) ) {
		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
	}
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['primary'] ) ) {
		$parent = 0;
		foreach ( (array) wp_get_nav_menu_items( $locations['primary'] ) as $item ) {
			if ( 0 === (int) $item->menu_item_parent && 'services' === sanitize_title( $item->title ) ) {
				$parent = (int) $item->ID;
				break;
			}
		}
		if ( $parent ) {
			$mi = wp_update_nav_menu_item( $locations['primary'], 0, array(
				'menu-item-title'       => 'WordPress development agency',
				'menu-item-object'      => 'service',
				'menu-item-object-id'   => $id,
				'menu-item-type'        => 'post_type',
				'menu-item-description' => 'Hand-coded builds, integrations and headless.',
				'menu-item-parent-id'   => $parent,
				'menu-item-position'    => 0,
				'menu-item-status'      => 'publish',
			) );
			$log[] = 'primary-menu:' . ( is_wp_error( $mi ) ? $mi->get_error_message() : $mi );
		}
	}
	if ( ! empty( $locations['footer_services'] ) ) {
		$mi = wp_update_nav_menu_item( $locations['footer_services'], 0, array(
			'menu-item-title'     => 'WordPress development agency',
			'menu-item-object'    => 'service',
			'menu-item-object-id' => $id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		) );
		$log[] = 'footer-menu:' . ( is_wp_error( $mi ) ? $mi->get_error_message() : $mi );
	}

	if ( class_exists( 'WPSEO_Sitemaps_Cache' ) && method_exists( 'WPSEO_Sitemaps_Cache', 'clear' ) ) {
		WPSEO_Sitemaps_Cache::clear();
	}
	update_option( 'le_migration_wp_dev_agency_service', gmdate( 'c' ) . ' ' . implode( ' ', $log ), false );
}, 23 );

/**
 * 2026-10-31: the two agency pages out of the core list on /services/ (back to
 * the original six) and into the specialised list instead.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_agency_pages_to_sub_list' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	$services_page = get_page_by_path( 'services' );
	if ( ! $services_page ) {
		return;
	}
	$agency = array();
	foreach ( array( 'wordpress-agency', 'wordpress-development-agency' ) as $slug ) {
		$p = get_page_by_path( $slug, OBJECT, 'service' );
		if ( $p ) {
			$agency[] = (int) $p->ID;
		}
	}
	$core = get_field( 'services_items', $services_page->ID, false );
	$core = is_array( $core ) ? array_map( 'intval', $core ) : array();
	$core = array_values( array_diff( $core, $agency ) );
	update_field( 'services_items', $core, $services_page->ID );

	$sub = get_field( 'services_sub_items', $services_page->ID, false );
	$sub = is_array( $sub ) ? array_map( 'intval', $sub ) : array();
	$sub = array_values( array_unique( array_merge( $sub, $agency ) ) );
	update_field( 'services_sub_items', $sub, $services_page->ID );

	update_option( 'le_migration_agency_pages_to_sub_list', gmdate( 'c' ) . ' core:' . count( $core ) . ' sub:' . count( $sub ), false );
}, 24 );

/**
 * One-off: the Brighton & Hove Museums logo for its testimonial. Their site
 * prints the logo as inline SVG (no file), so this reads it from their
 * homepage header, adds the xmlns a standalone file needs, sanitises it like
 * a normal SVG upload and imports it to the Media Library. It is set as the
 * "Company logo" of any testimonial whose company mentions Brighton and has
 * none yet. Runs for an admin in wp-admin, so SVG uploads are allowed.
 */
add_action( 'admin_init', function () {
	if ( get_option( 'le_migration_bhm_logo' ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$log = array();
	$res = wp_remote_get( 'https://brightonmuseums.org.uk/', array( 'timeout' => 20, 'user-agent' => 'Mozilla/5.0 (compatible; LewisEdwardSite/1.0)' ) );
	$html = is_wp_error( $res ) ? '' : (string) wp_remote_retrieve_body( $res );
	$svg  = '';
	if ( $html && preg_match( '#<header\b.*?(<svg\b[^>]*viewBox="0 0 218\.691 99\.999"[^>]*>.*?</svg>)#is', $html, $m ) ) {
		$svg = $m[1];
	} elseif ( $html && preg_match( '#(<svg\b[^>]*viewBox="0 0 218\.691 99\.999"[^>]*>.*?</svg>)#is', $html, $m ) ) {
		$svg = $m[1];
	}
	if ( ! $svg ) {
		update_option( 'le_migration_bhm_logo', gmdate( 'c' ) . ' not-found:' . ( is_wp_error( $res ) ? $res->get_error_code() : wp_remote_retrieve_response_code( $res ) ), false );
		return;
	}
	if ( false === stripos( $svg, 'xmlns=' ) ) {
		$svg = preg_replace( '#^<svg\b#i', '<svg xmlns="http://www.w3.org/2000/svg"', $svg );
	}
	$clean = function_exists( 'le_sanitize_svg_string' ) ? le_sanitize_svg_string( '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $svg ) : '';
	if ( ! $clean || false === stripos( $clean, '<svg' ) ) {
		update_option( 'le_migration_bhm_logo', gmdate( 'c' ) . ' sanitise-failed', false );
		return;
	}
	$tmp = wp_tempnam( 'brighton-hove-museums-logo.svg' );
	file_put_contents( $tmp, $clean ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	$id = media_handle_sideload( array( 'name' => 'brighton-hove-museums-logo.svg', 'tmp_name' => $tmp ), 0, 'Brighton & Hove Museums logo' );
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		update_option( 'le_migration_bhm_logo', gmdate( 'c' ) . ' upload:' . $id->get_error_code(), false );
		return;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', 'Brighton & Hove Museums logo' );
	$log[] = 'media:' . $id;
	foreach ( get_posts( array( 'post_type' => 'testimonial', 'post_status' => 'any', 'numberposts' => -1 ) ) as $t ) {
		$co = (string) get_field( 'testimonial_company', $t->ID );
		if ( false === stripos( $co, 'brighton' ) || get_field( 'testimonial_logo', $t->ID ) ) {
			continue;
		}
		update_field( 'field_ts_logo', $id, $t->ID );
		$log[] = 'testimonial:' . $t->ID;
	}
	update_option( 'le_migration_bhm_logo', gmdate( 'c' ) . ' ' . implode( ' ', $log ), false );
} );

/**
 * 2026-11-04: roll back the homepage title / meta description test.
 *
 * The 24 Oct test (migration _b above) did not move CTR (0.1% before and after)
 * and homepage impressions fell by about two thirds from the same week, with the
 * head terms dropping out of the top 100. This restores the pre-test values
 * from the rollback log in le_migration_seo_audit_2026_10_b. An empty logged
 * value means the field was unset (Yoast template), so the meta is deleted
 * rather than set to "". The homepage title and meta are then to be left alone
 * until at least 25 Nov so Google settles on one version.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_home_title_rollback_2026_11' ) ) {
		return;
	}
	$log   = array();
	$prior = (string) get_option( 'le_migration_seo_audit_2026_10_b' );
	$front = (int) get_option( 'page_on_front' );
	if ( ! $front || '' === $prior ) {
		update_option( 'le_migration_home_title_rollback_2026_11', gmdate( 'c' ) . ' skipped:' . ( $front ? 'no-rollback-log' : 'no-static-front-page' ), false );
		return;
	}

	$restore = array(
		'_yoast_wpseo_title'    => 'home:title-was:',
		'_yoast_wpseo_metadesc' => 'home:desc-was:',
	);
	foreach ( $restore as $key => $marker ) {
		$pos = strpos( $prior, $marker );
		if ( false === $pos ) {
			$log[] = $key . ':marker-missing';
			continue;
		}
		$value = substr( $prior, $pos + strlen( $marker ) );
		$end   = strpos( $value, ' | home:' ); // the title may itself contain " | " (Yoast separator).
		if ( false !== $end ) {
			$value = substr( $value, 0, $end );
		}
		$value = trim( $value );
		$log[] = $key . ':now-was:' . (string) get_post_meta( $front, $key, true );
		if ( '' === $value ) {
			delete_post_meta( $front, $key );
			$log[] = $key . ':unset';
		} else {
			update_post_meta( $front, $key, $value );
			$log[] = $key . ':restored:' . $value;
		}
	}

	if ( has_action( 'litespeed_purge_all' ) ) {
		do_action( 'litespeed_purge_all' );
		$log[] = 'litespeed-purged';
	}

	update_option( 'le_migration_home_title_rollback_2026_11', gmdate( 'c' ) . ' ' . implode( ' | ', $log ), false );
}, 20 );

/**
 * 2026-11-05 Search Console audit, item 5: service page meta descriptions
 * rewritten as click propositions (named clients, year, London, an action).
 * Previous values are logged in the option for rollback. Homepage untouched.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_service_metadesc_2026_11' ) ) {
		return;
	}
	$log   = array();
	$descs = array(
		'web-design' => 'Bespoke WordPress web design from a small London studio trusted by easyJet, Lord\'s and the Church of England since 2011. Fast, easy to edit. Get a quote.',
		'web-development' => 'Hand-coded WordPress development in London. Custom themes, ACF and integrations for easyJet, Lord\'s and the Church of England since 2011. Get a quote.',
		'website-support' => 'Proactive WordPress support and maintenance from London: updates, security, backups and same-day fixes from a small senior team trusted since 2011.',
		'wordpress-agency' => 'A small, senior London WordPress agency: bespoke design, hand-coded development and proactive support for easyJet, Lord\'s and the Church of England.',
		'wordpress-development-agency' => 'London WordPress development agency that hand-codes every build: bespoke themes, ACF, integrations and headless WordPress for brands and agencies.',
		'e-commerce' => 'WooCommerce and Shopify development from a London WordPress studio established in 2011. Fast, secure online shops that are easy to run. Get a quote.',
		'seo' => 'WordPress SEO from a London agency that builds the sites it optimises: technical fixes, Core Web Vitals, content and structured data. White-hat only.',
		'wordpress-agency-partnership' => 'White-label WordPress, Webflow and AI development for creative agencies, delivered under your brand. A London partner with 15 years\' experience.',
		'ai-development' => 'AI development in London: custom tools, CRM and WordPress integrations built with Claude and Lovable around your existing systems. Est. 2011.',
		'website-optimisation' => 'WordPress performance optimisation from London: Core Web Vitals, caching, image and code fixes by developers who hand-code the sites they tune.',
		'web-hosting' => 'Fully managed WordPress hosting in the UK, including tech support: fast, secure and powered by 100% green energy, from a London studio since 2011.',
	);
	foreach ( $descs as $slug => $desc ) {
		$post = get_page_by_path( $slug, OBJECT, 'service' );
		if ( ! $post ) {
			$log[] = $slug . ':missing';
			continue;
		}
		$log[] = $slug . ':was:' . (string) get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
		update_post_meta( $post->ID, '_yoast_wpseo_metadesc', $desc );
	}
	if ( has_action( 'litespeed_purge_all' ) ) {
		do_action( 'litespeed_purge_all' );
		$log[] = 'litespeed-purged';
	}
	update_option( 'le_migration_service_metadesc_2026_11', gmdate( 'c' ) . ' ' . implode( ' | ', $log ), false );
}, 20 );

/**
 * 2026-11-05 audit item 9: rebuild the sitemap index now that the category
 * and work-category sitemaps are excluded (inc/seo.php).
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_sitemap_rebuild_2026_11' ) ) {
		return;
	}
	if ( class_exists( 'WPSEO_Sitemaps_Cache' ) && method_exists( 'WPSEO_Sitemaps_Cache', 'clear' ) ) {
		WPSEO_Sitemaps_Cache::clear();
	}
	update_option( 'le_migration_sitemap_rebuild_2026_11', gmdate( 'c' ), false );
}, 20 );

/**
 * 2026-11-05 audit item 3: the Consultancy service was "Discovered, not
 * indexed" with only the Services listing linking to it. Add it to the footer
 * Services menu so every page links to it.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_footer_consultancy_2026_11' ) ) {
		return;
	}
	$log  = array();
	$post = get_page_by_path( 'consultancy', OBJECT, 'service' );
	$locations = get_nav_menu_locations();
	if ( $post && ! empty( $locations['footer_services'] ) ) {
		$exists = false;
		foreach ( (array) wp_get_nav_menu_items( $locations['footer_services'] ) as $item ) {
			if ( (int) $item->object_id === (int) $post->ID ) {
				$exists = true;
			}
		}
		if ( $exists ) {
			$log[] = 'already-in-menu';
		} else {
			$mi = wp_update_nav_menu_item( $locations['footer_services'], 0, array(
				'menu-item-title'     => 'Consultancy',
				'menu-item-object'    => 'service',
				'menu-item-object-id' => $post->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );
			$log[] = 'footer-menu:' . ( is_wp_error( $mi ) ? $mi->get_error_message() : $mi );
		}
	} else {
		$log[] = $post ? 'no-footer-services-location' : 'consultancy-missing';
	}
	if ( has_action( 'litespeed_purge_all' ) ) {
		do_action( 'litespeed_purge_all' );
	}
	update_option( 'le_migration_footer_consultancy_2026_11', gmdate( 'c' ) . ' ' . implode( ' ', $log ), false );
}, 20 );

/**
 * 2026-11-05: "Website support" renamed "WordPress support & maintenance".
 * Buyers search "wordpress support" / "wordpress maintenance"; the short title
 * feeds the nav, footer, cards, homepage list and Service schema. Slug and
 * Yoast title are left alone; the H1 and every menu item for the service
 * follow the new name. Previous values logged for rollback.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_support_rename_2026_11' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	$log  = array();
	$post = get_page_by_path( 'website-support', OBJECT, 'service' );
	if ( ! $post ) {
		update_option( 'le_migration_support_rename_2026_11', gmdate( 'c' ) . ' missing', false );
		return;
	}
	$new = 'WordPress support & maintenance';

	$log[] = 'title-was:' . $post->post_title;
	wp_update_post( array( 'ID' => $post->ID, 'post_title' => $new, 'post_name' => 'website-support' ) );

	$log[] = 'h1-was:' . (string) get_field( 'service_headline', $post->ID );
	update_field( 'service_headline', $new, $post->ID );

	$items = get_posts( array(
		'post_type'      => 'nav_menu_item',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'meta_query'     => array( array( 'key' => '_menu_item_object_id', 'value' => $post->ID ) ),
		'fields'         => 'ids',
	) );
	foreach ( $items as $mid ) {
		$log[] = 'menu-' . $mid . '-was:' . get_the_title( $mid );
		wp_update_post( array( 'ID' => $mid, 'post_title' => $new ) );
	}

	if ( has_action( 'litespeed_purge_all' ) ) {
		do_action( 'litespeed_purge_all' );
	}
	update_option( 'le_migration_support_rename_2026_11', gmdate( 'c' ) . ' ' . implode( ' | ', $log ), false );
}, 20 );

/**
 * 2026-11-05: the support page's "Our approach" section still carried the old
 * service name in its eyebrow ("Website support") and title ("Our website
 * support options") after the rename above. Previous values logged.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_support_section_labels_2026_11' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	$log  = array();
	$post = get_page_by_path( 'website-support', OBJECT, 'service' );
	if ( ! $post ) {
		update_option( 'le_migration_support_section_labels_2026_11', gmdate( 'c' ) . ' missing', false );
		return;
	}
	$fields = array(
		'svc_process_eyebrow' => array( 'Website support', 'Support & maintenance' ),
		'svc_process_title'   => array( 'Our website support options', 'Our WordPress support options' ),
	);
	foreach ( $fields as $name => $pair ) {
		$was = (string) get_field( $name, $post->ID );
		if ( $was === $pair[0] ) {
			update_field( $name, $pair[1], $post->ID );
			$log[] = $name . ':was:' . $was;
		} else {
			$log[] = $name . ':left:' . $was;
		}
	}
	if ( has_action( 'litespeed_purge_all' ) ) {
		do_action( 'litespeed_purge_all' );
	}
	update_option( 'le_migration_support_section_labels_2026_11', gmdate( 'c' ) . ' ' . implode( ' | ', $log ), false );
}, 21 );

/**
 * 2026-11-05: optional "How did you hear about us?" dropdown on both forms.
 *
 * Analytics only runs after cookie consent, so most enquiries arrive with no
 * known source (Lidia's 21 Oct quote request left no trace in GA). The answer
 * is stored with the entry and appears in the notification email via
 * {all_fields}. Contact form (1): after "Your message", before consent.
 * Quote form (2): end of the "Your project" section, after "Budget range".
 * Admin label how_heard marks the field so this never adds it twice.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_how_heard_2026_11' ) || ! class_exists( 'GFAPI' ) || ! class_exists( 'GF_Fields' ) ) {
		return;
	}
	$choices = array();
	foreach ( array( 'Google search', 'ChatGPT or another AI tool', 'Recommendation or word of mouth', 'LinkedIn', 'Instagram', 'Clutch or another directory', 'Other' ) as $c ) {
		$choices[] = array( 'text' => $c, 'value' => $c, 'isSelected' => false );
	}
	$targets = array(
		1 => array( 'after_type' => 'textarea' ),          // Your message.
		2 => array( 'after_label' => 'Budget range' ),     // Last field of "Your project".
	);
	$log = array();
	foreach ( $targets as $form_id => $rule ) {
		$form = GFAPI::get_form( $form_id );
		if ( ! $form ) {
			$log[] = $form_id . ':no-form';
			continue;
		}
		$pos = null;
		foreach ( $form['fields'] as $i => $f ) {
			if ( 'how_heard' === $f->adminLabel ) {
				$pos = false;
				break;
			}
			$label = trim( preg_replace( '/\s*\(optional\)\s*$/i', '', (string) $f->label ) );
			if ( ( isset( $rule['after_type'] ) && $rule['after_type'] === $f->type ) || ( isset( $rule['after_label'] ) && 0 === strcasecmp( $rule['after_label'], $label ) ) ) {
				$pos = $i + 1;
			}
		}
		if ( false === $pos ) {
			$log[] = $form_id . ':exists';
			continue;
		}
		if ( null === $pos ) {
			$pos = count( $form['fields'] ); // Fallback: end of the form.
			foreach ( $form['fields'] as $i => $f ) {
				if ( 'consent' === $f->type ) {
					$pos = $i;
					break;
				}
			}
			$log[] = $form_id . ':anchor-missing';
		}
		$field = GF_Fields::create( array(
			'type'        => 'select',
			'id'          => GFFormsModel::get_next_field_id( $form['fields'] ),
			'formId'      => $form_id,
			'label'       => 'How did you hear about us? (optional)',
			'adminLabel'  => 'how_heard',
			'isRequired'  => false,
			'placeholder' => 'Select one',
			'choices'     => $choices,
		) );
		array_splice( $form['fields'], $pos, 0, array( $field ) );
		$res     = GFAPI::update_form( $form );
		$log[]   = $form_id . ':' . ( is_wp_error( $res ) ? 'error:' . $res->get_error_message() : 'added-id-' . $field->id . '-at-' . $pos );
	}
	if ( has_action( 'litespeed_purge_all' ) ) {
		do_action( 'litespeed_purge_all' );
	}
	update_option( 'le_migration_how_heard_2026_11', gmdate( 'c' ) . ' ' . implode( ' | ', $log ), false );
}, 22 );

/**
 * 2026-11-05: personal autoreply to whoever submits the contact or quote form.
 *
 * Sent from Lewis (hello@lewisedward.com, via WP Mail SMTP / Resend, domain
 * verified) to the email field, with a copy of what they sent. Wording agreed
 * with Lewis. "Force From Email" in WP Mail SMTP must be off, otherwise the
 * sender is rewritten to team@. Added once; marked by the notification name.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_autoreply_2026_11' ) || ! class_exists( 'GFAPI' ) ) {
		return;
	}
	$sig  = "Lewis\nLewis Edward · WordPress web design &amp; development · London";
	$copy = "\n\n<strong>A copy of what you sent</strong>\n{all_fields:noadmin}";
	$set  = array(
		2 => array(
			'email'   => '5',
			'subject' => 'Thanks for your quote request, {Your name (First):3.3}',
			'body'    => "Hi {Your name (First):3.3},\n\n"
				. "Thanks for getting in touch about your project. Your request has come straight to me, and I'll read it properly and reply personally within one working day, usually sooner.\n\n"
				. "If it looks like a good fit, the next step is a short call to talk it through. After that I'll send a written scope with a fixed price, so you know exactly what you're getting before anything starts.\n\n"
				. "In the meantime, you can see some recent work here: <a href=\"https://lewisedward.com/work/\">lewisedward.com/work</a>\n\n"
				. "If anything changes or you'd like to add something, just reply to this email.\n\n" . $sig . $copy,
		),
		1 => array(
			'email'   => '3',
			'subject' => 'Thanks for your message, {Your name (First):1.3}',
			'body'    => "Hi {Your name (First):1.3},\n\n"
				. "Thanks for your message. It's come straight to me and I'll reply personally within one working day, usually sooner.\n\n"
				. "If you'd like to add anything in the meantime, just reply to this email.\n\n" . $sig . $copy,
		),
	);
	$log = array();
	foreach ( $set as $form_id => $n ) {
		$form = GFAPI::get_form( $form_id );
		if ( ! $form ) {
			$log[] = $form_id . ':no-form';
			continue;
		}
		$exists = false;
		foreach ( (array) rgar( $form, 'notifications' ) as $existing ) {
			if ( 'Autoreply to sender' === rgar( $existing, 'name' ) ) {
				$exists = true;
			}
		}
		if ( $exists ) {
			$log[] = $form_id . ':exists';
			continue;
		}
		$id                           = uniqid();
		$form['notifications'][ $id ] = array(
			'id'                => $id,
			'name'              => 'Autoreply to sender',
			'isActive'          => true,
			'service'           => 'wordpress',
			'event'             => 'form_submission',
			'toType'            => 'field',
			'to'                => $n['email'],
			'from'              => 'hello@lewisedward.com',
			'fromName'          => 'Lewis Edward',
			'replyTo'           => 'hello@lewisedward.com',
			'subject'           => $n['subject'],
			'message'           => $n['body'],
			'disableAutoformat' => false,
			'enableAttachments' => false,
		);
		$res   = GFAPI::update_form( $form );
		$log[] = $form_id . ':' . ( is_wp_error( $res ) ? 'error:' . $res->get_error_message() : 'added-' . $id );
	}
	update_option( 'le_migration_autoreply_2026_11', gmdate( 'c' ) . ' ' . implode( ' | ', $log ), false );
}, 23 );

/**
 * 2026-11-05: the "How did you hear about us?" field's admin label (how_heard)
 * showed as-is in the admin notification and entry screen. Give it a readable
 * admin label. The add-field migration above is guarded by its option, so it
 * will not add the field again.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_how_heard_label_2026_11' ) || ! class_exists( 'GFAPI' ) ) {
		return;
	}
	$log = array();
	foreach ( array( 1, 2 ) as $form_id ) {
		$form = GFAPI::get_form( $form_id );
		if ( ! $form ) {
			continue;
		}
		foreach ( $form['fields'] as $f ) {
			if ( 'how_heard' === $f->adminLabel ) {
				$f->adminLabel = 'How they heard about us';
				$log[]         = $form_id . ':' . $f->id;
			}
		}
		GFAPI::update_form( $form );
	}
	update_option( 'le_migration_how_heard_label_2026_11', gmdate( 'c' ) . ' ' . implode( ' | ', $log ), false );
}, 24 );

/**
 * 2026-11-05: switch the autoreplies to the branded template (inc/email-template.php).
 * The footer now carries the business line, so the second signature line goes,
 * and Gravity Forms' grey {all_fields} table is replaced by {le_summary}.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_autoreply_brand_2026_11' ) || ! class_exists( 'GFAPI' ) ) {
		return;
	}
	$log = array();
	foreach ( array( 1, 2 ) as $form_id ) {
		$form = GFAPI::get_form( $form_id );
		if ( ! $form ) {
			continue;
		}
		foreach ( (array) $form['notifications'] as $nid => $n ) {
			if ( 'Autoreply to sender' !== rgar( $n, 'name' ) ) {
				continue;
			}
			$msg = (string) $n['message'];
			$new = str_replace(
				array( "\nLewis Edward · WordPress web design &amp; development · London", "<strong>A copy of what you sent</strong>\n{all_fields:noadmin}" ),
				array( '', '{le_summary}' ),
				$msg
			);
			$form['notifications'][ $nid ]['message'] = $new;
			$log[] = $form_id . ':' . ( $new === $msg ? 'unchanged' : 'updated' );
		}
		GFAPI::update_form( $form );
	}
	update_option( 'le_migration_autoreply_brand_2026_11', gmdate( 'c' ) . ' ' . implode( ' | ', $log ), false );
}, 25 );

/**
 * 2026-11-05: three sector landing pages (Travel & tourism; Arts, culture &
 * heritage; Financial services), created as DRAFTS for Lewis to review. Copy
 * lives in inc/data/sectors-2026-11.json. Skips any slug that already exists.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_sectors_2026_11' ) || ! function_exists( 'update_field' ) || ! post_type_exists( 'sector' ) ) {
		return;
	}
	$file = LE_DIR . '/inc/data/sectors-2026-11.json';
	$data = file_exists( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_array( $data ) ) {
		update_option( 'le_migration_sectors_2026_11', gmdate( 'c' ) . ' no-data', false );
		return;
	}
	$ids_by_slug = static function ( $slugs, $type ) {
		$ids = array();
		foreach ( (array) $slugs as $s ) {
			$p = get_page_by_path( $s, OBJECT, $type );
			if ( $p && 'publish' === $p->post_status ) {
				$ids[] = $p->ID;
			}
		}
		return $ids;
	};
	$log = array();
	foreach ( $data as $s ) {
		if ( get_page_by_path( $s['slug'], OBJECT, 'sector' ) ) {
			$log[] = $s['slug'] . ':exists';
			continue;
		}
		$id = wp_insert_post( array( 'post_type' => 'sector', 'post_status' => 'draft', 'post_title' => $s['title'], 'post_name' => $s['slug'], 'post_content' => '' ), true );
		if ( is_wp_error( $id ) ) {
			$log[] = $s['slug'] . ':failed';
			continue;
		}
		update_field( 'field_sv_subtitle', $s['subtitle'], $id );
		update_field( 'field_sv_desc', $s['desc'], $id );
		update_field( 'field_sv_headline', $s['headline'], $id );
		update_field( 'field_sv_intro', $s['intro'], $id );
		update_field( 'field_6a8d88e295d9a', $s['included_title'], $id );
		update_field( 'field_sv_dfeatures', array_map( static function ( $f ) {
			return array( 'field_sv_df_title' => $f[0], 'field_sv_df_desc' => $f[1] );
		}, $s['features'] ), $id );
		update_field( 'field_sv_benefits', array_map( static function ( $b ) {
			return array( 'field_sv_b_item' => $b );
		}, $s['benefits'] ), $id );
		update_field( 'field_sv_faq', array_map( static function ( $q ) {
			return array( 'field_sv_faq_q' => $q[0], 'field_sv_faq_a' => $q[1] );
		}, $s['faq'] ), $id );
		update_field( 'field_sv_related_work', $ids_by_slug( $s['work'], 'work' ), $id );
		update_field( 'field_sv_related', $ids_by_slug( $s['related'], 'service' ), $id );
		update_field( 'field_sv_testimonials', array_values( array_filter( array_map( 'intval', (array) $s['testimonials'] ), static function ( $t ) {
			return 'publish' === get_post_status( $t );
		} ) ), $id );
		update_field( 'field_svl_included_eyebrow', $s['included_eyebrow'], $id );
		update_field( 'field_svl_benefits_title', $s['benefits_title'], $id );
		update_field( 'field_svl_work_eyebrow', $s['work_eyebrow'], $id );
		update_field( 'field_svl_work_title', $s['work_title'], $id );
		update_field( 'field_svl_related_eyebrow', 'How we help', $id );
		update_field( 'field_svl_related_title', 'Services for ' . strtolower( $s['title'] ) . '.', $id );
		update_post_meta( $id, '_yoast_wpseo_title', $s['seo_title'] );
		update_post_meta( $id, '_yoast_wpseo_metadesc', $s['seo_desc'] );
		$log[] = $s['slug'] . ':' . $id;
	}
	flush_rewrite_rules( false ); // /sectors/{slug} is a new permalink base.
	update_option( 'le_migration_sectors_2026_11', gmdate( 'c' ) . ' ' . implode( ' ', $log ), false );
}, 30 );

/**
 * 2026-11-06: purge LiteSpeed (and, through its Cloudflare link, the edge)
 * once so every cached page picks up the trailing-slash link changes.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_purge_slashes_2026_11b' ) ) {
		return;
	}
	$log = array();
	if ( has_action( 'litespeed_purge_all' ) ) {
		do_action( 'litespeed_purge_all' );
		$log[] = 'litespeed-purged';
	}
	update_option( 'le_migration_purge_slashes_2026_11b', gmdate( 'c' ) . ' ' . implode( ' | ', $log ), false );
}, 20 );

/**
 * 2026-11-06 SEO review: new Yoast titles and meta descriptions on ten service
 * pages, matched to the searches each page should win. Old values are logged
 * in the option so they can be restored. Homepage untouched (frozen to 25 Nov).
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_service_titles_2026_11_06' ) ) {
		return;
	}
	$set = array(
		'web-design'           => array( 'WordPress Web Design Company in London | Lewis Edward', 'Bespoke WordPress websites, designed and built by one small London team. Trusted by easyJet, Lord\'s and ArtsEd. Fixed-price quotes from £5,000.' ),
		'web-development'      => array( 'WordPress Developer London | Custom Themes & Plugins', 'Hand-coded WordPress themes, plugins and integrations from London developers building on WordPress since 2011. Clear scope, fixed price, no surprises.' ),
		'website-support'      => array( 'WordPress Support & Maintenance London | Same-Day Fixes', 'Monthly WordPress support from a UK team: updates, backups, security monitoring and same-day fixes. Support plans from £100 a month.' ),
		'web-hosting'          => array( 'Managed WordPress Hosting UK, Including Tech Support', 'Fast, fully managed WordPress hosting on UK servers, including tech support from the people who built your site. Daily backups and green energy.' ),
		'consultancy'          => array( 'WordPress Consultant London | Independent Expert Advice', 'Need a second opinion on a WordPress site, an agency quote or a rebuild? Independent advice from a London WordPress consultant working since 2011.' ),
		'website-security'     => array( 'Hacked WordPress Site? Malware Removal & Security', 'Hacked or infected WordPress site? We clean it, find how they got in and lock it down, usually same day. Then we keep it patched and monitored.' ),
		'website-optimisation' => array( 'WordPress Speed Optimisation | Core Web Vitals Fixes', 'Slow WordPress site? We fix Core Web Vitals, images, caching and bloated plugins, and show you the before and after scores.' ),
		'migrate-to-wordpress' => array( 'WordPress Migration Services UK | Webflow, Wix, Squarespace', 'Moving to WordPress from Webflow, Wix, Squarespace or another CMS? We migrate your content, keep your rankings with proper redirects and train your team.' ),
		'e-commerce'           => array( 'WooCommerce Agency London | WooCommerce & Shopify Builds', '' ),
		'website-audits'       => array( 'WordPress Website Audit | Technical, SEO & UX Review', '' ),
	);
	$log = array();
	foreach ( $set as $slug => $vals ) {
		$post = get_page_by_path( $slug, OBJECT, 'service' );
		if ( ! $post ) {
			$log[] = $slug . ':missing';
			continue;
		}
		$log[] = $slug . ':title-was:' . (string) get_post_meta( $post->ID, '_yoast_wpseo_title', true );
		update_post_meta( $post->ID, '_yoast_wpseo_title', $vals[0] );
		if ( '' !== $vals[1] ) {
			$log[] = $slug . ':desc-was:' . (string) get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
			update_post_meta( $post->ID, '_yoast_wpseo_metadesc', $vals[1] );
		}
		// Re-save so Yoast rebuilds its indexable from the new meta.
		wp_update_post( array( 'ID' => $post->ID ) );
	}
	if ( has_action( 'litespeed_purge_all' ) ) {
		do_action( 'litespeed_purge_all' );
		$log[] = 'litespeed-purged';
	}
	update_option( 'le_migration_service_titles_2026_11_06', gmdate( 'c' ) . ' ' . implode( ' | ', $log ), false );
}, 20 );

/**
 * 2026-11-07: rewritten copy for /services/web-design/ and
 * /services/website-support/ (cost, timeline, proof and team sections plus
 * new hero, features, benefits and FAQs). Copy lives in
 * inc/data/service-rewrite-2026-11.json. The previous field values are kept
 * in option le_migration_service_rewrite_2026_11_backup so they can be put back.
 */
add_action( 'init', function () {
	if ( get_option( 'le_migration_service_rewrite_2026_11' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	$file = LE_DIR . '/inc/data/service-rewrite-2026-11.json';
	$data = file_exists( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_array( $data ) ) {
		update_option( 'le_migration_service_rewrite_2026_11', gmdate( 'c' ) . ' no-data', false );
		return;
	}
	$id_of = static function ( $slug, $type ) {
		$p = get_page_by_path( $slug, OBJECT, $type );
		return ( $p && 'publish' === $p->post_status ) ? (int) $p->ID : 0;
	};
	$testimonial_ids = static function ( $names ) {
		$all = get_posts( array( 'post_type' => 'testimonial', 'post_status' => 'publish', 'numberposts' => -1 ) );
		$ids = array();
		foreach ( (array) $names as $n ) {
			foreach ( $all as $t ) {
				$hay = $t->post_title . ' ' . (string) get_field( 'testimonial_author', $t->ID );
				if ( false !== stripos( $hay, $n ) ) {
					$ids[] = (int) $t->ID;
					break;
				}
			}
		}
		return $ids;
	};
	$keep = array( 'service_headline', 'service_intro', 'whats_included_title', 'service_detail_features', 'service_process', 'service_benefits', 'service_faq', 'service_related_work', 'service_related', 'service_testimonials', 'svc_process_eyebrow', 'svc_process_title', 'svc_process_note', 'svc_benefits_eyebrow', 'svc_benefits_title', 'svc_benefits_intro' );
	$backup = array();
	$log    = array();
	foreach ( $data as $slug => $s ) {
		$id = $id_of( $slug, 'service' );
		if ( ! $id ) {
			$log[] = $slug . ':missing';
			continue;
		}
		foreach ( $keep as $k ) {
			$backup[ $slug ][ $k ] = get_field( $k, $id, false );
		}
		update_field( 'field_sv_headline', $s['headline'], $id );
		update_field( 'field_sv_intro', $s['intro'], $id );
		update_field( 'field_6a8d88e295d9a', $s['included_title'], $id );
		update_field( 'field_sv_dfeatures', array_map( static function ( $f ) {
			return array( 'field_sv_df_title' => $f[0], 'field_sv_df_desc' => $f[1] );
		}, $s['features'] ), $id );
		update_field( 'field_sv_cost_eyebrow', $s['cost_eyebrow'] ?? '', $id );
		update_field( 'field_sv_cost_title', $s['cost_title'], $id );
		update_field( 'field_sv_cost_intro', $s['cost_intro'], $id );
		update_field( 'field_sv_cost_rows', array_map( static function ( $r ) {
			return array( 'field_sv_cost_label' => $r[0], 'field_sv_cost_price' => $r[1], 'field_sv_cost_desc' => $r[2] );
		}, $s['cost_rows'] ), $id );
		update_field( 'field_sv_cost_note', $s['cost_note'], $id );
		update_field( 'field_svl_process_eyebrow', $s['process_eyebrow'], $id );
		update_field( 'field_svl_process_title', $s['process_title'], $id );
		update_field( 'field_svl_process_note', $s['process_note'], $id );
		update_field( 'field_sv_process_intro', $s['process_intro'], $id );
		update_field( 'field_sv_process', array_map( static function ( $p ) {
			return array( 'field_sv_ps_step' => $p[0], 'field_sv_ps_title' => $p[1], 'field_sv_ps_desc' => $p[2] );
		}, $s['process'] ), $id );
		update_field( 'field_sv_proof_eyebrow', $s['proof_eyebrow'], $id );
		update_field( 'field_sv_proof_title', $s['proof_title'], $id );
		$proof = array();
		foreach ( $s['proof'] as $p ) {
			$wid = $id_of( $p[0], 'work' );
			if ( $wid ) {
				$proof[] = array( 'field_sv_proof_work' => $wid, 'field_sv_proof_line' => $p[1] );
			}
		}
		update_field( 'field_sv_proof', $proof, $id );
		update_field( 'field_svl_benefits_eyebrow', $s['benefits_eyebrow'], $id );
		update_field( 'field_svl_benefits_title', $s['benefits_title'], $id );
		update_field( 'field_svl_benefits_intro', $s['benefits_intro'], $id );
		update_field( 'field_sv_benefits', array_map( static function ( $b ) {
			return array( 'field_sv_b_item' => $b );
		}, $s['benefits'] ), $id );
		update_field( 'field_sv_team_title', $s['team_title'], $id );
		update_field( 'field_sv_team_text', $s['team_text'], $id );
		update_field( 'field_sv_faq', array_map( static function ( $q ) {
			return array( 'field_sv_faq_q' => $q[0], 'field_sv_faq_a' => $q[1] );
		}, $s['faq'] ), $id );
		update_field( 'field_sv_related_work', array_values( array_filter( array_map( static function ( $w ) use ( $id_of ) {
			return $id_of( $w, 'work' );
		}, $s['work'] ) ) ), $id );
		update_field( 'field_sv_related', array_values( array_filter( array_map( static function ( $r ) use ( $id_of ) {
			return $id_of( $r, 'service' );
		}, $s['related'] ) ) ), $id );
		$tids = $testimonial_ids( $s['testimonials'] );
		if ( $tids ) {
			update_field( 'field_sv_testimonials', $tids, $id );
		}
		wp_update_post( array( 'ID' => $id ) ); // Refresh modified date and Yoast's indexable.
		$log[] = $slug . ':' . $id . ' proof=' . count( $proof ) . ' testimonials=' . implode( ',', $tids );
	}
	update_option( 'le_migration_service_rewrite_2026_11_backup', $backup, false );
	if ( has_action( 'litespeed_purge_all' ) ) {
		do_action( 'litespeed_purge_all' );
		$log[] = 'litespeed-purged';
	}
	update_option( 'le_migration_service_rewrite_2026_11', gmdate( 'c' ) . ' ' . implode( ' | ', $log ), false );
}, 30 );

/** 2026-11-07: purge once so service pages pick up the price-label fix. */
add_action( 'init', function () {
	if ( get_option( 'le_migration_purge_price_label_2026_11' ) ) {
		return;
	}
	if ( has_action( 'litespeed_purge_all' ) ) {
		do_action( 'litespeed_purge_all' );
	}
	update_option( 'le_migration_purge_price_label_2026_11', gmdate( 'c' ), false );
}, 40 );
