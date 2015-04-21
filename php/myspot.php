<?php

namespace grp12\myspot;

const SERVERPATH = \grp12\SERVERPATH;

require_once SERVERPATH . "php/templates/template.php";
require_once SERVERPATH . "php/database/database.php";
require_once SERVERPATH . "php/user.php";


function prepMyspotTemplate() {

	$db = \grp12\database\Database::getInstance ()->connect ();
	$template = null;
	
	$usermgr = \grp12\user\userMgr::getInstance ();

	if (isset ( $_POST["name"] )) { //add or modify

		if (! $usermgr->loggedin) {
			header('Location: /login/');
			die();
		}

		$spotname = $_POST["name"];
		$template = getBasicSpotTemplate ($db);

		if ($spotname == ""){
			$template->infomsg = "Name darf nicht leer sein.";
			return $template;
		}

		$stmt = $db->prepare("SELECT * FROM myspots WHERE name=?");
		$stmt->execute(array($spotname));

		if ($stmt->rowCount () > 0) { //edit
			if($_POST["newspotaction"] == "new") {
				$template->infomsg = "Fehler: Spot $spotname existiert bereits.";
				return $template;
			}
			$stmt = $db -> prepare("UPDATE myspots SET location=:location, description=:description WHERE name=:name");
			$stmt -> execute(array(":name" => $spotname, ":location" => $_POST["loc"], ":description" => $_POST["desc"]));
			$infomsg = "#Spot $spotname geändert";
		} else { //add
			$stmt = $db -> prepare("INSERT INTO myspots(name,location,description) VALUES(:name,:location,:description)");
			$stmt -> execute(array(":name" => $spotname, ":location" => $_POST["loc"], ":description" => $_POST["desc"]));
			$infomsg = "#Spot $spotname hinzugefügt";
		}

	} else if (isset ( $_GET ["search"] )) {  //search

		$search = $_GET ["search"];
		$template = getBasicSpotTemplate ($db,$search);

	} else if (isset($_GET["add"])){

		$template = new \grp12\template\Template ( SERVERPATH . "php/templates/myspot_modify.phtml" );
		$template->header = "Spot hinzufügen";

	} else if (isset ( $_GET ["spotname"] )) { //single spot, display or action

		$spotname = $_GET["spotname"];
		$stmt = $db->prepare("SELECT name,location,description,astext(coordinates) as coordinates FROM myspots WHERE name=?");
		$stmt->execute(array($spotname));

		if ($stmt->rowCount () <= 0) {
			$template = getBasicSpotTemplate ($db);
			$template->infomsg = "Spot " . $spotname . " existiert nicht.";
			return $template;
		}

		if (isset ( $_GET ["action"] )) {
			if (! $usermgr->loggedin) {
				$template = getBasicSpotTemplate ($db);
				$template->infomsg = "Bitte loggen sie sich ein.";
				return $template;
			}

			if ($_GET ["action"] == "delete") { 

				//TODO delete image files

				$stmt = $db->prepare("DELETE FROM myspots WHERE name=?"); //!!
				$stmt->execute(array($spotname));

				$template = getBasicSpotTemplate ($db);
				$template->infomsg = "Spot " . $spotname . " wurde gelöscht.";

			} else if ($_GET ["action"] == "edit") {

				$template = new \grp12\template\Template ( SERVERPATH . "php/templates/myspot_edit.phtml" );

				$defaults = $stmt->fetch();
				$template->editspotname=$defaults["name"];
				$template->editspotloc=$defaults["location"];
				$template->editspotdesc=$defaults["description"];
				preg_match_all("/[-+]?[0-9]*\.?[0-9]+/",$defaults["coordinates"],$coords_temp,PREG_SET_ORDER);
				$lat = floatval($coords_temp[0][0]);
				$lon = floatval($coords_temp[1][0]);
				$template->editspotlat = $lat;
				$template->editspotlong = $lon;
				$template->action="edit";
				$template->title = $spotname . " ändern";
				
			} else if($_GET["action"] == "new") {
				$template = new \grp12\template\Template ( SERVERPATH . "php/templates/myspot_edit.phtml" );
				$template->action="new";
				$template->title="Neuen Spot hinzufügen";
			} else {
				$template = getBasicSpotTemplate ($db);
				$template->infomsg = "Ungültige Aktion.";
			}

		} else { //display details & images

			$template = new \grp12\template\Template ( SERVERPATH . "php/templates/myspot_detail.phtml" );
			$row = $stmt->fetch();
			$template->spotname = $row ["name"];
			$template->spotloc = $row ["location"];
			$template->spotdesc = $row ["description"];
			$template->loggedin = $usermgr->loggedin;

			$stmt_i = $db->prepare("SELECT * FROM myspots_images WHERE name=?");
			$stmt_i->execute (array($spotname));
			if ($stmt_i->rowCount() > 0){
				$template->images = $stmt_i->fetchAll();
			}
			
			preg_match_all("/[-+]?[0-9]*\.?[0-9]+/",$row["coordinates"],$coords_temp,PREG_SET_ORDER);
			$lat = floatval($coords_temp[0][0]);
			$lon = floatval($coords_temp[1][0]);
			$template->lat = $lat;
			$template->lon = $lon;
					
			//lat/breite: 1° ~ 111 km
			//lon/länge: 1°*cos(lat) ~ 111km
			
			$dist = 10;
			$radius = $dist/111;

			$lat0 = $lat + $radius;
			$lon0 = $lon + $radius * abs(cos(deg2rad($lat)));

			$lat1 = $lat - $radius;
			$lon1 = $lon - $radius * abs(cos(deg2rad($lat)));

			
			$start_time = microtime(true);
			$stmt_n = $db->prepare("SELECT name, ST_Distance(point(:lat, :lon),coordinates) as dist FROM myspots WHERE MBRContains(envelope(linestring(point(:lat0, :lon0), point(:lat1, :lon1))),coordinates) and name != :name ORDER BY ST_Distance(point(:lat, :lon),coordinates) ASC LIMIT 10");
			$stmt_n -> execute(array(":name" => $spotname, ":lat0" => $lat0, ":lon0" => $lon0, ":lat1" => $lat1, ":lon1" => $lon1, ":lat" => $lat, ":lon" => $lon));			$template->time = (microtime(true) - $start_time)*1000;

			if ($stmt_n ->rowCount() > 0){
				$data = $stmt_n->fetchAll();
				$templatedata = array();
				foreach($data as $near) {
					$templatedata[$near["name"]] = round($near["dist"]*40000/360, 2);
				}
				$template->nearby = $templatedata;
			}

			

		}
	} else {
		// Simple spot list without any other settings
		$template = getBasicSpotTemplate ($db);
	}
	
	return $template;
}

