// AUTO-CREATED FILE (created at 1356796121) do not edit!

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
* 
* 
*********************************************************************************/

// show a Message instead of alert (string, error, timeout)
function message(str, err, out) {
	var id = 'x'+Math.random().toString().substring(2);
	var h = '<div id="'+id+'" class="ui-widget"><div class="ui-state-'+(err?'error':'highlight')+' ui-corner-all" style="padding:0 .7em"><p><span class="ui-icon ui-icon-'+(err?'alert':'info')+'" style="float:left;margin-right:.3em;"></span>'+str+'</p></div></div>';
	$('#messagebox').append(h);
	window.setTimeout(function(){$('#'+id).slideUp()}, (out?out:3000));
};

// Dummy-Translation for Development ( do NOT remove the space between "_" and "(". All _-Calls are replaced by the Compressor)
function _ (str) {
	if(window.LL && window.LL[str]) return window.LL[str];
	return str.replace(/_/g, ' ');
};

// extract get-parameter out of a hash-string
function getget(){
	var h=window.location.hash.substr(1),g=[];
	if(h.length>0) {
		var p = h.split('&');
		for(var i=0,j=p.length;i<j;++i){
			var a = p[i].split('=');
			if(a[1]) g[a[0]]=a[1];
		}
	}
	return g;
};

// http://stackoverflow.com/questions/7771119/jquery-prev-of-a-type-regardless-of-its-parent-etc
// example $("#text3").realPrev("input", -2)
(function($) {
	$.fn.realPrev = function(selector, no) {
		var all = $("*");
		if(!no) no = -1;
		return all.slice(0,all.index(this)).filter(selector).slice(no).first();
	}
})(jQuery);

function array_diff (a, b) {
	return jQuery.grep(a, function(n,i) {
		return b.indexOf(n) < 0;
	});
};

function template(str, data) {
    return str.replace(/%(\w*)%/g,function(m,key){return data.hasOwnProperty(key) ? data[key] : '';});
};

function showTT(el) {
	alert($(el).next('span').html())
};

//extra-Function for file-manager
function transmitFilePath(p) {
	$('#'+targetFieldId).val(p);
	message(p+' '+langLabels.saved);
};

function checkForNumber(el) {
	el.style.color = (isNaN(el.value)) ? '#f00' : '#000';
};

$(window).unload(function(){window.name=JSON.stringify(store)});

/*


*/

/*
 * desktop-functions
*/

/*
 * Strategie:
 * 
 * JQ-UI wird beibehalten, kritische elemente/funkionen optimiert
 * 
 * 
 * TEMPLATE
 * -> mobile-CSS/JS laden
 * meta-tags für mobilgeräte
 * 
 * CSS
 * das fixe 3-spalten-layout wird zum responsiven 2-spalten-layout + popup von spalte 3
 * 
 * JS
 * ELEMENTE KILLEN
 * admin-wizards
 * logo
 * tag-filter
 * 
 * ELEMENTE UMBAUEN
 * selectbox -> plain select
 * date(time)picker ??? -> mobiscroll http://mobiscroll.com/
 * .nomobile Klasse für wizard-buttons?
 * vereinfachter dialog (fullscreen mit close-knopf?)
 * 
 * ggf angepasste jquery.ui ??
 * was ist mit zepto
 * 
 * */

var ch, actHash = '', objectName = '', objectId = '', objectHType = false;

// simple function to check Changes of the hash (=History-Changes)
function checkHash() 
{
	var h = window.location.hash.substr(1),g=[];
	if(h.length>0)
	{
		var p = h.split('&');
		for(var i=0,j=p.length;i<j;++i)
		{
			var a = p[i].split('=');
			if(a[1]) g[a[0]] = a[1];
		}
	}
	if(!g['object']){
		$('#colLeftb').html('')
	};
	
	//new object detected
	if(g['object'] && g['object']!=objectName)
	{
		var o = $('#object_'+g['object']);
		selectObject(g['object'],o.attr('data-label'),o.attr('data-htype'),o.attr('data-fields'));
	};
	
	
	if(!g['id']){
		$('#colMidb').empty();$('#colRightb').empty();
	};
	if(g['id'] && g['id']!=objectId){
		getContent(g['id'])
	};
	
	if(g['connect_to_object'] && g['connect_to_id'])
	{
		createContent();
		window.setTimeout(function()
		{
			$.post('crud.php?action=saveReferences&projectName='+projectName+'&objectName='+objectName+'&objectId='+objectId+'&referenceName='+g['connect_to_object'], 
			{ 
				order: 'l[]='+g['connect_to_id'] 
			}, 
			function(data) {
				getContent(objectId);
			});
		}, 1000);
	};

	window.setTimeout(checkHash, 3000);
};

var hierarchy='', fieldNames='id';

function selectObject(name, label, htype, addfields) 
{
	window.location.hash = actHash = 'object='+name;
	hierarchy = htype;
	fieldNames = addfields;
	init(name);
	activateTab(2, label);
}

function init(name, id)
{
	objectName = name;
	$('#colMidb').html('');
	$('#colRightb').html('');
	//$('#objectSelect').selectmenu("value", name);
	if(!store[objectName])
	{
		store[objectName]={	offset:0 }
	}
	getList(id);
}

// activate+set label + deactivate/clean further tabs
function activateTab(index, label)
{
	var t = ['wizard','home','left','mid','right'];
	var b = ['','colLeftb','colMidb','colRightb'];
	var d = [];
	// loop the Tabs backwards
	for(var i=4; i>index; --i)
	{
		d.push(i);// deaktivier-array für überhängige tabs füllen
		
		$('#lbl_'+t[i]).html('&nbsp;');// clear tab-label
		$('#'+b[i]).html('');// clear workspace
	}
	
	$('#lbl_'+t[index]).html(label);// set new Tab-Label
	
	// set Tab-Properties
	$('#wrapper').tabs(
	{
		disabled: d, 
		selected: index// 1.9 selected>activated ??
	});
}


$(document).ready(function()
{
	
	$('#wrapper').tabs({selected: 1,disabled: [2,3,4]});//(de)activate Tabs at Start
	
	// style Buttons
	styleButtons('body');
	
	// Resizable Spalten maxHeight:1300,maxWidth:850,minHeight:300,minWidth:200, 
	
	var dw = $(document).width(), 
		w = Math.floor((dw-50)/4);
	
	ch = $(document).height()-70;
	
	// wie viele Listenelemente passen auf den Screen?
	limitNumber = Math.floor((ch-50)/32);
	
	//limitNumber = 5;
	// Spaltengrösse anpassen
	
	//var cw = (store['cw']) ? store['cw'] : [w, w*2, w];
	
	
	
	originalFontSize = $('html').css('font-size');
	if(store['fnts']) {
		$('html').css('font-size', store['fnts']);
	}
	
	
	window.setTimeout(checkHash, 500);
	
});// document.ready END /////////////////////////////////////////////////////



function offSet(name, no) 
{
	store[name]['offset'] += no;
};



function getPlainList(id) 
{
	
	var o = $('#object_'+objectName);
		//selectObject(g['object'],o.attr('data-label'),o.attr('data-htype'),o.attr('data-fields'))
		
	//var sel=$("#objectSelect>option:selected");
	//var srt=store[objectName]['srt'], lbls=store[objectName]['lbls'], larr=[];
	
	
	
	$.get('crud.php', 
	{
		action: 'getList', 
		projectName: projectName, 
		objectName: objectName, 
		objectId: id, 
		limit: limitNumber,
		offset: parseInt(store[objectName]['offset']),
		mobile: 1
	}, 
	function(data) 
	{
		if(id && !isNaN(data))
		{
			store[objectName]['offset'] = parseInt(data);
			getPlainList();
			return;
		}
		
		$('#colLeftb').html(data);
		styleButtons('mainlistHead');
		$("#mainlist li").click(function()
		{
			var id=$(this).attr('rel');
			location.hash = 'object='+objectName+'&id='+id;
			getContent(id);
		});
	});
};

function specialAction(url, target, post)
{
	$.post(url, 
	{
		val: post
	},
	function(data) 
	{
		if(target) 
		{
			$('#'+target).html(data);
			$('#'+target+' #accordion').accordion({collapsible:true});
			$('#'+target+' #tabs').tabs();
			prettify(target);
		}
		else 
		{
			message(data);
		}
	});
};


function getTreeList(id, treeType)
{
	// define store-Object if not available
	if(!store[objectName]) store[objectName] = {};
	if(!store[objectName]['stat']) store[objectName]['stat'] = [];
	
	// define the GET-Parameter-String for this Object / 
	var params = '&projectName='+projectName+'&objectName='+objectName+'&objectId='+id+'&tType='+treeType+'&limit='+limitNumber+'&offset=';
	
	// get the actual Path from the Element with the id up to Root-Level
	if (id)
	{
		$.get('crud.php?action=getTreePath'+params,
		function(data)
		{
			$.merge( store[objectName]['stat'], data.split(',') );
			store[objectName]['stat'] = $.unique(store[objectName]['stat']);
		})
	};
	
	// get the Tree itself
	$.get('crud.php?action=getTreeHead'+params,
	function(data)
	{
		$('#colLeftb').html(data);
		
		styleButtons('mainlistHead');
		
		$("#mainlist2").folderTree(
		{
			script: 'crud.php?action=getTreeList'+params,
			loadMessage: 'load Data' + '...',
			statPut: function(id, stat)
			{
				if(stat===0)
				{
					store[objectName]['stat'] = $.grep(store[objectName]['stat'], function(value){return value!=id;}) 
				}
				if(stat===1 && $.inArray(id, store[objectName]['stat'])==-1)
				{ 
					store[objectName]['stat'].push(id)
				}
			},
			// open all active Nodes defined in the Path above
			statCheck: function(target)
			{
				target.find('li>span').each(function(i) {
					$(this).on('click', function(e) {
						getContent($(e.target).data('id'));
					});
					if(jQuery.inArray($(this).attr('alt'), store[objectName]['stat']) > -1) {
						$(this).find(".ui-icon-circle-plus").trigger('click');
					}
				})
			}
		})
	})
	
};


