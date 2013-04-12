<?php
/* header for various Files
 * 
 * */
session_start();

//error_reporting(0);
error_reporting(E_ALL ^ E_NOTICE);

// fix/sanitize GET-Parameter 
foreach ($_GET as $k=>$v){ $_GET[str_replace('amp;','',$k)] = preg_replace('/\W/', '', $v); }

// additional securing of Variables (probably via filter)??
$projectName = strtolower($_GET['projectName']);
$objectName = strtolower($_GET['objectName']);

// abort script if access is not allowed
if (!isset($_SESSION[$projectName]['objects'])) exit('not active');
if (!isset($_SESSION[$projectName]['objects']->$objectName)) exit('Object is not accessible!');

$ppath = realpath( __DIR__ . '/../../../projects/' . $projectName );

$objects = $_SESSION[$projectName]['objects'];
$db = intval($objects->{$objectName}->db);
$theme = end($_SESSION[$projectName]['config']['theme']);


/**
* translate Strings
*/
$lang = $_SESSION[$projectName]['lang'];
$LL = array();
include (dirname(__DIR__) . '/locale/'.$lang.'.php');
function L($str)
{
	global $LL;
	if(isset($LL[$str]))
	{
		return $LL[$str];
	}
	else
	{
		//file_put_contents(__DIR__ . '/ll.txt', $str.PHP_EOL, FILE_APPEND);chmod(__DIR__ . '/ll.txt',0777); // export all untranslated Labels
		return str_replace('_', ' ', $str);
	}
}

