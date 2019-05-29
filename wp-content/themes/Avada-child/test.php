<?php
/* Template Name: TEST */
// phpinfo();
get_header();
 if (have_posts()) : while (have_posts()) : the_post(); ?>
the_title(); ?>
        <div class="entry">
            <?php the_content(); ?>
        </div><!-- entry -->
<?php endwhile; ?>
<?php endif;
get_footer();