function showPagination() 
{
	if($('#pagination').html() != '') 
	{
		$('#pagination').toggle();
		return;
	}
	
	var o = $('#object_'+objectName);
	//selectObject(g['object'],o.attr('data-label'),o.attr('data-htype'),o.attr('data-fields'))
		
	//var sel=$("#objectSelect>option:selected");
	//var srt=store[objectName]['srt'], lbls=store[objectName]['lbls'], srtarr=[], larr=[];
	
	//for(var i=0,j=srt.length; i<j; ++i){ srtarr.push(srt[i].name + srt[i].value); }// sort-Parameter
	
	$.get('crud.php', 
	{ 
		action: 'getPagination', 
		projectName: projectName, 
		objectName: objectName, 
		limit: limitNumber, 
		offset: parseInt(store[objectName]['offset']) 
	}, 
	function(data) 
	{
		$('#pagination').html(data);
	})
};


function setPagination(n)
{
	store[objectName]['offset'] = limitNumber * n;
	getList();
};

// Haupt-Liste laden (wird automatisch aufgerufen)
function getList(id) 
{
	
	// autocomplete-suchbox
	$('#searchbox').autocomplete({
		source: 'inc/php/search.php?projectName='+projectName+'&objectName='+objectName,
		select: function(event,ui)
		{ 
			getContent(ui.item.id);
			return false;
		},
		minLength: 3
	});
	
	var ic = (objectId.length>0);
	
	if(hierarchy == 'Tree' || hierarchy == 'Graph')
	{
		getTreeList(id, hierarchy);
		if(id)
		{
			setTimeout(function(){
				$('#mainlist2 .folder[rel="'+id+'"]').addClass('sel')
			}, 2000)
		}
	}
	else
	{
		getPlainList(id);
		if(id)
		{
			setTimeout(function(){
				$('#mainlist li[rel="'+id+'"]').addClass('ui-selected')
			}, 1000)
		}
	}
	
	// fill object-wizard-links 
	setTimeout(function(){
		$('#objectWizards').html($('#objectWizardHtml').html());
	}, 1000);
};



// Haupt-Inhalt laden (automatisch oder per klick)
function getContent(id) 
{
	
	if(id=='undefined') {
		return false;
	};
	//alert($('#objectSelect')[$('#objectSelect').selectmenu('index')].getAttribute('alt'));
	//alert($('#objectSelect:nth-child('+$('#objectSelect').selectmenu('index')+')').attr('alt'));
	
	//alert($('#objectSelect option[value="'+objectName+'"]').attr('data-htype'));
	
	window.location.hash = actHash = '#object='+objectName+'&id='+id;// store the ID in URL
	objectId = id;
	$('#colMidb').empty();
	$('#colRightb').empty();
	$('#relSel').empty();
	//$('#colMidb').children().remove();
	
	$.get('crud.php', 
	{ 
		action: 'getContent', 
		projectName: projectName, 
		objectName: objectName, 
		objectId: id 
	}, 
	function(data) 
	{
		
		$('#colMidb').html(data);
		
		// content-processing
		$('#accordion').accordion({collapsible:true});
		$('#tabs').tabs();
		prettify('colMidb');
		
		$('#referenceSelect').appendTo('#relSel');
		
		
		//$('#colMidb textarea,input').autoResize({maxWidth: ($("#colMid").width()-200)});
		//$('#colMidb textarea').autosize();
		// show related Objects
		//$('#referenceSelect').selectmenu({style:'popup', icons:[{find: '.relType', icon: 'ui-icon-link'},	{find: '.relTypes', icon: 'ui-icon-arrowthick-2-e-w'}, {find: '.relTypec', icon: 'ui-icon-arrowthick-1-se'}, {find: '.relTypep', icon: 'ui-icon-arrowthick-1-nw'}]});
		
		//alert($('l_'+id+'>a').html());
		activateTab(3, id);
		
		// set correct Label for Content-Tab
		window.setTimeout(function() {
			var lbl = $('#colLeftb li[rel="'+id+'"]').text(), 
			lbla = (lbl.length>7?'...':''), 
			lblo = (lbl.length<2?id:lbl.substr(0,7)+lbla);
			$('#lbl_mid').html(lblo);
		},1000);
	});
	// selector verschieben referenceSelect
	
	getConnectedReferences(id);
};

function getConnectedReferences(id, off) 
{

	if(!off) off=0;
	$.get('crud.php', 
	{ 
		action: 'getConnectedReferences', 
		projectName: projectName, 
		objectName: objectName, 
		objectId: id,
		limit: limitNumber, 
		offset: off
	}, 
	function(data) 
	{
		$('#actRel').html(data);
		$('#actRel a').click(function(e)
		{
			actHash = '', 
			objectName = '', 
			objectId = '', 
			objectHType = false;
			location.hash = 'object='+$(this).attr('data-object')+'&id='+$(this).attr('data-id');
			checkHash();
			$(document).scrollTop(0);
			e.preventDefault();
		});
	});
};

// Inhalte im Formular speichern
function saveContent(id) 
{
	
	// embed-wizard-transfer
	$('#colMidb .eframe').each(function() {
		var f=$(this), fd=f[0].contentWindow||f[0]; // iframe
		fd.transfer();// run function
	});
	
	$.post('crud.php?action=saveContent&projectName='+projectName+'&objectName='+objectName+'&objectId='+id, 
	$('#colMidb').serialize(), 
	function(data) 
	{
		message(data);
		getList(id);
	});
};

// neuer Eintrag
function createContent() {
	$.get('crud.php', 
	{ 
		action: 'createContent', 
		projectName: projectName, 
		objectName: objectName
	}, 
	function(data) 
	{
		$('#colRightb').html('');
		if(data.substr(0,1)=='_') 
		{
			message(data.substr(1));
			return false;
		}else {
			objectId = data;
			window.location.hash = '#object='+objectName+'&id='+data;
			getContent(data);
			message('entry created'+' (ID:'+data+')');
		}
	});
};

// lösche Eintrag
function deleteContent(id) 
{
	var q = confirm('delete entry'+' (ID:'+id+')?');
	if(q) {
		$.get('crud.php', 
		{ 
			action: 'deleteContent', 
			projectName: projectName, 
			objectName: objectName, 
			objectId: id
		}, 
		function(data) 
		{
			message(data);
			window.location.hash = 'object='+objectName;
			location.reload();
			//$('#colMidb').empty();$('#colRightb').empty();getList();
		});
	}
};

// Liste der Referenzen zu dem Eintrag (vom Typ X)
function getReferences(id, offs) 
{
	//var o = $('#object_'+objectName);
		//selectObject(g['object'],o.attr('data-label'),o.attr('data-htype'),o.attr('data-fields'))
		
	var referenceName = $('#referenceSelect>option:selected').val();
	
	//var o = $('#object_'+rn);
	
	// Listenanzeige-Parameter sammeln
	/*if(srt){for(var i=0,j=srt['srt'].length; i<j; ++i){ 
		if(srt['srt'][i].name.substr(0,3)=='LA_') larr.push(srt['srt'][i].name.substr(3));
	}}*/
	
	if(rn)
	{
		
		$.get('crud.php', 
		{ 
			action: 'getReferences', 
			projectName: projectName, 
			objectName: objectName, 
			objectId: id, 
			referenceName: referenceName, 
			limit: limitNumber, 
			offset: offs
		}, 
		function(data) 
		{
			
			$('#colRightb').html(data);
			
			styleButtons('colRightb');
			
			// Listen sortierbar machen
			$( '#sublist, #sublist2' ).sortable(
			{
				items: 'li:not(.ui-state-disabled)',
				connectWith: '.rlist',
				placeholder: 'ui-state-highlight',
				handle: 'span'
			});
			
			// akzeptiere bei Parent-Listen nur ein Element (Anzahl mit dem Überschrift-Element == 2)
			$('.sublistParent').bind('sortreceive', function(event, ui) 
			{
				if ($(this).children().length > 2) 
				{
					//alert($(this).children().length);
					$(ui.sender).sortable('cancel');
					message('action cancelled, because only one connection allowed', true, 5000);
					return;
				};
			});
			
			function saveReference() {
				
				$.post('crud.php?action=saveReferences&projectName='+projectName+'&objectName='+objectName+'&objectId='+id+'&referenceName='+$('#referenceSelect').val(), 
				{ 
					order: $('#sublist').sortable('serialize', 'id') 
				}, 
				function(data) {
					message(data);
				});
			};
			
			// speichere die neue Liste(nanordnung) im Hintergrund
			$("#sublist").bind("sortupdate", function(event, ui) 
			{
				saveReference();
			});
			
			// Suche in Relationen initiieren
			$('#referenceSearchbox').autocomplete(
			{
				source: 'inc/php/search.php?projectName='+projectName+'&objectName='+referenceName,
				select: function(event, ui)
				{
					// 
					var lnk = 'location=\'backend.php?project='+projectName+'#object='+referenceName+'&id='+ui.item.id+'\'';
					var htm = '<h4>'+'what do you want to do?'+'</h4><button type="button" onclick="'+lnk+'">'+'show this entry'+'</button> ';
					
					// Eintrag noch nicht in der oberen Liste == verknüpft und kein Parent-Element vorhanden?
					if($('#sublist').find('#l_'+ui.item.id).length==0 && $('.sublistParent').children().length<2) 
					{
						htm += '<button type="button" id="insertListItem">'+'add this entry to the connection-list'+'</button>';
					};
					
					$( "#dialogb1" ).html(htm);
					$( "#dialogb1 button" ).button();
					$( "#dialog1" ).show();
					
					$('#insertListItem').click(function() 
					{
						$('#sublist').html( $('#sublist').html() + '<li id="l_'+ui.item.id+'" class="ui-state-default ui-selectee"><div onclick="'+lnk+'">'+ui.item.label+'</div></li>');
						$(this).hide();
						saveReference();
					});
					
					return false;
				},
				minLength: 3
			});
			
			$('#colRightb a').click(function(e)
			{
				actHash = '', 
				objectName = '', 
				objectId = '', 
				objectHType = false;
				location.hash = 'object='+$(this).attr('data-object')+'&id='+$(this).attr('data-id');
				checkHash();
				$(document).scrollTop(0);
				e.preventDefault();
			});
			
		});
		activateTab(4, referenceName);
	}
	else
	{
		//$('#colRightb').html('');
		getConnectedReferences(id);
	}
};


