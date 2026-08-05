<?php
/**
 * Bulk Image Management & Deletion — admin tool.
 *
 * Delete media attached to posts in a category for a selected year/month.
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Required capability for bulk image tools.
 *
 * @return string
 */
function communicationstoday_bulk_images_capability() {
	return apply_filters( 'communicationstoday_bulk_images_capability', 'manage_options' );
}

/**
 * Register admin menu.
 *
 * @return void
 */
function communicationstoday_bulk_images_admin_menu() {
	add_menu_page(
		__( 'Bulk Image Management', 'communicationstoday' ),
		__( 'Bulk Images', 'communicationstoday' ),
		communicationstoday_bulk_images_capability(),
		'communicationstoday-bulk-images',
		'communicationstoday_bulk_images_admin_page_render',
		'dashicons-images-alt2',
		58
	);
}
add_action( 'admin_menu', 'communicationstoday_bulk_images_admin_menu' );

/**
 * Month labels for filters.
 *
 * @return array<int, string>
 */
function communicationstoday_bulk_images_month_choices() {
	return array(
		0  => __( 'All months', 'communicationstoday' ),
		1  => __( 'January', 'communicationstoday' ),
		2  => __( 'February', 'communicationstoday' ),
		3  => __( 'March', 'communicationstoday' ),
		4  => __( 'April', 'communicationstoday' ),
		5  => __( 'May', 'communicationstoday' ),
		6  => __( 'June', 'communicationstoday' ),
		7  => __( 'July', 'communicationstoday' ),
		8  => __( 'August', 'communicationstoday' ),
		9  => __( 'September', 'communicationstoday' ),
		10 => __( 'October', 'communicationstoday' ),
		11 => __( 'November', 'communicationstoday' ),
		12 => __( 'December', 'communicationstoday' ),
	);
}

/**
 * Year choices (current year back 15 years).
 *
 * @return int[]
 */
function communicationstoday_bulk_images_year_choices() {
	$current = (int) gmdate( 'Y' );
	$years   = range( $current, $current - 15 );
	return apply_filters( 'communicationstoday_bulk_images_year_choices', $years );
}

/**
 * Build date_query for WP_Query from year/month.
 *
 * @param int $year  Year (required).
 * @param int $month Month 1–12, or 0 for all months in year.
 * @return array<int, array<string, int>>
 */
function communicationstoday_bulk_images_build_date_query( $year, $month ) {
	$year  = absint( $year );
	$month = absint( $month );

	if ( $year < 1970 || $year > 2100 ) {
		return array();
	}

	$date_query = array(
		array(
			'year' => $year,
		),
	);

	if ( $month >= 1 && $month <= 12 ) {
		$date_query[0]['month'] = $month;
	}

	return $date_query;
}

/**
 * Headline of the Day category slugs (used to resolve term ID per environment).
 *
 * @return string[]
 */
function communicationstoday_bulk_images_get_headline_category_slugs() {
	return apply_filters(
		'communicationstoday_bulk_images_headline_category_slugs',
		array(
			'headline-of-the-day',
			'headlines-of-the-day',
			'headline_of_the_day',
			'headlines_of_the_day',
		)
	);
}

/**
 * Latest News category slugs (companion for Headline of the Day).
 *
 * @return string[]
 */
function communicationstoday_bulk_images_get_latest_news_category_slugs() {
	return apply_filters(
		'communicationstoday_bulk_images_latest_news_category_slugs',
		array(
			'latest-news',
			'latest_news',
		)
	);
}

/**
 * Resolve a category term ID from slugs, with legacy fallback ID.
 *
 * @param string[] $slugs    Candidate slugs.
 * @param int      $fallback Legacy production term ID.
 * @return int
 */
function communicationstoday_bulk_images_resolve_term_id( array $slugs, $fallback = 0 ) {
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			return (int) $term->term_id;
		}
	}

	$fallback = absint( $fallback );
	if ( $fallback > 0 ) {
		$term = get_term( $fallback, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			return (int) $term->term_id;
		}
	}

	return 0;
}

/**
 * Headline of the Day term ID (legacy default: 82).
 *
 * @return int
 */
