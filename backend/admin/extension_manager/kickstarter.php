<?php
/********************************************************************************
*  Copyright notice
*
*  (c) 2012 Christoph Taubmann (info@cms-kit.org)
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


require '../../inc/php/functions.php';
require 'inc/path.php';

$ext_path = $mainpath[2] . $_GET['ext'];
$ext_path_len = strlen($ext_path);

if(	!$_SESSION[$projectName]['root'] || ($m>0 && $_SESSION[$projectName]['root']<2) )
{
	exit('no Rights to edit!');
}


$types = array('php','xml','js','html','css','md');

//$lang = $_SESSION[$projectName]['lang'];
//@include 'inc/locale/'.$_SESSION[$projectName]['lang'].'.php';

$gets = '?project='.$_GET['project'].'&m='.$m.'&ext='.$_GET['ext'];


// fix some access-problems of pclzip
function preExtractCallBack($p_event, &$p_header) {
	$info = pathinfo($p_header['filename']);//print_r($info);
	if(!$info['extension']) {
		$d = $info['dirname'].'/'.$info['filename'];
		mkdir($d);
		chmod($d, 0777);
		return 0;
	}
	else {// files are simply extracted
		return 1;
	}
}

function rrmdir($what)
{
	foreach(glob($what.'/*') as $file)
	{
		if(is_dir($file))
		{
			rrmdir($file);
		}
		else
		{
			@unlink($file);
		}
	}
}

$message = '';

if(isset($_POST))
{
	//path-security
	foreach($_POST as $k=>$v)
	{
		$_POST[$k] = str_replace('..','',$_POST[$k]);
	}
	
	if($_POST['folder_name'] && $_POST['main_dir'])
	{
		$all_folders = glob($ext_path . $_POST['main_dir'] . '*', GLOB_ONLYDIR | GLOB_NOSORT);
		$fn = array();
		foreach($all_folders as $f) $fn[] = basename($f);
		if(!in_array($_POST['folder_name'], $fn))
		{
			$d = $ext_path . $_POST['main_dir'] . $_POST['folder_name'];
			mkdir($d);
			chmod($d, 0777);
			
			$message = '<div class="success">'.$_POST['folder_name'].' '.L('created').'!</div>';
		}
		else
		{
			$message = '<div class="error">'.$_POST['folder_name'].' '.L('already_exists').'!</div>';
		}
	}// folder_name END
	
	if($_POST['file_name'] && $_POST['main_dir'] && $_POST['file_mime'])
	{
		$filename = $ext_path . $_POST['main_dir'] . $_POST['file_name'] . '.' . $_POST['file_mime'];
		if(!file_exists($filename))
		{
			@file_put_contents($filename, ' ');//needed one byte for further editing
			if(file_exists($filename))
			{
				chmod($filename, 0777);
				$message = '<div class="success">'.$_POST['file_name'].' '.L('created').'!</div>';
			}
			else
			{
				$message = $filename.'<div class="error">'.$_POST['file_name'].' '.L('could_not_be_created').'!</div>';

			}
		}
		else
		{
			$message = '<div class="error">'.$_POST['file_name'].' '.L('already_exists').'!</div>';
		}
	}// file_name END
	
	if($_POST['delete_it'] && $_POST['file_path'])
	{
		if(file_exists($ext_path . $_POST['file_path']))
		{
			if(unlink($ext_path . $_POST['file_path']))
			{
				$message = '<div class="success">'.$_POST['file_path'].' '.L('deleted').'!</div>';
			}
			else
			{
				$message = '<div class="error">'.$_POST['file_path'].' '.L('could_not_be_deleted').'!</div>';
			}
		}
	}
	
	if($_POST['extension_name'])
	{
		$all_exts = glob(dirname($ext_path) . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
		$en = array();
		foreach($all_exts as $e) $en[] = basename($e);
		if(!in_array($_POST['extension_name'], $en))
		{
			$d = dirname($ext_path) . '/' . $_POST['extension_name'];
			mkdir($d);
			chmod($d, 0777);
			
			// unzip the Extension-Sceleton
			require('../../inc/php/pclzip.lib.php');
			$archive = new PclZip('inc/dummy.zip');
			if ($archive->extract(	PCLZIP_OPT_PATH, $d,
									PCLZIP_CB_PRE_EXTRACT, 'preExtractCallBack',
									//PCLZIP_OPT_ADD_PATH, $ename,
									PCLZIP_OPT_SET_CHMOD, 0777
								 ) == 0)
			{
				exit('Unrecoverable error "'.$archive->errorName(true).'"');
			}
			else
			{
				$message = '<div class="success">'.$_POST['extension_name'].' '.L('created').'!</div>';
			}
			
		}
		else
		{
			$message = '<div class="error">'.$_POST['extension_name'].' '.L('already_exists').'!</div>';
		}
	}// extension_name END
	
	
	if($_FILES['extensionzip'] && 
	   $_FILES['extensionzip']['name'] && 
	   array_pop(explode('.',strval($_FILES['extensionzip']['name'])))=='zip') 
	{
		$ename = substr($_FILES['extensionzip']['name'],0,-4);
		if(!file_exists($ext_path . $ename))
		{
			require('../../inc/php/pclzip.lib.php');
			$archive = new PclZip($_FILES['extensionzip']['tmp_name']);
			if ($archive->extract(	PCLZIP_OPT_PATH, $ext_path,
									PCLZIP_CB_PRE_EXTRACT, 'preExtractCallBack',
									//PCLZIP_OPT_ADD_PATH, $ename,
									PCLZIP_OPT_SET_CHMOD, 0777
								 ) == 0) {
				exit('Unrecoverable error "'.$archive->errorName(true).'"');
			}else{
				
				$message = '<div class="success">'.L('Extension_extracted').'! <a href="index.php?'.$gets.'">'.L('show_it').'!</a></div>';
			}
		}
		else
		{
			$html .= '<div class="error">'.str_replace('%s', $ename, L('Extension_%s_already_exists')).'!</div>';
		}
	}
	
}




// scan extension-directory recursively
$dirs = array();
$fileList = array();
function rglob($pattern, $flags = 0, $path = '')
{
	
	global $dirs, $ext_path_len;
	$fn = basename($path);
	$dirs[] = substr($path, $ext_path_len);
	if(file_exists($path . '.nomedia') || file_exists($path . '.no' . substr($pattern, 2)) || $fn=='doc' || $fn=='config') return array();
	
	if (!$path && ($dir = dirname($pattern)) != '.')
	{
		if ($dir == '\\' || $dir == '/') $dir = '';
		return rglob(basename($pattern), $flags, $dir . '/');
	}
	$paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
	$files = glob($path . $pattern, $flags);
	foreach ($paths as $p) $files = array_merge($files, rglob($pattern, $flags, $p . '/'));
	return $files;
}


$tsel = '<select name="file_mime"><option value="">'.L('select_file_type').'</option>';
foreach($types as $type)
{
	$tsel .= '<option value="'.$type.'">'.$type.'</option>';
	$fileList = array_merge($fileList, rglob('*.'.$type, GLOB_MARK, $ext_path.'/'));
}
$tsel .= '</select>';



$dirs = array_unique($dirs);
$dsel = '<select name="main_dir"><option value="">'.L('select_directory').'</option>';
foreach($dirs as $dir)
{
	$dsel .= '<option '.(is_writable($ext_path . $dir)?'':' style="color:#ccc"').'value="'.$dir.'">'.$dir.'</option>';
}
$dsel .= '</select>';

$fsel = '<select name="file_path"><option value="">'.L('select_file').'</option>';
foreach($fileList as $filepath)
{
	$f = substr($filepath,$ext_path_len);
	$fsel .= '<option '.(is_writable($filepath)?'':' style="color:#ccc"').'value="'.$f.'">'.$f.'</option>';
}
$fsel .= '</select>';

$form_action = 'kickstarter.php' . $gets;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>cms-kit Extension-Kickstarter</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="inc/styles/style.css" />
<link href="../../inc/css/<?php echo end($_SESSION[$projectName]['config']['theme'])?>/jquery-ui.css" rel="stylesheet" />
<script src="../../inc/js/jquery.min.js"></script>
<script>$.uiBackCompat = false;</script>
<script src="../../inc/js/jquery-ui.js"></script>

<style>
#wrapper {
	width: 500px;
	
}
#wrapper input, #wrapper select{
	font-size: 2em;
}

</style>

</head>
<body>

<div id="wrapper">


<?php

echo '<h1>'.L('Project').' "'.$_GET['project'].'"</h1>';

echo $message;

if($_GET['ext'])
{
	
echo '<h2>'.L('Extension').' "'.$_GET['ext'].'"</h2>


<form action="'.$form_action.'" method="post">
	<h2>'.L('create_new_Folder_in').' "'.$_GET['ext'].'"</h2>
	'.$dsel.'
	<input type="text" name="folder_name" placeholder="'.L('type_Folder_Name').'" />
	<input type="submit" value="'.L('create').'" />
</form>

<form action="'.$form_action.'" method="post">
	<h2>'.L('create_new_empty_File_in').' "'.$_GET['ext'].'"</h2>
	<?php echo $dsel;?>
	<input type="text" name="file_name" placeholder="'.L('type_File_Name').'" />
	'.$tsel.'
	<input type="submit" value="'.L('create').'" />
</form>

<form id="del_form" action="'.$form_action.'" method="post">
	<h2>'.L('delete_File_in').' "'.$_GET['ext'].'"</h2>
	'.$fsel.'
	<input type="hidden" name="delete_it" value="1" />
	<input type="button" onclick="calldelete()" value="'.L('delete_it').'" />
</form>

';
}

if(!$_GET['m'] < 1 || $_SESSION[$projectName]['root']==2)
{
echo '
<hr />

<form action="'.$form_action.'" method="post">
	<h2>'.L('create_new_Extension').'</h2>
	<input type="text" name="extension_name" placeholder="'.L('name').'" />
	<input type="submit" value="'.L('create').'" />
</form>

<form action="'.$form_action.'" method="post" enctype="multipart/form-data">
	<h2>'.L('upload_new_Extension').'</h2>
	<input type="file" name="extensionzip" />
	<input type="submit" value="'.L('upload').'" />
</form>
';
}
?>
</div>


<script type="text/javascript"> 
/*<![CDATA[*//*---->*/

function calldelete()
{
	var q = confirm("<?php echo L('really_delete');?>?");
	if(q)
	{
		document.getElementById('del_form').submit();
	}
}

$('input[type=button], input[type=submit]').button();
$('select').addClass('ui-widget ui-state-default ui-corner-all').css('padding','7px');
/*--*//*]]>*/
</script>
</body>
</html>