// transform some Form-Elements into UI-Elements (within Element with container-id)
function prettify(id) 
{
	styleButtons(id);
	
	// Date(Time(Stamp))-Picker
	// Date
	var opts = {
					preset: 'date',
					dateOrder: 'ddmmyy',
					dateFormat: 'dd-mm-yy', 
					theme: 'default', 
					mode: 'mixed',
					dayText: 'Day',
					monthText: 'Month',
					yearText: 'Year',
					hourText: 'Hour',
					minuteText: 'Minute',
					secText: 'Second'
				};
	$('#'+id+' .date').scroller(opts);
	
	// Datetime
	opts['preset']='datetime';
	opts['timeFormat'] = 'HH:ii:ss';
	opts['timeWheels'] = 'HHiiss';
	$('#'+id+' .datetime').scroller(opts);
	
	// Timestamp
	opts['dateFormat'] = 'mm dd yy';
	opts['timeFormat'] = 'H:m:s';
	opts['onShow'] = function(html, inst)
	{
		var ts = parseInt($(this).val());
		var d = new Date((ts>0) ? ts*1000 : new Date().getTime());
		var a = [d.getDate(),d.getMonth(),d.getFullYear(),d.getHours(),d.getMinutes(),d.getSeconds()];
		$(this).scroller('setValue', a);
	}
	opts['onSelect'] = function(dateText, inst) {
		$(this).val(Math.floor(Date.parse(dateText)/1000))
	}// set value
	
	$('#'+id+' .timestamp').scroller(opts);
	
	$('#'+id+' .slider').each(function() 
	{
		var rl = $(this).attr('rel').split('-');
		$(this).slider(
		{
			range: 'min',
			value: parseInt($(this).attr('alt')),
			min: parseInt(rl[0]),
			max: parseInt(rl[1]),
			slide: function( event, ui ) {
				$('#input_' + $(this).attr('title') ).val( ui.value );
			}
		});
	});
	$('#'+id+' .checkbox').button({
		icons: {primary: 'ui-icon-circle-close'},
		text: false 
	})
	.click(function () {
		$(this).button('option', 'icons', {primary: this.checked ? 'ui-icon-circle-check':'ui-icon-circle-close'})
	})
	.filter(":checked").button({icons: {primary: "ui-icon-circle-check"}});
};

function styleButtons(id)
{ 
	$('#'+id+' button').each(function() {
		$(this).button( {icons:{ primary: 'ui-icon-'+$(this).attr('rel')}, text: (($(this).text()=='.')?false:true)})
	})
};



function logout() {
	store['lastPage'] = window.location.hash.substr(1);
	// there is a real user
	if(userId!=0){
		$.post('extensions/user/wizards/settings/save.php?projectName='+projectName, { id: userId, json: JSON.stringify(store) }, function(d) {
			window.location='index.php?project='+projectName;
			//message(d);
		});
	}else{
		window.location='index.php?project='+projectName;
	}
	
};



function getFrame(url) 
{
	
	//var wh = (store['dwh']) ? store['dwh'] : [$(document).width()-150, $(document).height()-300];
	var h=$(document).height(), s=$(window).scrollTop();
	$("#dialog2").css({'display':'block','z-index':1200,'width':'100%','height':h,'top':s});
	$("#dialogb2").css({'height':h-50});
	$("#dialogb2").attr('src',url);
	
};

function openAdminWizard(el) 
{
	if(el.value!='') window.open('admin/'+el.value+'/index.php?project='+projectName, el.value);
	//$('#adminWizard').selectmenu("index",0);// reset dropdown
};

function openGlobalWizard(el) 
{
	var url = el.value;
	if(url!=''){
		getFrame(template(url,window));
		//$('#globalWizard').selectmenu("index",0);// reset dropdown
	}
};


