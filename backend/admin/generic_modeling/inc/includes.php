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
************************************************************************************/

$backend = '../../';

$projectName = preg_replace('/\W/', '', $_GET['project']);

$level = ((substr(basename(dirname(dirname(__FILE__))),0,1)!=='_') ? 1 : 2);
if (!$_SESSION[$projectName]['root'] >= $level) exit('you are not allowed to access this Service!');


$ppath = realpath($backend . '../projects/' . strtolower($projectName)) . '/objects/';

$lang = $_SESSION[$projectName]['lang'];
$LL = array();
@include __DIR__ . '/locale/'.$lang.'.php';

/**
* 
*/
function L($str)
{
	global $LL;
	if(isset($LL[$str]))
	{
		return $LL[$str];
	}
	else
	{
		//file_put_contents(dirname(__FILE__).'/ll.txt', $str.PHP_EOL, FILE_APPEND);chmod(dirname(__FILE__).'/ll.txt',0777); // export all labels
		return str_replace('_',' ',$str);
	}
}
