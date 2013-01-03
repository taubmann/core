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
session_start();
$projectName = preg_replace('/[^-\w]/', '', $_GET['project']);
if($_SESSION[$projectName]['root']!==2) exit('no Rights to edit!');




$path = '../../inc/css/';

include 'helper.php';




$html = '';

foreach ($styles as $k => $v) {
	
	
	
	if(!file_exists($path.$k.'/jquery-ui.css')) {
		$html .= '<p style="color:red">' . $k . ' does not exist</p>';
		continue;
	}
	
	// get UI-Definitions from String $v and put them to $params
	parse_str($v, $params);
	
	// collect "additional" styles first
	$css = "\n";
	$css .= file_get_contents($path.'plugins/foldertree.css') . "\n";
	$css .= file_get_contents($path.'plugins/jquery.ui.selectmenu.css') . "\n";
	$css .= file_get_contents($path.'styles.css') . "\n";
	//$css .= file_get_contents('') . "\n";
	
	
	
	// generate output-string ( replacing placeholders with params)
	$str = file_get_contents($path.$k.'/jquery-ui.css') . strtr($css, $params);
	
	if(!isset($_GET['nocompress'])) {
		$str = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", '', $str); // remove comments
		$str = preg_replace('/(\\t|\\r|\\n)/','', $str);
	}
	// clear comments + blank-lines
	//$out = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", '', $out); // comments
	//$out = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $out); // blank lines
	
	
	// write css to file
	if(file_put_contents($path.$k.'/style.css', $str)){
		@chmod($path . $k . '/style.css', 0776);
		$html .= '<p><a target="_blank" href="'.$path.$k . '/style.css">'.$k .'/style.css</a> saved</p>';
	}else{
		$html .= '<p style="color:red">ERROR: <a target="_blank" href="'.$path.$k . '/style.css">'.$k .'/style.css</a> is not writable!!</p>';
	}
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
<title>css packer</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<style>
body{background: #eee;font:.9em "Trebuchet MS", sans-serif;}
a, a:visited{text-decoration:underline;color:#00f;}
</style>
</head>
<body>
	<a href="javascript:history.back()">back</a>
	<h2>CSS Packed</h2>
	
	<?php echo $html;?>
</body>

</html>
