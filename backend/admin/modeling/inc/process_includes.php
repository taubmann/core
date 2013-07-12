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
// 

/**
* 
* 
* 
*/
$idAdd = array (
	array (
		'sqlite' => 	array('INTEGER', 'INTEGER', 'PRIMARY KEY ASC', ''), // SQLite-ID-Fields
		'mysql' => 		array('INT (11)', 'INT (11)',  'NOT NULL AUTO_INCREMENT PRIMARY KEY', '') // MySql-ID-Fields
	),
	array (
		'sqlite' => 	array('INTEGER', 'TEXT', 'NOT NULL UNIQUE', ''), // SQLite-ID-Fields
		'mysql' => 		array('INT (11)', 'VARCHAR (25)', 'NOT NULL UNIQUE', '') // MySql-ID-Fields
	),
	
);



/**
* get the Table-Structure of all Databases
* 
*/
function getTableStructure()
{
	
	$tables = array();
	
	// loop the Databases
	foreach(Configuration::$DB_TYPE as $db => $type)
	{
		
		$tables[$db] = array();
		
		// create DB-agnostic SQL
		switch ($type)
		{
			case 'mysql':
				$sql1 = "SELECT table_name AS name FROM information_schema.tables WHERE table_type = 'base table' AND table_schema = '".Configuration::$DB_DATABASE[$db]."';";
				$sql2 = "SELECT column_name AS name, data_type AS type FROM information_schema.columns WHERE table_name = '%s';";
			break;
			case 'sqlite':
				$sql1 = "SELECT name FROM sqlite_master WHERE type = 'table';";
				$sql2 = "PRAGMA table_info(%s);";
			break;
		}
		
		// loop the Tables
		foreach (DB::instance($db)->query($sql1) as $row1)
		{
			$prepare = DB::instance($db)->prepare(str_replace('%s', $row1->name, $sql2));
			$prepare->setFetchMode(PDO::FETCH_ASSOC);
			$tables[$db][$row1->name] = array();
			try
			{
				$prepare->execute();
				$data = $prepare->fetchAll();
				// loop the Columns
				foreach($data as $a)
				{
					$tables[$db][$row1->name][$a['name']] = strtoupper($a['type']);
				}
			}
			catch (Exception $e)
			{
				echo $e->getMessage(); //throw Exception
			}
		}
	}
	
	return $tables;
}

/*
 * 
 * 
 * */
function processObject ($name, $object, $db, $increment)
{
	global $dbModel, $datatypes, $queries, $tables, $errorHtmlOutput;
	
	// every table needs an ID!!
	if ($object['fields']['field'][0]['@attributes']['name'] != 'id')
	{
		$errorHtmlOutput[] = 'ID is missing in "'.$name.'"';
		array_unshift($object['fields']['field'], array('@attributes'=>array('name'=>'id'),'datatype'=>'INTEGER'));
	}
	
	$queries[$db][$name] = array();
	
	$tmp = array('col'=>array('id'=>array('type'=>'INTEGER')));
	
	//@$old_db = intval($dbModel[][$name]['db']);
	
	// if Database has changed, create new Table (==kill old Object)
	//if($old_db != $db) $dbModel[$name] = null;
	
	if ( !isset($dbModel[$db][$name]) )
	{ 
		addTable($queries, $name, $db, $increment);
	}
	
	// warn user if we have only one Field ??????
	if ( !isset($object['fields']['field'][0]['@attributes']['name']) )
	{
		echo '<p>ONLY ONE FIELD IN "'.$name.'"!!</p>';
	}
	
	// loop the other Fields
	for ( $i=0; $i<count($object['fields']['field']); $i++ )
	{
		if ( $fname = $object['fields']['field'][$i]['@attributes']['name'] )
		{
			if ($fname == 'id')
			{
				// check within id-Field for siblings
				checkRelation ($queries, $name, 'id', 0, $object, $db, $increment, 's');
				continue;
			}
			
			// if the Column dosen't exist
			if ( !isset($dbModel[$db][$name][$fname]) )
			{
				addColumn ($queries, $name, $fname, $db, $object['fields']['field'][$i]['datatype']);
			}
			else
			{
				// check if Datatype has changed
				$oldColumnType = $dbModel[$db][$name][$fname];//$datatypes[  ][ Configuration::$DB_TYPE[$db] ];
				$newColumnType = $datatypes[ $object['fields']['field'][$i]['datatype'] ][ Configuration::$DB_TYPE[$db] ];
				
				if ($oldColumnType != $newColumnType)
				{
					//echo $name.' / '.$newColumnType;
					
				}
			}
			
			if ($fname != 'id' && substr($fname, -2) == 'id' && $object['fields']['field'][$i]['datatype'] == 'INTEGER')
			{
				// check within xxid-Fields (for parents)
				checkRelation ($queries, $name, $fname, $i, $object, $db, $increment, 'p');
			}
			
			// add some Credentials to the JSON-Object ////////////////////////////////////////////////
			
			
			
			$tmp['col'][$fname] = array	('type' => $object['fields']['field'][$i]['datatype'], 'tpl' => $datatypes[ $object['fields']['field'][$i]['datatype'] ]['tpl']);
			
			
			//
			if ($b = text2array($object['fields']['field'][$i]['filter']))
			{
				$tmp['col'][$fname]['filter'] = $b;
			}
			
			//
			if ($b = text2array($object['fields']['field'][$i]['add']))//, false, true
			{
				$tmp['col'][$fname]['add'] = $b;
			}
			//
			if (isset($object['fields']['field'][$i]['default']))
			{
				$tmp['col'][$fname]['default'] = trim( urldecode($object['fields']['field'][$i]['default']) );
				
			}
			//
			if ($b = text2array($object['fields']['field'][$i]['lang']))
			{
				$tmp['col'][$fname]['lang'] = processLabel($b);// see: inc/includes.php
			}
			
			//
			if ($b = text2array($object['fields']['field'][$i]['tags'], false, true) )
			{
				$tmp['col'][$fname]['tags'] = $b;
			}
			
			//
			if ($b = text2array($object['fields']['field'][$i]['comment'], true))
			{
				$tmp['col'][$fname]['comment'] = $b;
			}
			
		}
	
	}
	
	return $tmp['col'];
	
}// processObjects END



