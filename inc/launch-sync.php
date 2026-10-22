<?php
/**
 * Launch sync: carry content made on staging over to live, field by field,
 * without copying the staging database (live has newer content of its own).
 *
 * Staging: POST /wp-json/le/v1/sync-export          → writes a JSON bundle to
 *          uploads/le-sync/ and returns its URL.
 * Live:    POST /wp-json/le/v1/sync-import {url, dry:true}  → diff report.
 *          POST /wp-json/le/v1/sync-import {url, ops:[…], alts:bool, options:[…]}
 *          → applies only the listed changes. Images are matched by file name
 *          and dimensions, otherwise downloaded from staging.
 *
 * Admin only (manage_options). Temporary: remove after launch.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Post types carried by the bundle. */
function le_sync_types() {
	return array( 'page', 'work', 'service', 'testimonial', 'team', 'post' );
}

/** Staging slug → live slug, where they differ. */
function le_sync_slug_alias( $type, $slug ) {
	$map = array( 'service/wordpress-agency-partnership' => 'creative-agency-partner' );
	return $map[ $type . '/' . $slug ] ?? $slug;
}

/* ------------------------------------------------------------------ export */

function le_sync_att( $id ) {
	$id = (int) $id;
	if ( ! $id || 'attachment' !== get_post_type( $id ) ) {
		return null;
	}
	$meta = wp_get_attachment_metadata( $id );
	$orig = function_exists( 'wp_get_original_image_url' ) ? wp_get_original_image_url( $id ) : '';
	return array(
		'__att'   => 1,
		'id'      => $id,
		'file'    => basename( (string) get_post_meta( $id, '_wp_attached_file', true ) ),
		'w'       => (int) ( $meta['width'] ?? 0 ),
		'h'       => (int) ( $meta['height'] ?? 0 ),
		'url'     => (string) wp_get_attachment_url( $id ),
		'orig'    => $orig ? (string) $orig : '',
		'alt'     => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
		'title'   => get_the_title( $id ),
		'caption' => (string) wp_get_attachment_caption( $id ),
	);
}

function le_sync_postref( $id ) {
	$p = is_numeric( $id ) ? get_post( (int) $id ) : null;
	return $p ? array( '__post' => 1, 'type' => $p->post_type, 'slug' => $p->post_name ) : $id;
}

function le_sync_termref( $id, $tax ) {
	$t = is_numeric( $id ) ? get_term( (int) $id, $tax ) : null;
	return ( $t && ! is_wp_error( $t ) ) ? array( '__term' => 1, 'tax' => $t->taxonomy, 'slug' => $t->slug ) : $id;
}

/** Find a sub field definition by key or name. */
function le_sync_sub( $subs, $k ) {
	foreach ( (array) $subs as $s ) {
		if ( ( $s['key'] ?? '' ) === $k || ( $s['name'] ?? '' ) === $k ) {
			return $s;
		}
	}
	return null;
}

