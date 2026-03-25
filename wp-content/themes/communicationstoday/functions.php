<?php
/**
 * Communicationstoday functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Communicationstoday
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function communicationstoday_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on Communicationstoday, use a find and replace
		* to change 'communicationstoday' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'communicationstoday', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'communicationstoday' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'communicationstoday_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'communicationstoday_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function communicationstoday_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'communicationstoday_content_width', 640 );
}
add_action( 'after_setup_theme', 'communicationstoday_content_width', 0 );

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
}
add_action( 'widgets_init', 'communicationstoday_widgets_init' );

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

add_action(
	'widgets_init',
	function() {
		register_widget( 'Communicationstoday_Homepage_Stories_Widget' );
	}
);

/**
 * Enqueue scripts and styles.
 */
function communicationstoday_scripts() {
	wp_enqueue_style( 'communicationstoday-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'communicationstoday-style', 'rtl', 'replace' );

	wp_enqueue_script( 'communicationstoday-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'communicationstoday_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

