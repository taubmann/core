<?php



// recursive glob
// http://snipplr.com/view.php?codeview&id=16233 // $fileList = rglob("*", GLOB_MARK, '/path/to/dir');
function rglob($pattern, $flags = 0, $path = '') {
	
	$fn = basename($path);
	if(file_exists($path . '.nomedia') || file_exists($path . '.no' . substr($pattern, 2)) || $fn=='doc' || $fn=='config') return array();
	
	if (!$path && ($dir = dirname($pattern)) != '.') {
		if ($dir == '\\' || $dir == '/') $dir = '';
		return rglob(basename($pattern), $flags, $dir . '/');
	}
	$paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
	$files = glob($path . $pattern, $flags);
	foreach ($paths as $p) $files = array_merge($files, rglob($pattern, $flags, $p . '/'));
	return $files;
}

function createAlertIcon($str)
{
	global $html;
	$html .= '<a href="#"><img src="inc/styles/warning.png" /><span>'.$str.'</span></a>';
}

function getExtensionList($projectName, $m, $mainpath)
{
	$dirs = glob($mainpath[2].'*', GLOB_ONLYDIR);
	$html = '<h3>'.L('Extensions').'</h3>
	<div>
	';

	foreach($dirs as $dir)
	{
		//
		if($_SESSION[$projectName]['root']==2 || !file_exists($dir.'/.superadmin'))
		{
			$n = basename($dir);
			$pluginNames[] = $n;
			$highlight = (isset($_GET['ext']) && $_GET['ext']==$n) ? ' style="font-weight:bold" ' : '';
			$html .= '<button '.$highlight.'type="button" onclick="location=\'?m=' . $m . '&project=' . $projectName . '&ext=' . $n . '\'">' . str_replace('_',' ',$n) . '</button>';
		}
	}
	$html .= '</div>';
	return $html;
}


function getExtensionInfos($extname,$mainpath, $current_ext_path)
{
	$html = '<span class="ttips">
	<span style="font-size:1.5em;font-weight:bold;vertical-align:top;">'.str_replace('_',' ',$extname).'</span> ';
	// try to get basic infos from info.php
	if ($jstr = @file_get_contents($current_ext_path . '/doc/info.php'))
	{
		$jarr = explode('EOD', $jstr);
		if($infoson = json_decode($jarr[1], true))
		{
			$status = ( ($infoson['system']['version']>1) ? 'stable' : ($infoson['system']['version']>0.5 ? 'beta' : ($infoson['system']['version']>0 ?'alpha' : 'unproductive')) );
			$html .= 	'<a href="#"><img src="inc/styles/status_'.$status.'.png" />
							<span>
							Version: '.$infoson['system']['version'].' ('.$status.')<br />Created by: '.implode(', ',$infoson['info']['authors']).'<br />Web: '.$infoson['info']['homepage'].'
							</span>
						</a>';
			
			// check installation-path if necessary
			if($infoson['system']['install_to'] && $infoson['system']['install_to'] !== $mainpath[1])
			{
				createAlertIcon('your installation-path should be: '.$infoson['system']['install_to']);
			}
			
			// check availibility of wizards + other extensions
			foreach(array('wizards','extensions') as $what)
			{
				if(isset($infoson['system']['requirements'][$what]))
				{
					foreach($infoson['system']['requirements'][$what] as $wn => $wno)
					{
						if($wstr = file_get_contents('../../wizards/'.$wn.'/doc/info.php'))
						{
							$warr = explode('EOD', $wstr);
							$wson = json_decode($warr[1], true);
							if($wson['system']['version'] < $wno) createAlertIcon($wn.' is too old!');
						}
						else
						{
							createAlertIcon($wn.' is not available');
						}
					}
				}
			}
		}
	}
	else
	{
		$html .= '<img title="no Informations available!!" src="inc/styles/warning.png" />';
	}
	$html .= '</span>';
	
	return $html;
}

