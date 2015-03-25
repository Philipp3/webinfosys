<?php
namespace grp12;
include("php/templates/template.php");
include("php/menu.php");

$template = new template\Template("php/templates/team.phtml");

$template -> title = "Über das Team";
$template -> menuentries = $menuentries;
$template -> out();
?>