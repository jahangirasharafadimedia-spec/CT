<?php
/**
 * Widgets, widget areas, category image (Think Tank), Perspective carousel helpers, widget admin assets.
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function communicationstoday_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'communicationstoday' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'communicationstoday' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Homepage widget', 'communicationstoday' ),
			'id'            => 'homepage-widget',
			'description'   => esc_html__( 'Widgets added here will appear on the site front page.', 'communicationstoday' ),
			// This sidebar is intended for inserting content directly into existing page markup.
			// Keep wrappers empty so widgets can output the exact HTML structure needed.
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '',
			'after_title'   => '',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Leaderboard (top, site-wide)', 'communicationstoday' ),
			'id'            => 'leaderboard-top',
			'description'   => esc_html__( 'Shown at the top of every page when this area has content. Use Image, Custom HTML, or an ad widget. Recommended markup: a.top-ad-banner wrapping an img (link + banner).', 'communicationstoday' ),
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '',
			'after_title'   => '',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Archive listing — right rail', 'communicationstoday' ),
			'id'            => 'archive-listing-banner',
			'description'   => esc_html__( 'Right column on category, tag, date, and author archives (article listing layout). Add Image widgets or Custom HTML.', 'communicationstoday' ),
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '',
			'after_title'   => '',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Archive listing — mid ad', 'communicationstoday' ),
			'id'            => 'archive-listing-mid-ad',
			'description'   => esc_html__( 'Optional ad between the first and second post on archive listing pages. Use “Leaderboard banner (bottom)” or Custom HTML with class bottom-ad-banner.', 'communicationstoday' ),
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '',
			'after_title'   => '',
		)
	);

	for ( $i = 1; $i <= 5; $i++ ) {
		register_sidebar(
			array(
				/* translators: %d: footer column number (1–5). */
				'name'          => sprintf( esc_html__( 'Footer %d', 'communicationstoday' ), $i ),
				'id'            => 'footer-' . $i,
				'description'   => esc_html__( 'Footer grid column. Use Navigation Menu, Custom HTML, or other widgets.', 'communicationstoday' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="footer-column-title">',
				'after_title'   => '</h3>',
			)
		);
	}
}
add_action( 'widgets_init', 'communicationstoday_widgets_init' );

/**
 * Print the Homepage widget sidebar on the front page when widgets output HTML.
 * Uses output buffering so the area still appears if legacy/widget output bypasses is_active_sidebar edge cases.
 */
function communicationstoday_render_homepage_widget_area() {
	if ( ! is_front_page() ) {
		return;
	}
	ob_start();
	dynamic_sidebar( 'homepage-widget' );
	$html = ob_get_clean();
	if ( '' === trim( $html ) ) {
		return;
	}
	echo '<aside id="secondary" class="widget-area">';
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</aside>';
}

/**
 * Leaderboard ad strip: top of every page, only if the sidebar outputs HTML.
 */
function communicationstoday_render_leaderboard_banner() {
	ob_start();
	dynamic_sidebar( 'leaderboard-top' );
	$html = ob_get_clean();
	if ( '' === trim( (string) $html ) ) {
		return;
	}
	echo '<div class="top-banner-wrapper"><div class="container">';
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div></div>';
}

/**
 * Archive right column widgets (inside .article-lisitng-banner).
 */
