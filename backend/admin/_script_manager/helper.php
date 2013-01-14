<?php

/**
* compress javascript + css
* 
* @param string $str uncompressed String
* @return compressed String
*/
function compress($str)
{
	$str = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", '', $str); // remove comments
	$str = preg_replace('/(\\t|\\r|\\n)/','', $str); // remove tabs + line-feeds ( agressive Method )
	
	//// temorary methods ( less agressive )
	//// $str = preg_replace('/(\\t)/','', $str); // remove only the tabs
	//// $str = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", $str); // remove blank lines
	
	// replace multiple blanks with one
	$str = preg_replace('/( )+/', ' ', $str);

	// replace blanks/line-feeds between some characters
	$str = str_replace(
						array(	' = ',	') {',	'( ',	' )',	': ',	"}\n}",	";\n}"	), 
						array(	'=',	'){',	'(',	')',	':',	'}}',	';}'	), 
						$str);
 
	return $str;
}


