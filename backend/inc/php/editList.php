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
// comments partly in german - sorry!
require 'header.php';

if (isset($_GET['action']) && $_GET['action']=='saveOrder' && isset($_POST['order']))
{
	
	$sort = array();
	foreach($_POST['order'] as $item)
	{
		$sort[ $item['name'] ] = $item['value'];
	}
	$_SESSION[$projectName]['sort'][$objectName] = $sort;
	exit( L('sort_saved') );
}

//$ppath = '../../../projects/' . strtolower($projectName);

?>
<!DOCTYPE html>
<html>
<head>
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript" src="../js/jquery-ui.js"></script>
<script type="text/javascript" src="../js/jquery.ui.touch-punch.js"></script>
<script type="text/javascript" src="../js/jquery.ui.selectmenu.js"></script>
<link rel="stylesheet" type="text/css" id="mainTheme" href="../css/<?php echo end($_SESSION[$projectName]['config']['theme'])?>/jquery-ui.css" />


<style type="text/css">
	body{font: .8em sans;}
	#avlist, #sortlist {border:1px solid #ccc; padding:15px 15px 15px 35px;}
	#avlist li, #sortlist li{clear:both;padding:10px;}
	#avlist span, #sortlist span{cursor:pointer;}
	.checks{float:right;margin-top:-8px;}
	.drag {float:left;margin-right:10px;}
	
	/* export-select */
	select{
		background:#fff;
		border:1px solid #ccc;
		font-size: 1.2em;
		-webkit-border-radius:5px;
		-moz-border-radius: 5px;
		border-radius: 5px;
		padding: 7px;
	}
</style>

<script>

<?php


echo "listItems = [];\n";
foreach($objects[$objectName]['col'] as $k => $v)
{
	echo 'listItems["'.$k.'"]="'.(isset($v['lang'][$lang]) ? $v['lang'][$lang]['label'] : $k).'";';
}

echo "\nsortBy = [];\n";

foreach($_SESSION[$projectName]['sort'][$objectName] as $k => $v)
{
	echo 'sortBy["'.$k.'"]="'.$v.'";';
}

?>

</script>

</head>
<body>

<?php
//
echo '
<button rel="disk" style="float:right" onclick="save()" title="'.L('save').'">'.L('save').'</button>

<button rel="tag" onclick="window.location=\'editLabels.php?projectName='.$projectName.'&objectName='.$objectName.'\'">'.L('Labels_in_List').'</button>
';
//<select onchange="exportAs(this.value)"><option value="">'.L('export_as').'</option><option value="csv">CSV</option><option value="xml">XML</option></select>
echo '
<hr style="clear:both" />

<div id="filterselect"></div>

<form id="mySortForm">
<h2>'.L('sort').'</h2>
<div>'.L('used').'</div>
<ol id="sortlist" class="connectedSortable">
</ol>
</form>
<div>'.L('available').'</div>
<ol id="avlist" class="connectedSortable">
</ol>
';

?>


<script>
var srtstr = '';

function exportAs(type)
{
	if(type.length>0) window.location = '../../crud.php?action=exportList&projectName=<?php echo $projectName;?>&objectName=<?php echo $objectName;?>&type='+type+'&sortby='+srtstr;
}

$(document).ready(function() {
	
	
	//ps = [ "<?php echo implode('","', $_SESSION[$projectName]['labels'][$objectName])?>" ];
	
	// name, label, 
	function liCode(nm, lbl, d) 
	{
		return '<li id="xx'+nm+'" class="ui-state-default ui-selectee">'+
		'<span class="drag ui-icon ui-icon-arrow-2-n-s"></span> '+
		'<div class="checks">'+
		'<label for="a'+nm+'"><?php echo L('ascending');?></label>'+
		'<input id="a'+nm+'" name="'+nm+'" type="radio" value="asc" '+
		(!d ? 'checked="checked" ':'')+'/> '+
		'<label for="d'+nm+'"><?php echo L('descending');?></label>'+
		'<input id="d'+nm+'" name="'+nm+'" type="radio" value="desc" '+
		(d ? 'checked="checked" ':'')+' /> '+
		'</div>' + 
		lbl+
		'</li>';
	}
	
	// active Fields
	var str = '';
	for(e in sortBy)
	{
		str += liCode( e, listItems[e], (sortBy[e]=='desc'?true:false) );
	}
	
	$('#sortlist').html(str);
	
	
	// available Fields
	str = '';
	for(e in listItems) {
		if(!sortBy[e]) {
			str += liCode(e, listItems[e], false);
		}
	}
	$('#avlist').html(str);
	
	
	// style Radio-Buttons 
	$('.checks').buttonset();
	
	
	$('button').each(function() {
			$(this).button( {icons:{ primary: 'ui-icon-'+$(this).attr('rel')}, text: (($(this).text()=='.')?false:true)});
	});
	
	
	$( "#sortlist, #avlist" ).sortable(
	{
		connectWith: '.connectedSortable',
		placeholder: 'ui-state-highlight',
		handle: 'span'
	})
	.disableSelection();
	
});

// save new sorting
function save() 
{
	var s = $('#mySortForm').serializeArray();
	if(s.length > 0)
	{
		$.post('<?php echo 'editList.php?action=saveOrder&projectName='.$projectName.'&objectName='.$objectName;?>', 
		{
			order: s
		},
		function(data)
		{
			
			parent.message(data);
			hasChanged = true;
		}
		);
	}
	else
	{
		alert("<?php echo L('one_sort_field_is_needed');?>");
	}
}

var hasChanged = false;
$(window).unload(function()
{
	if(hasChanged)
	{
		parent.getList();
	}
});

</script>

</body>
</html>
