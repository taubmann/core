<?php
/*
 * AJAX-Backend for Fulltext-Search via %LIKE%
 * */
require 'header.php';

header('Content-Type: text/plain; charset=utf-8');

$ppath = '../../../projects/' . $projectName;
include_once($ppath . '/objects/__database.php');

// secure search-string (taken from $_REQUEST, not $_GET)
$strip_this = "/[^äöüßÄÖÜa-z0-9\\040\\.\\-\\_\\,\\:\\!\\%\\*\\@\\?]/i";
$term = preg_replace($strip_this, '', mb_strtolower($_REQUEST['term']));

//exit('[{"label":"'.$_REQUEST['term'].'"}]');

$fields = array();
$likes  = array();
$out    = array();

// Database-specific preparation of Concat-Statements
$labels = $_SESSION[$projectName]['labels'][$objectName];
if(Configuration::$DB_TYPE[$db] == 'sqlite'){ $concat = implode('`||\' \'||`', $labels ); }
if(Configuration::$DB_TYPE[$db] == 'mysql' ){ $concat =  'CONCAT('.implode(',\' \',', $labels ).')'; }

// prepare Query-Parts: `fieldname` LIKE + %term% 
foreach($_SESSION[$projectName]['objects']->{$_GET['objectName']}->col as $k => $v)
{
	if($k != 'id')
	{
		$fields[] = '`'.strtolower($k).'` LIKE ?';
		$likes[]  = '%' . ((substr($k, 0, 2) == 'e_') ? base64_encode($term) : $term) . '%';
	}
}

$query = 'SELECT `id` AS id, '.$concat.' AS lbl FROM `'.$objectName.'` WHERE ' . implode(' OR ', $fields) . ' LIMIT 30';

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
