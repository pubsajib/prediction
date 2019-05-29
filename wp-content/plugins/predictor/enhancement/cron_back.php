<?php 
/**
 * CRON
 * 
 * Params : all, match, toss, bpl, ipl, bbl(big bash legue)
 * PredictionCron::rankingCronFor('toss'); 
 */
class PredictionCron {
	static function config($type=false) {
		$config = [];
		// GENERAL
		$config['all'] 	 = ['tournamentID'=>false, 'type'=>'all', 'minItemToPredict'=>100, 'itemGrace'=>10, 'minParticipationRate'=>50];
		$config['match'] = ['tournamentID'=>false, 'type'=>'match', 'minItemToPredict'=>100, 'itemGrace'=>10, 'minParticipationRate'=>50];
		$config['toss']  = ['tournamentID'=>false, 'type'=>'toss', 'minItemToPredict'=>100, 'itemGrace'=>10, 'minParticipationRate'=>30];

		// TOURNAMENT
		$config['bpl'] 		= ['tournamentID'=>13, 'type'=>'all', 'minItemToPredict'=>10, 'itemGrace'=>0, 'minParticipationRate'=>80];
		$config['ipl'] 		= ['tournamentID'=>12, 'type'=>'all', 'minItemToPredict'=>10, 'itemGrace'=>0, 'minParticipationRate'=>80];
		$config['bpl'] 		 = ['tournamentID'=>266, 'type'=>'all', 'minItemToPredict'=>10, 'itemGrace'=>0, 'minParticipationRate'=>80];

		// $config['t_20'] = ['tournamentID'=>267, 'type'=>'match', 'minItemToPredict'=>100, 'itemGrace'=>0, 'minParticipationRate'=>80];
		// $config['odi']  = ['tournamentID'=>266, 'type'=>'match', 'minItemToPredict'=>50, 'itemGrace'=>0, 'minParticipationRate'=>80];
		// $config['test']       = ['tournamentID'=>265, 'type'=>'match', 'minItemToPredict'=>5, 'itemGrace'=>0, 'minParticipationRate'=>30];
		// $config['ipl']       = ['tournamentID'=>313, 'type'=>'match', 'minItemToPredict'=>10, 'itemGrace'=>0, 'minParticipationRate'=>80];
		// // TOSS
		// $config['t20_toss'] = ['tournamentID'=>267, 'type'=>'toss', 'minItemToPredict'=>100, 'itemGrace'=>0, 'minParticipationRate'=>80];
		// $config['odi_toss']  = ['tournamentID'=>266, 'type'=>'toss', 'minItemToPredict'=>50, 'itemGrace'=>0, 'minParticipationRate'=>80];
		// $config['test_toss']  = ['tournamentID'=>265, 'type'=>'toss', 'minItemToPredict'=>5, 'itemGrace'=>0, 'minParticipationRate'=>30];
		// $config['ipl_toss']       = ['tournamentID'=>313, 'type'=>'toss', 'minItemToPredict'=>10, 'itemGrace'=>0, 'minParticipationRate'=>50];

		if ($type) {
			if (!empty($config[$type])) return $config[$type];
			else return [];
		} else return $config;
	}
	static function cronTypes() {
		global $wpdb;
		$tableName = $wpdb->prefix ."predictor_cron_status";
		$types = array_keys(self::config());
		$types[] = 'summery';
		if ($types) {
			$names = '';
			foreach ($types as $type) {
				$names .= "'". $type ."',";
			}
			$data  = "SELECT *  FROM `". $tableName ."`;";
			$data = $wpdb->get_results($data);
			return $data;
		}
		return $types;
	}
	static function rankingCronFor($rankingType) {
		if ($param = self::config($rankingType)) {
			$type 					= !empty($param['type']) ? $param['type'] : false; 
			$tournamentID 			= !empty($param['tournamentID']) ? $param['tournamentID'] : false; 
			$minItemToPredict 		= !empty($param['minItemToPredict']) ? $param['minItemToPredict'] : 0; 
			$itemGrace 				= !empty($param['itemGrace']) ? $param['itemGrace'] : 0; 
			$minParticipationRate 	= !empty($param['minParticipationRate']) ? $param['minParticipationRate'] : 0; 
			$predictors  = self::getRakingFor($type, $tournamentID, $minItemToPredict, $itemGrace, $minParticipationRate);
			return self::inserIntoDB($rankingType, $predictors);
		}
		return false;
	}
	static function inserIntoDB($rankingType, $predictors) {
		global $wpdb;
		$tableName   = $wpdb->prefix.'predictor_rating_'.$rankingType;
		if ($predictors) {
			$insert = '';
			$insert .= 'INSERT INTO `'. $tableName .'` (`id`, `user_id`, `accuracy`, `win`, `lose`, `abandon`, `life_time_events`, `participated_events`, `participation_rate`, `min_participation_rate`, `min_participation_event`, `eligibility`, `login`, `name`, `url`, `avatar`, `country`, `description`, `likes`) VALUES ';
			foreach ($predictors as $predictorRank => $predictor) {
				// $insert .= "<br>";
				$eligible 					= !empty($predictor['eligible']) ? $predictor['eligible'] : 0;
				$correct 					= !empty($predictor['correct']) ? $predictor['correct'] : 0;
				$incorrect 					= !empty($predictor['incorrect']) ? $predictor['incorrect'] : 0;
				$abandon 					= !empty($predictor['abandon']) ? $predictor['abandon'] : 0;
				$score 						= !empty($predictor['score']) ? $predictor['score'] : 0;
				$participated 				= !empty($predictor['participated']) ? $predictor['participated'] : 0;
				$lifeTimePublishedEvents 	= !empty($predictor['lifeTimePublishedEvents']) ? $predictor['lifeTimePublishedEvents'] : 0;
				$lifeTimePublishedEventRate = !empty($predictor['lifeTimePublishedEventRate']) ? $predictor['lifeTimePublishedEventRate'] : 0;
				$minLifetimeParticipation 	= !empty($predictor['minLifetimeParticipation']) ? $predictor['minLifetimeParticipation'] : 0;
				$minLifetimeParticipationRate = !empty($predictor['minLifetimeParticipationRate']) ? $predictor['minLifetimeParticipationRate'] : 0;
				$login 						= !empty($predictor['login']) ? "'".$predictor['login']."'" : "''";
				$name 						= !empty($predictor['name']) ? "'".$predictor['name']."'" : "''";
				$url 						= !empty($predictor['url']) ? "'".$predictor['url']."'" : "''";
				$avatar 					= !empty($predictor['avatar']) ? "'".$predictor['avatar']."'" : "''";
				$country 					= !empty($predictor['country']) ? "'".$predictor['country']."'" : "''";
				$description 				= !empty($predictor['description']) ? "'".$predictor['description']."'" : "''";
				$likes 						= !empty($predictor['likes']) ? "'".$predictor['likes']."'" : "''";

				$insert .= "(NULL, {$predictor['id']}, {$score}, {$correct}, {$incorrect}, {$abandon}, {$lifeTimePublishedEvents}, {$participated}, {$lifeTimePublishedEventRate}, {$minLifetimeParticipationRate}, {$minLifetimeParticipation}, {$eligible}, {$login}, {$name}, {$url}, {$avatar}, {$country}, {$description}, {$likes}),";
			}
			$insert = rtrim($insert, ',').';';
			// return $insert;
			$truncate = "TRUNCATE ".$tableName;
			if ($wpdb->query($truncate) && $wpdb->query($insert)) { 
				$status = "UPDATE `". $wpdb->prefix ."predictor_cron_status` SET `status` = ". time() ." WHERE `rate_table` = '".$rankingType."';";
				$wpdb->query($status);
				return true;
			} else {
				$status = "UPDATE `". $wpdb->prefix ."predictor_cron_status` SET `status` = '0' WHERE `rate_table` = '".$rankingType."';";
				$wpdb->query($status);
				return false;
			}
		}
		return false;
	}
	static function insertIntoRankinSummeryTable(array $rankingTypes) {
		global $wpdb;
		if ($summeries = self::prepareRankingSummery($rankingTypes, $wpdb)) {
			ksort($summeries);
			// return $summeries;
			$ratingType = 'summery';
			$tableName = $wpdb->prefix.'predictor_rating_'.$ratingType;
			// return $summeries;
			$columns = array_map(function($item) { return "`". $item ."_rank`, `". $item ."_desc`";}, $rankingTypes);
			$types = array_map(function($item) { return $item;}, $rankingTypes);
			$sql = $insert = "";
			$sql .= "INSERT INTO `". $tableName ."` (`id`, `user_id`, ". implode(',', $columns) .", `login`, `name`, `url`, `avatar`, `country`, `description`, `created_at`, `updated_at`) VALUES ";
			foreach ($summeries as $uID => $summery) {
				// $sql .= "<br>";
				$sql .= "(NULL, ". $uID .", ";
				foreach ($types as $type) {
					$sql .= $summery[$type.'_rank'] .", ";
					$sql .= "'". $summery[$type.'_desc'] ."', ";
				}
				$sql .= "'". $summery['login'] ."', ";
				$sql .= "'". $summery['name'] ."', ";
				$sql .= "'". $summery['url'] ."', ";
				$sql .= "'". $summery['avatar'] ."', ";
				$sql .= "'". $summery['country'] ."', ";
				$sql .= "'". $summery['description'] ."', ";
				$sql .= " CURRENT_TIMESTAMP, CURRENT_TIMESTAMP), ";
			}
			$insert = rtrim($sql, ', '). ";";
			// return $insert;
			$truncate = "TRUNCATE ".$tableName;
			if ($wpdb->query($truncate) && $wpdb->query($insert)) { 
				$status = "UPDATE `". $wpdb->prefix ."predictor_cron_status` SET `status` = ". time() ." WHERE `rate_table` = '".$ratingType."';";
				$wpdb->query($status);
				return true;
			} else {
				$status = "UPDATE `". $wpdb->prefix ."predictor_cron_status` SET `status` = '0' WHERE `rate_table` = '".$ratingType."';";
				$wpdb->query($status);
				return false;
			}
		} else return false;
	}
	static function prepareRankingSummery(array $rankingTypes, $wpdb) {
		if (!$rankingTypes) return false;
		else {
			$psummery = [];
			$summery = [];
			foreach ($rankingTypes as $rankingType) {
				$users = [];
				$tableName   = $wpdb->prefix.'predictor_rating_'.$rankingType;
				$sql = "SELECT * FROM `". $tableName ."`";
				if ($ranks = $wpdb->get_results($sql)) {
					foreach ($ranks as $rank) {
						$users[$rank->user_id] = $rank;
						unset($users[$rank->user_id]->user_id);
					}
				}
				$summery[$rankingType] = $users;
			}
			foreach ($summery['all'] as $uID => $ps) {
				$psummery[$uID] = ['login'=>$ps->login, 'name'=>$ps->name, 'url'=>$ps->url, 'avatar'=>$ps->avatar, 'country'=>$ps->country, 'description'=>$ps->description, 'description'=>$ps->likes];
				foreach ($summery as $name => $values) {
					$rank = $summery[$name][$uID];
					$psummery[$uID][$name.'_rank'] = $rank->id;
					$psummery[$uID][$name.'_desc'] = json_encode(['accuracy'=>$rank->accuracy, 'win'=>$rank->win, 'lose'=>$rank->lose, 'participated'=>$rank->participated_events, 'eligibility'=>$rank->eligibility]);
				}
			}
			return $psummery;
		}
	}
	// RANKING
	// $ratingType = all, match, toss, bpl, ipl, ...
	static function getRakingFor($ratingType='all', $tournamentID=false, $minItemToPredict=80, $itemGrace=0, $minParticipationRate=10) {
		$ranking = [];
		$users = [];
		$minParticipationWithGrace = $minItemToPredict - $itemGrace;
		// RANKING FOR ALL USERS
		$predictors = self::getAllPredictors();
		if ($predictors) {
			foreach ($predictors as $predictor) {
				// LIFE TIME DATA
				if ($tournamentID) $prediction = self::tournamentData($predictor['id'], $tournamentID);
				else $prediction = self::predictionsOf($predictor['id']);
				$isRankAble = false;
				if (!empty($prediction['avg'])) {
					$participated 	= !empty($prediction['avg'][$ratingType]['participated']) 	? $prediction['avg'][$ratingType]['participated'] : 0;
					$PMatch 		= !empty($prediction['avg']['match']['rate']) 				? $prediction['avg']['match']['rate'] : 0;
					$PToss 			= !empty($prediction['avg']['toss']['rate']) 				? $prediction['avg']['toss']['rate'] : 0;
					$score 			= !empty($prediction['avg'][$ratingType]['rate']) 			? $prediction['avg'][$ratingType]['rate'] : 0;
					$correct 		= !empty($prediction['avg'][$ratingType]['correct']) 		? $prediction['avg'][$ratingType]['correct'] : 0;
					$incorrect 		= !empty($prediction['avg'][$ratingType]['incorrect']) 		? $prediction['avg'][$ratingType]['incorrect'] : 0;
					$abandon 		= !empty($prediction['avg'][$ratingType]['abandon']) 		? $prediction['avg'][$ratingType]['abandon'] : 0;
					$criterias = [
						'UID'=>$predictor['id'], 
						'participated' => $participated,
						'minLifetimeParticipationRate' => $minParticipationRate, 
						'accuracy' => $score,
						'grace' => $minParticipationWithGrace,
					];
					$lifeTimeEvents = count(self::lifeTimePublished($criterias['UID'], $ratingType, $tournamentID));
					if ($lifeTimeEvents) {
						$criterias['lifeTimePublishedEvents'] = $lifeTimeEvents;
						$criterias['lifeTimePublishedEventRate']  = number_format(($criterias['participated'] / $lifeTimeEvents) * 100, 2);
					} else {
						$criterias['lifeTimePublishedEvents'] = 0;
						$criterias['lifeTimePublishedEventRate'] = 0;
					}
					if ($participated) $isRankAble = self::isValidForRanking($criterias);
					$ranking[$predictor['id']]['id'] = $predictor['id'];
					$ranking[$predictor['id']]['eligible'] = $isRankAble;
					$ranking[$predictor['id']]['score'] = $score;
					$ranking[$predictor['id']]['matchAccuracy'] = $PMatch;
					$ranking[$predictor['id']]['tossAccuricy'] = $PToss;
					$ranking[$predictor['id']]['participated'] = $participated;
					$ranking[$predictor['id']]['correct'] = $correct;
					$ranking[$predictor['id']]['incorrect'] = $incorrect;
					$ranking[$predictor['id']]['abandon'] = $abandon;
					$ranking[$predictor['id']]['lifeTimePublishedEvents'] = $criterias['lifeTimePublishedEvents'];
					$ranking[$predictor['id']]['lifeTimePublishedEventRate'] = $criterias['lifeTimePublishedEventRate'];
					$ranking[$predictor['id']]['minLifetimeParticipationRate'] = $criterias['minLifetimeParticipationRate'];
					$ranking[$predictor['id']]['minLifetimeParticipation'] = $minParticipationWithGrace;

					$eligible_sort[] = $isRankAble;
					$accuracy_sort[] = $score;
					$matchParticipated_sort[] = $PMatch;
					$tossParticipated_sort[] = $PToss;
					$totalParticipated_sort[] = $participated;
				} else {
					$ranking[$predictor['id']]['id'] = $predictor['id'];
					$ranking[$predictor['id']]['eligible'] = 0;
					$ranking[$predictor['id']]['score'] = 0;
					$ranking[$predictor['id']]['matchAccuracy'] = 0;
					$ranking[$predictor['id']]['tossAccuricy'] = 0;
					$ranking[$predictor['id']]['participated'] = 0;
					$ranking[$predictor['id']]['correct'] = 0;
					$ranking[$predictor['id']]['incorrect'] = 0;
					$ranking[$predictor['id']]['abandon'] = 0;
					$ranking[$predictor['id']]['lifeTimePublishedEvents'] = 0;
					$ranking[$predictor['id']]['lifeTimePublishedEventRate'] = 0;
					$ranking[$predictor['id']]['minLifetimeParticipationRate'] = 0;
					$ranking[$predictor['id']]['minLifetimeParticipation'] = $minParticipationWithGrace;

					$eligible_sort[] = -9999;
					$accuracy_sort[] = 0;
					$matchParticipated_sort[] = 0;
					$tossParticipated_sort[] = 0;
					$totalParticipated_sort[] = 0;
				}
				$ranking[$predictor['id']]['login'] = $predictor['login'];
				$ranking[$predictor['id']]['name'] = $predictor['nickname'];
				$ranking[$predictor['id']]['avatar'] = $predictor['avatar'];
				$ranking[$predictor['id']]['country'] = $predictor['country'];
				$ranking[$predictor['id']]['description'] = $predictor['description'];
				$ranking[$predictor['id']]['likes'] = $predictor['likes'];
				// $ranking[$predictor['id']]['test'] = $tournamentID;
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
	static function lifeTimePublished($userID, $type='all', $tournamentID=false) {
		$published = [];
		$udata = get_userdata($userID);
		$registered = $udata->user_registered;
		// $registered = '2019-01-02 20:17:00'; // YYYY-mm-dd
		$query = ['post_type' => 'event', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'date_query' => ['after' => $registered]];
		if ($tournamentID) {
			$query = array_merge($query, ['tax_query' => [['taxonomy' => 'tournament', 'field' => 'term_id', 'terms' => $tournamentID]]]);
		}
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
			                    if ($type == 'all') {
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
	    $prediction = ['wl' =>[]];
	    if (!$tournamentID) $events = self::getEventIDs();
	    else $events = self::eventsByTournament($tournamentID);
	    $eventAVG = self::defaultCriteriaValues();
	    $eveID = '';
	    foreach ($events as $eventID) {
	        $data = self::predictionFor($eventID, $userID);
	        if (!$data) continue;
	        $eventAVG = self::eventAVG($eventAVG, @$data['avg']);
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
	    $tparticipated = $tcorrect = $tincorrect = $twin = $tlose = 0;
	    if (empty($ans[$userID])) return [];
	    if (@$meta['teams']) {
	        foreach ($meta['teams'] as $team) {
	            $participated = $correct = $incorrect = $win = $lose = 0;
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
	                    
	                    
	                    if (!$givenAns) $data[$teamID][$answerID]['warning'] = 'Answer is not given.';
	                    else {
	                        if ($defaultAns == 'abandon') {
	                            $criteriaAvg = self::updateCriteriaAVGFor($criteriaAvg, $option['id'], 'abandon');
	                            $isCorrect = 'abandon';
	                        } else if ($defaultAns == $givenAns) {
	                            $criteriaAvg = self::updateCriteriaAVGFor($criteriaAvg, $option['id'], 1);
	                            $isCorrect = 1;
	                        } else{
	                            $isCorrect = 0;
	                            $criteriaAvg = self::updateCriteriaAVGFor($criteriaAvg, $option['id'], 0);
	                        }
	                        // FOR DEBUGING / SHOW
	                        $data[$teamID][$answerID]['question']   = $option['title'];
	                        $data[$teamID][$answerID]['default']    = $defaultAns;
	                        $data[$teamID][$answerID]['given']      = $givenAns;
	                        $data[$teamID][$answerID]['is_correct'] = $isCorrect;
	                        $winLose[] = ['event'=>$eventID, 'team' => $team['name'],'item'=> $option['title'], 'type'=> $option['id'], 'status'=>$isCorrect];
	                    }
	                }
	            }
	            $eventAvg = self::eventAVG($eventAvg, $criteriaAvg);
	            // AVG RESULTS BY QUESTIONS
	            $data[$teamID]['name']          = $team['name'];
	            // AVG FOR CRITERIAS DATA
	            $data[$teamID]['avg']           = $criteriaAvg;
	        }
	        $data['event']  = $eventID;
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
	static function updateCriteriaAVGFor($criteriaAvg, $criteria='', $isCorrect=false) {
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
	        } else {
	            $criteriaAvg['all']['incorrect']++;
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
	static function getAllPredictors() {
		global $wpdb;
        $predictors = [];
        $users = get_users('role=predictor');
        if ($users) {
            foreach ($users as $user) {
        		$meta = ['nickname'=>'','country'=>'','description'=>'','avatar'=>'','likes'=>0];
	            $sql = "SELECT umeta_id, user_id, meta_key,`meta_value` FROM $wpdb->usermeta WHERE `user_id`= {$user->ID} AND `meta_key` IN ('nickname','country', 'description', 'likes')";
	            $umetas = $wpdb->get_results( $sql );
	            $meta['avatar'] = get_avatar_url( $user->user_email, null );
	            if ($umetas) {
	                foreach ($umetas as $umeta) {
	                    $meta[$umeta->meta_key] = $umeta->meta_value;
	                }
	            }
                $predictors[$user->ID] = array_merge(['id'=>$user->ID,'login'=>$user->data->user_login,'url'=>$user->data->user_url], $meta);
            }
        }
        return $predictors;
	}
	// TABLES
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
	static function deleteRatingTableFor($ratingType){
		global $wpdb;
		$tableName = $wpdb->prefix.'predictor_rating_'.$ratingType;
		$drop = "DROP TABLE IF EXISTS ". $tableName;
		if ($wpdb->query($drop)) {
			$status = "DELETE FROM `". $wpdb->prefix ."predictor_cron_status` WHERE rate_table = '".$ratingType."'";
			return $wpdb->query($status);
		}
		return false;
	}
	static function createRatingTableFor($ratingType){
		global $wpdb;
		$tableName = $wpdb->prefix.'predictor_rating_'.$ratingType;
		$sql = "CREATE TABLE IF NOT EXISTS `".$tableName."` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL,
			`accuracy` int(11) NOT NULL,
			`win` int(11) NOT NULL,
			`lose` int(11) NOT NULL,
			`abandon` int(11) NOT NULL,
			`participated_events` int(11) NOT NULL,
			`life_time_events` int(11) NOT NULL,
			`participation_rate` int(11) NOT NULL,
			`min_participation_rate` int(11) NOT NULL,
			`min_participation_event` int(11) NOT NULL,
			`eligibility` int(11) NOT NULL,
			`login` varchar(32) DEFAULT NULL,
			`name` varchar(32) DEFAULT NULL,
			`url` varchar(128) DEFAULT NULL,
			`avatar` varchar(128) DEFAULT NULL,
			`country` varchar(10) DEFAULT NULL,
			`description` tinytext,
			`likes` int(11) DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `user_id` (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

		// return $sql;
		if ($wpdb->query($sql)) {
			$status = "INSERT INTO `". $wpdb->prefix."predictor_cron_status` (`rate_table`, `status`) VALUES ('".$ratingType."', '0');";
			return $wpdb->query($status);
		}
		return false;
	}
	static function createRatingSummeryTable(array $options=[]) {
		global $wpdb;
		$ratingType = 'summery';
		$tableName = $wpdb->prefix.'predictor_rating_'.$ratingType;
		// if (!$options) $options = array_merge(['all', 'match', 'toss'], (array) get_option('predictor_cron_options'));
		$options = array_merge(['all', 'match', 'toss'], $options);
		$rankColumns = '';
		$sql = "";
		// $sql .= "DROP TABLE IF EXISTS `". $tableName ."`;";
		$sql .= "CREATE TABLE IF NOT EXISTS `". $tableName ."` ( ";
		$sql .= "`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
		$sql .= "`user_id` int(11) NOT NULL, ";
		foreach ($options as $option) { $rankColumns .= "`". $option ."_rank`, "; $sql .= "`". $option ."_rank` int(11) NOT NULL, "; $sql .= "`". $option ."_desc` tinytext, ";}
		$sql .= "`login` varchar(32) DEFAULT NULL, ";
		$sql .= "`name` varchar(32) DEFAULT NULL, ";
		$sql .= "`url` varchar(128) DEFAULT NULL, ";
		$sql .= "`avatar` varchar(128) DEFAULT NULL, ";
		$sql .= "`country` varchar(10) DEFAULT NULL, ";
		$sql .= "`description` tinytext, ";
		$sql .= "`likes` int(11) DEFAULT '0', ";
		$sql .= "`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
		$sql .= "`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ";
		$sql .= "INDEX (". $rankColumns ."`user_id`) ";
		$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		// return $sql;
		self::deleteRatingTableFor($ratingType);
		if ($wpdb->query($sql)) {
			$status = "INSERT INTO `". $wpdb->prefix."predictor_cron_status` (`rate_table`, `status`) VALUES ('".$ratingType."', '0');";
			return $wpdb->query($status);
		} else return false;
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