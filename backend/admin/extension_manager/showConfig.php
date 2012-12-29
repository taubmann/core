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
/*
 * JSON-Editor based on: http://www.thomasfrank.se/json_editor.html
 * 
 * */
require '../../inc/php/functions.php';
require 'inc/path.php';

//../../wizards/jsoneditor/

$file = $mainpath[2] . $_GET['ext'] . '/config/' . $_GET['file'] . '.php';
$s = '';

if(file_exists($file))
{
	
	// save config-json to file
	if(isset($_POST['json']))
	{
		
		if(file_put_contents($file, "<?php\n\$config = <<<EOD\n" . trim($_POST['json']) . "\nEOD;\n;?>"))
		{
			exit('File saved!');
		}
		else
		{
			exit('File could not be saved!');
		}
	}
	
	// read file
	$a = explode('EOD', file_get_contents($file));
	if(count($a)==3 && $j=json_decode($a[1]))
	{
		$str = trim($a[1]);
	}
	else
	{
		exit('File is not valid!');
	}
}
else
{
	exit('File "'.$file.'" not found!');
}

$action = 'showConfig.php?m='.$_GET['m'].'&project='.$projectName.'&ext='.$_GET['ext'].'&file='.$_GET['file'];

//$LL = array();
//$lang = $_SESSION[$projectName]['lang'];
//@include '../../wizards/jsoneditor/locale/'.$lang.'.php';

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>JSON-Editor</title>
<script type="text/javascript" src="../../inc/js/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="../../wizards/jsoneditor/jsoneditor/jsoneditor.css">
<link rel="stylesheet" type="text/css" href="../../wizards/jsoneditor/add/add.css">
<script type="text/javascript" src="../../wizards/jsoneditor/jsoneditor/jsoneditor.js"></script>
	

	
</head>
<body>
<?php echo $s;?>
<button onclick="save()" class="jsoneditor-menu jsoneditor-addbuttons jsoneditor-save" id="save" title="Save"></button>
<div id="jsoneditor" style="width: 100%; height: 95%;"></div>

<script>
	
var obj = <?php echo $str;?>;
	

var container = document.getElementById("jsoneditor");
var editor = new JSONEditor(container);
//var str = parent.$('#'+parent.targetFieldId).val();
//if(str.length<2) str = '{}';
editor.set(obj);


// activate saving
<?php
if(!is_writable($file))
{
	echo "\n".'$("save").hide();';
}
?>
		

// save json
function save() {
	var json = editor.get();
	//alert(JSON.stringify(json, null, 2));
	// save JSON to parent Field
	//parent.$('#'+parent.targetFieldId).val(JSON.stringify(json));
	//parent.saveContent("<?php echo $_GET['objectId'];?>");
	//parent.$('#dialog2').dialog('close');
	$.post('<?php echo $action?>',
	{
		json: JSON.stringify(json, null, 2)
	},
	function(data)
	{
		alert(data)
	});
}

</script>
</body>
</html>
