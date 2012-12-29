<?php
session_start();
$projectName = preg_replace('/\W/', '', $_GET['project']);

if(!isset($_SESSION[$projectName]['root'])) exit('no Rights to edit!');

$xml_path = '../../../projects/' . $projectName . '/objects/__modelxml.php';


if(isset($_POST['xml']))
{
$str0 = '<?php
$model = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
';
$str1 = '
EOD;
?>
';
	file_put_contents($xml_path, $str0 . stripcslashes($_POST['xml']) . $str1);
	@chmod($xml_path, 0776);
	echo 'saved';
}
else 
{
	include $xml_path;
	header ("Content-Type:text/xml");
	echo $model;
}

?>
