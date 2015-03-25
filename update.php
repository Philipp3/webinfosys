<?php
header("Content-type: text/plain");
echo("Git-update script:\n\n");
echo("Resetting possible changes in server directory: ".trim(shell_exec("git reset --hard HEAD 2>&1")));
echo("\nPulling changes from github: ".trim(shell_exec("git pull 2>&1")));
echo("\n\ngit status:\n");
echo(trim(shell_exec("git status 2>&1")));
?>
