<?php
namespace grp12\user;

include_once("/var/www/html/php/session.php");

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
    private function construct() {
    	$session = session\Session.getInstance();
    	$session -> start();
    	if(isset($session->username)) {
    		$loggedin = true;
    		$username = $session->username;
    	}
    }
    
    private $loggedin = false;
    private $username = null;
    private $session = null;
    
    public function login($username, $password) {
    	$db = database\Database.getInstance() -> connect();
    	if($db == null)
    		return self::ERROR_ESTABLISHING_DB_CONNECTION;
    	$stmt = $db -> prepare("SELECT * FROM users WHERE username=?");
    	$stmt -> execute(array($username));
    	if($stmt -> rowCount() > 0) {
    		$userdata = $stmt -> fetch();
    		$phrase = $password . $userdata["salt"];
    		if(hash("sha256", $phrase) == $userdata["hash"]) {
    			$session->username = $userdata["username"];
    			$loggedin = true;
    			$username = $userdata["username"];
    			return self::EXIT_SUCCESS;
    		} else {
    			return self::ERROR_WRONG_PASSWORD;
    		}
    	} else
    		return self::ERROR_NONEXISTING_USER;
    }
    
    public function logout() {
    	$loggedin = false;
    	$username = null;
    	unset($session->username);
    }
    
}
?>
