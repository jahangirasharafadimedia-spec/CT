<?php
/**
 * Header live search: title, content, excerpt, and post meta (incl. ACF values).
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minimum characters before a search runs.
 *
 * @return int
 */
function communicationstoday_live_search_min_length() {
	return (int) apply_filters( 'communicationstoday_live_search_min_length', 3 );
}

/**
 * Post types included in live search.
 *
 * @return string[]
 */
function communicationstoday_live_search_post_types() {
	$types = array( 'post', 'page', 'ct_video' );
	$types = array_values( array_filter( array_unique( array_map( 'sanitize_key', apply_filters( 'communicationstoday_live_search_post_types', $types ) ) ) ) );
	if ( empty( $types ) ) {
		return array( 'post' );
	}
	return $types;
}

/**
 * Run live search; matches post_title, post_content, post_excerpt, and any post_meta value (ACF stores in postmeta).
 *
 * @param string $term Search string.
 * @return int[] Post IDs, newest first.
 */
function communicationstoday_live_search_query_ids( $term ) {
	global $wpdb;

	$term = is_string( $term ) ? trim( $term ) : '';
	if ( strlen( $term ) < communicationstoday_live_search_min_length() ) {
		return array();
	}

	$post_types = communicationstoday_live_search_post_types();
	if ( empty( $post_types ) ) {
		return array();
	}

	$like = '%' . $wpdb->esc_like( $term ) . '%';

	$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
	$sql          = "SELECT DISTINCT p.ID, p.post_date
		FROM {$wpdb->posts} p
		LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
		WHERE p.post_status = 'publish'
		AND p.post_type IN ($placeholders)
		AND (
			p.post_title LIKE %s
			OR p.post_content LIKE %s
			OR p.post_excerpt LIKE %s
			OR pm.meta_value LIKE %s
		)
		ORDER BY p.post_date DESC
		LIMIT 20";

	$prepare_args = array_merge( $post_types, array( $like, $like, $like, $like ) );
	$prepared     = $wpdb->prepare( $sql, $prepare_args );
	$rows         = $wpdb->get_results( $prepared, ARRAY_A );

	if ( empty( $rows ) || ! is_array( $rows ) ) {
		return array();
	}

	$seen = array();
	$ids  = array();
	foreach ( $rows as $row ) {
		$id = isset( $row['ID'] ) ? absint( $row['ID'] ) : 0;
		if ( $id && empty( $seen[ $id ] ) ) {
			$seen[ $id ] = true;
			$ids[]       = $id;
		}
	}

	return $ids;
}

/**
 * Build HTML for one search result card.
 *
 * @param WP_Post $post Post object.
 * @return string
 */
function communicationstoday_live_search_result_card_html( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	$post_id = (int) $post->ID;
	$url     = get_permalink( $post_id );
	if ( ! $url ) {
		return '';
	}

	$title = get_the_title( $post_id );
	if ( '' === $title ) {
		$title = __( '(No title)', 'communicationstoday' );
	}

	$excerpt = get_the_excerpt( $post );
	if ( '' === trim( wp_strip_all_tags( (string) $excerpt ) ) ) {
		$excerpt = wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 28, '…' );
	} else {
		$excerpt = wp_trim_words( wp_strip_all_tags( (string) $excerpt ), 28, '…' );
	}

	$thumb = get_the_post_thumbnail_url( $post_id, 'medium' );
	if ( ! $thumb ) {
		$thumb = get_template_directory_uri() . '/asset/img/4a5e7e0aed3b12697a186a692abd5914622822d6.png';
	}

	ob_start();
	?>
<a href="<?php echo esc_url( $url ); ?>" class="search-result-card">
	<div class="search-result-image">
		<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="w-100" loading="lazy" decoding="async" width="150" height="100">
	</div>
	<div class="search-result-content">
		<h3 class="search-result-title"><?php echo esc_html( $title ); ?></h3>
		<p class="search-result-excerpt"><?php echo esc_html( $excerpt ); ?></p>
	</div>
