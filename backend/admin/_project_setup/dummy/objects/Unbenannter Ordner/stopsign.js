// StopSign - builds a CSS stop sign
//
// usage:
//
// div = StopSign(size, units, text, text_top, font_size)
//
// where:
//
// size		is the number of units for the height and width of the stop sign
// units	is the css measurement to use (e.g., 'px', 'in', etc.)
// text		is the text to display on the stop sign
// text_top	is how far down to move the text from the mid-point of the
//		upper-left side, expressed as a CSS measurement (e.g., "10px")
// font_size	is the size of font to use (e.g., "120%", "2em")
//
// You can apply additional styling via the class of the outer container, which is
// "stopsign-container".  The container is positioned relative, so you may need to move it.
// The div that directly contains the text is classed "stopsign-text".
//
function StopSign(size, units, text, text_top, font_size) {
  var side = size * 0.414213	// sqrt(2) - 1
  if (units == "px") {
    side = Math.floor(side);
  }
  var x = (size - side) / 4;
  if (units == "px") {
    x = Math.floor(x);
  }
  return $('<div class="stopsign-container" style="position:relative">' +
  	      '<div style="position:absolute;border-right:' + x + units + ' solid red;' +
	                 'border-top:' + x + units + ' solid transparent;' +
			 'border-bottom:' + x + units + ' solid transparent;top:' + x + units +
			 ';left:0px;width:0px;height:' + (side + x*2) + units +
			 ';max-height:' + side + units +';"></div>' +
	      '<div style="position:absolute;border-bottom:' + x + units + ' solid red;' +
	      		 'border-left:' + x + units + ' solid transparent;' +
			 'border-right:' + x + units + ' solid transparent;' +
			 'top:' + ($.browser.msie ? -2 : 0) + 'px;left:' + x + units +
			 ';width:' + (side + x*2) + units +
			 ';max-width:' + side + units + ';height:0px;font-size:0px;"></div>' +
	      '<div style="position:absolute;border-left:' + x + units + ' solid red;' +
	      		 'border-top:' + x + units + ' solid transparent;' +
			 'border-bottom:' + x + units + ' solid transparent;' +
			 'top:' + x + units + ';left:' + (x*3 + side) + units + ';width:0px;' +
			 'height:' + (side + x*2) + units + ';max-height:' + side + units + ';"></div>' +
	      '<div style="position:absolute;border-top:' + x + units + ' solid red;' +
	      		 'border-left:' + x + units + ' solid transparent;' +
			 'border-right:' + x + units + ' solid transparent;' +
			 'top:' + (x*3 + side) + units + ';left:' + x + units + ';' +
			 'width:' + (side + x*2) + units + ';max-width:' + side + units +
			 ';height:0px;font-size:0px;"></div>' +
	      '<div style="position:absolute;top:' + x + units + ';left:' + x + units +
	      		 ';width:' + (side + x*2) + units + ';height:' + (side + x*2) + units + ';' +
			 'background-color:red;">' +
		'<div class="stopsign-text" style="position:relative;top:' + text_top +
			 ';color:white;font-size:' + font_size + ';text-align:center;">' +
			 text +
		'</div>' +
	      '</div>' +
	    '</div>');
}
