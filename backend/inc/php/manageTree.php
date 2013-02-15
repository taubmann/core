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
************************************************************************************/

require 'header.php';
require_once($ppath . '/objects/class.'.$objectName.'.php');

$treeType = $objects->{$objectName}->ttype;

// Reset Sort-Order to default
$_SESSION[$projectName]['sort'][$objectName] = ($treeType == 'Tree') ? array('treeleft'=>'asc') : array();


$js_params   = '../../crud.php?action=getTreeList&projectName='.$projectName.'&objectName='.$objectName.'&objectId=0&tType='.$treeType.'&limit=20&offset=';
$url_params = '?projectName='.$projectName.'&objectName='.$objectName;

$message = '';

if ( !empty($_POST['pid']) && !empty($_POST['cid']) )
{
	$action = 'Add' . $objectName;
	
	// first we must check if the connection already exists
	$query = 	($treeType == 'Tree') ?
				'SELECT `treeparentid` AS i FROM `'.$objectName.'` WHERE `id` = ?' :
				'SELECT `pid` AS i FROM `'.$objectName.'matrix` WHERE `id` = ?';
	
	$prepare = DB::instance($db)->prepare($query);
	$prepare->execute(array($_POST['cid']));
	while($row = $prepare->fetch())
	{
		if ($row->i == $_POST['pid'])
		{
			$action = 'Remove' . $objectName;
		}
	}
	// beneath ID
	//$bid = (!empty($_POST['bid']))?$_POST['bid']:false;
	
	// create the Objects
	$o1 = new $objectName();
	$parent = $o1->Get($_POST['pid']);
	
	$o2 = new $objectName();
	$child  = $o2->Get($_POST['cid']);
	
	
	if (!empty($_POST['bid']))
	{
		$o3 = new $objectName();
		$beneath  = $o3->Get($_POST['bid']);
	}
	else
	{
		$beneath = false;
	}
	//print_r($_POST);
	// no recursion detected
	if($parent->$action($child, $beneath))
	{
		$message = ($action == 'Remove'.$objectName) ? 
					'<script>parent.message("'.L('connection_removed').'")</script>' : 
					'<script>parent.message("'.L('connection_saved').'")</script>';
		// 
		// reload the Tree in Backend
		$message .= '<script>parent.getList()</script>';
	}
	else
	{
		$message = '<script>parent.message("'.L('recursion_detected').'", 1)</script>';
	}
	
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>edit Tree</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<script type="text/javascript" src="../js/jquery.min.js"></script>
	<script type="text/javascript" src="../js/jquery-ui.js"></script>
	<script type="text/javascript" src="../js/jquery.foldertree.js"></script>
	<link rel="stylesheet" type="text/css" href="../css/<?php echo $theme?>/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="../css/<?php echo $theme?>/style.css" />

	<style>
	body{font-family:sans-serif;}
	
	#savestat{color:green;}
	
	
	.treewrap {
		width: 330px;
		float: left;
		margin-left: 10px;
		overflow-x: hidden;
	}
	#formwrap {
		width: 200px;
	}
	#frm input {
		width: 80px;
		float:right;
	}
	#frm p {
		clear:both;
	}
	
	
	</style>
</head>
<body>
<div id="controls" class="ui-widget-header ui-corner-all">
	<button
		style="float:right"
		
		class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" 
		role="button" 
		aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-help"></span>
			<span class="ui-button-text"><?php echo L('Help')?></span>
	</button>
	<button
		onclick="window.location='editLabels.php<?php echo $url_params?>'" 
		class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" 
		role="button" 
		aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-tag"></span>
			<span class="ui-button-text"><?php echo L('List_Labels')?></span>
	</button>
	<button
		onclick="window.location='editList.php<?php echo $url_params?>'" 
		class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" 
		role="button" 
		aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-shuffle"></span>
			<span class="ui-button-text"><?php echo L('List_Sort')?></span>
	</button>
</div>

<div class="treewrap">
	<h3><?php echo L('Parent_Tree');?></h3>
	<div class="tree" id="pid"></div>
</div>

<div id="formwrap" class="treewrap">
	<h3><?php echo L('connect_Parent_Child');?></h3>
	<form id="frm" method="post" action="manageTree.php<?php echo $url_params?>" onsubmit="$('#waiter').show()">
		<p><input type="radio" onclick="toid='#i_pid'" name="s" checked="checked" /><input id="i_pid" type="text" name="pid" /><label><?php echo L('Parent_ID')?> *</label> </p>
		<p><input type="radio" onclick="toid='#i_bid'" name="s" /><input id="i_bid" type="text" name="bid" /><label><?php echo L('Beneath_ID')?></label>  </p>
		<p><input type="radio" onclick="toid='#i_cid'" name="s" /><input id="i_cid" type="text" name="cid" /><label><?php echo L('Child_ID')?> *</label>  </p>
		<p>
			<button
				type="submit" 
				class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" 
				role="button" 
				aria-disabled="false">
					<span class="ui-button-icon-primary ui-icon ui-icon-link"></span>
					<span class="ui-button-text"><?php echo L('connect');?></span>
			</button>
		</p>
		<p id="savestat"><?php echo $message?></p>
	</form>
</div>
<!--
<div class="treewrapsss">
	<h3><?php echo L('Child_Tree')?></h3>
	<div class="tree" id="cid"></div>
</div>
-->
<script>

// draw the Trees
var params = '<?php echo $js_params?>';
var toid = "#i_pid"
$(document).ready(function()
{
	$('.tree').folderTree({
		script: params,
		statCheck: function(target)
		{
			target.find('li>span').each(function(i)
			{
				// add the ID to the Label
				var i = $(this).data('id')
				if(i) {$(this).append(' - [ ID: '+i+' ]')}
				// set Click-Handler
				$(this).on('click', function(e) {
					$(this).css('background','#fff');
					//$('#input_'+$(this).parents('.tree').attr('id'))
					$(toid).val( $(e.target).data('id') );
				})
			})
		}
	});
	$('body').on({
		ajaxStart: function() {
			$(this).addClass('loading');
		},
		ajaxStop: function() {
			$(this).removeClass('loading');
		}
	});
});
</script>

<div class="wait"></div>
</body>
</html>
