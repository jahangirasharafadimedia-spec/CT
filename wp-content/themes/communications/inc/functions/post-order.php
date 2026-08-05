<?php
/**
 * Manual story order via post menu_order (drag-and-drop in admin).
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enable menu_order on posts.
 *
 * @return void
 */
function communicationstoday_post_order_init() {
	add_post_type_support( 'post', 'page-attributes' );
}
add_action( 'init', 'communicationstoday_post_order_init', 20 );

/**
 * Get menu_order for a post (WP post field).
 *
 * @param int $post_id Post ID.
 * @return int
 */
function communicationstoday_get_post_menu_order( $post_id ) {
	return (int) get_post_field( 'menu_order', absint( $post_id ) );
}

/**
 * Sort post IDs by menu_order ASC, then publish date DESC.
 *
 * @param int[] $post_ids Post IDs.
 * @return int[]
 */
function communicationstoday_sort_post_ids_by_menu_order( array $post_ids ) {
	$post_ids = array_values( array_unique( array_map( 'absint', $post_ids ) ) );
	if ( count( $post_ids ) < 2 ) {
		return $post_ids;
	}

	usort(
		$post_ids,
		static function ( $a, $b ) {
			$a_order = communicationstoday_get_post_menu_order( $a );
			$b_order = communicationstoday_get_post_menu_order( $b );
			if ( $a_order !== $b_order ) {
				return $a_order <=> $b_order;
			}
			$a_date = (int) get_post_time( 'U', true, $a );
			$b_date = (int) get_post_time( 'U', true, $b );
			if ( $a_date === $b_date ) {
				return $b <=> $a;
			}
			return $b_date <=> $a_date;
		}
	);

	return $post_ids;
}

/**
 * Sticky posts first (each group by menu_order), for homepage / archives.
 *
 * @param int[] $post_ids    Post IDs.
 * @param int   $category_id Category term ID.
 * @return int[]
 */
function communicationstoday_sort_post_ids_for_category_display( array $post_ids, $category_id ) {
	$post_ids    = array_values( array_unique( array_map( 'absint', $post_ids ) ) );
	$category_id = absint( $category_id );

	if ( empty( $post_ids ) ) {
		return $post_ids;
	}

	$sticky = array();
	if ( $category_id > 0 && function_exists( 'communicationstoday_get_active_sticky_ids_for_category' ) ) {
		$sticky = communicationstoday_get_active_sticky_ids_for_category( $category_id );
		$sticky = array_values( array_intersect( $sticky, $post_ids ) );
	}

	$rest = array_values( array_diff( $post_ids, $sticky ) );

	return array_merge(
		communicationstoday_sort_post_ids_by_menu_order( $sticky ),
		communicationstoday_sort_post_ids_by_menu_order( $rest )
	);
}

/**
 * WP_Query orderby args: menu_order then date.
 *
 * @return array<string, string>
 */
function communicationstoday_get_menu_order_query_orderby() {
	return array(
		'menu_order' => 'ASC',
		'date'       => 'DESC',
	);
}

/**
 * Category archive main query: manual order when applicable.
 *
 * @param WP_Query $query Query.
 * @return void
 */
function communicationstoday_archive_posts_orderby_menu_order( $query ) {
	if ( is_admin() || ! ( $query instanceof WP_Query ) || ! $query->is_main_query() ) {
		return;
	}

	if ( ! is_category() && ! is_post_type_archive( 'ct_video' ) ) {
		return;
	}

	$query->set( 'orderby', communicationstoday_get_menu_order_query_orderby() );
}
add_action( 'pre_get_posts', 'communicationstoday_archive_posts_orderby_menu_order' );

/**
 * Admin submenu: drag-and-drop story order per category.
 *
 * @return void
 */
function communicationstoday_post_order_admin_menu() {
	add_posts_page(
		__( 'Story Order', 'communicationstoday' ),
		__( 'Story Order', 'communicationstoday' ),
		'edit_others_posts',
		'communicationstoday-story-order',
		'communicationstoday_post_order_admin_page_render'
	);
}
add_action( 'admin_menu', 'communicationstoday_post_order_admin_menu' );

/**
 * Admin page markup.
 *
 * @return void
 */