// target-id, wizard-type
function getWizard(id, type, add)
{
	if(type.substr(0,1)=='#') type=$(type).val();
	targetFieldId = id;
	getFrame( 'wizards/' + type + '/index.php?projectName='+projectName+'&objectName='+objectName+'&objectId='+objectId+((add)?'&'+add:'') );
};





 /*
 * jQuery UI Selectmenu version 1.4.0pre
 *
 * Copyright (c) 2009-2010 filament group, http://filamentgroup.com
 * Copyright (c) 2010-2012 Felix Nagel, http://www.felixnagel.com
 * Licensed under the MIT (MIT-LICENSE.txt)
 *
 * https://github.com/fnagel/jquery-ui/wiki/Selectmenu
 */

 (function($){$.widget("ui.selectmenu", {options:{appendTo:"body",typeAhead:1000,style:'dropdown',positionOptions:null,width:null,menuWidth:null,handleWidth:26,maxHeight:null,icons:null,format:null,escapeHtml:false,bgImage:function(){}},_create:function(){var self=this, o=this.options;var selectmenuId=this.element.uniqueId().attr("id");this.ids=[ selectmenuId, selectmenuId + '-button', selectmenuId + '-menu' ];this._safemouseup=true;this.isOpen=false;this.newelement=$('<a />', {'class':'ui-selectmenu ui-widget ui-state-default ui-corner-all','id' :this.ids[ 1 ],'role':'button','href':'#nogo','tabindex':this.element.attr('disabled') ? 1 :0,'aria-haspopup':true,'aria-owns':this.ids[ 2 ]});this.newelementWrap=$("<span />").append(this.newelement).insertAfter(this.element);var tabindex=this.element.attr('tabindex');if (tabindex){this.newelement.attr('tabindex', tabindex);}this.newelement.data('selectelement', this.element);this.selectmenuIcon=$('<span class="ui-selectmenu-icon ui-icon"></span>').prependTo(this.newelement);this.newelement.prepend('<span class="ui-selectmenu-status" />');this.element.bind({'click.selectmenu':function(event){self.newelement.focus();event.preventDefault();}});this.newelement.bind('mousedown.selectmenu', function(event){self._toggle(event, true);if (o.style == "popup"){self._safemouseup=false;setTimeout(function(){ self._safemouseup=true; }, 300);}event.preventDefault();}).bind('click.selectmenu', function(event){event.preventDefault();}).bind("keydown.selectmenu", function(event){var ret=false;switch (event.keyCode){case $.ui.keyCode.ENTER:ret=true;break;case $.ui.keyCode.SPACE:self._toggle(event);break;case $.ui.keyCode.UP:if (event.altKey){self.open(event);} else {self._moveSelection(-1);}break;case $.ui.keyCode.DOWN:if (event.altKey){self.open(event);} else {self._moveSelection(1);}break;case $.ui.keyCode.LEFT:self._moveSelection(-1);break;case $.ui.keyCode.RIGHT:self._moveSelection(1);break;case $.ui.keyCode.TAB:ret=true;break;case $.ui.keyCode.PAGE_UP:case $.ui.keyCode.HOME:self.index(0);break;case $.ui.keyCode.PAGE_DOWN:case $.ui.keyCode.END:self.index(self._optionLis.length);break;default:ret=true;}return ret;}).bind('keypress.selectmenu', function(event){if (event.which > 0){self._typeAhead(event.which, 'mouseup');}return true;}).bind('mouseover.selectmenu', function(){if (!o.disabled) $(this).addClass('ui-state-hover');}).bind('mouseout.selectmenu', function(){if (!o.disabled) $(this).removeClass('ui-state-hover');}).bind('focus.selectmenu', function(){if (!o.disabled) $(this).addClass('ui-state-focus');}).bind('blur.selectmenu', function(){if (!o.disabled) $(this).removeClass('ui-state-focus');});$(document).bind("mousedown.selectmenu-" + this.ids[ 0 ], function(event){if (self.isOpen && self.ids[ 1 ] != event.target.offsetParent.id){self.close(event);}});this.element.bind("click.selectmenu", function(){self._refreshValue();}).bind("focus.selectmenu", function(){if (self.newelement){self.newelement[ 0 ].focus();}});if (!o.width){o.width=this.element.outerWidth();}this.newelement.width(o.width);this.element.hide();this.list=$('<ul />', {'class':'ui-widget ui-widget-content','aria-hidden':true,'role':'listbox','aria-labelledby':this.ids[ 1 ],'id':this.ids[ 2 ]});this.listWrap=$("<div />", {'class':'ui-selectmenu-menu'}).append(this.list).appendTo(o.appendTo);this.list.bind("keydown.selectmenu", function(event){var ret=false;switch (event.keyCode){case $.ui.keyCode.UP:if (event.altKey){self.close(event, true);} else {self._moveFocus(-1);}break;case $.ui.keyCode.DOWN:if (event.altKey){self.close(event, true);} else {self._moveFocus(1);}break;case $.ui.keyCode.LEFT:self._moveFocus(-1);break;case $.ui.keyCode.RIGHT:self._moveFocus(1);break;case $.ui.keyCode.HOME:self._moveFocus(':first');break;case $.ui.keyCode.PAGE_UP:self._scrollPage('up');break;case $.ui.keyCode.PAGE_DOWN:self._scrollPage('down');break;case $.ui.keyCode.END:self._moveFocus(':last');break;case $.ui.keyCode.ENTER:case $.ui.keyCode.SPACE:self.close(event, true);$(event.target).parents('li:eq(0)').trigger('mouseup');break;case $.ui.keyCode.TAB:ret=true;self.close(event, true);$(event.target).parents('li:eq(0)').trigger('mouseup');break;case $.ui.keyCode.ESCAPE:self.close(event, true);break;default:ret=true;}return ret;}).bind('keypress.selectmenu', function(event){if (event.which > 0){self._typeAhead(event.which, 'focus');}return true;}).bind('mousedown.selectmenu mouseup.selectmenu', function(){ return false; });$(window).bind("resize.selectmenu-" + this.ids[ 0 ], $.proxy(self.close, this));},_init:function(){var self=this, o=this.options;var selectOptionData=[];this.element.find('option').each(function(){var opt=$(this);selectOptionData.push({value:opt.attr('value'),text:self._formatText(opt.text(), opt),selected:opt.attr('selected'),disabled:opt.attr('disabled'),classes:opt.attr('class'),typeahead:opt.attr('typeahead'),parentOptGroup:opt.parent('optgroup'),bgImage:o.bgImage.call(opt)});});var activeClass=(self.options.style == "popup") ? " ui-state-active" :"";this.list.html("");if (selectOptionData.length){for (var i=0; i < selectOptionData.length; i++){var thisLiAttr={ role :'presentation' };if (selectOptionData[ i ].disabled){thisLiAttr[ 'class' ]='ui-state-disabled';}var thisAAttr={html:selectOptionData[ i ].text || '&nbsp;',href:'#nogo',tabindex :-1,role:'option','aria-selected' :false};if (selectOptionData[ i ].disabled){thisAAttr[ 'aria-disabled' ]=selectOptionData[ i ].disabled;}if (selectOptionData[ i ].typeahead){thisAAttr[ 'typeahead' ]=selectOptionData[ i ].typeahead;}var thisA=$('<a/>', thisAAttr).bind('focus.selectmenu', function(){$(this).parent().mouseover();}).bind('blur.selectmenu', function(){$(this).parent().mouseout();});var thisLi=$('<li/>', thisLiAttr).append(thisA).data('index', i).addClass(selectOptionData[ i ].classes).data('optionClasses', selectOptionData[ i ].classes || '').bind("mouseup.selectmenu", function(event){if (self._safemouseup && !self._disabled(event.currentTarget) && !self._disabled($(event.currentTarget).parents("ul > li.ui-selectmenu-group "))){self.index($(this).data('index'));self.select(event);self.close(event, true);}return false;}).bind("click.selectmenu", function(){return false;}).bind('mouseover.selectmenu', function(e){if (!$(this).hasClass('ui-state-disabled') && !$(this).parent("ul").parent("li").hasClass('ui-state-disabled')){e.optionValue=self.element[ 0 ].options[ $(this).data('index') ].value;self._trigger("hover", e, self._uiHash());self._selectedOptionLi().addClass(activeClass);self._focusedOptionLi().removeClass('ui-selectmenu-item-focus ui-state-hover');$(this).removeClass('ui-state-active').addClass('ui-selectmenu-item-focus ui-state-hover');}}).bind('mouseout.selectmenu', function(e){if ($(this).is(self._selectedOptionLi())){$(this).addClass(activeClass);}e.optionValue=self.element[ 0 ].options[ $(this).data('index') ].value;self._trigger("blur", e, self._uiHash());$(this).removeClass('ui-selectmenu-item-focus ui-state-hover');});if (selectOptionData[ i ].parentOptGroup.length){var optGroupName='ui-selectmenu-group-' + this.element.find('optgroup').index(selectOptionData[ i ].parentOptGroup);if (this.list.find('li.' + optGroupName).length){this.list.find('li.' + optGroupName + ':last ul').append(thisLi);} else {$('<li role="presentation" class="ui-selectmenu-group ' + optGroupName + (selectOptionData[ i ].parentOptGroup.attr("disabled") ? ' ' + 'ui-state-disabled" aria-disabled="true"' :'"') + '><span class="ui-selectmenu-group-label">' + selectOptionData[ i ].parentOptGroup.attr('label') + '</span><ul></ul></li>').appendTo(this.list).find('ul').append(thisLi);}} else {thisLi.appendTo(this.list);}if (o.icons){for (var j in o.icons){if (thisLi.is(o.icons[ j ].find)){thisLi.data('optionClasses', selectOptionData[ i ].classes + ' ui-selectmenu-hasIcon').addClass('ui-selectmenu-hasIcon');var iconClass=o.icons[ j ].icon || "";thisLi.find('a:eq(0)').prepend('<span class="ui-selectmenu-item-icon ui-icon ' + iconClass + '"></span>');if (selectOptionData[ i ].bgImage){thisLi.find('span').css('background-image', selectOptionData[ i ].bgImage);}}}}}} else {$(' <li role="presentation"><a href="#nogo" tabindex="-1" role="option"></a></li>').appendTo(this.list);}var isDropDown=(o.style == 'dropdown');this.newelement.toggleClass('ui-selectmenu-dropdown', isDropDown).toggleClass('ui-selectmenu-popup', !isDropDown);this.list.toggleClass('ui-selectmenu-menu-dropdown ui-corner-bottom', isDropDown).toggleClass('ui-selectmenu-menu-popup ui-corner-all', !isDropDown).find('li:first').toggleClass('ui-corner-top', !isDropDown).end().find('li:last').addClass('ui-corner-bottom');this.selectmenuIcon.toggleClass('ui-icon-triangle-1-s', isDropDown).toggleClass('ui-icon-triangle-2-n-s', !isDropDown);if (o.style == 'dropdown'){this.list.width(o.menuWidth ? o.menuWidth :o.width);} else {this.list.width(o.menuWidth ? o.menuWidth :o.width - o.handleWidth);}this.list.css('height', 'auto');var listH=this.listWrap.height();var winH=$(window).height();var maxH=o.maxHeight ? Math.min(o.maxHeight, winH) :winH / 3;if (listH > maxH) this.list.height(maxH);this._optionLis=this.list.find('li:not(.ui-selectmenu-group)');if (this.element.attr('disabled')){this.disable();} else {this.enable();}this._refreshValue();this._selectedOptionLi().addClass('ui-selectmenu-item-focus');clearTimeout(this.refreshTimeout);this.refreshTimeout=window.setTimeout(function (){self._refreshPosition();}, 200);},destroy:function(){this.element.removeData(this.widgetName).removeClass('ui-selectmenu-disabled' + ' ' + 'ui-state-disabled').removeAttr('aria-disabled').unbind(".selectmenu");$(window).unbind(".selectmenu-" + this.ids[ 0 ]);$(document).unbind(".selectmenu-" + this.ids[ 0 ]);this.newelementWrap.remove();this.listWrap.remove();this.element.unbind(".selectmenu").show();$.Widget.prototype.destroy.apply(this, arguments);},_typeAhead:function(code, eventType){var self=this,c=String.fromCharCode(code).toLowerCase(),matchee=null,nextIndex=null;if (self._typeAhead_timer){window.clearTimeout(self._typeAhead_timer);self._typeAhead_timer=undefined;}self._typeAhead_chars=(self._typeAhead_chars === undefined ? "" :self._typeAhead_chars).concat(c);if (self._typeAhead_chars.length < 2 || (self._typeAhead_chars.substr(-2, 1) === c && self._typeAhead_cycling)){self._typeAhead_cycling=true;matchee=c;} else {self._typeAhead_cycling=false;matchee=self._typeAhead_chars;}var selectedIndex=(eventType !== 'focus' ? this._selectedOptionLi().data('index') :this._focusedOptionLi().data('index')) || 0;for (var i=0; i < this._optionLis.length; i++){var thisText=this._optionLis.eq(i).text().substr(0, matchee.length).toLowerCase();if (thisText === matchee){if (self._typeAhead_cycling){if (nextIndex === null)nextIndex=i;if (i > selectedIndex){nextIndex=i;break;}} else {nextIndex=i;}}}if (nextIndex !== null){this._optionLis.eq(nextIndex).find("a").trigger(eventType);}self._typeAhead_timer=window.setTimeout(function(){self._typeAhead_timer=undefined;self._typeAhead_chars=undefined;self._typeAhead_cycling=undefined;}, self.options.typeAhead);},_uiHash:function(){var index=this.index();return {index:index,option:$("option", this.element).get(index),value:this.element[ 0 ].value};},open:function(event){if (this.newelement.attr("aria-disabled") != 'true'){var self=this,o=this.options,selected=this._selectedOptionLi(),link=selected.find("a");self._closeOthers(event);self.newelement.addClass('ui-state-active');self.list.attr('aria-hidden', false);self.listWrap.addClass('ui-selectmenu-open');if (o.style == "dropdown"){self.newelement.removeClass('ui-corner-all').addClass('ui-corner-top');} else {this.list.css("left", -5000).scrollTop(this.list.scrollTop() + selected.position().top - this.list.outerHeight() / 2 + selected.outerHeight() / 2).css("left", "auto");}self._refreshPosition();if (link.length){link[ 0 ].focus();}self.isOpen=true;self._trigger("open", event, self._uiHash());}},close:function(event, retainFocus){if (this.newelement.is('.ui-state-active')){this.newelement.removeClass('ui-state-active');this.listWrap.removeClass('ui-selectmenu-open');this.list.attr('aria-hidden', true);if (this.options.style == "dropdown"){this.newelement.removeClass('ui-corner-top').addClass('ui-corner-all');}if (retainFocus){this.newelement.focus();}this.isOpen=false;this._trigger("close", event, this._uiHash());}},change:function(event){this.element.trigger("change");this._trigger("change", event, this._uiHash());},select:function(event){if (this._disabled(event.currentTarget)){ return false; }this._trigger("select", event, this._uiHash());},widget:function(){return this.listWrap.add(this.newelementWrap);},_closeOthers:function(event){$('.ui-selectmenu.ui-state-active').not(this.newelement).each(function(){$(this).data('selectelement').selectmenu('close', event);});$('.ui-selectmenu.ui-state-hover').trigger('mouseout');},_toggle:function(event, retainFocus){if (this.isOpen){this.close(event, retainFocus);} else {this.open(event);}},_formatText:function(text, opt){if (this.options.format){text=this.options.format(text, opt);} else if (this.options.escapeHtml){text=$('<div />').text(text).html();}return text;},_selectedIndex:function(){return this.element[ 0 ].selectedIndex;},_selectedOptionLi:function(){return this._optionLis.eq(this._selectedIndex());},_focusedOptionLi:function(){return this.list.find('.ui-selectmenu-item-focus');},_moveSelection:function(amt, recIndex){if (!this.options.disabled){var currIndex=parseInt(this._selectedOptionLi().data('index') || 0, 10);var newIndex=currIndex + amt;if (newIndex < 0){newIndex=0;}if (newIndex > this._optionLis.size() - 1){newIndex=this._optionLis.size() - 1;}if (newIndex === recIndex){return false;}if (this._optionLis.eq(newIndex).hasClass('ui-state-disabled')){(amt > 0) ? ++amt :--amt;this._moveSelection(amt, newIndex);} else {this._optionLis.eq(newIndex).trigger('mouseover').trigger('mouseup');}}},_moveFocus:function(amt, recIndex){if (!isNaN(amt)){var currIndex=parseInt(this._focusedOptionLi().data('index') || 0, 10);var newIndex=currIndex + amt;} else {var newIndex=parseInt(this._optionLis.filter(amt).data('index'), 10);}if (newIndex < 0){newIndex=0;}if (newIndex > this._optionLis.size() - 1){newIndex=this._optionLis.size() - 1;}if (newIndex === recIndex){return false;}var activeID='ui-selectmenu-item-' + Math.round(Math.random() * 1000);this._focusedOptionLi().find('a:eq(0)').attr('id', '');if (this._optionLis.eq(newIndex).hasClass('ui-state-disabled')){(amt > 0) ? ++amt :--amt;this._moveFocus(amt, newIndex);} else {this._optionLis.eq(newIndex).find('a:eq(0)').attr('id',activeID).focus();}this.list.attr('aria-activedescendant', activeID);},_scrollPage:function(direction){var numPerPage=Math.floor(this.list.outerHeight() / this._optionLis.first().outerHeight());numPerPage=(direction == 'up' ? -numPerPage :numPerPage);this._moveFocus(numPerPage);},_setOption:function(key, value){this.options[ key ]=value;if (key == 'disabled'){if (value) this.close();this.element.add(this.newelement).add(this.list)[ value ? 'addClass' :'removeClass' ]('ui-selectmenu-disabled ' + 'ui-state-disabled').attr("aria-disabled" , value);}},disable:function(index, type){if (typeof(index) == 'undefined'){this._setOption('disabled', true);} else {if (type == "optgroup"){this._toggleOptgroup(index, false);} else {this._toggleOption(index, false);}}},enable:function(index, type){if (typeof(index) == 'undefined'){this._setOption('disabled', false);} else {if (type == "optgroup"){this._toggleOptgroup(index, true);} else {this._toggleOption(index, true);}}},_disabled:function(elem){return $(elem).hasClass('ui-state-disabled');},_toggleOption:function(index, flag){var optionElem=this._optionLis.eq(index);if (optionElem){optionElem.toggleClass('ui-state-disabled', flag).find("a").attr("aria-disabled", !flag);if (flag){this.element.find("option").eq(index).attr("disabled", "disabled");} else {this.element.find("option").eq(index).removeAttr("disabled");}}},_toggleOptgroup:function(index, flag){var optGroupElem=this.list.find('li.ui-selectmenu-group-' + index);if (optGroupElem){optGroupElem.toggleClass('ui-state-disabled', flag).attr("aria-disabled", !flag);if (flag){this.element.find("optgroup").eq(index).attr("disabled", "disabled");} else {this.element.find("optgroup").eq(index).removeAttr("disabled");}}},index:function(newIndex){if (arguments.length){if (!this._disabled($(this._optionLis[ newIndex ])) && newIndex != this._selectedIndex()){this.element[ 0 ].selectedIndex=newIndex;this._refreshValue();this.change();} else {return false;}} else {return this._selectedIndex();}},value:function(newValue){if (arguments.length && newValue != this.element[ 0 ].value){this.element[ 0 ].value=newValue;this._refreshValue();this.change();} else {return this.element[ 0 ].value;}},_refreshValue:function(){var activeClass=(this.options.style == "popup") ? " ui-state-active" :"";var activeID='ui-selectmenu-item-' + Math.round(Math.random() * 1000);this.list.find('.ui-selectmenu-item-selected').removeClass("ui-selectmenu-item-selected" + activeClass).find('a').attr('aria-selected', 'false').attr('id', '');this._selectedOptionLi().addClass("ui-selectmenu-item-selected" + activeClass).find('a').attr('aria-selected', 'true').attr('id', activeID);var currentOptionClasses=(this.newelement.data('optionClasses') ? this.newelement.data('optionClasses') :"");var newOptionClasses=(this._selectedOptionLi().data('optionClasses') ? this._selectedOptionLi().data('optionClasses') :"");this.newelement.removeClass(currentOptionClasses).data('optionClasses', newOptionClasses).addClass(newOptionClasses).find('.ui-selectmenu-status').html(this._selectedOptionLi().find('a:eq(0)').html());this.list.attr('aria-activedescendant', activeID);},_refreshPosition:function(){var o=this.options,positionDefault={of:this.newelement,my:"left top",at:"left bottom",collision:'flip'};if (o.style == "popup"){var selected=this._selectedOptionLi();positionDefault.my="left top" + (this.list.offset().top - selected.offset().top - (this.newelement.outerHeight() + selected.outerHeight()) / 2);positionDefault.collision="fit";}this.listWrap.removeAttr('style').zIndex(this.element.zIndex() + 1001).position($.extend(positionDefault, o.positionOptions));}});})(jQuery);

