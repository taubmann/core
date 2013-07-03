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

$LL = array();
$headstr = (isset($_GET['nocompress']) ? 'Scripts concatenated' : 'Scripts packed');
$headline = '// AUTO-CREATED FILE (build at ' . date('d.m.Y H:i:s', time()) . ") do not edit!\n";
$links = '';
if(empty($_GET['lang'])) $_GET['lang'] = 'en';

$paths = getPaths('js');



// loop all templates name => array(filepath, compress_code, translate_labels, restore_commenthead)
foreach ($paths as $templatename => $arr)
{
	// try to get language-labels
	@include($arr['base'] . '/locale/'.$_GET['lang'].'.php');
	
	//
	$str = $headline;
	foreach ($arr['src'] as $src)
	{
		$p = str_replace(array('TEMPLATE', 'BACKEND'), array($arr['base'], $backend), $src[0]);
		if(!file_exists($p))
		{
			exit('<p>' . $p . ' is missing!</p>');
		}
		$s = file_get_contents($p);
		$str .= ((!$src[1] || !empty($_GET['nocompress'])) ? $s : compress($s, $src[3]));
		
		
		// translate Language-Calls found in the Code (the L-Function)
		if($src[2] && $LL)
		{
			$str = preg_replace("/_\('(\w+)'\)/e", "L('\\1')", $str);
		}
		$str .= "\n";
		
	}
	$o = str_replace(array('TEMPLATE', 'BACKEND', 'LANG'), array($arr['base'], $backend, $_GET['lang']), $arr['out']);
	
	putFile($templatename, $o, $str);
	
}// loop all templates END


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
	
	<?php echo $links;?>
</body>
</html>
