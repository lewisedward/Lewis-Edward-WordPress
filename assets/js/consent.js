/**
 * Lightweight cookie consent + gated tracking.
 *
 * No analytics or advertising code runs until the visitor accepts. The
 * choice is stored in the `le_consent` cookie (granted | denied) and
 * honoured on return visits.
 *
 * IMPORTANT: pages are LiteSpeed/Cloudflare cached, so consent is resolved
 * entirely client-side here. The banner always ships in the (cached) HTML
 * hidden, and this script decides whether to show it and whether to load
 * trackers — a server-side cookie check would cache one visitor's choice
 * for everyone.
 *
 * Gated vendors:
 *   - Google Analytics (gtag.js) — G-JPJ8G70E66
 *   - OpenAI (ChatGPT) Ads pixel — VscXZAefYPygZq7saYuX7z
 */
( function () {
	'use strict';

	var COOKIE = 'le_consent';
	var MAX_AGE = 60 * 60 * 24 * 180; // 180 days.
	var GA_ID = 'G-JPJ8G70E66';
	var OAIQ_PIXEL = 'VscXZAefYPygZq7saYuX7z';
	var OAIQ_SRC = 'https://bzrcdn.openai.com/sdk/oaiq.min.js';

	var analyticsLoaded = false;
	var adsLoaded = false;

	function getConsent() {
		var m = document.cookie.match( /(?:^|;\s*)le_consent=([^;]+)/ );
		return m ? m[1] : '';
	}

	function setConsent( value ) {
		document.cookie = COOKIE + '=' + value + ';path=/;max-age=' + MAX_AGE +
			';SameSite=Lax' + ( 'https:' === location.protocol ? ';Secure' : '' );
	}

	/* --- Google Analytics (gtag) — loaded only after consent ----------- */
	function loadAnalytics() {
		if ( analyticsLoaded ) { return; }
		analyticsLoaded = true;

		window.dataLayer = window.dataLayer || [];
		function gtag() { window.dataLayer.push( arguments ); }
		window.gtag = window.gtag || gtag;
		gtag( 'js', new Date() );
		gtag( 'config', GA_ID );

		// Defer the ~155KB library to first interaction or 10s (performance).
		var loaded = false;
		var timer;
		var opts = { once: true, passive: true, capture: true };
		var events = [ 'pointerdown', 'keydown', 'scroll', 'touchstart' ];

		function lib() {
			if ( loaded ) { return; }
			loaded = true;
			clearTimeout( timer );
			events.forEach( function ( e ) { window.removeEventListener( e, lib, opts ); } );
			var s = document.createElement( 'script' );
			s.async = true;
			s.src = 'https://www.googletagmanager.com/gtag/js?id=' + GA_ID;
			document.head.appendChild( s );
		}

		events.forEach( function ( e ) { window.addEventListener( e, lib, opts ); } );
		timer = setTimeout( lib, 10000 );
	}

	/* --- OpenAI (ChatGPT) Ads pixel — loaded only after consent -------- */
	function loadAds() {
		if ( adsLoaded ) { return; }
		adsLoaded = true;

		( function ( w, d, s, u ) {
			if ( w.oaiq ) { return; }
			var q = function () { q.q.push( arguments ); };
			q.q = [];
			w.oaiq = q;
			var j = d.createElement( s );
			j.async = 1;
			j.src = u;
			var f = d.getElementsByTagName( s )[0];
			f.parentNode.insertBefore( j, f );
		}( window, document, 'script', OAIQ_SRC ) );

		window.oaiq( 'init', { pixelId: OAIQ_PIXEL, debug: true } );
	}

	function loadAll() {
		loadAnalytics();
		loadAds();
	}

	/* --- Banner UI ----------------------------------------------------- */
	var banner = document.querySelector( '[data-consent]' );

	function showBanner() {
		if ( ! banner ) { return; }
		banner.hidden = false;
		requestAnimationFrame( function () { banner.classList.add( 'is-visible' ); } );
	}

	function hideBanner() {
		if ( ! banner ) { return; }
		banner.classList.remove( 'is-visible' );
		setTimeout( function () { banner.hidden = true; }, 400 );
	}

	function grant() { setConsent( 'granted' ); hideBanner(); loadAll(); }
	function deny() { setConsent( 'denied' ); hideBanner(); }

	// Public API — used by the footer "Cookie settings" link.
	window.leConsent = { open: showBanner, grant: grant, deny: deny, state: getConsent };

	function wire() {
		if ( banner ) {
			banner.querySelectorAll( '[data-consent-accept]' ).forEach( function ( b ) { b.addEventListener( 'click', grant ); } );
			banner.querySelectorAll( '[data-consent-reject]' ).forEach( function ( b ) { b.addEventListener( 'click', deny ); } );
		}
		document.querySelectorAll( '[data-consent-open]' ).forEach( function ( b ) {
			b.addEventListener( 'click', function ( e ) { e.preventDefault(); showBanner(); } );
		} );

		var state = getConsent();
		if ( 'granted' === state ) {
			loadAll();
		} else if ( 'denied' === state ) {
			/* Keep everything off. */
		} else {
			showBanner();
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', wire );
	} else {
		wire();
	}
}() );
