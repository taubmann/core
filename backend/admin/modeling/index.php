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
require $ppath . '__configuration.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />

<title>cms-kit Database-Modeling</title>

<link href="../../inc/css/<?php echo end($_SESSION[$projectName]['config']['theme'])?>/jquery-ui.css" rel="stylesheet" />

<!--<link href="inc/css/chosen.css" rel="stylesheet" />-->

<!--[if lt IE 9]>
    <script src="../../inc/js/jquery1.min.js"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
    <script src="../../inc/js/jquery2.min.js"></script>
<!--<![endif]-->


<script>$.uiBackCompat = false;</script>
<script src="../../inc/js/jquery-ui.js"></script>

<script src="../../inc/js/rules/disallowedNames.js"></script>

<!--
<script src="inc/js/jsquery.ui.multidraggable.js"></script>
-->
<script>if(!window.JSON){document.writeln('<script src="../../inc/js/json2.min.js"><\/script>')}</script>

<!--<script src="inc/js/chosen.jquery.min.js"></script>-->
<link type="text/css" href="inc/css/jquery.uix.multiselect.css" rel="stylesheet" />
<script type="text/javascript" src="inc/js/jquery.uix.multiselect.min.js"></script>

<script src="inc/js/JsonXml.js"></script>
<script src="inc/js/jquery.tmpl.js"></script>

<style>
body
{
	font: 65% "Trebuchet MS", sans-serif;
	margin: 0;
	background:url('inc/back.gif');
}

canvas
{
	border: 1px solid #000;
	z-index: 1;
	
}

#objects
{
	position: absolute;
	z-index: 2;
}
.object
{
	position: absolute;
	width: 220px;
}
.object p {
	padding: 7px;
}

.object label
{
	float: right;
	border: 1px solid #ccc;
	margin-right: 3px;
	cursor: pointer;
}

.ui-multidraggable
{
	border: 2px dashed #000;
}
.ui-icon-arrowthick-2-n-s
{
	cursor: move;
}
textarea
{
	line-height: 2em;
}

#menu
{
	position: fixed;
	z-index: 5;
	top: 5px;
	left: 5px;
	padding: 5px;
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
	border: 0;
}

.Tree p
{
	background: #FC9856;
}
.Graph p {
	background: #56BAFC;
}

ul
{
	list-style-type: none; margin: 0; padding: 0; margin: 0; width: 100%;
}
ul li
{
	margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px;
}
ul li span
{
	position: absolute; margin-left: -1.3em;
}


#menu_newwin {
	display: none;
}
</style>

<!-- jQuery-TEMPLATES BEGIN-->

<script id="objectExportTemplate" type="text/x-jquery-tmpl">
	<h2><?php echo L('Export')?></h2>
	<div class="ui-widget-header ui-corner-all">
		<button title="<?php echo L('export_XML-Model')?>" style="float:right" data-action="export" id="button_exportXML" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" type="button" role="button" aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-gear"></span>
			<span class="ui-button-text"><?php echo L('export_XML-Model')?></span>
		</button>
		<button title="<?php echo L('save_XML-Model')?>" style="float:right" data-action="n" id="button_saveXML" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" type="button" role="button" aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-disk"></span>
			<span class="ui-button-text"><?php echo L('save_XML-Model')?></span>
		</button>
		<button title="<?php echo L('rebuild_from_XML')?>" id="button_importXML" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" type="button" role="button" aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-arrowreturnthick-1-w"></span>
			<span class="ui-button-text"><?php echo L('rebuild_from_XML')?></span>
		</button>
		<button title="<?php echo L('sort_Objects_internally')?>" id="button_sortXML" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" type="button" role="button" aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-shuffle"></span>
			<span class="ui-button-text"><?php echo L('sort_Objects_internally')?></span>
		</button>
		
	</div>
	
<form id="dialogForm" data-action="foo" data-objectname="" data-fieldname="">
<textarea id="xmlToExport" style="width:550px;height:450px;">&lt;objects&gt;	
${obj}
&lt;/objects&gt;</textarea>
</form>

</script>

