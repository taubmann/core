<?php
/*
 * 
 * extract availabe Language-Files to their locations (if location exists and is writable)
 * */
session_start();
$projectName = preg_replace('/\W/', '', $_GET['project']);
if($_SESSION[$projectName]['root']!==2) exit('no Rights to edit!');

$backend = '../../';
$HTML = '';


// fix some access-problems of pclzip
function preExtractCallBack($p_event, &$p_header)
{
	global $HTML, $backend, $projectName;
	
	$p_header['filename'] = str_replace('%PROJECT%', $projectName, $p_header['filename']);
	$info = pathinfo($p_header['filename']);
	//print_r($info);
	
	
	if(!$info['extension'])// it's a folder => do nothing
	{
		return 0;
	}
	else // files inside *existing* folders are extracted ( return 1 )
	{
		if(file_exists($info['dirname']))
		{
			$dirname = substr($info['dirname'], strlen($backend));
			$filename = $dirname.DIRECTORY_SEPARATOR.$info['filename'].'.'.$info['extension'];
			if(is_writable($info['dirname']))
			{
				$HTML .= '<div class="grn">extracted "'.$filename.'"</div>';
				return 1;
			}
			else
			{
				$HTML .= '<div class="rd">could not extract "'.$filename.'" because "'.$dirname.'" is not writable!</div>';
				return 0;
			}
		}
		else
		{
			return 0;
		}
	}
}




if($_FILES['langfile'] && $_FILES['langfile']['name'] && array_pop(explode('.',strval($_FILES['langfile']['name'])))=='zip')
{
	$zipPath = $_FILES['langfile']['tmp_name'];
	
	require('../../inc/php/pclzip.lib.php');
	$archive = new PclZip($zipPath);
	
	if ($archive->extract(	
							PCLZIP_OPT_PATH, $backend,
							//PCLZIP_OPT_REMOVE_PATH, $tname,
							PCLZIP_CB_PRE_EXTRACT, 'preExtractCallBack',
							PCLZIP_OPT_SET_CHMOD, 0776
						) == 0)
	{
		exit('Unrecoverable error "' . $archive->errorName(true) . '"');
	}
}
else
{
	$HTML .= '<h2 class="rd">no valid Upload-File detected!</h2>';
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Processing Model</title>
<style>
	body {
		font: .8em "Trebuchet MS", sans-serif;
		margin: 10px;
		background:#ddd;
	}
	div {
		margin: 20px 0;
		background:#fff;
		border:1px solid #000;
		padding: 10px;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
		-khtml-border-radius: 5px;
		border-radius: 5px;
	}
	.bld {
		font-weight: bold;
	}
	.grn {
		color: green;
	}
	.rd {
		color: red;
	}
	.bl {
		color: blue;
	}
</style>
</head>

<body>
<a href="index.php?project=<?php echo $projectName;?>">back</a>
<hr />
<h2>extract Language-Files</h2>

<?php echo $HTML;?>

</body>
</html>
