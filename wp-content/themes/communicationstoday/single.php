<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Communicationstoday
 */

get_header();
?>

<main id="primary" class="site-main single-post-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<section class="article-detail-section">
			<div class="container">
				<div class="article-detail-layout">
					<div class="article-content-wrapper">
						<div class="article-date-wrapper">
							<div class="article-header">
								<?php
								$categories      = get_the_category();
								$primary_cat     = ! empty( $categories ) ? $categories[0]->name : __( 'Uncategorized', 'communicationstoday' );
								?>
								<span class="category-link"><?php echo esc_html( strtoupper( $primary_cat ) ); ?></span>
								<span class="article-date"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
							</div>
							<h1 class="detail-title"><?php the_title(); ?></h1>
						</div>
						<div class="article-right">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'full', array( 'class' => 'w-100', 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="article-detail-content">
			<div class="container">
				<div class="article-content-wrapper1">
					<div class="article-detail-content-left">
						<?php the_content(); ?>
					</div>

					<div class="article-detail-content-right">
						<?php communicationstoday_render_archive_listing_banner(); ?>
					</div>
				</div>
			</div>
		</section>
	<?php endwhile; ?>
</main>

<?php get_footer();
