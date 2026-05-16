<?php
/**
 * Duplicate any post type (posts, pages, CPTs) as draft with full meta + terms copy.
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post types that cannot be duplicated.
 *
 * @return string[]
 */
function communicationstoday_duplicate_excluded_post_types() {
	return apply_filters(
		'communicationstoday_duplicate_excluded_post_types',
		array(
			'attachment',
			'revision',
			'nav_menu_item',
			'custom_css',
			'customize_changeset',
			'oembed_cache',
			'user_request',
			'wp_block',
			'wp_template',
			'wp_template_part',
			'wp_global_styles',
			'wp_navigation',
		)
	);
}

/**
 * Post meta keys to skip when copying.
 *
 * @return string[]
 */
function communicationstoday_duplicate_skip_meta_keys() {
	return apply_filters(
		'communicationstoday_duplicate_skip_meta_keys',
		array(
			'_edit_lock',
			'_edit_last',
			'_wp_old_slug',
			'_wp_trash_meta_status',
			'_wp_trash_meta_time',
		)
	);
}

/**
 * Register Duplicate row action for every admin post type.
 *
 * @return void
 */
function communicationstoday_register_duplicate_row_actions() {
	$post_types = get_post_types( array( 'show_ui' => true ), 'names' );

	foreach ( $post_types as $post_type ) {
		if ( in_array( $post_type, communicationstoday_duplicate_excluded_post_types(), true ) ) {
			continue;
		}

		add_filter( "{$post_type}_row_actions", 'communicationstoday_duplicate_post_row_action', 10, 2 );
	}
}
add_action( 'admin_init', 'communicationstoday_register_duplicate_row_actions' );

/**
 * Duplicate link in Posts / Pages list tables.
 *
 * @param string[] $actions Row actions.
 * @param WP_Post  $post    Post object.
 * @return string[]
 */
function communicationstoday_duplicate_post_row_action( $actions, $post ) {
	if ( ! ( $post instanceof WP_Post ) ) {
		return $actions;
	}

	$post_type_object = get_post_type_object( $post->post_type );
	if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
		return $actions;
	}

	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return $actions;
	}

	$url = wp_nonce_url(
		add_query_arg(
			array(
				'action'  => 'communicationstoday_duplicate_post',
				'post_id' => (int) $post->ID,
			),
			admin_url( 'admin.php' )
		),
		'communicationstoday_duplicate_post_' . (int) $post->ID
	);

	$actions['communicationstoday_duplicate'] = sprintf(
		'<a href="%s" aria-label="%s">%s</a>',
		esc_url( $url ),
		esc_attr(
			sprintf(
				/* translators: %s: post title */
				__( 'Duplicate “%s” as draft', 'communicationstoday' ),
				$post->post_title
			)
		),
		esc_html__( 'Duplicate', 'communicationstoday' )
	);

	return $actions;
}

/**
 * Handle duplicate action from admin.
 *
 * @return void
 */
function communicationstoday_handle_duplicate_post_action() {
	if ( ! isset( $_GET['post_id'] ) ) {
		wp_die( esc_html__( 'Invalid duplicate request.', 'communicationstoday' ) );
	}

	$post_id = absint( $_GET['post_id'] );
	if ( $post_id < 1 ) {
		wp_die( esc_html__( 'Invalid post ID.', 'communicationstoday' ) );
	}

	check_admin_referer( 'communicationstoday_duplicate_post_' . $post_id );

	$post = get_post( $post_id );
	if ( ! $post ) {
		wp_die( esc_html__( 'Post not found.', 'communicationstoday' ) );
	}

	$post_type_object = get_post_type_object( $post->post_type );
	if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
		wp_die( esc_html__( 'You are not allowed to duplicate this item.', 'communicationstoday' ) );
	}

	$new_id = communicationstoday_duplicate_post_as_draft( $post_id );

	if ( is_wp_error( $new_id ) ) {
		wp_die( esc_html( $new_id->get_error_message() ) );
	}

	$redirect = add_query_arg(
		array(
			'post'                 => $new_id,
			'action'               => 'edit',
			'communicationstoday_duplicated' => '1',
		),
		admin_url( 'post.php' )
	);

	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_action_communicationstoday_duplicate_post', 'communicationstoday_handle_duplicate_post_action' );

/**
 * Duplicate a post (all fields, meta, taxonomies) as draft. Title gets " Copy" suffix.
 *
 * @param int $post_id Source post ID.
 * @return int|WP_Error New post ID or error.
 */
