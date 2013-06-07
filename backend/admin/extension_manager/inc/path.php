<?php
//error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
error_reporting(0);
session_start();

// Project-Name
$projectName = preg_replace('/[^-\w]/', '', $_GET['project']);
$level = ((substr(basename(dirname(__DIR__)),0,1)!=='_') ? 1 : 2);

if(!isset($_SESSION[$projectName]['root'])) exit();

$root = ($_SESSION[$projectName]['root'] == md5($_SERVER['REMOTE_ADDR'] . $super[1])) ? 2 : 1;

if ($root < $level) exit('you are not allowed to access this Folder!');

// Language
$lang = 'en';
if(isset($_SESSION[$projectName]['lang']))
{
	@include __DIR__ . '/locale/'.$_SESSION[$projectName]['lang'].'.php';
	$lang = $_SESSION[$projectName]['lang'];
}

// main-path
@$m = intval($_GET['m']);
$mainpaths = array(
	array( L('project_extensions'), 'project', '../../../projects/' . $projectName . '/extensions/', 1),
	array( L('global_extensions'), 'global', '../../extensions/', 2),
	array( L('wizards'), false, '../../wizards/', 2),
	array( L('admin_wizards'), false, '../../admin/', 2),
	array( L('backend_templates'), false, '../../templates/', 2),
);

$mainpath = $mainpaths[$m];

if($mainpath[3]>$root) exit();

// stop access if Extension is for Superadmins only!
if($root==1 && file_exists($mainpath[2] . $_GET['ext'] .'/.superadmin')) exit('access for super-admin only!');
