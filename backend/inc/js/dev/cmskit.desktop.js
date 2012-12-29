
/**
* Desktop-Functions
*/

var ch, actHash = '', objectName = '', objectId = '', objectHType = false;


/**
* simple function to check Changes of the Hash (=Browser-History-)
* name: checkHash
* 
*/
function checkHash()
{
	var h = window.location.hash.substr(1),
		g = [];
	if(h.length>0)
	{
		var p = h.split('&');
		for(var i=0,j=p.length;i<j;++i)
		{
			var a = p[i].split('=');
			if(a[1]) g[a[0]]=a[1];
		}
	}
	if(!g['object'])
	{
		$('#colLeftb').html('')
	};
	if(g['object'] && g['object']!=objectName)
	{
		init(g['object'], g['id'])
	};
	if(!g['id'])
	{
		$('#colMidb').empty();
		$('#colRightb').empty();
	};
	if(g['id'] && g['id']!=objectId)
	{
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
			function(data){
				getContent(objectId)
			}
			)
		}, 1000);
	};

	window.setTimeout(checkHash, 3000);
};


/**
* 
* name: unbekannt
* @param name
*/
function selectObject(name)
{
	window.location.hash = actHash = 'object='+name;
	init(name);
};

/**
* 
* name: init
* @param name
* @param id
*/
function init(name, id)
{
	objectName = name;
	$('#colMidb').html('');
	$('#colRightb').html('');
	//$('#objectSelect').selectmenu("value", name);
	$('#objectSelect').val(name);
	if(!store[objectName]) {
		store[objectName]={
			offset:0,
			srt:[],
			lbls:[]}
	}
	getList(id);	
};


$(document).ready(function()
{
	// Rules for Masked Input
	$.mask.definitions['~'] = '[+-]';//plus-minus
	$.mask.definitions['h'] = '[A-Fa-f0-9]';//color-hash (= #hhhhhh )
	
	/*
	* 
	*/
	$('body').bind('ajaxSend', function(elm, xhr, s)
	{
		if (s.type == "POST"||s.type == "GET") {
		xhr.setRequestHeader('X-CSRF-Token', '3')}
	});
	
	//$(this).bind("contextmenu", function(e) {e.preventDefault();});
	
	/* style Buttons
	* 
	*/
	$('button').each(function() { $(this).button( {icons:{ primary: 'ui-icon-'+$(this).attr('rel')}}); });
	
	
	/* style SELECTs
	* ATTENTION! fix z-index-Isuue in selectmenu.js like this
	* .zIndex( this.element.zIndex() + 1001 )
	
	
	$('select').selectmenu({
		style:'dropdown'
		//change: function(e, object){//alert(object.value);		}
	}); */
	
	// css "einschalten" sonst flackereffekt bei mozilla
	//$('#mainTheme').attr('href','inc/css/'+(store['theme']?store['theme']:theme)+'/style.css');
	
	// Spaltenbreite
	function setColW()
	{
		var cw = [ parseInt($('#colLeft').css('width')), parseInt($('#colMid').css('width')), parseInt($('#colRight').css('width')) ];
		$('#colMid').css('left', (cw[0]+20)+'px');
		$('#colRight').css('left', (cw[0]+cw[1]+30)+'px');
		//$.storage({id : 'colWidth', value: cw})
		store['cw'] = cw;
	};
	
	
	// Resizable Spalten maxHeight:1300,maxWidth:850,minHeight:300,minWidth:200, 
	
	var dw = $(document).width(),
		w  = Math.floor((dw-50)/4),// 4-columns-grid
		cw = (store['cw']) ? store['cw'] : [w, w*2, w];
		
		// global variables
		ch = $(document).height()-70,// 
		limitNumber = Math.floor((ch-50)/32);// wie viele Listenelemente passen auf den Screen?
	
	$("#colLeft").resizable({stop:setColW});
	$("#colMid").resizable({stop:setColW});
	$("#colRight").resizable({stop:setColW});
	$("#colLeft").css({'width':cw[0],'height':ch});
	$("#colMid").css({'width':cw[1],'left':cw[0]+20,'height':ch});
	$("#colRight").css({'width':cw[2],'left':cw[0]+cw[1]+30,'height':ch});
	
	// content-wrapper auf höhe zwingen (wg. overflow)
	$("#colLeftb").height(ch-50);
	$("#colMidb").height(ch);
	$("#colRightb").height(ch-10);
	
	// hide logo on small screens
	if(dw<850) $("#logo").hide();
	
	
	// get/set Font-Size
	originalFontSize = $('html').css('font-size');
	if(store['fnts']) {
		$('html').css('font-size', store['fnts']);
	}
	
	
	window.setTimeout(checkHash, 500);
	
});// document.ready END /////////////////////////////////////////////////////



