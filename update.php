<?php
  echo(shell_exec("git pull"));
  echo("\n\nStatus:\n");
  echo(shell_exec("git status"));
?>
