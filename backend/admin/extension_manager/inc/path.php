<?php
//error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
error_reporting(0);
session_start();

// project-name
$projectName = preg_replace('/[^-\w]/', '', $_GET['project']);
if(!$_SESSION[$projectName]['root']) exit('no Rights to edit!');

// language
$lang = 'en';
if(isset($_SESSION[$projectName]['lang']))
{
	@include dirname(__FILE__) . '/locale/'.$_SESSION[$projectName]['lang'].'.php';
	$lang = $_SESSION[$projectName]['lang'];
}

// main-path
@$m = intval($_GET['m']);
$mainpaths = array(
	array(L('project_extensions'), 'project', '../../../projects/' . $projectName . '/extensions/'),
	array(L('global_extensions'), 'global', '../../extensions/'),
	array(L('wizards'), false, '../../wizards/'),
);
$mainpath = $mainpaths[$m];

// stop access if Extension is for Superadmins only!
if($_SESSION[$projectName]['root']==1 && file_exists($mainpath[2] . $_GET['ext'] .'/.superadmin')) exit('superadmin only');