function getDocList($lang, $current_ext_path)
{
	$html = '';
	$path = $current_ext_path.'/doc';
	$in_lang = '';
	if(file_exists($current_ext_path.'/doc/'.$lang))
	{
		$path = $current_ext_path.'/doc/'.$lang;
		$in_lang = ' ('.$lang.') ';
	}
	// default Language "en"
	else
	{
		$path = $current_ext_path.'/doc/en';
		$in_lang = ' (en) ';
	}
	
	$docs = glob($path.'/{*.md,*.html}', GLOB_BRACE);
	if(count($docs)>0)
	{
		
		
		$list = array();
		foreach($docs as $doc)
		{
			//$n = basename($n,	'.txt');
			$n = basename($doc,	'.md');
			$n = basename($n,	'.html');
			
			$add = ",'&edit_me=".substr($doc, strlen($current_ext_path)+1)."'";
			
			// dont show internal/hidden Files beginning with "."
			if( substr($n, 0, 1) != '.')
			{
				$list[] = 	'<button type="button" onclick="setFrame(\'showDoc\',\''.$doc.'\''.$add.')">' .
							preg_replace(array('/^[0-9]+/','/_/'), array('',' '), $n) . // replace Numbers at the Beginning and Underscores
							//$n .
							'</button>';
			}
		}
		
		if(count($list)>0)
		{
			$html .= '<h3>'.L('Docs').' ('.count($docs).')'.$in_lang.'</h3><div>' .
			implode('', $list) .
			'</div>';
		} 
		
	}
	return $html;
}

function getConfigList($current_ext_path)
{
	$html = '';
	$configs = glob($current_ext_path.'/config/*.php');
	if(count($configs) > 0)
	{
		$html .= '<h3>'.L('Configuration').' ('.count($configs).')</h3>
		<div>';
		foreach($configs as $config){
			$n = basename($config, '.php');
			$html .= '<button type="button" onclick="setFrame(\'showConfig\',\''.$n.'\')">'.str_replace('_',' ',$n).'</button>';
		}
		$html .= '</div>';
	}
	return $html;
}


function getFileList($type, &$imp, &$interfaces)
{
	global $projectName, $current_ext_path, $interfaces;
	$interfaces[$type] = array();
	$fileList = rglob('*.'.$type, GLOB_MARK, $current_ext_path.'/');
	
	if(count($fileList)==0) return '';
	
	$importFiles = array(	
							'model.xml'=>'model', 
							'dumps.sql'=>'sql', 
							'hooks.php'=>'hooks'
						);
	$c = 0;
	$ihtml = '';
	$html = '';
	foreach($fileList as $file)
	{
		$n = substr($file, (strlen($current_ext_path)+1));// shortened pathname
		$fn = basename($n);// filename without path
		$fc = substr($fn, 0, 1);
		if($fc != '_')
		{
			if($fc != '.')
			{
				// 
				$ihtml .= '<button type="button" onclick="setFrame(\'showFile\',\''.$n.'\')">'.$n.'</button>';
				
				// we try to collect importable Files as well
				$sn = substr($n, -9);
				if(isset($importFiles[$sn])) {
					$imp[] = '<button type="button" onclick="importFile(\''.$importFiles[$sn].'\',\''.$n.'\')">'.$n.'</button>';
				}
				$c++;
			}
		}
		else // we assume, this is a wizard
		{
			$interfaces[$type][] = '<button type="button" onclick="frameTo(\''.$file.'?project='.$projectName.'\')">'.str_replace('_',' ',array_shift(explode('.',substr($fn,1)))).'</button>';
		}
		
	}
	// there are Files with this Type =>
	if($c>0)
	{
		$html = '<h4>'.strtoupper($type).' ('.$c.')</h4>'.$ihtml.'';
	}
	
	return $html;
}
function getWizardList($interfaces)
{
	$html = '';
	$ints = array_merge($interfaces['html'], $interfaces['php']);
	if(count($ints)>0)
	{
		
		$html .= '<h3>'.L('WIZARDS').'</h3>
		<div>';
		// array(name,path)
		foreach($ints as $int)
		{
			$html .= $int;
		}
		$html .= '
		</div>
		';
	}
	return $html;
}

function getImportList($imp)
{
	$html = '';
	if(count($imp) > 0)
	{
		$html .= '<h3>'.L('IMPORT').'</h3>
		<div>';
		foreach($imp as $i)
		{
			$html .= $i;
		}
		$html .= "</div>\n";
	}
	return $html;
}
