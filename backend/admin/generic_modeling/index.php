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
session_start();


require 'inc/includes.php';
require '../../inc/php/collectExtensionInfos.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>cms-kit Generic Modeling</title>
<meta charset="utf-8" />
<link href="../../inc/css/<?php echo end($_SESSION[$projectName]['config']['theme'])?>/jquery-ui.css" rel="stylesheet" />
<link href="../../templates/default/css/packed_<?php echo end($_SESSION[$projectName]['config']['theme'])?>.css" rel="stylesheet" />

<style>
#filelist
{
	position: absolute;
	top: 50px;
	left: 5px;
	width:200px;
}
#fieldlist
{
	position: absolute;
	top: 0px;
	left: 0px;
	width: 400px;
	border: 1px solid #eee;
	padding: 10px;
}
.ui-icon, .label
{
	display: inline-block;
}
.label
{
	margin-left: 10px;
	width: 70%;
	height: 15px;
}
.ui-icon-trash, .ui-icon-pencil, .ui-icon-copy
{
	float: right;
}
#dialogbody label
{
	display: inline-block;
	width: 120px;
	font-weight: bold;
	border-bottom: 1px solid #ccc;
}
#dialogbody input, #dialogbody textarea, #dialogbody  select
{
	background: #fff;
	width: 400px;
	border: 1px solid #666;
	padding: 5px;
	font: 1.2em/120% Tahoma, Arial, sans-serif; color: navy;
}
#dialogbody iframe
{
	width: 100%;
	height: 490px;
	border: 0px none;
}

.dangerous {
	border: 2px solid #c00;
}
    
</style>


<script src="../../inc/js/jquery.min.js"></script>
<script>$.uiBackCompat = false;</script>
<script src="../../inc/js/jquery-ui.js"></script>

<script>if(!window.JSON){document.writeln('<script src="../../inc/js/json2.min.js"><\/script>')}</script>



<script type="text/javascript">
/* <![CDATA[ */
var model = {};
var disallowedFieldNames = [' ', '_', ' ', 'id'];

<?php
echo "var project = '".$projectName."', wizards = [];\n";

// available Wizards (backend/inc/php/collectExtensionInfos.php)
$embeds = collectExtensionInfos($projectName);

foreach($embeds['w'] as $k => $v){	echo  "wizards['$k'] = {" . implode(',', $v) . "}\n"; }// available Wizards

	

// these Types makes no Sense in a JSON-Model
$forbiddenTypes = array(
							'EXCLUDEDINTEGER',
							'EXCLUDEDVARCHAR',
							'EXCLUDEDTEXT',
							'BLOB',
							'MODEL',
						);

$datatypes = json_decode(file_get_contents('../../inc/js/rules/datatypes.json'), true);

echo 'var typeSelect = \'<select onchange="checkTypeSelect(this)" name="type">';
foreach($datatypes as $k => $v)
{
	if(!in_array($k, $forbiddenTypes))
	{
		echo '<option value="' . $k . '">' . addslashes(L($k)) . '</option>';
	}
	foreach($v['default'] as $dk=>$dv)
	{
		$ddefaultLabel[] = $dk;
	}
}
echo '</select>\';';


echo '
var ddefault=[];
';
$ddefaultLabel = array_unique($ddefaultLabel);
foreach($ddefaultLabel as $dl)
{
	if (isset($v['default'][$dl])) echo "ddefault['$dl']=['".L($dl)."','".$v['default'][$dl]."'];\n";
}

?>

