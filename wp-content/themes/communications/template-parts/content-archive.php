<?php
/**
 * Archive listing card (category / tag / date / author / videos).
 *
 * @package Communicationstoday
 */

$post_id    = get_the_ID();
$post_type  = get_post_type( $post_id );
$categories = get_the_category();
$cat_label  = '';
if ( ! empty( $categories ) ) {
	$cat_label = $categories[0]->name;
}
if ( '' === $cat_label && 'post' === $post_type ) {
	$cat_label = __( 'News', 'communicationstoday' );
}
if ( '' === $cat_label && 'ct_video' === $post_type ) {
	$cat_label = __( 'Videos', 'communicationstoday' );
}

$has_thumb = false;
$thumb_url = '';

if ( 'ct_video' === $post_type && function_exists( 'communicationstoday_get_video_card_image_url' ) ) {
	$thumb_url = communicationstoday_get_video_card_image_url( $post_id, 'medium_large' );
	if ( ! $thumb_url ) {
		$thumb_url = communicationstoday_get_video_card_image_url( $post_id, 'medium' );
	}
	$has_thumb = ( '' !== $thumb_url );
} elseif ( has_post_thumbnail( $post_id ) ) {
	$thumb_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
	if ( ! $thumb_url ) {
		$thumb_url = get_the_post_thumbnail_url( $post_id, 'medium' );
	}
	$has_thumb = (bool) $thumb_url;
}

$card_classes = array( 'listing-article-card' );
if ( ! $has_thumb ) {
	$card_classes[] = 'listing-article-card1';
}

$excerpt = get_the_excerpt();
if ( '' === trim( wp_strip_all_tags( (string) $excerpt ) ) ) {
	$excerpt = wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ), 45, '…' );
} else {
	$excerpt = wp_trim_words( wp_strip_all_tags( (string) $excerpt ), 45, '…' );
}
?>
<div id="post-<?php the_ID(); ?>" <?php post_class( $card_classes ); ?>>
	<?php if ( $has_thumb && $thumb_url ) : ?>
	<div class="listing-article-image">
		<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" class="w-100" loading="lazy" decoding="async">
		</a>
	</div>
	<?php endif; ?>
	<div class="listing-article-content">
		<h2 class="listing-article-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>
		<p class="listing-article-excerpt"><?php echo esc_html( $excerpt ); ?></p>
	</div>
</div>
