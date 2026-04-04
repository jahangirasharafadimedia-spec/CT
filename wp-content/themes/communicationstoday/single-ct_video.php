<?php
/**
 * Single video template.
 *
 * @package Communicationstoday
 */

get_header();
?>

	<main id="primary" class="site-main">
		<?php
		while ( have_posts() ) :
			the_post();
			$details = communicationstoday_get_video_details( get_the_ID() );
			$video_url = $details['url'];
			$duration  = $details['duration'];
			$poster_id = get_post_thumbnail_id();
			$poster    = $poster_id ? wp_get_attachment_image_url( $poster_id, 'large' ) : '';
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					<?php if ( $duration !== '' ) : ?>
						<p class="video-duration"><span class="screen-reader-text"><?php esc_html_e( 'Duration', 'communicationstoday' ); ?> </span><?php echo esc_html( $duration ); ?></p>
					<?php endif; ?>
				</header>

				<?php if ( $poster ) : ?>
					<meta itemprop="thumbnailUrl" content="<?php echo esc_url( $poster ); ?>">
				<?php endif; ?>

				<div class="entry-content video-entry-content">
					<?php
					if ( $video_url ) {
						$embed = wp_oembed_get( $video_url, array( 'width' => 848 ) );
						if ( $embed ) {
							echo '<div class="video-embed-responsive">' . $embed . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} elseif ( preg_match( '/\.(mp4|webm|ogg)(\?.*)?$/i', $video_url ) ) {
							?>
							<video class="w-100" controls playsinline <?php echo $poster ? 'poster="' . esc_url( $poster ) . '"' : ''; ?>>
								<source src="<?php echo esc_url( $video_url ); ?>">
							</video>
							<?php
						} else {
							printf(
								'<p><a class="button" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a></p>',
								esc_url( $video_url ),
								esc_html__( 'Open video', 'communicationstoday' )
							);
						}
					}

					the_content();
					?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</main>

<?php
get_footer();
