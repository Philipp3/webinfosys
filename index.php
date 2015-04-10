<?php

namespace grp12;

require_once "php/templates/template.php";
require_once "php/menu.php";
require_once "php/database/database.php";

$site = "index";

if (isset ( $_GET ["site"] ))
	$site = $_GET ["site"];

switch ($site) {
	case "index" :
		$template = new template\Template ( "php/templates/index.phtml" );
		$template->title = "Startseite";
		$template->menuentries = $menuentries;
		$template->out ();
		break;
	
	case "img" :
		$template = new template\Template ( "php/templates/img.phtml" );
		$template->title = "Beispielseite";
		$template->menuentries = $menuentries;
		$template->out ();
		break;
	
	case "team" :
		$template = new template\Template ( "php/templates/team.phtml" );
		$template->title = "Über das Team";
		$template->menuentries = $menuentries;
		$template->out ();
		break;
	
	case "myspot" :
		initMyspots ();
		break;
	default :
}
?>