function communicationstoday_post_order_admin_page_render() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
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

	$category_id = isset( $_GET['cat'] ) ? absint( $_GET['cat'] ) : 0;
	$posts       = array();

	if ( $category_id > 0 ) {
		$q = new WP_Query(
			array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => 100,
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'orderby'                => communicationstoday_get_menu_order_query_orderby(),
				'tax_query'              => array(
					array(
						'taxonomy'         => 'category',
						'field'            => 'term_id',
						'terms'            => $category_id,
						'include_children' => true,
					),
				),
				'update_post_meta_cache' => false,
			)
		);

		if ( $q->have_posts() ) {
			$ids = wp_list_pluck( $q->posts, 'ID' );
			$ids = communicationstoday_sort_post_ids_for_category_display( $ids, $category_id );
			foreach ( $ids as $pid ) {
				$post = get_post( $pid );
				if ( $post ) {
					$posts[] = $post;
				}
			}
		}
		wp_reset_postdata();
	}

	$cat_name = $category_id > 0 ? get_cat_name( $category_id ) : '';
	?>
	<div class="wrap communicationstoday-story-order-wrap">
		<h1><?php esc_html_e( 'Story Order', 'communicationstoday' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Drag posts to set homepage and category archive order. Pinned (sticky) posts stay at the top on the site; order them here among other pinned items. Order is saved as post menu order.', 'communicationstoday' ); ?>
		</p>

		<form method="get" action="">
			<input type="hidden" name="page" value="communicationstoday-story-order">
			<p>
				<label for="ct-story-order-cat"><strong><?php esc_html_e( 'Category', 'communicationstoday' ); ?></strong></label><br>
				<select name="cat" id="ct-story-order-cat" class="regular-text">
					<option value="0"><?php esc_html_e( 'Select category', 'communicationstoday' ); ?></option>
					<?php foreach ( $categories as $cat ) : ?>
						<option value="<?php echo esc_attr( (string) (int) $cat->term_id ); ?>" <?php selected( $category_id, (int) $cat->term_id ); ?>>
							<?php echo esc_html( $cat->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="button"><?php esc_html_e( 'Load posts', 'communicationstoday' ); ?></button>
			</p>
		</form>

		<?php if ( $category_id > 0 ) : ?>
			<?php if ( empty( $posts ) ) : ?>
				<p><?php esc_html_e( 'No published posts in this category.', 'communicationstoday' ); ?></p>
			<?php else : ?>
				<h2>
					<?php
					printf(
						/* translators: %s: category name */
						esc_html__( 'Order for: %s', 'communicationstoday' ),
						esc_html( $cat_name )
					);
					?>
				</h2>
				<p>
					<button type="button" class="button button-primary" id="ct-story-order-save">
						<?php esc_html_e( 'Save order', 'communicationstoday' ); ?>
					</button>
					<span class="ct-story-order-status" aria-live="polite"></span>
				</p>
				<ul id="ct-story-order-list" class="ct-story-order-list" data-category-id="<?php echo esc_attr( (string) $category_id ); ?>">
					<?php foreach ( $posts as $post ) : ?>
						<?php
						$is_sticky = function_exists( 'communicationstoday_is_post_sticky_active' ) && communicationstoday_is_post_sticky_active( $post->ID );
						?>
						<li class="ct-story-order-item" data-post-id="<?php echo esc_attr( (string) (int) $post->ID ); ?>">
							<span class="ct-story-order-handle dashicons dashicons-menu" aria-hidden="true"></span>
							<span class="ct-story-order-title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
							<?php if ( $is_sticky ) : ?>
								<span class="ct-story-order-badge"><?php esc_html_e( 'Pinned', 'communicationstoday' ); ?></span>
							<?php endif; ?>
							<span class="ct-story-order-meta">
								<?php
								printf(
									/* translators: %d: menu order number */
									esc_html__( 'Order: %d', 'communicationstoday' ),
									(int) $post->menu_order
								);
								?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Category ID from Posts list screen query string (cat filter).
 *
 * @return int
 */
function communicationstoday_get_posts_list_category_filter_id() {
	if ( ! is_admin() ) {
		return 0;
	}
	return isset( $_GET['cat'] ) ? absint( $_GET['cat'] ) : 0;
}

/**
 * Posts list table: default sort by menu_order.
 *
 * @param WP_Query $query Query.
 * @return void
 */
function communicationstoday_admin_posts_list_orderby_menu_order( $query ) {
	if ( ! is_admin() || ! ( $query instanceof WP_Query ) || ! $query->is_main_query() ) {
		return;
	}

	global $pagenow;
	if ( 'edit.php' !== $pagenow ) {
		return;
	}

	$post_type = $query->get( 'post_type' );
	if ( empty( $post_type ) ) {
		$post_type = 'post';
	}
	if ( 'post' !== $post_type ) {
		return;
	}

	if ( $query->get( 'orderby' ) ) {
		return;
	}

	$query->set( 'orderby', communicationstoday_get_menu_order_query_orderby() );
}
add_action( 'pre_get_posts', 'communicationstoday_admin_posts_list_orderby_menu_order' );

/**
 * Hint above Posts list table.
 *
 * @return void
 */
function communicationstoday_posts_list_order_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-post' !== $screen->id ) {
		return;
	}
	?>
	<div class="notice notice-info ct-posts-order-notice">
		<p>
			<?php esc_html_e( 'Drag the ≡ handle in the Order column to reorder stories. Order saves automatically.', 'communicationstoday' ); ?>
			<span class="ct-posts-order-notice-status" aria-live="polite"></span>
		</p>
	</div>
	<?php
}
add_action( 'all_admin_notices', 'communicationstoday_posts_list_order_notice' );