$(function()
{
	
	$('body').on({
		ajaxStart: function() {
			$(this).addClass('loading');
		},
		ajaxStop: function() {
			$(this).removeClass('loading');
		}
	});
	
	// File-List
	$('#filelist').on('click', '.label', function()
	{
		$('#filelist li').removeClass('ui-selected');
		$(this).parent().addClass('ui-selected');
		loadModel($(this).parent().data('name'));
	});
	
	// File delete
	$('#filelist').on('click', '.ui-icon-trash', function()
	{
		var name = $(this).parent().data('name');
		var q = confirm('delete '+name+'?');
		if(q)
		{
			$.get('json_io.php',
			{
				action : 'delete',
				project : project,
				file : name
			}, 
			function (data)
			{
				alert(data);
			});
			$(this).parent().remove();
		}
	});
	
	// File copy
	$('#filelist').on('click', '.ui-icon-copy', function()
	{
		var name = $(this).parent().data('name');
		var nn = prompt('<?php echo L('enter_new_Name')?>', '');
		if(nn)
		{
			$.get('json_io.php',
			{
				action : 'dup',
				project : project,
				file : name,
				newfile : nn
			},
			function (data)
			{
				alert(data);
			});
			// get the outer HTML of the LI-Element
			var li = $(this).parent().clone().wrap('<div>').parent().html();
			
			// replace old with new Name and append it to the List
			var reg = new RegExp(name, 'g');
			$('#filelist').append($(li.replace(reg, nn)));
		}
	});
	
	// File add
	$('#addModelButton').on('click', function()
	{
		var n = prompt('modellname eingeben', '');
		
		if(n)
		{
			n = n.replace(' ','_').replace(/[^\d\w]/g, '').toLowerCase();
			
			$.get('json_io.php',
			{
				action : 'add',
				project : project,
				file : n
			},
			function(data)
			{
				if(data == 'ok')
				{
					$('#filelist').append($('<li class="ui-state-default ui-selectee" data-name="'+n+'"><span class="ui-icon ui-icon-trash"></span><span class="label">'+n+'</span></li>'));
				}
			});
		}
	});
	
	$('#getHelpButton').on('click', function()
	{
		$('#dialogbody').html('<iframe src="../extension_manager/showDoc.php?file=../../extensions/documentation/doc/<?php echo $lang?>/.generic_modeling.md"></iframe>');
		$('#dialog_SaveButton').hide();
		$('#dialog').dialog('open');
		
	});
	$('#gotoRestore').on('click', function()
	{
		window.location = 'restore.php?project='+project;
	});
	
	
	$('#dialog').dialog(
	{
		autoOpen: false,
		modal: true,
		width: 600,
		height: 650,
		close: function() {
			$('#dialogbody').html('');
			$('#dialog_SaveButton').show();
		},
		buttons: [
			{
				text: '<?php echo L('Save')?>',
				id: 'dialog_SaveButton',
				click: function()
				{
					var arr = $('#editform').serializeArray();
					
					var name = $('#editform').data('fieldname');
					for(var i=0,j=arr.length; i<j; ++i)
					{
						var n = arr[i]['name'];
						switch(n)
						{
							case 'type':
							case 'value':
							case 'comment':
								model[name][n] = esc(arr[i]['value']);
							break;
							
							
							case 'add':
							case 'tags':
								model[name][n] = toArr(arr[i]['value']);
							break;
							
							case 'lang':
								
								$.get('json_io.php',
								{
									action : 'process_label',
									project : project,
									str : JSON.stringify(toArr(arr[i]['value'])),
									file: 'x'
								}, 
								function (data)
								{
									model[name]['lang'] = JSON.parse(data);
								});
							break;
						}
					}
					$(this).dialog( "close" );
				}
			},
			{
				text: '<?php echo L('Close')?>',
				click: function()
				{
					$(this).dialog( 'close' );
				}
			}
		]
	});
	
});// ready end



// load a Model from File
function loadModel(name)
{
	$.get('json_io.php',
	{
		action : 'get',
		project : project,
		file : name
	}, 
	function (data)
	{
		showModel(data, name);
	});
};

