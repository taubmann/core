<?php
session_start();

$projectName = preg_replace('/\W/', '', $_GET['project']);
if(!isset($_SESSION[$projectName]['root'])) exit('no Rights to edit!');

$opath = '../../../projects/' . $projectName.'/objects/';
$path = $opath . 'generic/' . preg_replace('/\W/', '', $_GET['file']) . '.php';

switch ($_GET['action'])
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
			echo 'file already exists';
		}
	break;
	case 'delete':
		if(file_exists($path))
		{
			unlink($path);
			echo 'file deleted';
		}
	break;
	case 'dup':
		if(file_exists($path))
		{
			$newfile = dirname($path) . '/' . preg_replace('/\W/', '', $_GET['newfile']) . '.php';
			copy($path, $newfile);chmod($newfile, 0777);
			echo 'file duplicated';
		}
	break;
	case 'process_label':
		require '../modeling/inc/includes.php';
		if($arr = json_decode($_GET['str']))
		{
			echo json_encode(processLabel($arr));
		}
	break;
	case 'save':
		
		if(!is_writable($path)) exit('ERROR: "'.$_GET['file'].'.php" is not writable!');
		
		////////////////////////////////////////////////////////////////////////////////////////////
		$new = json_decode($_POST['json'], true);
		$old = json_decode(substr(file_get_contents($path), 13), true);
		switch(json_last_error())
		{
			case JSON_ERROR_DEPTH: 		exit('JSON-Error in '.$_GET['file'].': Maximum stack depth exceeded'); break;
			case JSON_ERROR_CTRL_CHAR: 	exit('JSON-Error in '.$_GET['file'].': Unexpected control character found'); break;
			case JSON_ERROR_SYNTAX: 	exit('JSON-Error in '.$_GET['file'].': Syntax error, malformed JSON'); break;
		}
		
		if($new === $old)
		{
			exit('sorry, absolutely nothing changed!');
		}
		
		require 'json_patch.php';
		
		$p = new JsonPatch();
		$diff = $p->diff($old, $new);
		
		// we need to lookup for all Fields with Type "Model"
		require $opath . '__model.php';
		require $opath . '__database.php';
		
		$updated = 0;
		foreach($objects as $objectname => $object)
		{
			foreach($object->col as $fieldname => $field)
			{
				if ( $field->type == 'MODEL' && isset($object->col->{$fieldname.'_flag'}) && isset($object->col->{$fieldname.'_select'}) )
				{
					//echo $fieldname;
					try
					{
						// get all entries matching the Model-Name
						$query = 'SELECT `id`, `'.$fieldname.'` as j FROM `'.$objectname.'` WHERE `'.$fieldname.'_flag` = ?';
						$prepare = $prepare = DB::instance(intval($object->db))->prepare($query);
						$prepare->execute(array($_GET['file']));
						
						while ($row = $prepare->fetch())
						{
							
							// get the saved structure
							$current = json_decode($row->j, true);
							$patched = $p->patch($current, $diff);// apply patch to 
							$patched = $p->fixOrder($new, $patched);// fix the Sort-Order on the first Level
							
							// write the Patched Model back to DB
							$prepare2 = DB::instance(intval($object->db))->prepare('UPDATE `'.$objectname.'` SET `'.$fieldname.'` = ? WHERE `id` = ?;');
							$prepare2->execute(array(json_encode($patched), $row->id));
							
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
		
		// create a Backup
		$backup = dirname($path).'/backup/'.time().'_'.$_GET['file'].'.php';
		copy($path, $backup);chmod($backup, 0777);
		
		// save the new JSON
		file_put_contents($path, '<?php exit;?>'."\n".$_POST['json']);
		
		echo 'ok ('.$updated.' Entries updated)';
				
		
		///////////////////////////////////////////////////////////////////////////////////////////
		
	break;
}
	
