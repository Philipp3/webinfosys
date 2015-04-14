<?php

require_once "/var/www/html/php/user.php";

$usermgr = \grp12\user\userMgr::getInstance();

$menuentries = array(
    "index.php" => "Startseite",
    "team.php" => "Über das Team",
    "img.php" => "Beispielseite",
    "myspot.php" => "MySpot");

if($usermgr->loggedin)
	$menuentries["login.php?logout=true"] = "Logout";
else
	$menuentries["login.php"] = "Login";  
?>