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
				'@id'         => get_permalink( $sid ) . '#service',
				'name'        => html_entity_decode( wp_strip_all_tags( get_post_field( 'post_title', $sid ) ), ENT_QUOTES, 'UTF-8' ),
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
		'legalName'    => 'Lewis Edward Ltd',
		'email'        => 'hello@lewisedward.com',
		'foundingDate' => (string) $est,
		'founder'      => array( '@id' => $person_id ),
		'employee'     => array( '@id' => $person_id ),
		'address'      => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => '71-75 Shelton Street',
			'addressLocality' => 'London',
			'addressRegion'   => 'Greater London',
			'postalCode'      => 'WC2H 9JQ',
			'addressCountry'  => 'GB',
		),
		'openingHoursSpecification' => array(
			array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ),
				'opens'     => '08:00',
				'closes'    => '20:00',
			),
		),
		'areaServed'   => array(
			array( '@type' => 'City', 'name' => 'London' ),
			array( '@type' => 'Country', 'name' => 'United Kingdom' ),
		),
		'priceRange'   => '££',
		'sameAs'       => array_values( array_filter( array( $linkedin, 'https://find-and-update.company-information.service.gov.uk/company/11928542' ) ) ),
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

/**
 * Service pages: add a Service node to Yoast's graph and point the page's
 * WebPage node at it, so each service page names what it is about and who
 * provides it. The @id matches the homepage OfferCatalog item for the same
 * service, so the two reference one entity.
 *
 * @param array<int,array<string,mixed>> $graph Yoast graph pieces.
 * @return array<int,array<string,mixed>>
 */
function le_service_page_schema( $graph ) {
	if ( ! is_singular( 'service' ) || ! is_array( $graph ) ) {
		return $graph;
	}
	$sid = get_queried_object_id();
	if ( ! $sid ) {
		return $graph;
	}

	$org_id  = null;
	$page_id = null;
	foreach ( $graph as $node ) {
		$types = (array) ( $node['@type'] ?? array() );
		if ( ! $org_id && in_array( 'Organization', $types, true ) ) {
			$org_id = $node['@id'] ?? null;
		}
		if ( ! $page_id && in_array( 'WebPage', $types, true ) ) {
			$page_id = $node['@id'] ?? null;
		}
	}
	if ( ! $org_id ) {
		$org_id = home_url( '/' ) . '#organization';
	}

	$url  = get_permalink( $sid );
	$desc = le_field( 'service_short_description', $sid );
	if ( ! $desc ) {
		$desc = le_field( 'service_excerpt', $sid );
	}
	$desc = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $desc ) ) );

	$service = array_filter( array(
		'@type'            => 'Service',
		'@id'              => $url . '#service',
		'name'             => html_entity_decode( wp_strip_all_tags( get_post_field( 'post_title', $sid ) ), ENT_QUOTES, 'UTF-8' ),
		'serviceType'      => html_entity_decode( wp_strip_all_tags( get_post_field( 'post_title', $sid ) ), ENT_QUOTES, 'UTF-8' ),
		'description'      => $desc ?: null,
		'url'              => $url,
		'provider'         => array( '@id' => $org_id ),
		'areaServed'       => array(
			array( '@type' => 'City', 'name' => 'London' ),
			array( '@type' => 'Country', 'name' => 'United Kingdom' ),
		),
		'mainEntityOfPage' => $page_id ? array( '@id' => $page_id ) : null,
	) );

	foreach ( $graph as $i => $node ) {
		if ( $page_id && ( $node['@id'] ?? null ) === $page_id ) {
			$graph[ $i ]['about']      = array( '@id' => $service['@id'] );
			$graph[ $i ]['mainEntity'] = array( '@id' => $service['@id'] );
			break;
		}
	}
	$graph[] = $service;
	return $graph;
}
add_filter( 'wpseo_schema_graph', 'le_service_page_schema', 21 );
