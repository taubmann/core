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
/*
 * basic check for System-Capabilities
 * */

function systemInfos()
{
	
	// variables
	$ok = array();
	$html = '<h4 style="cursor:pointer" onclick="$(\'#system_check\').toggle()">'.L('System_Check').'</h4>
	<div id="system_check" style="display:none">';
	
	// PHP version
	$phpversion = phpversion();
	$ok['php_version'] = array(version_compare($phpversion, '5.3.0', '>='), $phpversion);
	
	//
	// echo "<pre>" .print_r( get_declared_classes(), true ). "</pre>"; 
	/*
	foreach (array() as $m)
	{
		foreach (get_declared_classes() as $c)
		{
			if ($m === strtolower($c)) {
				$exists = TRUE;
				break;
			}
		}
	}*/
	
	// PDO
	$driver = PDO::getAvailableDrivers();
	$ok['pdo'] = array((count($driver)>0), implode(', ', $driver));
	
	
	// Safe mode
	$safe_mode = ini_get('safe_mode');
	$safe_modeOk = ($safe_mode == '' || $safe_mode == 0 || $safe_mode == 'Off');
	$ok['safe_mode'] = array($safe_modeOk, ($safe_modeOk?'OFF':'ON'));
	
	// Maximum execution time
	$max_execution_time = ini_get('max_execution_time');
	$ok['max_execution_time'] = array(($max_execution_time >= 30), $max_execution_time.' SEC');
	
	// Memory limit
	$memory_limit = ini_get('memory_limit');
	$ok['memory_limit'] = array((intval($memory_limit) >= 16), $memory_limit.'');
	
	// Register globals
	$register_globals = ini_get('register_globals');
	$register_globalsOk = ($register_globals == '' || $register_globals == 0 || $register_globals == 'Off');
	$ok['register_globals'] = array($register_globalsOk, ($register_globalsOk?'OFF':'ON'));
	
	// File uploads
	$file_uploads = ini_get('file_uploads');
	$file_uploadsOk = ($file_uploads == 1 || $file_uploads == 'On');
	$ok['file_uploads'] = array($file_uploadsOk, ($file_uploadsOk?'On':'Off'));
	
	// Upload maximum filesize
	$upload_max_filesize = ini_get('upload_max_filesize');
	$ok['upload_max_filesize'] = array((intval($upload_max_filesize) >= 8), $upload_max_filesize);
	
	
	
	//print_r($ok);
	
	foreach($ok as $k => $v){
		$html .= '<p class="si '.($v[0]?'si_ok':'si_err').'"><b>'.L($k).'</b> '.$v[1].'</p>';
	}
	
	$html .= '<h4>'.L('Directory_Check').'</h4>';
	$folders = array(
		'projects',
		'backend/extensions',
		'backend/wizards'
	);
	$main = '../../../';
	foreach($folders as $folder)
	{
		$f = $main . $folder;
		$html .= '<p class="si ' . ((file_exists($f)) ? (is_writable(($f)) ? 'si_ok' : 'si_warn') : 'si_err') . '"><b>'.$f.'</b></p>';
	}
	
	$html .= '</div>';
	
	return $html;
}

?>
