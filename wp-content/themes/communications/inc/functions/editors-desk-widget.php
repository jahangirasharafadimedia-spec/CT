<?php
/**
 * "From The Editor's Desk" archive sidebar widget (editors-desk category).
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Category slugs that use the editors-desk archive sidebar layout.
 *
 * @return string[]
 */
function communicationstoday_get_editors_desk_category_slugs() {
	return apply_filters(
		'communicationstoday_editors_desk_category_slugs',
		array( 'editors-desk', 'editors_desk' )
	);
}

/**
 * Whether the current view is an editors-desk category archive.
 *
 * @return bool
 */
function communicationstoday_is_editors_desk_category_archive() {
	if ( ! is_category() ) {
		return false;
	}
	$term = get_queried_object();
	if ( ! $term || ! isset( $term->slug ) ) {
		return false;
	}
	return in_array( $term->slug, communicationstoday_get_editors_desk_category_slugs(), true );
}

/**
 * Archive sidebar widget: photo, name, and bio for Editor's Desk.
 */
class Communicationstoday_Editors_Desk_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'communicationstoday_editors_desk',
			__( "From The Editor's Desk", 'communicationstoday' ),
			array(
				'description' => __( "Profile sidebar for editors-desk archive. Add to \"Editor's Desk — archive sidebar\" under Appearance → Widgets.", 'communicationstoday' ),
			)
		);
	}

	/**
	 * @param array<string, mixed> $instance Widget instance.
	 * @return bool
	 */
	protected function should_display( $instance ) {
		$slug = ! empty( $instance['category_slug'] ) ? sanitize_title( (string) $instance['category_slug'] ) : 'editors-desk';

		if ( ! is_category() ) {
			return false;
		}

		$term = get_queried_object();
		return $term && isset( $term->slug ) && $term->slug === $slug;
	}

	/**
	 * @param array<string, mixed> $instance Instance.
	 * @return void
	 */
	public function form( $instance ) {
		$title         = ! empty( $instance['title'] ) ? $instance['title'] : __( "From The Editor's Desk", 'communicationstoday' );
		$attachment_id = isset( $instance['attachment_id'] ) ? absint( $instance['attachment_id'] ) : 0;
		$name          = isset( $instance['name'] ) ? (string) $instance['name'] : '';
		$bio           = isset( $instance['bio'] ) ? (string) $instance['bio'] : '';
		$category_slug = isset( $instance['category_slug'] ) ? (string) $instance['category_slug'] : 'editors-desk';
		$preview_url   = $attachment_id ? (string) wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Section heading', 'communicationstoday' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category_slug' ) ); ?>"><?php esc_html_e( 'Show only on category slug', 'communicationstoday' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'category_slug' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category_slug' ) ); ?>" value="<?php echo esc_attr( $category_slug ); ?>">
			<small><?php esc_html_e( 'Default: editors-desk', 'communicationstoday' ); ?></small>
		</p>
		<div class="communicationstoday-think-tank-ad-media">
			<p>
				<label><?php esc_html_e( 'Photo', 'communicationstoday' ); ?></label><br>
				<input type="hidden" class="think-tank-attachment-id" id="<?php echo esc_attr( $this->get_field_id( 'attachment_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'attachment_id' ) ); ?>" value="<?php echo esc_attr( (string) $attachment_id ); ?>">
				<img src="<?php echo esc_url( $preview_url ); ?>" alt="" class="think-tank-attachment-preview" style="max-width:120px;height:auto;border-radius:50%;<?php echo $preview_url ? '' : 'display:none;'; ?>">
			</p>
			<p>
				<button type="button" class="button communicationstoday-think-tank-media"><?php esc_html_e( 'Select image', 'communicationstoday' ); ?></button>
				<button type="button" class="button communicationstoday-think-tank-remove" style="<?php echo $attachment_id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove image', 'communicationstoday' ); ?></button>
			</p>
		</div>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'name' ) ); ?>"><?php esc_html_e( 'Name', 'communicationstoday' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'name' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'name' ) ); ?>" value="<?php echo esc_attr( $name ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'bio' ) ); ?>"><?php esc_html_e( 'Biography', 'communicationstoday' ); ?></label>
			<textarea class="widefat" rows="8" id="<?php echo esc_attr( $this->get_field_id( 'bio' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'bio' ) ); ?>"><?php echo esc_textarea( $bio ); ?></textarea>
			<small><?php esc_html_e( 'Separate paragraphs with blank lines. Basic HTML allowed (bold, italic, links).', 'communicationstoday' ); ?></small>
		</p>
		<?php
	}

	/**
	 * @param array<string, mixed> $new_instance New instance.
	 * @param array<string, mixed> $old_instance Old instance.
	 * @return array<string, mixed>
	 */
	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );
		$instance                    = array();
		$instance['title']           = isset( $new_instance['title'] ) ? sanitize_text_field( wp_unslash( $new_instance['title'] ) ) : '';
		$instance['attachment_id']   = isset( $new_instance['attachment_id'] ) ? absint( $new_instance['attachment_id'] ) : 0;
		$instance['name']            = isset( $new_instance['name'] ) ? sanitize_text_field( wp_unslash( $new_instance['name'] ) ) : '';
		$bio_raw                     = isset( $new_instance['bio'] ) ? wp_unslash( $new_instance['bio'] ) : '';
		$instance['bio']             = wp_kses( $bio_raw, $this->bio_allowed_html() );
		$instance['category_slug']   = isset( $new_instance['category_slug'] ) ? sanitize_title( wp_unslash( $new_instance['category_slug'] ) ) : 'editors-desk';
		return $instance;
	}

	/**
	 * Allowed tags in biography HTML.
	 *
	 * @return array<string, array<string, bool>>
	 */
	protected function bio_allowed_html() {
		return array(
			'p'      => array(),
			'br'     => array(),
			'strong' => array(),
			'b'      => array(),
			'em'     => array(),
			'i'      => array(),
			'a'      => array(
				'href'   => true,
				'title'  => true,
				'target' => true,
				'rel'    => true,
			),
		);
	}

	/**
	 * Remove stray characters / empty paragraphs from pasted bio HTML.
	 *
	 * @param string $html Bio HTML.
	 * @return string
	 */
	protected function clean_bio_html( $html ) {
		$html = (string) $html;
		$html = str_replace( array( "\xc2\xa0", '&nbsp;' ), ' ', $html );
		$html = preg_replace( '/<(p|div)[^>]*>\s*(&raquo;|&laquo;|»|«|›|‹|\*)\s*<\/\1>/iu', '', $html );
		$html = preg_replace( '/\s*(&raquo;|&laquo;|»|«)\s*/u', ' ', $html );
		return trim( $html );
	}

	/**
	 * Format bio text into paragraphs when plain text.
	 *
	 * @param string $bio Raw bio.
	 * @return string
	 */
	protected function format_bio_html( $bio ) {
		$bio = trim( (string) $bio );
		if ( '' === $bio ) {
			return '';
		}

		if ( preg_match( '/<p|<br|<strong|<em|<ul|<ol|<li/i', $bio ) ) {
			return $this->clean_bio_html( wp_kses( $bio, $this->bio_allowed_html() ) );
		}

		$paragraphs = preg_split( "/\r\n|\r|\n\s*\n/", $bio );
		if ( ! is_array( $paragraphs ) ) {
			return $this->clean_bio_html( '<p>' . esc_html( $bio ) . '</p>' );
		}

		$html = '';
		foreach ( $paragraphs as $paragraph ) {
			$paragraph = trim( $paragraph );
			$paragraph = preg_replace( '/^(&raquo;|&laquo;|»|«|›|‹)\s*/u', '', $paragraph );
			if ( '' === $paragraph || preg_match( '/^(&raquo;|&laquo;|»|«|›|‹|\*)\s*$/u', $paragraph ) ) {
				continue;
			}
			$html .= '<p>' . nl2br( esc_html( $paragraph ) ) . '</p>';
		}

		return $this->clean_bio_html( $html );
	}

	/**
	 * @param array<string, mixed> $args     Widget args.
	 * @param array<string, mixed> $instance Instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		if ( ! $this->should_display( $instance ) ) {
			return;
		}

		$title         = ! empty( $instance['title'] ) ? $instance['title'] : __( "From The Editor's Desk", 'communicationstoday' );
		$attachment_id = isset( $instance['attachment_id'] ) ? absint( $instance['attachment_id'] ) : 0;
		$name          = isset( $instance['name'] ) ? trim( (string) $instance['name'] ) : '';
		$bio           = isset( $instance['bio'] ) ? (string) $instance['bio'] : '';
		$image_url     = $attachment_id ? (string) wp_get_attachment_image_url( $attachment_id, 'medium_large' ) : '';

		if ( '' === $image_url && '' === $name && '' === trim( wp_strip_all_tags( $bio ) ) ) {
			return;
		}

		$bio_html = $this->format_bio_html( $bio );

		echo $args['before_widget'];
		?>
		<aside class="editors-desk-panel sidebar-editor" aria-label="<?php echo esc_attr( $title ); ?>">
			<?php if ( $title ) : ?>
				<h2 class="editors-desk-panel__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>

			<?php if ( $image_url ) : ?>
				<div class="editors-desk-panel__photo-wrap">
					<img class="editors-desk-panel__photo" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $name ? $name : $title ); ?>" width="120" height="120" loading="lazy" decoding="async">
				</div>
			<?php endif; ?>

			<?php if ( $name ) : ?>
				<p class="editors-desk-panel__name"><?php echo esc_html( $name ); ?></p>
			<?php endif; ?>

			<?php if ( $bio_html ) : ?>
				<div class="editors-desk-panel__bio editor_content editor-bio">
					<?php echo $bio_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized in format_bio_html. ?>
				</div>
			<?php endif; ?>
		</aside>
		<?php
		echo $args['after_widget'];
	}
}

add_action(
	'widgets_init',
	function () {
		register_widget( 'Communicationstoday_Editors_Desk_Widget' );
	}
);
