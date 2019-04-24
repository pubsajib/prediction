<?php
/**
 * The footer template.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
					<?php do_action( 'avada_after_main_content' ); ?>

				</div>  <!-- fusion-row -->
			</main>  <!-- #main -->
			<?php do_action( 'avada_after_main_container' ); ?>

			<?php global $social_icons; ?>

			<?php
			/**
			 * Get the correct page ID.
			 */
			$c_page_id = Avada()->fusion_library->get_page_id();
			?>

			<?php
			/**
			 * Only include the footer.
			 */
			?>
			<?php if ( ! is_page_template( 'blank.php' ) ) : ?>
				<?php $footer_parallax_class = ( 'footer_parallax_effect' === Avada()->settings->get( 'footer_special_effects' ) ) ? ' fusion-footer-parallax' : ''; ?>

				<div class="fusion-footer<?php echo esc_attr( $footer_parallax_class ); ?>">
					<?php get_template_part( 'templates/footer-content' ); ?>
				</div> <!-- fusion-footer -->
			<?php endif; // End is not blank page check. ?>

			<?php
			/**
			 * Add sliding bar.
			 */
			?>
			<?php if ( Avada()->settings->get( 'slidingbar_widgets' ) && ! is_page_template( 'blank.php' ) ) : ?>
				<?php get_template_part( 'sliding_bar' ); ?>
			<?php endif; ?>
		</div> <!-- wrapper -->

		<?php
		/**
		 * Check if boxed side header layout is used; if so close the #boxed-wrapper container.
		 */
		$page_bg_layout = 'default';
		if ( $c_page_id && is_numeric( $c_page_id ) ) {
			$fpo_page_bg_layout = get_post_meta( $c_page_id, 'pyre_page_bg_layout', true );
			$page_bg_layout = ( $fpo_page_bg_layout ) ? $fpo_page_bg_layout : $page_bg_layout;
		}
		?>
		<?php if ( ( ( 'Boxed' === Avada()->settings->get( 'layout' ) && 'default' === $page_bg_layout ) || 'boxed' === $page_bg_layout ) && 'Top' !== Avada()->settings->get( 'header_position' ) ) : ?>
			</div> <!-- #boxed-wrapper -->
		<?php endif; ?>
		<?php if ( ( ( 'Boxed' === Avada()->settings->get( 'layout' ) && 'default' === $page_bg_layout ) || 'boxed' === $page_bg_layout ) && 'framed' === Avada()->settings->get( 'scroll_offset' ) && 0 !== intval( Avada()->settings->get( 'margin_offset', 'top' ) ) ) : ?>
			<div class="fusion-top-frame"></div>
			<div class="fusion-bottom-frame"></div>
			<?php if ( 'None' !== Avada()->settings->get( 'boxed_modal_shadow' ) ) : ?>
				<div class="fusion-boxed-shadow"></div>
			<?php endif; ?>
		<?php endif; ?>
		<a class="fusion-one-page-text-link fusion-page-load-link"></a>

		<?php wp_footer(); 
		if (is_singular('event')) { 
		$sliderID = ".owlCarousel_".$post->ID;
		?>
			<script> (function($) { 
				jQuery("<?php echo $sliderID; ?>").owlCarousel({loop:true, margin: 10, nav: true, autoplay:true, autoplayTimeout:15000, URLhashListener:true, autoplayHoverPause:true, startPosition: "URLHash", responsive: {0: {items: 1 }, 600: {items: 1 }, 1000: {items: 2 } } }) 
				jQuery('.skillbar').each(function(){jQuery(this).find('.skillbar-bar').animate({width:jQuery(this).attr('data-percent') },5000);
	        });
			})(jQuery); </script>
		<?php } ?>
<!-- 		<div class="md-modal md-effect-12">
			<div class="md-content custom-mobile-menu">
				<div class="header">
                     <div class="close-pop">
						<img class="md-close" src="https://cricdiction.com/wp-content/uploads/2019/03/delete.png">
					</div>
					<div class="logo">
						<a href="https://cricdiction.com/"><img src="https://cricdiction.com/wp-content/uploads/2018/10/CRIC.png"></a>
					</div>
					<div class="links">
						<ul>
							<li><a href="https://cricdiction.com/log-in/?redirect_to=https://cricdiction.com/road-to-top-10/">Log In</a></li>
							<li class="ml"><a href="https://cricdiction.com/contact-us/">Contact</a></li>
						</ul>
					</div>
				</div>
				<ul class="mobile-menu-tabs">
					<li class="tab-link current" data-tab="matches"><img src="https://cricdiction.com/wp-content/uploads/2019/01/cricket.png"><span>Matches</span></li>
					<li class="tab-link" data-tab="experts"><img src="https://cricdiction.com/wp-content/uploads/2019/01/rank.png"><span>Experts</span></li>
					<li class="tab-link" data-tab="disqus"><img src="https://cricdiction.com/wp-content/uploads/2019/03/conversation.png"><span>Disqus</span></li>
					<li class="tab-link" data-tab="soccer"><img src="https://cricdiction.com/wp-content/uploads/2019/01/soccer-ball.png"><span>Soccer</span></li>
				</ul>
				<div id="matches" class="mobile-menu-tab-content current">
					<div class="white-box">
					    <h2>FEATURE MATCHES</h2>
						<?php echo do_shortcode('[header-notification tournaments="267:T20,266:ONE DAY,265:TEST"]');?>
						<br />
						<div class="text-center">
							<a href="https://cricdiction.com/matches/"><span class="fusion-button button-default button-small">View All</span></a>
						</div>
					</div>
					<div class="ads-box">
						<img src="https://cricdiction.com/wp-content/uploads/2019/03/post-thumb-placeholder.jpg">
					</div>
				</div>
				<div id="experts" class="mobile-menu-tab-content">
					<div class="white-box">
						<div class='tabs tabs_default' id="TopPredictor">
							<ul class='horizontal'>
								<li class="proli"><a href="#match">Match Experts</a></li>
								<li class="proli"><a href="#toss">Toss Experts</a></li>
							</ul>
							<div  id="match">
								<div class="item">
									<?php echo do_shortcode('[matchTop avatar=1]');?>
								</div>
							</div>
							<div  id="toss">
								<div class="item">
									<?php echo do_shortcode('[tossTop avatar=1]');?>
								</div>
							</div>
						</div>
						<br />
						<div class="text-center">
							<a href="https://cricdiction.com/expert-predictors-match/"><span class="fusion-button button-default button-small">View All</span></a>
						</div>
					</div>
				</div>
				<div id="disqus" class="mobile-menu-tab-content">
					Disqus Under Construction
				</div>
				<div id="soccer" class="mobile-menu-tab-content">
					Soccer Under Construction
				</div>
			</div>
		</div>
		<div class="md-overlay"></div> -->
	</body>
</html>