<script id="objectEditTemplate" type="text/x-jquery-tmpl">
<h2><?php echo str_replace('%s', '${obj["@name"]}', L('edit_Object_%s'))?></h2>
<form id="dialogForm" data-action="saveObjectProps" data-objectname="${obj['@name']}" data-fieldname="">
	{{if db}}
	<p>
		<label><?php echo L('Database')?>:</label>
		<select name="db" id="dbSelect">
		{{each db}}
			<option style="border-left:3px solid ${dbcolors[$index]}" {{if obj['db'] && obj['db']==$index}} selected="selected"{{/if}} value="${$index}">${$value}</option>
		{{/each}}
		</select>
	</p>
	{{/if}}
	<p>
		<label><?php echo L('Increment')?>:</label>
		<select name="increment" onchange="alert('<?php echo L('changing_Increment_needs_probably_adaption_of_existing_DB_Schemes')?>')">
			<option value="0"><?php echo L('Auto_Increment')?></option>
			<option {{if obj.increment==1}} selected="selected" {{/if}}value="1"><?php echo L('Timestamp')?></option>
		</select>
	</p>
	<p>
		<label style="float:left"><?php echo L('Templates')?>:</label>
		<select style="height:200px;float:right" name="templates" id="templateSelect" multiple="multiple"  data-placeholder="<?php echo L('add_Templates_to_Object')?>">
			{{each templates}}
			<option value="${$value[0]}" {{if $value[1]}} selected="selected"{{/if}}>${$value[0]}</option>
			{{/each}}
		</select>
	</p>
	<p style="clear:both">
		<label><?php echo L('Language_Labels')?>:</label>
		<textarea name="lang">{{if obj['lang']}}${unesc(obj['lang'])}{{/if}}</textarea>
	</p>
	<p>
		<label></label>
		<select id="hookSelect"><option value=""><?php echo L('select_Hook')?></option>
		{{each hooks}}
			<option title="${$value.d}" value="${$value.e}">${$value.n}</option>
		{{/each}}
		</select>
		<label><?php echo L('Hooks')?>:</label>
		<textarea name="hooks" id="obj_hooks">{{if obj.hooks}}${unesc(obj['hooks'])}{{/if}}</textarea>
	</p>
	<p>
		<label><?php echo L('Wizard_URLs')?>:</label>
		<textarea name="url">{{if obj['url']}}${unesc(obj['url'])}{{/if}}</textarea>
	</p>
	<p>
		<label><?php echo L('Preview_URLs')?>:</label>
		<textarea name="vurl">{{if obj['vurl']}}${unesc(obj['vurl'])}{{/if}}</textarea>
	</p>
	<p>
		<label><?php echo L('Hierarcy')?>:</label>
		<select name="ttype">
			{{each ttypes}}
				<option {{if obj['ttype'] && obj['ttype']==$value[0]}} selected="selected"{{/if}} value="${$value[0]}" style="background:${$value[1]}">${dbhLabel[$value[0]]}</option>
			{{/each}}
		</select>
		<input type="hidden" name="hidettype" id="obj_hidettype" value="${obj['hidettype']}" />
		<input type="checkbox" {{if obj['hidettype'] && obj['hidettype']==='true' && obj['ttype']!='List'}}checked="checked"{{/if}} onchange="$('#obj_hidettype').val(this.checked)" title="<?php echo L('hide_Hiearchy_in_Backend')?>" />
	</p>
	<p>
		<label><?php echo L('Tags')?>:</label>
		<textarea name="tags">{{if obj['tags']}}${unesc(obj['tags'])}{{/if}}</textarea>
	</p>
	<p>
		<label><?php echo L('Comment')?>:</label>
		<textarea name="comment">{{if obj['comment']}}${unesc(obj['comment'])}{{/if}}</textarea>
	</p>
</form>
</script>

<script id="objectSortTemplate" type="text/x-jquery-tmpl">
<h2><?php echo L('sort_Objects')?></h2>
<form id="dialogForm" data-action="foo" data-objectname="" data-fieldname="">
<ul class="ui-state-default" id="objectSortUl">
	{{each obj}}
		<li class="ui-state-default" id="s_o_r_t${$value['@name']}"><span title="<?php echo L('drag_to_Sort')?>" class="ui-icon ui-icon-arrowthick-2-n-s"></span>${$value['@name']}</li>
	{{/each}}
</ul>
</form>
</script>

<script id="fieldEditTemplate" type="text/x-jquery-tmpl">
<h2><?php echo str_replace('%s', '${field["@name"]}', L('edit_Field_%s'))?></h2>
<form id="dialogForm" data-action="saveFieldProps" data-objectname="${obj}" data-fieldname="${field['@name']}">
	<p>
		<label><?php echo L('Language_Labels')?>:</label>
		<textarea name="lang" id="field_lang">{{if field['lang']}}${unesc(field['lang'])}{{/if}}</textarea>
	</p>
	<p>
		<label><?php echo L('Datatype')?>:</label>
		<select name="datatype" id="field_datatype">
			{{each types}}
				<option {{if field['datatype']==$value[0]}}selected="selected"{{/if}} value="${$value[0]}" style="border-left:3px solid ${$value[1]}">${dtypeLabel[$value[0]]} ( ${$value[0]} )</option>
			{{/each}}
		</select>
	</p>
	<p>
		<label><?php echo L('Filter')?>:</label>
		<input type="text" value="{{if field['filter']}}${unesc(field['filter'])}{{/if}}" name="filter" id="field_filter" />
	</p>
	<p>
		<label><?php echo L('Default_Value')?>:</label>
		<span id="defaultSelect">
			<select id="defaultDefaults" onchange="$('#field_default').val(this.value)"><option value=""><?php echo L('Default_Value')?></option>
				{{each defaults}}
					<option {{if unesc(field['default'])==$value}}selected="selected"{{/if}} value="${$value}">${defaultLabel[$index]}</option>
				{{/each}}
			</select><br />
			<input type="text" style="margin-left:122px" value="{{if field['default']}}${unesc(field['default'])}{{/if}}" name="default" id="field_default" />
		</span>
	</p>
	<p>
		<label></label>
		<span id="wizardSelect"></span>
	</p>
	<p>
		<label><?php echo L('Addition')?>:</label>
		<textarea name="add" id="field_add">{{if field['add']}}${unesc(field['add'])}{{/if}}</textarea>
	</p>
	<p>
		<label><?php echo L('Tags')?>:</label>
		<textarea name="tags" id="field_tags">{{if field['tags']}}${unesc(field['tags'])}{{/if}}</textarea>
	</p>
	<p>
		<label><?php echo L('Comment')?>:</label>
		<textarea name="comment" id="field_comment">{{if field['comment']}}${unesc(field['comment'])}{{/if}}</textarea>
	</p>
