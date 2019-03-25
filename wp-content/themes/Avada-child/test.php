<?php
/* Template Name: TEST */
get_header();
 if (have_posts()) : while (have_posts()) : the_post(); ?>

<?php the_title(); ?></h1>  
        <div class="entry">
            <?php the_content(); ?>
        </div><!-- entry -->
<?php endwhile; ?>
<?php endif;
get_footer();