function communicationstoday_render_archive_listing_banner() {
	ob_start();
	dynamic_sidebar( 'archive-listing-banner' );
	$html = ob_get_clean();
	if ( '' === trim( (string) $html ) ) {
		return;
	}
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Optional ad after first post on archives (wrapped in .bottom-ad-banner-wrapper).
 */
function communicationstoday_render_archive_mid_ad() {
	ob_start();
	dynamic_sidebar( 'archive-listing-mid-ad' );
	$html = ob_get_clean();
	if ( '' === trim( (string) $html ) ) {
		return;
	}
	echo '<div class="bottom-ad-banner-wrapper">';
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';
}

/**
 * Category image for Think Tank: ACF image fields on the category (if ACF active), else theme term meta below.
 */
add_action(
	'init',
	function () {
		register_term_meta(
			'category',
			'communicationstoday_category_image_id',
			array(
				'type'              => 'integer',
				'single'            => true,
				'sanitize_callback' => 'absint',
				'show_in_rest'      => false,
			)
		);
	}
);

/**
 * @param WP_Term $term Term.
 */
function communicationstoday_category_image_edit_form_field( $term ) {
	$image_id = absint( get_term_meta( $term->term_id, 'communicationstoday_category_image_id', true ) );
	$url      = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
	wp_nonce_field( 'communicationstoday_save_category_image', 'communicationstoday_category_image_nonce' );
	?>
	<tr class="form-field">
		<th scope="row"><label><?php esc_html_e( 'Category image', 'communicationstoday' ); ?></label></th>
		<td>
			<div class="communicationstoday-cat-image-field">
				<input type="hidden" name="communicationstoday_category_image_id" value="<?php echo esc_attr( (string) $image_id ); ?>">
				<p>
					<img src="<?php echo esc_url( (string) $url ); ?>" alt="" class="communicationstoday-cat-img-preview" style="max-width:220px;height:auto;<?php echo $url ? '' : 'display:none;'; ?>">
				</p>
				<p>
					<button type="button" class="button communicationstoday-cat-select-image"><?php esc_html_e( 'Select image', 'communicationstoday' ); ?></button>
					<button type="button" class="button communicationstoday-cat-remove-image" style="<?php echo $image_id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove image', 'communicationstoday' ); ?></button>
				</p>
				<p class="description"><?php esc_html_e( 'Fallback for Think Tank if this category has no ACF image field set.', 'communicationstoday' ); ?></p>
			</div>
		</td>
	</tr>
	<?php
}

function communicationstoday_category_image_add_form_field() {
	wp_nonce_field( 'communicationstoday_save_category_image', 'communicationstoday_category_image_nonce' );
	?>
	<div class="form-field communicationstoday-cat-image-field">
		<label><?php esc_html_e( 'Category image', 'communicationstoday' ); ?></label>
		<input type="hidden" name="communicationstoday_category_image_id" value="">
		<p>
			<img src="" alt="" class="communicationstoday-cat-img-preview" style="display:none;max-width:220px;height:auto;">
		</p>
		<p>
			<button type="button" class="button communicationstoday-cat-select-image"><?php esc_html_e( 'Select image', 'communicationstoday' ); ?></button>
			<button type="button" class="button communicationstoday-cat-remove-image" style="display:none;"><?php esc_html_e( 'Remove image', 'communicationstoday' ); ?></button>
		</p>
		<p class="description"><?php esc_html_e( 'Fallback for Think Tank if this category has no ACF image field set.', 'communicationstoday' ); ?></p>
	</div>
	<?php
}

/**
 * Convert ACF image field value to attachment ID (supports ID, array, URL return formats).
 *
 * @param mixed $value Field value.
 * @return int Attachment ID or 0.
 */
function communicationstoday_acf_image_value_to_attachment_id( $value ) {
	if ( is_numeric( $value ) ) {
		return absint( $value );
	}
	if ( is_array( $value ) ) {
		if ( isset( $value['ID'] ) ) {
			return absint( $value['ID'] );
		}
		if ( isset( $value['id'] ) ) {
			return absint( $value['id'] );
		}
	}
	if ( is_string( $value ) && $value !== '' && filter_var( $value, FILTER_VALIDATE_URL ) ) {
		$found = attachment_url_to_postid( $value );
		return $found ? absint( $found ) : 0;
	}
	return 0;
}

/**
 * Collect ACF image subfield names from a field list (top-level + inside group fields).
 *
 * @param array $fields Field definitions from acf_get_fields().
 * @return string[]
 */
function communicationstoday_acf_collect_category_image_field_names( $fields ) {
	$names = array();
	if ( ! is_array( $fields ) ) {
		return $names;
	}
	foreach ( $fields as $field ) {
		if ( empty( $field['name'] ) || empty( $field['type'] ) ) {
			continue;
		}
		if ( 'image' === $field['type'] ) {
			$names[] = $field['name'];
			continue;
		}
		if ( 'group' === $field['type'] && ! empty( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ) {
			$names = array_merge( $names, communicationstoday_acf_collect_category_image_field_names( $field['sub_fields'] ) );
		}
	}
	return $names;
}

/**
 * Attachment ID for Think Tank main image: ACF (category) first, then theme term meta.
 *
 * Use filter `communicationstoday_think_tank_acf_category_image_field_names` to pass explicit
 * ACF field names in order, e.g. `add_filter( ..., fn () => array( 'my_category_image' ) );`.
 *
 * @param int $term_id Category term ID.
 * @return int Attachment ID or 0.
 */
function communicationstoday_get_think_tank_category_image_attachment_id( $term_id ) {
	$term_id = absint( $term_id );
	if ( ! $term_id ) {
		return 0;
	}

	$term = get_term( $term_id, 'category' );
	if ( ! $term || is_wp_error( $term ) ) {
		return 0;
	}

	$acf_context = 'category_' . $term_id;

	if ( function_exists( 'get_field' ) && function_exists( 'acf_get_field_groups' ) && function_exists( 'acf_get_field_group_visibility' ) ) {
		$screen = array( 'taxonomy' => 'category' );

		$preferred = apply_filters( 'communicationstoday_think_tank_acf_category_image_field_names', array() );
		if ( is_array( $preferred ) ) {
			foreach ( $preferred as $acf_name ) {
				if ( ! is_string( $acf_name ) || $acf_name === '' ) {
					continue;
				}
				$val = get_field( $acf_name, $acf_context );
				$id  = communicationstoday_acf_image_value_to_attachment_id( $val );
				if ( $id > 0 ) {
					return $id;
				}
			}
		}

		$groups = acf_get_field_groups();
		if ( is_array( $groups ) ) {
			foreach ( $groups as $group ) {
				if ( ! acf_get_field_group_visibility( $group, $screen ) ) {
					continue;
				}
				$group_key = ! empty( $group['key'] ) ? $group['key'] : 0;
				if ( ! $group_key && ! empty( $group['ID'] ) ) {
					$group_key = $group['ID'];
				}
				if ( ! $group_key || ! function_exists( 'acf_get_fields' ) ) {
					continue;
				}
				$fields = acf_get_fields( $group_key );
				if ( ! is_array( $fields ) ) {
					continue;
				}
				foreach ( communicationstoday_acf_collect_category_image_field_names( $fields ) as $name ) {
					$val = get_field( $name, $acf_context );
					$id  = communicationstoday_acf_image_value_to_attachment_id( $val );
					if ( $id > 0 ) {
						return $id;
					}
				}
			}
		}
	}

	return absint( get_term_meta( $term_id, 'communicationstoday_category_image_id', true ) );
}

/**
 * @param int $term_id Term ID.
 */
function communicationstoday_category_image_save( $term_id ) {
	if ( ! isset( $_POST['communicationstoday_category_image_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['communicationstoday_category_image_nonce'] ) ), 'communicationstoday_save_category_image' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	$id = isset( $_POST['communicationstoday_category_image_id'] ) ? absint( wp_unslash( $_POST['communicationstoday_category_image_id'] ) ) : 0;
	if ( $id > 0 ) {
		update_term_meta( $term_id, 'communicationstoday_category_image_id', $id );
	} else {
		delete_term_meta( $term_id, 'communicationstoday_category_image_id' );
	}
}

add_action( 'category_add_form_fields', 'communicationstoday_category_image_add_form_field' );
add_action( 'category_edit_form_fields', 'communicationstoday_category_image_edit_form_field', 10, 1 );
add_action( 'created_category', 'communicationstoday_category_image_save', 10, 1 );
add_action( 'edited_category', 'communicationstoday_category_image_save', 10, 1 );

/**
 * Scripts for category image on Categories screen.
 *
 * @param string $hook_suffix Current admin page.
 */
function communicationstoday_category_image_admin_assets( $hook_suffix ) {
	$taxonomy = '';
	if ( 'edit-tags.php' === $hook_suffix && isset( $_GET['taxonomy'] ) ) {
		$taxonomy = sanitize_key( wp_unslash( $_GET['taxonomy'] ) );
	} elseif ( 'term.php' === $hook_suffix && function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		if ( $screen && ! empty( $screen->taxonomy ) ) {
			$taxonomy = $screen->taxonomy;
		}
	}
	if ( 'category' !== $taxonomy ) {
		return;
	}
	if ( wp_script_is( 'communicationstoday-category-image-admin', 'enqueued' ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'communicationstoday-category-image-admin',
		get_template_directory_uri() . '/js/category-image-admin.js',
		array( 'jquery' ),
		_S_VERSION,
		true
	);
	wp_localize_script(
		'communicationstoday-category-image-admin',
		'communicationstodayCategoryImage',
		array(
			'select' => esc_html__( 'Choose image', 'communicationstoday' ),
			'use'    => esc_html__( 'Use image', 'communicationstoday' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'communicationstoday_category_image_admin_assets', 20 );

/**
 * Homepage Stories Widget:
 * Admin me category select karein, aur front page par us category ki latest 3 posts show hon.
 */
class Communicationstoday_Homepage_Stories_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'communicationstoday_homepage_stories',
			esc_html__( 'Homepage Stories', 'communicationstoday' ),
			array( 'description' => esc_html__( 'Shows latest 3 posts from selected category.', 'communicationstoday' ) )
		);
	}

	public function form( $instance ) {
		$category_id = isset( $instance['category_id'] ) ? (int) $instance['category_id'] : 0;

		$categories = get_categories(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
			)
		);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category_id' ) ); ?>">
				<?php esc_html_e( 'Select category', 'communicationstoday' ); ?>
			</label>
		</p>
		<p>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'category_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category_id' ) ); ?>">
				<option value="0"><?php esc_html_e( 'Choose category', 'communicationstoday' ); ?></option>
				<?php foreach ( $categories as $cat ) : ?>
					<option value="<?php echo esc_attr( (int) $cat->term_id ); ?>" <?php selected( $category_id, (int) $cat->term_id ); ?>>
						<?php echo esc_html( $cat->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['category_id'] = isset( $new_instance['category_id'] ) ? absint( $new_instance['category_id'] ) : 0;
		return $instance;
	}

	public function widget( $args, $instance ) {
		$category_id = isset( $instance['category_id'] ) ? (int) $instance['category_id'] : 0;

		if ( $category_id <= 0 ) {
			// Keep front-page clean; no extra text inside story grid.
			return;
		}

		$cat_name = get_cat_name( $category_id );
		if ( empty( $cat_name ) ) {
			return;
		}

		$query = new WP_Query(
			array(
				'post_type'           => 'post',
				'cat'                 => $category_id,
				'posts_per_page'      => 3,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}?>
		<div class="stories-grid">
<?php 
		while ( $query->have_posts() ) :
			$query->the_post();
			$post_id = get_the_ID();

			$thumb_url = get_the_post_thumbnail_url( $post_id, 'large' );
			$title     = get_the_title( $post_id );
			$link      = get_permalink( $post_id );
			?>
			<a href="<?php echo esc_url( $link ); ?>" class="story-card">
				<div class="story-image">
					<?php if ( ! empty( $thumb_url ) ) : ?>
						<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $title ); ?>">
					<?php endif; ?>
				</div>
				<div class="story-image-overlay">
					<div class="story-content">
						<span class="category-link"><?php echo esc_html( $cat_name ); ?></span>
						<h3 class="story-title"><?php echo esc_html( $title ); ?></h3>
					</div>
				</div>
			</a>
			<?php
		endwhile; ?>

		 </div>
		<?php
		wp_reset_postdata();
	}
}

/**
 * Think Tank: add to any widget area (Sidebar, Homepage widget, etc.).
 */
class Communicationstoday_Think_Tank_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'communicationstoday_think_tank',
			esc_html__( 'Think Tank', 'communicationstoday' ),
			array(
				'description' => esc_html__( 'Top stories: label, category, Side ads 1 & 2 each with image + optional link URL.', 'communicationstoday' ),
			)
		);
	}

	public function form( $instance ) {
		$header_label        = isset( $instance['header_label'] ) ? $instance['header_label'] : 'THINK TANK';
		$category_id         = isset( $instance['category_id'] ) ? (int) $instance['category_id'] : 0;
		$ad1_attachment_id   = isset( $instance['ad1_attachment_id'] ) ? absint( $instance['ad1_attachment_id'] ) : 0;
		$ad1_url             = isset( $instance['ad1_url'] ) ? $instance['ad1_url'] : '';
		$ad2_attachment_id   = isset( $instance['ad2_attachment_id'] ) ? absint( $instance['ad2_attachment_id'] ) : 0;
		$ad2_url             = isset( $instance['ad2_url'] ) ? $instance['ad2_url'] : '';
		$ad1_preview_url     = $ad1_attachment_id ? wp_get_attachment_image_url( $ad1_attachment_id, 'medium' ) : '';
		$ad2_preview_url     = $ad2_attachment_id ? wp_get_attachment_image_url( $ad2_attachment_id, 'medium' ) : '';

		$categories = get_categories(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
			)
		);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'header_label' ) ); ?>"><?php esc_html_e( 'Section label', 'communicationstoday' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'header_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'header_label' ) ); ?>" value="<?php echo esc_attr( $header_label ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category_id' ) ); ?>"><?php esc_html_e( 'Category (main block)', 'communicationstoday' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'category_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category_id' ) ); ?>">
				<option value="0"><?php esc_html_e( 'Choose category', 'communicationstoday' ); ?></option>
				<?php foreach ( $categories as $cat ) : ?>
					<option value="<?php echo esc_attr( (int) $cat->term_id ); ?>" <?php selected( $category_id, (int) $cat->term_id ); ?>>
						<?php echo esc_html( $cat->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<small>
				<?php esc_html_e( 'Main block uses the ACF image on this category (taxonomy fields), or the theme “Category image” fallback. “See more” uses the category archive. Side ad links are optional; if empty they use the category archive.', 'communicationstoday' ); ?>
			</small>
		</p>
		<div class="communicationstoday-think-tank-ad-media">
			<p><strong><?php esc_html_e( 'Side ad 1', 'communicationstoday' ); ?></strong></p>
			<p>
				<label><?php esc_html_e( 'Image', 'communicationstoday' ); ?></label><br>
				<input type="hidden" class="think-tank-attachment-id" id="<?php echo esc_attr( $this->get_field_id( 'ad1_attachment_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad1_attachment_id' ) ); ?>" value="<?php echo esc_attr( (string) $ad1_attachment_id ); ?>">
				<img src="<?php echo esc_url( (string) $ad1_preview_url ); ?>" alt="" class="think-tank-attachment-preview" style="max-width:100%;height:auto;<?php echo $ad1_preview_url ? '' : 'display:none;'; ?>">
			</p>
			<p>
				<button type="button" class="button communicationstoday-think-tank-media"><?php esc_html_e( 'Select image', 'communicationstoday' ); ?></button>
				<button type="button" class="button communicationstoday-think-tank-remove" style="<?php echo $ad1_attachment_id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove image', 'communicationstoday' ); ?></button>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'ad1_url' ) ); ?>"><?php esc_html_e( 'Link URL (optional)', 'communicationstoday' ); ?></label>
				<input class="widefat" type="url" id="<?php echo esc_attr( $this->get_field_id( 'ad1_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad1_url' ) ); ?>" value="<?php echo esc_attr( $ad1_url ); ?>">
			</p>
		</div>
		<div class="communicationstoday-think-tank-ad-media">
			<p><strong><?php esc_html_e( 'Side ad 2', 'communicationstoday' ); ?></strong></p>
			<p>
				<label><?php esc_html_e( 'Image', 'communicationstoday' ); ?></label><br>
				<input type="hidden" class="think-tank-attachment-id" id="<?php echo esc_attr( $this->get_field_id( 'ad2_attachment_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad2_attachment_id' ) ); ?>" value="<?php echo esc_attr( (string) $ad2_attachment_id ); ?>">
				<img src="<?php echo esc_url( (string) $ad2_preview_url ); ?>" alt="" class="think-tank-attachment-preview" style="max-width:100%;height:auto;<?php echo $ad2_preview_url ? '' : 'display:none;'; ?>">
			</p>
			<p>
				<button type="button" class="button communicationstoday-think-tank-media"><?php esc_html_e( 'Select image', 'communicationstoday' ); ?></button>
				<button type="button" class="button communicationstoday-think-tank-remove" style="<?php echo $ad2_attachment_id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove image', 'communicationstoday' ); ?></button>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'ad2_url' ) ); ?>"><?php esc_html_e( 'Link URL (optional)', 'communicationstoday' ); ?></label>
				<input class="widefat" type="url" id="<?php echo esc_attr( $this->get_field_id( 'ad2_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad2_url' ) ); ?>" value="<?php echo esc_attr( $ad2_url ); ?>">
			</p>
			<p><small><?php esc_html_e( 'If empty, this ad links to the selected category archive.', 'communicationstoday' ); ?></small></p>
		</div>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance                        = array();
		$instance['header_label']        = isset( $new_instance['header_label'] ) ? sanitize_text_field( wp_unslash( $new_instance['header_label'] ) ) : '';
		$instance['category_id']       = isset( $new_instance['category_id'] ) ? absint( $new_instance['category_id'] ) : 0;
		$instance['ad1_attachment_id'] = isset( $new_instance['ad1_attachment_id'] ) ? absint( $new_instance['ad1_attachment_id'] ) : 0;
		$instance['ad1_url']           = isset( $new_instance['ad1_url'] ) ? esc_url_raw( wp_unslash( $new_instance['ad1_url'] ) ) : '';
		$instance['ad2_attachment_id'] = isset( $new_instance['ad2_attachment_id'] ) ? absint( $new_instance['ad2_attachment_id'] ) : 0;
		$instance['ad2_url']           = isset( $new_instance['ad2_url'] ) ? esc_url_raw( wp_unslash( $new_instance['ad2_url'] ) ) : '';
		return $instance;
	}

	public function widget( $args, $instance ) {
		$header_label = ! empty( $instance['header_label'] ) ? $instance['header_label'] : 'THINK TANK';
		$category_id  = isset( $instance['category_id'] ) ? (int) $instance['category_id'] : 0;
		$category_link = ( $category_id > 0 ) ? get_category_link( $category_id ) : '#';
		$see_more_url  = $category_link;

		$ad1_attachment_id = isset( $instance['ad1_attachment_id'] ) ? absint( $instance['ad1_attachment_id'] ) : 0;
		$ad1_image         = $ad1_attachment_id ? (string) wp_get_attachment_image_url( $ad1_attachment_id, 'full' ) : '';
		if ( ! $ad1_image && ! empty( $instance['ad1_image'] ) ) {
			$ad1_image = $instance['ad1_image'];
		}
		$ad1_url = ! empty( $instance['ad1_url'] ) ? $instance['ad1_url'] : $category_link;

		$ad2_attachment_id = isset( $instance['ad2_attachment_id'] ) ? absint( $instance['ad2_attachment_id'] ) : 0;
		$ad2_image         = $ad2_attachment_id ? (string) wp_get_attachment_image_url( $ad2_attachment_id, 'full' ) : '';
		if ( ! $ad2_image && ! empty( $instance['ad2_image'] ) ) {
			$ad2_image = $instance['ad2_image'];
		}
		$ad2_url = ! empty( $instance['ad2_url'] ) ? $instance['ad2_url'] : $category_link;

		$thumb_url  = '';
		$main_link  = $category_link;
		$img_alt    = '';

		if ( $category_id > 0 ) {
			$img_alt   = get_cat_name( $category_id );
			$image_id  = communicationstoday_get_think_tank_category_image_attachment_id( $category_id );
			$thumb_url = $image_id ? (string) wp_get_attachment_image_url( $image_id, 'large' ) : '';
		}

		?>
		<section class="top-stories">
			<div class="container">
				<div class="top-stories-grid mb-30">
					<span class="category-link "><?php echo esc_html( $header_label ); ?></span>
					<a href="<?php echo esc_url( $see_more_url ); ?>" class="top-story-card"><?php esc_html_e( 'See More', 'communicationstoday' ); ?> <i class="fas fa-chevron-right"></i></a>
				</div>

				<div class="top-stories-content">
					<div class="think_tank_content">
						<?php if ( $thumb_url ) : ?>
							<a href="<?php echo esc_url( $main_link ); ?>" class="top-story-card">
								<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" class="w-100">
							</a>
						<?php endif; ?>
					</div>
					<div class="side-ad-content">
						<?php if ( $ad1_image ) : ?>
							<a href="<?php echo esc_url( $ad1_url ); ?>" class="">
								<img src="<?php echo esc_url( $ad1_image ); ?>" alt="" class="w-100">
							</a>
						<?php endif; ?>
						<?php if ( $ad2_image ) : ?>
							<a href="<?php echo esc_url( $ad2_url ); ?>" class="">
								<img src="<?php echo esc_url( $ad2_image ); ?>" alt="" class="w-100">
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}

