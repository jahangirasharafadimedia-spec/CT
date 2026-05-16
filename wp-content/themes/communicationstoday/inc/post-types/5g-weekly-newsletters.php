<?php
/**
 * Back-compat loader — newsletter CPTs live in newsletters.php.
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require get_template_directory() . '/inc/post-types/newsletters.php';
