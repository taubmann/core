<?php

session_start();
$projectName = preg_replace('/\W/', '', $_GET['project']);
if($_SESSION[$projectName]['root']!==2) exit('no Rights to edit!');

function relativePath($from, $to, $ps = DIRECTORY_SEPARATOR)
{
	$arFrom = explode($ps, rtrim($from, $ps));
	$arTo = explode($ps, rtrim($to, $ps));
	while(count($arFrom) && count($arTo) && ($arFrom[0] == $arTo[0]))
	{
		array_shift($arFrom);
		array_shift($arTo);
	}
	return str_pad('', count($arFrom) * 3, '..'.$ps).implode($ps, $arTo);
}

$backend = dirname(dirname(dirname(__FILE__)));
$HTML = '';

