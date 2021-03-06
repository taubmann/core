<?php
/**
* first Processings for various Files
* 
*/
session_start();

//error_reporting(0);
error_reporting(E_ALL ^ E_NOTICE);

// fix/sanitize GET-Parameter 
foreach ($_GET as $k=>$v){ $_GET[str_replace('amp;','',$k)] = preg_replace('/\W/', '', $v); }

// additional securing of Variables (probably via filter)??
$projectName = $_GET['projectName'];
$objectName = $_GET['objectName'];

// prevent Session-Hijacking
if( !isset($_SESSION[$projectName]['user_fingerprint']) || $_SESSION[$projectName]['user_fingerprint'] != md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . date('z'))) 
{
	exit('Session expired or IP has changed');
}

// abort the Script if access is not allowed
if (!isset($_SESSION[$projectName]['objects'])) exit('not active');
if (!isset($_SESSION[$projectName]['objects'][$objectName])) exit('Object is not accessible!');

// absolute Project-Path
$ppath = realpath( __DIR__ . '/../../../projects/' . $projectName );

// accessible Objects 
$objects = $_SESSION[$projectName]['objects'];
$db = intval($objects[$objectName]['db']);
$theme = end($_SESSION[$projectName]['config']['theme']);


/**
* translate Strings (todo: move the lang-arrays somwhere else...)
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
		// uncomment to add all untranslated Labels to "ll.txt" (Directory must be writable!)
		// $D = dirname(realpath($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']));
		// file_put_contents(__DIR__ . '/ll.txt', $str.'<<<'.$_SERVER['PHP_SELF'].'>>>'. PHP_EOL, FILE_APPEND);
		// chmod(__DIR__ . '/ll.txt',0777);
		return str_replace('_', ' ', $str);	
	}
}

