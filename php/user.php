<?php
namespace grp12\user;

include_once("/var/www/html/php/session.php");
include_once("/var/www/html/php/database/database.php");

class userMgr {
	const EXIT_SUCCESS = 0;
	const ERROR_NONEXISTING_USER = 1;
	const ERROR_WRONG_PASSWORD = 2;
	const ERROR_ESTABLISHING_DB_CONNECTION = 3;
	
    private static $instance = null;
	public static function getInstance() {
		if(!isset(self::$instance))
			self::$instance = new self();
		return self::$instance;
	}
	
	
    
    private $loggedin = false;
    private $username = null;
    private $session = null;
	
    private function __construct() {
    	$this -> session = \grp12\session\Session::getInstance();
    	$this -> session -> start();
    	if(isset($session->username)) {
    		$this -> loggedin = true;
    		$this -> username = $session->username;
    	}
    }
    
    public function login($username, $password) {
    	$db = \grp12\database\Database::getInstance() -> connect();
    	if($db == null)
    		return self::ERROR_ESTABLISHING_DB_CONNECTION;
    	$stmt = $db -> prepare("SELECT * FROM users WHERE username=?");
    	$stmt -> execute(array($username));
    	if($stmt -> rowCount() > 0) {
    		$userdata = $stmt -> fetch();
    		$phrase = $password . $userdata["salt"];
    		if(hash("sha256", $phrase) == $userdata["hash"]) {
    			$this -> session -> username = $username;
    			$this -> loggedin = true;
    			$this -> username = $username;
    			return self::EXIT_SUCCESS;
    		} else {
    			return self::ERROR_WRONG_PASSWORD;
    		}
    	} else
    		return self::ERROR_NONEXISTING_USER;
    }
    
    public function __get($name) {
    	switch($name) {
    		case "loggedin":
    			return $this->loggedin;
    			break;
    		case "username":
    			return $this->username;
    			break;
    	}
    }
    
    public function logout() {
    	$this -> loggedin = false;
    	$this -> username = null;
    	unset($this -> session->username);
    }
    
}
?>