/** Walk a raw ACF value, mapping IDs to portable references (dir = out) or back (dir = in). */
function le_sync_walk( $field, $value, $dir, &$ctx ) {
	$t = $field['type'] ?? '';
	if ( in_array( $t, array( 'image', 'file' ), true ) ) {
		return 'out' === $dir ? ( is_numeric( $value ) ? le_sync_att( $value ) : $value ) : le_sync_att_in( $value, $ctx );
	}
	if ( 'gallery' === $t ) {
		return array_values( array_filter( array_map( function ( $v ) use ( $dir, &$ctx ) {
			return 'out' === $dir ? le_sync_att( $v ) : le_sync_att_in( $v, $ctx );
		}, (array) $value ) ) );
	}
	if ( in_array( $t, array( 'relationship', 'post_object', 'page_link' ), true ) ) {
		$one = function ( $v ) use ( $dir, &$ctx ) {
			return 'out' === $dir ? le_sync_postref( $v ) : le_sync_post_in( $v, $ctx );
		};
		return is_array( $value ) ? array_values( array_filter( array_map( $one, $value ) ) ) : $one( $value );
	}
	if ( 'taxonomy' === $t ) {
		$tax = $field['taxonomy'] ?? '';
		$one = function ( $v ) use ( $dir, $tax, &$ctx ) {
			return 'out' === $dir ? le_sync_termref( $v, $tax ) : le_sync_term_in( $v, $ctx );
		};
		return is_array( $value ) ? array_values( array_filter( array_map( $one, $value ) ) ) : $one( $value );
	}
	if ( 'group' === $t && is_array( $value ) ) {
		$out = array();
		foreach ( $value as $k => $v ) {
			$sub       = le_sync_sub( $field['sub_fields'] ?? array(), $k );
			$out[ $k ] = $sub ? le_sync_walk( $sub, $v, $dir, $ctx ) : $v;
		}
		return $out;
	}
	if ( 'repeater' === $t && is_array( $value ) ) {
		$rows = array();
		foreach ( $value as $i => $row ) {
			if ( ! is_array( $row ) ) {
				$rows[ $i ] = $row;
				continue;
			}
			foreach ( $row as $k => $v ) {
				$sub        = le_sync_sub( $field['sub_fields'] ?? array(), $k );
				$row[ $k ]  = $sub ? le_sync_walk( $sub, $v, $dir, $ctx ) : $v;
			}
			$rows[ $i ] = $row;
		}
		return $rows;
	}
	if ( 'flexible_content' === $t && is_array( $value ) ) {
		$rows = array();
		foreach ( $value as $i => $row ) {
			$layout = null;
			foreach ( (array) ( $field['layouts'] ?? array() ) as $l ) {
				if ( ( $l['name'] ?? '' ) === ( $row['acf_fc_layout'] ?? '' ) ) {
					$layout = $l;
				}
			}
			foreach ( (array) $row as $k => $v ) {
				if ( 'acf_fc_layout' === $k || ! $layout ) {
					continue;
				}
				$sub       = le_sync_sub( $layout['sub_fields'] ?? array(), $k );
				$row[ $k ] = $sub ? le_sync_walk( $sub, $v, $dir, $ctx ) : $v;
			}
			$rows[ $i ] = $row;
		}
		return $rows;
	}
	return $value;
}

/** Raw ACF fields of a post (or 'options') as portable values, keyed by field name. */
function le_sync_fields( $post_id ) {
	$out = array();
	if ( ! function_exists( 'get_field_objects' ) ) {
		return $out;
	}
	$ctx = array();
	foreach ( (array) get_field_objects( $post_id, false ) as $name => $f ) {
		$out[ $name ] = array( 'key' => $f['key'], 'type' => $f['type'], 'v' => le_sync_walk( $f, $f['value'], 'out', $ctx ) );
	}
	return $out;
}