/*
 * 
 * 
 * 
 * */
function addTable (&$queries, $name, $db, $increment)
{
	global $idAdd;
	$add = $idAdd[ $increment ][ Configuration::$DB_TYPE[$db] ];
	
	$queries[$db][$name][] = 'CREATE TABLE IF NOT EXISTS `' . $name . '` (`id` ' . $add[1] . ' ' . $add[2] . ')' . $add[3] . ';';
}

/*
 * 
 * 
 * 
 * */
function addColumn (&$queries, $name, $fname, $db, $type)
{
	global $tables, $datatypes;
	
	if ( !isset($tables[$name][$fname]) )
	{
		$queries[$db][$name][] = 'ALTER TABLE `' . $name . '` ADD COLUMN `' . $fname . '` ' . $datatypes[ $type ][ Configuration::$DB_TYPE[$db] ] . ';';
	}
}
/**
* 
* 
* 
*/
function deleteColumn (&$queries, $name, $fname, $db)
{
	global $tables, $reduced_tables;
	
	// sqlite does not understand drop column!!!!
	if (Configuration::$DB_TYPE[$db] == 'sqlite') {
		// CREATE TABLE tmp_table AS SELECT id, name FROM src_table
		//$reduced_tables[$name] = 1;// see fixSQLiteColumns()
	}
	//
	if (Configuration::$DB_TYPE[$db] == 'mysql') {
		$queries[$db][$name][] = 'ALTER TABLE `' . $name . '` DROP `' . $fname . '`;';
	}
}
/**
* 
* 
* 
*/
function deleteTable (&$queries, $name, $db)
{
	global $ppath, $fileHtmlOutput, $objects_to_delete;
	$objects_to_delete[] = $name;
	$queries[$db][$name][] = 'DROP TABLE IF EXISTS `'.$name.'`;';
	
	$fileHtmlOutput[] = '<div class="orn">' . L('PHP_Class') . ' <strong>"' . $name . '"</strong> ' . L('deleted') . '</div>';
	@unlink( $ppath . 'class.' . $name . '.php');
}

// dosent work atm !!!!!!!!!!!!!!!!!!!!!!!!!!!
/*
function fixSQLiteColumns()
{
	global $reduced_tables, $newModel, $queries;
	
	foreach($reduced_tables as $name=>$x)
	{
		$cols = array_keys($newModel[$name]['col']);
		$queries[$name][] = 'CREATE TABLE `tmp____'.$name.'` AS SELECT `'.implode('`, `',$cols).'` FROM `'.$name.'`;';
		$queries[$name][] = 'DROP TABLE IF EXISTS `'.$name.'`;';
		$queries[$name][] = 'ALTER TABLE `tmp____'.$name.'` RENAME TO `'.$name.'`;';
	}
}*/

function alterColumnType (&$queries, $name, $fname, $db)
{
	global $tables;

	// sqlite does not understand this kind of alter column!!!!
	if (Configuration::$DB_TYPE[$db] == 'sqlite') {
		// CREATE TABLE tmp_table AS SELECT id, name FROM src_table
	}
	//
	if (Configuration::$DB_TYPE[$db] == 'mysql') {
		$queries[$db][$name][] = 'ALTER TABLE `' . $name . '` MODIFY COLUMN `' . $fname . '` ' . ';';
	}
}

