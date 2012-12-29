
// clone DIV-Content to generate multiple DB-Definitions in Step 3
var counter = 0;
function cloneFields()
{
	var tpl = $('#DBdefinition').html();
	$('#writeout').append( tpl.replace(/\[\[x\]\]/g, counter).replace(/\[x\]/g, '['+counter+']') );
	counter++;
}

// show/hide some Fields in Step 3
function toggleFields(el, indx)
{
	var disp1 = ((el.value=='mysql')  ? true : false);// show if MySql
	var disp2 = ((el.value=='sqlite') ? true : false);// show if SQLite
	
	$('#divDbhost'+indx).toggle(disp1);
	$('#putDbhost'+indx).toggle(disp1);
	$('#divDbport'+indx).toggle(disp1);
	$('#putDbport'+indx).toggle(disp1);
	$('#divDbuser'+indx).toggle(disp1);
	$('#putDbname'+indx).toggle(disp2);
	$('#putDbpass'+indx).toggle(disp2);
}

// put a random string into some fields
// (string-length, input-id, mime-addition)
function randomString(len, id, add)
{
	var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');

	if (!len) {
		len = Math.floor(Math.random() * chars.length);
	}
	var str = '';
	for (var i = 0; i < len; i++) {
		str += chars[Math.floor(Math.random() * chars.length)];
	}
	$('#'+id).val(str + add);
}

// remove forbidden characters from project-name
function clearString(el) {
	el.value = el.value.replace(' ','_').replace(/[^\d\w]/g, '').toLowerCase();
}

$(document).ready(function()
{
	// hide label-elements if placeholders are avalable
	if('placeholder' in document.createElement('input')) {
		$('label').hide();
	}
});
