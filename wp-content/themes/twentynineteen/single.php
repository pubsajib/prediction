<?php get_header(); ?>
<section id="primary" class="content-area">
	<main id="main" class="site-main">
		<?php while ( have_posts() ) : the_post();
			echo '<div style="width:60%;margin: 0 auto;">'; 
				echo do_shortcode('[prediction id='. $post->ID .']'); 
			echo "</div>";
		endwhile; ?>
	</main><!-- #main -->
</section><!-- #primary -->
<?php get_footer();