function communicationstoday_bulk_images_get_headline_category_id() {
	static $id = null;

	if ( null !== $id ) {
		return $id;
	}

	$id = communicationstoday_bulk_images_resolve_term_id(
		communicationstoday_bulk_images_get_headline_category_slugs(),
		(int) apply_filters( 'communicationstoday_bulk_images_headline_category_id', 82 )
	);

	return $id;
}

/**
 * All Latest News category term IDs (slug, name, ticker, legacy fallback).
 *
 * @return int[]
 */
function communicationstoday_bulk_images_get_latest_news_category_ids() {
	static $ids = null;

	if ( null !== $ids ) {
		return $ids;
	}

	$ids = array();

	foreach ( communicationstoday_bulk_images_get_latest_news_category_slugs() as $slug ) {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			$ids[] = (int) $term->term_id;
		}
	}

	// Match by display name when slug differs (e.g. local ID 4).
	$by_name = get_terms(
		array(
			'taxonomy'   => 'category',
			'name'       => 'Latest News',
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);
	if ( ! is_wp_error( $by_name ) && ! empty( $by_name ) ) {
		$ids = array_merge( $ids, array_map( 'intval', (array) $by_name ) );
	}

	if ( function_exists( 'communicationstoday_get_ticker_category_ids' ) ) {
		$ids = array_merge( $ids, communicationstoday_get_ticker_category_ids() );
	} elseif ( function_exists( 'communicationstoday_get_ticker_category_id' ) ) {
		$ticker_id = communicationstoday_get_ticker_category_id();
		if ( $ticker_id > 0 ) {
			$ids[] = $ticker_id;
		}
	}

	$fallback = (int) apply_filters( 'communicationstoday_bulk_images_latest_news_category_id', 83 );
	if ( $fallback > 0 ) {
		$term = get_term( $fallback, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			$ids[] = (int) $term->term_id;
		}
	}

	$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );

	return apply_filters( 'communicationstoday_bulk_images_latest_news_category_ids', $ids );
}

/**
 * Primary Latest News term ID for display (first resolved ID).
 *
 * @return int
 */
function communicationstoday_bulk_images_get_latest_news_category_id() {
	$ids = communicationstoday_bulk_images_get_latest_news_category_ids();

	return ! empty( $ids ) ? (int) $ids[0] : 0;
}

/**
 * Category IDs that allow deletion only when the post has that category alone (legacy: 84, 85, 87).
 *
 * @return int[]
 */
function communicationstoday_bulk_images_get_single_only_category_ids() {
	return array_map(
		'absint',
		(array) apply_filters(
			'communicationstoday_bulk_images_single_only_category_ids',
			array( 84, 85, 87 )
		)
	);
}

/**
 * Companion category ID allowed with the selected filter (default: 4 = Latest News).
 *
 * @return int
 */
function communicationstoday_bulk_images_get_companion_category_id() {
	$companion_id = (int) apply_filters( 'communicationstoday_bulk_images_companion_category_id', 4 );

	if ( $companion_id > 0 ) {
		$term = get_term( $companion_id, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			return $companion_id;
		}
	}

	$latest_ids = communicationstoday_bulk_images_get_latest_news_category_ids();

	return ! empty( $latest_ids ) ? (int) $latest_ids[0] : 4;
}

/**
 * Category rule: delete only when post has selected category alone, or selected + companion (ID 4).
 *
 * @param int[] $cat_ids            Post category term IDs.
 * @param int   $filter_category_id Selected category from bulk tool.
 * @param int   $companion_id       Latest News / companion category ID.
 * @return bool
 */
function communicationstoday_bulk_images_post_matches_category_rule( array $cat_ids, $filter_category_id, $companion_id ) {
	$cat_ids            = array_values( array_unique( array_map( 'absint', $cat_ids ) ) );
	$filter_category_id = absint( $filter_category_id );
	$companion_id       = absint( $companion_id );
	$count              = count( $cat_ids );

	if ( $filter_category_id < 1 || ! in_array( $filter_category_id, $cat_ids, true ) ) {
		return false;
	}

	$allowed = array_values( array_unique( array_filter( array( $filter_category_id, $companion_id ) ) ) );

	foreach ( $cat_ids as $cat_id ) {
		if ( ! in_array( $cat_id, $allowed, true ) ) {
			return false;
		}
	}

	if ( 1 === $count ) {
		return (int) $cat_ids[0] === $filter_category_id;
	}

	if ( 2 === $count ) {
		return in_array( $filter_category_id, $cat_ids, true ) && in_array( $companion_id, $cat_ids, true );
	}

	return false;
}

