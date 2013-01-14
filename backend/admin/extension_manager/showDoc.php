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
* show Documentation-Files (or redirect to HTM(L)
*/

error_reporting(0);

// decode encoded filepath - useful for translation-services or robust links
// e.g. http://yourdomain.com/backend/admin/extension_manager/showDoc.php?&e=...
// your encoded path is shown in the page-source
if(isset($_GET['e'])) $_GET['file'] = base64_decode($_GET['e']);

$mime = array_pop(explode('.', $_GET['file']));
if($mime=='html' || $mime=='htm') {
	header('location: '  . $_GET['file']);
	exit();
}
if($mime!='md' && $mime!='txt') {
	exit('mimetype "'.$mime.'" is not allowed!');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation</title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<link rel="stylesheet" href="inc/styles/toc_helper.css" />
<link rel="stylesheet" href="inc/styles/syntax.css" />

<script src="inc/js/toc_helper.js"></script>
<script src="inc/js/highlight.pack.js"></script>

<style>
	body{font:.8em "Trebuchet MS", sans-serif;}
	img{border:0px none;}
	pre{padding:10px;margin:10px;border:1px dotted #bbb;}
	.footnotes{font-size:.8em;}
</style>
<style media="print">
	#innertoc{display:none;}
	a[href^="http"]:after{content:" (" attr(href) ") ";}
</style>
</head>
<body>
<?php

//echo '<!-- e='.trim(base64_encode($_GET['file']),'=')." -->\n";

$basePath = '';

// draw edit link
if(isset($_GET['edit_me']))
{
	$basePath = substr($_GET['file'], 0, (strlen($_GET['file'])-strlen($_GET['edit_me'])) );
	echo '<a id="edit_link" href="showFile.php?m='.$_GET['m'].'&project='.$_GET['project'].'&ext='.$_GET['ext'].'&file='.$_GET['edit_me'].'" title="edit"><img src="inc/styles/edit.png"></a>';
}

// draw "open in new window"-link (with special get-parameter)
if(!isset($_GET['e'])) {
	echo '&nbsp;&nbsp;<a title="open in new window" target="_blank" href="showDoc.php?e='.trim(base64_encode($_GET['file']),'=').'"><img src="inc/styles/externallink.png"></a>';
}

// see https://github.com/egil/php-markdown-extra-extended
include 'inc/markdown_extended.php';
$doc_path = dirname($_GET['file']).'/';
echo ( ($str=file_get_contents($_GET['file'])) ? MarkdownExtended($str) : 'no File found!' );
?>

<script>
	document.body.appendChild(createTOC());
	hljs.initHighlightingOnLoad();
</script>
</body>
</html>
