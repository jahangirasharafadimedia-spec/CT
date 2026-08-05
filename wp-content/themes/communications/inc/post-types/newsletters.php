<?php
/**
 * Newsletter custom post types (ACF-driven; no content editor).
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'COMMUNICATIONSTODAY_5G_NEWSLETTER_POST_TYPE', '5g_weekly_newsletter' );

/**
 * Newsletter CPT definitions.
 *
 * @return array<string, array<string, mixed>>
 */
function communicationstoday_get_newsletter_post_type_definitions() {
	return array(
		'5g_weekly_newsletter' => array(
			'menu_name'      => __( '5G Weekly Newsletters', 'communicationstoday' ),
			'singular'       => __( '5G Weekly Newsletter', 'communicationstoday' ),
			'plural'         => __( '5G Weekly Newsletters', 'communicationstoday' ),
			'slug'           => '5g_weekly_newsletter',
			'menu_position'  => 7,
			'menu_icon'      => 'dashicons-email-alt',
		),
		'daily_newsletter'     => array(
			'menu_name'      => __( 'Daily Newsletters', 'communicationstoday' ),
			'singular'       => __( 'Daily Newsletter', 'communicationstoday' ),
			'plural'         => __( 'Daily Newsletters', 'communicationstoday' ),
			'slug'           => 'daily_newsletter',
			'menu_position'  => 8,
			'menu_icon'      => 'dashicons-calendar-alt',
		),
		'weekly_roundup'       => array(
			'menu_name'      => __( 'Weekly Roundup', 'communicationstoday' ),
			'singular'       => __( 'Weekly Roundup', 'communicationstoday' ),
			'plural'         => __( 'Weekly Roundups', 'communicationstoday' ),
			'slug'           => 'weekly_roundup',
			'menu_position'  => 9,
			'menu_icon'      => 'dashicons-chart-bar',
		),
		'imc_newsletter'       => array(
			'menu_name'      => __( 'IMC Newsletters', 'communicationstoday' ),
			'singular'       => __( 'IMC Newsletter', 'communicationstoday' ),
			'plural'         => __( 'IMC Newsletters', 'communicationstoday' ),
			'slug'           => 'imc_newsletter',
			'menu_position'  => 10,
			'menu_icon'      => 'dashicons-megaphone',
		),
	);
}

/**
 * All registered newsletter post type slugs.
 *
 * @return string[]
 */
function communicationstoday_get_newsletter_post_types() {
	return array_keys( communicationstoday_get_newsletter_post_type_definitions() );
}

/**
 * Build labels array for a newsletter CPT.
 *
 * @param array<string, string> $config Config from definitions.
 * @return array<string, string>
 */
function communicationstoday_get_newsletter_post_type_labels( $config ) {
	$singular = $config['singular'];
	$plural   = $config['plural'];

	return array(
		'name'                  => $plural,
		'singular_name'         => $singular,
		'menu_name'             => $config['menu_name'],
		'name_admin_bar'        => $singular,
		'add_new'               => __( 'Add New', 'communicationstoday' ),
		'add_new_item'          => sprintf(
			/* translators: %s: singular post type label */
			__( 'Add New %s', 'communicationstoday' ),
			$singular
		),
		'edit_item'             => sprintf(
			/* translators: %s: singular post type label */
			__( 'Edit %s', 'communicationstoday' ),
			$singular
		),
		'new_item'              => sprintf(
			/* translators: %s: singular post type label */
			__( 'New %s', 'communicationstoday' ),
			$singular
		),
		'view_item'             => sprintf(
			/* translators: %s: singular post type label */
			__( 'View %s', 'communicationstoday' ),
			$singular
		),
		'view_items'            => sprintf(
			/* translators: %s: plural post type label */
			__( 'View %s', 'communicationstoday' ),
			$plural
		),
		'search_items'          => sprintf(
			/* translators: %s: plural post type label */
			__( 'Search %s', 'communicationstoday' ),
			$plural
		),
		'not_found'             => sprintf(
			/* translators: %s: plural post type label */
			__( 'No %s found.', 'communicationstoday' ),
			$plural
		),
		'not_found_in_trash'    => sprintf(
			/* translators: %s: plural post type label */
			__( 'No %s found in Trash.', 'communicationstoday' ),
			$plural
		),
		'all_items'             => sprintf(
			/* translators: %s: plural post type label */
			__( 'All %s', 'communicationstoday' ),
			$plural
		),
		'archives'              => sprintf(
			/* translators: %s: plural post type label */
			__( '%s Archives', 'communicationstoday' ),
			$plural
		),
	);
}

/**
 * Register newsletter post types.
 */
function communicationstoday_register_newsletter_post_types() {
	foreach ( communicationstoday_get_newsletter_post_type_definitions() as $post_type => $config ) {
		register_post_type(
			$post_type,
			array(
				'labels'              => communicationstoday_get_newsletter_post_type_labels( $config ),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => true,
				'query_var'           => true,
				'rewrite'             => array(
					'slug'       => $config['slug'],
					'with_front' => false,
				),
				'capability_type'     => 'post',
				'has_archive'         => true,
				'hierarchical'        => false,
				'menu_position'       => (int) $config['menu_position'],
				'menu_icon'           => $config['menu_icon'],
				'supports'            => array( 'title', 'thumbnail', 'author', 'revisions' ),
			)
		);
	}
}
add_action( 'init', 'communicationstoday_register_newsletter_post_types' );

/**
 * Flush permalinks when the theme is activated.
 */
function communicationstoday_newsletters_flush_rewrites() {
	communicationstoday_register_newsletter_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'communicationstoday_newsletters_flush_rewrites' );

/**
 * One-time flush after newsletter rewrite slug changes (underscore URLs).
 */
function communicationstoday_maybe_flush_newsletter_rewrites() {
	$version = '2026-05-17-newsletter-underscore-slugs';

	if ( get_option( 'communicationstoday_newsletter_rewrite_version' ) === $version ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( 'communicationstoday_newsletter_rewrite_version', $version );
}
add_action( 'init', 'communicationstoday_maybe_flush_newsletter_rewrites', 999 );
