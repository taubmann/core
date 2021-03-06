<?php
/********************************************************************************
*  Copyright notice
*
*  (c) 2013 Christoph Taubmann (info@cms-kit.org)
*  All rights reserved
*
*  This script is part of cms-kit Framework. 
*  This is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License Version 3 as published by
*  the Free Software Foundation, or (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/licenses/gpl.html
*  A copy is found in the textfile GPL.txt and important notices to other licenses
*  can be found found in LICENSES.txt distributed with these scripts.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
*********************************************************************************/

/**
* Setup
* 
*
* 
*/

session_start();
$backend = '../../';
require $backend.'inc/php/functions.php';
require($backend.'inc/super.php');

$lang = browserLang( glob('inc/locale/*.php') );

$LL = array();
@include 'inc/locale/'.$lang.'.php';

// create the project-folder if not exists
if (!file_exists($backend.'../projects'))
{
	mkdir($backend.'../projects');
	chmod($backend.'../projects', 0776);
	file_put_contents($backend.'../projects/index.html', '');
}


// create Tooltip-Labels used in Step 3 / 4 below
function hlp($what, $float=true)
{
	global $LL;
	return (isset($LL['project_setup_help_'.$what]) ? 
			'<a class="tt'.($float?' fr':'').'" href="#">?<span>'.L('project_setup_help_'.$what).'</span></a>' : 
			'');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>create a new Project</title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1" />
<script type="text/javascript" src="../../inc/js/jquery.min.js"></script>

<link rel="stylesheet" type="text/css" href="inc/css/styles.css" />
<script type="text/javascript" src="inc/js/functions.js"></script>

</head>
<body>
<div id="wrapper">
<?php

// #### 1 #### no (correct) super-password/captcha => draw login-form (again)
if (!isset($_POST['pass']) || crpt($_POST['pass'], $super[0]) !== $super[0].':'.$super[1])
{
	//echo '<h3>'.L('incorrect_password').'</h3>';
	
	require 'inc/step1.php';
}
if (isset($_POST['pass']) && (!isset($_POST['captcha_answer']) || $_POST['captcha_answer'] != $_SESSION['captcha_answer']) )
{
	echo '<h3>'.L('incorrect_captcha').'</h3>';
	require 'inc/step1.php';
}

// #### 2 #### no (wished) Project-Name is given => draw Input to enter Project-Name
if (!isset($_POST['wished_name']))
{
	require 'inc/step2.php';
}

$_POST['wished_name'] = @preg_replace('/[^a-z0-9_]/si', '', $_POST['wished_name']);
$ppath = $backend.'../projects/'.$_POST['wished_name'];

// Project still exists => show Error & draw Input to enter Project-Name
if (file_exists($ppath.'/objects/__configuration.php'))
{
	echo '<h3>'.L('Project_Name').' "'.$_POST['wished_name'].'" '.L('already_in_use').'</h3>';
	require 'inc/step2.php';
}

// #### 3 #### draw input for Database-/Folder-Credentials
if (!isset($_POST['generate_project']))
{
	require 'inc/step3.php';
}

// #### 4 #### show the Success-Form
if (isset($_POST['generate_project']))
{
	require 'inc/step4.php';
}

// ... this shouldn't happen
echo '<h3>'.L('nothing_to_do').'</h3>';
?>

</body>
</html>