</a>
	<?php
	return (string) ob_get_clean();
}

/**
 * AJAX: return HTML fragment for live search.
 *
 * @return void
 */
function communicationstoday_ajax_live_search() {
	check_ajax_referer( 'communicationstoday_search', 'nonce' );

	$term = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';

	if ( strlen( $term ) < communicationstoday_live_search_min_length() ) {
		wp_send_json_success(
			array(
				'html'  => '',
				'count' => 0,
				'too_short' => true,
			)
		);
	}

	$ids = communicationstoday_live_search_query_ids( $term );
	if ( empty( $ids ) ) {
		wp_send_json_success(
			array(
				'html'  => '<p class="search-results-empty">' . esc_html__( 'No posts found.', 'communicationstoday' ) . '</p>',
				'count' => 0,
			)
		);
	}

	$posts = get_posts(
		array(
			'post__in'            => $ids,
			'post_type'           => communicationstoday_live_search_post_types(),
			'orderby'             => 'post__in',
			'posts_per_page'      => count( $ids ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	$html  = '';
	$count = 0;
	foreach ( $posts as $post ) {
		$card = communicationstoday_live_search_result_card_html( $post );
		if ( '' !== $card ) {
			$html .= $card;
			++$count;
		}
	}

	if ( '' === $html ) {
		wp_send_json_success(
			array(
				'html'  => '<p class="search-results-empty">' . esc_html__( 'No posts found.', 'communicationstoday' ) . '</p>',
				'count' => 0,
			)
		);
	}

	wp_send_json_success(
		array(
			'html'  => $html,
			'count' => $count,
		)
	);
}
add_action( 'wp_ajax_communicationstoday_live_search', 'communicationstoday_ajax_live_search' );
add_action( 'wp_ajax_nopriv_communicationstoday_live_search', 'communicationstoday_ajax_live_search' );

/**
 * AJAX: archive "More posts" button.
 *
 * @return void
 */
function communicationstoday_ajax_archive_load_more() {
	check_ajax_referer( 'communicationstoday_archive_load_more', 'nonce' );

	$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
	$page = max( 2, $page );

	$query_vars_json = isset( $_POST['query_vars'] ) ? wp_unslash( $_POST['query_vars'] ) : '';
	$query_vars      = is_string( $query_vars_json ) ? json_decode( $query_vars_json, true ) : array();
	if ( ! is_array( $query_vars ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Invalid request.', 'communicationstoday' ),
			)
		);
	}

	$allowed_keys = array(
		'cat',
		'category_name',
		'tag',
		'tag_id',
		'author',
		'author_name',
		'year',
		'monthnum',
		'day',
		'post_type',
		'taxonomy',
		'term',
		's',
	);
	$query = array();
	foreach ( $allowed_keys as $k ) {
		if ( isset( $query_vars[ $k ] ) ) {
			$query[ $k ] = $query_vars[ $k ];
		}
	}

	$query['post_status']         = 'publish';
	$query['ignore_sticky_posts'] = true;
	$query['posts_per_page']      = 10;
	$query['paged']               = $page;

	$wpq = new WP_Query( $query );

	ob_start();
	if ( $wpq->have_posts() ) {
		while ( $wpq->have_posts() ) {
			$wpq->the_post();
			get_template_part( 'template-parts/content', 'archive' );
		}
	}
	wp_reset_postdata();

	$html    = (string) ob_get_clean();
	$has_more = $wpq->max_num_pages > $page;

	wp_send_json_success(
		array(
			'html'      => $html,
			'has_more'  => $has_more,
			'next_page' => $page + 1,
		)
	);
}
add_action( 'wp_ajax_communicationstoday_archive_load_more', 'communicationstoday_ajax_archive_load_more' );
add_action( 'wp_ajax_nopriv_communicationstoday_archive_load_more', 'communicationstoday_ajax_archive_load_more' );