/**
 * Build one perspective slide from attachment ID.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $link          URL for the slide anchor.
 * @return array{link: string, thumb: string, title: string}|null
 */
function communicationstoday_perspective_slide_from_attachment_id( $attachment_id, $link = '#' ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id || ! function_exists( 'wp_attachment_is_image' ) || ! wp_attachment_is_image( $attachment_id ) ) {
		return null;
	}
	$url = wp_get_attachment_image_url( $attachment_id, 'large' );
	if ( ! $url ) {
		return null;
	}
	return array(
		'link'  => $link,
		'thumb' => (string) $url,
		'title' => (string) get_the_title( $attachment_id ),
	);
}

/**
 * Turn ACF image / gallery / repeater value into perspective slides.
 *
 * @param mixed $val Raw get_field() value.
 * @return array<int, array{link: string, thumb: string, title: string}>
 */
function communicationstoday_normalize_acf_value_to_perspective_slides( $val ) {
	$slides = array();
	if ( empty( $val ) && $val !== 0 && $val !== '0' ) {
		return $slides;
	}
	if ( is_numeric( $val ) ) {
		$s = communicationstoday_perspective_slide_from_attachment_id( $val, '#' );
		return $s ? array( $s ) : $slides;
	}
	if ( is_string( $val ) && filter_var( $val, FILTER_VALIDATE_URL ) ) {
		$aid = attachment_url_to_postid( $val );
		if ( $aid ) {
			$s = communicationstoday_perspective_slide_from_attachment_id( $aid, '#' );
			return $s ? array( $s ) : $slides;
		}
		$slides[] = array(
			'link'  => '#',
			'thumb' => $val,
			'title' => '',
		);
		return $slides;
	}
	if ( ! is_array( $val ) ) {
		return $slides;
	}
	if ( isset( $val['ID'] ) || isset( $val['id'] ) ) {
		$id = isset( $val['ID'] ) ? absint( $val['ID'] ) : absint( $val['id'] );
		if ( $id && function_exists( 'wp_attachment_is_image' ) && wp_attachment_is_image( $id ) ) {
			$url = isset( $val['url'] ) ? $val['url'] : wp_get_attachment_image_url( $id, 'large' );
			if ( $url ) {
				$slides[] = array(
					'link'  => '#',
					'thumb' => (string) $url,
					'title' => ( isset( $val['title'] ) && is_string( $val['title'] ) ) ? $val['title'] : (string) get_the_title( $id ),
				);
			}
		}
		return $slides;
	}
	$all_numeric = true;
	foreach ( $val as $item ) {
		if ( ! is_numeric( $item ) ) {
			$all_numeric = false;
			break;
		}
	}
	if ( $all_numeric && array() !== $val ) {
		foreach ( $val as $id ) {
			$s = communicationstoday_perspective_slide_from_attachment_id( $id, '#' );
			if ( $s ) {
				$slides[] = $s;
			}
		}
		return $slides;
	}
	foreach ( $val as $row ) {
		if ( is_numeric( $row ) ) {
			$s = communicationstoday_perspective_slide_from_attachment_id( $row, '#' );
			if ( $s ) {
				$slides[] = $s;
			}
			continue;
		}
		if ( ! is_array( $row ) ) {
			continue;
		}
		$link = '';
		foreach ( array( 'link', 'slide_link' ) as $lk ) {
			if ( ! empty( $row[ $lk ] ) && is_string( $row[ $lk ] ) ) {
				$link = $row[ $lk ];
				break;
			}
		}
		$img_part = null;
		foreach ( array( 'image', 'slide_image', 'photo', 'picture', 'homepage_perspective_slider' ) as $k ) {
			if ( ! empty( $row[ $k ] ) ) {
				$img_part = $row[ $k ];
				break;
			}
		}
		if ( null === $img_part ) {
			$nested = communicationstoday_normalize_acf_value_to_perspective_slides( $row );
			if ( ! empty( $nested ) ) {
				$s = $nested[0];
				if ( $link !== '' ) {
					$s['link'] = esc_url_raw( $link );
				}
				$slides[] = $s;
			}
			continue;
		}
		$nested = communicationstoday_normalize_acf_value_to_perspective_slides( $img_part );
		if ( ! empty( $nested ) ) {
			$s = $nested[0];
			if ( $link !== '' ) {
				$s['link'] = esc_url_raw( $link );
			}
			$slides[] = $s;
		}
	}
	return $slides;
}

