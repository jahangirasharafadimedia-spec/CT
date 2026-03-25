<?php
get_header();
?>

<main id="primary" class="site-main">
	<?php
	if (have_posts()) :
		while (have_posts()) :
			the_post();
			get_template_part('template-parts/content', get_post_type());
		endwhile;

		the_posts_navigation();
	else :
		get_template_part('template-parts/content', 'none');
	endif;
	?>
</main><!-- #main -->

<?php
if (is_active_sidebar('homepage-widget')) :
?>
	<aside id="secondary" class="widget-area">
		<?php dynamic_sidebar('homepage-widget'); ?>
	</aside><!-- #secondary -->
<?php
endif;



?>




<?php get_footer();
