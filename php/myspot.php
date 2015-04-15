<?php

namespace grp12\myspot;

use const \grp12\SERVERPATH;
require_once SERVERPATH . "php/templates/template.php";
require_once SERVERPATH . "php/database/database.php";
require_once SERVERPATH . "php/user.php";
function prepMyspotTemplate() {
	$db = database\Database::getInstance ()->connect ();
	$template = null;
	
	$usermgr = user\userMgr::getInstance ();
	
	if (isset ( $_GET ["searchSpotName"] )) {
		// TODO display results from a search after a certain spotname
		$search = $_GET ["searchSpotName"];
	} else if (isset ( $_GET ["spotname"] )) {
		// Handle viewing, editing, deleting etc. of chosen spot
		$spotname = $_GET ["spotname"];
		if (isset ( $_GET ["action"] )) {
			if ($_GET ["action"] == "delete") {
				$stmt = $db->prepare ( "SELECT * FROM myspots WHERE name=?" );
				$stmt->execute ( array (
						$_GET ["spotname"] 
				) );
				$template = getBasicSpotTemplate ();
				if ($stmt->rowCount () > 0) {
					$filename = $stmt->fetch ()["spotimg"];
					if ($filename != "") {
						delete_file ( SERVERPATH . "images/$filename" );
					}
					$stmt = $db->prepare ( "DELETE FROM myspots WHERE name=?" );
					$stmt->execute ( array (
							$_GET ["spotname"] 
					) );
					$template->infomsg = "Spot " . $_GET ["spotname"] . " wurde gelöscht.";
				} else {
					$template->infomsg = "Spot " . $_GET ["spotname"] . " existiert nicht.";
				}
			} else if ($_GET ["action"] == "edit") {
				// TODO implement editing function
			} else if ($_GET ["action"] == "new") {
				// TODO implement function for adding new spot
			}
		} else {
			$stmt = $db->prepare ( "SELECT * FROM myspots WHERE name=?" );
			$stmt->execute ( array (
					$_GET ["spotname"] 
			) );
			if ($stmt->rowCount () > 0) {
				$template = new template\Template ( SERVERPATH . "php/templates/myspot_detail.phtml" );
				$row = $stmt->fetch ();
				$template->spotname = $row ["name"];
				$template->spotloc = $row ["location"];
				$template->spotdesc = $row ["description"];
				$template->spotimg = $row ["spotimg"];
			} else {
				if (isset ( $_POST ["spotNewName"] )) {
					// TODO implement functionality to add new spot to the database
				}
				$template = getBasicSpotTemplate ();
				$template->infomsg = "Spot " . $_GET ["spotname"] . " existiert nicht.";
			}
		}
	} else {
		// Simple spot list without any other seettings
		$template = getBasicSpotTemplate ();
	}
	
	return $template;
}
function getBasicSpotTemplate() {
	$pagNumber = 1;
	if (isset ( $_GET ["page"] ) && is_numeric ( $_GET ["page"] ))
		$pagNumber = $_GET ["page"];
	$template = new template\Template ( SERVERPATH . "php/templates/myspot_list.phtml" );
	$template->title = "Spotliste";
	$query = $db->query ( "SELECT COUNT(*) FROM myspots" );
	$spotcount = $query->fetch ()[0];
	if (($pagNumber - 1) * 10 >= $spotcount) {
		$template->pageTooHigh = true;
		$pagNumber = max ( 1, ceil ( $spotcount / 10 ) );
	}
	$query = $db->query ( "SELECT name FROM myspots LIMIT " . (($pagNumber - 1) * 10) . ", 10" );
	if ($spotcount > 10)
		$template->showPaginator = true;
	$template->spotCount = $spotcount;
	$template->currPagVal = $pagNumber;
	$template->spotNrStart = $pagNumber * 10 - 9;
	$template->spotNrEnd = min ( $pagNumber * 10, $spotcount );
	$template->spotdata = $query->fetchAll ();
	$template->pagPages = ceil ( $spotcount / 10 );
	
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