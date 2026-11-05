<?php
/**
 * Branded HTML email for the Gravity Forms autoreplies.
 *
 * The wording stays editable in Gravity Forms (Form > Settings > Notifications >
 * "Autoreply to sender"); this file only dresses it: a dark card in the site's
 * colours with the lime "L." mark, the message, a tidy summary of what the
 * person sent ({le_summary}) and a footer. Email-safe markup: tables, inline
 * styles, bgcolor attributes, web-safe fonts, absolute PNG image URLs.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Brand tokens (hex versions of the theme's HSL variables). */
function le_email_tokens() {
	return array(
		'bg'      => '#0f1111',
		'card'    => '#161818',
		'panel'   => '#1d2020',
		'line'    => '#2c302f',
		'text'    => '#ffffff',
		'muted'   => '#a3a8a6',
		'lime'    => '#bfff00',
		'font'    => "'Helvetica Neue', Helvetica, Arial, sans-serif",
		'mono'    => "'SF Mono', Menlo, Consolas, 'Courier New', monospace",
	);
}

/** Notifications that get the branded shell. */
function le_email_is_branded( $notification ) {
	return 'Autoreply to sender' === rgar( (array) $notification, 'name' );
}

/**
 * {le_summary}: the submitted answers as clean label/value rows, skipping
 * section headings, consent, honeypot and empty fields.
 */
add_filter( 'gform_replace_merge_tags', function ( $text, $form, $entry ) {
	if ( false === strpos( (string) $text, '{le_summary}' ) || ! is_array( $entry ) ) {
		return $text;
	}
	$t    = le_email_tokens();
	$rows = '';
	foreach ( (array) rgar( $form, 'fields' ) as $field ) {
		if ( in_array( $field->type, array( 'section', 'consent', 'html', 'page', 'captcha', 'honeypot' ), true ) || ! empty( $field->visibility ) && 'hidden' === $field->visibility ) {
			continue;
		}
		$value = GFCommon::get_lead_field_display( $field, RGFormsModel::get_lead_field_value( $entry, $field ), rgar( $entry, 'currency' ), true, 'html' );
		if ( '' === trim( wp_strip_all_tags( (string) $value ) ) && false === strpos( (string) $value, '<a' ) ) {
			continue;
		}
		$label = trim( preg_replace( '/\s*\((optional|required)\)\s*$/i', '', wp_strip_all_tags( (string) $field->label ) ) );
		$value = preg_replace( '/<a /', '<a style="color:' . $t['lime'] . ';text-decoration:underline;" ', (string) $value );
		$rows .= '<tr><td style="padding:14px 0;border-top:1px solid ' . $t['line'] . ';">'
			. '<div style="font-family:' . $t['mono'] . ';font-size:11px;letter-spacing:0.08em;text-transform:uppercase;color:' . $t['muted'] . ';padding-bottom:6px;">' . esc_html( $label ) . '</div>'
			. '<div style="font-family:' . $t['font'] . ';font-size:15px;line-height:1.55;color:' . $t['text'] . ';">' . wp_kses_post( $value ) . '</div>'
			. '</td></tr>';
	}
	if ( '' === $rows ) {
		return str_replace( '{le_summary}', '', $text );
	}
	$block = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="' . $t['panel'] . '" style="background:' . $t['panel'] . ';margin-top:8px;">'
		. '<tr><td style="padding:22px 24px 8px;">'
		. '<div style="font-family:' . $t['mono'] . ';font-size:11px;letter-spacing:0.08em;text-transform:uppercase;color:' . $t['lime'] . ';padding-bottom:10px;">&#9632;&nbsp; What you sent</div>'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">' . $rows . '</table>'
		. '</td></tr></table>';
	return str_replace( '{le_summary}', $block, $text );
}, 10, 3 );

/**
 * Wrap the merged message in the branded shell.
 *
 * @param string $body      Message HTML (merge tags already replaced).
 * @param string $preheader Inbox preview text.
 */
