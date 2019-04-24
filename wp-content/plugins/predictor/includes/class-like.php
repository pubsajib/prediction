<?php 
/**
 * LIke
 */
namespace PLUGIN_NAME;
class Like {
	private $userID;
	private $eventID;
	private $IP;
	private $DB;
	private $table;
	private $attr=['user'=>0, 'event'=>0, 'ip'=>0];

	function __construct($attr=[]) {
		global $wpdb;
		$this->DB = $wpdb;
		$this->table = $this->DB->prefix.'predictor_likes';
		if ($attr) {
			$this->attr = array_merge($this->attr, $attr);
			$this->userID = $this->attr['user'];
			$this->eventID = $this->attr['event'];
			// $this->IP = $this->getClientIP();
		}
	}
	function getClientIP() {
	    $ipaddress = '';
	    if (isset($_SERVER['HTTP_CLIENT_IP']))
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    else if(isset($_SERVER['REMOTE_ADDR']))
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    else
	        $ipaddress = 'UNKNOWN';
	    return $ipaddress;
	}
	static function add($attr) {
		global $wpdb;
		if (!$attr['ip']) $attr['ip'] = 4;
		$wpdb->insert($wpdb->prefix.'predictor_likes', $attr, ['%d', '%d', '%s']);
		return $wpdb->insert_id;
	}
	function remove($ip=0) {
		if (!$ip) $ip = $this->getClientIP();
		if ($ip) return $this->DB->delete($this->table, ['ip'=>$ip], ['%s']);
		return false;
	}
	function predictor($id, $count=false) {
		if (!$id) $id = $this->userID;
		if ($id) {
			if ($count) return $this->DB->get_results( "SELECT * FROM ".$this->table." WHERE user = $id", OBJECT );
			else return $this->DB->get_results( "SELECT COUNT(*) FROM ".$this->table." WHERE user = $id", OBJECT );
		}
		return false;
	}
	function event($id) {
		if (!$id) $id = $this->eventID;
		if ($id) return $this->DB->get_results( "SELECT * FROM ".$this->table." WHERE event = $id", OBJECT );
		return false;
	}
	function test() {
		echo '<br>$this->userID : '. $this->userID;
		echo '<br>$this->eventID : '. $this->eventID;
		echo '<br>$this->IP : '. $this->IP;
		echo '<br>$this->table : '. $this->table;
	}
}