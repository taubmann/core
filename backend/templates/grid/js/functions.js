

$(document).ready(function ()
{
	// create a connection-dialog
	/*
	dialogbox = $('<div><div id="connect_list"></div></div>').appendTo($('body'));
	dialogbox.dialog({
		autoOpen: false,
		show: 'fade',
		hide: 'fade',
		modal: true,
		height: '600',
		width: '600',
        minWidth: '600',
		title: 'connect'
	});*/
	
	
	
	$('#objectSelect').on('change', function() {
		window.location.href = 'backend.php?project='+projectName+'&object=' + $(this).val()
	});
	
	$('#templateSelect').on('change', function() {
		window.location.href = 'backend.php?project='+projectName+'&object=' + objectName + '&template=' + $(this).val()
	});
	
	$('button').each(function() { $(this).button( {icons:{ primary: 'ui-icon-'+$(this).attr('rel')}}); })
	
	
	if (objectName)
	{
		
		var call = 'crud.php?projectName='+projectName+'&actTemplate=grid&objectName=#####&action=';
		var rows = Math.floor(($(window).height()-200)/ 35 );
		
		for (e in subObjects)
		{
			var subCall = call.replace('#####', objectName);
			var subCallAdd = '&jtSorting=id ASC&referenceName='+e+'&referenceType='+objectProps[e][1]+'&objectId=';
			// see: http://www.jtable.org/Demo/MasterChild
			var o = {};
				o['title'] = objectProps[e][0];
				o['width'] = '3%';
				o['sorting'] = false;
				o['edit'] = false;
				o['create'] = false;
				o['searchable'] = false;
				o['display'] = function (subData)
				{
					var $img = $('<img src="templates/grid/img/list.png" />');
					$img.click(function ()
					{
						//alert(subCall + 'getConnectedReferences'	+ subCallAdd + subData.record.id)
						$('#main').jtable('openChildTable', $img.closest('tr'),
						{
							title: objectProps[e][0],
							paging: true,
							pageSize: 15,
							sorting: true,
							
							
							//defaultSorting: 'id ASC',
							actions: {
								listAction:   subCall + 'getConnectedReferences'			+ subCallAdd + subData.record.id,
								createAction: subCall + 'createSubContent'					+ subCallAdd + subData.record.id,
								updateAction: call.replace('#####', e) + 'updateContent&referenceName='+objectName+'&referenceType='+objectProps[e][1]+'&referenceId=' + subData.record.id,
								deleteAction: call.replace('#####', e) + 'removeContent'
							},
							fields: subObjects[e]
						},
						function (data)
						{
							data.childTable.jtable('load');
						});
					});
					return $img;
				}
			
			mainObject[e] = o;
		}
		
		
		var mainCall = call.replace('#####', objectName);
		
		// Prepare main jTable
		$('#main')
		.width( $(window).width()-20 )
		.jtable(
		{
			title: objectProps[objectName][0],
			jqueryuiTheme: true,
			paging: true,
			pageSize: rows,
			sorting: true,
			actions: {
				listAction:   mainCall + 'getList',
				createAction: mainCall + 'createNewContent',
				updateAction: mainCall + 'updateContent',
				deleteAction: mainCall + 'removeContent'
			},
			fields: mainObject
		});
		
		//Load main List from Server
		$('#main').jtable('load');
		
		//$( ".selector" ).dialog( "option", "width", 500 );
	}
});


/**
* saves Settings and redirects to index.php
* name: logout
*/
function logout()
{
	store['lastPage'] = window.location.hash.substr(1);
	
	// there is a real user
	if(userId != 0)
	{
		$.post('extensions/user/settings/save.php?projectName='+projectName, 
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
