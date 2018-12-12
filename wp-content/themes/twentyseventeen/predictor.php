<?php
/* Template Name: Predictor */

get_header(); 
// Set the Current Author Variable $user
$user = (isset($_GET['p'])) ? get_userdatabylogin($_GET['p']) : [];
$prediction = predictionsOf($user->ID);
// echo '<br><pre>'. print_r($prediction, true) .'</pre>';
?>
     
<div class="author-profile-card">
	<div class="half p20 left">
		<h3><?php echo $user->display_name; ?></h3>
	    <div class="author-photo"> <?php echo get_avatar( $user->user_email , '120 '); ?> </div>
	    <?php if ($user->user_url): ?>
	    	
	    <p><strong>Website:</strong> <a href="<?php echo $user->user_url; ?>"><?php echo $user->user_url; ?></a><br />
	    <?php endif ?>
	    <?php if ($user->user_description): ?>
	    	
	    <strong>Bio:</strong> <?php echo $user->user_description; ?></p>
	    <?php endif ?>
	</div>
	<div class="half p20 right text-right summeryWrapper">
		<div class="summeryContainer accuracy"><div class="title">Accuracy : </div>
			<div class="value">
				<?php echo $prediction['accuracy']; ?>% <br>
				<progress value="<?php echo $prediction['accuracy']; ?>" max="100"></progress>
			</div>
		</div>
		<div class="summeryContainer participate"><div class="title">Participated: </div> <div class="value"> <?php echo $prediction['participated'] ?> </div> </div>
		<div class="summeryContainer correct"><div class="title">Right: </div> <div class="value"> <?php echo $prediction['correct'] ?> </div> </div>
		<div class="summeryContainer wrong"><div class="title">Wrong: </div> <div class="value"> <?php echo $prediction['incorrect'] ?> </div> </div>
	</div>
	<div class="clearfix"></div>
</div>
<div class="boxed">
	<h3>Participated List</h3>
	<table border="1">
		<tr>
			<th class="text-center">##</th>
			<th class="text-center">Event</th>
			<th class="text-center">Earned</th>
		</tr>
		<tr>
			<td class="text-center">1</td>
			<td>Question?</td>
			<td class="text-center">20</td>
		</tr>
		<tr>
			<td class="text-center">2</td>
			<td>Question2?</td>
			<td class="text-center">-10</td>
		</tr>
		<tr>
			<th class="text-center" colspan="2">Total</th>
			<th class="text-center">10</th>
		</tr>
	</table>
	<div class="clearfix"></div>
</div>

<?php get_footer();