/**
 * Whether a post's featured image may be deleted.
 *
 * @param int $post_id            Post ID.
 * @param int $filter_category_id Category chosen in the bulk tool.
 * @return bool
 */
function communicationstoday_bulk_images_post_is_eligible_for_image_deletion( $post_id, $filter_category_id = 0 ) {
	$post_id            = absint( $post_id );
	$filter_category_id = absint( $filter_category_id );

	if ( $post_id < 1 || $filter_category_id < 1 ) {
		return false;
	}

	$categories = get_the_category( $post_id );
	if ( empty( $categories ) ) {
		return false;
	}

	$cat_ids = array_map( 'intval', wp_list_pluck( $categories, 'term_id' ) );

	return communicationstoday_bulk_images_post_matches_category_rule(
		$cat_ids,
		$filter_category_id,
		communicationstoday_bulk_images_get_companion_category_id()
	);
}

/**
 * Keep only posts that pass category rules for the selected filter.
 *
 * @param int[] $post_ids           Post IDs.
 * @param int   $filter_category_id Category chosen in the bulk tool.
 * @return array{post_ids: int[], protected_posts: int, total_posts: int}
 */
function communicationstoday_bulk_images_filter_eligible_posts( array $post_ids, $filter_category_id = 0 ) {
	$post_ids           = array_values( array_unique( array_map( 'absint', $post_ids ) ) );
	$filter_category_id = absint( $filter_category_id );
	$eligible           = array();
	$protected_posts    = 0;

	foreach ( $post_ids as $post_id ) {
		if ( communicationstoday_bulk_images_post_is_eligible_for_image_deletion( $post_id, $filter_category_id ) ) {
			$eligible[] = $post_id;
		} else {
			++$protected_posts;
		}
	}

	return array(
		'post_ids'        => $eligible,
		'protected_posts' => $protected_posts,
		'total_posts'     => count( $post_ids ),
	);
}

/**
 * Post IDs in category for year/month (published posts).
 *
 * @param int $category_id Category term ID.
 * @param int $year        Year.
 * @param int $month       Month 0 = all.
 * @return int[]
 */
function communicationstoday_bulk_images_get_post_ids( $category_id, $year, $month ) {
	$category_id = absint( $category_id );
	$year        = absint( $year );
	$month       = absint( $month );

	if ( $category_id < 1 || $year < 1970 ) {
		return array();
	}

	$date_query = communicationstoday_bulk_images_build_date_query( $year, $month );
	if ( empty( $date_query ) ) {
		return array();
	}

	$q = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'date_query'             => $date_query,
			'tax_query'              => array(
				array(
					'taxonomy'         => 'category',
					'field'            => 'term_id',
					'terms'            => $category_id,
					'include_children' => true,
				),
			),
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
		)
	);

	$post_ids = is_array( $q->posts ) ? array_map( 'absint', $q->posts ) : array();

	if ( ! empty( $post_ids ) ) {
		communicationstoday_bulk_images_prime_post_caches( $post_ids );
	}

	return $post_ids;
}

/**
 * Load post meta, terms, and attachment data so thumbnail checks match the delete step.
 *
 * @param int[] $post_ids Post IDs.
 * @return void
 */
function communicationstoday_bulk_images_prime_post_caches( array $post_ids ) {
	$post_ids = array_values( array_unique( array_map( 'absint', $post_ids ) ) );
	if ( empty( $post_ids ) ) {
		return;
	}

	if ( function_exists( '_prime_post_caches' ) ) {
		_prime_post_caches( $post_ids, true, true );
	} else {
		update_postmeta_cache( $post_ids );
	}

	$thumb_ids = array();
	foreach ( $post_ids as $post_id ) {
		$thumb_id = (int) get_post_meta( $post_id, '_thumbnail_id', true );
		if ( $thumb_id > 0 ) {
			$thumb_ids[] = $thumb_id;
		}
	}

	if ( ! empty( $thumb_ids ) && function_exists( '_prime_post_caches' ) ) {
		_prime_post_caches( array_values( array_unique( $thumb_ids ) ), false, true );
	}
}

