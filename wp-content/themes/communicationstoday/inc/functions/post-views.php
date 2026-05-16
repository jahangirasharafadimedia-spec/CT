<?php
/**
 * Post view counter — lightweight table + deferred writes (high traffic safe).
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Legacy post meta (migrated to custom table). */
define( 'COMMUNICATIONSTODAY_POST_VIEWS_META', '_ct_post_views' );

/** DB schema version. */
define( 'COMMUNICATIONSTODAY_POST_VIEWS_DB_VERSION', '1.0.0' );

/**
 * Pending view increments (flushed on shutdown).
 *
 * @var array<int, int>
 */
$GLOBALS['communicationstoday_post_views_pending'] = array();

/**
 * Custom table name for view counts.
 *
 * @return string
 */
function communicationstoday_post_views_table_name() {
	global $wpdb;

	return $wpdb->prefix . 'ct_post_views';
}

/**
 * Create / upgrade views table.
 *
 * @return void
 */
function communicationstoday_post_views_install_table() {
	global $wpdb;

	$table   = communicationstoday_post_views_table_name();
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		post_id bigint(20) unsigned NOT NULL,
		views bigint(20) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (post_id),
		KEY views (views)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	update_option( 'communicationstoday_post_views_db_version', COMMUNICATIONSTODAY_POST_VIEWS_DB_VERSION, false );
}

/**
 * Ensure table exists.
 *
 * @return void
 */
function communicationstoday_post_views_maybe_install() {
	$installed = get_option( 'communicationstoday_post_views_db_version', '' );
	if ( COMMUNICATIONSTODAY_POST_VIEWS_DB_VERSION === $installed ) {
		return;
	}

	communicationstoday_post_views_install_table();
	communicationstoday_post_views_migrate_legacy_meta();
}
add_action( 'init', 'communicationstoday_post_views_maybe_install', 5 );

/**
 * Copy old post meta counts into the custom table (one-time).
 *
 * @return void
 */
function communicationstoday_post_views_migrate_legacy_meta() {
	global $wpdb;

	$table = communicationstoday_post_views_table_name();
	$rows  = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != ''",
			COMMUNICATIONSTODAY_POST_VIEWS_META
		)
	);

	if ( empty( $rows ) ) {
		return;
	}

	foreach ( $rows as $row ) {
		$post_id = absint( $row->post_id );
		$views   = max( 0, (int) $row->meta_value );
		if ( $post_id < 1 || $views < 1 ) {
			continue;
		}

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table} (post_id, views) VALUES (%d, %d)
				ON DUPLICATE KEY UPDATE views = GREATEST(views, VALUES(views))",
				$post_id,
				$views
			)
		);
	}
}

/**
 * Get view count for a post (reads custom table).
 *
 * @param int $post_id Post ID.
 * @return int
 */
function communicationstoday_get_post_views( $post_id ) {
	global $wpdb;

	$post_id = absint( $post_id );
	if ( $post_id < 1 ) {
		return 0;
	}

	communicationstoday_post_views_maybe_install();

	$table = communicationstoday_post_views_table_name();
	$views = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT views FROM {$table} WHERE post_id = %d LIMIT 1",
			$post_id
		)
	);

	if ( null !== $views ) {
		return max( 0, (int) $views );
	}

	// Fallback for unmigrated meta.
	return max( 0, (int) get_post_meta( $post_id, COMMUNICATIONSTODAY_POST_VIEWS_META, true ) );
}

/**
 * Queue +1 view (written on shutdown — page response is not blocked).
 *
 * @param int $post_id Post ID.
 * @return void
 */
function communicationstoday_queue_post_view( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id < 1 ) {
		return;
	}

	if ( ! isset( $GLOBALS['communicationstoday_post_views_pending'] ) ) {
		$GLOBALS['communicationstoday_post_views_pending'] = array();
	}

	if ( ! isset( $GLOBALS['communicationstoday_post_views_pending'][ $post_id ] ) ) {
		$GLOBALS['communicationstoday_post_views_pending'][ $post_id ] = 0;
	}

	++$GLOBALS['communicationstoday_post_views_pending'][ $post_id ];
}

/**
 * Atomic increment in DB (single fast query per post).
 *
 * @param int $post_id Post ID.
 * @param int $delta   How much to add.
 * @return void
 */
function communicationstoday_post_views_add_delta( $post_id, $delta ) {
	global $wpdb;

	$post_id = absint( $post_id );
	$delta   = absint( $delta );

	if ( $post_id < 1 || $delta < 1 ) {
		return;
	}

	$table = communicationstoday_post_views_table_name();

	$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO {$table} (post_id, views) VALUES (%d, %d)
			ON DUPLICATE KEY UPDATE views = views + %d",
			$post_id,
			$delta,
			$delta
		)
	);
}

