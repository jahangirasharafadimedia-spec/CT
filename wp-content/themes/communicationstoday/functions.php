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
			'menu-1'        => esc_html__( 'Primary', 'communicationstoday' ),
			'sidebar-popup' => esc_html__( 'Mobile / sidebar panel', 'communicationstoday' ),
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
 * Widgets & related theme hooks (sidebars, custom widgets, category image, think tank helpers).
 */
require get_template_directory() . '/inc/functions/widgets.php';

/**
 * Custom post types.
 */
require get_template_directory() . '/inc/post-types/register.php';

/**
 * Live search (AJAX).
 */
require get_template_directory() . '/inc/ajax-live-search.php';

/**
 * Enqueue scripts and styles.
 */
function communicationstoday_scripts() {
	wp_enqueue_style( 'communicationstoday-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'communicationstoday-style', 'rtl', 'replace' );

	wp_enqueue_script( 'communicationstoday-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	wp_register_script(
		'swiper',
		'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
		array(),
		'11.0.0',
		true
	);
	wp_enqueue_script(
		'communicationstoday-custom',
		get_template_directory_uri() . '/asset/js/custom.js',
		array( 'swiper' ),
		_S_VERSION,
		true
	);
	wp_localize_script(
		'communicationstoday-custom',
		'communicationstodaySearch',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'communicationstoday_search' ),
			'i18n'    => array(
				'minChars' => __( 'Type at least 3 characters to search.', 'communicationstoday' ),
				'loading'  => __( 'Searching…', 'communicationstoday' ),
				'error'    => __( 'Something went wrong. Try again.', 'communicationstoday' ),
			),
		)
	);

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
 * Sidebar off-canvas menu walker.
 */
require get_template_directory() . '/inc/class-communicationstoday-sidebar-nav-walker.php';

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

