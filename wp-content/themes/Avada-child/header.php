<?php
/**
 * Header template.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
<!DOCTYPE html>
<html class="<?php avada_the_html_class(); ?>" <?php language_attributes(); ?>>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<?php Avada()->head->the_viewport(); ?>

	<?php wp_head(); ?>

	<?php $object_id = get_queried_object_id(); ?>
	<?php $c_page_id = Avada()->fusion_library->get_page_id(); ?>

	<script type="text/javascript">
		var doc = document.documentElement;
		doc.setAttribute('data-useragent', navigator.userAgent);
	</script>

	<?php
	/**
	 *
	 * The settings below are not sanitized.
	 * In order to be able to take advantage of this,
	 * a user would have to gain access to the database
	 * in which case this is the least on your worries.
	 */
	echo apply_filters( 'avada_google_analytics', Avada()->settings->get( 'google_analytics' ) ); // WPCS: XSS ok.
	echo apply_filters( 'avada_space_head', Avada()->settings->get( 'space_head' ) ); // WPCS: XSS ok.
	?>
</head>

<?php
$wrapper_class = ( is_page_template( 'blank.php' ) ) ? 'wrapper_blank' : '';

if ( 'modern' === Avada()->settings->get( 'mobile_menu_design' ) ) {
	$mobile_logo_pos = strtolower( Avada()->settings->get( 'logo_alignment' ) );
	if ( 'center' === strtolower( Avada()->settings->get( 'logo_alignment' ) ) ) {
		$mobile_logo_pos = 'left';
	}
}

?>
<body <?php body_class(); ?>>
	 
	<div class="ibs-full-modal-container" id="modal1">
  <div class="ibs-full-modal">
    <header class="ibs-modal-header">
      <h4 class="ibs-modal-title">Default Modal</h4>
      <span class="ibs-btn-close">&times;</span>
    </header>
    <div class="ibs-modal-body has-header has-footer">
		<div id="tabs" class="tabs">
				<nav>
					<ul>
						<li><a href="#section-1" class="icon-shop"><span>Shop</span></a></li>
						<li><a href="#section-2" class="icon-cup"><span>Drinks</span></a></li>
					</ul>
				</nav>
				<div class="content">
					<section id="section-1">
						<div class="mediabox">
							<h3>Sushi Gumbo Beetroot</h3>
							<p>Sushi gumbo beet greens corn soko endive gumbo gourd. Parsley shallot courgette tatsoi pea sprouts fava bean collard greens dandelion okra wakame tomato.</p>
						</div>
						<div class="mediabox">
							<h3>Pea Sprouts Fava Soup</h3>
							<p>Lotus root water spinach fennel kombu maize bamboo shoot green bean swiss chard seakale pumpkin onion chickpea gram corn pea.</p>
						</div>
						<div class="mediabox">
							<h3>Turnip Broccoli Sashimi</h3>
							<p>Nori grape silver beet broccoli kombu beet greens fava bean potato quandong celery. Bunya nuts black-eyed pea prairie turnip leek lentil turnip greens parsnip.</p>
						</div>
					</section>
					<section id="section-2">
						<div class="mediabox">
							<h3>Asparagus Cucumber Cake</h3>
							<p>Chickweed okra pea winter purslane coriander yarrow sweet pepper radish garlic brussels sprout groundnut summer purslane earthnut pea tomato spring onion azuki bean gourd. </p>
						</div>
						<div class="mediabox">
							<h3>Magis Kohlrabi Gourd</h3>
							<p>Salsify taro catsear garlic gram celery bitterleaf wattle seed collard greens nori. Grape wattle seed kombu beetroot horseradish carrot squash brussels sprout chard.</p>
						</div>
						<div class="mediabox">
							<h3>Ricebean Rutabaga</h3>
							<p>Celery quandong swiss chard chicory earthnut pea potato. Salsify taro catsear garlic gram celery bitterleaf wattle seed collard greens nori. </p>
						</div>
					</section>
				</div><!-- /content -->
			</div>
    </div>
    <div class="ibs-modal-footer">
		<p>Here you will find FREE cricket predictions on a wide range of games. Also covering Royal London One-Day Cup, One day and T20 matches. You will also find predictions for all the major 				international games, test matches, one day internationals and of course tips on the Cricket World cup. And you get free sessions call too!!!</p>
    </div>
  </div>
