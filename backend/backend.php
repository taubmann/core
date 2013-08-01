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
* Backend-Functions
*/
session_start();
//error_reporting(0);
error_reporting(E_ALL);
session_regenerate_id();


// fix/sanitize GET/POST/(+1 REQUEST)-Parameter
foreach($_GET as $k=>$v){  $_GET[$k]  = preg_replace('/\W/', '', $v); }
foreach($_POST as $k=>$v){ $_POST[$k] = preg_replace('/\W,@/', '', $v); }
$projectName = preg_replace('/\W/', '', $_REQUEST['project']);

// reset SESSION if a Login is detected
if (isset($_POST['project']))
{
	unset($_SESSION[$projectName]);
}

require_once 'inc/php/functions.php';

$ppath = '../projects/' . $projectName;

// back to Login 
if (!file_exists($ppath . '/objects/__database.php'))
{
	header('location: index.php?error=project_unknown');
}


// start the Verification-Process
if (!isset($_SESSION[$projectName]) )
{
	//echo $projectName;
	
	// load the Credentials
	require_once 'inc/super.php';
	
	// set the Check-Variable to false
	$log = false;
	$tmp = explode(',', $_POST['client']);
	// define/reset the main Session-Array
	$_SESSION[$projectName] = array	(
										'special'	=> array(), 
										'lang'		=> $_POST['lang'], 
										'client' => array(
															'width'			=> array_shift($tmp),
															'height'		=> array_shift($tmp),
															'capabilities'	=> $tmp
														 ), 
										'settings'	=> '{}', 
										'sort'		=> array(),
										'fields'	=> array()
									);
	
	// load Model + Database
	require_once $ppath . '/objects/__model.php';
	require_once $ppath . '/objects/__database.php';
	
	// Array containing Hook-Names to be processed (should be filled in hooks.php)
	$loginHooks = array();
	include_once 'extensions/cms/hooks.php';
	include_once $ppath . '/extensions/cms/hooks.php';
	
	
	
	
	
	// collect global Backend-Templates from  the Directory
	$templateFolders = glob('templates/*', GLOB_ONLYDIR);
	$config['templates'] = array();
	foreach ($templateFolders as $templatePath)
	{
		if (file_exists($templatePath.'/backend.php'))
		{
			$config['templates'][basename($templatePath)] = $templatePath;
		}
	}
	
	// collect some configuration-Settings from "cms"-Extensions
	$configFiles = array (	
						'extensions/cms/config/config.php',
						$ppath . '/extensions/cms/config/config.php'
					 );
	// load config-string
	foreach ($configFiles as $configFile)
	{
		if ($s = file_get_contents($configFile))
		{
			$arr = explode('EOD', $s);
			if (count($arr) == 3 && $j = json_decode($arr[1], true))
			{
				$config = array_merge_recursive($config, $j);
				// print_r($config);exit(); // test if config is merged correctly (swap $configFiles above)
			}
		}
	}
	
	$_SESSION[$projectName]['config'] = $config;
	
	
	// check for Super-Root
	if ( (
			crpt($_POST['pass'], $super[0]) === $super[0].':'.$super[1] 
			&& 
			(
				in_array($_SERVER['SERVER_NAME'], array('localhost','127.0.0.1')) // no need for Captchas on localhost
				||
				isset($_SESSION['captcha_answer']) && $_POST['name'] == $_SESSION['captcha_answer']
			)
		 )
		 || 
		 end($config['autolog']) === 1 // login is disabled at all
		)
	{
		// define User as "Super-Root" and put some infos into the user-array 
		$_SESSION[$projectName]['root'] = md5($_SERVER['REMOTE_ADDR'] . $super[1]);
		$_SESSION[$projectName]['special']['user'] = 
		array	(
			'prename'	=> 'superroot',
			'lastname'	=> 'superroot',
			'profiles'	=> array(0 => 'superroot'),
			'id'		=> 0,
			'lastlogin'	=> 0,
			'logintime'	=> time(),
			'wizards' => array(),
			'fileaccess' => array(array(
				'driver'	=> 'LocalFileSystem',
				'path'		=> '',
				'tmbPath'	=> 'files/.tmb',
			))
		);
		
		$log = true;
		
	}
	
	// (try to) call Login-Hooks
	foreach ($loginHooks as $hook)
	{
		
		if (function_exists($hook))
		{
			call_user_func($hook);
		}
	}
	
	
	// collect Admin-Wizards
	if (isset($_SESSION[$projectName]['root']))
	{
		$_SESSION[$projectName]['adminfolders'] = array();
		foreach (glob('admin/*', GLOB_ONLYDIR) as $f)
		{
			$f = basename($f);
			// Admin-Wizards beginning with "_" are for Super-Admins only
			if ($_SESSION[$projectName]['root']>1 || substr($f,0,1) != '_')
			{
				$_SESSION[$projectName]['special']['user']['wizards'][] = 
				array	(
					'name' => $f,
					'url' => 'admin/' . $f . '/index.php?project=' . $projectName
				);
			}
		}
	}
	
	// login failed
	if (!$log)
	{
		unset($_SESSION[$projectName]);
		header('location: index.php?error=please_log_in&project=' . $projectName);
		exit();
	}
	// Login-Check was successful
	else
	{
		
		$_SESSION[$projectName]['objects'] = $objects;
		$_SESSION[$projectName]['loginTime'] = time();
		
		// create Check to prevent Session-Hijacking in crud.php
		$_SESSION[$projectName]['user_fingerprint'] = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . date('z'));
		
		// relocate this Page to kill $_POST
		header('location: backend.php?project=' . $projectName);
	}
	$_SESSION[$projectName]['template'] = array();
	unset($super);
	
} // Verification-Process END