function le_sync_export( WP_REST_Request $req ) {
	$up  = wp_upload_dir();
	$dir = trailingslashit( $up['basedir'] ) . 'le-sync';
	foreach ( (array) glob( $dir . '/bundle-*.json' ) as $old ) {
		@unlink( $old ); // phpcs:ignore
	}
	if ( $req->get_param( 'cleanup' ) ) {
		return array( 'cleaned' => true );
	}
	$bundle = array( 'site' => home_url(), 'at' => gmdate( 'c' ), 'posts' => array(), 'options' => le_sync_fields( 'options' ), 'media' => array() );
	$q      = get_posts( array( 'post_type' => le_sync_types(), 'post_status' => array( 'publish', 'draft', 'private', 'pending' ), 'numberposts' => -1 ) );
	foreach ( $q as $p ) {
		$bundle['posts'][] = array(
			'type'     => $p->post_type,
			'slug'     => $p->post_name,
			'title'    => $p->post_title,
			'status'   => $p->post_status,
			'excerpt'  => $p->post_excerpt,
			'content'  => $p->post_content,
			'order'    => (int) $p->menu_order,
			'date'     => $p->post_date_gmt,
			'parent'   => $p->post_parent ? le_sync_postref( $p->post_parent ) : null,
			'template' => (string) get_post_meta( $p->ID, '_wp_page_template', true ),
			'featured' => le_sync_att( get_post_thumbnail_id( $p ) ),
			'seo'      => array(
				'title' => (string) get_post_meta( $p->ID, '_yoast_wpseo_title', true ),
				'desc'  => (string) get_post_meta( $p->ID, '_yoast_wpseo_metadesc', true ),
			),
			'terms'    => array_map( function ( $tax ) use ( $p ) {
				$ts = wp_get_object_terms( $p->ID, $tax, array( 'fields' => 'slugs' ) );
				return is_wp_error( $ts ) ? array() : $ts;
			}, array_combine( get_object_taxonomies( $p->post_type ), get_object_taxonomies( $p->post_type ) ) ),
			'fields'   => le_sync_fields( $p->ID ),
		);
	}
	foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'fields' => 'ids' ) ) as $aid ) {
		$a = le_sync_att( $aid );
		if ( $a ) {
			$bundle['media'][] = $a;
		}
	}
	wp_mkdir_p( $dir );
	file_put_contents( $dir . '/index.html', '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	$name = 'bundle-' . wp_generate_password( 24, false ) . '.json';
	file_put_contents( $dir . '/' . $name, wp_json_encode( $bundle ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	return array( 'url' => trailingslashit( $up['baseurl'] ) . 'le-sync/' . $name, 'posts' => count( $bundle['posts'] ), 'media' => count( $bundle['media'] ) );
}

/* ------------------------------------------------------------------ import */

/** Live attachment matching a staging one (same file name and dimensions). */
function le_sync_find_att( $a ) {
	global $wpdb;
	if ( empty( $a['file'] ) ) {
		return 0;
	}
	$base = $a['file'];
	$ids  = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND ( meta_value = %s OR meta_value LIKE %s )", $base, '%/' . $wpdb->esc_like( $base ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	foreach ( $ids as $id ) {
		$m = wp_get_attachment_metadata( (int) $id );
		if ( empty( $a['w'] ) || ( (int) ( $m['width'] ?? 0 ) === (int) $a['w'] && (int) ( $m['height'] ?? 0 ) === (int) $a['h'] ) ) {
			return (int) $id;
		}
	}
	return 0;
}

function le_sync_att_in( $a, &$ctx ) {
	if ( ! is_array( $a ) || empty( $a['__att'] ) ) {
		return $a;
	}
	$id = le_sync_find_att( $a );
	if ( $id ) {
		return $id;
	}
	if ( ! empty( $ctx['dry'] ) ) {
		$ctx['download'][ $a['file'] ] = 1;
		return 0;
	}
	if ( isset( $ctx['made'][ $a['file'] ] ) ) {
		return $ctx['made'][ $a['file'] ];
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$src = $a['orig'] ? $a['orig'] : $a['url'];
	$tmp = download_url( $src, 300 );
	if ( is_wp_error( $tmp ) ) {
		$ctx['errors'][] = $a['file'] . ': ' . $tmp->get_error_message();
		return 0;
	}
	$id = media_handle_sideload( array( 'name' => basename( (string) wp_parse_url( $src, PHP_URL_PATH ) ), 'tmp_name' => $tmp ), 0, $a['title'] );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore
		$ctx['errors'][] = $a['file'] . ': ' . $id->get_error_message();
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $a['alt'] );
	if ( $a['caption'] ) {
		wp_update_post( array( 'ID' => $id, 'post_excerpt' => $a['caption'] ) );
	}
	$ctx['made'][ $a['file'] ] = (int) $id;
	return (int) $id;
}

function le_sync_find_post( $type, $slug ) {
	$p = get_page_by_path( le_sync_slug_alias( $type, $slug ), OBJECT, $type );
	if ( ! $p ) {
		$ids = get_posts( array( 'post_type' => $type, 'name' => le_sync_slug_alias( $type, $slug ), 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
		$p   = $ids ? get_post( $ids[0] ) : null;
	}
	return $p;
}

function le_sync_post_in( $r, &$ctx ) {
	if ( ! is_array( $r ) || empty( $r['__post'] ) ) {
		return $r;
	}
	$p = le_sync_find_post( $r['type'], $r['slug'] );
	if ( ! $p ) {
		$ctx['missing_posts'][ $r['type'] . '/' . $r['slug'] ] = 1;
	}
	return $p ? (int) $p->ID : 0;
}

function le_sync_term_in( $r, &$ctx ) {
	if ( ! is_array( $r ) || empty( $r['__term'] ) ) {
		return $r;
	}
	$t = get_term_by( 'slug', $r['slug'], $r['tax'] );
	return $t ? (int) $t->term_id : 0;
}

/** Comparable signature of a portable value. */
function le_sync_sig( $v ) {
	if ( is_array( $v ) ) {
		if ( ! empty( $v['__att'] ) ) {
			return 'att:' . $v['file'] . ':' . $v['w'] . 'x' . $v['h'];
		}
		if ( ! empty( $v['__post'] ) ) {
			return 'post:' . $v['type'] . '/' . le_sync_slug_alias( $v['type'], $v['slug'] );
		}
		if ( ! empty( $v['__term'] ) ) {
			return 'term:' . $v['tax'] . '/' . $v['slug'];
		}
		$o = array();
		foreach ( $v as $k => $x ) {
			$o[ $k ] = le_sync_sig( $x );
		}
		return $o;
	}
	return null === $v || false === $v ? '' : (string) $v;
}

/** Loose text: case, dashes, quotes and spacing ignored (what the copy clean-up changes). */
function le_sync_loose( $s ) {
	$s = is_array( $s ) ? wp_json_encode( $s ) : (string) $s;
	$s = str_replace( array( '—', '–', '—', '–', '’', '’', '&ndash;', '&mdash;' ), array( '-', '-', '-', '-', "'", "'", '-', '-' ), $s );
	return strtolower( preg_replace( '/[\s\-]+/', ' ', $s ) );
}

function le_sync_brief( $s ) {
	$s = is_array( $s ) ? wp_json_encode( $s ) : (string) $s;
	$s = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $s ) ) );
	return mb_strlen( $s ) > 140 ? mb_substr( $s, 0, 140 ) . '…' : $s;
}