//
function showModel(data, name)
{
	model = JSON.parse(data);
	
	var lstr = '<li class="ui-state-default ui-selectee" data-name="XX" id="col_XX"><span title="<?php echo L('delete_Field')?>" class="ui-icon ui-icon-trash"></span><span title="<?php echo L('edit_Field_properties')?>" class="ui-icon ui-icon-pencil"></span><span title="<?php echo L('drag_to_Sort')?>" class="ui-icon ui-icon-arrowthick-2-n-s"></span><span class="label">XX</span></li>';
	
	html = 	'<div id="colMid">'+
			
			'<span  style="float:right">' +
			'<button id="saveFieldButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" ' +
			'role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-disk"></span><span class="ui-button-text"><?php echo L('Save');?></span>'+
			'</button> '+
			//'<input type="checkbox" title="do not perform any DB-Update" id="no_db_update" />' +
			'</span>' +
			
			'<button id="showJsonButton" style="float:right" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" ' +
			'role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-disk"></span><span class="ui-button-text"><?php echo L('show_Code');?></span>'+
			'</button> '+
			
			'<button id="addFieldButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" ' +
			'role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-plus"></span><span class="ui-button-text"><?php echo L('new_Field');?></span>'+
			'</button>'+
			
			'<ul id="fieldUL" class="ilist rlist">';
	
	
	for(e in model)
	{
		html += lstr.replace(/XX/g, e);
	}
	
	html += '</ul></div>';
	
	
	$('#fieldlist').html(html);
	
	
	// add a Field
	$('#addFieldButton').on('click', function()
	{
		var n = prompt('<?php echo L('enter_Field_Name')?>', '');
		if(n)
		{
			
			n = n.replace(' ','_').replace(/[^\d\w]/g, '').toLowerCase();
			
			if(model[n])
			{
				alert('<?php echo L('Field_Name_already_exists')?>!');
				return;
			}
			
			if($.inArray(n, disallowedFieldNames) != -1)
			{
				alert('<?php echo L('Field_Name_not_allowed')?>!');
				return;
			}
			
			
			model[n] = {};
			model[n]['type'] = 'INTEGER';
			model[n]['lang'] = {};
			model[n]['value'] = '';
			model[n]['add'] = '';
			var li = $(lstr.replace(/XX/g, n));
			$('#fieldUL').append(li);
			$('#fieldUL').sortable('refresh');
		}
	});
	
	$('#saveFieldButton').on('click', function()
	{
		var str = JSON.stringify(model, null, '\t');
		
		var q = confirm('<?php echo L('save_Model_permanetly')?>?');
		if(q)
		{
			$.post('json_io.php?project='+project+'&action=save&file='+name,
			{
				json : str
			},
			function (data)
			{
				alert(data);
			});
		}
		
	});
	
	
	$('#showJsonButton').on('click', function()
	{
		var str = JSON.stringify(model, null, '\t');
		
		var html  = '<span style="float:right">' +
					'<button id="saveOnlyJson" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-state-error" ' +
					'role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-disk"></span><span class="ui-button-text"><?php echo L('save_Json');?> (<?php echo L('no_DB_Update');?>)</span>'+
					'</button>'+
					'<button id="replaceInDbModels" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-state-error" ' +
					'role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-scissors"></span><span class="ui-button-text"><?php echo L('replace_DB_Model_String');?></span>'+
					'</button>'+
					'</span>' +
					
					'<button id="loadStrButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" ' +
					'role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-arrowreturnthick-1-s"></span><span class="ui-button-text"><?php echo L('load_Json');?></span>'+
					'</button>'+
					
					'<p><textarea id="jsonField" style="width:95%;height:500px">'+str+'</textarea></p>'+
					
					//'<button id="clearBackups" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-state-error" ' +
					//'role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-trash"></span><span class="ui-button-text"><?php echo L('clear_Backups');?></span>'+
					//'</button>'+
					'';
		
		$('#dialogbody').html(html);
		
		$('#loadStrButton').on('click', function()
		{
			showModel($('#jsonField').val(), name);
			$('#dialog').dialog('close');
		});
		
		$('#saveOnlyJson').on('click', function()
		{
			var q1 = confirm('<?php echo L('save_only_the_Model_without_updating_the_Database')?>?');
			var q2 = confirm('<?php echo L('you_know_what_you_are_doing')?>?');
			if (q1 && q2)
			{
				$.post('json_io.php?project='+project+'&action=saveonlyjson&file='+name,
				{
					json : str
				},
				function (data)
				{
					alert(data);
				});
			}
		});
		
		$('#replaceInDbModels').on('click', function()
		{
			var sn = prompt('<?php echo L('insert_Needle')?>','//');
			var sr = prompt('<?php echo L('insert_Replacement')?>','');
			var ts = confirm('<?php echo L('run_a_Test_on_the_first_Entry')?>');
			if (sn && sr)
			{
				$.post('json_io.php?project='+project+'&action=dbreplace&file='+name,
				{
					test: (ts ? 1 : 2),
					needle: sn,
					replacement: sr,
					json: str
				},
				function (data)
				{
					alert(data);
				});
			}
		});
		/*
		$('#clearBackups').on('click', function()
		{
			var q = confirm('<?php echo L('clear_definitively_all_Backups')?>?');
			if (q)
			{
				$.post('json_io.php?project='+project+'&action=clearbackups&file=x',
				{},
				function (data)
				{
					alert(data);
				});
				
			}
		});
		*/
		
		$('#dialog_SaveButton').hide();
		$('#dialog').dialog('open');
	});
	
	
	// delete a Field
	$('#fieldlist').on('click', '.ui-icon-trash', function()
	{
		var name = $(this).parent().data('name');
		var q = confirm('<?php echo L('delete_%s')?>'.replace(/\%s/,name)+'?');
		if(q)
		{
			$('#col_'+name).remove();
			delete(model[name]);
		}
	});
	
	// edit a Field
	$('#fieldlist').on('click', '.ui-icon-pencil', function()
	{
		editField($(this).parent().data('name'));
	});
	
	// sort the Fields
	$('#fieldUL').sortable(
	{
		handle: '.ui-icon-arrowthick-2-n-s',
		update: function(event, ui)
		{
			var sortedIDs = $('#fieldUL').sortable("toArray");
			var tmp = {};
			for(var i=0, l=sortedIDs.length; i<l; ++i)
			{
				var n = sortedIDs[i].substr(4);
				tmp[n] = model[n];
			}
			model = tmp;
		}
	});

};

//php.js
function esc(str)
{
	str = (str + '').toString();
	// Tilde should be allowed unescaped in future versions of PHP, but if you want to reflect current
	// PHP behavior, you would need to add ".replace(/~/g, '%7E');" to the following.
	return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
	replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
}