function communicationstoday_duplicate_post_as_draft( $post_id ) {
	$post_id = absint( $post_id );
	$post    = get_post( $post_id );

	if ( ! $post ) {
		return new WP_Error( 'invalid_post', __( 'Post not found.', 'communicationstoday' ) );
	}

	if ( in_array( $post->post_type, communicationstoday_duplicate_excluded_post_types(), true ) ) {
		return new WP_Error( 'invalid_type', __( 'This item type cannot be duplicated.', 'communicationstoday' ) );
	}

	$title_suffix = apply_filters( 'communicationstoday_duplicate_title_suffix', ' Copy', $post );
	$new_title    = $post->post_title . $title_suffix;

	$new_post_data = array(
		'post_title'     => $new_title,
		'post_content'   => $post->post_content,
		'post_excerpt'   => $post->post_excerpt,
		'post_status'    => 'draft',
		'post_type'      => $post->post_type,
		'post_author'    => get_current_user_id(),
		'post_parent'    => (int) $post->post_parent,
		'menu_order'     => (int) $post->menu_order,
		'comment_status' => $post->comment_status,
		'ping_status'    => $post->ping_status,
		'post_password'  => $post->post_password,
	);

	$new_post_id = wp_insert_post( wp_slash( $new_post_data ), true );

	if ( is_wp_error( $new_post_id ) ) {
		return $new_post_id;
	}

	communicationstoday_duplicate_copy_post_meta( $post_id, $new_post_id );
	communicationstoday_duplicate_copy_taxonomies( $post_id, $new_post_id, $post->post_type );

	/**
	 * After a post is duplicated.
	 *
	 * @param int $new_post_id New post ID.
	 * @param int $post_id     Source post ID.
	 */
	do_action( 'communicationstoday_after_duplicate_post', $new_post_id, $post_id );

	return (int) $new_post_id;
}

/**
 * Copy all post meta (ACF and custom fields included).
 *
 * @param int $source_id Source post ID.
 * @param int $target_id New post ID.
 * @return void
 */
function communicationstoday_duplicate_copy_post_meta( $source_id, $target_id ) {
	$skip_keys = communicationstoday_duplicate_skip_meta_keys();
	$meta      = get_post_meta( $source_id );

	if ( ! is_array( $meta ) ) {
		return;
	}

	foreach ( $meta as $meta_key => $values ) {
		if ( in_array( $meta_key, $skip_keys, true ) ) {
			continue;
		}

		if ( ! is_array( $values ) ) {
			continue;
		}

		foreach ( $values as $value ) {
			add_post_meta( $target_id, $meta_key, maybe_unserialize( $value ) );
		}
	}
}

/**
 * Copy category / tag / custom taxonomy terms.
 *
 * @param int    $source_id Source post ID.
 * @param int    $target_id New post ID.
 * @param string $post_type Post type.
 * @return void
 */
function communicationstoday_duplicate_copy_taxonomies( $source_id, $target_id, $post_type ) {
	$taxonomies = get_object_taxonomies( $post_type, 'names' );

	foreach ( $taxonomies as $taxonomy ) {
		$term_ids = wp_get_object_terms(
			$source_id,
			$taxonomy,
			array(
				'fields' => 'ids',
			)
		);

		if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
			continue;
		}

		wp_set_object_terms( $target_id, array_map( 'intval', $term_ids ), $taxonomy );
	}
}

/**
 * Admin notice after successful duplicate.
 *
 * @return void
 */
function communicationstoday_duplicate_admin_notice() {
	if ( ! is_admin() || ! isset( $_GET['communicationstoday_duplicated'] ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'post' !== $screen->base ) {
		return;
	}

	echo '<div class="notice notice-success is-dismissible"><p>';
	esc_html_e( 'Draft duplicate created. Title includes “Copy”. Review and publish when ready.', 'communicationstoday' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'communicationstoday_duplicate_admin_notice' );

/**
 * Duplicate button on post edit screen (Publish box).
 *
 * @param WP_Post $post Post object.
 * @return void
 */
function communicationstoday_duplicate_submitbox_action( $post ) {
	if ( ! ( $post instanceof WP_Post ) ) {
		return;
	}

	if ( in_array( $post->post_type, communicationstoday_duplicate_excluded_post_types(), true ) ) {
		return;
	}

	$post_type_object = get_post_type_object( $post->post_type );
	if ( ! $post_type_object || ! current_user_can( 'edit_post', $post->ID ) ) {
		return;
	}

	$url = wp_nonce_url(
		add_query_arg(
			array(
				'action'  => 'communicationstoday_duplicate_post',
				'post_id' => (int) $post->ID,
			),
			admin_url( 'admin.php' )
		),
		'communicationstoday_duplicate_post_' . (int) $post->ID
	);
	?>
	<div class="misc-pub-section communicationstoday-duplicate-section">
		<a href="<?php echo esc_url( $url ); ?>" class="button button-secondary">
			<?php esc_html_e( 'Duplicate', 'communicationstoday' ); ?>
		</a>
		<span class="description" style="display:block;margin-top:4px;">
			<?php esc_html_e( 'Creates a draft copy with all fields and custom data.', 'communicationstoday' ); ?>
		</span>
	</div>
	<?php
}
add_action( 'post_submitbox_misc_actions', 'communicationstoday_duplicate_submitbox_action' );