/**
 * Image URL for Perspective slides: featured image, else first attached image, else theme placeholder.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function communicationstoday_get_perspective_post_image_url( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return '';
	}
	$url = get_the_post_thumbnail_url( $post_id, 'large' );
	if ( $url ) {
		return (string) $url;
	}
	$attachments = get_attached_media( 'image', $post_id );
	if ( ! empty( $attachments ) ) {
		foreach ( $attachments as $att ) {
			$u = wp_get_attachment_image_url( $att->ID, 'large' );
			if ( $u ) {
				return (string) $u;
			}
		}
	}
	$placeholder = get_template_directory_uri() . '/asset/img/logo.png';
	return $placeholder ? (string) $placeholder : '';
}

/**
 * Perspective carousel image for a post: ACF “Homepage Perspective slider” (per post), not featured image.
 * Optional filter communicationstoday_perspective_use_featured_if_post_slider_empty = true to fall back.
 *
 * @param int $post_id Post ID.
 * @return string Image URL or empty.
 */
function communicationstoday_get_perspective_post_homepage_slider_image_url( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id || ! function_exists( 'get_field' ) ) {
		return '';
	}
	$names = apply_filters(
		'communicationstoday_post_perspective_slider_acf_field_names',
		array(
			'homepage_perspective_slider',
			'home_perspective_slider',
			'perspective_slider',
		)
	);
	if ( ! is_array( $names ) ) {
		return '';
	}
	foreach ( $names as $name ) {
		if ( ! is_string( $name ) || $name === '' ) {
			continue;
		}
		$val    = get_field( $name, $post_id );
		$slides = communicationstoday_normalize_acf_value_to_perspective_slides( $val );
		if ( ! empty( $slides[0]['thumb'] ) ) {
			return (string) $slides[0]['thumb'];
		}
	}
	if ( apply_filters( 'communicationstoday_perspective_use_featured_if_post_slider_empty', false ) ) {
		return communicationstoday_get_perspective_post_image_url( $post_id );
	}
	return '';
}

/**
 * Numeric ACF/meta "Order" for Perspective posts (lower = earlier in carousel). Missing value sorts last.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function communicationstoday_get_perspective_post_order_value( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return 999999;
	}
	$keys = apply_filters(
		'communicationstoday_perspective_order_acf_field_names',
		array( 'order', 'Order', 'perspective_order' )
	);
	$raw  = null;
	if ( function_exists( 'get_field' ) && is_array( $keys ) ) {
		foreach ( $keys as $key ) {
			if ( ! is_string( $key ) || $key === '' ) {
				continue;
			}
			$v = get_field( $key, $post_id );
			if ( null !== $v && false !== $v && '' !== $v ) {
				$raw = $v;
				break;
			}
		}
	}
	if ( null === $raw || '' === $raw ) {
		foreach ( array( 'order', 'Order', 'perspective_order' ) as $meta_key ) {
			$v = get_post_meta( $post_id, $meta_key, true );
			if ( null !== $v && '' !== $v ) {
				$raw = $v;
				break;
			}
		}
	}
	if ( is_numeric( $raw ) ) {
		return (int) $raw;
	}
	return 999999;
}

/**
 * Slides from ACF (Options page or static front page). Field names filterable.
 *
 * @return array<int, array{link: string, thumb: string, title: string}>
 */