function toArr(str)
{
	var arr = str.split("\n"), out = {};
	for (var i=0,j=arr.length; i<j; ++i)
	{
		var arr1 = arr[i].split(':');
		if(arr1[0])
		{
			out[arr1.shift()] = $.trim(arr1.join(':'));
		}
	}
	return out;
}

function checkTypeSelect(el)
{
	var t = el.value;
	$('#addField').val('');
	if(wizards[t])
	{
		var s = '<select><option value=""><?php echo L('Select');?></option>';
		for(w in wizards[t])
		{
			s += '<option value="'+wizards[t][w][0]+'">'+wizards[t][w][1]+'</option>';
		}
		s += '</select>';
		$('#addSelect').html(s);
	}
	else
	{
		$('#addSelect').html('');
	}
}

// draw the Editing-Window
function editField(name)
{
	var m = model[name], t = '',
		html  = '<form data-fieldname="'+name+'" id="editform">';
		html += '<h3>'+( '<?php echo L('edit_Field_%s');?>'.replace(/\%s/, name) )+'</h3>';
		
		for (i in m['lang'])
		{
			var s = m['lang'][i];
			if (typeof(s)=='object')
			{
				s = (s['accordionhead']?s['accordionhead']+' -- ':'') + 
					(s['tabhead']?s['tabhead']+' || ':'') + 
					(s['label']?s['label']:'') +
					(s['placeholder']?' ['+s['placeholder']+'] ':'') + 
					(s['tooltip']?' ('+s['tooltip']+')':'') +
					(s['doc']?' <'+s['doc']+'> ':'')
			}
			t += i+':'+s+"\n";
		}
		html += '<p><label><?php echo L('Language_Labels');?></label><textarea name="lang">'+t+'</textarea></p>';	
	
		t = new RegExp('value="'+m['type']+'"');
		html += '<p><label><?php echo L('Datatype');?></label>'+typeSelect.replace(t, 'value="'+m['type']+'" selected="selected"')+'</p>';
		html += '<p><span style="margin-left:120px" id="addSelect"></span>';
		
		t = '';for (i in m['add']){ t += i+':'+m['add'][i]+"\n"}
		html += '<p><label><?php echo L('Addition');?></label><textarea id="addField" name="add">'+t+'</textarea></p>';
		
		html += '<p><label><?php echo L('Default_Value');?></label><input type="text" name="value" value="'+m['value']+'" /></p>';
		
		t = '';for (i in m['tags']){ t += i+':'+m['tags'][i]+"\n"}
		html += '<p><label><?php echo L('Tags');?></label><textarea name="tags">'+t+'</textarea></p>';
		
		html += '<p><label><?php echo L('Comment');?></label><textarea name="comment">'+(m['comment']?m['comment']:'')+'</textarea></p>';
		
		html += '</form>';
		
	$('#dialogbody').html(html);
	$('#addSelect').on('change', 'select', function()
	{
		$('#addField').val( $.trim($('#addField').val() + "\n" + $(this).val()) );
	});
	$('#dialog_SaveButton').show();
	$('#dialog').dialog('open');
}

/* ]]> */
</script>
</head>
<body>
<div id="colLeft">
	<span style="float:right">
	<button
		id="gotoRestore"
		title="<?php echo L('Backups')?>"
		class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" 
		role="button" 
		aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-clock"></span>
			<span class="ui-button-text"><?php echo L('restore_from_Backup')?></span>
	</button>
	<button
		id="getHelpButton"
		title="<?php echo L('get_Help')?>"
		class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" 
		role="button" 
		aria-disabled="false">
		<span class="ui-button-icon-primary ui-icon ui-icon-help"></span>
		<span class="ui-button-text"><?php echo L('get_Help')?></span>
	</button>
	
	</span>
	<button
		id="addModelButton"
		class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" 
		role="button" 
		aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-plus"></span>
			<span class="ui-button-text"><?php echo L('new_Model')?></span>
	</button>
	
	<ul id="filelist" style="clear:both" class="ilist rlist">
	<?php
	$files = glob($ppath.'generic/*.php');
	foreach ($files as $file)
	{ 
		$n = substr(basename($file),0,-4);
		echo '<li class="ui-state-default ui-selectee" data-name="'.$n.'">
				<span title="'.L('delete_Model').'" class="ui-icon ui-icon-trash"></span>
				<span title="'.L('duplicate_Model').'" class="ui-icon ui-icon-copy"></span>
				<span class="label">'.$n.'</span>
			 </li>';
	}
	?>
	</ul>
	
	
	
</div>

<div id="fieldlist">

</div>

<div id="dialog"><div id="dialogbody"></div></div>

<div class="wait"></div>
</body>
</head>
