<?php
get_header(); ?>
	<section id="primary" class="content-area">
			<?php
			/* Start the Loop */
			while ( have_posts() ) :
				the_post();
				echo '<div style="width:60%;margin: 0 auto;">'; the_content(); echo "</div>";
			endwhile; // End of the loop.
			?>
	</section><!-- #primary -->
<?php get_footer();
