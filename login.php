<?php
namespace grp12;

include("php/templates/template.php");
include("php/menu.php");
include("php/user.php");

$template = new template\Template("php/templates/login.phtml");

$infomsg = "";
if(isset($_POST["username"]) && isset($_POST["password"])) {
	$username = $_POST["username"];
	$password = $_POST["password"];

	$ret = user\userMgr::getInstance().login($username, $password);

	if ($ret==user\userMgr::EXIT_SUCCESS){
		$infomsg = "#Login erfolgreich.";
	} else if ($ret==user\userMgr::ERROR_WRONG_PASSWORD || $ret==user\userMgr::ERROR_NONEXISTING_USER){
		$infomsg = "!Ungültiger Benutzername oder Passwort";
	} else {
		$infomsg = "!Internal Server Error";
	}
} else {
   $infomsg = "!Benutzername und Passwort eingeben.";
}


$template -> title = "Login";
$template -> menuentries = $menuentries;

if($infomsg != "") {
    $template -> infotype = "infomsg";
    if(substr($infomsg,0,1) == "#")
        $template -> infotype = "infosucc";
    else if(substr($infomsg,0,1) == "!")
        $template -> infotype = "infoerr";
    $template -> infomsg = substr($infomsg,1);
} else $template -> infomsg = "";

$template -> out();

?>
