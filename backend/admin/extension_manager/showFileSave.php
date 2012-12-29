<?php
if($_POST['content']) {
	require '../../inc/php/functions.php';
	require 'inc/path.php';
	$file = $mainpath[2] . $_GET['ext'] . '/' . $_GET['file'];
	if(file_exists($file)) {
		if(is_writable($file)) {
				file_put_contents($file, $_POST['content']);
				echo 'File saved';
		}
		
	}else{
		echo 'File "'.$file.'" not found!';
	}
}
?>
