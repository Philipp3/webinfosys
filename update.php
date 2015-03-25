<?php
  header("Content-type: text/plain");
  echo(trim(shell_exec("git reset --hard HEAD 2>&1")));
  echo(trim(shell_exec("git pull 2>&1")));
  echo("\n\nStatus:\n");
  echo(trim(shell_exec("git status 2>&1")));
?>
