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

/** A UK business: Open Graph locale is en_GB, not WordPress's en_US default. */
add_filter( 'wpseo_locale', function () {
	return 'en_GB';
} );

/** The author archive duplicates the journal: keep it out of the index and the sitemap. */
add_filter( 'wpseo_robots', function ( $robots ) {
	return is_author() ? 'noindex, follow' : $robots;
} );
add_filter( 'wpseo_sitemap_exclude_author', '__return_empty_array' );

/**
 * Category and work-category archives are navigation, not landing pages: they
 * sat in Search Console's "Discovered, not indexed" bucket competing for crawl
 * with the pages that matter. Keep them crawlable (follow) but out of the index
 * and the sitemap.
 */
add_filter( 'wpseo_robots', function ( $robots ) {
	return ( is_category() || is_tax( 'work_category' ) ) ? 'noindex, follow' : $robots;
} );
add_filter( 'wpseo_sitemap_exclude_taxonomy', function ( $exclude, $taxonomy ) {
	return in_array( $taxonomy, array( 'category', 'work_category' ), true ) ? true : $exclude;
}, 10, 2 );

/**
 * WordPress 6.8+ prints a speculationrules script whose exclusion patterns
 * (/wp-*.php, /wp-admin/*, /wp-content/*, /*) Googlebot reads as URLs and
 * crawls, filling Search Console's 404 report. The prefetch gain is marginal
 * behind LiteSpeed and Cloudflare, so the script is not printed at all.
 */
add_filter( 'wp_speculation_rules_configuration', '__return_null' );

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

/**
 * Internal menu links with a trailing slash. Menu items saved in wp-admin as
 * /services, /work, /privacy etc. otherwise point at a 301 on every page.
 */
add_filter( 'nav_menu_link_attributes', function ( $atts ) {
	if ( empty( $atts['href'] ) ) {
		return $atts;
	}
	$home = untrailingslashit( home_url() );
	$href = (string) $atts['href'];
	if ( 0 === strpos( $href, '/' ) && 0 !== strpos( $href, '//' ) ) {
		$href = $home . $href;
	}
	if ( 0 !== strpos( $href, $home . '/' ) ) {
		return $atts; // External, mailto:, anchors.
	}
	$parts = wp_parse_url( $href );
	$path  = isset( $parts['path'] ) ? $parts['path'] : '/';
	if ( '/privacy' === untrailingslashit( $path ) ) {
		$path = '/privacy-policy/';
	}
	if ( '/' !== substr( $path, -1 ) && ! preg_match( '/\.[a-z0-9]{2,5}$/i', $path ) ) {
		$path .= '/';
	}
	$atts['href'] = $home . $path
		. ( isset( $parts['query'] ) ? '?' . $parts['query'] : '' )
		. ( isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '' );
	return $atts;
} );

/**
 * Tell Cloudflare that public pages for anonymous visitors may be cached at
 * the edge for 2 hours. Paired with the Cloudflare cache rule set to "use
 * cache-control header if present", so responses that say no-store (the
 * host's bot-protection challenge page, previews, logged-in views) are never
 * cached and served to everyone.
 */
add_action( 'template_redirect', function () {
	if ( headers_sent() || is_admin() || is_user_logged_in() || is_404() || is_search() || is_preview() ) {
		return;
	}
	if ( function_exists( 'le_is_staging_host' ) && le_is_staging_host() ) {
		return;
	}
	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) : 'GET';
	if ( 'GET' !== $method && 'HEAD' !== $method ) {
		return;
	}
	if ( ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return; // Previews, ?skin=, search, form postbacks.
	}
	header( 'Cache-Control: public, max-age=0, s-maxage=7200' );
}, 99 );
