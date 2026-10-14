/**
 * OpenAI (ChatGPT) Ads — form conversion tracking.
 *
 * Fires the "lead_created" conversion when a Gravity Forms submission
 * succeeds. Gravity Forms triggers `gform_confirmation_loaded` once per
 * successful AJAX submission (when the inline confirmation replaces the
 * form), so each completed quote or contact form counts as exactly one
 * lead. It does NOT fire on initial render or on validation errors.
 *
 * Enqueued only on the form pages (contact, quotes), where Gravity Forms
 * already loads jQuery — declared as a dependency so jQuery is guaranteed
 * present before this binds.
 *
 * Note: this relies on AJAX-mode forms with an inline confirmation. If a
 * form is set to redirect to a separate thank-you page on submit, the
 * conversion should instead be fired on that page.
 */
( function () {
	if ( ! window.jQuery ) {
		return;
	}

	window.jQuery( document ).on( 'gform_confirmation_loaded', function () {
		if ( window.oaiq ) {
			window.oaiq( 'measure', 'lead_created', { type: 'customer_action' } );
		}
	} );
}() );
