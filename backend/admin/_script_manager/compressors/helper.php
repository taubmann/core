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
*********************************************************************************/

/**
* compress javascript + css
* 
* @param string $str uncompressed String
* @param bool $noc no Comment
* @return compressed String
*/
function compress($str, $noc=false)
{
	//grab the first comment-block
	$comment = ($noc?'':array_shift(explode('*/', $str)).'*/');
	
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
						$str
					  );
	$str = "\n" . $comment . "\n\n" . $str;
	return $str;
}


