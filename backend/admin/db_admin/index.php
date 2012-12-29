<?php
/*
 * simple Redirection with GET-Params to adminer.php
 */
session_start();
$projectName = @preg_replace('/[^a-z0-9_]/si', '', strtolower($_GET['project']));
if(!isset($_SESSION[$projectName]['root'])) exit('you are not allowed to access this Service!');

$ppath = '../../../projects/' . $projectName;
require($ppath . '/objects/__configuration.php');

/* create the Target for Links/Redirection
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
<meta charset="utf-8" />
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
button{
	/*background:#fff;
	border: 1px solid #ccc;
	padding: 5px;*/
	margin-top: 10px;
	width: 200px;
	cursor: pointer;
}
</style>

</head>
<body>
<div id="wrapper">
		
<?php



$html = '<h4>choose Database</h4>

';

$cnt = count(Configuration::$DB_TYPE);

for($i=0; $i<$cnt; $i++) 
{
	//$html .= '<p><a href="'.mkLink($i).'">'.Configuration::$DB_ALIAS[$i]."</a></p>\n";
	$html .= '<form method="post" target="_blank" action="adminer.php">';
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

$html .= '
';

echo $html;


?>

</div>
</body>
</html>