function communicationstoday_perspective_get_slides_from_acf() {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}
	$names = apply_filters(
		'communicationstoday_perspective_slider_acf_field_names',
		array(
			'homepage_perspective_slider',
			'home_perspective_slider',
			'perspective_slider',
		)
	);
	if ( ! is_array( $names ) ) {
		return array();
	}
	$contexts   = array( 'option', 'options' );
	$front_page = (int) get_option( 'page_on_front' );
	if ( $front_page > 0 ) {
		$contexts[] = $front_page;
	}
	$contexts = apply_filters( 'communicationstoday_perspective_acf_contexts', $contexts );
	$contexts = array_unique( array_map( 'strval', $contexts ) );
	foreach ( $names as $name ) {
		if ( ! is_string( $name ) || $name === '' ) {
			continue;
		}
		foreach ( $contexts as $ctx ) {
			$val    = get_field( $name, $ctx );
			$slides = communicationstoday_normalize_acf_value_to_perspective_slides( $val );
			if ( ! empty( $slides ) ) {
				return $slides;
			}
		}
	}
	return array();
}

/**
 * Perspective-style carousel: ACF “Homepage Perspective slider” (or filter names) when set; else posts from a category.
 */
class Communicationstoday_Perspective_Swiper_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'communicationstoday_perspective_swiper',
			esc_html__( 'Perspective carousel', 'communicationstoday' ),
			array(
				'description' => esc_html__( 'Uses ACF Homepage Perspective slider (Options or front page) if filled; otherwise posts from the selected category.', 'communicationstoday' ),
			)
		);
	}

	public function form( $instance ) {
		$header_label = isset( $instance['header_label'] ) ? $instance['header_label'] : __( 'Perspective', 'communicationstoday' );
		$category_id  = isset( $instance['category_id'] ) ? (int) $instance['category_id'] : 0;
		$post_count   = isset( $instance['post_count'] ) ? absint( $instance['post_count'] ) : 12;
		$post_count   = min( 30, max( 1, $post_count ) );

		$categories = get_categories(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
			)
		);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'header_label' ) ); ?>"><?php esc_html_e( 'Section label', 'communicationstoday' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'header_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'header_label' ) ); ?>" value="<?php echo esc_attr( $header_label ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category_id' ) ); ?>"><?php esc_html_e( 'Category', 'communicationstoday' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'category_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category_id' ) ); ?>">
				<option value="0"><?php esc_html_e( 'Choose category', 'communicationstoday' ); ?></option>
				<?php foreach ( $categories as $cat ) : ?>
					<option value="<?php echo esc_attr( (int) $cat->term_id ); ?>" <?php selected( $category_id, (int) $cat->term_id ); ?>>
						<?php echo esc_html( $cat->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'post_count' ) ); ?>"><?php esc_html_e( 'Max slides (posts in category)', 'communicationstoday' ); ?></label>
			<input class="small-text" type="number" min="1" max="30" id="<?php echo esc_attr( $this->get_field_id( 'post_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_count' ) ); ?>" value="<?php echo esc_attr( (string) $post_count ); ?>">
		</p>
		<p><small><?php esc_html_e( 'If Options/front-page ACF gallery is set, it replaces the whole slider. Otherwise each slide uses that post’s ACF “Homepage Perspective slider” image (not the featured image). Sorted by post “Order”, then date. Subcategories included.', 'communicationstoday' ); ?></small></p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );
		$instance                 = array();
		$instance['header_label'] = isset( $new_instance['header_label'] ) ? sanitize_text_field( wp_unslash( $new_instance['header_label'] ) ) : '';
		$instance['category_id']  = isset( $new_instance['category_id'] ) ? absint( $new_instance['category_id'] ) : 0;
		$n                        = isset( $new_instance['post_count'] ) ? absint( $new_instance['post_count'] ) : 12;
		$instance['post_count']   = min( 30, max( 1, $n ) );
		return $instance;
	}

	public function widget( $args, $instance ) {
		$header_label = ! empty( $instance['header_label'] ) ? $instance['header_label'] : __( 'Perspective', 'communicationstoday' );
		$category_id  = isset( $instance['category_id'] ) ? (int) $instance['category_id'] : 0;
		$post_count   = isset( $instance['post_count'] ) ? absint( $instance['post_count'] ) : 12;
		$post_count   = min( 30, max( 1, $post_count ) );

		$slides = communicationstoday_perspective_get_slides_from_acf();

		if ( empty( $slides ) ) {
			if ( $category_id <= 0 ) {
				return;
			}

			$fetch_cap = min( 50, max( $post_count * 4, $post_count + 10 ) );
			$query     = new WP_Query(
				array(
					'post_type'           => 'post',
					'posts_per_page'      => $fetch_cap,
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'tax_query'           => array(
						array(
							'taxonomy'         => 'category',
							'field'            => 'term_id',
							'terms'            => $category_id,
							'include_children' => true,
						),
					),
				)
			);

			if ( $query->have_posts() ) {
				$candidates = array();
				while ( $query->have_posts() ) {
					$query->the_post();
					$post_id = get_the_ID();
					$thumb   = communicationstoday_get_perspective_post_homepage_slider_image_url( $post_id );
					if ( ! $thumb ) {
						continue;
					}
					$candidates[] = array(
						'post_id' => $post_id,
						'thumb'   => $thumb,
						'title'   => get_the_title( $post_id ),
						'order'   => communicationstoday_get_perspective_post_order_value( $post_id ),
						'date'    => (int) get_post_time( 'U', true, $post_id ),
					);
				}
				wp_reset_postdata();

				$tiebreak = apply_filters( 'communicationstoday_perspective_order_tiebreak_date', 'DESC' );
				$tiebreak = ( is_string( $tiebreak ) && 'ASC' === strtoupper( $tiebreak ) ) ? 'ASC' : 'DESC';

				usort(
					$candidates,
					static function ( $a, $b ) use ( $tiebreak ) {
						if ( $a['order'] !== $b['order'] ) {
							return $a['order'] <=> $b['order'];
						}
						if ( 'ASC' === $tiebreak ) {
							return $a['date'] <=> $b['date'];
						}
						return $b['date'] <=> $a['date'];
					}
				);

				$candidates = array_slice( $candidates, 0, $post_count );
				foreach ( $candidates as $c ) {
					$slides[] = array(
						'link'  => get_permalink( $c['post_id'] ),
						'thumb' => $c['thumb'],
						'title' => $c['title'],
					);
				}
			}
		}

		if ( empty( $slides ) ) {
			return;
		}

		$category_link = '';
		if ( $category_id > 0 ) {
			$link = get_category_link( $category_id );
			if ( ! is_wp_error( $link ) && $link ) {
				$category_link = $link;
			}
		}

		$wrapper_id = 'swiper-wrapper-' . wp_unique_id();
		$total      = count( $slides );

		echo $args['before_widget'];
		?>
		<section class="perspective-section">
			<div class="container">
				<div class="top-stories-grid mb-30">
					<span class="category-link"><?php echo esc_html( $header_label ); ?></span>
					<?php if ( $category_link ) : ?>
					<a href="<?php echo esc_url( $category_link ); ?>" class="top-story-card"><?php esc_html_e( 'See More', 'communicationstoday' ); ?> <i class="fas fa-chevron-right"></i></a>
					<?php endif; ?>
				</div>
				<div class="perspective-swiper-wrapper">
					<div class="swiper perspective-swiper">
						<div class="swiper-wrapper" id="<?php echo esc_attr( $wrapper_id ); ?>" aria-live="polite">
							<?php
							$index = 0;
							foreach ( $slides as $slide ) :
								++$index;
								$aria = sprintf(
									/* translators: 1: current slide number, 2: total slides */
									__( '%1$d / %2$d', 'communicationstoday' ),
									$index,
									$total
								);
								?>
							<div class="swiper-slide" role="group" aria-label="<?php echo esc_attr( $aria ); ?>">
								<a href="<?php echo '#' === $slide['link'] ? '#' : esc_url( $slide['link'] ); ?>" class="speaker-card">
									<div class="speaker-image">
										<img src="<?php echo esc_url( $slide['thumb'] ); ?>" alt="<?php echo esc_attr( $slide['title'] ); ?>" class="w-100" loading="lazy" decoding="async">
									</div>
								</a>
							</div>
								<?php
							endforeach;
							?>
						</div>
					</div>
					<div class="perspective-navigation">
						<div class="swiper-button-prev perspective-prev" tabindex="0" role="button" aria-label="<?php esc_attr_e( 'Previous slide', 'communicationstoday' ); ?>" aria-controls="<?php echo esc_attr( $wrapper_id ); ?>"></div>
						<div class="swiper-button-next perspective-next" tabindex="0" role="button" aria-label="<?php esc_attr_e( 'Next slide', 'communicationstoday' ); ?>" aria-controls="<?php echo esc_attr( $wrapper_id ); ?>"></div>
					</div>
				</div>
			</div>
		</section>
		<?php
		echo $args['after_widget'];
	}
}

