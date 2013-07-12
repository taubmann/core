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
* AJAX-Backend for Fulltext-Search via %LIKE%
*/
require '../../inc/php/header.php';
require $ppath . '/objects/__database.php';

header('Content-Type: text/plain; charset=utf-8');

// secure Search-String (taken from $_REQUEST, not $_GET)
$strip_this = "/[^äöüßÄÖÜa-z0-9\\040\\.\\-\\_\\,\\:\\!\\%\\*\\@\\?]/i";

$term = function_exists('mb_strtolower') ? mb_strtolower($_REQUEST['term']) : strtolower($_REQUEST['term']);
$term = preg_replace($strip_this, '', $term);

// test if autocomplete is working at all
// exit('[{"label":"'.$_REQUEST['term'].'"}]');

$fields = array();
$likes  = array();
$out    = array();

// Database-specific preparation of Concat-Statements (todo: better approach??)
$labels = $_SESSION[$projectName]['labels'][$objectName];
if(Configuration::$DB_TYPE[$db] == 'sqlite'){ $concat = implode('`||\' \'||`', $labels ); }
if(Configuration::$DB_TYPE[$db] == 'mysql' ){ $concat =  'CONCAT('.implode(',\' \',', $labels ).')'; }


// prepare Query-Parts: `fieldname` LIKE + %term% 
foreach($_SESSION[$projectName]['objects'][$_GET['objectName']]['col'] as $k => $v)
{
	if($k != 'id')
	{
		$fields[] = '`'.$k.'` LIKE ?';
		$likes[]  = '%' . ((substr($k, 0, 2) == 'e_') ? base64_encode($term) : $term) . '%';
	}
}

$query = 'SELECT `id` AS id, '.$concat.' AS lbl FROM `'.$objectName.'` WHERE ' . implode(' OR ', $fields) . ' LIMIT 30';

// test show some under-the-hood
// exit('[{"label":"'.$query.'"}]');
// exit('[{"label":"'.implode(',',$likes).'"}]');

$prepare = DB::instance($db)->prepare($query);
$prepare->setFetchMode(PDO::FETCH_ASSOC);
$prepare->execute($likes);

while ($row = $prepare->fetch())
{
	$out[] = '{"id":"'.$row['id'].'","label":"'.$row['lbl'].'","value":"'.$row['lbl'].'"}';
}

echo '['.implode(',', $out).']';


?>
