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
* CSS concatenation + compression + theming
*/

include '../header.php';
include 'helper.php';

$headstr = (isset($_GET['nocompress']) ? 'Stylesheets concatenated' : 'Stylesheets packed');
$headline = '// AUTO-CREATED FILE (build at ' . date('d.m.Y H:i:s', time()) . ") do not edit!\n";
$links = '';


// grab the parameters of all jQuery-UI - Styles
$uiFolders = glob($backend.'/inc/css/*', GLOB_ONLYDIR);
$styles = array();
foreach ($uiFolders as $uiFolder)
{
	if (@$paramstr = file_get_contents($uiFolder.'/parameter.txt'))
	{
		parse_str($paramstr, $styles[basename($uiFolder)]);
	}
}

$paths = getPaths('css');

// loop all Templates: name => array(filepath, compress_code)
foreach ($paths as $templatename => $arr)
{
	$str = $headline;
	foreach ($arr['src'] as $src)
	{
		$p = str_replace(array('TEMPLATE', 'BACKEND'), array($arr['base'], $backend), $src[0]);
		if(!file_exists($p))
		{
			exit('<p>' . $p . ' is missing!</p>');
		}
		
		$s = file_get_contents($p);
		
		// compress string if active
		$str .= ( (!$src[1] || !empty($_GET['nocompress'])) ? $s : compress($s, true));
	}
	
	
	// should we replace Placeholders with UI-Values?
	if ($arr['lessify'])
	{
		foreach ($styles as $k => $v)
		{
			$o = str_replace(array('TEMPLATE', 'BACKEND', 'UI'), array($arr['base'], $backend, $k), $arr['out']);
			
			// build relative path pointing to the UI-Directory
			$v['BASEPATH'] = relativePath(dirname($o), $backend.'/inc/css/'.$k);
			
			// save File with Replacements
			putFile($templatename, $o, strtr($str, $v));
		}
	}
	else
	{
		$o = str_replace(array('TEMPLATE', 'BACKEND'), array($arr['base'], $backend), $arr['out']);
		putFile($templatename, $o, $str);
	}
	$links .= '<hr />';
	
}// loop all templates END


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
	<h2><?php echo $headstr;?></h2>
	
	<?php echo $links;?>
</body>

</html>