/* jQuery Folder Tree Plugin
 * Version 1.00 - released (28 September 2011) Giannis Koutsaftakis
 * <htmlab> (http://www.htmlab.gr) Visit http://www.htmlab.gr/blog for more information
 * 28 September 2011
 * TERMS OF USE
 * This plugin is dual-licensed under the GNU General Public License and the MIT License and
 * is copyright 2008 A Beautiful Site, LLC. 
 */

(function($){$.fn.folderTree=function(o){if(!o) var o={};if(o.root == undefined) o.root=0; if(o.script == undefined) o.script='';if(o.loadMessage == undefined) o.loadMessage='Loading...';if(o.statCheck == undefined) o.statCheck=function(id,stat){};return this.each(function(){function create_node (script, dir, target, fol){if($(fol).hasClass("sel")){$(fol).removeClass('folder').addClass('waitb');}else{$(fol).removeClass('folder').addClass('wait');}$.post(script, { id:dir }, function(data){$(fol).removeClass('wait waitb').addClass('folder');if(dir == o.root){ target.html(data);target.find("ul.jqueryFolderTree").show();}else{target.append(data);target.find("ul.jqueryFolderTree").css({'padding-left':'20px'}).show();}o.statCheck(target);});};$(this).on("click", ".ui-icon-circle-plus", function(e){$(this).removeClass("ui-icon-circle-plus").addClass("ui-icon-circle-minus");var cur_li=$(this).closest("li");var ul_to=cur_li.find("ul.jqueryFolderTree").first();if(ul_to.length > 0){ul_to.show();}else{create_node(o.script+'0', $(this).data('id'), cur_li, $(this).next('li span.folder'));}});$(this).on("click", ".foldoffset", function(e){var p=$(this).parent('ul');$.post(o.script+$(this).data('offset'), { id:$(this).data('pid') }, function(data){var p2=$(data.replace('display:none', 'padding-left:20px'));p.replaceWith(p2);o.statCheck(p2);});});$(this).on("click", ".ui-icon-circle-minus", function(e){$(this).removeClass("ui-icon-circle-minus").addClass("ui-icon-circle-plus");var cur_li=$(this).closest("li");var ul_to=cur_li.find("ul.jqueryFolderTree").first();ul_to.hide();});$(this).on("click", ".folder", function(e){$(".folder", $(this).attr('id')).removeClass('sel');$(this).addClass('sel');});$(this).html('<ul class="jqueryFolderTree"><li class="wait">' + o.loadMessage + '</li></ul>');create_node(o.script+'0', o.root, $(this));});}})(jQuery);
/*
 * 
 * */