</form>
</script>

<!-- jQuery-TEMPLATES END -->

</head>
<body>

<canvas id="bezier" width="6000" height="6000" style="position:absolute;top:0;left:0;"></canvas>

<span id="objects"></span>

<div id="dialog">
	<div id="dialogbody"></div>
</div>

<div id="menu" class="ui-widget-header ui-corner-all">
	<button title="<?php echo L('create_new_Object')?>" id="menu_new_object" data-icon="circle-plus" type="button">
		<?php echo L('new_Object')?>
	</button>
	<button title="<?php echo L('save_or_export_new_Model')?>" id="menu_export" data-icon="gear" type="button">
		<?php echo L('Export')?>
	</button>
	<button title="<?php echo L('open_Documentation')?>" id="menu_help" data-icon="help" type="button">
		<?php echo L('Help')?>
	</button>
	<button title="<?php echo L('open_new_Window')?>" id="menu_newwin" data-icon="newwin" type="button">
	&nbsp;
	</button>
</div>


<script type="text/javascript">
/* <![CDATA[ */

var dtypeLabel=[],
	ddefaultLabel=[],
	datatypes = [],
	datatype = {},
	datatype_defaults = {},
	fieldtypecolor = [];
	fieldtypecolor["NUMERIC"]	= "#4682b4",
	fieldtypecolor["CHARACTER"]	= "#a0522d",
	fieldtypecolor["OTHER"]		= "#cdc9a5",
	dbhLabel = [];
	
<?php

// available Databases 
echo "var databases = ['" . implode("','", Configuration::$DB_ALIAS) . "'];\n";

echo "var templates = ['" . implode("','", array_keys($_SESSION[$projectName]['config']['templates'])) . "'];\n";

echo "var project = '".$projectName."', wizards = [];\n";

// available Wizards (backend/inc/php/collectExtensionInfos.php)
$embeds = collectExtensionInfos($projectName);
foreach($embeds['w'] as $k => $v)
{
	echo  "wizards['$k'] = {" . implode(',', $v) . "}\n";
}

// available Hooks
echo  '
var hooks = [' . str_replace(array('"description"','"embed"','"name"'), array('d', 'e', 'n'), implode(',', array_values($embeds['h']))) . '];
	dbhLabel["List"]  = "'.L('htype_List').'";
	dbhLabel["Tree"]  = "'.L('htype_Tree').'";
	dbhLabel["Graph"] = "'.L('htype_Graph').'";
';
$ddefaultLabel = array();
$datatypes = json_decode(file_get_contents('../../inc/js/rules/datatypes.json'), true);

foreach($datatypes as $k => $v)
{
	echo "dtypeLabel['$k'] = '".L($k)."';\n";
	echo "datatype['$k'] = fieldtypecolor['".$v['type']."'];\n";
	echo "datatypes.push(['$k', fieldtypecolor['".$v['type']."']]);\n";
	echo "datatype_defaults['$k'] = {};\n";
	
	foreach($v['default'] as $dk=>$dv)
	{
		echo "datatype_defaults['$k']['$dk'] = '$dv';\n";
		$ddefaultLabel[] = $dk;
	}
}
$ddefaultLabel = array_unique($ddefaultLabel);
foreach($ddefaultLabel as $dl)
{
	echo "ddefaultLabel['$dl'] = '".L($dl)."';\n";
}


?>

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////




// define global Variables
var canvas,
	ctx,
	objects = {},
	path = [],
	relations = [],
	dbcolors = ['transparent','#800080','#40e0d0','#a52a2a','#add8e6'],// colors for up to 5 Databases atm (first is transparent)
	relationcolors = ['#0c3', '#03c'],// colors for sibling, parent/child - Connections
	ttypes = [['List','#ccc'],['Tree','#FC9856'],['Graph','#56BAFC']];

