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

// PREPARATION ////////////////////////////////////////////////////////////////////


session_start();
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(0);


// VARIABLES

$html 			= ''; // Html-Output (at the bottom)

$queries 		= array(); // array of DB-Queries
$tables 		= array(); // array to hold existing Column-Names
$reduced_tables = array(); // array to hold Tables with dropped columns for sqlite

require 'inc/includes.php';

foreach(	array(
					
					'inc/process_includes.php',// load Helper-Functions
					'inc/objecttemplate.php',// load Template-Class
					
					'../../inc/php/pclzip.lib.php',// load ZIP-Library
					'../../inc/php/functions.php',// version no
					
					$ppath . '__model.php',// load old Model => (object)$objects
					$ppath . '__modelxml.php',// load new Model => (string)$model
					$ppath . '__database.php',// load Database-Connector
					
				) as $inc)
{
	if(file_exists($inc)){
		//echo $inc.'<br />';
		include $inc;
	}else {
		exit($inc . ' does not exist!');
	}
}

if(!is_writable($ppath)) exit('Folder "objects" is not writable!');

// INCLUDES



// OBJECTS
$datatypes 		= json_decode(file_get_contents('rules/datatypes.json'), true);// load Datatypes
$dbModel 		= getTableStructure(); //
$jsonModel		= json_decode(json_encode($objects), true);
$xmlModel		= json_decode(json_encode(simplexml_load_string($model)), true);
$newModel 		= array();

$oldKeys 		= array_keys($dbModel);
$newKeys 		= array();
$relations 		= array();
$objects_to_rebuild = array();
$objects_to_delete = array();

// define ZIP-Object
$zipName = time() . '_' . md5(rand()) . '.zip';// name containing timestamp + obfuscated string (prevent direct downloading)
$zipPath = $ppath . 'backup/' . $zipName;
$archive = new PclZip ( $zipPath );
$z = $archive->add($ppath . '/__modelxml.php', PCLZIP_OPT_REMOVE_PATH, $ppath);


// Query-Array for HTML-Output
$queryHtmlOutput 	= array();
$fileHtmlOutput 	= array();

// fix it, if we have only one Object
if ( !isset($xmlModel['object'][0]['@attributes']['name']) )
{
	$xmlModel['object'] = array($xmlModel['object']);
}


/*
 * 
 * 
 * 
 * */
foreach ($xmlModel['object'] as $object)
{
	
	// temporary Object-Array
	$tmp = array();
	
	// get Object-Name
	$name = strtolower($object['@attributes']['name']);
	
	// die if invalid Object-Name!
	if(!preg_match("#^[\w]+$#", $name)) { 
		exit('Object-Name: "'.$name.'" is not valid!');
	}
	
	// transform XML-Object to JSON-Structure
	
	// prepare & convert Nodes (key, 	simple, deep)
	$nodes = array(	array('lang',		false, false),
					array('tags',		false, true),
					array('hooks',		false, true ),
					array('url',		false, false),
					array('vurl',		true,  false),
					array('ttype',		true,  false),
					array('hidettype',	true,  false),
					array('comment',	true,  false)
				  );
	foreach($nodes as $a)
	{
		if( isset($object[$a[0]]) && $b = text2array($object[$a[0]], $a[1], $a[2]) )
		{
			$tmp[$a[0]] = $b;
		}
	}
	
	// test the Tables 
	// define Database-Index
	$tmp['db'] = intval($object['db']);
	if(!isset($queries[$tmp['db']])) $queries[$tmp['db']] = array();
	
	// test & process Fields
	$tmp['col'] = processObject ($name, $object, $tmp['db']);
	
	// test Hierarchy
	checkHierarchy($queries, $name, $tmp['db'], $tmp['ttype'], $tmp);
	
	
	// assign temporary Object to the new Model
	$newModel[$name] = $tmp;
	
}// foreach $xmlModel END


//add Relations to newModel [type, name1, name2]
foreach($relations as $r)
{
	$types = ($r[0]=='p') ? array('p','c') : array('s','s');
	$newModel[$r[1]]['rel'][$r[2]] = $types[0];
	$newModel[$r[2]]['rel'][$r[1]] = $types[1];
	
}// foreach relations END




/*
 * 
 * 
 * 
 * */
foreach ($jsonModel as $old_name => $old_object)
{
	// delete the whole Object/Table
	if (!isset($newModel[$old_name]))
	{
		// delete relations
		foreach($old_object['rel'] as $k=>$v)
		{
			deleteTable ($queries, mapName($k,$old_name), $old_object['db']);
		}
		// delete the old object itself
		deleteTable ($queries, $old_name, $old_object['db']);
	}
	// check for Columns to delete
	else
	{
		$columnsToDelete = array();
		if (is_array($old_object['col']))
		{
			$columnsToDelete = array_diff (
										array_keys($old_object['col']),
										array_keys($newModel[$old_name]['col'])
										  );
		}
		foreach ($columnsToDelete as $d) {
			deleteColumn ($queries, $old_name, $d, $old_object['db']);
		}
	}
	
}// foreach old Model END


