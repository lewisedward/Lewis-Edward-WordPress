<?php
/**
 * About page — branded animated SVG icons.
 *
 * Ported from the React AboutIcons. Selected per "Why choose us" item via an
 * ACF select field; rendered inline so `currentColor` picks up the lime accent.
 * Subtle motion is added via CSS (see .about-why__icon in theme.css).
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return an inline SVG for a given icon key.
 *
 * @param string $key One of: pricing, support, team, experience, quick_response.
 * @return string SVG markup (safe, static).
 */
function le_about_icon( $key ) {
	$open  = '<svg class="le-icon" width="56" height="56" viewBox="0 0 56 56" fill="none" aria-hidden="true" focusable="false">';
	$close = '</svg>';
	$c     = 'currentColor';

	switch ( $key ) {
		case 'pricing':
			$svg = '<g class="le-icon__float"><circle cx="28" cy="28" r="18" stroke="' . $c . '" stroke-width="1.2"/><circle cx="28" cy="28" r="15" stroke="' . $c . '" stroke-width="0.6" opacity="0.3"/><path d="M22 38C22 38 24 38 28 38C32 38 34 38 34 38M21 29H33M24 38C24 34 25 31 25 28C25 22 22 19 27 16C30 14.5 33 16 34 17.5" stroke="' . $c . '" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" fill="none"/></g>';
			break;

		case 'support':
			$svg = '<circle cx="28" cy="28" r="20" stroke="' . $c . '" stroke-width="1.2"/><circle cx="28" cy="28" r="16" stroke="' . $c . '" stroke-width="0.5" opacity="0.2"/>'
				. '<line x1="28" y1="8" x2="28" y2="13" stroke="' . $c . '" stroke-width="1.2"/><line x1="28" y1="43" x2="28" y2="48" stroke="' . $c . '" stroke-width="1.2"/><line x1="8" y1="28" x2="13" y2="28" stroke="' . $c . '" stroke-width="1.2"/><line x1="43" y1="28" x2="48" y2="28" stroke="' . $c . '" stroke-width="1.2"/>'
				. '<g class="le-icon__spin"><path d="M28 13L25 28H31L28 13Z" fill="' . $c . '" opacity="0.8"/><path d="M28 43L31 28H25L28 43Z" stroke="' . $c . '" stroke-width="0.8" fill="none"/></g>'
				. '<circle cx="28" cy="28" r="2.5" stroke="' . $c . '" stroke-width="1"/><circle cx="28" cy="28" r="1" fill="' . $c . '" opacity="0.5"/>';
			break;

		case 'team':
			$svg = '<circle cx="28" cy="28" r="5" stroke="' . $c . '" stroke-width="1.2"/><circle cx="28" cy="28" r="2" fill="' . $c . '" opacity="0.4"/>'
				. '<circle cx="12" cy="14" r="3.5" stroke="' . $c . '" stroke-width="1"/><circle cx="44" cy="14" r="3.5" stroke="' . $c . '" stroke-width="1"/><circle cx="10" cy="44" r="3.5" stroke="' . $c . '" stroke-width="1"/><circle cx="46" cy="44" r="3.5" stroke="' . $c . '" stroke-width="1"/><circle cx="28" cy="50" r="2.5" stroke="' . $c . '" stroke-width="0.8"/>'
				. '<g class="le-icon__pulse"><line x1="24" y1="24" x2="14" y2="17" stroke="' . $c . '" stroke-width="0.8"/><line x1="32" y1="24" x2="42" y2="17" stroke="' . $c . '" stroke-width="0.8"/><line x1="24" y1="32" x2="13" y2="41" stroke="' . $c . '" stroke-width="0.8"/><line x1="32" y1="32" x2="43" y2="41" stroke="' . $c . '" stroke-width="0.8"/><line x1="28" y1="33" x2="28" y2="47" stroke="' . $c . '" stroke-width="0.6" opacity="0.5"/></g>';
			break;

		case 'experience':
			$svg = '<g class="le-icon__float"><path d="M20 28V16C20 14 21 12 24 12C27 12 27 14 27 16V24H38C40 24 42 26 42 28V32C42 38 38 44 32 44H24C20 44 18 42 18 40" stroke="' . $c . '" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 28H20V44H14V28Z" stroke="' . $c . '" stroke-width="1.2" stroke-linejoin="round"/></g>';
			break;

		case 'quick_response':
			$svg = '<g class="le-icon__float"><path d="M8 28L46 12L36 44L26 30L8 28Z" stroke="' . $c . '" stroke-width="1.2" stroke-linejoin="round"/><path d="M26 30L46 12" stroke="' . $c . '" stroke-width="1"/></g>';
			break;

		default:
			return '';
	}

	return $open . $svg . $close;
}
