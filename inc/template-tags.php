<?php
/**
 * Template tags — presentation helpers shared across templates.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Echo the site logo (SVG/image from Theme Options) or fall back to the name.
 *
 * @param array $args { 'class' => string }
 */
function le_site_logo( $args = array() ) {
	$class = isset( $args['class'] ) ? $args['class'] : 'site-logo';
	$logo  = le_option( 'site_logo' );
	$home  = esc_url( home_url( '/' ) );
	$name  = esc_attr( get_bloginfo( 'name' ) );

	echo '<a href="' . $home . '" class="' . esc_attr( $class ) . '" aria-label="' . $name . '">';
	if ( $logo && is_array( $logo ) && ! empty( $logo['url'] ) ) {
		printf(
			'<img src="%1$s" alt="%2$s" width="%3$s" height="%4$s" />',
			esc_url( $logo['url'] ),
			esc_attr( $name ),
			esc_attr( $logo['width'] ?? '' ),
			esc_attr( $logo['height'] ?? '' )
		);
	} else {
		echo esc_html( get_bloginfo( 'name' ) );
	}
	echo '</a>';
}

/**
 * Render a primary nav menu, falling back to a page list.
 *
 * @param string $location Registered menu location.
 * @param array  $args     wp_nav_menu overrides.
 */
function le_nav_menu( $location, $args = array() ) {
	$defaults = array(
		'theme_location' => $location,
		'container'      => false,
		'menu_class'     => 'menu menu--' . $location,
		'fallback_cb'    => false,
		'depth'          => 3,
	);
	wp_nav_menu( wp_parse_args( $args, $defaults ) );
}

/**
 * Estimated reading time for journal entries.
 *
 * @param int|null $post_id Post ID (defaults to current).
 * @return int Minutes.
 */
function le_reading_time( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( $content ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Output a responsive figure for an attachment or ACF image array.
 *
 * @param array|int $image ACF image array or attachment ID.
 * @param string    $size  Image size.
 * @param array     $attr  Extra <img> attributes.
 */
function le_image( $image, $size = 'le_card', $attr = array() ) {
	$id = 0;
	if ( is_array( $image ) && isset( $image['ID'] ) ) {
		$id = (int) $image['ID'];
	} elseif ( is_numeric( $image ) ) {
		$id = (int) $image;
	}
	if ( $id ) {
		echo wp_get_attachment_image( $id, $size, false, $attr );
	} elseif ( is_array( $image ) && ! empty( $image['url'] ) ) {
		printf( '<img src="%s" alt="%s" loading="lazy" />', esc_url( $image['url'] ), esc_attr( $image['alt'] ?? '' ) );
	}
}

/**
 * Breadcrumb trail (simple, schema-friendly).
 */
function le_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'lewisedward' ) . '">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'lewisedward' ) . '</a>';

	if ( is_singular( 'post' ) ) {
		echo ' <span class="sep">/</span> <a href="' . esc_url( home_url( '/journal' ) ) . '">' . esc_html__( 'Journal', 'lewisedward' ) . '</a>';
		echo ' <span class="sep">/</span> <span class="current">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_singular( array( 'service', 'work' ) ) ) {
		$pt         = get_post_type();
		$archive    = get_post_type_archive_link( $pt );
		$pt_object  = get_post_type_object( $pt );
		if ( $archive && $pt_object ) {
			echo ' <span class="sep">/</span> <a href="' . esc_url( $archive ) . '">' . esc_html( $pt_object->labels->name ) . '</a>';
		}
		echo ' <span class="sep">/</span> <span class="current">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_page() ) {
		echo ' <span class="sep">/</span> <span class="current">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_post_type_archive() ) {
		echo ' <span class="sep">/</span> <span class="current">' . esc_html( post_type_archive_title( '', false ) ) . '</span>';
	}
	echo '</nav>';
}
