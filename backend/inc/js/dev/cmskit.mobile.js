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
			loadMessage: _('load_Data') + '...',
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
			message(_('entry_created')+' (ID:'+data+')');
		}
	});
};

// lösche Eintrag
function deleteContent(id) 
{
	var q = confirm(_('delete_entry')+' (ID:'+id+')?');
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
					message(_('only_one_connection_allowed'), true, 5000);
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
					var htm = '<h4>'+_('what_to_do')+'</h4><button type="button" onclick="'+lnk+'">'+_('show_entry')+'</button> ';
					
					// Eintrag noch nicht in der oberen Liste == verknüpft und kein Parent-Element vorhanden?
					if($('#sublist').find('#l_'+ui.item.id).length==0 && $('.sublistParent').children().length<2) 
					{
						htm += '<button type="button" id="insertListItem">'+_('connect_entry')+'</button>';
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
					dayText: _('Day'),
					monthText: _('Month'),
					yearText: _('Year'),
					hourText: _('Hour'),
					minuteText: _('Minute'),
					secText: _('Second')
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



