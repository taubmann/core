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

require 'header.php';

// save new Labels into the SESSION and exit
if(isset($_GET['action']) && $_GET['action']=='saveOrder' && isset($_POST['lbls']))
{
	$_SESSION[$projectName]['labels'][$objectName] = $_POST['lbls'];
	exit( L('labels_saved') );
}

include('../locale/'.$lang.'.php');


?>
<!DOCTYPE html>
<html>
<head>
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript" src="../js/jquery-ui.js"></script>
<script type="text/javascript" src="../js/jquery.ui.touch-punch.js"></script>
<link rel="stylesheet" type="text/css" id="mainTheme" href="../css/<?php echo end($_SESSION[$projectName]['config']['theme'])?>/jquery-ui.css" />


<style type="text/css">
	body{font: .8em sans;}
	#avlist, #sortlist {border:1px solid #ccc; padding:15px 15px 15px 35px;}
	#avlist li, #sortlist li{clear:both;padding:10px;}
	#avlist span, #sortlist span{cursor:pointer;}
	.drag {float:left;margin-right:10px;}
</style>
<script>

<?php

$listItems = array();
echo "listItems=[];\n";

foreach($_SESSION[$projectName]['objects']->$objectName->col as $k => $v)
{
	if(substr($k,-2) != 'id' && substr($k,-4) != 'sort' && (preg_match('/HIDDEN/is', $v->type) != 1))
	{
		echo 'listItems["'.$k.'"]="'.(isset($v->lang->{$lang}) ? baseLabel($v->lang->{$lang}) : $k) . "\";\n";
	}
}

echo "\nusedItems = ['". implode( "','", $_SESSION[$projectName]['labels'][$objectName] ) . "'];\n";

?>

</script>
</head>
<body>

<?php

echo '
<button style="float:right" onclick="save()" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" title="'.L('save').'"><span class="ui-button-icon-primary ui-icon ui-icon-disk"></span><span class="ui-button-text">'.L('save').'</span></button>
<hr style="clear:both" />
<form id="mySortForm">
<div>'.L('used').'</div>
<ol id="sortlist" class="connectedSortable"></ol>
</form>
<div>'.L('available').'</div>
<ol id="avlist" class="connectedSortable"></ol>';

?>

<script> 
var hasChanged = false;

$(document).ready(function() {
	
	function liCode(nm, lbl) {
		return '<li id="xx'+nm+'" class="ui-state-default ui-selectee"><span class="drag ui-icon ui-icon-arrow-2-n-s"></span><input type="hidden" name="'+nm+'" />'+lbl+'</li>';
	}
	
	var str = '';
	
	
	// choose Columns
	for(i=0,j=usedItems.length; i<j; ++i)
	{
		str += liCode(usedItems[i], listItems[ usedItems[i] ]);
	}
	
	$('#sortlist').html(str);
	
	// the Rest
	str = '';
	for(e in listItems) {
		if($.inArray(e, usedItems) == -1) {
			str += liCode(e, listItems[e]);
		}
	}
	$('#avlist').html(str);
	
	$( "#sortlist, #avlist" ).sortable({
		connectWith: '.connectedSortable',
		placeholder: 'ui-state-highlight',
		handle: 'span'
	}).disableSelection();
	
});

// Zustände serialisieren und sichern
function save() {
	var arr = $('#mySortForm').serializeArray(), toSave = [];
	
	if(arr.length > 0)
	{
		for(var i=0,j=arr.length; i<j; ++i){
			toSave.push(arr[i].name);
		}
		
		$.post(
			"<?php echo 'editLabels.php?action=saveOrder&projectName='.$projectName.'&objectName='.$objectName?>",
			{lbls: toSave},
			function(data) {
				parent.message(data);
				hasChanged = true;
			}
		);
	}
	else
	{
		alert('<?php echo L('one_label_field_is_needed');?>');
	}
}


$(window).unload(function()
{
	if(hasChanged)
	{
		//parent.location.reload()
		parent.getList();
	}
});



</script>

</body>
</html>
