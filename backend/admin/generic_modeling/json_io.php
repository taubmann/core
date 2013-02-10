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
session_start();


require 'inc/includes.php';

//$projectName = preg_replace('/\W/', '', $_GET['project']);
$action = preg_replace('/\W/', '', $_GET['action']);
if(!isset($_SESSION[$projectName]['root'])) exit('no Rights to edit!');

$backuppath = $ppath . 'generic/backup/';
$path = $ppath . 'generic/' . preg_replace('/\W/', '', $_GET['file']) . '.php';

if(!is_writable($ppath . 'generic')) exit('ERROR: "objects/generic/" is not writable!');
if(!is_writable($backuppath)) exit('ERROR: "objects/generic/backup/" is not writable!');

switch ($action)
{
	case 'get':
		if(file_exists($path))
		{
			echo substr(file_get_contents($path), 13);
		}
		else
		{
			echo '{}';
		}
	break;
	case 'add':
		if(!file_exists($path))
		{
			file_put_contents($path, '<?php exit;?> {}');
			chmod($path, 0776);
			echo 'ok';
		}
		else
		{
			echo L('file_already_exists');
		}
	break;
	case 'delete':
		if(file_exists($path))
		{
			unlink($path);
			echo L('file_deleted');
		}
	break;
	case 'dup':
		if(file_exists($path))
		{
			$newfile = dirname($path) . '/' . preg_replace('/\W/', '', $_GET['newfile']) . '.php';
			copy($path, $newfile);chmod($newfile, 0777);
			echo L('file_duplicated');
		}
	break;
	case 'process_label':
		require '../modeling/inc/includes.php';
		if($arr = json_decode($_GET['str']))
		{
			echo json_encode(processLabel($arr));
		}
	break;
	
	case 'saveonlyjson': // super-root: only change the JSON-File
	case 'dbreplace': // super-root: only replace something in DB-Models
	case 'save': // classic Save-Procedure
		
		if(!is_writable($path)) exit('ERROR: "'.$_GET['file'].'.php" '.L('is not writable').'!');
		
		// TIMESTAMP-ACTION-MODELNAME-USERID
		$bp1 = $backuppath . time() . '-' . $action . '-' . $_GET['file'] . '-' . $_SESSION[$projectName]['special']['user']['id'];
		
		
							
		
		////////////////////////////////////////////////////////////////////////////////////////////
		$new = json_decode($_POST['json'], true);
		$old = json_decode(substr(file_get_contents($path), 13), true);
		
		switch (json_last_error())
		{
			case JSON_ERROR_DEPTH: 		exit('JSON-Error in '.$_GET['file'].': Maximum stack depth exceeded'); break;
			case JSON_ERROR_CTRL_CHAR: 	exit('JSON-Error in '.$_GET['file'].': Unexpected control character found'); break;
			case JSON_ERROR_SYNTAX: 	exit('JSON-Error in '.$_GET['file'].': Syntax error, malformed JSON'); break;
		}
		
		// strip/adapt the arrays if no fullscan required (only values stored in DB)
		if(substr($_GET['file'],0,1) != '_')
		{
			foreach($new as $k=>$v){ $new[$k] = array('value'=>$new[$k]['value']); }
			$new['MODEL'] = ' '.$_GET['file'];
			foreach($old as $k=>$v){ $old[$k] = array('value'=>$old[$k]['value']); }
			$old['MODEL'] = ' '.$_GET['file'];
		}
		
		if ($new === $old && $action !== 'dbreplace')
		{
			exit(L('absolutely_nothing_changed'));
		}
		
		$updated = 0;
		if ($action !== 'saveonlyjson')
		{
			require 'json_patch.php';
			
			$p = new JsonPatch();
			$diff = $p->diff($old, $new);
			
			// we need to lookup for all Fields with Type "Model"
			require $ppath . '__model.php';
			require $ppath . '__database.php';
			
			
			foreach ($objects as $objectname => $object)
			{
				foreach($object->col as $fieldname => $field)
				{
					if ( $field->type == 'MODEL' && 
						 isset($object->col->{$fieldname.'_flag'}) && 
						 isset($object->col->{$fieldname.'_select'})
						)
					{
						//echo $fieldname;
						try
						{
							// get all entries matching the Model-Name
							$query = 'SELECT `id`, `'.$fieldname.'` as j FROM `'.$objectname.'` WHERE `'.$fieldname.'_flag` = ?';
							$prepare = $prepare = DB::instance(intval($object->db))->prepare($query);
							$prepare->execute(array($_GET['file']));
							
							$prepare2 = DB::instance(intval($object->db))->prepare('UPDATE `'.$objectname.'` SET `'.$fieldname.'` = ? WHERE `id` = ?;');
							
							$bp2 = $bp1 . '-' . $objectname . '-' . $fieldname . '-';
							while ($row = $prepare->fetch())
							{
								// create a Backup of the Entry in case something is going wrong!
								// TIMESTAMP-ACTION-MODELNAME-USERID-OBJECTNAME-FIELDNAME-OBJECTID.php
								$backup =  $bp2 . $row->id . '.php';
								file_put_contents( $backup, '<?php exit;?>'."\n".$row->j );
								chmod($backup, 0777);
								
								if ($action == 'save')
								{
									$current = json_decode($row->j, true); // get the saved structure
									$patched = $p->patch($current, $diff); // apply patch to it
									$patched = $p->fixOrder($new, $patched); // fix the Sort-Order on the first Level
									$prepare2->execute( array(json_encode($patched), $row->id) ); // write the Patched Model back to DB
								}
								if ($action == 'dbreplace')
								{
									$str = preg_replace($_POST['needle'], $_POST['replacement'], $row->j);
									
									// abort if Replacement fails
									if ($str == null) exit('Error: Replacement failed');
									
									if ($_POST['test'] == 2)
									{
										$prepare2->execute( array($str, $row->id) ); // write the Patched Model back to DB
									}
									else
									{
										// show the changed JSON-Structure of the first Entry only
										exit( str_replace(array('{',',"'), array("{\n",",\n\""), $str) );
									}
								}
								
								$updated++;
							}
						}
						catch (Exception $e)
						{
							echo $e;
						}
					}
				}
			}
			
			// create a Backup of the old Model
			$backup = $bp1 .'.php';
			copy($path, $backup);
			chmod($backup, 0777);
		}
		
		// save the new JSON
		if ($_GET['action'] !== 'dbreplace')
		{
			file_put_contents($path, '<?php exit;?>'."\n".$_POST['json']);
		}
		
		echo ''.$updated.' '.L('Entries_updated');
				
		
		///////////////////////////////////////////////////////////////////////////////////////////
		
	break;
	/*
	case 'clearbackups':
		
		$deleted = 0;
		$backups = glob($backuppath.'*.php');
		foreach ($backups as $backup)
		{
			unlink($backup);
			$deleted++;
		}
		echo 'ok ('.$deleted.' '.L('Backups_deleted');
		
	break;
	*/
}
	
