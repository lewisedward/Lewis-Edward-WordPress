<?php
/**
 * Gravity Forms tweaks shared by both skins.
 *
 * - Plain-English "required" messages built from the field label ("Please add your name.")
 *   instead of Gravity Forms' "This field is required. Please complete the following fields: First."
 * - Consent checkbox links with an empty href point at the privacy policy.
 * - Short, calm summary message at the top of a form that failed validation.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'gform_field_validation',
	static function ( $result, $value, $form, $field ) {
		if ( ! empty( $result['is_valid'] ) || empty( $field->isRequired ) || ! empty( $field->errorMessage ) ) {
			return $result;
		}
		$msg = (string) ( $result['message'] ?? '' );
		if ( '' !== $msg && false === stripos( $msg, 'required' ) && false === stripos( $msg, 'complete the following' ) ) {
			return $result; // A format error (e.g. an invalid email): keep Gravity Forms' message.
		}
		if ( 'consent' === $field->type ) {
			$result['message'] = __( 'Please tick the box to agree to the privacy policy.', 'lewisedward' );
			return $result;
		}
		$label = trim( wp_strip_all_tags( (string) $field->label ) );
		$label = preg_replace( '/\s*\((optional|required)\)\s*$/i', '', $label );
		if ( '' === $label ) {
			$result['message'] = __( 'Please fill this in.', 'lewisedward' );
			return $result;
		}
		$label = function_exists( 'mb_strtolower' ) ? mb_strtolower( $label ) : strtolower( $label );
		if ( 0 !== strpos( $label, 'your ' ) ) {
			$label = 'your ' . $label;
		}
		/* translators: %s: field label, e.g. "your email address". */
		$result['message'] = sprintf( __( 'Please add %s.', 'lewisedward' ), $label );
		return $result;
	},
	10,
	4
);

add_filter(
	'gform_field_content',
	static function ( $content, $field ) {
		$content = str_replace( 'e.g., ', 'e.g. ', $content ); // House style: no comma after "e.g.".
		if ( 'consent' === $field->type && false !== strpos( $content, 'href=""' ) ) {
			$privacy = get_privacy_policy_url();
			$privacy = $privacy ? $privacy : home_url( '/privacy-policy/' );
			$content = str_replace( 'href=""', 'href="' . esc_url( $privacy ) . '"', $content );
		}
		return $content;
	},
	10,
	2
);

add_filter(
	'gform_validation_message',
	static function ( $message ) {
		// Keep Gravity Forms' wrapper (role, focus handling); swap only the wording inside the heading.
		$text = esc_html__( 'A few details are missing. Check the highlighted fields below.', 'lewisedward' );
		$new  = preg_replace( '#(<h2[^>]*>)(?:\s*<span[^>]*gform-icon[^>]*>\s*</span>)?.*?(</h2>)#s', '$1' . $text . '$2', (string) $message, 1 );
		return $new ? $new : $message;
	},
	10,
	2
);
