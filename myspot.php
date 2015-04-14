<?php

namespace grp12;

//use grp12\user\userMgr;

require_once "/var/www/html/php/templates/template.php";
require_once "/var/www/html/php/menu.php";
require_once "/var/www/html/php/database/database.php";
require_once "/var/www/html/php/user.php";

$db = database\Database::getInstance ()->connect ();
$template = null;

$usermgr = UserMgr::getInstance ();

$infomsg = "";

//if ($db == null) {
//	$template->fatalerror = true;
//	$template->fatalerrormsg = "Fehler: Verbindung zur Datenbank gescheitert.";
//}

if(isset($_GET["chgspot"])) {
	 if(isset($_GET["name"])) {
		 $name = $_GET["name"];
		 if($name=="") {
			 $infomsg = "!Fehler - Feld \"Name\" darf nicht leer sein.";
			 } else {
				 $stmt = $db -> prepare("SELECT * FROM myspots WHERE name=?");
				 $stmt -> execute(array($name));
				 if($stmt -> rowCount() == 0) {
					 $stmt = $db -> prepare("INSERT INTO myspots(name,location,description) VALUES(:name,:location,:description)");
					 $stmt -> execute(array(
							 ":name" => $name,
							 ":location" => $_GET["loc"],
							 ":description" => $_GET["desc"]
							 ));
					 $infomsg = "#Spot $name hinzugefügt";
					 } else {
						 $stmt = $db -> prepare("UPDATE myspots SET location=:location, description=:description WHERE name=:name");
						 $stmt -> execute(array(
								 ":name" => $name,
								 ":location" => $_GET["loc"],
								 ":description" => $_GET["desc"]
								 ));
						 $infomsg = "#Spot $name geändert";
						 }
			 }
	 }
}

if ($_GET ["action"] == "edit") {
	$template = new template\Template ( "php/templates/myspot_modify.phtml" );
	$template->menuentries = $menuentries;
	if (! isset ( $_GET ["spotname"] )) {
		$template->title = "Neuen Spot hinzufügen";
		$template->spotname = "";
		$template->spotloc = "";
		$template->spotdesc = "";
		$template->out ();
	} else {
		$template->title = "Spot ".$_GET["spotname"]." editieren";
		$stmt = $db->prepare ( "SELECT * FROM  myspots WHERE name=?" );
		$stmt->execute ( array (
				$_GET["spotname"]
		) );
		if($stmt->rowCount()==0) {
			$template->title = "Spot ".$_GET["spotname"]." hinzufügen";
			$template->spotname = $_GET["spotname"];
			$template->spotloc = "";
			$template->spotdesc = "";
		} else {
			$data = $stmt -> fetch();
			$template->spotname = $_GET["spotname"];
			$template->spotloc = $data["location"];
			$template->spotdesc = $data["description"];
		}
	}
} else {
	$template = new template\Template ( "php/templates/myspot_main.phtml" );
	$template->menuentries = $menuentries;
	if (! isset ( $_GET ["search"] )) {
		$template->title = "Myspots Startseite";
		$data = $db->query ( "SELECT * FROM myspots" );
		$template->groupcount = ceil ( count ( $data ) / 10 );
		$start = 0;
		if (isset ( $_GET ["start"] ))
			$start = $_GET ["start"];
		$template->currentpage = $start;
		$template->data = array_slice ( $data, 10 * ($start - 1), 10 );
		if ($_GET ["action"] == "delete") {
			$delete = $_GET ["spotname"];
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
		}
	} else {
		$template->title = "Suchergebnisse für " . $_GET ["search"];
		$stmt = $db->prepare ( "SELECT * FROM  myspots WHERE name LIKE ?" );
		$template->groupcount = 1;
		$template->currentpage = 1;
		$stmt->execute ( array (
				$_GET ["search"] 
		) );
		$template->data = $stmt->fetchAll ();
	}
	 if($infomsg != "") {
		 $template -> infotype = "infomsg";
		 if(substr($infomsg,0,1) == "#")
			 $template -> infotype = "infosucc";
		 else if(substr($infomsg,0,1) == "!")
			 $template -> infotype = "infoerr";
		$template -> infomsg = substr($infomsg,1);
	 }	
	$template->infomsg = $infomsg;
	$template->out ();
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