<?php
header("Content-type: text/plain");
echo("Git-update script:\n\n");
echo("Resetting possible changes in server directory:\n".trim(shell_exec("git reset --hard HEAD 2>&1")));
echo("\n\nPulling changes from github:\n".trim(shell_exec("git pull origin indexv2 2>&1")));
echo("\n\ngit status:\n");
echo(trim(shell_exec("git status 2>&1")));
?>
