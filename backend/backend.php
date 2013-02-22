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
* Backend-Functions
*/
session_start();
error_reporting(0);
session_regenerate_id();

header ('Cache-Control: no-cache,must-revalidate', true);


require_once 'inc/php/functions.php';

// fix/sanitize GET-Parameter
foreach($_GET as $k=>$v){  $_GET[$k]  = preg_replace('/\W/', '', $v); }
foreach($_POST as $k=>$v){ $_POST[$k] = preg_replace('/\W/', '', $v); }
$projectName = preg_replace('/\W/', '', $_REQUEST['project']);

if (isset($_POST['project']))
{
	unset($_SESSION[$projectName]);
}

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
	
	// define/reset the main Session-Array
	$_SESSION[$projectName] = array	(
										'special'	=> array(), 
										'lang'		=> $_POST['lang'], 
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
	
	$_SESSION[$projectName]['template'] = intval($_POST['template']);
	
	// collect some configuration-Settings from "cms"-Extensions
	$configs = array (	'extensions/cms/config/config.php',
						$ppath . '/extensions/cms/config/config.php'
					);
	
	foreach ($configs as $cf)
	{
		if ($s = file_get_contents($cf))
		{
			$arr = explode('EOD', $s);
			if(count($arr)==3 && $j = json_decode($arr[1], true)) 
			{
				$config = array_merge_recursive($config, $j);
			}
		}
	}
	$_SESSION[$projectName]['config'] = $config;
	
	
	// check for Super-Root
	if ( (
			crpt($_POST['pass'], $super[0]) === $super[1] && 
			(
				in_array($_SERVER['SERVER_NAME'], array('localhost','127.0.0.1')) ||
				isset($_SESSION['captcha_answer']) && $_POST['name']==$_SESSION['captcha_answer']
			)
		 ) || 
		 end($config['autolog']) == 1
		)
	{
		// define User as Super-Root (==2) and put some infs into the user-array 
		$_SESSION[$projectName]['root'] = 2;
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
		echo $hook;
		if (function_exists($hook))
		{
			call_user_func($hook);
		}
	}
	
	// collect Admin-Wizards
	if (isset($_SESSION[$projectName]['root']))
	{
		$_SESSION[$projectName]['adminfolders'] = array();
		foreach(glob('admin/*', GLOB_ONLYDIR) as $f)
		{
			$f = basename($f);
			// Admin-Wizards beginning with "_" are for Super-Admins only
			if($_SESSION[$projectName]['root']>1 || substr($f,0,1) != '_')
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
	// login successful
	else
	{
		
		$_SESSION[$projectName]['objects'] = $objects;
		$_SESSION[$projectName]['loginTime'] = time();
		
		// create Check to prevent Session-Hijacking in crud.php
		$_SESSION[$projectName]['user_agent'] = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . Configuration::$DB_PASSWORD[0]);
		// refresh this Page to kill POST-Variables
		header('location: backend.php?project=' . $projectName);
	}
	
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
@include('inc/locale/'.$lang.'.php');

// define some language-labels for JS (v.a. für Wizards)
$tmp = array();
$jsLangLabelArr = array('saved','connected','new_entry','entry_created','transfer','transferred','delete_entry','deleted','error');

foreach ($jsLangLabelArr as $v)
{
	$tmp[] = $v.':\''.L($v).'\'';
}
$jsLangLabels = implode(',', $tmp);


// collect Objects
foreach ($objects as $ok => $ov)
{
	$option = array(
					'name' => $ok, 
					'label' => ((isset($ov->lang)&&isset($ov->lang->{$lang})) ? $ov->lang->{$lang} : $ok), 
					'htype' => (isset($ov->ttype) ? $ov->ttype : '')
				);
	
	// collect Objects in Tag-Groups
	if (isset($ov->tags->{$lang}))
	{
		foreach($ov->tags->{$lang} as $t)
		{
			if(!isset($objectOptions[$t[0]])) $objectOptions[$t[0]]=array();
			$objectOptions[$t[0]][] = $option;
		}
		
	}
	else
	{
		$objectOptions[0][] = $option;
	}
	
		
	// define Field-Labels (Fallback id)
	if ( !isset($_SESSION[$projectName]['labels'][$ok]) )
	{
		$_SESSION[$projectName]['labels'][$ok] = array('id');// default
		foreach ($ov->col as $fk => $fv)
		{
			if (substr($fk,-2) != 'id' && (preg_match('/VARCHAR|TEXT/is', $fv->type) == 1))
			{
				$_SESSION[$projectName]['labels'][$ok] = array($fk);
				break;
			}
		}
	}
	
	if ( !isset($_SESSION[$projectName]['sort'][$ok]) )
	{
		$_SESSION[$projectName]['sort'][$ok] = array('id' => 'asc');
	}
	
}

ksort($objectOptions, SORT_LOCALE_STRING);

//print_r($objectOptions);

$user_wizards = array_merge($_SESSION[$projectName]['config']['wizards'], $_SESSION[$projectName]['special']['user']['wizards']);

// load Template
include $_SESSION[$projectName]['config']['backend_templates'][$_SESSION[$projectName]['template']];

?>
