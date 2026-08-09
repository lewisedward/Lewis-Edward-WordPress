<?php
/**
 * SVG upload support (safe).
 *
 * WordPress blocks SVGs by default and, once allowed, treats them as raster
 * images it can't read — producing "this file cannot be processed" on upload
 * and "File must be a valid image" in ACF image fields. This module makes SVGs
 * first-class, safely:
 *
 *   1. Allows the SVG mime type (for users who can upload files).
 *   2. Fixes WP's real-file-type check so SVGs aren't rejected.
 *   3. Sanitises every uploaded SVG (removes <script>, event handlers,
 *      javascript: URIs, external entities/DOCTYPE, etc.) before storing.
 *   4. Gives SVG attachments real width/height (from width/height or the
 *      viewBox) via attachment metadata + the media-JS + image src filters, so
 *      WordPress and ACF stop treating them as "not a valid image".
 *   5. Shows SVG thumbnails in the Media Library.
 *
 * For enterprise-grade sanitising, the "Safe SVG" plugin (bundling
 * enshrined/svg-sanitize) is recommended; this is a solid self-contained base.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allow the SVG mime type on upload (only for users who can upload files).
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
function le_allow_svg_mime( $mimes ) {
	if ( ! current_user_can( 'upload_files' ) ) {
		return $mimes;
	}
	$mimes['svg']  = 'image/svg';
	$mimes['svgz'] = 'image/svg';
	return $mimes;
}
add_filter( 'upload_mimes', 'le_allow_svg_mime' );

/**
 * Correct WP's file-type/ext detection for SVG so the upload isn't rejected.
 *
 * @param array  $data     ext/type/proper_filename.
 * @param string $file     Full path to the file.
 * @param string $filename The name of the file.
 * @param array  $mimes    Allowed mimes.
 * @return array
 */
function le_fix_svg_filetype( $data, $file, $filename, $mimes ) {
	if ( preg_match( '/\.svgz?$/i', $filename ) ) {
		$data['type']            = 'image/svg';
		$data['ext']             = preg_match( '/\.svgz$/i', $filename ) ? 'svgz' : 'svg';
		$data['proper_filename'] = $filename; // Prevent WP renaming/rejecting on mime mismatch.
	}
	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'le_fix_svg_filetype', 10, 4 );

/**
 * Read an SVG's intrinsic dimensions from width/height or viewBox.
 *
 * @param string $path Absolute path to the SVG file.
 * @return array{width:int,height:int}
 */
function le_svg_dimensions( $path ) {
	$w = 0;
	$h = 0;
	if ( $path && file_exists( $path ) ) {
		$prev = libxml_use_internal_errors( true );
		$svg  = simplexml_load_file( $path );
		libxml_clear_errors();
		libxml_use_internal_errors( $prev );
		if ( false !== $svg ) {
			$attr = $svg->attributes();
			if ( isset( $attr->width, $attr->height ) && (float) $attr->width && (float) $attr->height ) {
				$w = (float) $attr->width;
				$h = (float) $attr->height;
			} elseif ( isset( $attr->viewBox ) ) {
				$vb = preg_split( '/[\s,]+/', trim( (string) $attr->viewBox ) );
				if ( 4 === count( $vb ) ) {
					$w = (float) $vb[2];
					$h = (float) $vb[3];
				}
			}
		}
	}
	if ( ! $w || ! $h ) {
		$w = 300;
		$h = 300;
	}
	return array(
		'width'  => (int) round( $w ),
		'height' => (int) round( $h ),
	);
}

/**
 * Sanitise an SVG string. Returns cleaned SVG markup, or false if unsafe/invalid.
 *
 * @param string $svg Raw SVG contents.
 * @return string|false
 */
