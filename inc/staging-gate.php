<?php
/**
 * Staging gate.
 *
 * On the staging host every front-end request is held behind a passphrase:
 * the visitor enters it once and a cookie remembers them for 30 days. It was
 * added to keep trial fonts off the public network; the fonts have gone (the
 * grid skin now uses Geist, SIL OFL) but the gate is worth keeping while the
 * new design is in progress.
 *
 * The passphrase can be set as LE_STAGING_PASS in wp-config.php; the fallback
 * below is only there so the gate works before that is done.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function le_is_staging_host() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	return 0 === strpos( $host, 'staging.' );
}

function le_gate_pass() {
	return defined( 'LE_STAGING_PASS' ) ? (string) LE_STAGING_PASS : 'lime-grid-2026';
}

function le_gate_token() {
	return hash_hmac( 'sha256', le_gate_pass(), defined( 'AUTH_SALT' ) ? AUTH_SALT : 'le' );
}

function le_gate_open() {
	return isset( $_COOKIE['le_gate'] ) && hash_equals( le_gate_token(), (string) $_COOKIE['le_gate'] );
}

/**
 * Hold staging behind the passphrase (front end only).
 */
add_action( 'template_redirect', function () {
	if ( ! le_is_staging_host() || is_admin() || is_user_logged_in() ) {
		return;
	}
	if ( defined( 'DOING_AJAX' ) || defined( 'DOING_CRON' ) || defined( 'REST_REQUEST' ) ) {
		return;
	}
	if ( le_gate_open() ) {
		return;
	}
	if ( isset( $_POST['le_gate_pass'] ) && hash_equals( le_gate_pass(), (string) wp_unslash( $_POST['le_gate_pass'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		setcookie( 'le_gate', le_gate_token(), time() + 30 * DAY_IN_SECONDS, '/', '', true, true );
		wp_safe_redirect( esc_url_raw( add_query_arg( array() ) ), 303 );
		exit;
	}
	status_header( 401 );
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow', true );
	$wrong = isset( $_POST['le_gate_pass'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow"><title>Staging — Lewis Edward</title>
<style>
html,body{margin:0;min-height:100%;background:#121413;color:#fff;font:16px/1.5 system-ui,sans-serif}
body{display:grid;place-items:center;padding:24px}
form{width:min(380px,100%);border:1px solid rgba(255,255,255,.14);padding:28px;background:rgba(255,255,255,.035)}
h1{margin:0 0 6px;font-size:18px;font-weight:500}
p{margin:0 0 18px;color:rgba(255,255,255,.6);font-size:14px}
label{display:block;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.6);margin-bottom:8px}
input{width:100%;box-sizing:border-box;padding:12px 14px;border:1px solid rgba(255,255,255,.2);background:#0d0e0e;color:#fff;font:inherit;margin-bottom:14px}
button{width:100%;padding:12px 14px;border:0;background:#bfff00;color:#121413;font:inherit;font-weight:600;cursor:pointer}
.err{color:#ff8a8a}
</style></head>
<body><form method="post"><h1>Staging</h1><p>This is a private preview. Enter the passphrase to continue.</p><label for="p">Passphrase</label><input id="p" name="le_gate_pass" type="password" autocomplete="current-password" autofocus><?php if ( $wrong ) : ?><p class="err">That passphrase didn't match.</p><?php endif; ?><button type="submit">Enter</button></form></body></html>
	<?php
	exit;
}, 0 );
