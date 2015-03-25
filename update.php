<?php
  echo(htmlentities(trim(shell_exec("git pull"))));
  echo("\n\nStatus:\n");
  echo(htmlentities(trim(shell_exec("git status"))));
?>
