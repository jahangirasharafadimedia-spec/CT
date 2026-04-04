<?php
/**
 * Archive listing card (category / tag / date / author).
 *
 * @package Communicationstoday
 */

$categories = get_the_category();
$cat_label  = '';
if ( ! empty( $categories ) ) {
	$cat_label = $categories[0]->name;
}
if ( '' === $cat_label && 'post' === get_post_type() ) {
	$cat_label = __( 'News', 'communicationstoday' );
}

$thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' );
if ( ! $thumb_url ) {
	$thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
}
if ( ! $thumb_url ) {
	$thumb_url = get_template_directory_uri() . '/asset/img/4a5e7e0aed3b12697a186a692abd5914622822d6.png';
}

$excerpt = get_the_excerpt();
if ( '' === trim( wp_strip_all_tags( (string) $excerpt ) ) ) {
	$excerpt = wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', get_the_ID() ) ), 45, '…' );
} else {
	$excerpt = wp_trim_words( wp_strip_all_tags( (string) $excerpt ), 45, '…' );
}
?>
<div id="post-<?php the_ID(); ?>" <?php post_class( 'listing-article-card' ); ?>>
	<div class="listing-article-image">
		<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" class="w-100" loading="lazy" decoding="async">
		</a>
	</div>
	<div class="listing-article-content">
		<?php if ( '' !== $cat_label ) : ?>
		<span class="listing-article-category"><?php echo esc_html( $cat_label ); ?></span>
		<?php endif; ?>
		<h2 class="listing-article-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>
		<p class="listing-article-excerpt"><?php echo esc_html( $excerpt ); ?></p>
	</div>
</div>