/**
 * Flush queued views to database (end of request).
 *
 * @return void
 */
function communicationstoday_flush_post_view_queue() {
	$pending = isset( $GLOBALS['communicationstoday_post_views_pending'] )
		? $GLOBALS['communicationstoday_post_views_pending']
		: array();

	if ( empty( $pending ) ) {
		return;
	}

	communicationstoday_post_views_maybe_install();

	foreach ( $pending as $post_id => $delta ) {
		communicationstoday_post_views_add_delta( (int) $post_id, (int) $delta );
	}

	$GLOBALS['communicationstoday_post_views_pending'] = array();
}
add_action( 'shutdown', 'communicationstoday_flush_post_view_queue', 999 );

/**
 * Count one view on single post frontend loads (each refresh +1).
 *
 * @return void
 */
function communicationstoday_track_post_view_on_frontend() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	if ( is_preview() || is_feed() || is_trackback() ) {
		return;
	}

	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$post_id = get_queried_object_id();
	if ( $post_id < 1 || 'publish' !== get_post_status( $post_id ) ) {
		return;
	}

	communicationstoday_queue_post_view( $post_id );
}
add_action( 'template_redirect', 'communicationstoday_track_post_view_on_frontend', 20 );

/**
 * Posts list: Views column header.
 *
 * @param string[] $columns Columns.
 * @return string[]
 */
function communicationstoday_post_views_admin_columns( $columns ) {
	$columns['ct_views'] = __( 'Views', 'communicationstoday' );
	return $columns;
}
add_filter( 'manage_post_posts_columns', 'communicationstoday_post_views_admin_columns', 25 );

/**
 * Posts list: Views column value.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function communicationstoday_post_views_admin_column_content( $column, $post_id ) {
	if ( 'ct_views' !== $column ) {
		return;
	}

	echo esc_html( number_format_i18n( communicationstoday_get_post_views( $post_id ) ) );
}
add_action( 'manage_post_posts_custom_column', 'communicationstoday_post_views_admin_column_content', 10, 2 );

/**
 * Sortable Views column.
 *
 * @param string[] $columns Sortable columns.
 * @return string[]
 */
function communicationstoday_post_views_sortable_columns( $columns ) {
	$columns['ct_views'] = 'ct_views';
	return $columns;
}
add_filter( 'manage_edit-post_sortable_columns', 'communicationstoday_post_views_sortable_columns' );

/**
 * Sort Posts list by views (custom table).
 *
 * @param WP_Query $query Query.
 * @return void
 */
function communicationstoday_post_views_admin_sort( $query ) {
	global $wpdb;

	if ( ! is_admin() || ! ( $query instanceof WP_Query ) || ! $query->is_main_query() ) {
		return;
	}

	if ( 'ct_views' !== $query->get( 'orderby' ) ) {
		return;
	}

	$table = communicationstoday_post_views_table_name();
	$query->set( 'orderby', 'ct_views' );
	$query->set(
		'ct_views_join',
		"LEFT JOIN {$table} AS ct_views ON {$wpdb->posts}.ID = ct_views.post_id"
	);
}
add_action( 'pre_get_posts', 'communicationstoday_post_views_admin_sort' );

/**
 * JOIN custom table when sorting by views.
 *
 * @param string   $join  JOIN clause.
 * @param WP_Query $query Query.
 * @return string
 */
function communicationstoday_post_views_posts_join( $join, $query ) {
	if ( ! is_admin() || ! ( $query instanceof WP_Query ) ) {
		return $join;
	}

	$custom_join = $query->get( 'ct_views_join' );
	if ( $custom_join ) {
		$join .= ' ' . $custom_join;
	}

	return $join;
}
add_filter( 'posts_join', 'communicationstoday_post_views_posts_join', 10, 2 );

/**
 * ORDER BY views column.
 *
 * @param string   $orderby ORDER BY clause.
 * @param WP_Query $query   Query.
 * @return string
 */
function communicationstoday_post_views_posts_orderby( $orderby, $query ) {
	if ( ! is_admin() || ! ( $query instanceof WP_Query ) ) {
		return $orderby;
	}

	if ( 'ct_views' !== $query->get( 'orderby' ) ) {
		return $orderby;
	}

	$order = strtoupper( $query->get( 'order' ) );
	$order = 'ASC' === $order ? 'ASC' : 'DESC';

	return "CAST(COALESCE(ct_views.views, 0) AS UNSIGNED) {$order}";
}
add_filter( 'posts_orderby', 'communicationstoday_post_views_posts_orderby', 10, 2 );
