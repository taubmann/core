<?php
/*
 * included header for various importers
 * prepares file-paths
 * */

session_start();
$projectName = $_GET['project'];
if(!$_SESSION[$projectName]['root']) exit('alert("no Rights to edit!");');

$bp = '../../../..';//path to backend/

$obj_path = $bp . '/../projects/' . $projectName . '/objects/';

$add_path = ($_GET['m'] ? 
						$bp : 
						$bp . '/../projects/' . $projectName
			) . '/extensions/' . $_GET['ext'] . '/' . $_GET['file'];
