<?php
/**
 * Category sticky posts: pin to top of homepage category sections and category archives.
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'COMMUNICATIONSTODAY_STICKY_META_ENABLED', '_ct_sticky_enabled' );
define( 'COMMUNICATIONSTODAY_STICKY_META_DURATION', '_ct_sticky_duration' );
define( 'COMMUNICATIONSTODAY_STICKY_META_UNTIL', '_ct_sticky_until' );
define( 'COMMUNICATIONSTODAY_STICKY_META_PINNED_AT', '_ct_sticky_pinned_at' );

/**
 * Allowed sticky duration values.
 *
 * @return string[]
 */
function communicationstoday_sticky_duration_choices() {
	return array( 'day', 'week', 'month', 'manual' );
}

/**
 * Seconds to add for a duration slug.
 *
 * @param string $duration day|week|month|manual
 * @return int 0 for manual (no auto expiry).
 */
function communicationstoday_sticky_duration_seconds( $duration ) {
	switch ( $duration ) {
		case 'day':
			return DAY_IN_SECONDS;
		case 'week':
			return WEEK_IN_SECONDS;
		case 'month':
			return 30 * DAY_IN_SECONDS;
		default:
			return 0;
	}
}

/**
 * Whether sticky is enabled and not past its expiry time.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function communicationstoday_is_post_sticky_active( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id <= 0 || 'post' !== get_post_type( $post_id ) ) {
		return false;
	}

	if ( '1' !== get_post_meta( $post_id, COMMUNICATIONSTODAY_STICKY_META_ENABLED, true ) ) {
		return false;
	}

	$until = absint( get_post_meta( $post_id, COMMUNICATIONSTODAY_STICKY_META_UNTIL, true ) );
	if ( $until > 0 && time() > $until ) {
		communicationstoday_clear_post_sticky_meta( $post_id );
		return false;
	}

	return true;
}

/**
 * Remove sticky meta from a post.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function communicationstoday_clear_post_sticky_meta( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id <= 0 ) {
		return;
	}
	delete_post_meta( $post_id, COMMUNICATIONSTODAY_STICKY_META_ENABLED );
	delete_post_meta( $post_id, COMMUNICATIONSTODAY_STICKY_META_DURATION );
	delete_post_meta( $post_id, COMMUNICATIONSTODAY_STICKY_META_UNTIL );
	delete_post_meta( $post_id, COMMUNICATIONSTODAY_STICKY_META_PINNED_AT );
}

/**
 * Whether a post belongs to a category (including child categories).
 *
 * @param int $post_id     Post ID.
 * @param int $category_id Category term ID.
 * @return bool
 */
function communicationstoday_post_in_category_tree( $post_id, $category_id ) {
	$post_id     = absint( $post_id );
	$category_id = absint( $category_id );
	if ( $post_id <= 0 || $category_id <= 0 ) {
		return false;
	}

	return has_term( $category_id, 'category', $post_id );
}

/**
 * Active sticky post IDs for a category, newest pin first.
 *
 * @param int $category_id Category term ID.
 * @return int[]
 */
function communicationstoday_get_active_sticky_ids_for_category( $category_id ) {
	$category_id = absint( $category_id );
	if ( $category_id <= 0 ) {
		return array();
	}

	static $cache = array();
	if ( isset( $cache[ $category_id ] ) ) {
		return $cache[ $category_id ];
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => 50,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'   => COMMUNICATIONSTODAY_STICKY_META_ENABLED,
					'value' => '1',
				),
			),
			'tax_query'              => array(
				array(
					'taxonomy'         => 'category',
					'field'            => 'term_id',
					'terms'            => $category_id,
					'include_children' => true,
				),
			),
		)
	);

	$ids = array();
	if ( ! empty( $query->posts ) ) {
		foreach ( $query->posts as $pid ) {
			if ( communicationstoday_is_post_sticky_active( (int) $pid ) ) {
				$ids[] = (int) $pid;
			}
		}
	}

	if ( count( $ids ) > 1 && function_exists( 'communicationstoday_sort_post_ids_by_menu_order' ) ) {
		$ids = communicationstoday_sort_post_ids_by_menu_order( $ids );
	}

	$cache[ $category_id ] = $ids;
	return $ids;
}

/**
 * Put sticky post IDs first while preserving relative order within each group.
 *
 * @param int[] $post_ids    Post IDs in display order.
 * @param int   $category_id Category term ID.
 * @return int[]
 */
