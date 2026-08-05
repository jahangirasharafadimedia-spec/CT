<?php
/**
 * Load all custom post type definitions.
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require get_template_directory() . '/inc/post-types/videos.php';
require get_template_directory() . '/inc/post-types/5g-weekly-newsletters.php';
