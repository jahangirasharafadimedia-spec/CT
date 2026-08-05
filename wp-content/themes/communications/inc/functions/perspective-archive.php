<?php
/**
 * Perspective category archive helpers.
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Category slugs that use archive-perspective.php (including child categories).
 *
 * @return string[]
 */
function communicationstoday_get_perspective_category_slugs() {
	return apply_filters(
		'communicationstoday_perspective_category_slugs',
		array( 'perspective' )
	);
}

/**
 * Whether the current view is a perspective category archive (or a child of one).
 *
 * @param WP_Term|null $term Optional term; defaults to queried object on category archives.
 * @return bool
 */
function communicationstoday_is_perspective_category_archive( $term = null ) {
	if ( null === $term ) {
		if ( ! is_category() ) {
			return false;
		}
		$term = get_queried_object();
	}

	if ( ! $term instanceof WP_Term || 'category' !== $term->taxonomy ) {
		return false;
	}

	$slugs = communicationstoday_get_perspective_category_slugs();
	if ( ! is_array( $slugs ) ) {
		return false;
	}

	if ( in_array( $term->slug, $slugs, true ) ) {
		return true;
	}

	foreach ( $slugs as $slug ) {
		if ( ! is_string( $slug ) || '' === $slug ) {
			continue;
		}
		$parent = get_category_by_slug( $slug );
		if ( $parent && ! is_wp_error( $parent ) && term_is_ancestor_of( (int) $parent->term_id, (int) $term->term_id, 'category' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Whether a query vars array targets a perspective category archive.
 *
 * @param array<string, mixed> $query_vars Query vars from the main loop or AJAX.
 * @return bool
 */
function communicationstoday_is_perspective_archive_query( $query_vars ) {
	if ( ! is_array( $query_vars ) ) {
		return false;
	}

	if ( ! empty( $query_vars['category_name'] ) && is_string( $query_vars['category_name'] ) ) {
		$term = get_category_by_slug( sanitize_title( $query_vars['category_name'] ) );
		if ( $term && ! is_wp_error( $term ) ) {
			return communicationstoday_is_perspective_category_archive( $term );
		}
	}

	if ( ! empty( $query_vars['cat'] ) ) {
		$term = get_category( absint( $query_vars['cat'] ) );
		if ( $term && ! is_wp_error( $term ) ) {
			return communicationstoday_is_perspective_category_archive( $term );
		}
	}

	return false;
}

/**
 * Resolve an ACF image field value to a URL.
 *
 * @param mixed  $value Field value.
 * @param string $size  Image size.
 * @return string
 */
function communicationstoday_perspective_acf_image_url( $value, $size = 'medium_large' ) {
	if ( empty( $value ) ) {
		return '';
	}

	if ( is_string( $value ) ) {
		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}
		if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
			return $value;
		}
		if ( is_numeric( $value ) ) {
			$url = wp_get_attachment_image_url( (int) $value, $size );
			return $url ? (string) $url : '';
		}
	}

	if ( is_numeric( $value ) ) {
		$url = wp_get_attachment_image_url( (int) $value, $size );
		return $url ? (string) $url : '';
	}

	if ( is_array( $value ) ) {
		if ( ! empty( $value['url'] ) && is_string( $value['url'] ) ) {
			return $value['url'];
		}
		if ( ! empty( $value['ID'] ) ) {
			$url = wp_get_attachment_image_url( (int) $value['ID'], $size );
			return $url ? (string) $url : '';
		}
	}

	return '';
}

/**
 * Read a scalar ACF/meta value trying multiple keys.
 *
 * @param int                  $post_id Post ID.
 * @param string[]             $keys    Field names.
 * @param array<string, mixed> $row     Optional group row.
 * @return string
 */
function communicationstoday_perspective_acf_scalar( $post_id, $keys, $row = null ) {
	if ( is_array( $row ) ) {
		foreach ( $keys as $key ) {
			if ( ! is_string( $key ) || ! array_key_exists( $key, $row ) ) {
				continue;
			}
			$val = $row[ $key ];
			if ( is_string( $val ) || is_numeric( $val ) ) {
				$val = trim( (string) $val );
				if ( '' !== $val ) {
					return $val;
				}
			}
		}
	}

	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}

	foreach ( $keys as $key ) {
		if ( ! is_string( $key ) || '' === $key ) {
			continue;
		}
		$val = get_field( $key, $post_id );
		if ( is_string( $val ) || is_numeric( $val ) ) {
			$val = trim( (string) $val );
			if ( '' !== $val ) {
				return $val;
			}
		}
	}

	return '';
}

/**
 * Writer's Information for perspective archive cards (ACF).
 *
 * @param int $post_id Post ID.
 * @return array{photo_url: string, writer_name: string, writer_post: string, writer_company: string}
 */
function communicationstoday_get_perspective_writer_data( $post_id ) {
	$post_id = absint( $post_id );
	$data    = array(
		'photo_url'       => '',
		'writer_name'     => '',
		'writer_post'     => '',
		'writer_company'  => '',
	);

	if ( ! $post_id || ! function_exists( 'get_field' ) ) {
		return $data;
	}

	$group_keys = apply_filters(
		'communicationstoday_perspective_writer_group_field_names',
		array( 'writers_information', 'writer_information', 'writer_s_information' )
	);

	$photo_keys = apply_filters(
		'communicationstoday_perspective_writer_photo_field_names',
		array( 'writer_pic', 'photo', 'Photo' )
	);
	$name_keys = apply_filters(
		'communicationstoday_perspective_writer_name_field_names',
		array( 'writer_name', 'name', 'Name' )
	);
	$post_keys = apply_filters(
		'communicationstoday_perspective_writer_post_field_names',
		array( 'writer_post', 'designation', 'Designation' )
	);
	$company_keys = apply_filters(
		'communicationstoday_perspective_writer_company_field_names',
		array( 'writer_company', 'company_name', 'Company Name' )
	);

	$row = null;
	if ( is_array( $group_keys ) ) {
		foreach ( $group_keys as $group_key ) {
			if ( ! is_string( $group_key ) || '' === $group_key ) {
				continue;
			}
			$candidate = get_field( $group_key, $post_id );
			if ( is_array( $candidate ) && ! empty( $candidate ) ) {
				$row = $candidate;
				break;
			}
		}
	}

	foreach ( $photo_keys as $key ) {
		$raw = null;
		if ( is_array( $row ) && isset( $row[ $key ] ) ) {
			$raw = $row[ $key ];
		} elseif ( is_string( $key ) && '' !== $key ) {
			$raw = get_field( $key, $post_id );
		}
		$url = communicationstoday_perspective_acf_image_url( $raw );
		if ( '' !== $url ) {
			$data['photo_url'] = $url;
			break;
		}
	}

	$data['writer_name']    = communicationstoday_perspective_acf_scalar( $post_id, $name_keys, $row );
	$data['writer_post']    = communicationstoday_perspective_acf_scalar( $post_id, $post_keys, $row );
	$data['writer_company'] = communicationstoday_perspective_acf_scalar( $post_id, $company_keys, $row );

	return apply_filters( 'communicationstoday_perspective_writer_data', $data, $post_id );
}

/**
 * Echo writer name + role line (same markup as perspective archive listing).
 *
 * @param int|null $post_id Post ID; defaults to current post.
 * @return void
 */
function communicationstoday_render_perspective_writer_meta( $post_id = null ) {
	$post_id = null === $post_id ? get_the_ID() : absint( $post_id );
	if ( $post_id <= 0 ) {
		return;
	}

	$writer = communicationstoday_get_perspective_writer_data( $post_id );

	$has_meta = '' !== $writer['writer_name']
		|| '' !== $writer['writer_post']
		|| '' !== $writer['writer_company'];

	if ( ! $has_meta ) {
		return;
	}

	$writer_role_parts = array();
	if ( '' !== $writer['writer_post'] ) {
		$writer_role_parts[] = $writer['writer_post'];
	}
	if ( '' !== $writer['writer_company'] ) {
		$writer_role_parts[] = $writer['writer_company'];
	}
	$writer_role_line = implode( ', ', $writer_role_parts );
	?>
	<div class="perspective-writer-meta">
		<?php if ( '' !== $writer['writer_name'] ) : ?>
			<p class="perspective-writer-name"><?php echo esc_html( $writer['writer_name'] ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $writer_role_line ) : ?>
			<p class="perspective-writer-role"><?php echo esc_html( $writer_role_line ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Whether a post belongs to the perspective category (or a child of it).
 *
 * @param int|null $post_id Post ID; defaults to current post in the loop.
 * @return bool
 */
function communicationstoday_is_perspective_post( $post_id = null ) {
	$post_id = null === $post_id ? get_queried_object_id() : absint( $post_id );
	if ( $post_id <= 0 || 'post' !== get_post_type( $post_id ) ) {
		return false;
	}

	$slugs      = communicationstoday_get_perspective_category_slugs();
	$post_cats  = get_the_category( $post_id );
	if ( ! is_array( $slugs ) || empty( $post_cats ) ) {
		return false;
	}

	foreach ( $slugs as $slug ) {
		if ( ! is_string( $slug ) || '' === $slug ) {
			continue;
		}
		$perspective = get_category_by_slug( $slug );
		if ( ! $perspective || is_wp_error( $perspective ) ) {
			continue;
		}
		foreach ( $post_cats as $cat ) {
			if ( ! $cat instanceof WP_Term ) {
				continue;
			}
			if ( (int) $cat->term_id === (int) $perspective->term_id ) {
				return true;
			}
			if ( term_is_ancestor_of( (int) $perspective->term_id, (int) $cat->term_id, 'category' ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Load single-perspective.php for posts in the perspective category.
 *
 * @param string $template Path to the template file WordPress would load.
 * @return string
 */
function communicationstoday_perspective_single_template( $template ) {
	if ( is_singular( 'post' ) && communicationstoday_is_perspective_post() ) {
		$perspective_template = locate_template( 'single-perspective.php' );
		if ( $perspective_template ) {
			return $perspective_template;
		}
	}
	return $template;
}
add_filter( 'single_template', 'communicationstoday_perspective_single_template' );
