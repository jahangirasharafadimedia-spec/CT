<?php
/**
 * Video archive.
 *
 * @package Communicationstoday
 */

get_header();
?>

	<main id="primary" class="site-main">
		<?php if ( have_posts() ) : ?>
			<header class="page-header">
				<h1 class="page-title"><?php post_type_archive_title(); ?></h1>
			</header>
			<div class="videos-archive-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.5rem;">
				<?php
				while ( have_posts() ) :
					the_post();
					$details   = communicationstoday_get_video_details( get_the_ID() );
					$duration  = $details['duration'];
					$thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' );
					?>
					<article <?php post_class(); ?>>
						<a href="<?php the_permalink(); ?>" class="video-card-link" style="text-decoration:none;color:inherit;display:block;">
							<?php if ( $thumb_url ) : ?>
								<div class="video-card-thumb" style="aspect-ratio:16/9;overflow:hidden;background:#111;">
									<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" class="w-100" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
								</div>
							<?php endif; ?>
							<h2 class="h5" style="margin:0.75rem 0 0;"><?php the_title(); ?></h2>
							<?php if ( $duration !== '' ) : ?>
								<p class="video-duration" style="margin:0.25rem 0 0;font-size:0.9em;opacity:0.85;"><?php echo esc_html( $duration ); ?></p>
							<?php endif; ?>
						</a>
					</article>
					<?php
				endwhile;
				?>
			</div>
			<?php the_posts_navigation(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No videos yet.', 'communicationstoday' ); ?></p>
		<?php endif; ?>
	</main>

<?php
get_footer();
