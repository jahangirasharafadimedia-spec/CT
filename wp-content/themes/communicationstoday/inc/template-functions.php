<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package Communicationstoday
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function communicationstoday_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) && ! is_active_sidebar( 'homepage-widget' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'communicationstoday_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function communicationstoday_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'communicationstoday_pingback_header' );

/**
 * Print the Site Identity custom logo as an &lt;img&gt; (no wrapper link). No width/height attributes — size from CSS.
 * Falls back to theme asset/img/logo.png.
 *
 * @param array<string, string> $attr Extra attributes for the image (e.g. class, loading).
 * @return void
 */
function communicationstoday_the_custom_logo_image( $attr = array() ) {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	$alt     = get_bloginfo( 'name', 'display' );

	$defaults = array(
		'class' => 'custom-logo',
		'alt'   => $alt,
	);
	$attr = wp_parse_args( $attr, $defaults );

	if ( $logo_id && wp_attachment_is_image( $logo_id ) ) {
		$src = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $src ) {
			$class = isset( $attr['class'] ) ? sanitize_text_field( (string) $attr['class'] ) : 'custom-logo';
			$alt_t = isset( $attr['alt'] ) ? (string) $attr['alt'] : $alt;
			printf(
				'<img src="%1$s" alt="%2$s" class="%3$s" decoding="async">',
				esc_url( $src ),
				esc_attr( $alt_t ),
				esc_attr( $class )
			);
			return;
		}
	}

	$fallback = get_template_directory_uri() . '/asset/img/logo.png';
	$class    = isset( $attr['class'] ) ? sanitize_text_field( (string) $attr['class'] ) : 'custom-logo';
	printf(
		'<img src="%1$s" alt="%2$s" class="%3$s" decoding="async">',
		esc_url( $fallback ),
		esc_attr( $alt ),
		esc_attr( trim( $class . ' custom-logo-fallback' ) )
	);
}

/**
 * Social profile definitions (Customizer URLs).
 *
 * @return array<string, array{icon: string, label: string}>
 */
function communicationstoday_get_social_networks() {
	return array(
		'linkedin' => array(
			'icon'  => 'fab fa-linkedin-in',
			'label' => __( 'LinkedIn', 'communicationstoday' ),
		),
		'twitter'  => array(
			'icon'  => 'fab fa-twitter',
			'label' => __( 'Twitter', 'communicationstoday' ),
		),
		'youtube'  => array(
			'icon'  => 'fab fa-youtube',
			'label' => __( 'YouTube', 'communicationstoday' ),
		),
	);
}

/**
 * Sanitized social URL from theme mod, or empty string.
 *
 * @param string $slug linkedin|twitter|youtube
 * @return string
 */
function communicationstoday_get_social_url( $slug ) {
	$networks = communicationstoday_get_social_networks();
	if ( ! is_string( $slug ) || ! isset( $networks[ $slug ] ) ) {
		return '';
	}
	$raw = get_theme_mod( 'communicationstoday_social_' . $slug, '' );
	if ( ! is_string( $raw ) ) {
		return '';
	}
	$raw = trim( $raw );
	if ( '' === $raw ) {
		return '';
	}
	return esc_url( $raw );
}

/**
 * Category term ID for the header news ticker: Customizer choice, else category slug latest-news (filterable).
 *
 * @return int
 */
function communicationstoday_get_ticker_category_id() {
	$mod_id = absint( get_theme_mod( 'communicationstoday_ticker_category_id', 0 ) );
	if ( $mod_id > 0 ) {
		$term = get_term( $mod_id, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			return $mod_id;
		}
	}

	$default_slugs = apply_filters(
		'communicationstoday_ticker_default_category_slugs',
		array( 'latest-news', 'latest_news' )
	);
	foreach ( (array) $default_slugs as $slug_try ) {
		if ( ! is_string( $slug_try ) || '' === $slug_try ) {
			continue;
		}
		$term = get_term_by( 'slug', sanitize_title( $slug_try ), 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			return (int) $term->term_id;
		}
	}

	return 0;
}

/**
 * Latest posts for the news ticker.
 *
 * @return WP_Post[]
 */
function communicationstoday_get_ticker_posts() {
	$cat_id = communicationstoday_get_ticker_category_id();
	if ( $cat_id <= 0 ) {
		return array();
	}

	$n = absint( get_theme_mod( 'communicationstoday_ticker_post_count', 10 ) );
	$n = min( 20, max( 1, $n ) );

	$query = new WP_Query(
		array(
			'cat'                    => $cat_id,
			'posts_per_page'         => $n,
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( ! $query->have_posts() ) {
		return array();
	}

	return $query->posts;
}

/**
 * Label text for the news ticker (Customizer).
 *
 * @return string
 */
function communicationstoday_get_ticker_label() {
	$label = get_theme_mod( 'communicationstoday_ticker_label', __( 'Weekly Feed', 'communicationstoday' ) );
	return is_string( $label ) ? $label : __( 'Weekly Feed', 'communicationstoday' );
}

/**
 * Echo social icon links (only networks with a URL set in Customizer).
 *
 * @param string        $link_class    Classes for each &lt;a&gt; (e.g. footer-social-icon).
 * @param string[]|null $network_order Slugs in display order; default LinkedIn, Twitter, YouTube.
 * @return void
 */
function communicationstoday_render_social_links( $link_class, $network_order = null ) {
	$networks = communicationstoday_get_social_networks();
	if ( ! is_array( $network_order ) || empty( $network_order ) ) {
		$network_order = array( 'linkedin', 'twitter', 'youtube' );
	}
	$link_class = is_string( $link_class ) ? trim( $link_class ) : '';
	foreach ( $network_order as $slug ) {
		if ( ! isset( $networks[ $slug ] ) ) {
			continue;
		}
		$url = communicationstoday_get_social_url( $slug );
		if ( '' === $url ) {
			continue;
		}
		$conf  = $networks[ $slug ];
		$class = trim( $link_class . ' communicationstoday-social-link communicationstoday-social-' . sanitize_key( $slug ) );
		?>
<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $class ); ?>" target="_blank" rel="noopener noreferrer">
	<i class="<?php echo esc_attr( $conf['icon'] ); ?>" aria-hidden="true"></i>
	<span class="screen-reader-text"><?php echo esc_html( $conf['label'] ); ?></span>
</a>
		<?php
	}
}