/**
* 
* name: offSet
* @param name
* @param no
* 
*/
function offSet(name, no) {
	store[name]['offset'] += no;
};



/**
* 
* name: getPlainList
* @param id
* @return
* 
*/
function getPlainList(id) 
{
	
	$.get('crud.php', 
		{
			action: 'getList', 
			projectName: projectName, 
			objectName: objectName, 
			objectId: id, 
			limit: limitNumber, 
			offset: (store[objectName]['offset'] ? parseInt(store[objectName]['offset']) : 0)
		}, 
		function(data)
		{
			// re-call+abort drawing of the List if the Entry is out of Range
			if(id && !isNaN(data))
			{
				store[objectName]['offset'] = parseInt(data);
				getPlainList();
				return;
			}
			
			$('#colLeftb').html(data);
			styleButtons('mainlistHead');
			
			$("#mainlist").selectable(
			{
				stop: function()
				{
					var a = [];
					$('.ui-selected', this).each(function(){
						a.push(this.getAttribute('rel'))
					});
					
					if(a.length>1)
					{
						specialAction('crud.php?action=multiSelect&projectName='+projectName+'&objectName='+objectName+'&objectId='+a.join(','), 'colMidb');
						
					}
					else
					{
						if(typeof a[0]!='undefined') {
							getContent(a[0])
						};
					}
				}
			});
		}
	);
};

/**
* 
* name: specialAction
* @param url
* @param target
* @param post
* 
*/
function specialAction(url, target, post)
{
	$.post(url, {val: post}, function(data) {
		if(target) {
			$('#'+target).html(data);
			$('#'+target+' #accordion').accordion({collapsible:true});
			$('#'+target+' #tabs').tabs();
			prettify(target);
		}else {
			message(data);
		}
	});
};

/**
* 
* name: getTreeList
* @param id
* @param treeType
*/

function getTreeList(id, treeType)
{
	// define JS-Store-Object if not available
	if(!store[objectName]) store[objectName] = {};
	if(!store[objectName]['stat']) store[objectName]['stat'] = [];
	
	// define the GET-Parameter-String for this Object / 
	var params = '&projectName='+projectName+'&objectName='+objectName+'&objectId='+id+'&tType='+treeType+'&limit='+limitNumber+'&offset=';
	
	// get the actual Path from the Root-Level down to the actual Element (id)
	if (id)
	{
		$.get('crud.php?action=getTreePath'+params,
		function(data)
		{
			store[objectName]['stat'] = data.split(',');
			//$.merge( store[objectName]['stat'], data.split(',') );
			//store[objectName]['stat'] = $.unique(store[objectName]['stat']);
		})
	}
	else
	{
		store[objectName]['stat'] = [];
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
			
			// open all active Nodes defined in the Path above
			statCheck: function(target)
			{
				target.find('li>span').each(function(i)
				{
					// bind the "getContent"-Event
					$(this).on('click', function(e)
					{
						if($(e.target).data('id')) getContent($(e.target).data('id'));
					});
					var myd = $(this).data('id').toString();
					// if the Entry is in the Tree-Path (= Parent-Element), (try to) open the Branch
					if( $.inArray(myd, store[objectName]['stat']) > -1 )
					{
						$(this).parent().find(".ui-icon-circle-plus").trigger('click');
					}
					// highlight the actual Entry
					if( myd == id )
					{
						$(this).addClass('sel');
					}
					
				})
			}
		})
	})
	
};

/**
* 
* name: showPagination
* 
*/
function showPagination()
{
	if($('#pagination').html() != '')
	{
		$('#pagination').toggle();
		return;
	}
	
	$.get('crud.php', 
	{
		action: 'getPagination', 
		projectName: projectName, 
		objectName: objectName, 
		limit: limitNumber, 
		offset: parseInt(store[objectName]['offset'])
		//sortby: srtarr.join(',') 
	},
	function(data) {
		$('#pagination').html(data);
	});
}

/**
* 
* name: setPagination
* @param n
* @return
* 
*/
function setPagination(n)
{
	store[objectName]['offset']=limitNumber*n;getList();
}

