<?php 
/*
 * Follower
 */
class Follower {
	// followeeID = current user id
	// follows 	  = current user follows
	// followerID = who's goona follow
	static function followBtn($followerID=0, $followeeID=0, $follows=[]) {
		$html = '';
		if ($followeeID && $followerID && ($followeeID != $followerID)) {
			if (in_array($followerID, $follows)) {
				$html = '<button class="btn btn-md btn-primary unFollow" type="button" followee="'.$followeeID.'" follower="'.$followerID.'">Unfollow</button>';
			} else {
				$html = '<button class="btn btn-md btn-primary addFollower" type="button" followee="'.$followeeID.'" follower="'.$followerID.'">Follow</button>';
			}
		}
		return $html;
	}
	static function add($followerID, $followeeID) {
		global $wpdb;
		$tableName = $wpdb->prefix.'predictor_followers';
		if ($wpdb->insert( $tableName, ['followee_id'=>$followeeID, 'follower_id'=>$followerID] )) return 200;
		return 201;
	}
	static function remove($followerID, $followeeID) {
		global $wpdb;
		$tableName = $wpdb->prefix.'predictor_followers';
		if ($wpdb->delete( $tableName, ['followee_id'=>$followeeID, 'follower_id'=>$followerID] )) return 200;
		return 201;
	}
	static function statusChange($followerID, $followeeID) {}
	static function block($followerID, $followeeID) {}
	static function getFollowerIds($userID) {
		global $wpdb;
		$tableName = $wpdb->prefix.'predictor_followers';
		$sql = "SELECT *  FROM `".$tableName."` WHERE `followee_id` = ".$userID;
		$results = $wpdb->get_results($sql, ARRAY_A);
		return array_map(function($a){ return $a['follower_id']; }, $results);
	}
	static function getFollowers($userID) {
		$follows = self::getFollowerIds($userID);
		if ($follows) return self::selectUsers($follows);
		return [];
	}
	static function getFolloweesIds($userID) {
		global $wpdb;
		$tableName = $wpdb->prefix.'predictor_followers';
		$sql = "SELECT *  FROM `".$tableName."` WHERE `follower_id` = ".$userID;
		$results = $wpdb->get_results($sql, ARRAY_A);
		return array_map(function($a){ return $a['followee_id']; }, $results);
		return $results;
	}
	static function getFollowees($userID) {
		$followees = self::getFolloweesIds($userID);
		if ($followees) return self::selectUsers($followees);
		return [];
	}
	static function check($followerID, $followeeID) {
		global $wpdb;
		$tableName = $wpdb->prefix.'predictor_followers';
		$sql = "SELECT *  FROM `".$tableName."` WHERE `followee_id` = ".$followeeID." AND `follower_id` = ".$followerID;
		$wpdb->get_results($sql); 
		return $wpdb->num_rows;
	}
	static function createTable() {
		global $wpdb;
		$tableName = $wpdb->prefix.'predictor_followers';
		$sql = "";
		// $sql .= "DROP TABLE IF EXISTS `". $tableName ."`; ";
		$sql .= "CREATE TABLE IF NOT EXISTS`". $tableName ."` ( ";
			$sql .= "`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
			$sql .= "`followee_id` int(11) NOT NULL, ";
			$sql .= "`follower_id` int(11) NOT NULL, ";
			$sql .= "`status` tinyint(1) DEFAULT NULL, ";
			$sql .= "`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
			$sql .= "`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ";
			$sql .= "INDEX (`followee_id`,`follower_id`) ";
		$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1; ";
		// return $sql;
		return $wpdb->query($sql);
	}
	static function feed($userID) {
		$feeds = $users = $results = [];
		$follows = self::getFollowerIds($userID);
	    $events = self::events();
	    if ($events) {
	    	foreach ($events as $event) {
	    		$meta    = (array) get_post_meta($event, 'event_ops', true);
				$answers = (array) get_post_meta($event, 'event_ans', true);
				$feeds = array_merge($feeds, self::singleFeed($meta, $answers, $follows));
				if ($answers) $users = array_merge($users, array_keys($answers));
	    	}
	    }
	    $allUsers = self::getAllUsers($users, $follows);
	    $results = self::getAllFeeds($feeds, $allUsers);
		return $results;
	}
	static function getAllFeeds($feeds, $users) {
		$results = [];
		if ($feeds) {
			foreach ($feeds as $feed) {
				if ($feed) {
					foreach ($feed as $answers) {
						$results[$answers['user']] = $users[$answers['user']];
						$results[$answers['user']]['matches'] = $feed;
						break;
					}
				}
			}
		}
		return $results;
	}
	static function singleFeed($meta, $answers, $follows) {
		$feeds 	 = [];
        if (!empty($meta) && !empty($answers)) {
        	foreach ($answers as $uID => $answer) {
        		if (in_array($uID, $follows) && !empty($answer) && !empty($meta['teams'])) {
    				foreach ($meta['teams'] as $team) {
    					$givenAnswers = '';
                        $teamID = predictor_id_from_string($team['name']);
                        $options = 'team_'. $teamID;
                        if (!empty($meta[$options])) {
                            foreach ($meta[$options] as $option) {
                                $ansID = $options.'_'.predictor_id_from_string($option['title']);
                                if (empty($answer[$ansID])) continue;
                                $userAnswer = !empty($answer[$ansID]) ? $answer[$ansID] : false;
                                if ($userAnswer) {
                                	$feeds[$uID][$options]['user'] = $uID;
                                	$feeds[$uID][$options]['name'] = $team['name'];
                                	$feeds[$uID][$options]['ans'][$option['id']] = !empty($answer[$ansID]) ? $answer[$ansID] : false;
                                }
                            }
                        }
    				}
        		}
        	}
        }
        return $feeds;
    }
	static function events() {
		$date = date('Y-m-d');
		$query = array(
	        'post_type' => 'event',
	        'post_status' => 'publish',
	        'posts_per_page' => -1,
	        'orderby' => 'publish_date',
	        'fields' => 'ids',
	        'meta_query' => [['key'=>'pre-date', 'value'=>$date, 'compare'=>'=', 'type'=>'DATE']],
	    	'order' => 'DESC',
	    );
	    $events = new WP_Query($query);
	    $events = $events->posts;
		return $events;
	}
	static function getAllUsers($users, $follows) {
		if (!empty($users) && !empty($follows)) {
			$userIDs = array_intersect($users, $follows);
			return self::selectUsers($userIDs);
		}
		return [];
    }
    static function selectUsers($userIDs) {
		global $wpdb;
		$answeredUsers = [];
		$users = implode(',', $userIDs);
        $results = $wpdb->get_results( "SELECT id, user_login, user_email, display_name AS name FROM $wpdb->users WHERE ID IN ($users)", ARRAY_A);
        if ($results) {
        	foreach ($results as $user) {
        		$answeredUsers[$user['id']] = $user;
        		$answeredUsers[$user['id']]['avatar'] = get_avatar($user['user_email']);
        	}
        }
        return $answeredUsers;
    }
}