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
 * Javascript concatenation + compression + translation
 * */
session_start();
error_reporting(0);
$projectName = preg_replace('/\W/', '', $_GET['project']);
if($_SESSION[$projectName]['root']!==2) exit('no Rights to edit!');

$path     = '../../inc/';
$js_path  = $path . 'js/';
$out_path = $path . 'locale/';

if(!$_GET['lang'] || !file_exists($path.'locale/'.$_GET['lang'].'.php'))
{
	$_GET['lang'] = 'en';
}

$LL = array();
include($path.'locale/'.$_GET['lang'].'.php');

function L($str)
{
	global $LL;
	$str = trim($str);
	return ( isset($LL[$str]) ? ('\''.$LL[$str].'\'') : ('\'' . str_replace('_', ' ', $str) . '\'') );
}


//				array(filename, compress, translate, commenthead)
$src = array(
			
			// Desktop-Version
			array(
				array('jquery.maskedinput.min.js', false, false, true),
				array('dev/cmskit.core.js', true, true, true),
				array('dev/cmskit.desktop.js', true, true, false),
				array('jquery.ui.selectmenu.js', true, false, true),
				array('dev/jquery.autosize.min.js', false, false, true),
				array('jquery.foldertree.js', true, false, true),
				array('dev/jquery-ui-timepicker.js', true, true, true),
			  ),
			
			// Mobile-Version
			array(
				array('dev/cmskit.core.js', false, true, true),
				array('dev/cmskit.mobile.js', false, true, false),
				array('jquery.ui.selectmenu.js', true, false, true),
				array('jquery.foldertree.js', true, false, true),
				array('dev/mobiscroll.min.js', false, false, true),
				array('jquery.ui.touchpunch.js',false, false, true),
			  ),
		);


$c = 0;
foreach ($src as $aa)
{
	$out = '// AUTO-CREATED FILE (created at '.time().") do not edit!\n\n";
	
	foreach($aa as $a)
	{
		
		$str = file_get_contents($js_path . $a[0]);
		
		// compress (sort of)
		if ($a[1])
		{
			//grab the first comment-block
			$comment = array_shift(explode('*/', $str));
			
			$str = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", '', $str); // remove comments
			
			if(!isset($_GET['nocompress'])) {
				$str = preg_replace('/(\\t|\\r|\\n)/','', $str); // remove tabs + line-feeds ( agressive Method ! )
			}
			//// temporary Methods ( less agressive )
			//// $str = preg_replace('/(\\t)/','', $str); // remove only the tabs
			//// $str = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", $str); // remove blank lines
			
			// replace multiple blanks with one
			$str = preg_replace('/( )+/', ' ', $str);
			
			// replace blanks/line-feeds between some characters
			$str = str_replace(
								array(	' = ',	') {',	'( ',	' )',	': ',	"}\n}",	";\n}"	), 
								array(	'=',	'){',	'(',	')',	':',	'}}',	';}'	), 
								$str);
			
			// put the previously grabbed comment at the top
			if($a[3]) {
				$str = "\n" . $comment . "*/\n\n" . $str;
			}
		}
		
		// translate Languge-Calls found in the Code (the L-Word)
		if($a[2] && $LL)
		{
			$str = preg_replace("/_\('(\w+)'\)/e", "L('\\1')", $str);
		}
		
		$out .= $str . "\n";
	}
	
	if(file_put_contents($out_path.$_GET['lang'].$c.'.js', $out))
	{
		chmod($out_path.$_GET['lang'].$c.'.js', 0777);
	}
	
	else
	{
		exit('File could not be written!!!');
	}
	$c++;
}

	
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>JS-Packer</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<style>
body{background: #eee;font:.9em "Trebuchet MS", sans-serif;}
a, a:visited{text-decoration:underline;color:#00f;}
</style>
</head>
<body>
	<a href="javascript:history.back()">back</a>
	<h2>JS Packed</h2>
	<p>Labels were translated to: "<?php echo $_GET['lang'];?>".</p>
	<p>Desktop: <a target="_blank" href="<?php echo $out_path.$_GET['lang'];?>0.js">File</a></p>
	<p>Mobile:  <a target="_blank" href="<?php echo $out_path.$_GET['lang'];?>1.js">File</a></p>
</body>
</html>