// Haupt-Liste laden (wird automatisch aufgerufen)
/**
* 
* name: getList
* @param id
* 
*/
function getList(id)
{
	
	// autocomplete-searchbox
	$('#searchbox').autocomplete({
		source: 'inc/php/search.php?projectName='+projectName+'&objectName='+objectName,
		minLength: 3,
		select: function(event,ui)
		{ 
			getContent(ui.item.id);
			return false;
		}
	});
	
	var ic = (objectId.length>0);
	var objectHType = $('#objectSelect option[value="'+objectName+'"]').data('htype');
	
	if(objectHType && objectHType!='List')
	{
		getTreeList(id, objectHType);
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
			},1000)
		}
	}
	
	// fill object-wizard-links 
	setTimeout(function()
	{
		$('#objectWizards').html( $('#objectWizardHtml').html() );
		//$('#globalWizard').selectmenu();
	}, 1000);
};



/**
* load Content into main Area
* @name getContent
* @param id 
* @return
* 
*/
function getContent(id)
{
	
	if(id=='undefined'){return false;};
	
	window.location.hash = actHash = '#object='+objectName+'&id='+id;// store the ID in URL
	objectId = id;
	$('#colMidb').empty();
	$('#colRightb').empty();
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
		//$('#colMidb').css({opacity:0.0}).html(data).animate({opacity:1.0},1000);
		
		// content-processing
		$('#accordion').accordion({collapsible:true});
		$('#tabs').tabs();
		prettify('colMidb');
		
		//$('#colMidb textarea,input').autoResize({maxWidth: ($("#colMid").width()-200)});
		$('#colMidb textarea').autosize();
		
		// show related Objects
		/*
		$('#referenceSelect').selectmenu(
		{
			style:'popup', 
			icons:[
					{find: '.relType',  icon: 'ui-icon-link'},	
					{find: '.relTypes', icon: 'ui-icon-arrowthick-2-e-w'}, 
					{find: '.relTypec', icon: 'ui-icon-arrowthick-1-se'}, 
					{find: '.relTypep', icon: 'ui-icon-arrowthick-1-nw'}
				  ]
		});*/
		
		// bind a Input-Mask to this Field
		$('#colMidb input[data-mask]').each(function(){
			$(this).mask($(this).data('mask'))
		});
		// bind a Input-Mask to this Field
		$('#colMidb *[data-readonly]').each(function(){
			$(this).attr('readonly','readonly')
		});
		// change type="..." (e.g. for HTML5 checks)
		$('#colMidb input[data-type]').each(function(){
			var newEl = $(this).clone();
			newEl.attr("type", $(this).data('type'));
			newEl.insertBefore($(this));
			$(this).remove();
		});
		
	}
	);
	
	getConnectedReferences(id);
};

/**
* loads all References asocciated to the Entry
* 
* name: getConnectedReferences
* @param id
* @param off
* 
*/
function getConnectedReferences(id, off)
{
	if(!off) off=0;
	// get the References
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
			//alert(data);
			$('#colRightb').html(data);
			styleButtons('colRightb');
			$('#colRightb .lnk').click(function(e)
			{
				objectName = $(this).data('object');
				getList();
				//$('#objectSelect').selectmenu("value", objectName);
				$('#objectSelect').val(objectName);
				getContent($(this).data('id'));
				e.preventDefault();
			});
		});
};


/**
* saves the Content
* 
* name: saveContent
* @param id
* 
 */
function saveContent(id)
{
	
	// embed-wizard-transfer
	$('#colMidb .eframe').each(function() {
		var f=$(this), fd=f[0].contentWindow||f[0]; // iframe
		fd.transfer();// run function
	});
	
	$.post('crud.php?action=saveContent&projectName='+projectName+'&objectName='+objectName+'&objectId='+id, $('#colMidb').serialize(), 
	function(data) {
		message(data);
		getList(id);
	});
};

