<?php
require 'header.php';

require_once($ppath . '/objects/class.'.$objectName.'.php');

$treeType = $objects->{$objectName}->ttype;

$js_params   = '../../crud.php?action=getTreeList&projectName='.$projectName.'&objectName='.$objectName.'&objectId=0&tType='.$treeType.'&limit=20&offset=';
$url_params = '?projectName='.$projectName.'&objectName='.$objectName;

$message = '';

if ( !empty($_POST['pid']) && !empty($_POST['cid']) )
{
	$action = 'Add' . $objectName;
	
	// first we must check if the connection already exists
	$query = 	($treeType == 'Tree') ?
				'SELECT `treeparentid` AS i FROM `baum` WHERE `id` = ?' :
				'SELECT `pid` AS i FROM `grafmatrix` WHERE `id` = ?';
	
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
	$bid = (!empty($_POST['bid']))?$_POST['bid']:false;
	
	// create the Objects
	$o1 = new $objectName();
	$parent = $o1->Get($_POST['pid']);
	
	$o2 = new $objectName();
	$child  = $o2->Get($_POST['cid']);
	
	// no recursion detected
	if($parent->$action($child, $bid))
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
	
	#waiter {
		position:absolute;
		top:40%;left:50%;
		padding: 20px;
		margin-left: -45px;
		z-index: 5;
		width:70px;
		background: #fff;
		text-align: center;
		border-radius: 15px;
		display: none;
		color: #000;
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

</script>

<div id="waiter">
	<img src="../css/spinner.gif" /><br />
	<?php echo L('please_wait')?>
</div>

</body>
</html>