// onload-Block
$(function()
{
	if (top != self){ $('#menu_newwin').show() }
	
	// define the canvas http://www.w3schools.com/tags/canvas_strokestyle.asp
	canvas = document.getElementById('bezier');
	ctx = canvas.getContext('2d');
	
	$.get('xml_io.php?project=<?php echo $projectName?>', 
	function(xml)
	{
		processXML(xml);
	});
	
	
	
	// Menu-Buttons
	$('#menu_new_object').on('click', function(){
		addObject();
	});
	
	$('#menu_export').on('click', function()
	{
		// check if Session is still alive
		var head = document.getElementsByTagName('head')[0];
		var lnk = document.createElement('script');
		lnk.type = 'text/javascript'; 
		lnk.src = 'inc/session.php?project='+project;
		head.appendChild(lnk);
		
		$('#dialog_SaveButton').hide();
		
		// fix if id is missing
		$.each(objects, function(index, item)
		{
			if(item[0]['fields']['field'][0]["@name"] != 'id')
			{
				var t={};
				t["@name"] = "id";
				t["datatype"] = "INTEGER";
				item[0]['fields']['field'].unshift(t);
			}
		});
		
		// create a local Copy of objects
		var J = $.extend(true, {}, objects);
		
		// add Relations to the Object [FROM,TO,TYPE]
		$.each(relations, function(index, item)
		{
			// indexes item[0] == from , item[1] == to, item[2] == type(0/1)
			var i0 = path[item[0]][0],
				i1 = path[item[1]][0],
				si = ((item[2] == 1) ? path[item[0]][1][item[1]+'id'] : 0);//sub-index (child-parent OR sibling)
			
			if (J.object[i0])
			{
				if(!J.object[i0]['fields']['field'][si]['relation']) {
					J.object[i0]['fields']['field'][si]['relation'] = [];
				}
				
				J.object[i0]['fields']['field'][si]['relation'].push( {'@object': item[1]} );
			}
		});
		
		
		var newxml = xmlJsonClass.json2xml(J, '	');
		$('#objectExportTemplate').tmpl({
			obj: newxml.
						replace(/\<object \/\>/g,'').
						replace(/\<field \/\>/g,'').
						replace(/\<lang \/\>/g,'').
						replace(/\<filter \/\>/g,'').
						replace(/\<default \/\>/g,'').
						replace(/\<add \/\>/g,'').
						replace(/\<tags \/\>/g,'').
						replace(/\<comment \/\>/g,'').
						replace(/\<relation \/\>/g,'')
		}).appendTo('#dialogbody');
		
		// re-import XML from Textarea
		$('#button_importXML').on('click', function()
		{
			objects = {}, path = [], relations = [];//reset JS-Objects
			clearLines();//clear BG-Vectors
			$('#objects div').each(function(){ $(this).remove() });//remove all Objects from Stage
			var o = parseXml($('#xmlToExport').val());
			if(o) {
				processXML(o);
				$('#dialog').dialog('close');
			}else {
				alert('<?php echo L('could_not_process_XML')?>!');
			}
		});
		
		$('#button_saveXML, #button_exportXML').on('click', function()
		{
			var action = $(this).data('action');
			
			$.post('xml_io.php?project=<?php echo $projectName?>', { xml: $('#xmlToExport').val() }, function(data)
			{
				if(data=='saved')
				{
					if(action == 'export')
					{
						var q = confirm('<?php echo L('open_Setup_and_write_Model_to_Database')?>!');
						if(q)
						{
							//window.location = 'process.php?project=<?php echo $projectName?>'
							$('#dialogbody').html('<iframe src="process.php?project=<?php echo $projectName?>"></iframe>');
						}
					}else {
						alert('<?php echo L('saved_Model')?>');
					}
				} else {
					alert('<?php echo L('could_not_save')?>: '+data);
				}
			});
			
		});
		
		// sort internal Order of the objects
		$('#button_sortXML').on('click', function()
		{
			$('#dialogbody').html('');
			$('#objectSortTemplate').tmpl({ obj: objects.object }).appendTo('#dialogbody');
			
			$('#objectSortUl').sortable({
				update: function(event, ui)
				{
					var order = $(this).sortable('toArray');
					var tmpn = [], tmpo = [];
					for(var i=0,j=order.length; i<j; ++i) { tmpn.push(order[i].split('s_o_r_t').pop()); }
					for(var i=0,j=tmpn.length;  i<j; ++i) { tmpo.push( objects.object[ path[tmpn[i]][0] ] ); }
					for(var i=0,j=tmpn.length;  i<j; ++i) { path[tmpn[i]][0] = i; }
					objects.object = tmpo;
				}
			});
			$('#dialog_SaveButton').hide();
			$('#dialog').dialog('open');
		});
		
		$('#dialog').dialog('open');
		
	});
	
	$('#menu_help').on('click', function(){
		$('#dialogbody').html('<iframe src="../extension_manager/showDoc.php?file=../../extensions/documentation/doc/<?php echo $lang?>/.object_modeling.md"></iframe>');
		$('#dialog_SaveButton').hide();
		$('#dialog').dialog('open');
	});
	
	$('#menu_newwin').on('click', function(){
		window.open(document.location, document.title)
	});
	
	// Button-Styling
	$('#menu button').each(function() {
		$(this).button( {icons:{ primary: 'ui-icon-'+$(this).data('icon')}, text: (($(this).text()=='.')?false:true)})
	});
	
	// Menu END
	
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
				click: function() {
					var form = $('#dialogForm');
					var action = form.data('action');// get Function-Name
					window[action](form.data('objectname'), form.data('fieldname'), form.serializeArray());// call Function
					$(this).dialog( "close" );
					
				}
			},
			{
				text: '<?php echo L('Close')?>',
				click: function() {
					$(this).dialog( 'close' );
				}
			}
		]
	});
	
	
	
});// (document).ready END

function parseXml(xml)
{
	var dom = null;
	if (window.DOMParser)
	{
		try { 
			dom = (new DOMParser()).parseFromString(xml, "text/xml"); 
		} catch (e) {
			dom = null;
		}
	}
	else if (window.ActiveXObject)
	{
		try {
			dom = new ActiveXObject('Microsoft.XMLDOM');
			dom.async = false;
			if (!dom.loadXML(xml)) {
				alert(dom.parseError.reason + dom.parseError.srcText);// parse error ..
			}
		} catch (e) {
			dom = null;
		}
	}
	else
	{
		alert("<?php echo L('could_not_parse_XML')?>!");
	}
	return dom;
};