/**
* 
* 
* 
*/
function checkRelation (&$queries, $name, $fname, $index, $object, $db, $increment, $type)
{
	global $dbModel, $relations, $objects_to_rebuild;
	global $datatypes, $idAdd;
	
	
	$add = $idAdd[ $increment ][ Configuration::$DB_TYPE[$db] ];
	
	if ( $rel = $object['fields']['field'][$index]['relation'] )
	{
		
		foreach ($rel as $r)
		{
			$rl = (isset($r['@attributes']['object']) ? $r['@attributes']['object'] : $r['object']);
			$map = mapName($name, $rl);
			
			if ( $type=='s' && !isset($dbModel[$db][$map]) )
			{
				$objects_to_rebuild[] = $name;
				$objects_to_rebuild[] = $rl;
				$queries[$db][$name][] = 'CREATE TABLE IF NOT EXISTS `' . $map . '` (`' . $name . 'id` ' . $add[1] . ', `' . $rl . 'id` ' . $add[1] . ', `' . $name . 'sort` ' . $add[0] . ', `' . $rl . 'sort` ' . $add[0] . ');';
			}
			
			$relations[] = array( $type, $name, $rl );
		}
	}
	
}// checkRelation END

/**
 * create Name for the Mapping-Tables (abmap)
 * @param string $a Table-Name a
 * @param string $b Table-Name b
 * @param string $add Addition (default "map")
 * @return string Mapping-Name
 * */
function mapName ($a, $b, $add = 'map')
{
	$x = array(strtolower($a), strtolower($b));
	natsort($x);
	return implode('', $x) . $add;
}


/**
* 
* 
* 
*/
function checkHierarchy (&$queries, $name, $db, $increment, $type, &$tmp)
{
	global $newModel, $dbModel, $idAdd;
	
	
	// add DB-Stuff
	switch($type)
	{
		case 'Tree':
			// create new Columns 
			$id_type = (($increment==1)?'VARCHAR':'INTEGER');
			
			if(!isset($dbModel[$db][$name]['treeparentid']))
			{
				addColumn ($queries, $name, 'treeparentid',	$db, $id_type);
				addColumn ($queries, $name, 'treeleft', 	$db, 'INTEGER');
				addColumn ($queries, $name, 'treeright', 	$db, 'INTEGER');
			}
			
			//add the Columns to the new Object
			$tmp['col']['treeparentid']['type'] = $id_type;
			$tmp['col']['treeleft']['type'] = 'INTEGER';
			$tmp['col']['treeright']['type'] = 'INTEGER';
			
			// add informations to hide the Fields ????
			
			
		break;
		case 'Graph':
			if(!isset($dbModel[$db][$name.'matrix'])) 
			{
				$add = $idAdd[ $increment ][ Configuration::$DB_TYPE[$db] ];
				$queries[$db][$name][] = 'CREATE TABLE IF NOT EXISTS `' . $name . 'matrix` (`pid` ' . $add[1] . ', `id` ' . $add[1] . ', `hops` ' . $add[0] . ', `sort` ' . $add[0] . ');';
			}
		break;
	}
	
	// remove Tables/Columns if...
	// ...we are no Graph anymore
	if (in_array($type, array('List','Tree')) && isset($dbModel[$db][$name.'matrix']))
	{
		$queries[$db][$name][] = 'DROP TABLE IF EXISTS `' . $name . 'matrix`;';
	}
	// we are no Tree anymore
	if (in_array($type, array('List','Graph')) && isset($dbModel[$db][$name]['treeparentid']))
	{
		deleteColumn ($queries, $name, 'treeparentid', 	$db);
		deleteColumn ($queries, $name, 'treeleft', 		$db);
		deleteColumn ($queries, $name, 'treeright', 	$db);
	}
	
}


// rebuild the table from scratch does not work atm
/*
 * 
 * 
 * 
 * 
function forceTableRebuild($name, &$queries)
{
	global $newObject;
	
	$queries[$name][] = 'CREATE TABLE IF NOT EXISTS `'.strtolower($name).'_____tmp` (' . implode(', ', fieldStr($k)) . ');';
	$queries[$name][] = 'INSERT INTO `' . strtolower($k) . '_____tmp` SELECT `' . strtolower( implode('`,`', array_keys($newObject[$k]['col'])) ) . '` FROM `' . strtolower($k) . '`;';
	$queries[$name][] = 'DROP TABLE IF EXISTS `' . strtolower($k) . '`;';
	$queries[$name][] = 'ALTER TABLE `' . strtolower($k) . '_____tmp` RENAME TO `' . strtolower($k) . '`;';
}*/

