<?php
/* Template Name: Road To Top 2*/
get_header();
$user = wp_get_current_user();
?>
<!-- User Info -->
<div class="author-profile-card">
	<div class="profile-info"> <?php profileInfo($user); ?> </div>
</div>

<?php
RoadToTop();
$items = profileEvents();
$unpublishedItems = array_filter($items, function($item) {
	return !$item['item']['published'];
});
$publishedItems = array_filter($items, function($item) {
	return $item['item']['published'];
});
?>
<div class="tabs tabs_default" id="Roadtotop">
	<ul class="horizontal">
		<li class="proli"><a href="#recent">Recent</a></li>
		<li class="proli"><a href="#completed">Completed</a></li>
	</ul>
	<div  id="recent">
		<div class="event-item">
			<div class="event-title"><span class="event-name"><a href="#">BPL, Chittagong Vikings vs Comilla Victorians and Rajshahi Kings vs Rangpur Riders. BPL Match Prediction on Jan 29, 2019</a></span> </div>
			<small class="info"><a href="#">BANGLADESH PREMIER LEAGUE</a>, <span class="date">29-01-2019 18:30:00 PM</span></small>
			<div class="row">
				<div class="col-sm-6 items">
					<a href="#">
						<div class="event-match">	
							<p>Rajshahi Kings vs Rangpur Riders</p>
							<div class="event-predict">
								<span class="toss"><strong>Toss: </strong>N/A</span>
								<span class="match"><strong>Match: </strong>N/A</span>
							</div>
						</div>
					</a>
				</div>
				<div class="col-sm-6 items">
					<a href="#">
						<div class="event-match">	
							<p>Rajshahi Kings vs Rangpur Riders</p>
							<div class="event-predict">
								<span class="toss"><strong>Toss: </strong>N/A</span>
								<span class="match"><strong>Match: </strong>N/A</span>
							</div>
						</div>
					</a>
				</div>
			</div>
			<div class="footer"><a href="#" class="fusion-button button-default button-small">Predict Now</a></div>
		</div><!-- /Item one-->
		<div class="event-item">
			<div class="event-title"><span class="event-name"><a href="#">BPL, Chittagong Vikings vs Comilla Victorians and Rajshahi Kings vs Rangpur Riders. BPL Match Prediction on Jan 29, 2019</a></span> </div>
			<small class="info"><a href="#">BANGLADESH PREMIER LEAGUE</a>, <span class="date">29-01-2019 18:30:00 PM</span></small>
			<div class="row">
				<div class="col-sm-6 items">
					<a href="#">
						<div class="event-match">	
							<p>Rajshahi Kings vs Rangpur Riders</p>
							<div class="event-predict">
								<span class="toss"><strong>Toss: </strong>N/A</span>
								<span class="match"><strong>Match: </strong>N/A</span>
							</div>
						</div>
					</a>
				</div>
				<div class="col-sm-6 items">
					<a href="#">
						<div class="event-match">	
							<p>Rajshahi Kings vs Rangpur Riders</p>
							<div class="event-predict">
								<span class="toss"><strong>Toss: </strong>N/A</span>
								<span class="match"><strong>Match: </strong>N/A</span>
							</div>
						</div>
					</a>
				</div>
			</div>
			<div class="footer"><a href="#" class="fusion-button button-default button-small">Predict Now</a></div>
		</div><!-- /Item two-->
	</div>
	<div id="completed">
		<p>Under Construction</p>
	</div>
</div>

<?php
// $cat = get_the_terms(13355, 'tournament');
// echo '<br><pre>'. print_r(count($items), true) .'</pre>';
// echo '<br><pre>'. print_r(count($publishedItems), true) .'</pre>';
// echo '<br>unpublishedItems : '.count($unpublishedItems).'<pre>'. print_r($unpublishedItems, true) .'</pre>';

$events = $unpublishedItems;
if ($events) {
	$counter = 1;
	$data = '';
	$data = '<h3>Items ('. count($events) .')</h3>';
	$data .= '<table class="ubpublished">';
		$data .= '<thead>';
			$data .= '<tr>';
				$data .= '<th> # </th>';
				$data .= '<th> Event </th>';
				$data .= '<th> Category </th>';
				$data .= '<th> Team </th>';
				$data .= '<th> Options </th>';
				$data .= '<th> Status </th>';
				$data .= '<th> Date </th>';
			$data .= '</tr>';
		$data .= '</thead>';
		$data .= '<tbody>';
			foreach ($events as $item) {
				$class = $answerBtn = '';
				if ($item['item']['answerable']) {
					$class 	= ' answerable';
					$status = '<a href="'. $item['slug'] .'" class="btn">Participate</a>';
				} else {
					$status = $item['item']['status'];
				}
				$data .= '<tr class="'. $class .'">';
					$data .= '<td> '. $counter .' </td>';
					$data .= '<td> '. $item['event']['title'] .' </td>';
					$data .= '<td> '. $item['event']['cats'] .' </td>';
					$data .= '<td> '. $item['team']['title'] .' </td>';
					$data .= '<td> '. $item['item']['title'] .' </td>';
					$data .= '<td> '. $status .' </td>';
					$data .= '<td> '. $item['team']['time'] .' </td>';
				$data .= '</tr>';
				$counter++;
			}
		$data .= '</tbody>';
	$data .= '</table>';
	
	echo $data;
}
get_footer();