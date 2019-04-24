<?php if ( ! defined( 'ABSPATH' ) ) {exit( 'Direct script access denied.' );} ?>

				</div>  <!-- fusion-row -->
			</main>  <!-- #main -->
			<div class="fusion-footer"> <?php get_template_part( 'templates/footer-content' ); ?> </div> <!-- fusion-footer -->

		</div> <!-- wrapper -->

		<?php wp_footer(); 
		$sliderID = ".owlCarousel_".$post->ID;
		?>
			<script> (function($) { 
				jQuery("<?php echo $sliderID; ?>").owlCarousel({loop:true, margin: 10, nav: true, autoplay:true, autoplayTimeout:15000, URLhashListener:true, autoplayHoverPause:true, startPosition: "URLHash", responsive: {0: {items: 1 }, 600: {items: 1 }, 1000: {items: 2 } } }) 
				jQuery('.skillbar').each(function(){jQuery(this).find('.skillbar-bar').animate({width:jQuery(this).attr('data-percent') },5000);
	        });
			})(jQuery); </script>
	</body>
</html>
