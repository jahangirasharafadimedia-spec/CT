<?php
/**
 * Sidebar / mobile panel menu markup: top-level with children = dropdown; without = single link.
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Walker for wp_nav_menu in the off-canvas sidebar.
 */
class Communicationstoday_Sidebar_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( $depth > 0 ) {
			return;
		}
		$output .= '<ul class="sidebar-dropdown-menu">';
	}

	/**
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( $depth > 0 ) {
			return;
		}
		$output .= '</ul>';
	}

	/**
	 * @param string   $output            Used to append additional content (passed by reference).
	 * @param WP_Post  $data_object       Menu item data object.
	 * @param int      $depth             Depth of menu item. Used for padding.
	 * @param stdClass $args              An object of wp_nav_menu() arguments.
	 * @param int      $current_object_id Optional. ID of the current menu item. Default 0.
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

		if ( 0 === $depth ) {
			$has_children = in_array( 'menu-item-has-children', (array) $item->classes, true );
			if ( $has_children ) {
				$output .= '<div class="sidebar-menu-item-dropdown">';
				$url = $item->url ? $item->url : '#';
				$output .= '<a href="' . esc_url( $url ) . '" class="sidebar-menu-item sidebar-dropdown-toggle">';
				$output .= '<span>' . esc_html( $title ) . '</span>';
				$output .= '<i class="fas fa-plus" aria-hidden="true"></i></a>';
			} else {
				$output .= '<a href="' . esc_url( $item->url ) . '" class="sidebar-menu-item">' . esc_html( $title ) . '</a>';
			}
		} elseif ( 1 === $depth ) {
			$output .= '<li><a href="' . esc_url( $item->url ) . '">' . esc_html( $title ) . '</a></li>';
		}
	}

	/**
	 * @param string   $output            Used to append additional content (passed by reference).
	 * @param WP_Post  $data_object       Menu item data object.
	 * @param int      $depth             Depth of menu item. Used for padding.
	 * @param stdClass $args              An object of wp_nav_menu() arguments.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$item = $data_object;
		if ( ! $item instanceof WP_Post ) {
			return;
		}
		if ( 0 === $depth && in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
			$output .= '</div>';
		}
	}
}