function communicationstoday_prioritize_sticky_post_ids( array $post_ids, $category_id ) {
	if ( function_exists( 'communicationstoday_sort_post_ids_for_category_display' ) ) {
		return communicationstoday_sort_post_ids_for_category_display( $post_ids, $category_id );
	}

	$sticky = communicationstoday_get_active_sticky_ids_for_category( $category_id );
	if ( empty( $sticky ) || empty( $post_ids ) ) {
		return $post_ids;
	}

	$post_ids = array_values( array_unique( array_map( 'absint', $post_ids ) ) );
	$sticky   = array_values( array_intersect( $sticky, $post_ids ) );
	$rest     = array_values( array_diff( $post_ids, $sticky ) );

	return array_merge( $sticky, $rest );
}

/**
 * Latest posts for a category with active sticky posts pinned to the top.
 *
 * @param int   $category_id Category term ID.
 * @param int   $limit         Number of posts.
 * @param array $query_args    Extra WP_Query arguments.
 * @return WP_Post[]
 */
function communicationstoday_get_posts_for_category_with_sticky( $category_id, $limit, $query_args = array() ) {
	$category_id = absint( $category_id );
	$limit       = max( 1, absint( $limit ) );
	if ( $category_id <= 0 ) {
		return array();
	}

	$sticky_ids = communicationstoday_get_active_sticky_ids_for_category( $category_id );
	$sticky_ids = array_slice( $sticky_ids, 0, $limit );

	$posts = array();
	if ( ! empty( $sticky_ids ) ) {
		$sticky_query = new WP_Query(
			array_merge(
				array(
					'post_type'           => 'post',
					'post_status'         => 'publish',
					'post__in'            => $sticky_ids,
					'orderby'             => 'post__in',
					'posts_per_page'      => count( $sticky_ids ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
				),
				$query_args
			)
		);
		if ( $sticky_query->have_posts() ) {
			$posts = $sticky_query->posts;
		}
		wp_reset_postdata();
	}

	$remaining = $limit - count( $posts );
	if ( $remaining <= 0 ) {
		return array_slice( $posts, 0, $limit );
	}

	$exclude = wp_list_pluck( $posts, 'ID' );
	$exclude = array_merge( $exclude, communicationstoday_get_active_sticky_ids_for_category( $category_id ) );
	$exclude = array_values( array_unique( array_map( 'absint', $exclude ) ) );

	$orderby = function_exists( 'communicationstoday_get_menu_order_query_orderby' )
		? communicationstoday_get_menu_order_query_orderby()
		: array(
			'date' => 'DESC',
		);

	$defaults = array(
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'posts_per_page'         => $remaining,
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'orderby'                => $orderby,
		'post__not_in'           => $exclude,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
		'tax_query'              => array(
			array(
				'taxonomy'         => 'category',
				'field'            => 'term_id',
				'terms'            => $category_id,
				'include_children' => true,
			),
		),
	);

	$regular_query = new WP_Query( array_merge( $defaults, $query_args ) );
	if ( $regular_query->have_posts() ) {
		$posts = array_merge( $posts, $regular_query->posts );
	}
	wp_reset_postdata();

	return array_slice( $posts, 0, $limit );
}

/**
 * Pin active sticky posts to the top of category archive results (page 1 only).
 *
 * @param WP_Post[]     $posts Query posts.
 * @param WP_Query|null $query Query object.
 * @return WP_Post[]
 */
function communicationstoday_archive_prioritize_sticky_posts( $posts, $query ) {
	if ( is_admin() || ! ( $query instanceof WP_Query ) || ! $query->is_main_query() ) {
		return $posts;
	}

	if ( ! is_category() ) {
		return $posts;
	}

	$category_id = (int) get_queried_object_id();
	if ( $category_id <= 0 ) {
		return $posts;
	}

	$sticky_ids = communicationstoday_get_active_sticky_ids_for_category( $category_id );

	$paged = max( 1, (int) $query->get( 'paged' ) );

	if ( empty( $sticky_ids ) ) {
		if ( function_exists( 'communicationstoday_sort_post_ids_by_menu_order' ) && ! empty( $posts ) ) {
			$ids    = wp_list_pluck( $posts, 'ID' );
			$sorted = communicationstoday_sort_post_ids_by_menu_order( $ids );
			$by_id  = array();
			foreach ( $posts as $post ) {
				if ( $post instanceof WP_Post ) {
					$by_id[ (int) $post->ID ] = $post;
				}
			}
			$posts = array();
			foreach ( $sorted as $pid ) {
				if ( isset( $by_id[ $pid ] ) ) {
					$posts[] = $by_id[ $pid ];
				}
			}
		}
		return $posts;
	}

	if ( $paged > 1 ) {
		return array_values(
			array_filter(
				$posts,
				static function ( $post ) use ( $sticky_ids ) {
					return $post instanceof WP_Post && ! in_array( (int) $post->ID, $sticky_ids, true );
				}
			)
		);
	}

	$per_page = (int) $query->get( 'posts_per_page' );
	if ( $per_page <= 0 ) {
		$per_page = (int) get_option( 'posts_per_page', 10 );
	}

	$non_sticky = array_values(
		array_filter(
			$posts,
			static function ( $post ) use ( $sticky_ids ) {
				return $post instanceof WP_Post && ! in_array( (int) $post->ID, $sticky_ids, true );
			}
		)
	);

	if ( function_exists( 'communicationstoday_sort_post_ids_by_menu_order' ) && ! empty( $non_sticky ) ) {
		$ns_ids = wp_list_pluck( $non_sticky, 'ID' );
		$ns_ids = communicationstoday_sort_post_ids_by_menu_order( $ns_ids );
		$by_id  = array();
		foreach ( $non_sticky as $post ) {
			$by_id[ (int) $post->ID ] = $post;
		}
		$non_sticky = array();
		foreach ( $ns_ids as $pid ) {
			if ( isset( $by_id[ $pid ] ) ) {
				$non_sticky[] = $by_id[ $pid ];
			}
		}
	}

	$sticky_posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'post__in'       => $sticky_ids,
			'orderby'        => 'post__in',
			'posts_per_page' => count( $sticky_ids ),
		)
	);

	$merged = array_merge( $sticky_posts, $non_sticky );
	return array_slice( $merged, 0, $per_page );
}
add_filter( 'the_posts', 'communicationstoday_archive_prioritize_sticky_posts', 20, 2 );