/**
* 
* 
*/
function text2array($str, $simple=false, $deep=false)
{
	
	if ( (isset($str) && !empty($str) && ($str !== '__EMPTY_STRING_') || $str === '0') )
	{
		//echo $str."\n";
		if ($simple) return urldecode(strval($str));
		
		$lines = explode(PHP_EOL, urldecode($str));
		$array = array();
		$pk = false;
		foreach ($lines as $line)
		{
			$lineArr = explode(':', trim($line));
			$k = array_shift($lineArr);
			
			//if(!isset($lineArr[0])) return null;
			
			if ($deep)
			{
				if(!isset($array[$k])){ $array[$k] = array(); }
				$array[$k][] = $lineArr;
			}
			else
			{
				if($k == $pk)
				{
					$lineArr[0] = $array[$k] . '|' . $lineArr[0];
				}
				$array[$k] = $lineArr[0];
			}
			$pk = $k;
		}
		//ksort($array);
		return $array;
	}
	
	return null;
	
}// text2array END





/**
* taken from: http://snipplr.com/view/13024
* 
*/
function dumpMySqlTables ($db, $queries)
{
	
	$sDatabase = Configuration::$DB_DATABASE[$db];
	$sQuery = "SHOW tables FROM " . $sDatabase;
	$sResult = DB::instance($db)->query($sQuery);

	$sData = "
-- cms-kit SQL-Dump (".date(DATE_RFC822).") --

SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";

--
-- Database: `$sDatabase`
--

-- --------------------------------------------------------
";
	 
	while ($aTable = $sResult->fetch(PDO::FETCH_ASSOC))
	{
		$sTable = $aTable['Tables_in_' . $sDatabase];
		
		// no Table-Backup if there are no Changes
		if ( !isset($queries[$sTable]) || count($queries[$sTable])==0 )
		{
			continue;
		}
		
		
		$sQuery = "SHOW CREATE TABLE `$sTable`;";
		
		$sResult2 = DB::instance($db)->query($sQuery);
		
		$aTableInfo = $sResult2->fetch(PDO::FETCH_ASSOC);
		
		$sData .= "\n\n--\n-- Table-Structure of: `$sTable`\n--\n\n";
		$sData .= "DROP TABLE IF EXISTS `$sTable`;\n";
		$sData .= $aTableInfo['Create Table'] . ";\n";
		 
		$sData .= "\n\n--\n-- Data for Table: `$sTable`\n--\n\n";
		
		$sQuery = "SELECT * FROM `$sTable`;\n";
		
		$sResult3 = DB::instance($db)->query($sQuery);
		
		while ($aRecord = $sResult3->fetch(PDO::FETCH_ASSOC))
		{
		
			// Insert query per record
			$sData .= "INSERT INTO `$sTable` VALUES (";
			$sRecord = array();
			foreach( $aRecord as $sField => $sValue ) {
				$sRecord[] = DB::instance($db)->quote($sValue);
			}
			$sData .= implode(',', $sRecord);
			$sData .= ");\n";
		}
	}
	
	return $sData;
}




/**
* Indents a flat JSON string to make it more human-readable
* @param string $json The original JSON string to process
* @return string indented Version of the original JSON string
*/
function indentJson ($json)
{

	$result		= '';
	$pos		= 0;
	$strLen		= strlen($json);
	$indentStr	= "\t";
	$newLine	= PHP_EOL;
	$prevChar	= '';
	$outOfQuotes= true;

	for ($i=0; $i<=$strLen; $i++)
	{

		// Grab the next character in the string.
		$char = substr($json, $i, 1);
		

		// Are we inside a quoted string?
		if ($char == '"' && $prevChar != '\\') {
			$outOfQuotes = !$outOfQuotes;
		
		// If this character is the end of an element, output a new line and indent the next line.
		}
		else if (($char == '}' || $char == ']') && $outOfQuotes)
		{
			$result .= $newLine;
			$pos --;
			for ($j=0; $j<$pos; $j++)
			{
				$result .= $indentStr;
			}
		}
		
		// Add the character to the result string.
		$result .= $char;

		// If the last character was the beginning of an element, output a new line and indent the next line.
		if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
			$result .= $newLine;
			if ($char == '{' || $char == '[')
			{
				$pos ++;
			}
			
			for ($j = 0; $j < $pos; $j++)
			{
				$result .= $indentStr;
			}
		}
		
		$prevChar = $char;
	}

	return $result;
}

/**
* 
* 
* 
*/
function getBackupList()
{
	global $ppath;
	
	$html = '';
	
	$bac_files = glob($ppath . 'backup/*.zip');
	foreach ($bac_files as $bac_file)
	{
		$name = basename($bac_file);
		$st = array_shift(explode('_', $name));
		if (is_numeric($st)) $html .= '<option value="'.$name.'">' . date("d m Y H:i:s", intval($st)) . '</option>';
	}
	
	return $html;
}
