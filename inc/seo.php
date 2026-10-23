<?php
/**
 * SEO polish on top of Yoast: archive titles and descriptions, a default share
 * image, and keeping the author archive out of the index.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The term behind a category / work-category archive, if this is one. */
function le_seo_archive_term() {
	if ( is_category() || is_tax( 'work_category' ) ) {
		$t = get_queried_object();
		return $t instanceof WP_Term ? $t : null;
	}
	return null;
}

/** Archive titles: "Web design articles | Lewis Edward", "SaaS projects | WordPress portfolio | Lewis Edward". */
add_filter( 'wpseo_title', function ( $title ) {
	$t = le_seo_archive_term();
	if ( ! $t ) {
		return $title;
	}
	$name = wp_strip_all_tags( $t->name );
	return is_category()
		? sprintf( '%s articles | WordPress journal | Lewis Edward', $name )
		: sprintf( '%s projects | WordPress portfolio | Lewis Edward', $name );
} );

/** Archive descriptions: the term description when written, otherwise a sensible default. */
add_filter( 'wpseo_metadesc', function ( $desc ) {
	$t = le_seo_archive_term();
	if ( ! $t || '' !== trim( (string) $desc ) ) {
		return $desc;
	}
	$own = trim( wp_strip_all_tags( (string) $t->description ) );
	if ( '' !== $own ) {
		return $own;
	}
	$name = function_exists( 'mb_strtolower' ) && ! in_array( $t->name, array( 'AI', 'SaaS' ), true ) ? mb_strtolower( $t->name ) : $t->name;
	return is_category()
		? sprintf( 'Articles on %s from Lewis Edward, a London WordPress web designer and developer: practical guides, comparisons and honest advice.', $name )
		: sprintf( 'Selected %s projects by Lewis Edward, a London WordPress web designer and developer, with case studies for brands and agencies.', $name );
} );

/** The author archive duplicates the journal: keep it out of the index and the sitemap. */
add_filter( 'wpseo_robots', function ( $robots ) {
	return is_author() ? 'noindex, follow' : $robots;
} );
add_filter( 'wpseo_sitemap_exclude_author', '__return_empty_array' );

/** Default share image for pages without a featured image of their own. */
add_action( 'wpseo_add_opengraph_additional_images', function ( $images ) {
	if ( is_object( $images ) && method_exists( $images, 'has_images' ) && ! $images->has_images() && method_exists( $images, 'add_image' ) ) {
		$images->add_image( array(
			'url'    => get_theme_file_uri( 'assets/images/brand/le-share-1200x630.png' ),
			'width'  => 1200,
			'height' => 630,
			'type'   => 'image/png',
			'alt'    => 'Lewis Edward, London WordPress web designer & developer',
		) );
	}
} );