function le_sanitize_svg_string( $svg ) {
	if ( '' === trim( $svg ) || false === stripos( $svg, '<svg' ) ) {
		return false;
	}

	$svg = preg_replace( '/<\?php.*?\?>/is', '', $svg );
	$svg = preg_replace( '/<!DOCTYPE.*?>/is', '', $svg );
	$svg = preg_replace( '/<!ENTITY.*?>/is', '', $svg );
	$svg = str_replace( "\0", '', $svg );

	if ( ! class_exists( 'DOMDocument' ) ) {
		return false;
	}

	$prev = libxml_use_internal_errors( true );
	if ( function_exists( 'libxml_disable_entity_loader' ) && PHP_VERSION_ID < 80000 ) {
		@libxml_disable_entity_loader( true ); // phpcs:ignore
	}

	$dom = new DOMDocument();
	if ( ! $dom->loadXML( $svg, LIBXML_NONET ) ) {
		libxml_clear_errors();
		libxml_use_internal_errors( $prev );
		return false;
	}

	$bad_tags = array( 'script', 'foreignObject', 'iframe', 'embed', 'object', 'audio', 'video', 'set', 'animate', 'animatetransform', 'handler', 'listener' );
	foreach ( $bad_tags as $tag ) {
		$nodes = $dom->getElementsByTagName( $tag );
		for ( $i = $nodes->length - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );
			if ( $node && $node->parentNode ) {
				$node->parentNode->removeChild( $node );
			}
		}
	}

	$xpath = new DOMXPath( $dom );
	$all   = $xpath->query( '//*' );
	if ( $all ) {
		foreach ( $all as $el ) {
			if ( ! $el->hasAttributes() ) {
				continue;
			}
			for ( $i = $el->attributes->length - 1; $i >= 0; $i-- ) {
				$attr  = $el->attributes->item( $i );
				$name  = strtolower( $attr->nodeName );
				$value = trim( $attr->nodeValue );
				if ( 0 === strpos( $name, 'on' ) ) {
					$el->removeAttribute( $attr->nodeName );
					continue;
				}
				if ( in_array( $name, array( 'href', 'xlink:href', 'src' ), true ) ) {
					if ( preg_match( '/^\s*(javascript:|data:|blob:|vbscript:)/i', $value ) ) {
						$el->removeAttribute( $attr->nodeName );
						continue;
					}
				}
				if ( 'style' === $name && preg_match( '/(expression\s*\(|url\s*\(\s*[\'"]?\s*javascript:)/i', $value ) ) {
					$el->removeAttribute( $attr->nodeName );
				}
			}
		}
	}

	$clean = $dom->saveXML( $dom->documentElement, LIBXML_NOEMPTYTAG );
	libxml_clear_errors();
	libxml_use_internal_errors( $prev );
	return $clean ? $clean : false;
}

/**
 * Sanitise SVGs at upload time; reject if they can't be cleaned.
 *
 * @param array $file $_FILES entry being uploaded.
 * @return array
 */
