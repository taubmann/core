<?php
/**
* Simple (and therefore readable) Script to check the Integrity of all your Files & Folders 
* in Backend-Folder or your Project-Folder
* 
* In Case of a possible Attack:
* make sure that this File has not been manipulated by checking "header.php" and this script or by replacing the whole Folder "admin/_script_manager" with a fresh one (it has no dependencies)!
* 
* 
*/

// include Session-Protection
require '../header.php';

// we don't want to look inside these Folders (adapt it to your needs)
$excludedFileFolders = array('.git');

////////////////////////////////////////////////////////////////////////
$mypath = $backend;
$get = '?project='.$projectName;

// switch the Base-Path to Project-Folder
if (isset($_GET['testmyproject']))
{
	$mypath = dirname(dirname($mypath)).'/projects/'.$projectName.'/';
	$get .= '&testmyproject=1';
}

$path = realpath($mypath);
$offset = strlen($path);

$dirs = array();
$sums = false;
$size = 0;
$errors = '';

// if a POST-String is detected, we have to test against a this String
if (isset($_POST['checksums']))
{
	$lines = explode("\n", $_POST['checksums']);
	$sums = array();
	foreach ($lines as $line)
	{
		$line = explode('|', $line);
		
		if (isset($line[1]) && strlen(trim($line[1])) == 32)
		{
			$sums[trim($line[0])] = trim($line[1]);
		}
	}
	//print_r($sums);
}

/**
* simple Function to format given Bytes
* 
* @param int Bytes
* @return string formatted String
*/
function formatBytes ($size, $precision = 2)
{
    $base = log($size) / log(1024);
    $suffixes = array('', ' k', ' M', ' G', ' T');   

    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}

/**
* recursively collect all Files/Folders with their MD5-Checksums
* add them to a global Variable $dirs
* 
* @param string Directory-Path
* @return string MD5 Checksum
*/
function collect ($dir)
{
	global $errors, $offset, $dirs, $size, $excludedFileFolders;
	
	if (!is_dir($dir))
	{
		return false;
	}
	if (!is_readable($dir))
	{
		$errors .= "WARNING: " . $dir . " IS NOT READABLE!\n";
		return false;
	}
   
	$md5s = array();
	$handler = dir($dir);

	while (false !== ($entry = $handler->read()))
	{
		if ($entry != '.' && $entry != '..' && !in_array($entry, $excludedFileFolders))
		{
			$subpath = $dir . DIRECTORY_SEPARATOR . $entry;
			$key = substr($subpath, $offset);
			if (is_dir($subpath))
			{
				
				$md5s[] = $dirs[$key] = collect($subpath);
			}
			else
			{
				$md5s[] = $dirs[$key] = md5_file($subpath);
				$size += filesize($subpath);
			}
			 
		 }
	}
	$handler->close();
	return md5(implode('', $md5s));
}

// call the Function
$x = collect($path);
// sort the global Array by its Keys
ksort($dirs);

?>
<!DOCTYPE html>
<head lang="en">
<title>show/check Checksums</title>
<meta charset="utf-8" />
<style>
	body{ font: .8em sans-serif; }
	textarea{ width: 100%; height: 400px; }
	.r{ color: #c00; }
	.g{ color: #0c0; }
	.b{ color: #00c; }
	.n{ color: #ccc; }
</style>
</head>
<body>

<?php

echo '<p>
<a href="../index.php?project='.$projectName.'">&lArr; back</a> / 
<a href="show_checksums.php?project='.$projectName.'">test Backend-Folder</a> / 
<a href="show_checksums.php?project='.$projectName.'&testmyproject=1">test Project-Folder "'.$projectName.'"</a>
</p>';

// we have to check against this Array
if ($sums)
{
	$changed = false;
	echo '<h3>Test Checksums against input</h3>';
	echo '<p><a href="show_checksums.php'.$get.'">show actual Checksums</a></p>';
	echo '<div>';
	foreach ($dirs as $k => $v)
	{
		if (!isset($sums[$k]))
		{
			echo 		'<div class="b">"' . $k . '" added' . 
						(is_file($path.$k)?' (' . date('d.m.Y H:i:s', filemtime($path.$k)) . ')' : '')  . 
						"</div>\n";
			$changed = true;
		}
		else
		{
			if ($sums[$k] != $v)
			{
				echo 	'<div class="r">"' .$k . '" changed' . 
						(is_file($path.$k)?' (' . date('d.m.Y H:i:s', filemtime($path.$k)) . ')' : '')  . 
						"</div>\n";
				$changed = true;
			}
			else
			{
				// show all unchanged Files/Folders
				// echo	'<div class="n">"' .$k . '" unchanged</div>';
			}
		}
	}
	
	if (!$changed)
	{
		echo '<b class="g">nothing changed!</b>';
	}
	
	echo '</div>';
}
// we only have to show a Snapshot of Checksums in a Textarea
else
{
	echo '<h3>show actual Checksums</h3>
	<div>Size: '.formatBytes($size).'</div>
	<form method="post" action="show_checksums.php'.$get.'">
	<textarea spellcheck="false" name="checksums">' . $errors;
	
	foreach ($dirs as $k => $v)
	{
		echo $k . ' | ' . $v . "\n";
	}
	
	echo '
	</textarea>
	<input type="submit" value="check" />
	</form>
	<p>save this List or paste a previously saved List and check against your Installation!</p>';
}


?>

</body>
</html>