function processXML(xml)
{
	
	objects = xmlJsonClass.xml2json(xml, '', true);
	
	//alert(JSON.stringify(objects, '\t'));
	
	if(objects && objects.object)
	{
		// wrap in Array if there is only one Object
		if(!$.isArray(objects.object)) {
			objects.object = [objects.object];
		}

		for(e in objects.object)
		{
			//alert(JSON.stringify(objects.object[e], '\t'));
			
			// ignore empty objects
			if(!objects.object[e]) continue;
			
			path[objects.object[e]['@name']] = [e, []];
			
			// create the object
			addObject(objects.object[e]['@name'], objects.object[e]['@x'], objects.object[e]['@y']);
			
			// create the Fields
			for(c in objects.object[e]['fields']['field'])
			{
				// ignore empty/illegal columns
				if(!objects.object[e]['fields']['field'][c]) {
					
					continue;
				}
				
				//we have (probably) only one field (the id) so we have to wrap the object
				if(!objects.object[e]['fields']['field'][c]['@name']) {
					objects.object[e]['fields']['field'] = [ objects.object[e]['fields']['field'] ];
					c = 0;
				}
				
				
				path[ objects.object[e]['@name'] ][1][ objects.object[e]['fields']['field'][c]['@name']] = c;
				
				if(objects.object[e]['fields']['field'][c]['@name'] == 'id')
				{
					
					// create sibling-relations
					for(r in objects.object[e]['fields']['field'][c]['relation'])
					{
						
						var t = objects.object[e]['fields']['field'][c]['relation'][r],
							t = ( t['@object'] ? t['@object'] : t );
						relations.push( [objects.object[e]['@name'], t, 0] );
					}
				}
				else
				{
					addField(objects.object[e]['@name'], objects.object[e]['fields']['field'][c]['@name'], datatype[ objects.object[e]['fields']['field'][c]['datatype'] ], true);
					
					// create parent-child-relations (only check "parentid"-Fields)
					if(objects.object[e]['fields']['field'][c]['@name'].slice(-2) == 'id')
					{
						for(r in objects.object[e]['fields']['field'][c]['relation'])
						{
							var t = objects.object[e]['fields']['field'][c]['relation'][r],
								t = ( t['@object'] ? t['@object'] : t );
							relations.push( [objects.object[e]['@name'], t, 1] );
						}
					}
				}
				
				// remove Relations from Objects (adding lateron)
				if(objects.object[e]['fields']['field'][c]['relation']) {
					delete objects.object[e]['fields']['field'][c]['relation'];
				}
				
			}
		}
		
		drawLines();
		
	}
	else
	{
		// fallback when Project is empty
		objects = {};
		objects.object = [];
	}
	//alert(JSON.stringify(relations, '\t'));
	
	
};// processXML END

// dummy-function
function foo(){};

function saveObjectProps(objectname, x, arr)
{
	var i0 = path[objectname][0];
	objects.object[i0]['templates']=[];
	for(var i=0,j=arr.length; i<j; ++i) {
		
		// change Color-Class of the Object
		if(arr[i].name=='db') { $('#'+objectname+'>p').css('border-left','4px solid '+dbcolors[arr[i].value]); }
		if(arr[i].name=='ttype') { $('#'+objectname).removeClass('List Tree Graph').addClass(arr[i].value); }
		
		switch(arr[i].name)
		{
			case 'templates':
				objects.object[i0][arr[i].name].push(arr[i].value);
			break;
			
			default:
				objects.object[i0][arr[i].name] = esc(arr[i].value);
			break;
		}
	};
	objects.object[i0]['templates'] = objects.object[i0]['templates'].join(',')
	
}

function saveFieldProps(objectname, fieldname, arr)
{
	var i0 = path[objectname][0], 
		i1 = path[objectname][1][fieldname];
	
	for(var i=0,j=arr.length;i<j;++i)
	{
		objects.object[i0]['fields']['field'][i1][arr[i].name] = esc(arr[i].value);
		// change ColorCode of the Field
		if(arr[i].name=='datatype') { $('#'+objectname+'-____-'+fieldname).css('border-left','3px solid '+datatype[arr[i].value]); }
	};
}