// reset Captcha-Answer if exists
if (isset($_SESSION['captcha_answer'])) unset($_SESSION['captcha_answer']);


// Objects
$els = array();
$entries = array();
$objectOptions = array();
$objects = $_SESSION[$projectName]['objects'];
$lang = $_SESSION[$projectName]['lang'];

// load language-array (used by Function "L")
@include 'inc/locale/'.$lang.'.php';

// define some language-labels for JS (v.a. für Wizards)
$tmp = array();
$jsLangLabelArr = array('saved','connected','new_entry','entry_created','transfer','transferred','delete_entry','deleted','error');

foreach ($jsLangLabelArr as $v)
{
	$tmp[] = $v.':\''.L($v).'\'';
}
$jsLangLabels = implode(',', $tmp);


// collect Objects
foreach ($objects as $objectKey => $objectValues)
{
	$option = array(
					'name' => $objectKey, 
					'label' => ((isset($objectValues['lang']) && isset($objectValues['lang'][$lang])) ? $objectValues['lang'][$lang] : $objectKey), 
					'htype' => (isset($objectValues['ttype']) ? $objectValues['ttype'] : '')
				);
	
	// collect Objects in Tag-Groups
	if (isset($objectValues['tags'][$lang]))
	{
		foreach ($objectValues['tags'][$lang] as $t)
		{
			if ( !isset($objectOptions[$t[0]]) )
			{
				$objectOptions[$t[0]] = array();
			}
			$objectOptions[$t[0]][] = $option;
		}
		
	}
	else
	{
		$objectOptions[0][] = $option;
	}
	
	
	// define Field-Labels (Fallback id)
	
	$_SESSION[$projectName]['labels'][$objectKey] = array('id');// default
	foreach ($objectValues['col'] as $fieldKey => $fieldValues)
	{
		if (
			substr($fieldKey,-2) != 'id' && // ignore id-Fields
			!in_array(substr($fieldKey,0,2), array('__','c_','e_')) && // ignore Fields beginning with...
			strpos($fieldValues['type'], 'CHAR') // take only (Var)char-Fields
			)
		{
			$_SESSION[$projectName]['labels'][$objectKey] = array($fieldKey);
			break;
		}
	}
	
	// save available Backend-Templates for later use
	$_SESSION[$projectName]['templates'][$objectKey] = (isset($objectValues['templates']) ? explode(',', $objectValues['templates']) : $_SESSION[$projectName]['config']['template']);
	
	if ( !isset($_SESSION[$projectName]['sort'][$objectKey]) )
	{
		$_SESSION[$projectName]['sort'][$objectKey] = ($option['htype']=='Tree') ? array('treeleft' => 'asc') : array('id' => 'asc');
	}
	
}// collect Objects END

//????
ksort($objectOptions, SORT_LOCALE_STRING);

$user_wizards = array_merge($_SESSION[$projectName]['config']['wizards'], $_SESSION[$projectName]['special']['user']['wizards']);

$object = (!empty($_GET['object']) ? $_GET['object']: false);

// define actual Template. Fallback-Order: 1. GET, 2. SESSION, 3. default
$_SESSION[$projectName]['template'][$object] = (!empty($_GET['template']) ? $_GET['template'] : (isset($_SESSION[$projectName]['template'][$object]) ? $_SESSION[$projectName]['template'][$object] : end($_SESSION[$projectName]['config']['template'])));


//print_r($_SESSION[$projectName]['templates']);

// prevent caching of HTML (in addition to Meta-Tags)
header ('Cache-Control: no-cache,must-revalidate', true);

// load Template
include $_SESSION[$projectName]['config']['templates'][ $_SESSION[$projectName]['template'][$object] ] . '/backend.php';

?>
