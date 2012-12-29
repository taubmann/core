<?php
/********************************************************************************
*  Copyright notice
*
*  (c) 2012 Christoph Taubmann (info@cms-kit.org)
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
$fileName = (($_GET['file']=='__model') ? '__model.php' : 'class.'.$_GET['file'].'.php');
$ppath = '../../../../projects/' . $projectName . '/objects/'.$fileName;

if(!$_SESSION[$projectName]['root']) exit('no Rights to edit!');
if(!file_exists('../../../wizards/syntax/src/ace.js')) exit('Syntax-Wizard is missing :-(');
if(!file_exists($ppath)) exit('File is missing ?:-(');


$saved = false;
if(isset($_POST['content'])) {
	file_put_contents($ppath, $_POST['content']);
	$saved = 'File saved!';
}

$content = file_get_contents($ppath);

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	
	<title><?php echo $_GET['file'];?></title>
	
	<link href="../../../wizards/syntax/inc/styles.css" rel="stylesheet" />
	<script src="../../../wizards/syntax/inc/scripts.js" type="text/javascript" charset="utf-8"></script>
	<script src="../../../wizards/syntax/src/ace.js" type="text/javascript" charset="utf-8"></script>
	<style>
		body, #helpdesk{ font-size:14px;background-color:#fff;color:#000;border:1px solid #000;}
	</style>
</head>
<body>

<form method="post" id="form" enctype="multipart/form-data" action="phprev.php?file=<?php echo $_GET['file'];?>">
	<input type="hidden" id="content" name="content" value="" />
</form>

<img src="../../../wizards/syntax/inc/img/help.png" id="bhelp" onclick="helpToggle()" title="help" />
<img src="../../../wizards/syntax/inc/img/save.png" id="bsave" onclick="save()" title="save" />

<?php
if($saved){ echo '<script>alert("'.$saved.'")</script>'; }// save-feedback
?>

<pre id="editor"><?php echo htmlentities($content);?></pre>

<div id="helpdesk" style="display:none">
	<div>
		<img style="float:right" src="../../../wizards/syntax/inc/img/close.png" onclick="helpToggle()" />
		<span style="font-size:10px" id="stats"></span>
	</div>
	<?php echo file_get_contents('../../../wizards/syntax/inc/help.html');?>
</div>

<script>

var editor;
var mode = 'php';
window.onload = function()
{
	editor = ace.edit('editor');
	editor.setTheme('ace/theme/chrome');
	editor.getSession().setMode("ace/mode/php");
	
	document.getElementById('editor').style.fontSize = '14px';// Font size
	editor.getSession().setTabSize(4);// Tab size:
	editor.getSession().setUseSoftTabs(true);// Use soft tabs:
};

function save()
{
	document.getElementById('content').value = editor.getSession().getValue();
	document.getElementById('form').submit();
}

</script>

</body>
</html>

