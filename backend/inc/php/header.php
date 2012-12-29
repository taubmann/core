<?php
/* header for various Files
 * 
 * */
session_start();

//error_reporting(0);
error_reporting(E_ALL ^ E_NOTICE);

// fix/sanitize GET-Parameter 
foreach($_GET as $k=>$v){ $_GET[str_replace('amp;','',$k)] = preg_replace('/\W/', '', $v); }

// additional securing of Variables (probably via filter)??
$projectName = strtolower($_GET['projectName']);
$objectName = strtolower($_GET['objectName']);

// abort script if access is not allowed
if(!isset($_SESSION[$projectName]['objects'])) exit('not active');
if(!isset($_SESSION[$projectName]['objects']->$objectName)) exit('Object is not accessible!');

$ppath = realpath(dirname(__FILE__).'/../../../projects/'.$projectName);

$objects = $_SESSION[$projectName]['objects'];
$db = intval($objects->{$objectName}->db);
$theme = end($_SESSION[$projectName]['config']['theme']);

//require_once($ppath.'/objects/__configuration.php');

// Language-Labeling
$lang = $_SESSION[$projectName]['lang'];
$LL = array();
function L($str) {
	global $LL; return (isset($LL[$str]) ? $LL[$str] : str_replace('_', ' ', $str));
}
// clear special things (like tab-/accordionheadings and tooltips)
function baseLabel($str) {
	return array_pop(explode('||',array_pop(explode('--',array_shift(explode('##',$str))))));
}

// autoload of classes if not included
function object_autoloader($class) {
	global $ppath;
	$file = $ppath.'/objects/class.'.$class.'.php';
	if (file_exists($file)){ require_once ($file); }else{ exit('class '.$file.' not found!'); }
}
spl_autoload_register('object_autoloader', true);
