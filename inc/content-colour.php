<?php
/**
 * Keep editor-authored colour on the theme's palette.
 *
 * ACF heading fields are rendered as raw HTML, so a colour pasted into the
 * editor as an inline style (`<span style="color: #bfff00">`) is baked into the
 * database and survives every palette change in theme.css. That is how the
 * "Creative Agencies" accent on the home Partnership heading stayed lime after
 * the rest of the site moved off it.
 *
 * This module rewrites those inline `color` declarations to the theme's
 * .text-primary class, so editor accents follow hsl(var(--primary)) like
 * everything else. Only the `color` property is touched: background-color,
 * border-color and friends are left alone, which keeps things like the client
 * logo swatches intact. An element marked data-keep-colour is skipped.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Swap inline text colour for the theme accent class.
 *
 * @param string $html Editor-authored HTML fragment.
 * @return string
 */
function le_accentise_inline_colour( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return $html;
	}

	// Fast path: nothing that could be an inline text colour.
	if ( false === stripos( $html, 'color' ) || false === stripos( $html, 'style' ) ) {
		return $html;
	}

	if ( ! class_exists( 'DOMDocument' ) ) {
		return $html;
	}

	$doc      = new DOMDocument();
	$previous = libxml_use_internal_errors( true );

	// The XML declaration forces UTF-8 without the deprecated HTML-ENTITIES
	// conversion; the wrapper gives us a known node to read the fragment back
	// out of without picking up an implied <html>/<body>.
	$loaded = $doc->loadHTML(
		'<?xml encoding="UTF-8"?><div id="le-colour-root">' . $html . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);

	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded ) {
		return $html;
	}

	$root = $doc->getElementById( 'le-colour-root' );
	if ( ! $root ) {
		return $html;
	}

	$xpath   = new DOMXPath( $doc );
	$styled  = $xpath->query( '//*[@style]', $root );
	$changed = false;

	foreach ( $styled as $node ) {
		if ( $node->hasAttribute( 'data-keep-colour' ) ) {
			continue;
		}

		$style = $node->getAttribute( 'style' );

		// Match a `color` declaration that is not part of background-color,
		// border-color, outline-color, text-decoration-color, etc.
		$stripped = preg_replace( '/(?<![a-z-])color\s*:[^;]*;?/i', '', $style );

		if ( null === $stripped || $stripped === $style ) {
			continue;
		}

		$stripped = preg_replace( '/\s+/', ' ', $stripped );
		$stripped = trim( $stripped, " \t\n\r\0\x0B;" );

		if ( '' === $stripped ) {
			$node->removeAttribute( 'style' );
		} else {
			$node->setAttribute( 'style', $stripped . ';' );
		}

		$classes = preg_split( '/\s+/', trim( $node->getAttribute( 'class' ) ), -1, PREG_SPLIT_NO_EMPTY );
		$classes = is_array( $classes ) ? $classes : array();

		if ( ! in_array( 'text-primary', $classes, true ) ) {
			$classes[] = 'text-primary';
		}

		$node->setAttribute( 'class', implode( ' ', $classes ) );
		$changed = true;
	}

	if ( ! $changed ) {
		return $html;
	}

	$out = '';
	foreach ( $root->childNodes as $child ) {
		$out .= $doc->saveHTML( $child );
	}

	return $out;
}