/** Classify one difference: same | case | diff. */
function le_sync_cmp( $a, $b ) {
	$sa = wp_json_encode( le_sync_sig( $a ) );
	$sb = wp_json_encode( le_sync_sig( $b ) );
	if ( $sa === $sb || ( in_array( $sa, array( '""', 'null', '[]' ), true ) && in_array( $sb, array( '""', 'null', '[]' ), true ) ) ) {
		return 'same';
	}
	return le_sync_loose( $sa ) === le_sync_loose( $sb ) ? 'case' : 'diff';
}

function le_sync_import( WP_REST_Request $req ) {
	@set_time_limit( 600 ); // phpcs:ignore
	$url = (string) $req->get_param( 'url' );
	if ( 'staging.lewisedward.com' !== wp_parse_url( $url, PHP_URL_HOST ) ) {
		return new WP_Error( 'le_sync_url', 'Bundle must come from staging.lewisedward.com', array( 'status' => 400 ) );
	}
	$res = wp_remote_get( $url, array( 'timeout' => 120 ) );
	$b   = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $b ) || empty( $b['posts'] ) ) {
		return new WP_Error( 'le_sync_bundle', 'Could not read the bundle', array( 'status' => 400 ) );
	}
	$dry = (bool) $req->get_param( 'dry' );
	$ctx = array( 'dry' => $dry, 'download' => array(), 'errors' => array(), 'made' => array(), 'missing_posts' => array() );

	if ( $dry ) {
		$report = array( 'create' => array(), 'posts' => array(), 'options' => array(), 'alts' => array() );
		foreach ( $b['posts'] as $sp ) {
			$lp = le_sync_find_post( $sp['type'], $sp['slug'] );
			$id = $sp['type'] . '/' . $sp['slug'];
			if ( ! $lp ) {
				$report['create'][] = $id . ' (' . $sp['status'] . ')';
				continue;
			}
			$d = array();
			if ( $sp['status'] !== $lp->post_status ) {
				$d['status'] = $lp->post_status . ' → ' . $sp['status'];
			}
			foreach ( array( 'title' => 'post_title', 'excerpt' => 'post_excerpt', 'content' => 'post_content' ) as $k => $col ) {
				$c = le_sync_cmp( $lp->$col, $sp[ $k ] );
				if ( 'same' !== $c ) {
					$d[ $k ] = 'diff' === $c ? array( 'live' => le_sync_brief( $lp->$col ), 'staging' => le_sync_brief( $sp[ $k ] ) ) : 'case';
				}
			}
			$lf = le_sync_att( get_post_thumbnail_id( $lp ) );
			if ( 'same' !== le_sync_cmp( $lf, $sp['featured'] ) ) {
				$d['featured'] = ( $lf['file'] ?? '-' ) . ' → ' . ( $sp['featured']['file'] ?? '-' );
			}
			$live = le_sync_fields( $lp->ID );
			foreach ( $sp['fields'] as $name => $f ) {
				$lv = $live[ $name ]['v'] ?? null;
				$c  = le_sync_cmp( $lv, $f['v'] );
				if ( 'same' === $c ) {
					continue;
				}
				$d[ 'f:' . $name ] = 'case' === $c ? 'case' : array( 'live' => le_sync_brief( le_sync_sig( $lv ) ), 'staging' => le_sync_brief( le_sync_sig( $f['v'] ) ) );
				le_sync_walk( array( 'type' => $f['type'] ) + ( function_exists( 'acf_get_field' ) ? (array) acf_get_field( $f['key'] ) : array() ), $f['v'], 'in', $ctx );
			}
			if ( $d ) {
				$report['posts'][ $id ] = $d;
			}
		}
		$lo = le_sync_fields( 'options' );
		foreach ( (array) $b['options'] as $name => $f ) {
			$c = le_sync_cmp( $lo[ $name ]['v'] ?? null, $f['v'] );
			if ( 'same' !== $c ) {
				$report['options'][ $name ] = 'case' === $c ? 'case' : array( 'live' => le_sync_brief( le_sync_sig( $lo[ $name ]['v'] ?? null ) ), 'staging' => le_sync_brief( le_sync_sig( $f['v'] ) ) );
			}
		}
		$n = 0;
		foreach ( $b['media'] as $a ) {
			$lid = le_sync_find_att( $a );
			if ( $lid && $a['alt'] && $a['alt'] !== (string) get_post_meta( $lid, '_wp_attachment_image_alt', true ) ) {
				$n++;
				if ( count( $report['alts'] ) < 12 ) {
					$report['alts'][] = $a['file'] . ': "' . get_post_meta( $lid, '_wp_attachment_image_alt', true ) . '" → "' . $a['alt'] . '"';
				}
			}
		}
		$report['alts_total']    = $n;
		$report['would_download'] = array_keys( $ctx['download'] );
		$report['missing_posts']  = array_keys( $ctx['missing_posts'] );
		return $report;
	}

	// Apply.
	$done  = array();
	$index = array();
	foreach ( $b['posts'] as $sp ) {
		$index[ $sp['type'] . '/' . $sp['slug'] ] = $sp;
	}
	foreach ( (array) $req->get_param( 'ops' ) as $op ) {
		$key = ( $op['type'] ?? '' ) . '/' . ( $op['slug'] ?? '' );
		$sp  = $index[ $key ] ?? null;
		if ( ! $sp ) {
			$ctx['errors'][] = $key . ': not in bundle';
			continue;
		}
		$lp = le_sync_find_post( $sp['type'], $sp['slug'] );
		if ( ! $lp && ! empty( $op['create'] ) ) {
			$new = wp_insert_post( array(
				'post_type'    => $sp['type'],
				'post_name'    => $sp['slug'],
				'post_title'   => $sp['title'],
				'post_status'  => $sp['status'],
				'post_excerpt' => $sp['excerpt'],
				'post_content' => $sp['content'],
				'menu_order'   => $sp['order'],
				'post_date_gmt' => $sp['date'],
			), true );
			if ( is_wp_error( $new ) ) {
				$ctx['errors'][] = $key . ': ' . $new->get_error_message();
				continue;
			}
			foreach ( (array) $sp['terms'] as $tax => $slugs ) {
				wp_set_object_terms( $new, $slugs, $tax );
			}
			if ( $sp['template'] ) {
				update_post_meta( $new, '_wp_page_template', $sp['template'] );
			}
			foreach ( array( 'title' => '_yoast_wpseo_title', 'desc' => '_yoast_wpseo_metadesc' ) as $k => $mk ) {
				if ( ! empty( $sp['seo'][ $k ] ) ) {
					update_post_meta( $new, $mk, $sp['seo'][ $k ] );
				}
			}
			$lp          = get_post( $new );
			$op['fields'] = 'all';
			$op['featured'] = true;
			$done[]      = 'created ' . $key;
		}
		if ( ! $lp ) {
			$ctx['errors'][] = $key . ': not on live';
			continue;
		}
		if ( ! empty( $op['status'] ) && in_array( $op['status'], array( 'draft', 'publish' ), true ) ) {
			wp_update_post( array( 'ID' => $lp->ID, 'post_status' => $op['status'] ) );
			$done[] = $key . ' status ' . $op['status'];
		}
		if ( ! empty( $op['featured'] ) && $sp['featured'] ) {
			$aid = le_sync_att_in( $sp['featured'], $ctx );
			if ( $aid ) {
				set_post_thumbnail( $lp->ID, $aid );
				$done[] = $key . ' featured';
			}
		}
		$names = 'all' === ( $op['fields'] ?? '' ) ? array_keys( $sp['fields'] ) : (array) ( $op['fields'] ?? array() );
		foreach ( $names as $name ) {
			if ( ! isset( $sp['fields'][ $name ] ) ) {
				continue;
			}
			$f   = $sp['fields'][ $name ];
			$def = function_exists( 'acf_get_field' ) ? acf_get_field( $f['key'] ) : null;
			if ( ! $def ) {
				$ctx['errors'][] = $key . ': no field ' . $name;
				continue;
			}
			update_field( $f['key'], le_sync_walk( $def, $f['v'], 'in', $ctx ), $lp->ID );
			$done[] = $key . ' ' . $name;
		}
	}
	foreach ( (array) $req->get_param( 'options' ) as $name ) {
		$f   = $b['options'][ $name ] ?? null;
		$def = ( $f && function_exists( 'acf_get_field' ) ) ? acf_get_field( $f['key'] ) : null;
		if ( $def ) {
			update_field( $f['key'], le_sync_walk( $def, $f['v'], 'in', $ctx ), 'options' );
			$done[] = 'options ' . $name;
		}
	}
	if ( $req->get_param( 'alts' ) ) {
		$n = 0;
		foreach ( $b['media'] as $a ) {
			$lid = le_sync_find_att( $a );
			if ( $lid && $a['alt'] && $a['alt'] !== (string) get_post_meta( $lid, '_wp_attachment_image_alt', true ) ) {
				update_post_meta( $lid, '_wp_attachment_image_alt', $a['alt'] );
				$n++;
			}
		}
		$done[] = 'alts ' . $n;
	}
	return array( 'done' => $done, 'downloaded' => $ctx['made'], 'errors' => $ctx['errors'], 'missing_posts' => array_keys( $ctx['missing_posts'] ) );
}

add_action( 'rest_api_init', function () {
	$admin = function () {
		return current_user_can( 'manage_options' );
	};
	register_rest_route( 'le/v1', '/sync-export', array( 'methods' => 'POST', 'permission_callback' => $admin, 'callback' => 'le_sync_export' ) );
	register_rest_route( 'le/v1', '/sync-import', array( 'methods' => 'POST', 'permission_callback' => $admin, 'callback' => 'le_sync_import' ) );
} );
