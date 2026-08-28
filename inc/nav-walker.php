<?php
/**
 * Custom nav walker — mega-menu dropdown for the primary navigation.
 *
 * Uses the standard WordPress menu (Appearance -> Menus, location "Primary").
 * Any top-level item that has children is rendered as a "mega" dropdown: its
 * children become a two-column grid of links, each showing the menu item's
 * Description field (enable via Screen Options -> Description), plus a footer
 * row with the item count and a "View All" link to the parent.
 *
 * This mirrors the React site's Services dropdown while keeping menu content
 * fully editable in wp-admin.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pre-count children per parent so the mega footer can show "06 Services".
 * Runs on the primary menu only.
 *
 * @param array    $items Sorted menu items.
 * @param stdClass $args  wp_nav_menu args.
 * @return array
 */
function le_count_menu_children( $items, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $items;
	}
	$counts = array();
	foreach ( $items as $it ) {
		$parent = (int) $it->menu_item_parent;
		if ( $parent ) {
			$counts[ $parent ] = isset( $counts[ $parent ] ) ? $counts[ $parent ] + 1 : 1;
		}
	}
	LE_Mega_Walker::$child_counts = $counts;
	return $items;
}
add_filter( 'wp_nav_menu_objects', 'le_count_menu_children', 10, 2 );

/**
 * Chevron used on parent items.
 *
 * @return string
 */
function le_chevron_svg() {
	return '<svg class="nav-chevron" width="10" height="10" viewBox="0 0 18 18" fill="none" aria-hidden="true" focusable="false"><path d="M9 11.25L5.818 8.068L6.879 7.007L9 9.129L11.121 7.007L12.182 8.068L9 11.25Z" fill="currentColor"/></svg>';
}

/**
 * Diagonal arrow used in the mega footer / CTA.
 *
 * @param int $size Pixel size.
 * @return string
 */
function le_arrow_diagonal_svg( $size = 12 ) {
	$size = (int) $size;
	return '<svg class="arrow-diagonal" width="' . $size . '" height="' . $size . '" viewBox="0 0 18 18" fill="none" aria-hidden="true" focusable="false"><path d="M13.922 4.5V11.8125C13.922 11.9244 13.8776 12.0317 13.7985 12.1108C13.7193 12.1899 13.612 12.2344 13.5002 12.2344C13.3883 12.2344 13.281 12.1899 13.2018 12.1108C13.1227 12.0317 13.0783 11.9244 13.0783 11.8125V5.51953L4.79547 13.7953C4.71715 13.8736 4.61092 13.9176 4.50015 13.9176C4.38939 13.9176 4.28316 13.8736 4.20484 13.7953C4.12652 13.717 4.08252 13.6108 4.08252 13.5C4.08252 13.3892 4.12652 13.283 4.20484 13.2047L12.4806 4.92188H6.18765C6.07577 4.92188 5.96846 4.87743 5.88934 4.79831C5.81023 4.71919 5.76578 4.61189 5.76578 4.5C5.76578 4.38811 5.81023 4.28081 5.88934 4.20169C5.96846 4.12257 6.07577 4.07813 6.18765 4.07812H13.5002C13.612 4.07813 13.7193 4.12257 13.7985 4.20169C13.8776 4.28081 13.922 4.38811 13.922 4.5Z" fill="currentColor"/></svg>';
}

/**
 * Mega-menu walker.
 */
class LE_Mega_Walker extends Walker_Nav_Menu {

	/**
	 * Map of parent menu-item ID => child count.
	 *
	 * @var array
	 */
	public static $child_counts = array();

	/**
	 * Current mega parent context (url + count) for the footer.
	 *
	 * @var array
	 */
	protected $mega = array();

	/**
	 * Open the children container. depth 0 => mega panel; deeper => plain list.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '<div class="mega-menu glass" role="menu">';
			$output .= '<div class="mega-menu__grid">';
		} else {
			$output .= '<ul class="sub-menu">';
		}
	}

	/**
	 * Close the children container and, at depth 0, add the mega footer.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '</div>'; // .mega-menu__grid
			
			$services_count = wp_count_posts( 'service' );
			$count = isset( $services_count->publish ) ? (int) $services_count->publish : 0;

			$url   = isset( $this->mega['url'] ) ? $this->mega['url'] : '#';
			$label = isset( $this->mega['label'] ) ? $this->mega['label'] : __( 'Services', 'lewisedward' );

			$output .= '<div class="mega-menu__footer">';
			$output .= '<div class="mega-menu__count">';
			$output .= '<span class="mega-menu__count-num">' . esc_html( str_pad( (string) $count, 2, '0', STR_PAD_LEFT ) ) . '</span>';
			$output .= '<span class="mega-menu__count-label">' . esc_html( $label ) . '</span>';
			$output .= '</div>';
			$output .= '<a class="mega-menu__all" href="' . esc_url( $url ) . '">';
			$output .= '<span>' . esc_html__( 'View All', 'lewisedward' ) . '</span>' . le_arrow_diagonal_svg( 12 );
			$output .= '</a>';
			$output .= '</div>';

			$output .= '</div>'; // .mega-menu
			$this->mega = array();
		} else {
			$output .= '</ul>';
		}
	}

	/**
	 * Render a menu item.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$active  = ( in_array( 'current-menu-item', $classes, true )
			|| in_array( 'current-menu-ancestor', $classes, true )
			|| in_array( 'current_page_item', $classes, true )
			|| in_array( 'current-menu-parent', $classes, true ) );

		$url   = ! empty( $item->url ) ? $item->url : '#';
		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$desc  = ! empty( $item->description ) ? $item->description : '';

		if ( 0 === $depth ) {
			$has_children = $this->has_children;
			$li_class     = 'menu-item nav-item' . ( $has_children ? ' nav-item--mega' : '' ) . ( $active ? ' is-active' : '' );

			$output .= '<li class="' . esc_attr( $li_class ) . '">';

			if ( $has_children ) {
				// Stash context for the footer.
				$this->mega = array(
					'url'   => $url,
					'label' => $title,
					'count' => isset( self::$child_counts[ $item->ID ] ) ? self::$child_counts[ $item->ID ] : 0,
				);
				$output .= '<a class="nav-link nav-link--parent" href="' . esc_url( $url ) . '" aria-haspopup="true" aria-expanded="false">';
				$output .= esc_html( $title ) . le_chevron_svg();
				$output .= '</a>';
				$output .= '<button class="nav-mega-toggle" type="button" aria-label="' . esc_attr( sprintf( __( 'Toggle %s submenu', 'lewisedward' ), $title ) ) . '" aria-expanded="false">' . le_chevron_svg() . '</button>';
			} else {
				$output .= '<a class="nav-link" href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a>';
			}
		} else {
			// Child inside the mega grid.
			$output .= '<a class="mega-item' . ( $active ? ' is-active' : '' ) . '" href="' . esc_url( $url ) . '" role="menuitem">';
			$output .= '<span class="mega-item__head"><span class="mega-item__dot" aria-hidden="true"></span><span class="mega-item__label">' . esc_html( $title ) . '</span></span>';
			if ( $desc ) {
				$output .= '<span class="mega-item__desc">' . esc_html( $desc ) . '</span>';
			}
			$output .= '</a>';
		}
	}

	/**
	 * Close a menu item. Grid children close themselves in start_el.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '</li>';
		}
	}
}
