<?php

namespace grp12\database;

include_once ("/var/www/html/php/Logger.php");

include_once ("dblogininfo.php");
class Database {
	private static $instance;
	public static function getInstance() {
		if (! isset ( self::$instance ))
			self::$instance = new self ();
		return self::$instance;
	}
	private function __construct() {
	}
	private $db = null;
	private $connected = false;
	public function connect() {
		try {
			$this->db = new \PDO ( 'mysql:host=localhost;dbname=database;charset=utf8', dbint\dbuser, dbint\dbpwd );
			$this->connected = true;
		} catch ( PDOException $ex ) {
			logger\Logger . getLogger () . error ( "Problem establishing database connection: " . $ex->getMessage () );
		}
		return ($this->db);
	}
	public function disconnect() {
		$this->db = null;
		$this->connected = false;
	}
}

?>