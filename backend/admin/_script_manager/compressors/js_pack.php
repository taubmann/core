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

/**
* Javascript concatenation + compression + translation
*/
include '../header.php';
include 'helper.php';

$path = $backend . '/inc/';
$js_path  = $path . 'js/';
$out_path = $path . 'locale/';
$relpath = relativePath(dirname(__FILE__),$path) . '/locale/';

$headstr = 'Javascript concatenated';

if(!$_GET['lang'] || !file_exists($path.'locale/'.$_GET['lang'].'.php'))
{
	$_GET['lang'] = 'en';
}

$LL = array();
include($path . 'locale/'.$_GET['lang'].'.php');

function L($str)
{
	global $LL;
	$str = trim($str);
	return ( isset($LL[$str]) ? ('\''.$LL[$str].'\'') : ('\'' . str_replace('_', ' ', $str) . '\'') );
}


//				array(filepath, compress_code, translate_labels, restore_commenthead)
$src = array(
			
			// Desktop-Version
			array(
				//array('jquery.maskedinput.min.js', false, false, true),
				array('../dev/js/cmskit.core.js', true, true, true),
				array('../dev/js/cmskit.desktop.js', true, true, false),
				//array($js_path.'jquery.ui.selectmenu.js', true, false, true),
				array('../dev/js/jquery.autosize.min.js', false, false, true),
				array($js_path.'jquery.foldertree.js', true, false, true),
				//array('dev/jquery-ui-timepicker.js', true, true, true),
				//array('jqCron.js', true, true, true),
			  ),
			
			// Mobile-Version
			array(
				array('../dev/js/cmskit.core.js', false, true, true),
				array('../dev/js/cmskit.mobile.js', false, true, false),
				//array('jquery.ui.selectmenu.js', true, false, true),
				array($js_path.'jquery.foldertree.js', true, false, true),
				array('../dev/js/mobiscroll.min.js', false, false, true),
				array($js_path.'jquery.ui.touchpunch.js',false, false, true),
				//array('jqCron.js', true, true, true),
			  ),
		);


$c = 0;
foreach ($src as $aa)
{
	$out = '// AUTO-CREATED FILE (created at '.date('d.m.Y H:i:s',time()).") do not edit!\n\n";
	
	foreach($aa as $a)
	{
		
		if(!$str = file_get_contents($a[0]))
		{
			exit($js_path . $a[0] . ' is missing!');
		}
		
		// compress (sort of)
		if ($a[1] && !isset($_GET['nocompress']))
		{
			$str = compress($str);
			$headstr = 'Javascript packed';
		}
		
		// translate Languge-Calls found in the Code (the L-Word)
		if($a[2] && $LL)
		{
			$str = preg_replace("/_\('(\w+)'\)/e", "L('\\1')", $str);
		}
		
		$out .= $str . "\n";
	}
	
	if (file_put_contents($out_path.$_GET['lang'].$c.'.js', $out))
	{
		chmod($out_path.$_GET['lang'].$c.'.js', 0766);
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
	<h2><?php echo $headstr;?></h2>
	<p>Labels were translated to: "<?php echo $_GET['lang'];?>".</p>
	
	<p>Desktop: <a target="_blank" href="<?php echo $relpath.$_GET['lang'];?>0.js">File</a></p>
	<p>Mobile:  <a target="_blank" href="<?php echo $relpath.$_GET['lang'];?>1.js">File</a></p>
</body>
</html>
