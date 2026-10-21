<?php
/**
 * Cookie consent banner.
 *
 * Ships hidden in the (cached) HTML on every page. assets/js/consent.js
 * decides client-side whether to reveal it and whether to load analytics /
 * advertising scripts, so the decision is never baked into a cached page.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$le_privacy_url = home_url( '/privacy-policy/' );
?>
<div class="le-consent" data-consent role="dialog" aria-live="polite" aria-label="<?php esc_attr_e( 'Cookie consent', 'lewisedward' ); ?>" hidden>
	<div class="le-consent__inner glass">
		<p class="le-consent__text">
			<?php esc_html_e( 'We use cookies to analyse traffic and measure our advertising. You can accept or reject these.', 'lewisedward' ); ?>
			<a href="<?php echo esc_url( $le_privacy_url ); ?>"><?php esc_html_e( 'Privacy policy', 'lewisedward' ); ?></a>
		</p>
		<div class="le-consent__actions">
			<button type="button" class="le-consent__btn le-consent__btn--reject" data-consent-reject><?php esc_html_e( 'Reject', 'lewisedward' ); ?></button>
			<button type="button" class="le-consent__btn le-consent__btn--accept" data-consent-accept><?php esc_html_e( 'Accept', 'lewisedward' ); ?></button>
		</div>
	</div>
</div>
