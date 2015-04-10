<?php

namespace grp12;

include ("php/templates/template.php");
include ("php/menu.php");

$template = new template\Template ( "php/templates/img.phtml" );

$template->title = "Beispielseite";
$template->menuentries = $menuentries;
$template->out ();
?>