// SQLite post-processing for dropped colums
//fixSQLiteColumns();



// create Database-Backup
if (!isset($_SESSION[$projectName]['config']['modeling']['no_backup']))
{
	for ($i=0; $i<count(Configuration::$DB_TYPE); $i++)
	{
		
		if (Configuration::$DB_TYPE[$i] == 'sqlite')
		{
			$z = $archive->add( $ppath . Configuration::$DB_DATABASE[$i], PCLZIP_OPT_REMOVE_PATH, $ppath );
		}
		
		if (Configuration::$DB_TYPE[$i] == 'mysql')
		{
			
			$sql_filename = Configuration::$DB_DATABASE[$i] . '.sql';
			$sql_string = dumpMySqlTables($i, $queries);
			
			$z = $archive->create( array(
											array(
												PCLZIP_ATT_FILE_NAME 	=> $sql_filename,
												PCLZIP_ATT_FILE_CONTENT => $sql_string
											)
										)
								);
		}
	}
}



//print_r($queries);
foreach ($queries as $i => $db_queries)
{
	foreach ($db_queries as $name => $arr)
	{
		// detect structural changes within the Object
		if (count($arr) > 0)
		{
			// process Queries
			foreach ($arr as $q)
			{
				$err = '';
				
				// (try to) save changes to the Database
				try
				{
					//$indx = (isset($newModel[$name]['db']) ? $newModel[$name]['db'] : $dbModel[$name]['db']);
					DB::instance( $i )->query( $q );
				}
				catch(Exception $e)
				{
					$err = ' <span style="color:red">(' . $e->getMessage() . ')</span> ';
				}
				
				// record Query for HTML-Output (with simple SQL-Syntax-Highlighting)
				$queryHtmlOutput[] = '<div>' . preg_replace_callback('/[A-Z]{2,}/', create_function('$matches','return "<strong>".$matches[0]."</strong>";'), $q) . $err . '</div>';
				
			}
		}
		
		if (	
				!in_array($name, $objects_to_delete) && 
				
				(	count($arr) > 0 || 
					in_array($name, $objects_to_rebuild) || 
					isset($_GET['rebuild_objects'])
				)
			)
		{
			
			// call Object-Generator ($name, $model, $types, $savepath)
			new ObjectGenerator($projectName, $name, $newModel, $datatypes, $ppath, $KITVERSION, isset($_GET['debug']));
			
			$fileHtmlOutput[] = '<div class="grn">' . L('PHP_Class') . ' "<strong>' . $name . '</strong>" ' . 
								(file_exists($ppath .'class.'.$name.'.php') ? 
											(is_writable($ppath .'class.'.$name.'.php') ? L('updated') : '<span class="rd">'.L('could_not_be_written').'</span>' ) : 
											L('created')
								) .
								" - ( <a target='_blank' href='helper/phprev.php?project=$projectName&file=$name'>" . L('view_Source') . 
								"</a> )</div>";
		}
		
	}
}
//print_r($newModel);
///////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////



$fileHtmlOutput[] = '<div class="bld">
					<strong>__model.php</strong> ' . L('updated') . 
					' - ( <a target="_blank" href="helper/phprev.php?project='.$projectName.'&file=__model">' . 
					L('view_Source') . 
					'</a> )</div>';


// save the new Model as JSON

$jsonstr0 = '<?php
//cms-kit Data-Model for: "'.$projectName.'"
$stringified_objects = <<<EOD
';
$jsonstr1 = json_encode($newModel);
$jsonstr2 = '
EOD;
$objects = json_decode($stringified_objects);
?>
';
file_put_contents( $ppath."__model.php", $jsonstr0 . indentJson($jsonstr1) . $jsonstr2 );


chmod($ppath . "__model.php", 0777);
chmod($zipPath, 0777);

// register new Model for instant Adaption of Backend-Settings
$_SESSION[$projectName]['objects'] = json_decode($jsonstr1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>cms-kit-process</title>
	<meta charset="utf-8" />
	
	<link href="../../inc/css/<?php echo end($_SESSION[$projectName]['config']['theme'])?>/jquery-ui.css" rel="stylesheet" />
	<link href="../../inc/css/<?php echo end($_SESSION[$projectName]['config']['theme'])?>/style.css" rel="stylesheet" />
<style>
body {
	font: 65% "Trebuchet MS", sans-serif;
}

#controls {
	position: fixed;
	top: 5px;
	left: 5px;
	padding: 5px;
}

#working_area {
	position: absolute;
	width: 96%;
	top: 80px;
	left: 10px;
}