/**
 * Headlines of the Day: 1 main + side ad + 3 cards (4 latest posts from category). Side ad image + optional URL.
 */
class Communicationstoday_Headlines_Day_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'communicationstoday_headlines_day',
			esc_html__( 'Homepage section', 'communicationstoday' ),
			array(
				'description' => esc_html__( 'Latest post (large) + optional side ad + 3 more posts from one category.', 'communicationstoday' ),
			)
		);
	}

	public function form( $instance ) {
		$header_label       = isset( $instance['header_label'] ) ? $instance['header_label'] : __( 'Homepage section', 'communicationstoday' );
		$category_id        = isset( $instance['category_id'] ) ? (int) $instance['category_id'] : 0;
		$ad_attachment_id   = isset( $instance['ad_attachment_id'] ) ? absint( $instance['ad_attachment_id'] ) : 0;
		$ad_url             = isset( $instance['ad_url'] ) ? $instance['ad_url'] : '';
		$ad_preview_url     = $ad_attachment_id ? wp_get_attachment_image_url( $ad_attachment_id, 'medium' ) : '';

		$categories = get_categories(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
			)
		);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'header_label' ) ); ?>"><?php esc_html_e( 'Section label', 'communicationstoday' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'header_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'header_label' ) ); ?>" value="<?php echo esc_attr( $header_label ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category_id' ) ); ?>"><?php esc_html_e( 'Category', 'communicationstoday' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'category_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category_id' ) ); ?>">
				<option value="0"><?php esc_html_e( 'Choose category', 'communicationstoday' ); ?></option>
				<?php foreach ( $categories as $cat ) : ?>
					<option value="<?php echo esc_attr( (int) $cat->term_id ); ?>" <?php selected( $category_id, (int) $cat->term_id ); ?>>
						<?php echo esc_html( $cat->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<div class="communicationstoday-think-tank-ad-media">
			<p><strong><?php esc_html_e( 'Side ad', 'communicationstoday' ); ?></strong></p>
			<p>
				<label><?php esc_html_e( 'Image', 'communicationstoday' ); ?></label><br>
				<input type="hidden" class="think-tank-attachment-id" id="<?php echo esc_attr( $this->get_field_id( 'ad_attachment_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_attachment_id' ) ); ?>" value="<?php echo esc_attr( (string) $ad_attachment_id ); ?>">
				<img src="<?php echo esc_url( (string) $ad_preview_url ); ?>" alt="" class="think-tank-attachment-preview" style="max-width:100%;height:auto;<?php echo $ad_preview_url ? '' : 'display:none;'; ?>">
			</p>
			<p>
				<button type="button" class="button communicationstoday-think-tank-media"><?php esc_html_e( 'Select image', 'communicationstoday' ); ?></button>
				<button type="button" class="button communicationstoday-think-tank-remove" style="<?php echo $ad_attachment_id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove image', 'communicationstoday' ); ?></button>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'ad_url' ) ); ?>"><?php esc_html_e( 'Ad link URL (optional)', 'communicationstoday' ); ?></label>
				<input class="widefat" type="url" id="<?php echo esc_attr( $this->get_field_id( 'ad_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_url' ) ); ?>" value="<?php echo esc_attr( $ad_url ); ?>">
			</p>
			<p><small><?php esc_html_e( 'If ad link is empty, it uses the category archive.', 'communicationstoday' ); ?></small></p>
		</div>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );
		$instance                       = array();
		$instance['header_label']       = isset( $new_instance['header_label'] ) ? sanitize_text_field( wp_unslash( $new_instance['header_label'] ) ) : '';
		$instance['category_id']        = isset( $new_instance['category_id'] ) ? absint( $new_instance['category_id'] ) : 0;
		$instance['ad_attachment_id']   = isset( $new_instance['ad_attachment_id'] ) ? absint( $new_instance['ad_attachment_id'] ) : 0;
		$instance['ad_url']             = isset( $new_instance['ad_url'] ) ? esc_url_raw( wp_unslash( $new_instance['ad_url'] ) ) : '';
		return $instance;
	}

	public function widget( $args, $instance ) {
		$header_label     = ! empty( $instance['header_label'] ) ? $instance['header_label'] : __( 'Homepage section', 'communicationstoday' );
		$category_id      = isset( $instance['category_id'] ) ? (int) $instance['category_id'] : 0;
		$ad_attachment_id = isset( $instance['ad_attachment_id'] ) ? absint( $instance['ad_attachment_id'] ) : 0;
		$ad_url_raw       = ! empty( $instance['ad_url'] ) ? $instance['ad_url'] : '';

		if ( $category_id <= 0 ) {
			return;
		}

		$category_link = get_category_link( $category_id );
		if ( is_wp_error( $category_link ) || ! $category_link ) {
			return;
		}

		$ad_image = $ad_attachment_id ? (string) wp_get_attachment_image_url( $ad_attachment_id, 'full' ) : '';
		$ad_link  = $ad_url_raw ? $ad_url_raw : $category_link;

		$query = new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => 20,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'orderby'             => 'date',
				'order'               => 'DESC',
				'tax_query'           => array(
					array(
						'taxonomy'         => 'category',
						'field'            => 'term_id',
						'terms'            => $category_id,
						'include_children' => true,
					),
				),
			)
		);

		$items = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() && count( $items ) < 4 ) {
				$query->the_post();
				$post_id = get_the_ID();
				$img     = communicationstoday_get_perspective_post_image_url( $post_id );
				if ( ! $img ) {
					continue;
				}
				$items[] = array(
					'id'    => $post_id,
					'title' => get_the_title( $post_id ),
					'link'  => get_permalink( $post_id ),
					'img'   => $img,
				);
			}
			wp_reset_postdata();
		}

		if ( empty( $items ) ) {
			return;
		}

		$main = $items[0];
		$grid = array_slice( $items, 1, 3 );

		echo $args['before_widget'];
		?>
		<section class="headlines-of-the-day">
			<div class="container">
				<div class="top-stories-grid mb-30">
					<span class="category-link"><?php echo esc_html( $header_label ); ?></span>
					<a href="<?php echo esc_url( $category_link ); ?>" class="top-story-card"><?php esc_html_e( 'See More', 'communicationstoday' ); ?> <i class="fas fa-chevron-right"></i></a>
				</div>

				<div class="headlines-of-the-day-content">
					<div class="headlines-of-the-day-main-content">
						<a href="<?php echo esc_url( $main['link'] ); ?>" class="headlines-of-the-day-card">
							<div class="headlines-of-the-day-main-content-image">
								<img src="<?php echo esc_url( $main['img'] ); ?>" alt="<?php echo esc_attr( $main['title'] ); ?>" class="w-100" loading="lazy" decoding="async">
							</div>
							<p><?php echo esc_html( $main['title'] ); ?></p>
						</a>
					</div>
					<?php if ( $ad_image ) : ?>
					<div class="side-ad-content">
						<a href="<?php echo esc_url( $ad_link ); ?>" class="">
							<img src="<?php echo esc_url( $ad_image ); ?>" alt="" class="w-100" loading="lazy" decoding="async">
						</a>
					</div>
					<?php else : ?>
					<div class="side-ad-content"></div>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $grid ) ) : ?>
				<div class="article-cards-grid">
					<?php
					$gi = 0;
					foreach ( $grid as $row ) :
						++$gi;
						$card_class = 'article-card';
						if ( 2 === $gi ) {
							$card_class .= ' text-center';
						}
						?>
					<a href="<?php echo esc_url( $row['link'] ); ?>" class="<?php echo esc_attr( $card_class ); ?>">
						<div class="article-image">
							<img src="<?php echo esc_url( $row['img'] ); ?>" alt="<?php echo esc_attr( $row['title'] ); ?>" class="w-100" loading="lazy" decoding="async">
						</div>
						<div class="article-title"><?php echo esc_html( $row['title'] ); ?></div>
					</a>
						<?php
					endforeach;
					?>
				</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
		echo $args['after_widget'];
	}
}

/**
 * Top events carousel: latest Videos (CPT) with poster, title, duration; links open the stored video URL in a new tab.
 */
