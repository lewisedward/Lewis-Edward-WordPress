/**
 * Form conversion tracking: OpenAI (ChatGPT) Ads pixel + Google Analytics.
 *
 * On a successful Gravity Forms submission this fires:
 *   - OpenAI "lead_created" (both forms), and
 *   - a GA4 key event per form: "generate_lead" for the contact form (ID 1)
 *     and "request_quote" for the quote form (ID 2).
 *
 * Both forms submit by postback (full page reload), so success is detected
 * by the confirmation wrapper Gravity Forms prints in the response page.
 * The AJAX `gform_confirmation_loaded` hook is kept too, in case a form is
 * ever switched to AJAX; each submission still counts exactly once.
 *
 * gtag and oaiq only exist once the visitor has accepted cookies (see
 * consent.js), so nothing is sent without consent. Enqueued only on the
 * contact and quotes pages.
 */
( function () {
	var EVENTS = { 1: 'generate_lead', 2: 'request_quote' };
	var sent = {};

	function track( formId ) {
		formId = parseInt( formId, 10 ) || 0;
		if ( sent[ formId ] ) {
			return true;
		}
		var name = EVENTS[ formId ] || 'generate_lead';
		var done = false;
		if ( 'function' === typeof window.gtag ) {
			window.gtag( 'event', name, {
				form_id: String( formId ),
				form_name: 2 === formId ? 'quote' : 'contact',
				page_path: location.pathname
			} );
			done = true;
		}
		if ( 'function' === typeof window.oaiq ) {
			window.oaiq( 'measure', 'lead_created', { type: 'customer_action' } );
			done = true;
		}
		if ( done ) {
			sent[ formId ] = true;
		}
		return done;
	}

	// Postback forms: the confirmation is in the page that loads after submit.
	function fromPage() {
		var wraps = document.querySelectorAll( '[id^="gform_confirmation_wrapper_"]' );
		if ( ! wraps.length ) {
			return;
		}
		var ids = [];
		wraps.forEach( function ( w ) {
			ids.push( w.id.replace( 'gform_confirmation_wrapper_', '' ) );
		} );
		// consent.js may define gtag a moment after this runs: retry briefly.
		var tries = 0;
		( function attempt() {
			var pending = ids.filter( function ( id ) { return ! track( id ); } );
			if ( pending.length && ++tries < 20 ) {
				ids = pending;
				setTimeout( attempt, 250 );
			}
		}() );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', fromPage );
	} else {
		fromPage();
	}

	// AJAX forms (not used today, kept for safety).
	if ( window.jQuery ) {
		window.jQuery( document ).on( 'gform_confirmation_loaded', function ( e, formId ) {
			track( formId );
		} );
	}
}() );
