<?php
/*
 * 
 * */
require 'header.php';
	
$getP = 'project='.$projectName;

// check for languages
$langs = array();
$lf = glob('../../inc/locale/*.php');
foreach($lf as $l) $langs[] = substr($l, -6, 2);
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>cms-kit Script-Manager</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<style>
	body{background: #eee;font:.9em "Trebuchet MS", sans-serif;}
	a, a:visited{text-decoration:none;color:blue;}
</style>
</head>
<body>
	<h2>Script Manager</h2>
	<strong>
	This is a Collection of several Development-Tools:
	</strong>
	<hr />
	
	<p><b>CSS-Packer</b> lets you concatenate and compress your Backend-CSS-Files</p>
	<ul>
		<li>
			<a href="compressors/css_pack.php?<?php echo $getP;?>">pack CSS</a> /
			<a href="compressors/css_pack.php?nocompress=1&<?php echo $getP;?>">concat CSS (uncompressed)</a>
		</li>
	</ul>
	<p>
	<a href="themeparams/index.php?<?php echo $getP;?>">Translate</a> CSS-Parameter of/for <a target="_blank" href="http://jqueryui.com/themeroller">JQueryUI-Themeroller</a>
	</p>
	<hr />
	
	<p><b>JS-Packer</b> lets you concatenate and compress your Backend-Javascript-Files. In addition some Labels within the JS-Files were translated. </p>
	<p>Available Languages: </p>
	<ul>
	<?php
	foreach ($langs as $l) {
		echo '<li>
		<a href="compressors/js_pack.php?lang='.$l.'&'.$getP.'">pack "'.strtoupper($l).'"</a> / 
		<a href="compressors/js_pack.php?nocompress=1&lang='.$l.'&'.$getP.'">concat "'.strtoupper($l).'" (uncompressed)</a>
		</li> ';
	}
	?>
	</ul>
	<hr />
	
	<p><a href="misc/show_checksums.php?<?php echo $getP;?>">Checksum-Test</a> lets you save a Checksum-Snapshot of your System or test against a previously saved Snapshot. This is useful for testing if someone has corrupted your system.</p>
	<hr />
	
	<p><a href="misc/pic_of_the_day.php?<?php echo $getP;?>">Pic of the Day Editor</a> lets you manage Pictures shown at Login</p>
	<hr />
	
	<p>
	Backend-Languages can updated/extended by uploading a Language-ZIP-File
	</p>
	<form action="misc/update_languages.php?<?php echo $getP;?>" method="post" enctype="multipart/form-data">
		<input type="file" name="langfile" /><br />
		<input type="submit" value="Upload Language-Package" />
	</form>
	<hr />
	
	
</body>
</html>