// addObject-Function
function addObject(objectname, x, y)
{
	
	if(!objectname) {
		objectname = prompt('<?php echo L('enter_Object_Name')?>','');
		if(!objectname) return;
		//
		if($.inArray(objectname, disallowedTableNames) != -1)
		{
			alert('<?php echo L('Object_Name_not_allowed')?>!');
			return;
		}
		
		objectname = objectname.replace(' ','_').replace(/[^\d\w]/g, '').toLowerCase();
		
		if(path[objectname])
		{
			alert('<?php echo L('Objectname_already_exists')?>!');
			return;
		}
		
		// add new Object to objects
		var l = objects.object.length;
		objects.object[l] = {};
		objects.object[l]['@name'] = objectname;
		objects.object[l]['fields'] = {};
		objects.object[l]['fields']['field'] = [];
		objects.object[l]['fields']['field'][0] = {};
		objects.object[l]['fields']['field'][0]["@name"] = "id" ;
		objects.object[l]['fields']['field'][0]["datatype"] = "INTEGER";
		
		path[objectname] = [l, []];
		path[objectname][1]['id'] = 0;
	}
	
	
	var index = path[objectname][0];
	
	if(!y) y = $(window).scrollTop() + 50;
	if(!x) x = $(window).scrollLeft() + 20;
	
	var dbi = (objects.object[index]['db']?objects.object[index]['db']:0);
	
	// create Object-HTML
	var html  = '<div id="'+objectname+'" class="object '+((objects.object[index]['ttype'])?objects.object[index]['ttype']:'');
		html += '" style="top:'+parseInt(y)+'px;left:'+parseInt(x)+'px;">';
		
		// Header
		html += '<p style="border-left:4px solid '+dbcolors[dbi]+';" class="ui-widget-header ui-corner-all">';
		html += '<label title="<?php echo L('delete_Object')?>" class="ui-icon ui-icon-trash"></label>';
		html += '<label title="<?php echo L('edit_Object_Properties')?>" class="ui-icon ui-icon-pencil"></label>';
		html += '<label title="<?php echo L('new_Field')?>" class="ui-icon ui-icon-circle-plus"></label>';
		html +=  objectname + '</p>';
		
		// UL-List-Body
		html += '<ul><li id="'+objectname+'-____-id" class="ui-state-default id_col">';
		html += '<label style="border-color:'+relationcolors[0]+'" title="<?php echo L('create_m:n_Relation')?>" class="ui-icon ui-icon-arrowthick-2-e-w"></label>';
		html += '<label style="border-color:'+relationcolors[1]+'" title="<?php echo L('create_1:n_Relation')?>" class="ui-icon ui-icon-arrowthick-1-ne"></label>';
		html += 'id</li></ul>';
		
		html += '</div>';
	$('#objects').append(html);
	
	// start m:n Connecting
	$('#'+objectname+' .ui-icon-arrowthick-2-e-w:first').on('click', function()
	{
		var target = prompt('<?php echo L('enter_Name_of_Sibling_Object')?>','');
		if(target && path[target]) {
			toggleConnection(objectname, target, 0);
		}
	});
	// start 1:n Connecting
	$('#'+objectname+' .ui-icon-arrowthick-1-ne:first').on('click', function()
	{
		var target = prompt('<?php echo L('enter_Name_of_Parent_Object')?>','');
		if(target && path[target]) {
			toggleConnection(objectname, target, 1);
		}
	});
	
	// edit Object-Function
	$('#'+objectname+' p .ui-icon-pencil:first').on('click', function()
	{
		// prepare Template-Array
		var tpls = [], t = [];
		if (objects.object[index]['templates'])
		{
			t = objects.object[index]['templates'].split(',');
			for(var i=0,j=t.length; i<j; ++i)
			{
				tpls.push([t[i], true]);
			}
		}
		for(var i=0,j=templates.length; i<j; ++i)
		{
			if(t.indexOf(templates[i]) == -1){ tpls.push([templates[i], false]); }
		}
		// prepare Template-Array END
		
		//alert(JSON.stringify(objects.object[i]));
		$('#objectEditTemplate').tmpl({
			obj: objects.object[index],
			ttypes: ttypes,
			hooks: hooks,
			db : databases,
			templates: tpls,
			dbcolors: dbcolors
		}).appendTo('#dialogbody');
		
		//$('#templateSelect').chosen();
		$("#templateSelect").multiselect({sortable:true});
		
		$('#dialog').dialog('open');
		
		//
		$('#dbSelect').on('change', function() {
			alert('<?php echo L('Attention:_all_related_Objects_must_be_in_the_same_Database')?>!');
		});
		
		$('#hookSelect').on('change', function() {
			var v = $('#obj_hooks').val();
			$('#obj_hooks').val((v!=''?v+'\n':'')+$(this).val());
		});
	});
	
	// Add-Field-Function
	$('#'+objectname+' p .ui-icon-circle-plus:first').on('click', function()
	{
		var fieldname = prompt('<?php echo L('enter_Field_Name')?>','');
		if(fieldname)
		{
			fieldname = fieldname.replace(' ','_').replace(/[^\d\w]/g, '').toLowerCase();
			
			if($.inArray(fieldname, disallowedFieldNames) != -1)
			{
				alert('<?php echo L('Field_Name_not_allowed')?>!');
				return;
			}
			
			if(path[objectname][1][fieldname])
			{
				alert('<?php echo L('Field_Name_already_exists')?>!');
				return;
			}
			
			var oi = path[objectname][0], //object-index
				fi = objects.object[oi]['fields']['field'].length; //field-index
			
			// add field to objects.object + path
			path[ objectname ][1][ fieldname ] = fi
			objects.object[oi]['fields']['field'][fi] = {};
			objects.object[oi]['fields']['field'][fi]["@name"] = fieldname;
			objects.object[oi]['fields']['field'][fi]["datatype"] = 'INTEGER';
			
			// add field
			addField(objectname, fieldname, datatype['INTEGER']);
		}
	});
	
	// delete Object
	$('#'+objectname+' p .ui-icon-trash:first').on('click', function() {
		var q = confirm('<?php echo L('delete_%s')?>?'.replace('%s', objectname));
		if(q)
		{
			var tmp = [];
			$.each(relations, function(index, item)
			{
				// indexes item[0] == from , item[1] == to, item[2] == type(0/1)
				if(item && item[0]!=objectname && item[1]!=objectname) {
					tmp.push(item);
				}
			});
			relations = tmp;
			
			objects.object[path[objectname][0]] = null;
			$('#'+objectname).remove();
			clearLines();
			drawLines();
			//path[objectname] = false;
			
		}
	});
	
	// make Object draggable 
	$('#'+objectname).draggable({
	//$('#'+objectname).multidraggable({
		
		handle: 'p',

		start: function(event, ui)
		{
			clearLines();
		},
		stop: function(event, ui)
		{
			//save new Position
			var i0 = path[objectname][0];
			objects.object[i0]['@x'] = ui.position.left;
			objects.object[i0]['@y'] = ui.position.top;
			
			// draw Bezier-Connectors
			drawLines();
		}
	});
	
	// make List-Elements sortable
	$('#'+objectname+'>ul').sortable(
	{
		items: 'li:not(.id_col)',
		handle: 'span',
		update: function(event, ui)
		{
			//serialize the List (returns IDs)
			var order = $(this).sortable('toArray');
				order.unshift(objectname+'-____-id');//add ID because its not within the sortable-array
				
			var index = path[objectname][0],
				tmpn = [],// field-names
				tmpo = [];// tmp-object
			
			// get the field-name
			for(var i=0,j=order.length; i<j; ++i) { tmpn.push(order[i].split('-____-').pop()); }
			
			// get the old index-numbers from the path and re-order the tmp-object
			for(var i=0,j=tmpn.length;  i<j; ++i) {
				
				//var ix = path[objectname][1][tmpn[i]] || 0;// if there is a new created object id is undefined
				//tmpo.push( objects.object[index]['fields']['field'][ ix ] ); 
				tmpo.push( objects.object[index]['fields']['field'][ path[objectname][1][tmpn[i]] ] );
			}
			// re-order the path itself
			for(var i=0,j=tmpn.length;  i<j; ++i) { path[objectname][1][tmpn[i]] = i; }
			
			// assign tmp-object to the official object
			objects.object[index]['fields']['field'] = tmpo;
		}
	});
	
};// addObject END

