<?php
/*
 * 
 * */
	session_start();
	$projectName = preg_replace('/[^-\w]/', '', $_GET['project']);
	if($_SESSION[$projectName]['root']!==2) exit('no Rights to edit!');
	
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
	This is a simple Linklist to several Development-Tools:
	</strong>
	<hr />
	<p><b>CSS-Packer</b> lets you concatenate and compress your Backend-CSS-Files</p>
	<p>
		<a href="css_pack.php?<?php echo $getP;?>">pack CSS</a> /
		<a href="css_pack.php?nocompress=1&<?php echo $getP;?>">concat CSS (uncompressed)</a>
	</p>
	<hr />
	<p><b>JS-Packer</b> lets you concatenate and compress your Backend-Javascript-Files. In addition some Labels within the JS-Files were translated. </p>
	<p>Available Languages: </p>
	<ul>
	<?php
	foreach ($langs as $l) {
		echo '<li>
		<a href="js_pack.php?lang='.$l.'&'.$getP.'">pack "'.strtoupper($l).'"</a> / 
		<a href="js_pack.php?nocompress=1&lang='.$l.'&'.$getP.'">concat "'.strtoupper($l).'" (uncompressed)</a>
		</li> ';
	}
	?>
	</ul>
	<hr />
	<p><a href="../../inc/login/index.php?<?php echo $getP;?>">Pic-of-the-Day - Editor</a> lets you add/remove Pictures shown on Login</p>
	<hr />
	Update <b>Language-Files</b> <br />
	By uploading a Language-ZIP-File, you can update/extend your Backend-Languages<br /><br />
	<form action="update_languages.php?<?php echo $getP;?>" method="post" enctype="multipart/form-data">
		<input type="file" name="langfile" /><br />
		<input type="submit" value="Upload Language-Package" />
	</form>
	
	
	<!--<p>
	Upload a new Wizard
	<form>
	<input type="file" name="wizard" />
	<input type="submit" value="upload Wizard" />
	</form>
	</p>-->
</body>
</html>