(function(b){function p(a,c){function l(){var a=document.body,b=document.documentElement;return Math.max(a.scrollHeight,a.offsetHeight,b.clientHeight,b.scrollHeight,b.offsetHeight)}function n(a){e=b("li.dw-v",a).eq(0).index();d=b("li.dw-v",a).eq(-1).index();f=h.height;H=j}function k(a){var b=h.headerText;return b?"function"==typeof b?b.call(B,a):b.replace(/{value}/i,a):""}function r(){j.temp=J&&null!==j.val&&j.val!=a.val()||null===j.values?h.parseValue(a.val()?a.val():"",j):j.values.slice(0);j.setValue(!0)}
function z(a,c,n,e,f){h.validate.call(B,x,n);b(".dww ul",x).each(function(e){var h=b(this),d=b('li[data-val="'+j.temp[e]+'"]',h),h=d.index(),k=d,d=h;if(!k.hasClass("dw-v")){for(var g=k,i=0,l=0;g.prev().length&&!g.hasClass("dw-v");)g=g.prev(),i++;for(;k.next().length&&!k.hasClass("dw-v");)k=k.next(),l++;(l<i&&l&&1==!f||!i||!g.hasClass("dw-v")||1==f)&&k.hasClass("dw-v")?d+=l:(k=g,d-=i);j.temp[e]=k.data("val")}g=e==n||void 0===n;if(h!=d||g)j.scroll(b(this),d,g?a:0,c,e)});j.change(e)}function p(){var a=
0,c=0,e=b(window).width(),n=b(window).height(),d=b(window).scrollTop(),h=b(".dwo",x),f=b(".dw",x),k,g;b(".dwc",x).each(function(){k=b(this).outerWidth(!0);a+=k;c=k>c?k:c});k=a>e?c:a;f.width(k);k=f.outerWidth();g=f.outerHeight();f.css({left:(e-k)/2,top:d+(n-g)/2});h.height(0).height(l())}function y(a){var b=+a.data("pos")+1;m(a,b>d?e:b,1)}function F(a){var b=+a.data("pos")-1;m(a,b<e?d:b,2)}var j=this,B=a,a=b(B),M,h=b.extend({},D),O,x,N={},P={},J=a.is("input"),K=!1;j.enable=function(){h.disabled=!1;
J&&a.prop("disabled",!1)};j.disable=function(){h.disabled=!0;J&&a.prop("disabled",!0)};j.scroll=function(a,b,c,e,n){var k=(O-b)*h.height;a.attr("style",(c?I+"-transition:all "+c.toFixed(1)+"s ease-out;":"")+(E?I+"-transform:translate3d(0,"+k+"px,0);":"top:"+k+"px;"));if(c){var d=0;clearInterval(N[n]);N[n]=setInterval(function(){d+=0.1;a.data("pos",Math.round((b-e)*Math.sin(d/c*(Math.PI/2))+e));d>=c&&(clearInterval(N[n]),a.data("pos",b))},100);clearTimeout(P[n]);P[n]=setTimeout(function(){"mixed"==
h.mode&&!a.hasClass("dwa")&&a.closest(".dwwl").find(".dwwb").fadeIn("fast")},1E3*c)}else a.data("pos",b)};j.setValue=function(b,c,n){var e=h.formatResult(j.temp);j.val=e;j.values=j.temp.slice(0);K&&b&&z(n);c&&J&&a.val(e).trigger("change")};j.validate=function(a,b,c,n){z(a,b,c,!0,n)};j.change=function(a){var c=h.formatResult(j.temp);"inline"==h.display?j.setValue(!1,a):b(".dwv",x).html(k(c));a&&h.onChange.call(B,c,j)};j.hide=function(){if(!1===h.onClose.call(B,j.val,j))return!1;b(".dwtd").prop("disabled",
!1).removeClass("dwtd");a.blur();x&&x.remove();K=!1;b(window).unbind(".dw")};j.show=function(){if(h.disabled||K)return!1;var c=h.height,e=h.rows*c;r();for(var d='<div class="'+h.theme+'">'+("inline"==h.display?'<div class="dw dwbg dwi"><div class="dwwr">':'<div class="dwo"></div><div class="dw dwbg"><div class="dwwr">'+(h.headerText?'<div class="dwv"></div>':"")),k=0;k<h.wheels.length;k++){var d=d+('<div class="dwc'+("scroller"!=h.mode?" dwpm":" dwsc")+(h.showLabel?"":" dwhl")+'"><div class="dwwc dwrc"><table cellpadding="0" cellspacing="0"><tr>'),
f;for(f in h.wheels[k]){var d=d+('<td><div class="dwwl dwrc">'+("scroller"!=h.mode?'<div class="dwwb dwwbp" style="height:'+c+"px;line-height:"+c+'px;"><span>+</span></div><div class="dwwb dwwbm" style="height:'+c+"px;line-height:"+c+'px;"><span>&ndash;</span></div>':"")+'<div class="dwl">'+f+'</div><div class="dww dwrc" style="height:'+e+"px;min-width:"+h.width+'px;"><ul>'),i;for(i in h.wheels[k][f])d+='<li class="dw-v" data-val="'+i+'" style="height:'+c+"px;line-height:"+c+'px;">'+h.wheels[k][f][i]+
"</li>";d+='</ul><div class="dwwo"></div></div><div class="dwwol"></div></div></td>'}d+="</tr></table></div></div>"}d+=("inline"!=h.display?'<div class="dwbc"><span class="dwbw dwb-s"><a href="#" class="dwb">'+h.setText+'</a></span><span class="dwbw dwb-c"><a href="#" class="dwb">'+h.cancelText+"</a></span></div>":'<div class="dwcc"></div>')+"</div></div></div>";x=b(d);z();"inline"!=h.display?x.appendTo("body"):a.is("div")?a.html(x):x.insertAfter(a);K=!0;M.init(x,j);"inline"!=h.display&&(b(".dwb-s a",
x).click(function(){j.setValue(!1,!0);j.hide();h.onSelect.call(B,j.val,j);return!1}),b(".dwb-c a",x).click(function(){j.hide();h.onCancel.call(B,j.val,j);return!1}),b("input,select").each(function(){b(this).prop("disabled")||b(this).addClass("dwtd")}),b("input,select").prop("disabled",!0),p(),b(window).bind("resize.dw",p));x.delegate(".dwwl","DOMMouseScroll mousewheel",function(a){if(!h.readonly){a.preventDefault();var a=a.originalEvent,a=a.wheelDelta?a.wheelDelta/120:a.detail?-a.detail/3:0,c=b("ul",
this),d=+c.data("pos"),d=Math.round(d-a);n(c);m(c,d,a<0?1:2)}}).delegate(".dwb, .dwwb",G,function(){b(this).addClass("dwb-a")}).delegate(".dwwb",G,function(a){if(!h.readonly){a.preventDefault();a.stopPropagation();var c=b(this).closest(".dwwl").find("ul");func=b(this).hasClass("dwwbp")?y:F;n(c);clearInterval(g);g=setInterval(function(){func(c)},h.delay);func(c)}}).delegate(".dwwl",G,function(a){if(!t&&h.mode!="clickpick"&&!h.readonly){a.preventDefault();t=true;u=b("ul",this).addClass("dwa");h.mode==
"mixed"&&b(".dwwb",this).fadeOut("fast");w=+u.data("pos");n(u);v=q(a);A=new Date;s=v;j.scroll(u,w)}});h.onShow.call(B,x,j)};j.init=function(d){M=b.extend({defaults:{},init:o},b.scroller.themes[d.theme?d.theme:h.theme]);b.extend(h,M.defaults,c,d);j.settings=h;O=Math.floor(h.rows/2);var n=b.scroller.presets[h.preset];a.unbind(".dw");n&&(n=n.call(B,j),b.extend(h,n,c,d),b.extend(C,n.methods));void 0!==a.data("dwro")&&(B.readOnly=i(a.data("dwro")));K&&j.hide();"inline"==h.display?j.show():(r(),J&&h.showOnFocus&&
(a.data("dwro",B.readOnly),B.readOnly=!0,a.bind("focus.dw",j.show)))};j.values=null;j.val=null;j.temp=null;j.init(c)}function y(a){for(var b in a)if(void 0!==z[a[b]])return!0;return!1}function q(a){return F?a.originalEvent?a.originalEvent.changedTouches[0].pageY:a.changedTouches[0].pageY:a.pageY}function i(a){return!0===a||"true"==a}function m(a,c,f,n,k){var g=a.closest(".dwwr").find("ul").index(a),c=c>d?d:c,c=c<e?e:c,a=b("li",a).eq(c);H.temp[g]=a.data("val");H.validate(n?c==k?0.1:Math.abs(0.1*(c-
k)):0,k,g,f)}var l={},g,o=function(){},f,e,d,H,r=(new Date).getTime(),t=!1,u=null,v,s,A,w,z=document.createElement(z).style,E=y(["perspectiveProperty","WebkitPerspective","MozPerspective","OPerspective","msPerspective"])&&"webkitPerspective"in document.documentElement.style,I=function(){var a=["Webkit","Moz","O","ms"],b;for(b in a)if(y([a[b]+"Transform"]))return"-"+a[b].toLowerCase();return""}(),F="ontouchstart"in window,G=F?"touchstart":"mousedown",L=F?"touchend":"mouseup",D={width:70,height:40,
rows:3,delay:300,disabled:!1,readonly:!1,showOnFocus:!0,showLabel:!0,wheels:[],theme:"",headerText:"{value}",display:"modal",mode:"scroller",preset:"",setText:"Set",cancelText:"Cancel",onShow:o,onClose:o,onSelect:o,onCancel:o,onChange:o,formatResult:function(a){for(var b="",d=0;d<a.length;d++)b+=(0<d?" ":"")+a[d];return b},parseValue:function(a,b){for(var d=b.settings.wheels,a=a.split(" "),n=[],e=0,f=0;f<d.length;f++)for(var g in d[f]){if(void 0!==d[f][g][a[e]])n.push(a[e]);else for(var i in d[f][g]){n.push(i);
break}e++}return n},validate:o},C={init:function(a){void 0===a&&(a={});return this.each(function(){this.id||(r+=1,this.id="scoller"+r);l[this.id]=new p(this,a)})},enable:function(){return this.each(function(){var a=l[this.id];a&&a.enable()})},disable:function(){return this.each(function(){var a=l[this.id];a&&a.disable()})},isDisabled:function(){var a=l[this[0].id];if(a)return a.settings.disabled},option:function(a,b){return this.each(function(){var d=l[this.id];if(d){var n={};"object"===typeof a?
n=a:n[a]=b;d.init(n)}})},setValue:function(a,b,d){return this.each(function(){var n=l[this.id];n&&(n.temp=a,n.setValue(!0,b,d))})},getInst:function(){return l[this[0].id]},getValue:function(){var a=l[this[0].id];if(a)return a.values},show:function(){var a=l[this[0].id];if(a)return a.show()},hide:function(){return this.each(function(){var a=l[this.id];a&&a.hide()})},destroy:function(){return this.each(function(){var a=l[this.id];a&&(a.hide(),b(this).unbind(".dw"),delete l[this.id],b(this).is("input")&&
(this.readOnly=i(b(this).data("dwro"))))})}};b(document).bind(F?"touchmove":"mousemove",function(a){t&&(a.preventDefault(),s=q(a),a=w+(v-s)/f,a=a>d+1?d+1:a,a=a<e-1?e-1:a,H.scroll(u,a))});b(document).bind(L,function(a){if(t){a.preventDefault();u.removeClass("dwa");var c=new Date-A,a=w+(v-s)/f,a=a>d+1?d+1:a,a=a<e-1?e-1:a;300>c?(c=(s-v)/c,c=c*c/0.0012,0>s-v&&(c=-c)):c=s-v;m(u,Math.round(w-c/f),0,!0,Math.round(a));t=!1;u=null}clearInterval(g);b(".dwb-a").removeClass("dwb-a")});b.fn.scroller=function(a){if(C[a])return C[a].apply(this,
Array.prototype.slice.call(arguments,1));if("object"===typeof a||!a)return C.init.apply(this,arguments);b.error("Unknown method")};b.scroller={setDefaults:function(a){b.extend(D,a)},presets:{},themes:{}}})(jQuery);(function(b){b.scroller.themes.ios={defaults:{dateOrder:"MMdyy",rows:5,height:30,width:55,headerText:!1,showLabel:!1}}})(jQuery);(function(b){var p={defaults:{dateOrder:"Mddyy",mode:"mixed",rows:5,width:70,showLabel:!1}};b.scroller.themes["android-ics"]=p;b.scroller.themes["android-ics light"]=p})(jQuery);(function(b){b.scroller.themes.android={defaults:{dateOrder:"Mddyy",mode:"clickpick",height:50}}})(jQuery);(function(b){var p={inputClass:""};b.scroller.presets.select=function(y){var q=b.extend({},p,y.settings),i=b(this),m=this.id+"_dummy";b('label[for="'+this.id+'"]').attr("for",m);var l=b('label[for="'+m+'"]'),l=l.length?l.text():i.attr("name"),g=[],o=[{}];o[0][l]={};var f=o[0][l];b("option",i).each(function(){var d=b(this).attr("value");f["_"+d]=b(this).text();b(this).prop("disabled")&&g.push(d)});b("#"+m).remove();var e=b('<input type="text" id="'+m+'" value="'+f["_"+i.val()]+'" class="'+q.inputClass+
'" readonly />').insertBefore(i);q.showOnFocus&&e.focus(function(){y.show()});i.hide().closest(".ui-field-contain").trigger("create");return{width:200,wheels:o,headerText:!1,formatResult:function(b){return f[b[0]]},parseValue:function(){return["_"+i.val()]},validate:function(d){b.each(g,function(e,f){b('li[data-val="_'+f+'"]',d).removeClass("dw-v")})},onSelect:function(b,f){e.val(b);i.val(f.values[0].replace(/_/,"")).trigger("change")},onChange:function(b,f){"inline"==q.display&&(e.val(b),i.val(f.temp[0].replace(/_/,
"")).trigger("change"))},onClose:function(){e.blur()}}}})(jQuery);(function(b){var p=new Date,y={dateFormat:"mm/dd/yy",dateOrder:"mmddy",timeWheels:"hhiiA",timeFormat:"hh:ii A",startYear:p.getFullYear()-100,endYear:p.getFullYear()+1,monthNames:"January,February,March,April,May,June,July,August,September,October,November,December".split(","),monthNamesShort:"Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec".split(","),dayNames:"Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday".split(","),dayNamesShort:"Sun,Mon,Tue,Wed,Thu,Fri,Sat".split(","),shortYearCutoff:"+10",
monthText:"Month",dayText:"Day",yearText:"Year",hourText:"Hours",minuteText:"Minutes",secText:"Seconds",ampmText:"&nbsp;",stepHour:1,stepMinute:1,stepSecond:1,separator:" "},p=function(q){function i(a,b,c){return void 0!==r[b]?+a[r[b]]:void 0!==c?c:I[t[b]]?I[t[b]]():t[b](I)}function m(a,b){return Math.floor(a/b)*b}function l(a){var b=i(a,"h",0);return new Date(i(a,"y"),i(a,"m"),i(a,"d"),i(a,"ap")?b+12:b,i(a,"i",0),i(a,"s",0))}var g=b(this),o;if(g.is("input")){switch(g.attr("type")){case "date":o=
"yy-mm-dd";break;case "datetime":o="yy-mm-ddTHH:ii:ssZ";break;case "datetime-local":o="yy-mm-ddTHH:ii:ss";break;case "month":o="yy-mm";y.dateOrder="mmyy";break;case "time":o="HH:ii:ss"}var f=g.attr("min"),g=g.attr("max");f&&(y.minDate=b.scroller.parseDate(o,f));g&&(y.maxDate=b.scroller.parseDate(o,g))}var e=b.extend({},y,q.settings),d=0,f=[],p=[],r={},t={y:"getFullYear",m:"getMonth",d:"getDate",h:function(a){a=a.getHours();a=z&&12<=a?a-12:a;return m(a,F)},i:function(a){return m(a.getMinutes(),G)},
s:function(a){return m(a.getSeconds(),L)},ap:function(a){return w&&11<a.getHours()?1:0}},u=e.preset,v=e.dateOrder,s=e.timeWheels,A=v.match(/D/),w=s.match(/a/i),z=s.match(/h/),E="datetime"==u?e.dateFormat+e.separator+e.timeFormat:"time"==u?e.timeFormat:e.dateFormat,I=new Date,F=e.stepHour,G=e.stepMinute,L=e.stepSecond,D=e.minDate,C=e.maxDate;o=o?o:E;if(u.match(/date/i)){b.each(["y","m","d"],function(a,b){a=v.search(RegExp(b,"i"));-1<a&&p.push({o:a,v:b})});p.sort(function(a,b){return a.o>b.o?1:-1});
b.each(p,function(a,b){r[b.v]=a});for(var g={},a=0;3>a;a++)if(a==r.y){d++;g[e.yearText]={};for(var c=D?D.getFullYear():e.startYear,Q=C?C.getFullYear():e.endYear;c<=Q;c++)g[e.yearText][c]=v.match(/yy/i)?c:(c+"").substr(2,2)}else if(a==r.m){d++;g[e.monthText]={};for(c=0;12>c;c++)g[e.monthText][c]=v.match(/MM/)?e.monthNames[c]:v.match(/M/)?e.monthNamesShort[c]:v.match(/mm/)&&9>c?"0"+(c+1):c+1}else if(a==r.d){d++;g[e.dayText]={};for(c=1;32>c;c++)g[e.dayText][c]=v.match(/dd/i)&&10>c?"0"+c:c}f.push(g)}if(u.match(/time/i)){g=
{};if(s.match(/h/i)){r.h=d++;g[e.hourText]={};for(c=0;c<(z?12:24);c+=F)g[e.hourText][c]=z&&0==c?12:s.match(/hh/i)&&10>c?"0"+c:c}if(s.match(/i/)){r.i=d++;g[e.minuteText]={};for(c=0;60>c;c+=G)g[e.minuteText][c]=s.match(/ii/)&&10>c?"0"+c:c}if(s.match(/s/)){r.s=d++;g[e.secText]={};for(c=0;60>c;c+=L)g[e.secText][c]=s.match(/ss/)&&10>c?"0"+c:c}w&&(r.ap=d++,d=s.match(/A/),g[e.ampmText]={"0":d?"AM":"am",1:d?"PM":"pm"});f.push(g)}q.setDate=function(a,b,c){for(var d in r)this.temp[r[d]]=a[t[d]]?a[t[d]]():t[d](a);
this.setValue(!0,b,c)};q.getDate=function(a){return l(a)};return{wheels:f,headerText:function(){return b.scroller.formatDate(E,l(q.temp),e)},formatResult:function(a){return b.scroller.formatDate(o,l(a),e)},parseValue:function(a){var c=new Date,d=[];try{c=b.scroller.parseDate(o,a,e)}catch(f){}for(var g in r)d[r[g]]=c[t[g]]?c[t[g]]():t[g](c);return d},validate:function(a,c){var d=q.temp,f={m:0,d:1,h:0,i:0,s:0,ap:0},g={m:11,d:31,h:m(z?11:23,F),i:m(59,G),s:m(59,L),ap:1},l=!0,o=!0;b.each(D||C?"y,m,d,ap,h,i,s".split(","):
c==r.y||c==r.m||void 0===c?["d"]:[],function(c,k){if(void 0!==r[k]){var m=f[k],h=g[k],z=31,q=i(d,k),u=b("ul",a).eq(r[k]),s,p;"d"==k&&(s=i(d,"y"),p=i(d,"m"),h=z=32-(new Date(s,p,32)).getDate(),A&&b("li",u).each(function(){var a=b(this),c=a.data("val"),d=(new Date(s,p,c)).getDay();a.html(v.replace(/[my]/gi,"").replace(/dd/,10>c?"0"+c:c).replace(/d/,c).replace(/DD/,e.dayNames[d]).replace(/D/,e.dayNamesShort[d]))}));l&&D&&(m=D[t[k]]?D[t[k]]():t[k](D));o&&C&&(h=C[t[k]]?C[t[k]]():t[k](C));if("y"!=k){var w=
b('li[data-val="'+m+'"]',u).index(),y=b('li[data-val="'+h+'"]',u).index();b("li",u).removeClass("dw-v").slice(w,y+1).addClass("dw-v");"d"==k&&b("li",u).removeClass("dw-h").slice(z).addClass("dw-h");q<m&&(q=m);q>h&&(q=h)}l&&(l=q==m);o&&(o=q==h);if(e.invalid&&"d"==k){var E=[];e.invalid.dates&&b.each(e.invalid.dates,function(a,b){b.getFullYear()==s&&b.getMonth()==p&&E.push(b.getDate()-1)});if(e.invalid.daysOfWeek){var H=(new Date(s,p,1)).getDay();b.each(e.invalid.daysOfWeek,function(a,b){for(var c=b-
H;c<z;c=c+7)c>=0&&E.push(c)})}e.invalid.daysOfMonth&&b.each(e.invalid.daysOfMonth,function(a,b){b=(b+"").split("/");b[1]?b[0]-1==p&&E.push(b[1]-1):E.push(b[0]-1)});b.each(E,function(a,c){b("li",u).eq(c).removeClass("dw-v")})}}})},methods:{getDate:function(a){var c=b(this).scroller("getInst");if(c)return c.getDate(a?c.temp:c.values)},setDate:function(a,c,d){void 0==c&&(c=!1);return this.each(function(){var e=b(this).scroller("getInst");e&&e.setDate(a,c,d)})}}}};b.scroller.presets.date=p;b.scroller.presets.datetime=
p;b.scroller.presets.time=p;b.scroller.formatDate=function(q,i,m){if(!i)return null;for(var m=b.extend({},y,m),l=function(b){for(var e=0;d+1<q.length&&q.charAt(d+1)==b;)e++,d++;return e},g=function(b,d,e){d=""+d;if(l(b))for(;d.length<e;)d="0"+d;return d},o=function(b,d,e,f){return l(b)?f[d]:e[d]},f="",e=!1,d=0;d<q.length;d++)if(e)"'"==q.charAt(d)&&!l("'")?e=!1:f+=q.charAt(d);else switch(q.charAt(d)){case "d":f+=g("d",i.getDate(),2);break;case "D":f+=o("D",i.getDay(),m.dayNamesShort,m.dayNames);break;
case "o":f+=g("o",(i.getTime()-(new Date(i.getFullYear(),0,0)).getTime())/864E5,3);break;case "m":f+=g("m",i.getMonth()+1,2);break;case "M":f+=o("M",i.getMonth(),m.monthNamesShort,m.monthNames);break;case "y":f+=l("y")?i.getFullYear():(10>i.getYear()%100?"0":"")+i.getYear()%100;break;case "h":var p=i.getHours(),f=f+g("h",12<p?p-12:0==p?12:p,2);break;case "H":f+=g("H",i.getHours(),2);break;case "i":f+=g("i",i.getMinutes(),2);break;case "s":f+=g("s",i.getSeconds(),2);break;case "a":f+=11<i.getHours()?
"pm":"am";break;case "A":f+=11<i.getHours()?"PM":"AM";break;case "'":l("'")?f+="'":e=!0;break;default:f+=q.charAt(d)}return f};b.scroller.parseDate=function(q,i,m){var l=new Date;if(!q||!i)return l;for(var i="object"==typeof i?i.toString():i+"",g=b.extend({},y,m),m=l.getFullYear(),o=l.getMonth()+1,f=l.getDate(),e=-1,d=l.getHours(),l=l.getMinutes(),p=0,r=-1,t=!1,u=function(b){(b=w+1<q.length&&q.charAt(w+1)==b)&&w++;return b},v=function(b){u(b);b=i.substr(A).match(RegExp("^\\d{1,"+("@"==b?14:"!"==b?
20:"y"==b?4:"o"==b?3:2)+"}"));if(!b)return 0;A+=b[0].length;return parseInt(b[0],10)},s=function(b,d,e){b=u(b)?e:d;for(d=0;d<b.length;d++)if(i.substr(A,b[d].length).toLowerCase()==b[d].toLowerCase())return A+=b[d].length,d+1;return 0},A=0,w=0;w<q.length;w++)if(t)"'"==q.charAt(w)&&!u("'")?t=!1:A++;else switch(q.charAt(w)){case "d":f=v("d");break;case "D":s("D",g.dayNamesShort,g.dayNames);break;case "o":e=v("o");break;case "m":o=v("m");break;case "M":o=s("M",g.monthNamesShort,g.monthNames);break;case "y":m=
v("y");break;case "H":d=v("H");break;case "h":d=v("h");break;case "i":l=v("i");break;case "s":p=v("s");break;case "a":r=s("a",["am","pm"],["am","pm"])-1;break;case "A":r=s("A",["am","pm"],["am","pm"])-1;break;case "'":u("'")?A++:t=!0;break;default:A++}100>m&&(m+=(new Date).getFullYear()-(new Date).getFullYear()%100+(m<=g.shortYearCutoff?0:-100));if(-1<e){o=1;f=e;do{g=32-(new Date(m,o-1,32)).getDate();if(f<=g)break;o++;f-=g}while(1)}d=new Date(m,o-1,f,-1==r?d:r&&12>d?d+12:!r&&12==d?0:d,l,p);if(d.getFullYear()!=
m||d.getMonth()+1!=o||d.getDate()!=f)throw"Invalid date";return d}})(jQuery);

