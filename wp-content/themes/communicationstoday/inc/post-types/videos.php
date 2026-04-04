<?php
/**
 * Custom post type: Videos (title, featured image = poster, video URL, duration).
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'COMMUNICATIONSTODAY_VIDEO_URL_META', 'communicationstoday_video_url' );
define( 'COMMUNICATIONSTODAY_VIDEO_DURATION_META', 'communicationstoday_video_duration' );

/**
 * Register Videos post type and meta.
 */
function communicationstoday_register_video_post_type() {
	$labels = array(
		'name'               => esc_html__( 'Videos', 'communicationstoday' ),
		'singular_name'      => esc_html__( 'Video', 'communicationstoday' ),
		'menu_name'          => esc_html__( 'Videos', 'communicationstoday' ),
		'add_new'            => esc_html__( 'Add New', 'communicationstoday' ),
		'add_new_item'       => esc_html__( 'Add New Video', 'communicationstoday' ),
		'edit_item'          => esc_html__( 'Edit Video', 'communicationstoday' ),
		'new_item'           => esc_html__( 'New Video', 'communicationstoday' ),
		'view_item'          => esc_html__( 'View Video', 'communicationstoday' ),
		'search_items'       => esc_html__( 'Search Videos', 'communicationstoday' ),
		'not_found'          => esc_html__( 'No videos found.', 'communicationstoday' ),
		'not_found_in_trash' => esc_html__( 'No videos found in Trash.', 'communicationstoday' ),
	);

	register_post_type(
		'ct_video',
		array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'videos' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 6,
			'menu_icon'          => 'dashicons-video-alt3',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		)
	);

	register_post_meta(
		'ct_video',
		COMMUNICATIONSTODAY_VIDEO_URL_META,
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'auth_callback'     => 'communicationstoday_video_meta_auth',
		)
	);

	register_post_meta(
		'ct_video',
		COMMUNICATIONSTODAY_VIDEO_DURATION_META,
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'auth_callback'     => 'communicationstoday_video_meta_auth',
		)
	);
}
add_action( 'init', 'communicationstoday_register_video_post_type' );

/**
 * @return bool
 */
function communicationstoday_video_meta_auth() {
	return current_user_can( 'edit_posts' );
}

/**
 * Meta box: video link + duration (featured image = poster in sidebar).
 */
function communicationstoday_video_add_meta_box() {
	add_meta_box(
		'communicationstoday_video_details',
		esc_html__( 'Video details', 'communicationstoday' ),
		'communicationstoday_video_meta_box_callback',
		'ct_video',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'communicationstoday_video_add_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function communicationstoday_video_meta_box_callback( $post ) {
	wp_nonce_field( 'communicationstoday_save_video_meta', 'communicationstoday_video_meta_nonce' );
	$url      = get_post_meta( $post->ID, COMMUNICATIONSTODAY_VIDEO_URL_META, true );
	$duration = get_post_meta( $post->ID, COMMUNICATIONSTODAY_VIDEO_DURATION_META, true );
	?>
	<p>
		<label for="communicationstoday_video_url"><strong><?php esc_html_e( 'Video link', 'communicationstoday' ); ?></strong></label><br>
		<input type="url" class="large-text" id="communicationstoday_video_url" name="communicationstoday_video_url" value="<?php echo esc_attr( (string) $url ); ?>" placeholder="https://">
	</p>
	<p class="description"><?php esc_html_e( 'YouTube, Vimeo, or direct link (.mp4, .webm, etc.).', 'communicationstoday' ); ?></p>
	<p>
		<label for="communicationstoday_video_duration"><strong><?php esc_html_e( 'Total time', 'communicationstoday' ); ?></strong></label><br>
		<input type="text" class="regular-text" id="communicationstoday_video_duration" name="communicationstoday_video_duration" value="<?php echo esc_attr( (string) $duration ); ?>" placeholder="<?php esc_attr_e( 'e.g. 12:45 or 1:05:30', 'communicationstoday' ); ?>">
	</p>
	<p class="description"><?php esc_html_e( 'Use the Featured image box (sidebar) for the video poster / thumbnail.', 'communicationstoday' ); ?></p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function communicationstoday_save_video_meta( $post_id ) {
	if ( ! isset( $_POST['communicationstoday_video_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['communicationstoday_video_meta_nonce'] ) ), 'communicationstoday_save_video_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( 'ct_video' !== get_post_type( $post_id ) ) {
		return;
	}

	$url = isset( $_POST['communicationstoday_video_url'] ) ? esc_url_raw( wp_unslash( $_POST['communicationstoday_video_url'] ) ) : '';
	update_post_meta( $post_id, COMMUNICATIONSTODAY_VIDEO_URL_META, $url );

	$duration = isset( $_POST['communicationstoday_video_duration'] ) ? sanitize_text_field( wp_unslash( $_POST['communicationstoday_video_duration'] ) ) : '';
	update_post_meta( $post_id, COMMUNICATIONSTODAY_VIDEO_DURATION_META, $duration );
}
add_action( 'save_post_ct_video', 'communicationstoday_save_video_meta' );

/**
 * Flush rewrite rules when theme is activated (CPT is registered on init).
 */
function communicationstoday_video_flush_rewrites() {
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'communicationstoday_video_flush_rewrites' );

/**
 * @param int $post_id Post ID.
 * @return array{url: string, duration: string}
 */
function communicationstoday_get_video_details( $post_id ) {
	$post_id = absint( $post_id );
	return array(
		'url'      => $post_id ? (string) get_post_meta( $post_id, COMMUNICATIONSTODAY_VIDEO_URL_META, true ) : '',
		'duration' => $post_id ? (string) get_post_meta( $post_id, COMMUNICATIONSTODAY_VIDEO_DURATION_META, true ) : '',
	);
}
