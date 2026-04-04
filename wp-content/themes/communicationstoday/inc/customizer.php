<?php
/**
 * Communicationstoday Theme Customizer
 *
 * @package Communicationstoday
 */

/**
 * Default footer copyright string (current year).
 *
 * @return string
 */
function communicationstoday_get_footer_copyright_default() {
	return sprintf(
		/* translators: %s: current year (GMT). */
		__( 'Copyright © %s Communications Today', 'communicationstoday' ),
		(string) gmdate( 'Y' )
	);
}

/**
 * Footer copyright text from Customizer.
 *
 * @return string
 */
function communicationstoday_get_footer_copyright_text() {
	$default = communicationstoday_get_footer_copyright_default();
	$text    = get_theme_mod( 'communicationstoday_footer_copyright', $default );
	if ( ! is_string( $text ) || '' === trim( $text ) ) {
		return $default;
	}
	return $text;
}

/**
 * Selective refresh output for the copyright line (full &lt;p&gt; element).
 *
 * @return void
 */
function communicationstoday_customize_partial_footer_copyright() {
	echo '<p class="footer-copyright">' . esc_html( communicationstoday_get_footer_copyright_text() ) . '</p>';
}

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function communicationstoday_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'communicationstoday_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'communicationstoday_customize_partial_blogdescription',
			)
		);
	}

	$wp_customize->add_section(
		'communicationstoday_footer',
		array(
			'title'       => __( 'Footer', 'communicationstoday' ),
			'description' => __( 'Copyright line and footer widget areas (Appearance → Widgets: Footer 1–5).', 'communicationstoday' ),
			'priority'    => 90,
		)
	);

	$wp_customize->add_setting(
		'communicationstoday_footer_copyright',
		array(
			'default'           => communicationstoday_get_footer_copyright_default(),
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'communicationstoday_footer_copyright',
		array(
			'label'       => __( 'Copyright line', 'communicationstoday' ),
			'description' => __( 'Shown at the bottom of the footer. Plain text.', 'communicationstoday' ),
			'section'     => 'communicationstoday_footer',
			'type'        => 'textarea',
		)
	);

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'communicationstoday_footer_copyright_partial',
			array(
				'settings'            => array( 'communicationstoday_footer_copyright' ),
				'selector'            => '.footer-copyright',
				'container_inclusive' => true,
				'render_callback'     => 'communicationstoday_customize_partial_footer_copyright',
			)
		);
	}

	$wp_customize->add_section(
		'communicationstoday_ticker',
		array(
			'title'       => __( 'News ticker', 'communicationstoday' ),
			'description' => __( 'Header bar that scrolls the latest posts from one category.', 'communicationstoday' ),
			'priority'    => 86,
		)
	);

	$wp_customize->add_setting(
		'communicationstoday_ticker_category_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	$ticker_choices = array(
		0 => __( 'Default: category slug latest-news', 'communicationstoday' ),
	);
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $ticker_cat ) {
		$ticker_choices[ (int) $ticker_cat->term_id ] = $ticker_cat->name;
	}

	$wp_customize->add_control(
		'communicationstoday_ticker_category_id',
		array(
			'label'       => __( 'Category', 'communicationstoday' ),
			'description' => __( 'If Default is selected, the ticker uses the category whose slug is latest-news (Posts → Categories). Override by choosing another category.', 'communicationstoday' ),
			'section'     => 'communicationstoday_ticker',
			'type'        => 'select',
			'choices'     => $ticker_choices,
		)
	);

	$wp_customize->add_setting(
		'communicationstoday_ticker_label',
		array(
			'default'           => __( 'Weekly Feed', 'communicationstoday' ),
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'communicationstoday_ticker_label',
		array(
			'label'   => __( 'Ticker label', 'communicationstoday' ),
			'section' => 'communicationstoday_ticker',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'communicationstoday_ticker_post_count',
		array(
			'default'           => 10,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'communicationstoday_ticker_post_count',
		array(
			'label'       => __( 'Number of posts', 'communicationstoday' ),
			'description' => __( 'Between 1 and 20.', 'communicationstoday' ),
			'section'     => 'communicationstoday_ticker',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 1,
				'max'  => 20,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_section(
		'communicationstoday_social',
		array(
			'title'       => __( 'Social links', 'communicationstoday' ),
			'description' => __( 'These URLs are used for social icons in the mobile sidebar, top bar, and footer.', 'communicationstoday' ),
			'priority'    => 88,
		)
	);

	$social_fields = array(
		'linkedin' => __( 'LinkedIn URL', 'communicationstoday' ),
		'twitter'  => __( 'Twitter / X URL', 'communicationstoday' ),
		'youtube'  => __( 'YouTube URL', 'communicationstoday' ),
	);

	foreach ( $social_fields as $slug => $label ) {
		$id = 'communicationstoday_social_' . $slug;
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'       => $label,
				'description' => __( 'Full profile or channel URL, including https://', 'communicationstoday' ),
				'section'     => 'communicationstoday_social',
				'type'        => 'url',
				'input_attrs' => array(
					'placeholder' => 'https://',
				),
			)
		);
	}
}
add_action( 'customize_register', 'communicationstoday_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function communicationstoday_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function communicationstoday_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function communicationstoday_customize_preview_js() {
	wp_enqueue_script( 'communicationstoday-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), _S_VERSION, true );
}
add_action( 'customize_preview_init', 'communicationstoday_customize_preview_js' );