fieldset { 
	border: 2px solid #ccc; 
	border-radius: 6px; 
	background: white;
	margin-bottom: 40px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	-khtml-border-radius: 5px;
	-moz-box-shadow: 4px 4px 8px #888; /* FF 3.5+ */
	-webkit-box-shadow: 4px 4px 8px #888; /* Safari 3.0+, Chrome */
	box-shadow: 4px 4px 8px #888; /* Opera 10.5, IE 9.0 */
	filter: progid:DXImageTransform.Microsoft.Shadow(Strength=5, Direction=135, Color='#888888'); /* IE 6, IE 7 */
	-ms-filter: progid:DXImageTransform.Microsoft.Shadow(Strength=5, Direction=135, Color='#888888'); /* IE 8 */
}

fieldset div {
	border: 1px solid #eee; 
	border-radius: 6px; 
	margin-bottom: 5px;
	padding: 3px;
}

#sql-fieldset strong {color: #006;}
.grn {color: green;}
.orn {color: orange;}
.rd {color: red;}
.bl {color: blue;}

#del_backup iframe {
	width: 50px;
	height: 10px;
	margin: 0;
	border: 0px none;
}


/*
#controls button, #controls  iframe {
	width: 170px;
	margin-top: 5px;
}

#clear-frame {
	width: 90%;
	height: 35px;
	
}
#clearForm input[type=number] {
	width: 20px;
	float: right;
}
#clearForm div {
	clear: both;
}

*/

</style>
<!--[if lt IE 9]>
	<style type="text/css" title="text/css">
		fieldset { border: 1px solid silver; padding: 3px; }
	</style>
<![endif]-->
<script>

function showDiff(el)
{ 
	if(el.value != '') {
		window.open('helper/json_diff.php?project=<?php echo $projectName;?>&zip='+el.value, 'diff')
	}
}
function deleteBackups(el)
{
	var i = el.selectedIndex, v = el.value;
	if(v != '') {
		var q = confirm('<?php echo L('really_delete_Backups_before');?>: ' + el.options[i].text);
		if(q) {
			document.getElementById('del_backup').innerHTML = '<iframe src="helper/clear_backups.php?project=<?php echo $projectName;?>&ts='+v.split('_').shift()+'"></iframe>';
		}
	}
}

</script>
</head>
<body>


<div id="controls" class="ui-widget-header ui-corner-all">
	<!--
	<button
		onclick="window.location='index.php?project=<?php echo $projectName;?>'"
		class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" 
		role="button" 
		aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-arrowreturnthick-1-w"></span>
			<span class="ui-button-text"><?php echo L('Data_Modeling');?></span>
	</button>
	-->
	<button
		onclick="window.location='process.php?rebuild_objects=1&project=<?php echo $projectName;?>';"
		class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary"  
		role="button" 
		aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-refresh"></span>
			<span class="ui-button-text"><?php echo L('Rebuild_Objects');?></span>
	</button>
	<button
		onclick="window.location='process.php?rebuild_objects=1&debug=1&project=<?php echo $projectName;?>';"
		class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary"  
		role="button" 
		aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-refresh"></span>
			<span class="ui-button-text"><?php echo L('Rebuild_Objects');?> ( DEBUG )</span>
	</button>
	<button
		onclick="window.open('../file_manager/index.php?project=<?php echo $projectName;?>','fm')"
		class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" 
		title=""  
		role="button" 
		aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-folder-open"></span>
			<span class="ui-button-text"><?php echo L('file_manager');?></span>
	</button>
	<button
		onclick="window.open('../db_admin/index.php?project=<?php echo $projectName;?>','db')"
		class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" 
		title=""  
		role="button" 
		aria-disabled="false">
			<span class="ui-button-icon-primary ui-icon ui-icon-calculator"></span>
			<span class="ui-button-text"><?php echo L('db_admin');?></span>
	</button>
	
</div>
	
<form id="working_area">
	
<?php

$backupList = getBackupList();
echo '
<fieldset>
	<legend>1. '.L('Backup').'</legend>
	<div><strong>'.L('Backup').'</strong>: <a href="'.$zipPath.'">'.$zipName.'</a></div>
	<div>
	<div><strong>'.L('old_Backups').'</strong>: 
	<select class="ui-button ui-widget ui-state-default ui-corner-all" onchange="showDiff(this)"><option value="">' . L('show_diff') . '</option>' . $backupList . '</select>
	<span id="del_backup"><select class="ui-button ui-widget ui-state-default ui-corner-all" onchange="deleteBackups(this)"><option value="">' . L('delete_backups_before') . '</option>' . $backupList . '</select></span>
	</div>
</fieldset>

<fieldset id="sql-fieldset">
	<legend>2. '.L('SQL_Queries').'</legend>
' . implode("\n", $queryHtmlOutput) . '
</fieldset>

<fieldset>
	<legend>3. '.L('PHP_Objects').'</legend>
' . implode("\n", $fileHtmlOutput) . 
'</fieldset>
';
?>
	
</form>
		
</body>
</html>
