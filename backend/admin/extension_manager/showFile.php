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
 * Show/Edit the content of a File (depends on wizards/syntax)
 * */


require '../../inc/php/functions.php';
require 'inc/path.php';

if(!file_exists('../../wizards/syntax/src/ace.js')) exit('Syntax-Wizard is missing :-(');

$file = (isset($_GET['int']) ? '../..' : '../../../projects/'.$_GET['project']) . '/extensions/'.$_GET['ext'].'/'.$_GET['file'];

// check if the File is inside a Folder called "ext"
/*
if( !in_array('extensions', explode(DIRECTORY_SEPARATOR, realpath(dirname($file)))) ) {
	exit('file is not located in a Extension-Folder!');
}*/

$file = $mainpath[2] . $_GET['ext'] . '/' . $_GET['file'];

$mime = array_pop(explode('.', $_GET['file']));

// allowed modes + translation mime => ace-mode (see: syntax/src/mode-xxx.js)
$mode = array(
	'js' => 'javascript',
	'php' => 'php',
	'md' => 'markdown',
	'css' => 'css',
	'html' => 'html',
	'htm' => 'html',
	'sql' => 'sql',
	'json' => 'json',
	'xml' => 'xml',
	'txt' => 'markdown'
);

$saved = false;

if(file_exists($file)) {
	$content = utf8_decode(file_get_contents($file));
	$canSave = is_writable($file);
}else {
	exit('File "'.$file.'" not found!');
}
;?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	
	<title>Code-Editor</title>
	
	<link href="../../wizards/syntax/inc/styles.css" rel="stylesheet" />
	<script src="../../wizards/syntax/inc/scripts.js" type="text/javascript" charset="utf-8"></script>
	<script src="../../inc/js/jquery.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="../../wizards/syntax/src/ace.js" type="text/javascript" charset="utf-8"></script>
	<style>
		body, #helpdesk{ font-size:14px;background-color:#fff;color:#000;border:1px solid #000;}
	</style>
	
</head>
<body>

<img src="../../wizards/syntax/inc/img/help.png" id="bhelp" onclick="helpToggle()" title="help" />

<?php
$readonly = '';
// draw save-button only if allowed (project-folder OR super-root)
if($m < 1 || $_SESSION[$projectName]['root'] == 2)
{
	// draw active save-button if file is writable or to create one
	if($canSave || $_GET['create']) {
		echo '<img src="../../wizards/syntax/inc/img/save.png" id="bsave" onclick="save()" title="'.L('save').'" />';
	}else {
		echo '<img src="../../wizards/syntax/inc/img/nosave.png" id="bsave" title="'.L('file_is_not_writable').'" />';
		$readonly = 'editor.setReadOnly(true);';
	}
}
else
{
	$readonly = 'editor.setReadOnly(true);';
}

?>

<pre id="editor"><?php echo htmlentities($content);?></pre>
<div id="overlay" style="display:none;" onclick="$('#overlay').hide()"></div>
<div id="helpdesk" style="display:none">
	<div>
		<img style="float:right" src="../../wizards/syntax/inc/img/close.png" onclick="helpToggle()" />
		<span style="font-size:10px" id="stats"></span>
	</div>
	<?php echo file_get_contents('../../wizards/syntax/inc/help.html');?>
</div>

<script>

var editor;
var mode = '<?php echo $mode[$mime];?>';

window.onload = function() {
	editor = ace.edit('editor');
	editor.setTheme("ace/theme/chrome");
	editor.getSession().setMode("ace/mode/<?php echo $mode[$mime];?>");
	
	// Font size
	document.getElementById('editor').style.fontSize = '14px';
	// Tab size:
	editor.getSession().setTabSize(4);
	// Use soft tabs:
	editor.getSession().setUseSoftTabs(true);
	// shortcut-saving
	var commands = editor.commands;
	commands.addCommand({
		name: "save",
		bindKey: {win: "Ctrl-S", mac: "Command-S"},
		exec: function() {save()}
	});
	
	<?php echo $readonly;?>
	
};

save = function() {
	var contents = editor.getSession().getValue();
	var url = "showFileSave.php?m=<?php echo $m;?>&project=<?php echo $_GET['project'];?>&ext=<?php echo $_GET['ext'];?>&file=<?php echo $_GET['file'];?>";
	$.post(url, 
			{content: contents },
			function(data) {
					// show return
					$('#overlay').html(data);
					$('#overlay').show();
					window.setTimeout(function(){$('#overlay').hide()},3000);
			}
	);
};


</script>

</body>
</html>
