<?php
/**
 * Structured data for the homepage: a ProfessionalService entity with Lewis
 * as its founder (Person), the London service area and the live service
 * catalogue. Complements the SEO plugin's WebSite / WebPage graph, which has
 * no business entity, and prints on both skins because it hooks wp_head.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the ProfessionalService graph for the front page.
 *
 * @return array<string,mixed>
 */
function le_front_page_schema() {
	$home     = home_url( '/' );
	$front_id = (int) get_option( 'page_on_front' );
	$name     = get_bloginfo( 'name' ) ?: 'Lewis Edward';

	$lede = le_field( 'home_hero_lede', $front_id );
	$lede = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $lede ) ) );
	if ( ! $lede ) {
		$lede = __( 'London WordPress web designer & developer. Bespoke websites, designed and built by the same pair of hands.', 'lewisedward' );
	}

	$portrait = le_field( 'home_about_portrait', $front_id );
	$portrait = ( is_array( $portrait ) && ! empty( $portrait['url'] ) ) ? $portrait['url'] : LE_URI . '/assets/images/lewis-wordpress-developer.jpg';
	$logo     = LE_URI . '/assets/images/lewis-edward-logo.webp';
	$linkedin = le_option( 'social_linkedin', 'https://www.linkedin.com/in/lewis-edward/' );
	$est      = (int) le_option( 'footer_established', 2011 );
	$est      = $est > 1990 ? $est : 2011;

	$person_id  = $home . '#lewis-edward';
	$service_id = $home . '#studio';

	$offers = array();
	$ids    = get_posts( array(
		'post_type'      => 'service',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );
	foreach ( $ids as $sid ) {
		$desc     = le_field( 'service_short_description', $sid );
		$offers[] = array_filter( array(
			'@type'       => 'Offer',
			'itemOffered' => array_filter( array(
				'@type'       => 'Service',
				'name'        => get_the_title( $sid ),
				'url'         => get_permalink( $sid ),
				'description' => $desc ? wp_strip_all_tags( (string) $desc ) : null,
				'provider'    => array( '@id' => $service_id ),
				'areaServed'  => 'London',
			) ),
		) );
	}

	$person = array(
		'@type'     => 'Person',
		'@id'       => $person_id,
		'name'      => 'Lewis Edward',
		'jobTitle'  => 'WordPress Web Designer & Developer',
		'url'       => home_url( '/about/' ),
		'image'     => $portrait,
		'worksFor'  => array( '@id' => $service_id ),
		'sameAs'    => array_values( array_filter( array( $linkedin ) ) ),
		'knowsAbout' => array( 'WordPress', 'Web design', 'Web development', 'WooCommerce', 'Website support', 'AI development' ),
		'address'   => array( '@type' => 'PostalAddress', 'addressLocality' => 'London', 'addressCountry' => 'GB' ),
	);

	$studio = array(
		'@type'        => array( 'ProfessionalService', 'LocalBusiness' ),
		'@id'          => $service_id,
		'name'         => $name,
		'alternateName' => 'Lewis Edward — London WordPress web designer & developer',
		'url'          => $home,
		'description'  => $lede,
		'image'        => $portrait,
		'logo'         => $logo,
		'email'        => 'hello@lewisedward.com',
		'foundingDate' => (string) $est,
		'founder'      => array( '@id' => $person_id ),
		'employee'     => array( '@id' => $person_id ),
		'address'      => array( '@type' => 'PostalAddress', 'addressLocality' => 'London', 'addressCountry' => 'GB' ),
		'areaServed'   => array(
			array( '@type' => 'City', 'name' => 'London' ),
			array( '@type' => 'Country', 'name' => 'United Kingdom' ),
		),
		'priceRange'   => '££',
		'sameAs'       => array_values( array_filter( array( $linkedin ) ) ),
		'knowsAbout'   => array( 'WordPress web design', 'WordPress web development', 'WooCommerce', 'WordPress website support', 'AI development', 'Webflow', 'Web hosting' ),
	);
	if ( $offers ) {
		$studio['hasOfferCatalog'] = array(
			'@type'           => 'OfferCatalog',
			'name'            => __( 'WordPress services', 'lewisedward' ),
			'itemListElement' => $offers,
		);
	}

	return array(
		'@context' => 'https://schema.org',
		'@graph'   => array( $studio, $person ),
	);
}

/**
 * With Yoast active, fold the studio into Yoast's own Organization node (so
 * there is one business entity, not two) and add the Person node to its graph.
 *
 * @param array<int,array<string,mixed>> $graph Yoast graph pieces.
 * @return array<int,array<string,mixed>>
 */
function le_merge_schema_into_yoast( $graph ) {
	if ( ! is_front_page() || ! is_array( $graph ) ) {
		return $graph;
	}
	$ours   = le_front_page_schema();
	$studio = $ours['@graph'][0];
	$person = $ours['@graph'][1];
	$org_id = null;

	foreach ( $graph as $i => $node ) {
		$types = (array) ( $node['@type'] ?? array() );
		if ( in_array( 'Organization', $types, true ) ) {
			$org_id = $node['@id'] ?? $studio['@id'];
			$merged = $node;
			foreach ( $studio as $k => $v ) {
				if ( in_array( $k, array( '@type', '@id', 'name', 'url', 'logo', 'image' ), true ) ) {
					continue; // Yoast owns identity and the logo ImageObject.
				}
				if ( 'sameAs' === $k ) {
					$merged['sameAs'] = array_values( array_unique( array_merge( (array) ( $node['sameAs'] ?? array() ), (array) $v ) ) );
					continue;
				}
				if ( ! isset( $merged[ $k ] ) ) {
					$merged[ $k ] = $v;
				}
			}
			$merged['@type'] = array_values( array_unique( array_merge( $types, array( 'ProfessionalService', 'LocalBusiness' ) ) ) );
			if ( ! empty( $merged['hasOfferCatalog']['itemListElement'] ) ) {
				foreach ( $merged['hasOfferCatalog']['itemListElement'] as &$offer ) {
					if ( isset( $offer['itemOffered']['provider'] ) ) {
						$offer['itemOffered']['provider'] = array( '@id' => $org_id );
					}
				}
				unset( $offer );
			}
			$merged['founder'] = array( '@id' => $person['@id'] );
			$merged['employee'] = array( '@id' => $person['@id'] );
			$graph[ $i ] = $merged;
			break;
		}
	}
	if ( ! $org_id ) {
		$graph[] = $studio;
		$org_id  = $studio['@id'];
	}
	$person['worksFor'] = array( '@id' => $org_id );
	$graph[]            = $person;
	return $graph;
}

/**
 * Print the graph in the head of the front page (standalone fallback when
 * Yoast is not there to carry it).
 */
function le_print_front_page_schema() {
	if ( ! is_front_page() || is_admin() || defined( 'WPSEO_VERSION' ) ) {
		return;
	}
	$graph = apply_filters( 'le_front_page_schema', le_front_page_schema() );
	if ( ! $graph ) {
		return;
	}
	echo '<script type="application/ld+json" id="le-schema-studio">' . wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'le_print_front_page_schema', 5 );
add_filter( 'wpseo_schema_graph', 'le_merge_schema_into_yoast', 20 );