</div>
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'Avada' ); ?></a>
	<?php
	do_action( 'avada_before_body_content' );

	$boxed_side_header_right = false;
	$page_bg_layout = 'default';
	if ( $c_page_id && is_numeric( $c_page_id ) ) {
		$fpo_page_bg_layout = get_post_meta( $c_page_id, 'pyre_page_bg_layout', true );
		$page_bg_layout = ( $fpo_page_bg_layout ) ? $fpo_page_bg_layout : $page_bg_layout;
	}

	?>
	<?php if ( ( ( 'Boxed' === Avada()->settings->get( 'layout' ) && ( 'default' === $page_bg_layout || '' == $page_bg_layout ) ) || 'boxed' === $page_bg_layout ) && 'Top' != Avada()->settings->get( 'header_position' ) ) : ?>
		<div id="boxed-wrapper">
	<?php endif; ?>
	<?php if ( ( ( 'Boxed' === Avada()->settings->get( 'layout' ) && 'default' === $page_bg_layout ) || 'boxed' === $page_bg_layout ) && 'framed' === Avada()->settings->get( 'scroll_offset' ) ) : ?>
		<div class="fusion-sides-frame"></div>
	<?php endif; ?>
	<div id="wrapper" class="<?php echo esc_attr( $wrapper_class ); ?>">
		<div id="home" style="position:relative;top:-1px;"></div>
		<?php avada_header_template( 'Below', ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && ! ( class_exists( 'WooCommerce' ) && is_shop() ) ); ?>
		<?php if ( 'Left' === Avada()->settings->get( 'header_position' ) || 'Right' === Avada()->settings->get( 'header_position' ) ) : ?>
			<?php avada_side_header(); ?>
		<?php endif; ?>

		<?php avada_sliders_container(); ?>

		<?php avada_header_template( 'Above', ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && ! ( class_exists( 'WooCommerce' ) && is_shop() ) ); ?>

		<?php if ( has_action( 'avada_override_current_page_title_bar' ) ) : ?>
			<?php do_action( 'avada_override_current_page_title_bar', $c_page_id ); ?>
		<?php else : ?>
			<?php avada_current_page_title_bar( $c_page_id ); ?>
		<?php endif; ?>
		<?php do_action( 'avada_after_page_title_bar' ); ?>

		<?php
		$main_css   = '';
		$row_css    = '';
		$main_class = '';

		if ( apply_filters( 'fusion_is_hundred_percent_template', false, $c_page_id ) ) {
			$main_css = 'padding-left:0px;padding-right:0px;';
			$hundredp_padding = get_post_meta( $c_page_id, 'pyre_hundredp_padding', true );
			if ( Avada()->settings->get( 'hundredp_padding' ) && ! $hundredp_padding ) {
				$main_css = 'padding-left:' . Avada()->settings->get( 'hundredp_padding' ) . ';padding-right:' . Avada()->settings->get( 'hundredp_padding' );
			}
			if ( $hundredp_padding ) {
				$main_css = 'padding-left:' . $hundredp_padding . ';padding-right:' . $hundredp_padding;
			}
			$row_css    = 'max-width:100%;';
			$main_class = 'width-100';
		}
		do_action( 'avada_before_main_container' );
		?>
<!-- 		<div class="custom-menus">
			<button id="openBtn" class="btn btn-primary btn-lg">x</button>
		</div> -->
		<main id="main" role="main" class="clearfix <?php echo esc_attr( $main_class ); ?>" style="<?php echo esc_attr( $main_css ); ?>">
			<div class="fusion-row" style="<?php echo esc_attr( $row_css ); ?>">
