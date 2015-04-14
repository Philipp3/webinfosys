<?php
namespace grp12;

const SERVERPATH = "/var/www/html/";

require_once SERVERPATH."php/templates/template.php";
require_once SERVERPATH."php/user.php";

$maintemplate = new template\Template(SERVERPATH."php/templates/base.phtml");

$usermgr = user\userMgr::getInstance();


$menuentries = array(
		"/index.php" => "Startseite",
		"/team.php" => "Über das Team",
		"/img.php" => "Beispielseite",
		"/myspot.php" => "MySpot");
if($usermgr->loggedin)
	$menuentries["/login.php?action=logout"] = $usermgr->username." abmelden";
else
	$menuentries["/login.php"] = "Anmelden";

$maintemplate->menuentries = $menuentries;

if($usermgr->loggedin)
	$maintemplate->sidebarcontent = "Angemeldet als ".$usermgr->username;
else
	$maintemplate->sidebarcontent = "Nicht angemeldet.";

$site = "index";
if(isset($_GET["site"])) $site = $_GET["site"];

switch($site) {
	case "index":
		$maintemplate->pagetitle = "Startseite";
		$sidetemplate = new template\Template(SERVERPATH."php/templates/index.phtml");
		$maintemplate->content =  $sidetemplate->outString();
		break;
	case "img":
		$maintemplate->pagetitle = "Beispielseite für Fließtext";
		$sidetemplate = new template\Template(SERVERPATH."php/templates/img.phtml");
		$maintemplate->content = $sidetemplate->outString();
		break;
	case "team":
		$maintemplate->pagetitle = "Über das Team";
		$sidetemplate = new template\Template(SERVERPATH."php/templates/team.phtml");
		$maintemplate->content = $sidetemplate->outString();
		break;
	default:
		$maintemplate->pagetitle = "Seite nicht gefunden";
		$sidetemplate = new template\Template(SERVERPATH."php/templates/404.phtml");
		$maintemplate->content = $sidetemplate->outString();
		break;
}

$maintemplate -> out();

?>