/**
 * Exclude sticky posts from archive AJAX load-more (they only appear on page 1).
 *
 * @param array $query Query arguments.
 * @return array
 */
function communicationstoday_archive_load_more_exclude_sticky( $query ) {
	if ( ! is_array( $query ) ) {
		return $query;
	}

	$category_id = 0;
	if ( ! empty( $query['cat'] ) ) {
		$category_id = absint( $query['cat'] );
	} elseif ( ! empty( $query['category_name'] ) ) {
		$term = get_term_by( 'slug', sanitize_title( (string) $query['category_name'] ), 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			$category_id = (int) $term->term_id;
		}
	}

	if ( $category_id <= 0 ) {
		return $query;
	}

	$sticky_ids = communicationstoday_get_active_sticky_ids_for_category( $category_id );
	if ( empty( $sticky_ids ) ) {
		return $query;
	}

	$exclude = isset( $query['post__not_in'] ) && is_array( $query['post__not_in'] ) ? $query['post__not_in'] : array();
	$query['post__not_in'] = array_values( array_unique( array_merge( array_map( 'absint', $exclude ), $sticky_ids ) ) );

	return $query;
}

/**
 * Register post editor meta box.
 *
 * @return void
 */
function communicationstoday_sticky_posts_add_meta_box() {
	add_meta_box(
		'communicationstoday_sticky_post',
		__( 'Pin to category top', 'communicationstoday' ),
		'communicationstoday_sticky_posts_meta_box_render',
		'post',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'communicationstoday_sticky_posts_add_meta_box' );

/**
 * Meta box markup.
 *
 * @param WP_Post $post Post object.
 * @return void
 */
function communicationstoday_sticky_posts_meta_box_render( $post ) {
	wp_nonce_field( 'communicationstoday_save_sticky_post', 'communicationstoday_sticky_post_nonce' );

	// Read meta directly in admin (do not auto-clear expired pins while rendering the edit screen).
	$enabled = '1' === get_post_meta( $post->ID, COMMUNICATIONSTODAY_STICKY_META_ENABLED, true );
	$duration = get_post_meta( $post->ID, COMMUNICATIONSTODAY_STICKY_META_DURATION, true );
	if ( ! is_string( $duration ) || ! in_array( $duration, communicationstoday_sticky_duration_choices(), true ) ) {
		$duration = 'week';
	}

	$until = absint( get_post_meta( $post->ID, COMMUNICATIONSTODAY_STICKY_META_UNTIL, true ) );
	?>
	<p>
		<label>
			<input type="checkbox" name="ct_sticky_enabled" value="1" <?php checked( $enabled ); ?>>
			<?php esc_html_e( 'Pin this post to the top of its category on the homepage and category archive', 'communicationstoday' ); ?>
		</label>
	</p>
	<p>
		<label for="ct_sticky_duration"><?php esc_html_e( 'Pin duration', 'communicationstoday' ); ?></label><br>
		<select name="ct_sticky_duration" id="ct_sticky_duration" class="widefat">
			<option value="day" <?php selected( $duration, 'day' ); ?>><?php esc_html_e( '1 day', 'communicationstoday' ); ?></option>
			<option value="week" <?php selected( $duration, 'week' ); ?>><?php esc_html_e( '1 week', 'communicationstoday' ); ?></option>
			<option value="month" <?php selected( $duration, 'month' ); ?>><?php esc_html_e( '1 month', 'communicationstoday' ); ?></option>
			<option value="manual" <?php selected( $duration, 'manual' ); ?>><?php esc_html_e( 'Until manually unpinned', 'communicationstoday' ); ?></option>
		</select>
	</p>
	<?php if ( $enabled && $until > 0 ) : ?>
		<p class="description">
			<?php
			printf(
				/* translators: %s: formatted date/time */
				esc_html__( 'Auto-unpins on: %s', 'communicationstoday' ),
				esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $until ) )
			);
			?>
		</p>
	<?php elseif ( $enabled ) : ?>
		<p class="description"><?php esc_html_e( 'Pinned until you uncheck this box and update the post.', 'communicationstoday' ); ?></p>
	<?php endif; ?>
	<p class="description"><?php esc_html_e( 'Pinned posts appear first on the site. Among pinned items, order follows Posts → Story Order (menu order).', 'communicationstoday' ); ?></p>
	<?php
}

