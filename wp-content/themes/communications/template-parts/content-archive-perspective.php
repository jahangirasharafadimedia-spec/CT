<?php
/**
 * Perspective category archive listing card (Writer's Information ACF).
 *
 * @package Communicationstoday
 */

$post_id = get_the_ID();
$writer  = function_exists( 'communicationstoday_get_perspective_writer_data' )
	? communicationstoday_get_perspective_writer_data( $post_id )
	: array(
		'photo_url'      => '',
		'writer_name'    => '',
		'writer_post'    => '',
		'writer_company' => '',
	);

$has_writer_photo = '' !== $writer['photo_url'];

$featured_url = '';
if ( has_post_thumbnail( $post_id ) ) {
	$featured_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
	if ( ! $featured_url ) {
		$featured_url = get_the_post_thumbnail_url( $post_id, 'medium' );
	}
}
$has_featured = is_string( $featured_url ) && '' !== $featured_url;

$has_meta = '' !== $writer['writer_name']
	|| '' !== $writer['writer_post']
	|| '' !== $writer['writer_company'];

$card_classes = array( 'listing-article-card', 'perspective-listing-card' );
if ( ! $has_writer_photo && ! $has_featured ) {
	$card_classes[] = 'listing-article-card1';
}
?>
<div id="post-<?php the_ID(); ?>" <?php post_class( $card_classes ); ?>>
	<?php if ( $has_writer_photo || $has_featured ) : ?>
	<div class="perspective-listing-images">
		<?php if ( $has_writer_photo ) : ?>
		<div class="listing-article-image perspective-writer-photo">
			<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<img src="<?php echo esc_url( $writer['photo_url'] ); ?>" alt="<?php echo esc_attr( $writer['writer_name'] ? $writer['writer_name'] : get_the_title() ); ?>" class="w-100" loading="lazy" decoding="async">
			</a>
		</div>
		<?php endif; ?>
		<?php if ( $has_featured ) : ?>
		<div class="listing-article-image perspective-featured-photo">
			<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<img src="<?php echo esc_url( $featured_url ); ?>" alt="" class="w-100" loading="lazy" decoding="async">
			</a>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<div class="listing-article-content">
		<h2 class="listing-article-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>
		<?php if ( $has_meta && function_exists( 'communicationstoday_render_perspective_writer_meta' ) ) : ?>
			<?php communicationstoday_render_perspective_writer_meta( $post_id ); ?>
		<?php endif; ?>
	</div>
</div>
