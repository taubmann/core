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
*/
// cms-kit Release-Number "main.min.patch" according to: Semantic Versioning 2.0.0-rc.2 (http://semver.org)
$KITVERSION = '0.9.0';


/**
* encrypt Passwords (using bcrypt if possible)
* @param string password
* @pram string salt-string
* @return string "salt:password-hash"
*/
function crpt ($pass, $salt=false )
{
	// create a new "random" Salt if not set
	if(!$salt) $salt = microtime(true);
	
	if (defined('CRYPT_BLOWFISH') && CRYPT_BLOWFISH)
	{
		// create the Salt activating bcrypt with 7 Rounds
		$msalt = '$2a$07$'.substr(md5($salt), 0, 22).'$';
		return $salt.':'.md5(crypt($pass, $msalt));
	}
	else
	{
		// Fallback to the much weaker MD5-Encryption (3 Rounds)
		// throw new Exception('bcrypt is not supported!');
		return $salt.':'.md5(md5(md5($salt . $pass)));
	}
}

/**
* translate Strings if $str as key in $LL available
* $LL should be loaded separately from Language-File!
*/
if (!function_exists('L'))
{
	function L($str)
	{
		global $LL;
		if (isset($LL) && isset($LL[$str]))
		{
			return $LL[$str];
		}
		else
		{
			// uncomment to add all untranslated Labels to "ll.txt" (Directory must be writable!)
			// $D = dirname(realpath($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']));
			// file_put_contents($D . '/ll.txt', $str.'<<<'.$_SERVER['PHP_SELF'].'>>>'. PHP_EOL, FILE_APPEND);
			// chmod($D . '/ll.txt',0777);
			return str_replace('_', ' ', $str);
		}
	}
}

/**
* Detect Browser-Language
* 
* @param mixed File-Array containing Translations
* @param string Default-Language
* @return preferred detected Language
*/
function browserLang($file_arr=array('en.php'), $default='en')
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
		if (preg_match("/[\[\( ]{$k}[;,_\-\)]/", $ua))
		{
			return $k;
		}
	}
	// Return default language if language is not yet detected.
	return $default;
}
