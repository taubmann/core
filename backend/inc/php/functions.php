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
 * some Global Functions
 * */

// cms-kit Release-Number (main.min)
$KITVERSION = 0.8;

/* 
 * encrypt Passwords
 */
function crpt($pass, $salt=false)
{
	// if not set get filemtime as salt
	$salt = ($salt ? md5($salt) : md5(filemtime(__FILE__)));
	
	if (defined('CRYPT_BLOWFISH') && CRYPT_BLOWFISH)
	{
		$salt = '$2a$07$' . substr($salt, 0, 22) . '$';
		return md5(crypt($pass, $salt));
	}else
	{
		return md5(md5(md5($salt . $pass)));
	}
}

/*
 * translate Strings if $str as key in $LL available
 * $LL should be loaded separately from Language-File
 */
if(!function_exists('L'))
{
	$LL = array();
	function L($str)
	{
		global $LL;
		$str = trim($str);
		return ( isset($LL[$str]) ? $LL[$str] : str_replace('_',' ',$str) );
	}
}
/*
 * Detect Browser-Language 
 * browserLang(array(language-files), default-language)
 * browserLang(array('dir/ar.php','dir/de.php','dir/en.php'), 'en');
 * */
function browserLang($file_arr, $default='en')
{
	$al = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
	$arr = array();
	// we are extracting language-names from something like "bla/blubb/en.php"
	foreach ($file_arr as $f) $arr[] = substr(basename($f), 0, 2);
	
	// Try to detect Primary language if several languages are accepted.
	foreach ($arr as $k)
	{
		if (strpos($al, $k)===0 || strpos($al, $k)!==false)
		{
			return $k;
		}
	}
	// Try to detect any language if not yet detected.
	foreach ($arr as $k)
	{
		if (preg_match("/[\[\( ]{$k}[;,_\-\)]/",$ua))
		{
			return $k;
		}
	}
	// Return default language if language is not yet detected.
	return $default;
}
