<?php
/**
 * Perspective category archive (same layout as archive.php; separate template for custom logic later).
 *
 * @package Communicationstoday
 */

get_header();
?>

<main id="primary" class="site-main">

	<section class="article-listing-section">
		<div class="container">

			<header class="archive-page-header">
				<h1 class="listing-article-category">
					<?php single_cat_title(); ?>
				</h1>
			</header>

			<div class="article-listing-layout">

				<div class="article-lisitng-content-wrapper" id="archive-post-list">

					<?php if ( have_posts() ) : ?>

						<?php
						$communicationstoday_archive_after_first = false;
						while ( have_posts() ) :
							the_post();
							get_template_part( 'template-parts/content', 'archive-perspective' );
							if ( ! $communicationstoday_archive_after_first && ! is_paged() ) {
								communicationstoday_render_archive_mid_ad();
								$communicationstoday_archive_after_first = true;
							}
						endwhile;
						?>

						<?php global $wp_query; ?>
						<?php if ( isset( $wp_query->max_num_pages ) && (int) $wp_query->max_num_pages > 1 ) : ?>
						<div class="archive-load-more-wrap">
							<button
								type="button"
								class="listing-article-category archive-load-more-button"
								data-page="1"
								data-max-pages="<?php echo esc_attr( (string) (int) $wp_query->max_num_pages ); ?>"
								data-query-vars="<?php echo esc_attr( wp_json_encode( $wp_query->query_vars ) ); ?>"
							>
								<?php esc_html_e( 'More posts', 'communicationstoday' ); ?>
							</button>
						</div>
						<?php endif; ?>

					<?php else : ?>

						<?php get_template_part( 'template-parts/content', 'none' ); ?>

					<?php endif; ?>

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