//php.js
function unesc(str)
{
	return decodeURIComponent((str + '').replace(/\+/g, '%20'));
}

//php.js
function esc(str)
{
	str = (str + '').toString();
	// Tilde should be allowed unescaped in future versions of PHP, but if you want to reflect current
	// PHP behavior, you would need to add ".replace(/~/g, '%7E');" to the following.
	return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
	replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
}



function buildFollowers(v, my_type)
{
	var w  = wizards[v],
		df = datatype_defaults[v],
		tn = datatype[v],
		to = datatype[my_type];//
	
	if(to != tn) {
		alert('<?php echo L('Attention:_changing_general_Datatype_can_corrupt_Data')?>');
	}
	var html = '';
	if(w)
	{
		html += '<select id="wizardEmbedSelect"><option value=""><?php echo L('Wizard')?></option>';
		for(e in w) html += '<option title="'+w[e][1]+'" value="'+w[e][0]+'">'+e+'</option>';
		html += '</select>';
	}
	$('#wizardSelect').html(html);
	//$('#field_add').val('');
	$('#wizardEmbedSelect').on('change',function() {
		$('#field_add').val($(this).val().replace('#',"\n"));
	});
	
	var html = '';
	if(df)
	{
		html += '<select onchange="$(\'#field_default\').val(this.value)"><option value=""><?php echo L('Default_Value')?></option>';
		for(e in df) html += '<option value="'+df[e]+'">'+e+'</option>';
		html += '</select>';
		html += '<input type="text" style="margin-left:122px" value="" name="default" id="field_default" />';
		$('#defaultSelect').html(html);
	}
}

