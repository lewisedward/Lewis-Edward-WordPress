<?php
/**
 * Legacy 301 redirects.
 *
 * Ported verbatim from the React site's src/data/legacyRedirects.ts (v1 custom
 * site + v2 Webflow site). Preserves SEO by 301-ing old URLs to their modern
 * equivalents. Matching is done on the request path only (query string
 * ignored), case-insensitively, with/without a trailing slash.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The legacy -> modern path map.
 *
 * @return array<string,string>
 */
function le_legacy_redirects() {
	return array(
		'/services/saas-applications' => '/services/ai-development',
		'/services/agency-partner' => '/services/agency-web-developer',
		'/london-wordpress-developer' => '/london-wordpress-and-ai-developers',
		'/charity-donations-for-wordpress' => '/services/ecommerce',
		'/upgrade-to-https' => '/services/website-security',
		'/projects/diligenc' => '/work',
		'/projects/the-parlour' => '/work',
		'/web-designer-south-londons-streatham' => '/services',
		'/projects/diligenc-holding-page' => '/work',
		'/projects/twistfizz' => '/work',
		'/projects/centre-for-policy-studies' => '/work',
		'/projects/pedder' => '/work',
		'/projects/renew-life' => '/work',
		'/wordpress-website-maintenance' => '/services/website-support',
		'/services/hosting' => '/services/web-hosting',
		'/projects/the-zahid-mubarek-trust' => '/work',
		'/projects/blind-veterans-uk' => '/work',
		'/social-media-marketing' => '/services/website-support',
		'/engaging-social-media-marketing' => '/services/website-support',
		'/eco-friendly-web-hosting' => '/services/web-hosting',
		'/work-with-me/social-media-marketing' => '/services/website-support',
		'/blog' => '/journal',
		'/work-with-me' => '/about',
		'/the-problem-with-wordpress-updates' => '/services/website-support',
		'/projects/my-streatham' => '/work',
		'/wordpress-updates' => '/services/website-support',
		'/services/wordpress-updates' => '/services/website-support',
		'/corona-virus-update' => '/journal',
		'/london-web-designer' => '/services/web-design',
		'/long-tail-search-seo' => '/services/seo',
		'/projects/capx' => '/work',
		'/sub-service/content-entry' => '/services/website-support',
		'/sub-service/wordpress-api-integrations' => '/services/api-integrations',
		'/web-design' => '/services/web-design',
		'/journal/7-must-have-tools-for-web-designers' => '/journal',
		'/wordpress-web-design-for-covid-recovery' => '/journal',
		'/services/development' => '/services/web-development',
		'/sub-service/webflow' => '/services/webflow',
		'/projects/nous-comms' => '/work',
		'/projects' => '/work',
		'/more/quotes' => '/quotes',
		'/projects/butchies' => '/work',
		'/services/support' => '/services/website-support',
		'/projects/the-consulting-centre' => '/work',
		'/projects/instreatham' => '/work',
		'/services/design' => '/services/web-design',
		'/projects/cxo' => '/work/cxo',
		'/service/web-design' => '/services/web-design',
		'/service/web-development' => '/services/web-development',
		'/service/website-support' => '/services/website-support',
		'/service/agency-web-developer' => '/services/agency-web-developer',
		'/service/ecommerce' => '/services/ecommerce',
		'/sub-services/api-third-party-integrations' => '/services/api-integrations',
		'/sub-services/content-entry' => '/services/website-support',
		'/services/content-entry' => '/services/website-support',
		'/sub-services/migrate-to-wordpress' => '/services/migrate-to-wordpress',
		'/sub-services/seo' => '/services/seo',
		'/sub-services/ux-design' => '/services/web-design',
		'/services/ux-design' => '/services/web-design',
		'/services/wordpress-maintenance-london' => '/services/website-support',
		'/services/wordpress-migration' => '/services/migrate-to-wordpress',
		'/sub-services/web-hosting' => '/services/web-hosting',
		'/sub-services/webflow' => '/services/webflow',
		'/sub-services/website-auditing' => '/services/website-auditing',
		'/sub-services/website-optimisation' => '/services/website-optimisation',
		'/sub-services/website-security' => '/services/website-security',
		'/sub-services/website-training' => '/services/website-training',
		'/sub-services/wordpress-and-plugin-updates' => '/services/website-support',
		'/journal/caching-in-wordpress' => '/journal',
		'/work-category/webdesign' => '/work',
		'/work-category/webdevelopment' => '/work',
		'/category/plans' => '/services',
		'/checkout' => '/contact',
		'/order-confirmation' => '/',
		'/paypal-checkout' => '/contact',
		'/product/expert' => '/services',
		'/product/lite' => '/services',
		'/product/premium' => '/services',
		'/privacy-and-cookie-policy' => '/privacy',
		'/services/website-auditing' => '/services/website-audits',
	);
}

/**
 * Perform the redirect early, before WordPress resolves the query.
 */
function le_do_legacy_redirects() {
	if ( is_admin() ) {
		return;
	}

	$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( '' === $request ) {
		return;
	}

	// Strip query string, decode, normalise trailing slash & case.
	$path = wp_parse_url( $request, PHP_URL_PATH );
	$path = is_string( $path ) ? rawurldecode( $path ) : '';
	$path = strtolower( untrailingslashit( $path ) );
	if ( '' === $path ) {
		$path = '/';
	}

	$map = le_legacy_redirects();

	// Build a normalised lookup once.
	$normalised = array();
	foreach ( $map as $from => $to ) {
		$normalised[ strtolower( untrailingslashit( $from ) ) ] = $to;
	}

	if ( isset( $normalised[ $path ] ) ) {
		wp_safe_redirect( home_url( $normalised[ $path ] ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'le_do_legacy_redirects', 1 );
