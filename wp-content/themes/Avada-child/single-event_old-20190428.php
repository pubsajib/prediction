<?php
if ( ! defined( 'ABSPATH' ) ) { exit( 'Direct script access denied.' ); }
get_header('event'); ?>

<section id="content" style="width: 100%;">
	<div class="single-navigation clearfix">
		<?php previous_post_link( '%link', esc_attr__( 'Previous', 'Avada' ) ); ?>
		<?php next_post_link( '%link', esc_attr__( 'Next', 'Avada' ) ); ?>
	</div>

	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'post' ); ?>>
			<div class="post-content">
			    <?php 
				    // echo do_shortcode('[prediction id='. $post->ID .' html="box" avatarslider=0]');
				    Enhancement::loadEventSingle($post->ID);
				?>
			</div>
			<?php avada_render_social_sharing(); ?>
		</article>
	<?php endwhile; ?>
	<?php wp_reset_postdata(); ?>
</section>
<?php
get_footer('event');

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
