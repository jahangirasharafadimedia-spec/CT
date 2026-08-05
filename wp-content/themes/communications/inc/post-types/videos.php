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
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
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
	<p class="description"><?php esc_html_e( 'Use the Video banner → Detail banner field for listing + detail images. Featured image is fallback / video poster.', 'communicationstoday' ); ?></p>
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
 * Register ACF fields for videos.
 */
function communicationstoday_register_video_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ct_video_detail',
			'title'                 => esc_html__( 'Video banner', 'communicationstoday' ),
			'fields'                => array(
				array(
					'key'               => 'field_ct_video_detail_banner',
					'label'             => esc_html__( 'Detail banner', 'communicationstoday' ),
					'name'              => 'detail_banner',
					'type'              => 'image',
					'instructions'      => esc_html__( 'Shown on the video detail page and video listing cards. Featured image is used only as fallback / video poster.', 'communicationstoday' ),
					'required'          => 0,
					'return_format'     => 'id',
					'preview_size'      => 'medium',
					'library'           => 'all',
					'mime_types'        => 'jpg,jpeg,png,webp,gif',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ct_video',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
add_action( 'acf/init', 'communicationstoday_register_video_acf_fields' );

/**
 * @param int $post_id Post ID.
 * @return int Attachment ID for detail banner, or 0.
 */
function communicationstoday_get_video_detail_banner_id( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id || ! function_exists( 'get_field' ) ) {
		return 0;
	}

	$value = get_field( 'detail_banner', $post_id );
	if ( function_exists( 'communicationstoday_acf_image_value_to_attachment_id' ) ) {
		return communicationstoday_acf_image_value_to_attachment_id( $value );
	}

	return is_numeric( $value ) ? absint( $value ) : 0;
}

/**
 * Image for video listing / cards: detail_banner first, then featured image.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Image size.
 * @return string Image URL or empty.
 */
function communicationstoday_get_video_card_image_url( $post_id = 0, $size = 'medium_large' ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return '';
	}

	$banner_id = communicationstoday_get_video_detail_banner_id( $post_id );
	if ( $banner_id > 0 ) {
		$url = wp_get_attachment_image_url( $banner_id, $size );
		if ( $url ) {
			return (string) $url;
		}
	}

	$url = get_the_post_thumbnail_url( $post_id, $size );
	return $url ? (string) $url : '';
}

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

/**
 * Output video player markup for a video post.
 *
 * @param int $post_id Post ID.
 */
function communicationstoday_render_video_player( $post_id = 0 ) {
	$post_id   = $post_id ? absint( $post_id ) : get_the_ID();
	$details   = communicationstoday_get_video_details( $post_id );
	$video_url = $details['url'];

	if ( ! $video_url ) {
		return;
	}

	$poster_id = get_post_thumbnail_id( $post_id );
	$poster    = $poster_id ? wp_get_attachment_image_url( $poster_id, 'large' ) : '';

	$embed = wp_oembed_get( $video_url, array( 'width' => 848 ) );
	if ( $embed ) {
		echo '<div class="video-embed-responsive">' . $embed . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} elseif ( preg_match( '/\.(mp4|webm|ogg)(\?.*)?$/i', $video_url ) ) {
		?>
		<video class="w-100 video-player-native" controls playsinline <?php echo $poster ? 'poster="' . esc_url( $poster ) . '"' : ''; ?>>
			<source src="<?php echo esc_url( $video_url ); ?>">
		</video>
		<?php
	} else {
		printf(
			'<p><a class="button" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a></p>',
			esc_url( $video_url ),
			esc_html__( 'Open video', 'communicationstoday' )
		);
	}
}
