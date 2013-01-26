<?php

$backend = '../../';

$projectName = preg_replace('/\W/', '', $_GET['project']);
$ppath = $backend . '../projects/' . strtolower($projectName);

if(!isset($_SESSION[$projectName]['root'])) exit('no Rights to edit!');

$lang = $_SESSION[$projectName]['lang'];
$LL = array();
@include dirname(__FILE__) . '/locale/'.$lang.'.php';

function L($str){
	global $LL;
	//file_put_contents('ll.txt', $str.PHP_EOL, FILE_APPEND);chmod('ll.txt',0777); // export all labels
	return ( isset($LL[$str]) ? $LL[$str] : str_replace('_',' ',$str) );
}

require $ppath . '/objects/__configuration.php';

// get embed-codes for wizards an hooks
$dirs = glob($backend.'wizards/*',	GLOB_ONLYDIR);
$dirs = array_merge($dirs,glob($backend.'extensions/*',	GLOB_ONLYDIR));
$dirs = array_merge($dirs,glob($ppath.'/extensions/*',	GLOB_ONLYDIR));

$embeds = array(	'w' => array(),
					'h' => array()
				);

// collect Informations from Extensions & Wizards
foreach($dirs as $dir)
{
	if(@$estr = file_get_contents($dir.'/doc/info.php'))
	{
		
		//
		$arr = explode('EOD', $estr);
		if(@$eson = json_decode($arr[1], true))
		{
			// fill wizard-embeds (type/wizard)
			if(isset($eson['system']['inputs']))
			{
				foreach($eson['system']['inputs'] as $i)
				{
					if(isset($eson['system']['include']))
					{
						if(!isset($embeds['w'][$i])) $embeds['w'][$i] = array();
						
						$embeds['w'][$i][] = 	str_replace(array(' ','-'),'_', $eson['info']['name']) . 
												':[\'' . implode('#OR#', $eson['system']['include']) . '\',\'' .
												(	
													(isset($eson['info']['description'][$lang])) ?
														$eson['info']['description'][$lang] :
														$eson['info']['description']['en']
												).
												'\']';
						
					}
				}
			}
			
			// fill hook-embeds
			if(isset($eson['system']['hooks']))
			{
				foreach($eson['system']['hooks'] as $k=>$i)
				{
					//if(!$embeds['h'][$k]) $embeds['h'][$k] = array();
					$i['name'] = $k;
					$embeds['h'][$k] = json_encode($i);
				}
			}
			
		}
	}
}// collect END