class Communicationstoday_Top_Events_Videos_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'communicationstoday_top_events_videos',
			esc_html__( 'Videos — Top events', 'communicationstoday' ),
			array(
				'description' => esc_html__( 'Latest Videos (carousel): poster, title, duration; opens each post’s video link in a new tab.', 'communicationstoday' ),
			)
		);
	}

	public function form( $instance ) {
		$header_label = isset( $instance['header_label'] ) ? $instance['header_label'] : __( 'TOP EVENTS', 'communicationstoday' );
		$post_count   = isset( $instance['post_count'] ) ? absint( $instance['post_count'] ) : 10;
		$post_count   = min( 30, max( 1, $post_count ) );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'header_label' ) ); ?>"><?php esc_html_e( 'Section heading', 'communicationstoday' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'header_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'header_label' ) ); ?>" value="<?php echo esc_attr( $header_label ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'post_count' ) ); ?>"><?php esc_html_e( 'Number of videos', 'communicationstoday' ); ?></label>
			<input class="small-text" type="number" min="1" max="30" id="<?php echo esc_attr( $this->get_field_id( 'post_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_count' ) ); ?>" value="<?php echo esc_attr( (string) $post_count ); ?>">
		</p>
		<p><small><?php esc_html_e( 'Only published videos with a video link and featured image are shown.', 'communicationstoday' ); ?></small></p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );
		$instance                 = array();
		$instance['header_label'] = isset( $new_instance['header_label'] ) ? sanitize_text_field( wp_unslash( $new_instance['header_label'] ) ) : '';
		$n                        = isset( $new_instance['post_count'] ) ? absint( $new_instance['post_count'] ) : 10;
		$instance['post_count']   = min( 30, max( 1, $n ) );
		return $instance;
	}

	public function widget( $args, $instance ) {
		if ( ! function_exists( 'communicationstoday_get_video_details' ) || ! post_type_exists( 'ct_video' ) ) {
			return;
		}

		$header_label = ! empty( $instance['header_label'] ) ? $instance['header_label'] : __( 'TOP EVENTS', 'communicationstoday' );
		$post_count   = isset( $instance['post_count'] ) ? absint( $instance['post_count'] ) : 10;
		$post_count   = min( 30, max( 1, $post_count ) );

		$query = new WP_Query(
			array(
				'post_type'           => 'ct_video',
				'posts_per_page'      => min( 50, $post_count * 3 ),
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'orderby'             => 'date',
				'order'               => 'DESC',
			)
		);

		$slides = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				$details = communicationstoday_get_video_details( $post_id );
				$url     = isset( $details['url'] ) ? trim( (string) $details['url'] ) : '';
				if ( '' === $url ) {
					continue;
				}
				$url = esc_url( $url );
				if ( '' === $url ) {
					continue;
				}
				$thumb = get_the_post_thumbnail_url( $post_id, 'medium_large' );
				if ( ! $thumb ) {
					$thumb = get_the_post_thumbnail_url( $post_id, 'medium' );
				}
				if ( ! $thumb ) {
					continue;
				}
				$duration = isset( $details['duration'] ) ? trim( (string) $details['duration'] ) : '';
				$slides[] = array(
					'title'    => get_the_title( $post_id ),
					'url'      => $url,
					'thumb'    => $thumb,
					'duration' => '' !== $duration ? $duration : '',
				);
				if ( count( $slides ) >= $post_count ) {
					break;
				}
			}
			wp_reset_postdata();
		}

		if ( empty( $slides ) ) {
			return;
		}

		$wrapper_id = 'swiper-wrapper-' . wp_unique_id();
		$total      = count( $slides );

		echo $args['before_widget'];
		?>
		<section class="top-events-section">
			<div class="container">
				<div class="top_event_divider"></div>
				<div class="top-events-header">
					<span class="category-link1"><?php echo esc_html( $header_label ); ?></span>
					<div class="top-events-navigation">
						<div class="swiper-button-prev top-events-prev" tabindex="0" role="button" aria-label="<?php esc_attr_e( 'Previous slide', 'communicationstoday' ); ?>" aria-controls="<?php echo esc_attr( $wrapper_id ); ?>"></div>
						<div class="swiper-button-next top-events-next" tabindex="0" role="button" aria-label="<?php esc_attr_e( 'Next slide', 'communicationstoday' ); ?>" aria-controls="<?php echo esc_attr( $wrapper_id ); ?>"></div>
					</div>
				</div>
				<div class="swiper top-events-swiper">
					<div class="swiper-wrapper" id="<?php echo esc_attr( $wrapper_id ); ?>" aria-live="polite">
						<?php
						$index = 0;
						foreach ( $slides as $slide ) :
							++$index;
							$aria = sprintf(
								/* translators: 1: current slide number, 2: total slides */
								__( '%1$d / %2$d', 'communicationstoday' ),
								$index,
								$total
							);
							?>
						<div class="swiper-slide" role="group" aria-label="<?php echo esc_attr( $aria ); ?>">
							<a href="<?php echo esc_url( $slide['url'] ); ?>" class="event-card" target="_blank" rel="noopener noreferrer">
								<div class="event-image">
									<img src="<?php echo esc_url( $slide['thumb'] ); ?>" alt="<?php echo esc_attr( $slide['title'] ); ?>" class="w-100" loading="lazy" decoding="async">
								</div>
								<div class="event-info">
									<div class="event-title"><?php echo esc_html( $slide['title'] ); ?></div>
									<div class="event-meta">
										<?php if ( '' !== $slide['duration'] ) : ?>
										<span class="event-duration"><?php echo esc_html( $slide['duration'] ); ?></span>
										<?php endif; ?>
										<i class="fas fa-play-circle event-play-icon" aria-hidden="true"></i>
									</div>
								</div>
							</a>
						</div>
							<?php
						endforeach;
						?>
					</div>
				</div>
			</div>
		</section>
		<?php
		echo $args['after_widget'];
	}
}

/**
 * Media-upload helper widget base for ad/banner style widgets.
 */
abstract class Communicationstoday_Abstract_Attachment_Banner_Widget extends WP_Widget {

	/**
	 * @param int $attachment_id Attachment ID.
	 * @return string
	 */
	protected function get_attachment_image_url( $attachment_id ) {
		$attachment_id = absint( $attachment_id );
		if ( $attachment_id <= 0 ) {
			return '';
		}
		$url = wp_get_attachment_image_url( $attachment_id, 'full' );
		return $url ? (string) $url : '';
	}

	/**
	 * @param string $field_base  Field base key.
	 * @param int    $attachment_id Attachment ID.
	 * @param string $image_label Label.
	 * @return void
	 */
	protected function render_media_field( $field_base, $attachment_id, $image_label ) {
		$preview_url = $this->get_attachment_image_url( $attachment_id );
		?>
		<div class="communicationstoday-think-tank-ad-media">
			<p>
				<label><?php echo esc_html( $image_label ); ?></label><br>
				<input type="hidden" class="think-tank-attachment-id" id="<?php echo esc_attr( $this->get_field_id( $field_base ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field_base ) ); ?>" value="<?php echo esc_attr( (string) $attachment_id ); ?>">
				<img src="<?php echo esc_url( $preview_url ); ?>" alt="" class="think-tank-attachment-preview" style="max-width:100%;height:auto;<?php echo $preview_url ? '' : 'display:none;'; ?>">
			</p>
			<p>
				<button type="button" class="button communicationstoday-think-tank-media"><?php esc_html_e( 'Select image', 'communicationstoday' ); ?></button>
				<button type="button" class="button communicationstoday-think-tank-remove" style="<?php echo $attachment_id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove image', 'communicationstoday' ); ?></button>
			</p>
			<p>
				<label><?php esc_html_e( 'Image URL', 'communicationstoday' ); ?></label>
				<input class="widefat think-tank-image-url" type="url" value="<?php echo esc_attr( $preview_url ); ?>" readonly>
			</p>
		</div>
		<?php
	}

