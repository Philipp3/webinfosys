<?php
  header("Content-type: text/plain");
  echo(trim(shell_exec("git pull")));
  echo("\n\nStatus:\n");
  echo(trim(shell_exec("git status")));
?>
