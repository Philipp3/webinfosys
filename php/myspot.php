<?php

namespace grp12;

require_once SERVERPATH . "php/templates/template.php";
require_once SERVERPATH . "php/database/database.php";
require_once SERVERPATH . "php/user.php";

function prepMyspotTemplate() {
	$db = database\Database::getInstance ()->connect ();
	$template = null;
	
	$usermgr = user\userMgr::getInstance ();
	
	if(isset($_GET["searchSpotName"])) {
		//display results from a search after a certain spotname
	} else if(isset($_GET["spotname"])) {
		//Handle viewing, editing, deleting etc. of chosen spot
	} else {
		//Simple spot list without any other seettings
		$pagNumber = 1;
		if(isset($_GET["page"]))
			$pagNumber = $_GET["page"];
		$template = new template\Template(SERVERPATH . "php/templates/myspot_list.phtml");
		$template->title = "Spotliste";
		$query = $db->query("SELECT COUNT(*) FROM myspots");
		$spotcount = $query->fetch()[0];
		if(($pagNumber-1)*10 >= $spotcount) {
			$template->pageTooHigh = true;
			$pagNumber = max(1,ceil($spotcount/10));
		}
		$query = $db->query("SELECT name FROM myspots LIMIT ".(($pagNumber-1)*10).", 10");
		if($spotcount > 10)
			$template->showPaginator = true;
		$template->spotCount = $spotcount;
		$template->currPagVal = $pagNumber;
		$template->spotNrStart = $pagNumber*10-9;
		$template->spotNrEnd = min($pagNumber*10, $spotcount+10-$pagNumber*10);
		$template->spotdata = $query->fetchAll();
		$template->pagPages = ceil($spotcount/10);
	}
	
	return $template;
	
}

/*
 *
 * $infomsg = "";
 * if(isset($_GET["delete"])) {
 * $delete = $_GET["delete"];
 * $stmt = $db -> prepare("SELECT * FROM myspots WHERE name=?");
 * $stmt -> execute(array($delete));
 * if($stmt -> rowCount() > 0) {
 * $stmt = $db -> prepare("DELETE FROM myspots WHERE name=?");
 * $stmt -> execute(array($delete));
 * $infomsg = "#Spot $delete gelöscht.";
 * } else
 * $infomsg = "!Löschen gescheitert - Spot $delete nicht gefunden.";
 * }
 * else if(isset($_GET["name"])) {
 * $name = $_GET["name"];
 * if($name=="") {
 * $infomsg = "!Fehler - Feld \"Name\" darf nicht leer sein.";
 * } else {
 * $stmt = $db -> prepare("SELECT * FROM myspots WHERE name=?");
 * $stmt -> execute(array($name));
 * if($stmt -> rowCount() == 0) {
 * $stmt = $db -> prepare("INSERT INTO myspots(name,location,description) VALUES(:name,:location,:description)");
 * $stmt -> execute(array(
 * ":name" => $name,
 * ":location" => $_GET["loc"],
 * ":description" => $_GET["desc"]
 * ));
 * $infomsg = "#Spot $name hinzugefügt";
 * } else {
 * $stmt = $db -> prepare("UPDATE myspots SET location=:location, description=:description WHERE name=:name");
 * $stmt -> execute(array(
 * ":name" => $name,
 * ":location" => $_GET["loc"],
 * ":description" => $_GET["desc"]
 * ));
 * $infomsg = "#Spot $name geändert";
 * }
 * }
 * }
 *
 *
 * $template -> title = "MySpots";
 * $template -> data = $db -> query("SELECT * FROM myspots");
 * $template -> spotcount = $template -> data -> rowcount();
 * $template -> menuentries = $menuentries;
 *
 * if($infomsg != "") {
 * $template -> infotype = "infomsg";
 * if(substr($infomsg,0,1) == "#")
 * $template -> infotype = "infosucc";
 * else if(substr($infomsg,0,1) == "!")
 * $template -> infotype = "infoerr";
 * $template -> infomsg = substr($infomsg,1);
 * } else $template -> infomsg = "";
 *
 * $template -> out();
 */
?>