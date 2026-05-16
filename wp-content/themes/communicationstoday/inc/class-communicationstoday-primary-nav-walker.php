<?php
/**
 * Primary header nav markup: .nav-item / .dropdown / .dropdown-menu (matches theme UI).
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Walker for wp_nav_menu() in the header nav bar.
 */
class Communicationstoday_Primary_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Sub-menu wrapper.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( $depth > 0 ) {
			return;
		}
		$output .= '<ul class="dropdown-menu">';
	}

	/**
	 * Close sub-menu wrapper.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   wp_nav_menu() arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( $depth > 0 ) {
			return;
		}
		$output .= '</ul>';
	}

	/**
	 * Menu item output.
	 *
	 * @param string   $output            Used to append additional content (passed by reference).
	 * @param WP_Post  $data_object       Menu item data object.
	 * @param int      $depth             Depth of menu item.
	 * @param stdClass $args              wp_nav_menu() arguments.
	 * @param int      $current_object_id Optional. ID of the current menu item.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		if ( $depth > 1 ) {
			return;
		}

		$item = $data_object;
		if ( ! $item instanceof WP_Post ) {
			return;
		}

		$title = apply_filters( 'nav_menu_item_title', $item->title, $item, $args, $depth );
		$title = is_string( $title ) ? $title : '';
		if ( '' === trim( $title ) ) {
			$title = __( 'Menu item', 'communicationstoday' );
		}

		$url = ! empty( $item->url ) ? $item->url : '#';

		if ( 0 === $depth ) {
			$has_children = in_array( 'menu-item-has-children', (array) $item->classes, true );
			if ( $has_children ) {
				$output .= '<div class="nav-item dropdown">';
				$output .= '<a href="' . esc_url( $url ) . '" class="dropdown-toggle">';
				$output .= esc_html( $title );
				$output .= ' <i class="fas fa-chevron-down" aria-hidden="true"></i></a>';
			} else {
				$output .= '<div class="nav-item">';
				$output .= '<a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a>';
			}
		} elseif ( 1 === $depth ) {
			$output .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a></li>';
		}
	}

	/**
	 * Close top-level .nav-item wrapper.
	 *
	 * @param string   $output       Used to append additional content (passed by reference).
	 * @param WP_Post  $data_object  Menu item data object.
	 * @param int      $depth        Depth of menu item.
	 * @param stdClass $args         wp_nav_menu() arguments.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '</div>';
		}
	}
}