/**
* creates a new Entry and opens it in the main Slot
* 
* name: createContent
* 
*/
function createContent()
{
	$.get('crud.php', { action: 'createContent', projectName: projectName, objectName: objectName}, function(data) {
		$('#colRightb').html('');
		if(data.substr(0,1)=='_') {
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

/**
* deletes an Entry
* 
* name: deleteContent
* @param id 
*/
function deleteContent(id)
{
	var q = confirm(_('delete_entry')+' (ID:'+id+')?');
	if(q)
	{
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


/**
* loads References 
* 
* name: getReferences
* @param id 
* @param offs1 
* @param offs2 
*/
function getReferences (id, offs1, offs2)
{
	
	var referenceName = $('#referenceSelect>option:selected').val();
	
	if(referenceName)
	{
		
		$.get('crud.php', 
		{
			action: 'getReferences', 
			projectName: projectName, 
			objectName: objectName, 
			objectId: id, 
			referenceName: referenceName, 
			limit: limitNumber, 
			offset: offs1,
			offset2: offs2
		}, 
		function(data) 
		{
			
			$('#colRightb').html(data);
			
			styleButtons('colRightb');
			
			// make Lists sortable
			$( '#sublist, #sublist2' ).sortable({
				items: 'li:not(.ui-state-disabled)',
				connectWith: '.rlist',
				placeholder: 'ui-state-highlight',
				handle: 'span'
			});
			
			// only accept 1 Element if Parent-List (with Heading == 2)
			$('.sublistParent').bind('sortreceive', function(event, ui) {
				if ($(this).children().length > 2)
				{
					$(ui.sender).sortable('cancel');
					message(_('only_one_connection_allowed'), true, 5000);
					return;
				};
			});
			
			function saveReference()
			{
				
				$.post('crud.php?action=saveReferences&projectName='+projectName+'&objectName='+objectName+'&objectId='+id+'&referenceName='+referenceName, 
				{
					order: $('#sublist').sortable('serialize', 'id')
				}, 
				function(data)
				{
					message(data);
				});
			};
			
			// save List-Sort after Update of DragDrop-Event
			$("#sublist").bind("sortupdate", function(event, ui)
			{
				saveReference();
			});
			
			// init Searchbox
			$('#referenceSearchbox').autocomplete(
			{
				source: 'inc/php/search.php?projectName='+projectName+'&objectName='+referenceName,
				select: function(event, ui)
				{ 
					// 
					var lnk = 'location=\'backend.php?project='+projectName+'&object='+referenceName+'#'+ui.item.id+'\'';
					var htm = '<h4>'+_('what_to_do')+'</h4>';
						htm += '<button type="button" onclick="'+lnk+'">'+_('show_entry')+'</button> ';
					
					// if the Entry is connectable (not conneted AND no Parent-Relation available)
					if($('#sublist').find('#l_'+ui.item.id).length==0 && $('.sublistParent').children().length<2)
					{
						htm += '<button type="button" id="insertListItem">'+_('connect_entry')+'</button>';
					};
					
					$( "#dialogb1" ).html(htm);
					
					$( "#dialogb1 button" ).button();
					$( "#dialog1" ).dialog();
					
					// add Entry + save Relations
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
			
			$('#colRightb .lnk').click(function(e)
			{
				objectName = $(this).data('object');
				getList();
				//$('#objectSelect').selectmenu("value", objectName);
				$('#objectSelect').val(objectName);
				getContent($(this).data('id'));
				e.preventDefault();
			});
			
		});
		
	} else{
		//$('#colRightb').html('');
		getConnectedReferences(id);
	}
};


/**
* transform some Form-Elements into UI-Elements (within Element with container-id)
* 
* @name prettify
* @param id ID of HTML-Container
*/
function prettify(id)
{
	styleButtons(id);
	//$('#'+id+' .selectbox').selectmenu({style:'popup'});
	$('#'+id+' .date').datepicker(
	{
		dateFormat:'yy-mm-dd'
	});
	$('#'+id+' .datetime').datetimepicker(
	{
		dateFormat:'yy-mm-dd',
		timeFormat:'h:m:s',
		showSecond:true
	});
	
	$('#'+id+' .timestamp').datetimepicker(
	{
		dateFormat:'mm dd yy',
		timeFormat:'h:m:s',
		showSecond:true, 
		beforeShow: function(input, inst) 
		{
			var ts = parseInt($(input).val()),
				d = new Date((ts>0) ? ts*1000 : new Date().getTime()),
				str = (d.getMonth()+1)+' '+d.getDate()+' '+d.getFullYear()+' '+d.getHours()+':'+d.getMinutes()+':'+d.getSeconds();
			$(input).val(str);
		},
		onClose: function(dateText, inst) 
		{
			var dat = Math.floor(Date.parse(dateText)/1000);
			if(!isNaN(dat)) $(this).val(dat);
		}
	});
	$('#'+id+' .slider').each(function() {
		var rl = $(this).attr('rel').split('-');
		$(this).slider({
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

/**
* transform all Button-Elements within an Container-Element
* 
* name: styleButtons
* @param id
* @return
* 
*/
function styleButtons(id)
{ 
	$('#'+id+' button').each(function() {
		
		$(this).button( {
			icons:{primary:'ui-icon-'+$(this).attr('rel')}, 
			text:(($(this).text()=='.')?false:true),
			title:''
		})
	}
)};



/**
* saves Settings and redirects to index.php
* name: logout
*/
function logout()
{
	store['lastPage'] = window.location.hash.substr(1);
	//top.window.name=null;
	// there is a real user
	if(userId != 0)
	{
		$.post('extensions/user/wizards/settings/save.php?projectName='+projectName, 
		{ 
			id: userId, 
			json: JSON.stringify(store) 
		}, function(d) {
			window.location='index.php?project='+projectName;
		});
	}else{
		window.location='index.php?project='+projectName;
	}
	
};


/**
* opens a URL in the main Dialog
* name: getFrame
* @param url
* @param el
* 
*/
function getFrame(url, el)
{
	
	var wh = (store['dwh']) ? store['dwh'] : [($(document).width()*90/100), ($(document).height()*90/100)];
	$("#dialogb2").css({'width':wh[0]-20, 'height':wh[1]-70});
	$("#dialogb2").attr('src', url);
	$("#dialog2").dialog({ 
		width:  wh[0],
		height: wh[1], 
		modal: true,
		show: "scale",
		hide: "scale",
		close: function(event,ui){ 
			$("#dialogb2").attr('src','about:blank');
		},
		resizeStop: function(event, ui) {
			store['dwh'] = [$(this).dialog('option','width'), $(this).dialog('option','height')];
		}
	})
};

/**
* opens one of the global Wizards in the main Dialog
* name: openGlobalWizard
* @see getFrame
* @param el
* @return
* 
*/
function openGlobalWizard(el)
{
	var url = el.value;
	if(url!=''){
		getFrame(template(url, window),el);
		$('#globalWizard').val('');
	}
};


/**
* 
* name: getWizard
* @see getFrame
* @param id
* @param type
* @param add
* 
*/
function getWizard(id, type, add)
{
	if(type.substr(0,1)=='#') type=$(type).val();
	targetFieldId = id;
	getFrame( 'wizards/' + type + '/index.php?projectName='+projectName+'&objectName='+objectName+'&objectId='+objectId+((add)?'&'+add:'') );
};


/**
* add Fullscreen-Toggle for Dialogs
* 
* @url http://mabp.kiev.ua/2010/12/15/jquery-ui-fullscreen-button-for-dialog
* 
*/
(function() {
	var old = $.ui.dialog.prototype._create;
	$.ui.dialog.prototype._create = function(d)
	{
		old.call(this, d);
		var self = this,
			options = self.options,
			oldHeight = options.height,
			oldWidth = options.width,
			uiDialogTitlebarFull = $('<a class="ui-dialog-titlebar-full ui-corner-all" href="#"><span class="ui-icon ui-icon-newwin"></span></a>')
				.attr('role', 'button')
				.hover(
					function() {
						uiDialogTitlebarFull.addClass('ui-state-hover');
					},
					function() {
						uiDialogTitlebarFull.removeClass('ui-state-hover');
					}
				)
				.toggle(
					function() {
						self._setOptions({
							height : window.innerHeight - 10,
							width : window.innerWidth - 30
						});
						
						$("#dialogb2").css({'width':window.innerWidth-50,'height':window.innerHeight-80});
						self._position('center');
						return false;
					},
					function() {
						self._setOptions({
							height : oldHeight,
							width : oldWidth
						});
						
						$("#dialogb2").css({'width':oldWidth-20,'height':oldHeight-70});
						
						self._position('center');
						return false;
					}
				)
				.focus(function() {
					uiDialogTitlebarFull.addClass('ui-state-focus');
				})
				.blur(function() {
					uiDialogTitlebarFull.removeClass('ui-state-focus');
				})
				.appendTo(self.uiDialogTitlebar)
	};
})();

// capture save-shortcut ctrl+s
$(window).keypress(function(event) 
{
	if (!(event.which == 115 && event.ctrlKey) && !(event.which == 19)) return true;
	var id = $('#saveButton').attr('alt');
	if(id) saveContent(id);
	event.preventDefault();
	return false;
});
