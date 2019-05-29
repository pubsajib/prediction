<?php 
// define( 'SHORTINIT', true );
require_once( __DIR__.'/../wp-load.php' );
require_once( __DIR__.'/config.php' );
// TABLES
// createCronStatusTable();
// createRatingTableFor($tournamentName);
// $rankingType = 'toss';
// rankingCronFor($rankingType);
/**
 * CRON
 */
class PredictionCron {
	static function config() {
		$config = [];
		// GENERAL
		$config['all'] 	 = ['tournamentID'=>false, 'minItemToPredict'=>100, 'itemGrace'=>10, 'minParticipationRate'=>50];
		$config['match'] = ['tournamentID'=>false, 'minItemToPredict'=>100, 'itemGrace'=>10, 'minParticipationRate'=>50];
		$config['toss']  = ['tournamentID'=>false, 'minItemToPredict'=>100, 'itemGrace'=>10, 'minParticipationRate'=>30];

		// TOURNAMENT
		$config['bpl'] 	= ['tournamentID'=>13, 'minItemToPredict'=>10, 'itemGrace'=>0, 'minParticipationRate'=>80];
		$config['ipl'] 	= ['tournamentID'=>12, 'minItemToPredict'=>10, 'itemGrace'=>0, 'minParticipationRate'=>80];
		$config['bbl'] 	= ['tournamentID'=>12, 'minItemToPredict'=>10, 'itemGrace'=>0, 'minParticipationRate'=>80];
		return $config;
	}
	static function rankingCronFor($rankingType) {
		if ($param = self::config($rankingType)) {
			help($param); wp_die();
			$predictors  = self::getRakingFor($rankingType, $param['tournamentID'], $param['minItemToPredict'], $param['itemGrace'], $param['minParticipationRate']); 
			self::inserIntoDB($rankingType, $predictors);
		}
	}
	static function inserIntoDB($rankingType, $predictors) {
		global $wpdb;
		$tableName   = $wpdb->prefix.'predictor_rating_'.$rankingType;
		if ($predictors) {
			$insert = '';
			$insert .= 'INSERT INTO `'. $tableName .'` (`id`, `user_id`, `accuracy`, `win`, `lose`, `life_time_events`, `participated_events`, `participation_rate`, `min_participation_rate`, `min_participation_event`, `eligibility`) VALUES ';
			foreach ($predictors as $predictorRank => $predictor) {
				// $insert .= "<br>";
				$eligible 					= !empty($predictor['eligible']) ? $predictor['eligible'] : 0;
				$correct 					= !empty($predictor['correct']) ? $predictor['correct'] : 0;
				$incorrect 					= !empty($predictor['incorrect']) ? $predictor['incorrect'] : 0;
				$score 						= !empty($predictor['score']) ? $predictor['score'] : 0;
				$participated 				= !empty($predictor['participated']) ? $predictor['participated'] : 0;
				$lifeTimePublishedEvents 	= !empty($predictor['lifeTimePublishedEvents']) ? $predictor['lifeTimePublishedEvents'] : 0;
				$lifeTimePublishedEventRate = !empty($predictor['lifeTimePublishedEventRate']) ? $predictor['lifeTimePublishedEventRate'] : 0;
				$minLifetimeParticipation 	= !empty($predictor['minLifetimeParticipation']) ? $predictor['minLifetimeParticipation'] : 0;
				$minLifetimeParticipationRate = !empty($predictor['minLifetimeParticipationRate']) ? $predictor['minLifetimeParticipationRate'] : 0;

				$insert .= "(NULL, {$predictor['id']}, {$correct}, {$incorrect}, {$score}, {$eligible}, {$lifeTimePublishedEvents}, {$participated}, {$lifeTimePublishedEventRate}, {$minLifetimeParticipationRate}, {$minLifetimeParticipation}),";
			}
			$insert = rtrim($insert, ',').';';
			// wp_die($insert);
			$truncate = "TRUNCATE ".$tableName;
			if ($wpdb->query($truncate) && $wpdb->query($insert)) { 
				$status = "INSERT INTO `wp_predictor_cron_status` (`rate_table`, `status`) VALUES ('".$rankingType."', '1');";
				$wpdb->query($status);
			} else {
				$status = "INSERT INTO `wp_predictor_cron_status` (`rate_table`, `status`) VALUES ('".$rankingType."', '0');";
				$wpdb->query($status);
			}
		}
	}
	// RANKING
	// $ratingType = all, match, toss, bpl, ipl, ...
	static function getRakingFor($ratingType='all', $tournamentID=false, $minItemToPredict=80, $itemGrace=0, $minParticipationRate=10) {
		$ranking = [];
		$users = [];
		$minParticipationWithGrace = $minItemToPredict - $itemGrace;
		// RANKING FOR ALL USERS
		$predictors = get_users('role=predictor');
		if ($predictors) {
			foreach ($predictors as $predictor) {
				// LIFE TIME DATA
				if ($tournamentID) $prediction = self::tournamentData($predictor->ID, $tournamentID);
				else $prediction = self::predictionsOf($predictor->ID);
				// help($prediction['avg']['all']);
				$isRankAble = false;
				if (!empty($prediction['avg'])) {
					$participated = $prediction['avg'][$ratingType]['participated'];
					$PMatch = $prediction['avg']['match']['rate'];
					$PToss = $prediction['avg']['toss']['rate'];
					$score = $prediction['avg'][$ratingType]['rate'];
					$correct = $prediction['avg'][$ratingType]['correct'];
					$incorrect = $prediction['avg'][$ratingType]['incorrect'];
					$criterias = [
						'UID'=>$predictor->ID, 
						'participated' => $participated,
						'minLifetimeParticipationRate' => $minParticipationRate, 
						'accuracy' => $score,
						'grace' => $minParticipationWithGrace,
					];
					$lifeTimeEvents = count(self::lifeTimePublished($criterias['UID']));
					if ($lifeTimeEvents) {
						$criterias['lifeTimePublishedEvents'] = $lifeTimeEvents;
						$criterias['lifeTimePublishedEventRate']  = number_format(($criterias['participated'] / $lifeTimeEvents) * 100, 2);
					} else {
						$criterias['lifeTimePublishedEvents'] = 0;
						$criterias['lifeTimePublishedEventRate'] = 0;
					}
					if ($participated) $isRankAble = self::isValidForRanking($criterias);
					$ranking[$predictor->ID]['id'] = $predictor->ID;
					$ranking[$predictor->ID]['eligible'] = $isRankAble;
					$ranking[$predictor->ID]['score'] = $score;
					$ranking[$predictor->ID]['matchAccuracy'] = $PMatch;
					$ranking[$predictor->ID]['tossAccuricy'] = $PToss;
					$ranking[$predictor->ID]['participated'] = $participated;
					$ranking[$predictor->ID]['correct'] = $correct;
					$ranking[$predictor->ID]['incorrect'] = $incorrect;
					$ranking[$predictor->ID]['lifeTimePublishedEvents'] = $criterias['lifeTimePublishedEvents'];
					$ranking[$predictor->ID]['lifeTimePublishedEventRate'] = $criterias['lifeTimePublishedEventRate'];
					$ranking[$predictor->ID]['minLifetimeParticipationRate'] = $criterias['minLifetimeParticipationRate'];
					$ranking[$predictor->ID]['minLifetimeParticipation'] = $minParticipationWithGrace;

					$eligible_sort[] = $isRankAble;
					$accuracy_sort[] = $score;
					$matchParticipated_sort[] = $PMatch;
					$tossParticipated_sort[] = $PToss;
					$totalParticipated_sort[] = $participated;
				} else {
					$ranking[$predictor->ID]['id'] = $predictor->ID;
					$ranking[$predictor->ID]['eligible'] = 0;
					$ranking[$predictor->ID]['score'] = 0;
					$ranking[$predictor->ID]['matchAccuracy'] = 0;
					$ranking[$predictor->ID]['tossAccuricy'] = 0;
					$ranking[$predictor->ID]['participated'] = 0;
					$ranking[$predictor->ID]['correct'] = 0;
					$ranking[$predictor->ID]['incorrect'] = 0;
					$ranking[$predictor->ID]['lifeTimePublishedEvents'] = 0;
					$ranking[$predictor->ID]['lifeTimePublishedEventRate'] = 0;
					$ranking[$predictor->ID]['minLifetimeParticipationRate'] = 0;
					$ranking[$predictor->ID]['minLifetimeParticipation'] = $minParticipationWithGrace;

					$eligible_sort[] = -9999;
					$accuracy_sort[] = 0;
					$matchParticipated_sort[] = 0;
					$tossParticipated_sort[] = 0;
					$totalParticipated_sort[] = 0;
				}
				$users[$predictor->ID] = $predictor->data;
			}
			// TEST DATA
			// $ranking[3]['id'] = 3;
			// $ranking[3]['score'] = 8235;
			// $ranking[3]['participated'] = 17;
			// $ranking[3]['match'] = 16;
			// $scoreData[] = 8235;
			// $PRType[] = 17;
			// $matchParticipated[] = 16;
			if (isset($eligible_sort) || isset($accuracy_sort) || isset($matchParticipated_sort) || isset($tossParticipated_sort) || isset($totalParticipated_sort)) {
				array_multisort(
					$eligible_sort, SORT_DESC, 
					$accuracy_sort, SORT_DESC, 
					$matchParticipated_sort, SORT_DESC, 
					$tossParticipated_sort, SORT_DESC, 
					$totalParticipated_sort, SORT_DESC, 
					$ranking
				);
			}
		}
		return $ranking;
	}
	static function isValidForRanking($criterias) {
		$lifeTimeParticipationCriteria = $criterias['minLifetimeParticipationRate'] > $criterias['lifeTimePublishedEventRate'];
		if (!empty($criterias['grace'])) {
			for ($i=0; $i < $criterias['grace']; $i+=10) { 
				if ($criterias['participated'] < $i) return round($criterias['participated']/10);
			}
		}
		if ($criterias['accuracy'] < 50) return 80;

		// ACTUAL RANKING BEGAIN
		else if ($criterias['grace'] > $criterias['participated']) return 85;
		else if ($lifeTimeParticipationCriteria) {
			// if ($criterias['participated'] < 80) return 95;
			return 90;
		}
		else return 100;
	}
	static function lifeTimePublished($userID, $type=false) {
		$published = [];
		$udata = get_userdata($userID);
		$registered = $udata->user_registered;
		// $registered = '2019-01-02 20:17:00'; // YYYY-mm-dd
		$query = array(
	        'post_type' => 'event',
	        'post_status' => 'publish',
	        'posts_per_page' => -1,
	        'fields' => 'ids',
	        'date_query' => ['after' => $registered],
	    );
	    $events = new WP_Query($query);
	    // $events = $events->found_posts;
	    $events = $events->posts;
	    if ($events) {
			foreach ($events as $eventID) {
				$meta  = get_post_meta($eventID, 'event_ops', true);
				if (!empty($meta['teams'])) {
	        		foreach ($meta['teams'] as $team) {
	        			$ID     = self::predictor_id_from_string($team['name']);
	            		$teamID = 'team_'. $ID;

	            		if (!empty($meta[$teamID])) {
	            			foreach ($meta[$teamID] as $option) {
	            				$optionID = self::predictor_id_from_string($option['title']);
			                    $defaultID = 'default_'. $ID .'_'. $optionID;
			                    if (empty($meta[$defaultID.'_published'])) continue;
			                    if (!$type) {
			                    	$published[] = ['event' => $eventID, 'team' => $team['name'], 'item' => $option['title'], 'type' => $option['id']];
			                    } else if($type == $option['id']){
			                    	$published[] = ['event' => $eventID, 'team' => $team['name'], 'item' => $option['title'], 'type' => $option['id']];
			                    }
	            			}
	            		}
	        		}
	        	}
			}
		}
	    return $published;
	}
	static function totalPublished($type=false) {
		$published = [];
		$query = ['post_type' => 'event', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids'];
	    $events = new WP_Query($query);
	    // $events = $events->found_posts;
	    $events = $events->posts;
	    if ($events) {
			foreach ($events as $eventID) {
				$meta  = get_post_meta($eventID, 'event_ops', true);
				if (!empty($meta['teams'])) {
	        		foreach ($meta['teams'] as $team) {
	        			$ID     = self::predictor_id_from_string($team['name']);
	            		$teamID = 'team_'. $ID;

	            		if (!empty($meta[$teamID])) {
	            			foreach ($meta[$teamID] as $option) {
	            				$optionID = self::predictor_id_from_string($option['title']);
			                    $defaultID = 'default_'. $ID .'_'. $optionID;
			                    if (empty($meta[$defaultID.'_published'])) continue;
			                    if (!$type) {
			                    	$published[] = ['event' => $eventID, 'team' => $team['name'], 'item' => $option['title'], 'type' => $option['id']];
			                    } else if($type == $option['id']){
			                    	$published[] = ['event' => $eventID, 'team' => $team['name'], 'item' => $option['title'], 'type' => $option['id']];
			                    }
	            			}
	            		}
	        		}
	        	}
			}
		}
	    return $published;
	}
	static function getPredictorsList() {
		$users = [];
		$predictors = get_users( 'role=predictor' );
		if ($predictors) {
			foreach ($predictors as $predictor) {
				$users[$predictor->ID] = $predictor;
			}
		}
		return $users;
	}
	// PROFILE
	static function predictionsOf($userID=1, $tournamentID='') {
	    $prediction = ['gain'  => 0, 'wl' =>[]];
	    if (!$tournamentID) $events = self::getEventIDs();
	    else $events = self::eventsByTournament($tournamentID);
	    $eventAVG = self::defaultCriteriaValues();
	    $eveID = '';
	    foreach ($events as $eventID) {
	        $data = self::predictionFor($eventID, $userID);
	        if (!$data) continue;
	        $eventAVG = self::eventAVG($eventAVG, @$data['avg']);
	        $prediction['gain'] += !empty($data['gain']) ? $data['gain'] : 0;
	        $prediction['avg']  = $eventAVG;
	        $prediction['wl']   = array_merge($prediction['wl'], $data['wl']);
	        // echo '<br>'. $eventID .'<pre>'. print_r($data, true) .'</pre>';
	    }
	    // echo '<br><pre>'. print_r($prediction, true) .'</pre>';
	    return $prediction;
	}
	static function predictionFor($eventID, $userID) {
		$meta  = get_post_meta($eventID, 'event_ops', true);
		$ans   = get_post_meta($eventID, 'event_ans', true);
	    $data = [];
	    $winLose = [];
	    $eventAvg = self::defaultCriteriaValues();
	    $tgain = $tparticipated = $tcorrect = $tincorrect = $twin = $tlose = 0;
	    if (empty($ans[$userID])) return [];
	    if (@$meta['teams']) {
	        foreach ($meta['teams'] as $team) {
	            $gain   = $participated = $correct = $incorrect = $win = $lose = 0;
	            $criteriaAvg = self::defaultCriteriaValues();
	            $ID     = self::predictor_id_from_string($team['name']);
	            $teamID = 'team_'. $ID;

	            // OPTIONS
	            if ($meta[$teamID]) {
	                foreach ($meta[$teamID] as $option) {
	                    $optionID = self::predictor_id_from_string($option['title']);
	                    $defaultID = 'default_'. $ID .'_'. $optionID;
	                    if (empty($meta[$defaultID.'_published'])) continue;
	                    $isCorrect = null;
	                    $defaultAns = @$meta[$defaultID];

	                    $answerID = $teamID .'_'. $optionID;
	                    $givenAns = !empty($ans[$userID][$answerID]) ? $ans[$userID][$answerID] : [];
	                    $dWeight = self::getDefaultWeight($option['weight'], $defaultAns);
	                    $dWeight = $dWeight ? $dWeight : 0;
	                    
	                    if (!$givenAns) $data[$teamID][$answerID]['warning'] = 'Answer is not given.';
	                    else {
	                        if ($defaultAns == 'abandon') {
	                            $criteriaAvg = self::updateCriteriaAVGFor($criteriaAvg, $option['id'], $dWeight, 'abandon');
	                            $isCorrect = 'abandon';
	                            $gain += 0;
	                        } else if ($defaultAns == $givenAns) {
	                            $criteriaAvg = self::updateCriteriaAVGFor($criteriaAvg, $option['id'], $dWeight, 1);
	                            $isCorrect = 1;
	                            @$gain += $dWeight;
	                        } else{
	                            $isCorrect = 0;
	                            $criteriaAvg = self::updateCriteriaAVGFor($criteriaAvg, $option['id'], $dWeight, 0);
	                            @$gain -= $dWeight;
	                        }
	                        // FOR DEBUGING / SHOW
	                        $data[$teamID][$answerID]['question']   = $option['title'];
	                        $data[$teamID][$answerID]['weight']     = $dWeight;
	                        $data[$teamID][$answerID]['default']    = $defaultAns;
	                        $data[$teamID][$answerID]['given']      = $givenAns;
	                        $data[$teamID][$answerID]['is_correct'] = $isCorrect;
	                        $winLose[] = ['event'=>$eventID, 'team' => $team['name'],'item'=> $option['title'], 'type'=> $option['id'], 'status'=>$isCorrect];
	                    }
	                }
	            }
	            $eventAvg = self::eventAVG($eventAvg, $criteriaAvg);
	            $tgain          += @$gain;
	            // AVG RESULTS BY QUESTIONS
	            $data[$teamID]['name']          = $team['name'];
	            $data[$teamID]['gain']          = $gain;
	            // AVG FOR CRITERIAS DATA
	            $data[$teamID]['avg']           = $criteriaAvg;
	        }
	        $data['event']  = $eventID;
	        $data['gain']   = $tgain;
	        $data['avg']    = $eventAvg;
	        $data['wl']     = $winLose;
	    }
	    return $data;
	}
	static function getDefaultWeight($weights, $defaultAns) {
	    if ($weights) {
	        foreach ($weights as $weight) {
	            if (!$weight['name']) continue;
	            if ($weight['name'] == $defaultAns) return $weight['value'];
	        }
	    }
	    return 0;
	}
	static function getEventIDs() {
	    $query = ['post_type' => 'event', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids'];
	    $events = new WP_Query($query);
	    $events = $events->posts;
	    return $events;
	}
	static function updateCriteriaAVGFor($criteriaAvg, $criteria='', $weight=0, $isCorrect=false) {
	    if ($criteria) {
	        // CRITERIA
	        $criteriaID = self::predictor_id_from_string($criteria);
	        $criteriaAvg[$criteriaID]['participated']++;
	        if ($isCorrect === 'abandon') {$criteriaAvg[$criteriaID]['abandon']++;}
	        elseif ($isCorrect == 1) $criteriaAvg[$criteriaID]['correct']++;
	        else $criteriaAvg[$criteriaID]['incorrect']++;
	        // ALL
	        $criteriaAvg['all']['participated']++;
	        if ($isCorrect === 'abandon') {
	            $criteriaAvg['all']['abandon']++; 
	        } elseif ($isCorrect == 1) {
	            $criteriaAvg['all']['correct']++;
	            $criteriaAvg['all']['win'] = $weight;
	        } else {
	            $criteriaAvg['all']['incorrect']++;
	            $criteriaAvg['all']['lose'] = $weight;
	        }
	    }
	    return $criteriaAvg;
	}
	static function defaultCriteriaValues() {
	    $data = [];
	    // ALL
	    $data['all']['participated'] = 0;
	    $data['all']['correct'] = 0;
	    $data['all']['incorrect'] = 0;
	    $data['all']['win'] = 0;
	    $data['all']['lose'] = 0;
	    $data['all']['tweight'] = 0;
	    $data['all']['rate'] = 0;
	    $data['all']['abandon'] = 0;
	    // CRITERIAS
	    $criterias = cs_get_option('criteria_event');
	    if ($criterias) {
	        foreach ($criterias as $criteria) {
	            $criteriaID = self::predictor_id_from_string($criteria['name']);
	            $data[$criteriaID]['participated'] = 0;
	            $data[$criteriaID]['correct'] = 0;
	            $data[$criteriaID]['incorrect'] = 0;
	            $data[$criteriaID]['rate'] = 0;
	            $data[$criteriaID]['abandon'] = 0;
	        }
	    }
	    return $data;
	}
	static function eventAVG($eventAvg, $criteriaAvg) {
	    if ($eventAvg) {
	        foreach ($eventAvg as $criteriaName => $criteriaValues) {
	            if ($criteriaValues) {
	                foreach ($criteriaValues as $key => $value) {
	                    $eventAvg[$criteriaName][$key] += $criteriaAvg[$criteriaName][$key] ? $criteriaAvg[$criteriaName][$key] : 0;
	                }
	            }
	            // RATE BY WIN
	            if ($eventAvg[$criteriaName]['participated']) {
	                $totalEven = $eventAvg[$criteriaName]['participated'] - $eventAvg[$criteriaName]['abandon'];
	                if ($totalEven > 0) $rating = ($eventAvg[$criteriaName]['correct'] / $totalEven) * 100;
	                else $rating = 0;
	                $eventAvg[$criteriaName]['rate'] = number_format((float)$rating, 2, '.', '');
	            }
	            if ($criteriaName == 'all') {
	                // RATE BY WEIGHT
	                $eventAvg[$criteriaName]['tweight'] = $eventAvg[$criteriaName]['win'] + $eventAvg[$criteriaName]['lose'];
	            }
	        }
	    }
	    return $eventAvg;
	}
	static function tournamentData($userID=1, $tournamentID=4) {
		$data = self::predictionsOf($userID, $tournamentID);
		return $data;
	}
	static function predictor_id_from_string($string): string{
	    $string = str_replace(['#', '[', '(', ')', '-', '+', '/', ']', ' ', '?', '\''], '_', strtolower(trim($string)));
	    $string = str_replace(['&'], 'sand', $string);
	    return $string;
	}
	// TABLES
	static function createRatingTableFor($ratingType){
		global $wpdb;
		$tableName = $wpdb->prefix.'predictor_rating_'.$ratingType;
		$sql = "CREATE TABLE IF NOT EXISTS `".$tableName."` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL,
			`accuracy` int(11) NOT NULL,
			`win` int(11) NOT NULL,
			`lose` int(11) NOT NULL,
			`participated_events` int(11) NOT NULL,
			`life_time_events` int(11) NOT NULL,
			`participation_rate` int(11) NOT NULL,
			`min_participation_rate` int(11) NOT NULL,
			`min_participation_event` int(11) NOT NULL,
			`eligibility` int(11) NOT NULL,
			PRIMARY KEY (`id`),
			KEY `user_id` (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		return $wpdb->query($sql);
	}
	static function createCronStatusTable() {
		global $wpdb;
		$tableName = $wpdb->prefix.'predictor_cron_status';
		$sql = "CREATE TABLE IF NOT EXISTS `". $tableName ."` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`rate_table` varchar(30) NOT NULL,
			`status` tinyint(1) NOT NULL,
			`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		return $wpdb->query($sql);
	}
	// TOURNAMENT
	static function eventsByTournament($tournamentID=4) {
		$args = [
			'post_type' => 'event',
			'fields' => 'ids',
			'posts_per_page' => -1,
			'tax_query' => [['taxonomy' => 'tournament', 'field' => 'term_id', 'terms' => $tournamentID]]
		];
		$query = new WP_Query( $args );
		return $query->posts;
	}
}