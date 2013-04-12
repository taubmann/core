<?php

session_start();
$projectName = preg_replace('/\W/', '', $_GET['project']);

$level = ((substr(basename(__DIR__),0,1)!=='_') ? 1 : 2);
if (!$_SESSION[$projectName]['root'] >= $level) exit('you are not allowed to access this Service!');

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

$backend = dirname(dirname(__DIR__));
$HTML = '';