	/**
	 * @param array<string, mixed> $instance Instance.
	 * @param string               $class     CSS class.
	 * @param bool                 $allow_link Optional target link.
	 * @return void
	 */
	protected function render_banner_output( $instance, $class, $allow_link = false ) {
		$attachment_id = isset( $instance['attachment_id'] ) ? absint( $instance['attachment_id'] ) : 0;
		$image_url     = $this->get_attachment_image_url( $attachment_id );
		if ( '' === $image_url && isset( $instance['image_url'] ) ) {
			$image_url = esc_url( (string) $instance['image_url'] );
		}
		if ( '' === $image_url ) {
			return;
		}

		$alt = isset( $instance['alt'] ) ? sanitize_text_field( (string) $instance['alt'] ) : '';
		if ( '' === $alt ) {
			$alt = __( 'Advertisement', 'communicationstoday' );
		}

		$link = '';
		if ( $allow_link ) {
			$link = isset( $instance['link_url'] ) ? esc_url( (string) $instance['link_url'] ) : '';
		}

		if ( $allow_link && $link ) {
			echo '<a href="' . esc_url( $link ) . '" class="' . esc_attr( $class ) . '" target="_blank" rel="noopener noreferrer">';
		} else {
			echo '<div class="' . esc_attr( $class ) . '">';
		}

		printf(
			'<img src="%1$s" alt="%2$s" loading="lazy" decoding="async">',
			esc_url( $image_url ),
			esc_attr( $alt )
		);

		if ( $allow_link && $link ) {
			echo '</a>';
		} else {
			echo '</div>';
		}
	}
}

/**
 * Archive right rail side banner with media upload + image URL + optional target link.
 */
class Communicationstoday_Archive_Side_Banner_Widget extends Communicationstoday_Abstract_Attachment_Banner_Widget {

	public function __construct() {
		parent::__construct(
			'communicationstoday_archive_side_banner',
			esc_html__( 'Archive side banner', 'communicationstoday' ),
			array(
				'description' => esc_html__( 'For “Archive listing — right rail”: upload image (with visible image URL) and optional click URL.', 'communicationstoday' ),
			)
		);
	}

	public function form( $instance ) {
		$attachment_id = isset( $instance['attachment_id'] ) ? absint( $instance['attachment_id'] ) : 0;
		$link_url      = isset( $instance['link_url'] ) ? (string) $instance['link_url'] : '';
		$alt           = isset( $instance['alt'] ) ? (string) $instance['alt'] : '';
		$this->render_media_field( 'attachment_id', $attachment_id, __( 'Banner image', 'communicationstoday' ) );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'link_url' ) ); ?>"><?php esc_html_e( 'Click URL (optional)', 'communicationstoday' ); ?></label>
			<input class="widefat" type="url" id="<?php echo esc_attr( $this->get_field_id( 'link_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link_url' ) ); ?>" value="<?php echo esc_attr( $link_url ); ?>" placeholder="https://">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'alt' ) ); ?>"><?php esc_html_e( 'Image alt text', 'communicationstoday' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'alt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'alt' ) ); ?>" value="<?php echo esc_attr( $alt ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );
		$instance                  = array();
		$instance['attachment_id'] = isset( $new_instance['attachment_id'] ) ? absint( $new_instance['attachment_id'] ) : 0;
		$instance['link_url']      = isset( $new_instance['link_url'] ) ? esc_url_raw( trim( wp_unslash( $new_instance['link_url'] ) ) ) : '';
		$instance['alt']           = isset( $new_instance['alt'] ) ? sanitize_text_field( wp_unslash( $new_instance['alt'] ) ) : '';
		return $instance;
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		$this->render_banner_output( $instance, 'archive-side-banner', true );
		echo $args['after_widget'];
	}
}

/**
 * Leaderboard top banner (site-wide) - image upload only (no click URL).
 */
class Communicationstoday_Leaderboard_Banner_Widget extends Communicationstoday_Abstract_Attachment_Banner_Widget {

	public function __construct() {
		parent::__construct(
			'communicationstoday_leaderboard_banner',
			esc_html__( 'Leaderboard banner', 'communicationstoday' ),
			array(
				'description' => esc_html__( 'Top leaderboard image upload (no click URL). Place in “Leaderboard (top, site-wide)”.', 'communicationstoday' ),
			)
		);
	}

	public function form( $instance ) {
		$attachment_id = isset( $instance['attachment_id'] ) ? absint( $instance['attachment_id'] ) : 0;
		$alt           = isset( $instance['alt'] ) ? (string) $instance['alt'] : '';
		$this->render_media_field( 'attachment_id', $attachment_id, __( 'Banner image', 'communicationstoday' ) );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'alt' ) ); ?>"><?php esc_html_e( 'Image alt text', 'communicationstoday' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'alt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'alt' ) ); ?>" value="<?php echo esc_attr( $alt ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );
		$instance                  = array();
		$instance['attachment_id'] = isset( $new_instance['attachment_id'] ) ? absint( $new_instance['attachment_id'] ) : 0;
		$instance['alt']           = isset( $new_instance['alt'] ) ? sanitize_text_field( wp_unslash( $new_instance['alt'] ) ) : '';
		return $instance;
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		$this->render_banner_output( $instance, 'top-ad-banner', false );
		echo $args['after_widget'];
	}
}

/**
 * Leaderboard bottom banner (archive mid ad) - image upload only (no click URL).
 */
class Communicationstoday_Bottom_Ad_Banner_Widget extends Communicationstoday_Abstract_Attachment_Banner_Widget {

	public function __construct() {
		parent::__construct(
			'communicationstoday_bottom_ad_banner',
			esc_html__( 'Leaderboard banner (bottom)', 'communicationstoday' ),
			array(
				'description' => esc_html__( 'Bottom leaderboard image upload (no click URL). For “Archive listing — mid ad”.', 'communicationstoday' ),
			)
		);
	}

	public function form( $instance ) {
		$attachment_id = isset( $instance['attachment_id'] ) ? absint( $instance['attachment_id'] ) : 0;
		$alt           = isset( $instance['alt'] ) ? (string) $instance['alt'] : '';
		$this->render_media_field( 'attachment_id', $attachment_id, __( 'Banner image', 'communicationstoday' ) );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'alt' ) ); ?>"><?php esc_html_e( 'Image alt text', 'communicationstoday' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'alt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'alt' ) ); ?>" value="<?php echo esc_attr( $alt ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );
		$instance                  = array();
		$instance['attachment_id'] = isset( $new_instance['attachment_id'] ) ? absint( $new_instance['attachment_id'] ) : 0;
		$instance['alt']           = isset( $new_instance['alt'] ) ? sanitize_text_field( wp_unslash( $new_instance['alt'] ) ) : '';
		return $instance;
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		$this->render_banner_output( $instance, 'bottom-ad-banner', false );
		echo $args['after_widget'];
	}
}

add_action(
	'widgets_init',
	function () {
		register_widget( 'Communicationstoday_Homepage_Stories_Widget' );
		register_widget( 'Communicationstoday_Think_Tank_Widget' );
		register_widget( 'Communicationstoday_Perspective_Swiper_Widget' );
		register_widget( 'Communicationstoday_Headlines_Day_Widget' );
		register_widget( 'Communicationstoday_Top_Events_Videos_Widget' );
		register_widget( 'Communicationstoday_Archive_Side_Banner_Widget' );
		register_widget( 'Communicationstoday_Leaderboard_Banner_Widget' );
		register_widget( 'Communicationstoday_Bottom_Ad_Banner_Widget' );
	}
);

/**
 * Media picker for Think Tank widget (Widgets + Customizer + block-based widgets screen).
 */
function communicationstoday_enqueue_think_tank_widget_admin() {
	if ( wp_script_is( 'communicationstoday-think-tank-admin', 'enqueued' ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'communicationstoday-think-tank-admin',
		get_template_directory_uri() . '/js/think-tank-widget-admin.js',
		array( 'jquery' ),
		_S_VERSION,
		true
	);
	wp_localize_script(
		'communicationstoday-think-tank-admin',
		'communicationstodayThinkTank',
		array(
			'select' => esc_html__( 'Choose image', 'communicationstoday' ),
			'use'    => esc_html__( 'Use image', 'communicationstoday' ),
		)
	);
}

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		$load = in_array( $hook, array( 'widgets.php', 'customize.php', 'site-editor.php' ), true );
		if ( 'themes.php' === $hook && isset( $_GET['page'] ) && 'gutenberg-widgets' === $_GET['page'] ) {
			$load = true;
		}
		if ( $load ) {
			communicationstoday_enqueue_think_tank_widget_admin();
		}
	},
	20
);

add_action( 'customize_controls_enqueue_scripts', 'communicationstoday_enqueue_think_tank_widget_admin' );