/**
 * Featured image attachment ID for a post (reads _thumbnail_id directly).
 *
 * @param int $post_id Post ID.
 * @return int Attachment ID or 0.
 */
function communicationstoday_bulk_images_get_post_thumbnail_attachment_id( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id < 1 ) {
		return 0;
	}

	$thumb_id = (int) get_post_meta( $post_id, '_thumbnail_id', true );
	if ( $thumb_id < 1 ) {
		return 0;
	}

	$mime = get_post_mime_type( $thumb_id );
	if ( $mime && 0 === strpos( $mime, 'image/' ) ) {
		return $thumb_id;
	}

	return wp_attachment_is_image( $thumb_id ) ? $thumb_id : 0;
}

/**
 * Featured image attachment IDs for a post (legacy custom-ep: thumbnail only).
 *
 * @param int $post_id Post ID.
 * @return int[]
 */
function communicationstoday_bulk_images_collect_for_post( $post_id ) {
	$thumb_id = communicationstoday_bulk_images_get_post_thumbnail_attachment_id( $post_id );

	return $thumb_id > 0 ? array( $thumb_id ) : array();
}

/**
 * Collect featured image IDs for eligible posts (legacy custom-ep: always delete thumbnail).
 *
 * @param int[] $post_ids           Post IDs (already filtered to eligible).
 * @param int   $filter_category_id Category chosen in the bulk tool.
 * @return array{attachment_ids: int[], no_thumbnail: int, with_thumbnail: int}
 */
function communicationstoday_bulk_images_collect_deletable( array $post_ids, $filter_category_id = 0 ) {
	$post_ids           = array_values( array_unique( array_map( 'absint', $post_ids ) ) );
	$filter_category_id = absint( $filter_category_id );
	$attachment_ids     = array();
	$seen               = array();
	$no_thumbnail       = 0;
	$with_thumbnail     = 0;

	if ( ! empty( $post_ids ) ) {
		communicationstoday_bulk_images_prime_post_caches( $post_ids );
	}

	foreach ( $post_ids as $post_id ) {
		if ( ! communicationstoday_bulk_images_post_is_eligible_for_image_deletion( $post_id, $filter_category_id ) ) {
			continue;
		}

		$thumb_id = communicationstoday_bulk_images_get_post_thumbnail_attachment_id( $post_id );
		if ( $thumb_id < 1 ) {
			++$no_thumbnail;
			continue;
		}

		++$with_thumbnail;

		if ( isset( $seen[ $thumb_id ] ) ) {
			continue;
		}
		$seen[ $thumb_id ]  = true;
		$attachment_ids[] = $thumb_id;
	}

	return array(
		'attachment_ids' => array_values( array_unique( $attachment_ids ) ),
		'no_thumbnail'   => $no_thumbnail,
		'with_thumbnail' => $with_thumbnail,
	);
}

/**
 * Total file size for attachments (bytes).
 *
 * @param int[] $attachment_ids Attachment IDs.
 * @return int
 */
function communicationstoday_bulk_images_total_bytes( array $attachment_ids ) {
	$total = 0;
	foreach ( $attachment_ids as $attachment_id ) {
		$path = get_attached_file( $attachment_id );
		if ( $path && file_exists( $path ) ) {
			$total += (int) filesize( $path );
		}
	}
	return $total;
}

/**
 * Human-readable file size.
 *
 * @param int $bytes Bytes.
 * @return string
 */
function communicationstoday_bulk_images_format_bytes( $bytes ) {
	$bytes = max( 0, (int) $bytes );
	if ( $bytes < 1024 ) {
		return $bytes . ' B';
	}
	if ( $bytes < 1048576 ) {
		return round( $bytes / 1024, 1 ) . ' KB';
	}
	if ( $bytes < 1073741824 ) {
		return round( $bytes / 1048576, 2 ) . ' MB';
	}
	return round( $bytes / 1073741824, 2 ) . ' GB';
}

/**
 * Transient key for a delete job.
 *
 * @param string $job_id Job UUID.
 * @return string
 */
