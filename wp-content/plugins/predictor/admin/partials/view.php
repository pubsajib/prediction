<?php 
// $names = PredictionCron::insertIntoRankinSummeryTable(); help($names);
// $names = PredictionCron::createRatingSummeryTable(); help($names);
// $ratingTypes = array_filter(array_map(function ($name) {return $name->rate_table != 'summery' ? $name->rate_table : false;}, $names));
// help(PredictionCron::resetRankings($ratingTypes));
// help(PredictionCron::getAllPredictors());
// help(PredictionCron::insertIntoRankinSummeryTable($ratingTypes));
// CREATING TABLES
// $type = 'all'; PredictionCron::deleteRatingTableFor($type); PredictionCron::createRatingTableFor($type); help(PredictionCron::rankingCronFor($type));
$names = cs_get_option('cron');
echo '<div class="wrap">';
    echo '<h1>'.$heading.' CRON</h1>';
    echo '<div class="msgWrapper"></div>';
    if ($names) {
        echo '<div class="half first">';
            echo '<table class="cronTable" border="1" style="border-collapse: collapse;width: 100%;">';
                echo '<thead> <tr> <th>Name</th> <th>Action</th> </tr> </thead>';
                echo '<tbody>';
                    foreach ($names as $name) {
                        if ($name['is_active']) {
                            echo '<tr id="'. $name['id'] .'">';
                                echo '<td style="padding: 0 5px;">'. $name['name'] .'</td> ';
                                echo '<td style="text-align:center;width:100px;padding:5px 0;"><button class="button button-primary cronBtn" cron="'. $name['id'] .'" type="button">Run</button></td>';
                            echo '</tr>';
                        }
                    }
                    echo '<tr id="summery" style="border:2px solid red;">';
                        echo '<td style="padding: 0 5px;"><strong>Summery</strong></td> ';
                        echo '<td style="text-align:center;width:100px;padding:5px 0;"><button class="button button-primary cronBtn" cron="summery" type="button">Run</button></td>';
                    echo '</tr>';
                echo '</tbody>';
            echo '</table>';
        echo '</div>';
        echo '<div class="half last">';
        echo '</div>';
            
    } else echo '<h4 style="color: red;">Not found</h4>';
    echo '<div class="clearfix"></div>';
echo '</div>';