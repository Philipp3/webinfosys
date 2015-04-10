<?php

namespace grp12;

include ("php/templates/template.php");
include ("php/menu.php");
function initMyspots() {
	$db = database\Database::getInstance ()->connect ();
	$template = new template\Template ( "php/templates/myspot.phtml" );
	
	$template->fatalerror = false;
	
	if ($db == null) {
		$template->fatalerror = true;
		$template->fatalerrormsg = "Fehler: Verbindung zur Datenbank gescheitert.";
	}
	
	$infomsg = "";
	if (isset ( $_GET ["delete"] )) {
		$delete = $_GET ["delete"];
		$stmt = $db->prepare ( "SELECT * FROM  myspots WHERE name=?" );
		$stmt->execute ( array (
				$delete 
		) );
		if ($stmt->rowCount () > 0) {
			$stmt = $db->prepare ( "DELETE FROM myspots WHERE name=?" );
			$stmt->execute ( array (
					$delete 
			) );
			$infomsg = "#Spot $delete gelöscht.";
		} else
			$infomsg = "!Löschen gescheitert - Spot $delete nicht gefunden.";
	} else if (isset ( $_GET ["name"] )) {
		$name = $_GET ["name"];
		if ($name == "") {
			$infomsg = "!Fehler - Feld \"Name\" darf nicht leer sein.";
		} else {
			$stmt = $db->prepare ( "SELECT * FROM  myspots WHERE name=?" );
			$stmt->execute ( array (
					$name 
			) );
			if ($stmt->rowCount () == 0) {
				$stmt = $db->prepare ( "INSERT INTO myspots(name,location,description) VALUES(:name,:location,:description)" );
				$stmt->execute ( array (
						":name" => $name,
						":location" => $_GET ["loc"],
						":description" => $_GET ["desc"] 
				) );
				$infomsg = "#Spot $name hinzugefügt";
			} else {
				$stmt = $db->prepare ( "UPDATE myspots SET location=:location, description=:description WHERE name=:name" );
				$stmt->execute ( array (
						":name" => $name,
						":location" => $_GET ["loc"],
						":description" => $_GET ["desc"] 
				) );
				$infomsg = "#Spot $name geändert";
			}
		}
	}
	
	$template->title = "MySpots";
	$template->data = $db->query ( "SELECT * FROM myspots" );
	$template->spotcount = $template->data->rowcount ();
	$template->menuentries = $menuentries;
	
	if ($infomsg != "") {
		$template->infotype = "infomsg";
		if (substr ( $infomsg, 0, 1 ) == "#")
			$template->infotype = "infosucc";
		else if (substr ( $infomsg, 0, 1 ) == "!")
			$template->infotype = "infoerr";
		$template->infomsg = substr ( $infomsg, 1 );
	} else
		$template->infomsg = "";
	
	$template->out ();
}
?>