<?php 
/**
 * Notification
 */
class Notification {
	static function add($followerID, $followeeID) {
		global $wpdb;
		$tableName = $wpdb->prefix.'predictor_notifications';
		if ($wpdb->insert( $tableName, ['followee_id'=>$followeeID, 'follower_id'=>$followerID] )) return 200;
		return 201;
	}
	static function remove($followerID, $followeeID) {
		global $wpdb;
		$tableName = $wpdb->prefix.'predictor_notifications';
		if ($wpdb->delete( $tableName, ['followee_id'=>$followeeID, 'follower_id'=>$followerID] )) return 200;
		return 201;
	}
	static function changeStatus($followerID, $followeeID) {}
	static function createTable() {
		global $wpdb;
		$tableName = $wpdb->prefix.'predictor_notifications';
		$sql = "";
		// $sql .= "DROP TABLE IF EXISTS `". $tableName ."`; ";
		$sql .= "CREATE TABLE IF NOT EXISTS`". $tableName ."` ( ";
			$sql .= "`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
			$sql .= "`user` int(11) NOT NULL, ";
			$sql .= "`msg` TEXT, ";
			$sql .= "`type` ENUM('warning', 'info') NOT NULL DEFAULT 'info', ";
			$sql .= "`status` tinyint(1) DEFAULT NULL, ";
			$sql .= "`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
			$sql .= "`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ";
			$sql .= "INDEX (`user`) ";
		$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1; ";
		// return $sql;
		return $wpdb->query($sql);
	}
}
?>