<?php
/**
 * Archive pages: category, tag, author, date, post type (except templates like archive-ct_video.php).
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Communicationstoday
 */

get_header();
?>

<main id="primary" class="site-main">

	<section class="article-listing-section">
		<div class="container">

			<header class="archive-page-header">
				<?php the_archive_title( '<h1 class="page-title archive-page-title">', '</h1>' ); ?>
				<?php the_archive_description( '<div class="archive-description taxonomy-description">', '</div>' ); ?>
			</header>

			<div class="article-listing-layout">

				<div class="article-lisitng-content-wrapper">

					<?php if ( have_posts() ) : ?>

						<?php
						$communicationstoday_archive_after_first = false;
						while ( have_posts() ) :
							the_post();
							get_template_part( 'template-parts/content', 'archive' );
							if ( ! $communicationstoday_archive_after_first ) {
								communicationstoday_render_archive_mid_ad();
								$communicationstoday_archive_after_first = true;
							}
						endwhile;
						?>

						<nav class="archive-pagination" aria-label="<?php esc_attr_e( 'Posts navigation', 'communicationstoday' ); ?>">
							<?php
							the_posts_pagination(
								array(
									'mid_size'  => 2,
									'prev_text' => __( 'Newer posts', 'communicationstoday' ),
									'next_text' => __( 'Older posts', 'communicationstoday' ),
								)
							);
							?>
						</nav>

					<?php else : ?>

						<?php get_template_part( 'template-parts/content', 'none' ); ?>

					<?php endif; ?>

				</div>

				<div class="post_mobile">
					<span class="listing-article-category"><?php esc_html_e( 'More posts', 'communicationstoday' ); ?></span>
				</div>

				<div class="article-lisitng-banner">
					<?php communicationstoday_render_archive_listing_banner(); ?>
				</div>

			</div>
		</div>
	</section>

</main>

<?php
get_footer();
