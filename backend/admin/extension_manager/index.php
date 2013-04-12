<?php
/********************************************************************************
*  Copyright notice
*
*  (c) 2013 Christoph Taubmann (info@cms-kit.org)
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

/* 
 * cms-kit Extension-Management
 * show/edit/create Documentations, Configurations and Files
 * 
 * */


require '../../inc/php/functions.php';
require 'inc/path.php';
require 'inc/functions.php';

$_SESSION['extensionedit'] = 1;



/**
* show available Extensions
*/

$pluginNames = array();

$infohtml = '';

$html = '
<select 
		style="width:100%;padding:7px;font-weight:bold;" 
		class="ui-widget ui-state-default ui-corner-all" 
		onchange="window.location.search=\'project='.$projectName.'&m=\'+this.value"
>';
foreach($mainpaths as $mk=>$mv)
{
	$html .= '<option value="'.$mk.'"'.((isset($_GET['m']) && $_GET['m']==$mk)?' selected="selected"':'').'>'.$mv[0].'</option>';
}
$html .= '</select>';

// Accordion BEGIN
$html .= '<div id="accordion">';

$html .= getExtensionList($projectName, $m, $mainpath);

/**
* show Extension-Information-Bar
*/

if(isset($_GET['ext'])) 
{
	
	$current_ext_path = $mainpath[2] . $_GET['ext'];
	
	// show current Extension-Info at top
	$infohtml .= getExtensionInfos($_GET['ext'], $mainpath, $current_ext_path);
	
	// Documentation Menu
	$html .= getDocList($lang, $current_ext_path);
	
	// Configuration Menu
	$html .= getConfigList($current_ext_path);
	
	
	$interfaces = array();
	$imp = array();
	
	// list all Files
	$html .= '<h3>'.L('CODE').'</h3>
	<div>';
	
		$html .= getFileList('php', $imp, $interfaces);
		$html .= getFileList('html', $imp, $interfaces);
		$html .= getFileList('css', $imp, $interfaces);
		$html .= getFileList('js', $imp, $interfaces);
		$html .= getFileList('xml', $imp, $interfaces);
		$html .= getFileList('sql', $imp, $interfaces);
	
	$html .= '</div>';
	
	// Wizards
	$html .= getWizardList($interfaces);
	
	// Imortable Files
	$html .= getImportList($imp);
	
	// Extension-Kickstarter
	if($m < 1 || $_SESSION[$projectName]['root']==2)
	{
		$html .= '<h3>Tools</h3>
		<div>';
		$html .= '<button  onclick="frameTo(\'kickstarter.php?project='.$projectName.'&m='.$m.'&ext='.(isset($_GET['ext'])?$_GET['ext']:'').'\')" type="button">Kickstarter</button>';
		
		$html .= '
		</div>';
	}
	
	
	
	$html .= '</div>';
	// Accordion END
	
	// load the Overview
	$html .= '<script>frameTo(\'showDirtree.php?project='.$projectName.'&m='.$m.'&ext='.(isset($_GET['ext'])?$_GET['ext']:'').'\')</script>';

} ////////////// if($_GET['ext']) END ////////////////////


?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>cms-kit Extension-Management</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="inc/styles/style.css" />
<link href="../../inc/css/<?php echo end($_SESSION[$projectName]['config']['theme'])?>/jquery-ui.css" rel="stylesheet" />
<script src="../../inc/js/jquery.min.js"></script>
<script>$.uiBackCompat = false;</script>
<script src="../../inc/js/jquery-ui.js"></script>

<script type="text/javascript"> 
/*<![CDATA[*//*---->*/

// load frame source
function setFrame(type, name, add)
{
	$('#frame').attr('src', type + '.php?m=<?php echo $m;?>&project=<?php echo $projectName;?>&ext=<?php echo $_GET['ext'];?>&file='+name+(add?add:'') );
}
//simply set a new frame-src
function frameTo(path)
{
	$('#frame').attr('src', path);
}

// load file-importer-script
function importFile(what, name)
{
	// security question
	var q = confirm('<?php echo L('import');?> '+what+'?');
	if(q) {
		var fileref = document.createElement('script');
		fileref.setAttribute("type","text/javascript");
		fileref.setAttribute("src",'inc/importer/import_'+ what+'.php?m=<?php echo $m;?>&project=<?php echo $projectName;?>&ext=<?php echo $_GET['ext'];?>&file='+name);
		document.getElementsByTagName("head")[0].appendChild(fileref);
	}
}

// (re)set the size of menu+frame
function setSize()
{
	var w = $(window).width(),
		h = $(window).height();
	$('#frame').width(w-260).height(h-50);
	$('#menu').height(h-50);
}

// prepare the UI
$(document).ready(function()
{
	setSize();
	$('#accordion').accordion({
		heightStyle: "fill"
	});
	$('#menu button').button();
	
});
// set resize-listener
$(window).resize(function() {
  setSize();
  $('#accordion').accordion('refresh');
});

/*--*//*]]>*/
</script>

</head>
<body>
<iframe id="frame" src="about:blank"></iframe>

<div id="menu">
<?php echo $infohtml .$html;?>

</div>

</body>
</html>