function getBasicSpotTemplate($db, $search=null) {
	$pagNumber = 1;
	if (isset ( $_GET ["page"] ) && is_numeric ( $_GET ["page"] ))
		$pagNumber = $_GET ["page"];
	$template = new \grp12\template\Template ( SERVERPATH . "php/templates/myspot_list.phtml" );

	if ($search != null){
		$template->title = "Suchergebnisse";
		$template->search = $search;
		$stmt = $db->prepare ( "SELECT COUNT(*) FROM myspots WHERE name LIKE ?" );
		$stmt->execute(array("%{$search}%"));

	} else {
		$template->title = "Spotliste";
		$stmt = $db->prepare ( "SELECT COUNT(*) FROM myspots" );
		$stmt->execute();
	}

	$spotcount = $stmt->fetch()[0];
	if (($pagNumber - 1) * 10 >= $spotcount) {
		$template->pageTooHigh = true;
		$pagNumber = max ( 1, ceil ( $spotcount / 10 ) );
	}
	
	if ($search != null){
		$start_time = microtime(true);
		$stmt = $db->prepare ( "SELECT name FROM myspots WHERE name LIKE ? LIMIT " . (($pagNumber - 1) * 10) . ", 10" );
		$stmt->execute(array("%{$search}%"));
		$template->time = (microtime(true) - $start_time)*1000;
	} else {
		$start_time = microtime(true);
		$stmt = $db->prepare ( "SELECT name FROM myspots LIMIT " . (($pagNumber - 1) * 10) . ", 10" );
		$stmt->execute();
		$template->time = (microtime(true) - $start_time)*1000;
	}

	if ($spotcount > 10)
	$template->showPaginator = true;
	$template->spotCount = $spotcount;
	$template->currPagVal = $pagNumber;
	$template->spotNrStart = $pagNumber * 10 - 9;
	$template->spotNrEnd = min ( $pagNumber * 10, $spotcount );
	$template->spotdata = $stmt->fetchAll();
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