function communicationstoday_bulk_images_transient_key( $job_id ) {
	return 'ct_bulk_img_' . md5( (string) get_current_user_id() . '_' . sanitize_key( $job_id ) );
}

/**
 * Verify AJAX request.
 *
 * @return void
 */
function communicationstoday_bulk_images_verify_ajax() {
	if ( ! current_user_can( communicationstoday_bulk_images_capability() ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'communicationstoday' ) ), 403 );
	}

	check_ajax_referer( 'communicationstoday_bulk_images', 'nonce' );
}

/**
 * AJAX: preview counts and start job.
 *
 * @return void
 */
function communicationstoday_ajax_bulk_images_preview() {
	communicationstoday_bulk_images_verify_ajax();

	$category_id = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
	$year        = isset( $_POST['year'] ) ? absint( $_POST['year'] ) : 0;
	$month       = isset( $_POST['month'] ) ? absint( $_POST['month'] ) : 0;

	if ( $category_id < 1 || $year < 1970 ) {
		wp_send_json_error( array( 'message' => __( 'Please select a category and year.', 'communicationstoday' ) ) );
	}

	$term = get_term( $category_id, 'category' );
	if ( ! $term || is_wp_error( $term ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid category.', 'communicationstoday' ) ) );
	}

	$all_post_ids = communicationstoday_bulk_images_get_post_ids( $category_id, $year, $month );
	$filtered     = communicationstoday_bulk_images_filter_eligible_posts( $all_post_ids, $category_id );
	$post_ids     = $filtered['post_ids'];
	$collect      = communicationstoday_bulk_images_collect_deletable( $post_ids, $category_id );
	$bytes        = communicationstoday_bulk_images_total_bytes( $collect['attachment_ids'] );

	// Preview counts must match what delete will process.
	$image_count = count( $collect['attachment_ids'] );

	$job_id = wp_generate_uuid4();
	$key    = communicationstoday_bulk_images_transient_key( $job_id );

	set_transient(
		$key,
		array(
			'attachment_ids' => $collect['attachment_ids'],
			'post_ids'       => $post_ids,
			'category_id'    => $category_id,
			'year'           => $year,
			'month'          => $month,
		),
		15 * MINUTE_IN_SECONDS
	);

	$month_label = 0 === $month ? __( 'all months', 'communicationstoday' ) : gmdate( 'F', mktime( 0, 0, 0, $month, 1 ) );

	wp_send_json_success(
		array(
			'job_id'          => $job_id,
			'post_count'         => count( $post_ids ),
			'total_posts'        => (int) $filtered['total_posts'],
			'posts_image_delete' => (int) $collect['with_thumbnail'],
			'image_count'        => $image_count,
			'protected_posts'    => (int) $filtered['protected_posts'],
			'with_thumbnail'     => (int) $collect['with_thumbnail'],
			'no_thumbnail'       => (int) $collect['no_thumbnail'],
			'size_label'      => communicationstoday_bulk_images_format_bytes( $bytes ),
			'category_name'  => $term->name,
			'period_label'   => sprintf(
				/* translators: 1: month label, 2: year */
				__( '%1$s %2$d', 'communicationstoday' ),
				$month_label,
				$year
			),
		)
	);
}
add_action( 'wp_ajax_communicationstoday_bulk_images_preview', 'communicationstoday_ajax_bulk_images_preview' );

/**
 * AJAX: delete a batch of attachments from job transient.
 *
 * @return void
 */
function communicationstoday_ajax_bulk_images_delete() {
	communicationstoday_bulk_images_verify_ajax();

	$job_id = isset( $_POST['job_id'] ) ? sanitize_text_field( wp_unslash( $_POST['job_id'] ) ) : '';
	$offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
	$batch  = isset( $_POST['batch_size'] ) ? absint( $_POST['batch_size'] ) : 20;
	$batch  = max( 5, min( 50, $batch ) );

	if ( '' === $job_id ) {
		wp_send_json_error( array( 'message' => __( 'Missing delete job. Run preview again.', 'communicationstoday' ) ) );
	}

	$key = communicationstoday_bulk_images_transient_key( $job_id );
	$job = get_transient( $key );

	if ( ! is_array( $job ) || empty( $job['attachment_ids'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Delete job expired. Run preview again.', 'communicationstoday' ) ) );
	}

	$attachment_ids     = array_values( array_map( 'absint', (array) $job['attachment_ids'] ) );
	$post_ids           = array_values( array_map( 'absint', (array) ( $job['post_ids'] ?? array() ) ) );
	$filter_category_id = isset( $job['category_id'] ) ? absint( $job['category_id'] ) : 0;
	$total              = count( $attachment_ids );
	$slice          = array_slice( $attachment_ids, $offset, $batch );

	$deleted = 0;
	$failed  = 0;

	foreach ( $slice as $attachment_id ) {
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			++$failed;
			continue;
		}

		// Only featured images are queued (see collect_for_post) — never content/upload gallery.
		$posts_with_thumb = array();
		foreach ( $post_ids as $post_id ) {
			if ( (int) get_post_thumbnail_id( $post_id ) === $attachment_id ) {
				$posts_with_thumb[] = (int) $post_id;
			}
		}

		$result = wp_delete_attachment( $attachment_id, true );
		if ( $result ) {
			++$deleted;
			foreach ( $posts_with_thumb as $post_id ) {
				delete_post_thumbnail( $post_id );
			}
		} else {
			++$failed;
		}
	}

	$new_offset = $offset + count( $slice );
	$done       = $new_offset >= $total;

	if ( $done ) {
		delete_transient( $key );
	}

	wp_send_json_success(
		array(
			'deleted'    => $deleted,
			'failed'     => $failed,
			'offset'     => $new_offset,
			'total'      => $total,
			'done'       => $done,
			'percent'    => $total > 0 ? min( 100, (int) round( ( $new_offset / $total ) * 100 ) ) : 100,
		)
	);
}
add_action( 'wp_ajax_communicationstoday_bulk_images_delete', 'communicationstoday_ajax_bulk_images_delete' );

/**
 * Enqueue admin assets.
 *
 * @param string $hook_suffix Hook suffix.
 * @return void
 */
function communicationstoday_bulk_images_admin_assets( $hook_suffix ) {
	if ( 'toplevel_page_communicationstoday-bulk-images' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_style(
		'communicationstoday-bulk-image-admin',
		get_template_directory_uri() . '/css/bulk-image-admin.css',
		array(),
		defined( '_S_VERSION' ) ? _S_VERSION : '1.0.0'
	);

	wp_enqueue_script(
		'communicationstoday-bulk-image-admin',
		get_template_directory_uri() . '/js/bulk-image-admin.js',
		array( 'jquery' ),
		defined( '_S_VERSION' ) ? _S_VERSION : '1.0.0',
		true
	);

	wp_localize_script(
		'communicationstoday-bulk-image-admin',
		'communicationstodayBulkImages',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'communicationstoday_bulk_images' ),
			'i18n'    => array(
				'previewing'     => __( 'Scanning posts and images…', 'communicationstoday' ),
				'previewError'   => __( 'Could not load preview. Try again.', 'communicationstoday' ),
				'selectFilters'  => __( 'Select a category and year first.', 'communicationstoday' ),
				'noImages'       => __( 'No deletable images found for this selection.', 'communicationstoday' ),
				'confirmDelete'  => __( 'This permanently deletes only the featured image (thumbnail) on eligible posts. Images inside the post content are not removed. Continue?', 'communicationstoday' ),
				'typeConfirm'    => __( 'Type DELETE to confirm:', 'communicationstoday' ),
				'confirmWord'    => 'DELETE',
				'deleting'       => __( 'Deleting images…', 'communicationstoday' ),
				'deleteDone'     => __( 'Deletion complete.', 'communicationstoday' ),
				'deleteError'    => __( 'Deletion failed. Try again.', 'communicationstoday' ),
				'totalPosts'       => __( 'Total posts', 'communicationstoday' ),
				'postsImageDelete' => __( 'Posts whose featured image will be deleted', 'communicationstoday' ),
				'estimatedSize'    => __( 'Total size', 'communicationstoday' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'communicationstoday_bulk_images_admin_assets' );

/**
 * Admin page markup.
 *
 * @return void
 */
function communicationstoday_bulk_images_admin_page_render() {
	if ( ! current_user_can( communicationstoday_bulk_images_capability() ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'communicationstoday' ) );
	}

	$categories = get_categories(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	$years  = communicationstoday_bulk_images_year_choices();
	$months = communicationstoday_bulk_images_month_choices();
	?>
	<div class="wrap communicationstoday-bulk-images-wrap">
		<h1><?php esc_html_e( 'Bulk Image Management & Deletion', 'communicationstoday' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Deletes only the featured image (post thumbnail) — not images in the post body or media library uploads attached to the post. Same category rules as the legacy custom-ep tool.', 'communicationstoday' ); ?>
		</p>
		<p class="description">
			<?php esc_html_e( 'Delete when the post has only the selected category, or the selected category plus Latest News (companion ID 4 by default). If any other category is also assigned, the post is skipped.', 'communicationstoday' ); ?>
		</p>

		<div class="notice notice-warning inline ct-bulk-images-warning">
			<p>
				<strong><?php esc_html_e( 'Warning:', 'communicationstoday' ); ?></strong>
				<?php esc_html_e( 'Deleted files cannot be recovered from the trash. Always run Preview before deleting.', 'communicationstoday' ); ?>
			</p>
		</div>

		<div class="ct-bulk-images-card">
			<h2><?php esc_html_e( 'Filters', 'communicationstoday' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="ct-bulk-images-category"><?php esc_html_e( 'Category', 'communicationstoday' ); ?></label>
					</th>
					<td>
						<select id="ct-bulk-images-category" class="regular-text">
							<option value="0"><?php esc_html_e( 'Select category', 'communicationstoday' ); ?></option>
							<?php foreach ( $categories as $cat ) : ?>
								<option value="<?php echo esc_attr( (string) (int) $cat->term_id ); ?>">
									<?php echo esc_html( $cat->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ct-bulk-images-year"><?php esc_html_e( 'Year', 'communicationstoday' ); ?></label>
					</th>
					<td>
						<select id="ct-bulk-images-year" class="regular-text">
							<option value="0"><?php esc_html_e( 'Select year', 'communicationstoday' ); ?></option>
							<?php foreach ( $years as $year ) : ?>
								<option value="<?php echo esc_attr( (string) (int) $year ); ?>" <?php selected( (int) $year, (int) gmdate( 'Y' ) ); ?>>
									<?php echo esc_html( (string) (int) $year ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ct-bulk-images-month"><?php esc_html_e( 'Month', 'communicationstoday' ); ?></label>
					</th>
					<td>
						<select id="ct-bulk-images-month" class="regular-text">
							<?php foreach ( $months as $num => $label ) : ?>
								<option value="<?php echo esc_attr( (string) (int) $num ); ?>">
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php esc_html_e( 'Choose a specific month or “All months” to include every post published in that year.', 'communicationstoday' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<p class="submit">
				<button type="button" class="button button-secondary" id="ct-bulk-images-preview">
					<?php esc_html_e( 'Preview', 'communicationstoday' ); ?>
				</button>
				<span class="ct-bulk-images-status" id="ct-bulk-images-status" aria-live="polite"></span>
			</p>
		</div>

		<div class="ct-bulk-images-card ct-bulk-images-results" id="ct-bulk-images-results" hidden>
			<h2><?php esc_html_e( 'Preview results', 'communicationstoday' ); ?></h2>
			<p class="ct-bulk-images-period" id="ct-bulk-images-period"></p>
			<ul class="ct-bulk-images-stats" id="ct-bulk-images-stats"></ul>

			<div class="ct-bulk-images-progress-wrap" id="ct-bulk-images-progress-wrap" hidden>
				<div class="ct-bulk-images-progress-bar">
					<div class="ct-bulk-images-progress-fill" id="ct-bulk-images-progress-fill"></div>
				</div>
				<p class="ct-bulk-images-progress-label" id="ct-bulk-images-progress-label"></p>
			</div>

			<p>
				<button type="button" class="button button-primary button-hero" id="ct-bulk-images-delete" disabled>
					<?php esc_html_e( 'Delete featured images', 'communicationstoday' ); ?>
				</button>
			</p>
		</div>
	</div>
	<?php
}
