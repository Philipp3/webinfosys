<?php
namespace grp12;
include("php/templates/template.php");
include("php/menu.php");
 
$template = new template\Template("php/templates/index.phtml");

$template -> title = "Startseite";
$template -> menuentries = $menuentries;
$template -> out();
?>