function le_email_shell( $body, $preheader = '' ) {
	$t    = le_email_tokens();
	$mark = LE_URI . '/assets/images/brand/le-sig-mark-96.png';
	$home = home_url( '/' );

	// Style the paragraphs Gravity Forms produces (wpautop / nl2br).
	$p    = 'margin:0 0 18px;font-family:' . $t['font'] . ';font-size:16px;line-height:1.6;color:' . $t['text'] . ';';
	$body = preg_replace( '/<p>/i', '<p style="' . $p . '">', (string) $body );
	$body = preg_replace( '/<a (?![^>]*style=)/i', '<a style="color:' . $t['lime'] . ';text-decoration:underline;" ', $body );

	$foot = 'font-family:' . $t['mono'] . ';font-size:11px;letter-spacing:0.06em;text-transform:uppercase;color:' . $t['muted'] . ';';

	ob_start();
	?>
<!DOCTYPE html>
<html lang="en-GB" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="dark">
<meta name="supported-color-schemes" content="dark">
<title>Lewis Edward</title>
<style>
	body { margin:0; padding:0; }
	a { color:<?php echo esc_attr( $t['lime'] ); ?>; }
	@media (max-width:620px) { .le-pad { padding-left:24px !important; padding-right:24px !important; } }
</style>
</head>
<body style="margin:0;padding:0;background:<?php echo esc_attr( $t['bg'] ); ?>;" bgcolor="<?php echo esc_attr( $t['bg'] ); ?>">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;"><?php echo esc_html( $preheader ); ?>&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="<?php echo esc_attr( $t['bg'] ); ?>" style="background:<?php echo esc_attr( $t['bg'] ); ?>;">
<tr><td align="center" style="padding:32px 12px;">
	<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;">
		<tr><td bgcolor="<?php echo esc_attr( $t['lime'] ); ?>" style="height:4px;line-height:4px;font-size:0;background:<?php echo esc_attr( $t['lime'] ); ?>;background-image:linear-gradient(135deg,#d6ff33,#88cc00);">&nbsp;</td></tr>
		<tr><td bgcolor="<?php echo esc_attr( $t['card'] ); ?>" class="le-pad" style="background:<?php echo esc_attr( $t['card'] ); ?>;padding:28px 40px;border-bottom:1px solid <?php echo esc_attr( $t['line'] ); ?>;">
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
				<td width="40" valign="middle"><a href="<?php echo esc_url( $home ); ?>" style="text-decoration:none;"><img src="<?php echo esc_url( $mark ); ?>" width="40" height="40" alt="L." style="display:block;border:0;width:40px;height:40px;"></a></td>
				<td valign="middle" style="padding-left:14px;font-family:<?php echo esc_attr( $t['font'] ); ?>;font-size:18px;font-weight:500;letter-spacing:-0.01em;color:<?php echo esc_attr( $t['text'] ); ?>;">Lewis Edward</td>
				<td align="right" valign="middle" style="<?php echo esc_attr( $foot ); ?>"><span style="color:<?php echo esc_attr( $t['lime'] ); ?>;">&#9679;</span>&nbsp; London</td>
			</tr></table>
		</td></tr>
		<tr><td bgcolor="<?php echo esc_attr( $t['card'] ); ?>" class="le-pad" style="background:<?php echo esc_attr( $t['card'] ); ?>;padding:40px 40px 32px;font-family:<?php echo esc_attr( $t['font'] ); ?>;font-size:16px;line-height:1.6;color:<?php echo esc_attr( $t['text'] ); ?>;">
			<?php echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Gravity Forms notification HTML, already sanitised by GF. ?>
		</td></tr>
		<tr><td bgcolor="<?php echo esc_attr( $t['card'] ); ?>" class="le-pad" style="background:<?php echo esc_attr( $t['card'] ); ?>;padding:24px 40px 28px;border-top:1px solid <?php echo esc_attr( $t['line'] ); ?>;">
			<div style="<?php echo esc_attr( $foot ); ?>line-height:1.9;">
				<a href="<?php echo esc_url( $home ); ?>" style="color:<?php echo esc_attr( $t['text'] ); ?>;text-decoration:none;">lewisedward.com</a>
				&nbsp;&middot;&nbsp; <a href="<?php echo esc_url( home_url( '/work/' ) ); ?>" style="color:<?php echo esc_attr( $t['muted'] ); ?>;text-decoration:none;">Work</a>
				&nbsp;&middot;&nbsp; <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" style="color:<?php echo esc_attr( $t['muted'] ); ?>;text-decoration:none;">Services</a>
				&nbsp;&middot;&nbsp; <a href="https://www.linkedin.com/in/lewis-edward/" style="color:<?php echo esc_attr( $t['muted'] ); ?>;text-decoration:none;">LinkedIn</a>
			</div>
			<div style="font-family:<?php echo esc_attr( $t['font'] ); ?>;font-size:12px;line-height:1.6;color:<?php echo esc_attr( $t['muted'] ); ?>;padding-top:10px;">
				WordPress web design &amp; development since 2011. Lewis Edward Ltd, registered in England &amp; Wales (11928542). 71-75 Shelton Street, London WC2H 9JQ.<br>
				You're getting this because you contacted us through lewisedward.com.
			</div>
		</td></tr>
	</table>
</td></tr>
</table>
</body>
</html>
	<?php
	return (string) ob_get_clean();
}

/** Apply the shell to branded notifications just before sending. */
add_filter( 'gform_pre_send_email', function ( $email, $message_format, $notification = array() ) {
	if ( ! le_email_is_branded( $notification ) || 'html' !== $message_format ) {
		return $email;
	}
	$plain     = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $email['message'] ) ) );
	$preheader = function_exists( 'mb_substr' ) ? mb_substr( $plain, 0, 110 ) : substr( $plain, 0, 110 );
	$email['message'] = le_email_shell( $email['message'], $preheader );
	return $email;
}, 10, 3 );
