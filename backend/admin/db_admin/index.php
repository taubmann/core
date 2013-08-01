<?php
/**
* 
* simple Inclusion/Redirection of/to adminer.php
*/
session_start();
$projectName = preg_replace('/[^-\w]/', '', strtolower($_GET['project']));

$level = ((substr(basename(dirname(__FILE__)),0,1)!=='_') ? 1 : 2);
if (!isset($_SESSION[$projectName]['root'])  || !$_SESSION[$projectName]['root'] >= $level) exit('you are not allowed to access this Service!');

$ppath = '../../../projects/' . $projectName;
require($ppath . '/objects/__configuration.php');
$cnt = count(Configuration::$DB_TYPE);



/** 
* create the Target for Links/Redirection
* 
* 
*/

function createHiddenFields($i)
{
	global $ppath;
	$html = '';
	switch(Configuration::$DB_TYPE[$i])
	{
		case 'sqlite':
			$html .= '<input name="auth[driver]" type="hidden" value="sqlite" />';
			$html .= '<input name="auth[db]" type="hidden" value="'.realpath($ppath.'/objects/'.Configuration::$DB_DATABASE[$i]).'" />';
		break;
		
		case 'mysql':
			$html .= '<input name="auth[driver]" type="hidden" value="server" />';
			$html .= '<input name="auth[server]" type="hidden" value="'.Configuration::$DB_HOST[$i].'" />';
			$html .= '<input name="auth[username]" type="hidden" value="'.Configuration::$DB_USER[$i].'" />';
			$html .= '<input name="auth[password]" type="hidden" value="'.Configuration::$DB_PASSWORD[$i].'" />';
			$html .= '<input name="auth[db]" type="hidden" value="'.Configuration::$DB_DATABASE[$i].'" />';
		break;
	}
	
	return $html;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Database-Admin</title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
<link href="../../inc/css/<?php echo end($_SESSION[$projectName]['config']['theme'])?>/jquery-ui.css" rel="stylesheet" />

<style>
body{
	font: .8em sans-serif;
}
#wrapper {
	position: absolute;
	padding: 10px;
	border: 1px solid #ccc;
	top: 50%;
	left: 50%;
	width: 200px;
	margin: -200px 0 0 -110px;
}
button {
	/*background:#fff;
	border: 1px solid #ccc;
	padding: 5px;*/
	margin-top: 10px;
	width: 200px;
	cursor: pointer;
}
#framebreak {
	float: right;
	display: none;
}

</style>

</head>
<body>
<button 
	id="framebreak"
	class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" 
	title="open in new Window"
	type="button" role="button" aria-disabled="false"
	onclick="window.open(document.location, document.title)"
	>
	<span class="ui-button-icon-primary ui-icon ui-icon-newwin"></span>
	<span class="ui-button-text"></span>
</button>

<div id="wrapper">
		
<?php

$html = '<h4>choose Database</h4>

';

for($i=0; $i<$cnt; $i++) 
{
	$html .= '<form id="frm" method="post" action="dbm.php">';
	
	$html .= createHiddenFields($i);
	$html .= '<button 
				class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" 
				type="submit"
				role="submit" 
				aria-disabled="false">
				<span class="ui-button-icon-primary ui-icon ui-icon-calculator"></span>
				<span class="ui-button-text">'.Configuration::$DB_ALIAS[$i].'</span>
			</button>
			</form>';
}

if($cnt == 1)
{
	//$html .= '<script>document.getElementById("frm").submit()</script>';
}

$html .= '<p><a href="importXml.php?project='.$projectName.'">XML-Import</a></p>';

echo $html;


?>

</div>
<script>
if (top != self){ document.getElementById('framebreak').style.display='inline-block' }
</script>
</body>
</html>