/**
 * Save sticky meta from the post editor.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function communicationstoday_sticky_posts_save_meta( $post_id, $post = null, $update = null ) {
	static $saving = false;

	if ( $saving ) {
		return;
	}

	if ( ! isset( $_POST['communicationstoday_sticky_post_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['communicationstoday_sticky_post_nonce'] ) ), 'communicationstoday_save_sticky_post' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'post' !== get_post_type( $post_id ) ) {
		return;
	}

	$saving = true;

	$enable = ! empty( $_POST['ct_sticky_enabled'] );
	if ( ! $enable ) {
		communicationstoday_clear_post_sticky_meta( $post_id );
		$saving = false;
		return;
	}

	$duration = isset( $_POST['ct_sticky_duration'] ) ? sanitize_key( wp_unslash( $_POST['ct_sticky_duration'] ) ) : 'week';
	if ( ! in_array( $duration, communicationstoday_sticky_duration_choices(), true ) ) {
		$duration = 'week';
	}

	$was_active     = '1' === get_post_meta( $post_id, COMMUNICATIONSTODAY_STICKY_META_ENABLED, true );
	$old_duration   = get_post_meta( $post_id, COMMUNICATIONSTODAY_STICKY_META_DURATION, true );
	$duration_changed = ! is_string( $old_duration ) || $old_duration !== $duration;

	update_post_meta( $post_id, COMMUNICATIONSTODAY_STICKY_META_ENABLED, '1' );
	update_post_meta( $post_id, COMMUNICATIONSTODAY_STICKY_META_DURATION, $duration );

	if ( ! $was_active || $duration_changed ) {
		$seconds = communicationstoday_sticky_duration_seconds( $duration );
		$until   = $seconds > 0 ? time() + $seconds : 0;
		update_post_meta( $post_id, COMMUNICATIONSTODAY_STICKY_META_UNTIL, (string) $until );
	}

	if ( ! $was_active ) {
		update_post_meta( $post_id, COMMUNICATIONSTODAY_STICKY_META_PINNED_AT, (string) time() );
	}

	$saving = false;
}
add_action( 'save_post_post', 'communicationstoday_sticky_posts_save_meta', 10, 3 );

/**
 * Posts list table: pinned indicator column.
 *
 * @param string[] $columns Columns.
 * @return string[]
 */
function communicationstoday_sticky_posts_columns( $columns ) {
	$columns['ct_pinned'] = __( 'Pinned', 'communicationstoday' );
	return $columns;
}
add_filter( 'manage_post_posts_columns', 'communicationstoday_sticky_posts_columns' );

/**
 * Posts list table: pinned column content.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function communicationstoday_sticky_posts_column_content( $column, $post_id ) {
	if ( 'ct_pinned' !== $column ) {
		return;
	}
	if ( ! communicationstoday_is_post_sticky_active( $post_id ) ) {
		echo '—';
		return;
	}
	$duration = get_post_meta( $post_id, COMMUNICATIONSTODAY_STICKY_META_DURATION, true );
	$labels   = array(
		'day'    => __( '1 day', 'communicationstoday' ),
		'week'   => __( '1 week', 'communicationstoday' ),
		'month'  => __( '1 month', 'communicationstoday' ),
		'manual' => __( 'Manual', 'communicationstoday' ),
	);
	$label    = isset( $labels[ $duration ] ) ? $labels[ $duration ] : __( 'Pinned', 'communicationstoday' );
	echo '<span class="dashicons dashicons-admin-post" title="' . esc_attr( $label ) . '" aria-hidden="true"></span>';
}
add_action( 'manage_post_posts_custom_column', 'communicationstoday_sticky_posts_column_content', 10, 2 );