/**
 * Enqueue admin assets for story order screen and Posts list.
 *
 * @param string $hook_suffix Admin hook.
 * @return void
 */
function communicationstoday_post_order_admin_assets( $hook_suffix ) {
	$on_story_order = ( 'posts_page_communicationstoday-story-order' === $hook_suffix );
	$on_posts_list  = false;

	if ( 'edit.php' === $hook_suffix ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$on_posts_list = $screen && 'edit-post' === $screen->id;
	}

	if ( ! $on_story_order && ! $on_posts_list ) {
		return;
	}

	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_style(
		'communicationstoday-post-order-admin',
		get_template_directory_uri() . '/css/post-order-admin.css',
		array(),
		defined( '_S_VERSION' ) ? _S_VERSION : '1.0.0'
	);
	wp_enqueue_script(
		'communicationstoday-post-order-admin',
		get_template_directory_uri() . '/js/post-order-admin.js',
		array( 'jquery', 'jquery-ui-sortable' ),
		defined( '_S_VERSION' ) ? _S_VERSION : '1.0.0',
		true
	);
	wp_localize_script(
		'communicationstoday-post-order-admin',
		'communicationstodayPostOrder',
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'communicationstoday_save_post_order' ),
			'categoryId' => communicationstoday_get_posts_list_category_filter_id(),
			'context'    => $on_posts_list ? 'posts_list' : 'story_order',
			'i18n'       => array(
				'saving'  => __( 'Saving…', 'communicationstoday' ),
				'saved'   => __( 'Order saved.', 'communicationstoday' ),
				'error'   => __( 'Could not save order. Try again.', 'communicationstoday' ),
				'missing' => __( 'Select a category and drag posts before saving.', 'communicationstoday' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'communicationstoday_post_order_admin_assets' );

/**
 * AJAX: save menu_order for posts in list order.
 *
 * @return void
 */
function communicationstoday_ajax_save_post_order() {
	check_ajax_referer( 'communicationstoday_save_post_order', 'nonce' );

	if ( ! current_user_can( 'edit_others_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'communicationstoday' ) ) );
	}

	$category_id = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
	$post_ids    = isset( $_POST['post_ids'] ) ? wp_unslash( $_POST['post_ids'] ) : array();

	if ( ! is_array( $post_ids ) || empty( $post_ids ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid data.', 'communicationstoday' ) ) );
	}

	$post_ids = array_values( array_unique( array_map( 'absint', $post_ids ) ) );
	$position = 0;

	global $wpdb;

	foreach ( $post_ids as $post_id ) {
		if ( $post_id <= 0 || 'post' !== get_post_type( $post_id ) ) {
			continue;
		}
		if ( $category_id > 0 && function_exists( 'communicationstoday_post_in_category_tree' ) && ! communicationstoday_post_in_category_tree( $post_id, $category_id ) ) {
			continue;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			continue;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$wpdb->posts,
			array( 'menu_order' => $position ),
			array( 'ID' => $post_id ),
			array( '%d' ),
			array( '%d' )
		);
		clean_post_cache( $post_id );
		++$position;
	}

	wp_send_json_success(
		array(
			'message' => __( 'Order saved.', 'communicationstoday' ),
			'updated' => $position,
		)
	);
}
add_action( 'wp_ajax_communicationstoday_save_post_order', 'communicationstoday_ajax_save_post_order' );

/**
 * Post editor: manual menu order field.
 *
 * @return void
 */
function communicationstoday_post_order_meta_box() {
	add_meta_box(
		'communicationstoday_post_menu_order',
		__( 'Story display order', 'communicationstoday' ),
		'communicationstoday_post_order_meta_box_render',
		'post',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'communicationstoday_post_order_meta_box' );

/**
 * Meta box: menu_order number.
 *
 * @param WP_Post $post Post.
 * @return void
 */
function communicationstoday_post_order_meta_box_render( $post ) {
	wp_nonce_field( 'communicationstoday_save_post_menu_order', 'communicationstoday_post_menu_order_nonce' );
	$order = (int) $post->menu_order;
	?>
	<p>
		<label for="ct_post_menu_order"><?php esc_html_e( 'Order number', 'communicationstoday' ); ?></label>
		<input type="number" class="small-text" id="ct_post_menu_order" name="ct_post_menu_order" value="<?php echo esc_attr( (string) $order ); ?>" min="0" step="1">
	</p>
	<p class="description">
		<?php esc_html_e( 'Lower numbers appear first. Use Posts → Story Order to drag and drop, or set a number here.', 'communicationstoday' ); ?>
	</p>
	<p class="description">
		<a href="<?php echo esc_url( admin_url( 'edit.php?page=communicationstoday-story-order' ) ); ?>"><?php esc_html_e( 'Open Story Order screen', 'communicationstoday' ); ?></a>
	</p>
	<?php
}

/**
 * Set menu_order during post save (avoids save_post recursion).
 *
 * @param array $data    Post data.
 * @param array $postarr Raw post data.
 * @return array
 */
function communicationstoday_post_order_insert_post_data( $data, $postarr ) {
	if ( ! isset( $_POST['communicationstoday_post_menu_order_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['communicationstoday_post_menu_order_nonce'] ) ), 'communicationstoday_save_post_menu_order' ) ) {
		return $data;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $data;
	}

	if ( ! isset( $_POST['ct_post_menu_order'] ) ) {
		return $data;
	}

	if ( empty( $data['post_type'] ) || 'post' !== $data['post_type'] ) {
		return $data;
	}

	$data['menu_order'] = max( 0, (int) wp_unslash( $_POST['ct_post_menu_order'] ) );

	return $data;
}
add_filter( 'wp_insert_post_data', 'communicationstoday_post_order_insert_post_data', 10, 2 );

/**
 * Posts list: Order column.
 *
 * @param string[] $columns Columns.
 * @return string[]
 */
function communicationstoday_post_order_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['menu_order'] = __( 'Order', 'communicationstoday' );
		}
	}
	return $new;
}
add_filter( 'manage_post_posts_columns', 'communicationstoday_post_order_columns', 15 );

/**
 * Posts list: Order column value.
 *
 * @param string $column  Column.
 * @param int    $post_id Post ID.
 * @return void
 */
function communicationstoday_post_order_column_content( $column, $post_id ) {
	if ( 'menu_order' !== $column ) {
		return;
	}
	$order = communicationstoday_get_post_menu_order( $post_id );
	?>
	<span class="ct-posts-drag-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'communicationstoday' ); ?>" aria-hidden="true"></span>
	<span class="ct-posts-order-num"><?php echo esc_html( (string) $order ); ?></span>
	<?php
}
add_action( 'manage_post_posts_custom_column', 'communicationstoday_post_order_column_content', 10, 2 );
