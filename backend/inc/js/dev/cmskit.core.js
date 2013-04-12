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
* 
* 
*********************************************************************************/


// show a Message instead of alert (string, error, timeout)
function message(str, err, out) {
	var id = 'x'+Math.random().toString().substring(2);
	if(str.substr(0,2)=='[['){err=true,str=str.substring(2,str.length-2)}
	var h = '<div id="'+id+'" class="ui-widget"><div class="ui-state-'+(err?'error':'highlight')+' ui-corner-all" style="padding:0 .7em"><p><span class="ui-icon ui-icon-'+(err?'alert':'info')+'" style="float:left;margin-right:.3em;"></span>'+str+'</p></div></div>';
	$('#messagebox').append(h);
	window.setTimeout(function(){$('#'+id).slideUp()}, (out?out:3000));
};

// Dummy-Translation for Development ( do NOT remove the space between "_" and "(". All _-Calls are replaced normally by the Compressor)
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

function array_diff(a, b) {
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


/**
* localizing for Date/Time-Picker
* @todo fix the boolean vars
*/
$(function($){
	$.datepicker.regional[''] = {
		closeText: _('close'),
		prevText: _('back'),
		nextText: _('next'),
		currentText: _('today'),
		monthNames: [_('January'),_('February'),_('March'),_('April'),_('May'),_('June'),_('July'),_('August'),_('September'),_('October'),_('November'),_('December')],
		monthNamesShort: [_('Jan'),_('Feb'),_('Mar'),_('Apr'),_('May'),_('Jun'),_('Jul'),_('Aug'),_('Sept'),_('Oct'),_('Nov'),_('Dec')],
		dayNames: [_('Sunday'),_('Monday'),_('Tuesday'),_('Wednesday'),_('Thursday'),_('Friday'),_('Saturday')],
		dayNamesShort: [_('Sun'),_('Mon'),_('Tue'),_('Wed'),_('Thu'),_('Fri'),_('Sat')],
		dayNamesMin: [_('Su'),_('Mo'),_('Tu'),_('We'),_('Th'),_('Fr'),_('Sa')],
		weekHeader: _('Wk'),
		dateFormat: _('dd.mm.yy'),
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''
	};
	
	
	$.timepicker.regional[''] = {
	  timeOnlyTitle: _('choose_time'),
	  timeText: _('Time'),
	  hourText: _('Hour'),
	  minuteText: _('Minute'),
	  secondText: _('Second'),
	  currentText: _('Now'),
	  closeText: _('close'),
	  ampm: false
	};
});
	
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

function secToTimeframe (sec, name, show)
{
	var years = Math.floor(sec / 31556926);
	sec %= 31556926;
	var months = Math.round(sec / 2629743);
	sec %= 2629743;
	var days = Math.floor(sec / 86400);
	sec %= 86400;
	var hours = Math.floor(sec / 3600);
	sec %= 3600;
	var minutes = Math.floor(sec / 60);
	sec %= 60;
	var  str = '';
	if(/year/.test(show)) str += '<span class="timeframe" data-mult="31556926" data-field="'+name+'"> ' + _('Years') + ': <em>' + years.toString() + '</em></span>';
	if(/month/.test(show)) str += '<span class="timeframe" data-mult="2629743" data-field="'+name+'">' + _('Months') + ': <em>' + months.toString()  + '</em></span>';
	if(/day/.test(show)) str += '<span class="timeframe" data-mult="86400" data-field="'+name+'">' + _('Days') + ': <em>' + days.toString()  + '</em></span>';
	if(/hour/.test(show)) str += '<span class="timeframe" data-mult="3600" data-field="'+name+'">' + _('Hours') + ': <em>' + hours.toString()  + '</em></span>';
	if(/minute/.test(show)) str += '<span class="timeframe" data-mult="60" data-field="'+name+'">' + _('Minutes') + ': <em>' + minutes.toString()  + '</em></span>';
	if(/second/.test(show)) str += '<span class="timeframe" data-mult="1" data-field="'+name+'">' + _('Seconds') + ': <em>' + sec.toString()  + '</em></span>';
	
	return str;
};

$(document).ready(function()
{
	// supress href-call on blind Links
	$('body').on('click', 'a[href="#"]',function(e){e.preventDefault()});
	
	$.ajaxSetup({ cache:false });
	// show a waiter if Ajax-Call is loading
	$(document).ajaxStart(function(){$('body').addClass('loading')});
	$(document).ajaxStop(function(){$('body').removeClass('loading')});
	
});

// open a Help-File
function openDoc(p)
{
	getFrame('admin/extension_manager/showDoc.php?file=../../'+p)
};

$(window).unload(function(){window.name=JSON.stringify(store)});

