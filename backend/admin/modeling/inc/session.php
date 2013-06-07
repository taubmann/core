<?php
/*
 * Script to test if the needed Session is still alive
 * ja-include-check when setup-window is opened in modeler
 * */
session_start();
header('Content-Type: text/plain; charset=utf-8');
$projectName = preg_replace('/\W/', '', $_GET['project']);
if(!isset($_SESSION[$projectName]['root']))
{
	echo 'alert("Session expired! Please save your Work locally as XML and re-login!");';
}
?>