function le_sanitize_svg_on_upload( $file ) {
	$type   = isset( $file['type'] ) ? $file['type'] : '';
	$name   = isset( $file['name'] ) ? $file['name'] : '';
	$is_svg = ( 'image/svg' === $type ) || preg_match( '/\.svgz?$/i', $name );
	if ( ! $is_svg ) {
		return $file;
	}
	if ( ! current_user_can( 'upload_files' ) ) {
		$file['error'] = __( 'You are not allowed to upload SVG files.', 'lewisedward' );
		return $file;
	}
	if ( empty( $file['tmp_name'] ) || ! file_exists( $file['tmp_name'] ) ) {
		return $file;
	}

	$raw = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( preg_match( '/\.svgz$/i', $name ) && function_exists( 'gzdecode' ) ) {
		$maybe = @gzdecode( $raw ); // phpcs:ignore
		if ( false !== $maybe ) {
			$raw = $maybe;
		}
	}

	$clean = le_sanitize_svg_string( $raw );
	if ( false === $clean ) {
		$file['error'] = __( 'This SVG could not be sanitised and was not uploaded.', 'lewisedward' );
		return $file;
	}

	file_put_contents( $file['tmp_name'], $clean ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'le_sanitize_svg_on_upload' );

/**
 * Give SVG attachments real metadata dimensions so WordPress stops treating
 * them as invalid images (this is the key fix for the upload-time error).
 *
 * @param array $metadata      Generated metadata (usually empty for SVG).
 * @param int   $attachment_id Attachment ID.
 * @return array
 */
function le_svg_generate_metadata( $metadata, $attachment_id ) {
	if ( 'image/svg' !== get_post_mime_type( $attachment_id ) ) {
		return $metadata;
	}
	$path = get_attached_file( $attachment_id );
	$dim  = le_svg_dimensions( $path );
	return array(
		'width'  => $dim['width'],
		'height' => $dim['height'],
		'file'   => _wp_relative_upload_path( $path ),
		'sizes'  => array(),
	);
}
add_filter( 'wp_generate_attachment_metadata', 'le_svg_generate_metadata', 10, 2 );

/**
 * Provide a valid src array for SVGs so image_downsize()/wp_get_attachment_image
 * return the file instead of false.
 *
 * @param array|false  $image Image data or false.
 * @param int          $id    Attachment ID.
 * @param string|array $size  Requested size.
 * @return array|false
 */
function le_svg_image_downsize( $image, $id, $size ) {
	if ( 'image/svg' !== get_post_mime_type( $id ) ) {
		return $image;
	}
	$url = wp_get_attachment_url( $id );
	$dim = le_svg_dimensions( get_attached_file( $id ) );
	return array( $url, $dim['width'], $dim['height'], false );
}
add_filter( 'image_downsize', 'le_svg_image_downsize', 10, 3 );

/**
 * Give SVGs sensible dimensions and a preview in the Media Library / ACF.
 *
 * @param array   $response   Attachment data for JS.
 * @param WP_Post $attachment Attachment post.
 * @return array
 */
function le_svg_media_dimensions( $response, $attachment ) {
	if ( 'image/svg' !== get_post_mime_type( $attachment ) ) {
		return $response;
	}
	$url = wp_get_attachment_url( $attachment->ID );
	$dim = le_svg_dimensions( get_attached_file( $attachment->ID ) );

	$response['url']         = $url;
	$response['icon']        = $url;
	$response['width']       = $dim['width'];
	$response['height']      = $dim['height'];
	$response['sizes']       = array(
		'full'      => array(
			'url'         => $url,
			'width'       => $dim['width'],
			'height'      => $dim['height'],
			'orientation' => $dim['width'] >= $dim['height'] ? 'landscape' : 'portrait',
		),
		'thumbnail' => array(
			'url'         => $url,
			'width'       => $dim['width'],
			'height'      => $dim['height'],
			'orientation' => $dim['width'] >= $dim['height'] ? 'landscape' : 'portrait',
		),
	);
	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'le_svg_media_dimensions', 10, 2 );

/**
 * Allow SVGs to pass ACF image-field validation.
 *
 * @param array $errors     Validation errors.
 * @param array $file       Attachment data (acf_get_attachment result).
 * @param mixed $attachment Raw attachment array / ID.
 * @param array $field      ACF field.
 * @param mixed $context    Validation context.
 * @return array
 */
function le_acf_allow_svg_attachment( $errors, $file, $attachment, $field, $context ) {
	$mime = '';
	if ( is_array( $file ) ) {
		if ( ! empty( $file['mime_type'] ) ) {
			$mime = $file['mime_type'];
		} elseif ( ! empty( $file['type'] ) ) {
			$mime = $file['type'];
		}
	}
	if ( false === strpos( (string) $mime, 'svg' ) ) {
		$id = 0;
		if ( is_array( $attachment ) ) {
			$id = isset( $attachment['ID'] ) ? (int) $attachment['ID'] : ( isset( $attachment['id'] ) ? (int) $attachment['id'] : 0 );
		} elseif ( is_numeric( $attachment ) ) {
			$id = (int) $attachment;
		}
		if ( $id && 'image/svg' === get_post_mime_type( $id ) ) {
			$mime = 'image/svg';
		}
	}
	if ( false !== strpos( (string) $mime, 'svg' ) && is_array( $errors ) ) {
		foreach ( array( 'type', 'mime_types', 'width', 'height', 'min_width', 'min_height', 'max_width', 'max_height', 'min_size', 'max_size' ) as $key ) {
			unset( $errors[ $key ] );
		}
	}
	return $errors;
}
add_filter( 'acf/validate_attachment', 'le_acf_allow_svg_attachment', 20, 5 );

/**
 * Make SVG thumbnails visible in the Media Library grid/list.
 */
function le_svg_admin_thumbnail_css() {
	echo '<style>
		.attachment .thumbnail img[src$=".svg"],
		.media-icon img[src$=".svg"],
		.attachment-info .thumbnail img[src$=".svg"],
		.media-frame img[src$=".svg"],
		img.attachment-thumb[src$=".svg"] { width: 100% !important; height: auto !important; }
	</style>';
}
add_action( 'admin_head', 'le_svg_admin_thumbnail_css' );
