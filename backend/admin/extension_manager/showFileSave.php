<?php
if($_POST['content']) {
	require '../../inc/php/functions.php';
	require 'inc/path.php';
	$file = $mainpath[2] . $_GET['ext'] . '/' . $_GET['file'];
	if(file_exists($file)) {
		if(is_writable($file)) {
				file_put_contents($file, $_POST['content']);
				echo '<b style="color:green">File saved</b>';
		}else{
			echo '<b style="color:red">File is not writable</b>';
		}
		
	}else{
		echo '<b style="color:red">File "'.$file.'" not found!</b>';
	}
}
?>
