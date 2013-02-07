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
/**
* 
*/
function processLabel ($arr)
{
	$out = array();
	
	foreach ($arr as $k => $str)
	{
		
		$out[$k] = array();	
		/*
							'tooltip' => false,
							'doc' => false,
							'placeholder' => false,
							'tabhead' => false,
							'accordionhead' => false,
						);*/
		
		// Documentation-File <in Tag-Brackets>
		if (preg_match('/\<([^\)]+)\>/', $str, $doc))
		{
			$str = trim(preg_replace('/\s*\<[^)]*\>/', '', $str));
			$out[$k]['doc'] = $doc[1];
		}
		
		// Tooltip (in Brackets)
		if (preg_match('/\(([^\)]+)\)/', $str, $ttip))
		{
			$str = trim(preg_replace('/\s*\([^)]*\)/', '', $str));
			$out[$k]['tooltip'] = $ttip[1];
		}
		
		// Placeholder [in square Brackets]
		if (preg_match('/\[(.*?)\]/', $str, $pa))
		{
			$str = trim(preg_replace('/\[(.*?)\]/', '', $str));
			$out[$k]['placeholder'] = $pa[1];
		}
		
		// Accordion-Limiter "--"
		$arr = explode('--', $str);
		if (count($arr) === 2)
		{
			$str = trim($arr[1]);
			$out[$k]['accordionhead'] = trim($arr[0]);
		}
		
		// Tab-Limiter "||"
		$arr = explode('||', $str);
		if (count($arr) === 2)
		{
			$str = trim($arr[1]);
			$out[$k]['tabhead'] = trim($arr[0]);
		}
		
		// define the pure Label
		$out[$k]['label'] = $str;
		
	}
	
	return $out;
}
