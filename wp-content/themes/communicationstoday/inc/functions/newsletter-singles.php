<?php
/**
 * Performance and output fixes for newsletter single templates (zox HTML).
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request is a newsletter CPT single.
 *
 * @return bool
 */
function communicationstoday_is_newsletter_single() {
	if ( ! function_exists( 'communicationstoday_get_newsletter_post_types' ) ) {
		return false;
	}

	$types = communicationstoday_get_newsletter_post_types();

	if ( is_singular( $types ) ) {
		return true;
	}

	$object_id = get_queried_object_id();
	if ( $object_id && in_array( get_post_type( $object_id ), $types, true ) ) {
		return true;
	}

	return false;
}

/**
 * Newsletter singles output pure HTML; suppress on-screen PHP notices (they still log).
 */
function communicationstoday_newsletter_single_suppress_debug_display() {
	if ( ! communicationstoday_is_newsletter_single() ) {
		return;
	}

	@ini_set( 'display_errors', '0' );

	add_filter( 'show_admin_bar', '__return_false' );
}
add_action( 'template_redirect', 'communicationstoday_newsletter_single_suppress_debug_display', 0 );

/**
 * Avoid slow/broken image_downsize() for array( 600 ) in newsletter-parts/content-posts.php.
 *
 * @param mixed $downsize Current downsize result.
 * @param int   $id       Attachment ID.
 * @param mixed $size     Requested size.
 * @return mixed
 */
function communicationstoday_newsletter_image_downsize( $downsize, $id, $size ) {
	if ( false !== $downsize || ! communicationstoday_is_newsletter_single() ) {
		return $downsize;
	}

	if ( ! is_array( $size ) || ! isset( $size[0] ) || isset( $size[1] ) ) {
		return $downsize;
	}

	$full = wp_get_attachment_image_src( $id, 'full' );
	if ( ! $full ) {
		return $downsize;
	}

	$target_width  = (int) $size[0];
	$full_width    = (int) $full[1];
	$full_height   = (int) $full[2];
	$target_height = $full_width > 0 ? (int) round( $full_height * ( $target_width / $full_width ) ) : $full_height;

	return array( $full[0], $target_width, $target_height, false );
}
add_filter( 'image_downsize', 'communicationstoday_newsletter_image_downsize', 10, 3 );

/**
 * Fast cached thumbnail URL for newsletter templates (weekly roundup, etc.).
 *
 * @param int $post_id Post ID.
 * @return string
 */
function communicationstoday_newsletter_post_thumb_url( $post_id ) {
	static $cache = array();

	$post_id = (int) $post_id;
	if ( $post_id < 1 ) {
		return '';
	}

	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}

	$thumb_id = get_post_thumbnail_id( $post_id );
	$url      = $thumb_id ? wp_get_attachment_url( $thumb_id ) : '';

	$cache[ $post_id ] = $url ? $url : '';

	return $cache[ $post_id ];
}
