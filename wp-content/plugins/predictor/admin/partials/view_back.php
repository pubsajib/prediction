<?php 
$names = PredictionCron::cronTypes();
// $ratingTypes = array_filter(array_map(function ($name) {return $name->rate_table != 'summery' ? $name->rate_table : false;}, $names));
// help(PredictionCron::resetRankings($ratingTypes));
// help(PredictionCron::getAllPredictors());
// help(PredictionCron::insertIntoRankinSummeryTable($ratingTypes));
// CREATING TABLES
// $type = 'all'; PredictionCron::deleteRatingTableFor($type); PredictionCron::createRatingTableFor($type); help(PredictionCron::rankingCronFor($type));
help(cs_get_option('cron'));
echo '<div class="wrap">';
    echo '<h1>'.$heading.' CRON</h1>';
    echo '<div class="msgWrapper"></div>';
    if ($names) {
        echo '<div class="half first">';
            echo '<table class="cronTable" border="1" style="border-collapse: collapse;width: 100%;">';
                echo '<thead> <tr> <th>Name</th> <th>Updated At</th> <th>Status</th> <th>Action</th> </tr> </thead>';
                echo '<tbody>';
                    foreach ($names as $name) {
                        $status = $name->status ? 'Success' : 'error';
                        $important = 'summery' == $name->rate_table ? ' style="border:2px solid red;"' : '';
                        echo '<tr id="'. $name->rate_table .'"'. $important .'>';
                            echo '<td style="padding: 0 5px;">'. strtoupper(str_replace('_', ' ', $name->rate_table)) .'</td> ';
                            echo '<td class="date" style="padding: 0 5px; text-align:center;">'. date('M d, Y h:i:s A', strtotime($name->updated_at)) .'</td> ';
                            echo '<td style="padding: 0 5px; text-align:center;">'. $status .'</td> ';
                            echo '<td style="text-align:center;width:100px;padding:5px 0;"><button class="button button-primary cronBtn" cron="'. $name->rate_table .'" type="button">Run</button></td>';
                        echo '</tr>';
                    }
                echo '</tbody>';
            echo '</table>';
        echo '</div>';
        echo '<div class="half last">';
            echo '<form class="tournamentList" method="post">';
                echo '<h3 class="m-0">Select working tournaments</h3><br>';
                foreach ($names as $name) {
                    if (in_array($name->rate_table, ['all', 'match', 'toss', 'summery'])) continue;
                    $disabled = in_array($name->rate_table, (array) get_option('predictor_cron_options')) ? 'checked' : '';
                    echo '<div class="inputItem"><label><input type="checkbox" name="tournament[]" value="'. $name->rate_table .'" class="tournament" '.$disabled.'> '. strtoupper(str_replace('_', ' ', $name->rate_table)) .' </label></div>';
                }
                echo '<br><button class="button button-primary tournamentListBtn" type="submit">Add</button>';
            echo '</form>';
        echo '</div>';
    } else echo '<h4 style="color: red;">Not found</h4>';
    echo '<div class="clearfix"></div>';
echo '</div>';