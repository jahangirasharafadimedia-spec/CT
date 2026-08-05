<?php
get_header();
?>

<main id="primary" class="site-main">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', get_post_type() );
		endwhile;

		the_posts_navigation();
	else :
		get_template_part( 'template-parts/content', 'none' );
	endif;
	?>
</main><!-- #main -->

<?php communicationstoday_render_homepage_widget_area(); ?>

<?php
get_footer();