/* jQuery UI Touch Punch 0.2.2, Copyright 2011, Dave Furfero, http://touchpunch.furf.com/
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * Depends on jquery.ui.widget.js + jquery.ui.mouse.js
 */
(function(b){b.support.touch="ontouchend" in document;if(!b.support.touch){return;}var c=b.ui.mouse.prototype,e=c._mouseInit,a;function d(g,h){if(g.originalEvent.touches.length>1){return;}g.preventDefault();var i=g.originalEvent.changedTouches[0],f=document.createEvent("MouseEvents");f.initMouseEvent(h,true,true,window,1,i.screenX,i.screenY,i.clientX,i.clientY,false,false,false,false,0,null);g.target.dispatchEvent(f);}c._touchStart=function(g){var f=this;if(a||!f._mouseCapture(g.originalEvent.changedTouches[0])){return;}a=true;f._touchMoved=false;d(g,"mouseover");d(g,"mousemove");d(g,"mousedown");};c._touchMove=function(f){if(!a){return;}this._touchMoved=true;d(f,"mousemove");};c._touchEnd=function(f){if(!a){return;}d(f,"mouseup");d(f,"mouseout");if(!this._touchMoved){d(f,"click");}a=false;};c._mouseInit=function(){var f=this;f.element.bind("touchstart",b.proxy(f,"_touchStart")).bind("touchmove",b.proxy(f,"_touchMove")).bind("touchend",b.proxy(f,"_touchEnd"));e.call(f);};})(jQuery);