// add a Field
function addField (objectname, fieldname, col, norefresh)
{
	if(!fieldname) return;
	
	var ul = $('#'+objectname+'>ul');
	
	// create Column-HTML
	var html  = '<li id="'+objectname+'-____-'+fieldname+'" style="border-left:3px solid '+col+'" class="ui-state-default">';
	if(fieldname.slice(-2) != 'id') {}
		html += '<label title="<?php echo L('delete_Field')?>" class="ui-icon ui-icon-trash"></label>';
	
		html += '<label title="<?php echo L('edit_Field_properties')?>" class="ui-icon ui-icon-pencil"></label>';
		
		html += '<span title="<?php echo L('drag_to_Sort')?>" class="ui-icon ui-icon-arrowthick-2-n-s"></span>';
		html += '' + fieldname.substr(0,15) + '</li>';
	
	ul.append(html);
	
	if(!norefresh) ul.sortable('refresh');
	
	// edit Field-Function
	$('#'+objectname+'-____-'+fieldname+' .ui-icon-pencil:first').on('click', function()
	{
		var i0 = path[objectname][0], 
			i1 = path[objectname][1][fieldname];
		
		var my_type = objects.object[i0]['fields']['field'][i1]['datatype'];
		//alert(JSON.stringify(datatype_defaults[my_type]));
		
		//
		$('#fieldEditTemplate').tmpl({
			obj: objectname,
			field: objects.object[i0]['fields']['field'][i1],
			wizards: wizards[my_type],
			defaults: datatype_defaults[my_type],
			defaultLabel: ddefaultLabel,
			types: datatypes
		}).appendTo('#dialogbody');
		
		buildFollowers(my_type, my_type);
		
		$('#dialog').dialog('open');
		
		// if the User changes the Data-Type
		$('#field_datatype').on('change', function()
		{
			var v  = $(this).val();
			buildFollowers(v, my_type);
			/*	w  = wizards[v],
				df = datatype_defaults[v],
				tn = datatype[v],
				to = datatype[my_type];//
			
			if(to != tn) {
				alert('<?php echo L('Attention:_changing_general_Datatype_can_corrupt_Data')?>');
			}
			var html = '';
			if(w)
			{
				html += '<select id="wizardEmbedSelect"><option value=""><?php echo L('Wizard')?></option>';
				for(e in w) html += '<option title="'+w[e][1]+'" value="'+w[e][0]+'">'+e+'</option>';
				html += '</select>';
			}
			$('#wizardSelect').html(html);
			$('#field_add').val('');
			$('#wizardEmbedSelect').on('change',function() {
				$('#field_add').val($(this).val().replace('#',"\n"));
			});
			
			var html = '';
			if(df)
			{
				html += '<select onchange="$(\'#field_default\').val(this.value)"><option value=""><?php echo L('Default_Value')?></option>';
				for(e in df) html += '<option value="'+df[e]+'">'+e+'</option>';
				html += '</select>';
				html += '<input type="text" style="margin-left:122px" value="" name="default" id="field_default" />';
				$('#defaultSelect').html(html);
			}*/
		});
		
	});
	

	// delete Field-Function
	$('#'+objectname+'-____-'+fieldname+' .ui-icon-trash:first').on('click', function()
	{
		var c = confirm('delete '+fieldname+'?');
		if(c) {
			removeField(objectname,fieldname);
		}
	});
	
};// addField END

function removeField (objectname,fieldname)
{
	$('#'+objectname+'-____-'+fieldname).remove();
	var i0 = path[objectname][0], i1 = path[objectname][1][fieldname];
	objects.object[i0]['fields']['field'][i1] = null;// "remove" Element from Object-array
	path[ objectname ][1][ fieldname ] = false;// "remove" Element from path
};

function toggleConnection(from, to, type) {
	var match = false;
	if(from == to) 
	{
		alert('<?php echo L('Self_Reference_is_not_allowed')?>!');
		return;
	}
	$.each(relations, function(index, item)
	{
		if ((item[0] == from && item[1] == to) || (item[1] == from && item[0] == to))
		{ 
			match = index;
		}
	});
	
	if(match !== false)
	{
		var o = confirm('<?php echo L('Object_are_connected._Delete_Connection')?>?');
		if(o)
		{
			relations.splice(match, 1);
			removeField(from, to+'id');// remove parentid-Field if exists
			clearLines();
			drawLines();
		}
	}
	else
	{
		// add the Relation
		relations.push([from, to, type]);
		// if its child-parent-Relation
		if(type==1)
		{
			var i0 = path[from][0], o = objects.object[i0]['fields']['field'], ol = o.length;
			o[ol] = {};
			o[ol]['@name'] = to+'id';
			o[ol]['datatype'] = 'INTEGER';
			path[from][1][to+'id'] = ol;
			
			//alert(objects.object[i0]['fields']['field'][ol]['datatype']);
			
			addField(from, to + 'id', datatype['INTEGER']);
			$('#'+from+'-____-'+to+'id .ui-icon-trash:first').remove();
			$('#'+from+'-____-'+to+'id .ui-icon-pencil:first').hide();
		}
		addRelation(from, to, type);
	}
	
	//alert(JSON.stringify(relations))
};

// add a relation between 2 Objects
function addRelation(from, to, type)
{
	var ff = $( '#'+from+'-____-'+ (type==0 ? '' : to) +'id' ).offset(),
		ft = $( '#'+to+'-____-id' ).offset();
	
	if(ff && ft)
	{
		var x1 = ff.left,
			y1 = ff.top + 15,
			x2 = ft.left,
			y2 = ft.top + 15;
		var bw = 195;
		
		if (x1 > x2) x2 += bw;
		if (x2 > x1) x1 += bw;
		
		ctx.beginPath();
		
		// moveTo(startX, startY)
		ctx.moveTo(x1, y1);
		// bezierCurveTo(control_1_X, control_1_Y, control_2_X, control_2_Y, endX, endY)
		ctx.bezierCurveTo( x2,y1, x1,y2, x2,y2 );
		
		ctx.lineWidth = 3;
		ctx.strokeStyle = relationcolors[type];
		ctx.scale(1, 1);
		ctx.stroke();
	
	} else {
		alert('<?php echo L('could_not_find_HTML_Objects_for')?> '+from+'<->'+to+'!')
	}
	
};// addRelation END

// (re)draw all Connectors
function drawLines()
{
	
	for ( var i=0,j=relations.length; i<j; ++i ) {
		addRelation(relations[i][0], relations[i][1], relations[i][2]);
	}
};

// clear the canvas
function clearLines() {
	
	ctx.save();
	ctx.setTransform(1, 0, 0, 1, 0, 0);
	ctx.clearRect(0, 0, canvas.width, canvas.height);
	ctx.restore();
};

/* ]]> */
</script>
</body>
</html>
