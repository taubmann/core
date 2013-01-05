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
/*
* CRUD-Functions
*/
require 'inc/php/header.php';

$output = '';
//print_r($_GET);

$action = preg_replace('/\W/', '', $_REQUEST['action']);

if(!$_SESSION[$projectName]['objects']) exit('<iframe style="width:100px;height:20px" src="inc/php/na.php?p='.$projectName.'"></iframe>');

require_once($ppath.'/objects/class.'.strtolower($_GET['objectName']).'.php');
require_once('inc/php/class.crud.php');
@include('inc/locale/' . $lang . '.php');

// prevent session-hijacking
if( !isset($_SESSION[$projectName]['user_agent']) || $_SESSION[$projectName]['user_agent'] != md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . Configuration::$DB_PASSWORD[0])) {
	exit('Session expired or IP changed');
}


$c = new crud();

$objectDB = intval($objects->{$objectName}->db);

$objectId 			 = (isset($_GET['objectId']) ? $_GET['objectId'] : null);
$objectFields 		 = $_SESSION[$projectName]['labels'][$objectName];
$referenceName 		 = (isset($_GET['referenceName']) ? $_GET['referenceName'] : null);
$referenceId 		 = (isset($_GET['referenceId']) ? $_GET['referenceId'] : null);
$referenceFields 	 = (isset($_GET['referenceName']) ? $_SESSION[$projectName]['labels'][$referenceName] : array('id'=>1));



foreach ($_POST as $k => $v)
{
	switch (substr($k, 0, 2))
	{
	
	// base64-encode Content 
		case 'e_':
			$_POST[$k] = base64_encode($v);
		break;
	
		// encrypt Content (XOR or Blowfish)
		case 'c_':
			if (@$key = $_SESSION[$projectName]['config']['crypt'][$objectName][$k])
			{
				require_once('inc/php/crypt.php');
				$key  = md5($objectName . $k . $key);
				$_POST[$k] = 	( substr($objects->{$objectName}->col->{$k}->type, -4) == 'CHAR' ) ? 
								X_OR::encrypt($v, $key) : 
								Blowfish::encrypt($v, $key, md5(Configuration::$DB_PASSWORD[$objectDB]));
			}
			else
			{
				unset ($_POST[$k]);
			}
		break;
		
		// save Data from Micro-Structure-Inputs back into JSON-Field
		case 'j_':
			if (@$temp = json_decode($v, true))
			{ 
				foreach ($temp as $jk => $jv){ $temp[$jk]['value'] = $_POST[$jk]; }
				$_POST[$k] = json_encode($temp);
			}
		break;
	}
}


if (isset($objects->{$objectName}->hooks->PRE) || isset($objects->{$objectName}->hooks->PST))
{
	 include('extensions/cms/hooks.php');
	@include($ppath . '/extensions/cms/hooks.php');
}


$c->lang = $lang;
$c->LL = $LL;
$c->projectName = $projectName;
$c->ppath = $ppath;
$c->objects = $objects;
$c->objectName = $objectName;
$c->objectId = $objectId;
$c->objectFields = $objectFields;
$c->dbi = $objectDB;
$c->referenceName = $referenceName;
$c->referenceId = $referenceId;
$c->referenceFields = $referenceFields;
$c->limit =		(isset($_GET['limit'])  ? intval($_GET['limit']) : 0);
$c->offset =	(isset($_GET['offset']) ? intval($_GET['offset']) : 0);
$c->mobile =	(isset($_GET['mobile']) ? intval($_GET['mobile']) : 0);
$c->sortBy = $_SESSION[$projectName]['sort'][$objectName];


// call PRE/PST - Hooks
function callHooks ($when)
{
	global $objects, $objectName;
	
	if (@is_array($objects->$objectName->hooks->{$when}))
	{
		foreach ($objects->$objectName->hooks->{$when} as $hookarr)
		{
			if (function_exists($hookarr[0]))
			{
				call_user_func( $hookarr[0], (isset($hookarr[1]) ? explode(',',$hookarr[1]) : null) );
			}
		}
	}
}

callHooks('PRE');

if (method_exists($c, $action))
{
	$output = $c->$action();
}

callHooks('PST');

echo $output;

//echo memory_get_peak_usage();
?>
