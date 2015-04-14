<?php

namespace grp12;

const SERVERPATH = "/var/www/html/";

require_once SERVERPATH . "php/templates/template.php";
require_once SERVERPATH . "php/user.php";
require_once SERVERPATH . "php/myspot.php";

$maintemplate = new template\Template ( SERVERPATH . "php/templates/base.phtml" );

$usermgr = user\userMgr::getInstance ();

$site = "index";
if (isset ( $_GET ["site"] ))
	$site = $_GET ["site"];

$sidetemplate = null;

switch ($site) {
	case "index" :
		$maintemplate->pagetitle = "Startseite";
		$sidetemplate = new template\Template ( SERVERPATH . "php/templates/index.phtml" );
		break;
		
		
		
	case "img" :
		$maintemplate->pagetitle = "Beispielseite für Fließtext";
		$sidetemplate = new template\Template ( SERVERPATH . "php/templates/img.phtml" );
		break;
		
		
		
	case "team" :
		$maintemplate->pagetitle = "Über das Team";
		$sidetemplate = new template\Template ( SERVERPATH . "php/templates/team.phtml" );
		break;
		
		
		
	case "login" :
		$sidetemplate = new template\Template ( SERVERPATH . "php/templates/login.phtml" );
		$referer = "";
		if(isset($_SERVER["HTTP_REFERER"]))
			$sidetemplate->referer = $_SERVER ["HTTP_REFERER"];
		
		if (isset ( $_POST ["username"] )) {
			$username = $_POST ["username"];
			$password = $_POST ["password"];
			$referer = $_POST ["ref"];
			$sidetemplate->referer = $referer;
			if (! $usermgr->loggedin) {
				$return = $usermgr->login ( $username, $password );
				switch ($return) {
					case user\userMgr::EXIT_SUCCESS :
						$maintemplate->pagetitle = "Anmeldung erfolgreich";
						$sidetemplate->loggedin = true;
						$sidetemplate->infomsg = "infLoggedIn";
						break;
					case user\userMgr::ERROR_NONEXISTING_USER :
					case user\userMgr::ERROR_WRONG_PASSWORD :
						$maintemplate->pagetitle = "Fehler";
						$sidetemplate->loggedin = false;
						$sidetemplate->defaultUsername = $username;
						$sidetemplate->errmsg = "errUserPwd";
						break;
					case user\userMgr::ERROR_ESTABLISHING_DB_CONNECTION :
						$maintemplate->pagetitle = "Fehler";
						$sidetemplate->loggedin = false;
						$sidetemplate->errmsg = "errDbCon";
						break;
				}
			}
		} else {
			
			if ($usermgr->loggedin) {
				$sidetemplate->loggedin = true;
				if (isset($_GET["action"]) && $_GET ["action"] == "logout") {
					$usermgr->logout ();
					$sidetemplate->loggedin = false;
					$maintemplate->pagetitle = "Abmeldung erfolgreich";
					$sidetemplate->infomsg = "infLoggedOut";
				} else {
					$sidetemplate->pagetitle = "Angemeldet";
					$sidetemplate->infomsg = "infLoggedInAlready";
				}
			} else {
				$maintemplate->pagetitle = "Anmelden";
				$sidetemplate->loggedin = false;
				if (isset($_GET["action"]) && $_GET ["action"] == "logout") {
					$sidetemplate->errmsg = "ErrLoggedOutAlready";
				}
			}
		}
		break;
		
		
		
	case "myspot":
		$maintemplate->title = "Myspots";
		$sidetemplate = prepMyspotTemplate();
		break;
		
		
		
	default :
		$maintemplate->pagetitle = "Seite nicht gefunden";
		$sidetemplate = new template\Template ( SERVERPATH . "php/templates/404.phtml" );
		break;
}

$menuentries = array (
		"/" => "Startseite",
		"/team/" => "Über das Team",
		"/img/" => "Beispielseite",
		"/myspot/" => "MySpot"
);
if ($usermgr->loggedin)
	$menuentries ["/login/?action=logout"] = $usermgr->username . " abmelden";
else
	$menuentries ["/login/"] = "Anmelden";

$maintemplate->menuentries = $menuentries;

if ($usermgr->loggedin)
	$maintemplate->sidebarcontent = "Angemeldet als " . $usermgr->username;
else
	$maintemplate->sidebarcontent = "Nicht angemeldet.";

$maintemplate->content = $sidetemplate->outString ();

$maintemplate->out ();

?>