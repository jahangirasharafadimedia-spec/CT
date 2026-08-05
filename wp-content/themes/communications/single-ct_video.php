<?php
/**
 * Single video template (same layout as single.php).
 *
 * @package Communicationstoday
 */

get_header();
?>

<main id="primary" class="site-main single-post-main single-video-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php
		$detail_banner_id = function_exists( 'communicationstoday_get_video_detail_banner_id' )
			? communicationstoday_get_video_detail_banner_id( get_the_ID() )
			: 0;
		$has_detail_banner = $detail_banner_id > 0;
		?>
		<section class="article-detail-section">
			<div class="container">
				<div class="article-detail-layout">
						<div class="article-content-wrapper<?php echo $has_detail_banner ? '' : ' article-content-wrapper11'; ?>">
							<div class="article-date-wrapper">
							<div class="article-header">
								<span class="category-link"><?php echo esc_html( strtoupper( __( 'Videos', 'communicationstoday' ) ) ); ?></span>
								<span class="article-date"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
							</div>
							<h1 class="detail-title"><?php the_title(); ?></h1>
							<?php communicationstoday_render_post_share_links(); ?>
						</div>
						<?php if ( $has_detail_banner ) : ?>
						<div class="article-right">
							<?php
							echo wp_get_attachment_image(
								$detail_banner_id,
								'full',
								false,
								array(
									'class' => 'w-100',
									'alt'   => get_post_meta( $detail_banner_id, '_wp_attachment_image_alt', true ) ?: get_the_title(),
								)
							);
							?>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>

		<section class="article-detail-content">
			<div class="container">
				<div class="article-content-wrapper1">
					<div class="article-detail-content-left">
						<?php
						if ( function_exists( 'communicationstoday_render_video_player' ) ) {
							communicationstoday_render_video_player();
						}
						the_content();
						?>
					</div>

					<div class="article-detail-content-right">
						<?php communicationstoday_render_archive_listing_banner(); ?>
					</div>
				</div>
			</div>
		</section>
	<?php endwhile; ?>
</main>

<?php
get_footer();
