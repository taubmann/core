<!DOCTYPE html>
<html lang="<?php echo $lang;?>">
<head>
<title><?php echo $projectName;?>-backend</title>
<meta charset="utf-8" />

<!-- prevent Browser-Caching -->
<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
<meta http-equiv="pragma" content="no-cache" />

<!-- tell the Browser what we mean with script-/style-Tags -->
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="content-style-type" content="text/css" />

<!-- prevent zoom-out -->
<meta name="viewport" content="width=device-width, initial-scale=1" /> 

<?php
	
	if ( !in_array('json', $_SESSION[$projectName]['client']['capabilities']) ) echo '<script src="inc/js/json2.min.js"></script>';
	
	echo '
<script>
	var projectName="'.$projectName.'", objectName='.($object?'"'.$object.'"':'false').', theme="'.end($_SESSION[$projectName]['config']['theme']).'", lang="'.$lang.'", langLabels={'.$jsLangLabels.'}, userId="'.$_SESSION[$projectName]['special']['user']['id'].'";
	var store=((top.window.name && top.window.name.substr(0,1)=="{") ? JSON.parse(top.window.name) : JSON.parse(\''.$_SESSION[$projectName]['settings'].'\'));
</script>
	';
?>

<link rel="icon" type="image/png" href="inc/css/icon.png" />
<link rel="stylesheet" type="text/css" id="mainTheme" href="inc/css/<?php echo end($_SESSION[$projectName]['config']['theme'])?>/jquery-ui.css" />
<link rel="stylesheet" type="text/css" id="baseTheme" href="templates/default/css/packed_<?php echo end($_SESSION[$projectName]['config']['theme'])?>.css" />

<!--[if lt IE 9]>
    <script src="inc/js/jquery1.min.js"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
    <script src="inc/js/jquery2.min.js"></script>
<!--<![endif]-->

<script src="inc/js/jquery-ui.js"></script>

<script src="templates/default/js/packed_<?php echo $lang;?>.js" ></script>

</head>
<body>

<!-- mini-overlay -->
<div id="dialog1"><div id="dialogb1"></div></div>

<!-- maxi-overlay -->
<div id="dialog2"><iframe id="dialogb2" style="border:0px none"></iframe></div>

<!-- status-messagebox -->
<div id="messagebox"></div>

<div id="iHead" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		
	<div id="iHeadRight" style="float:right">
		
		<?php


		if(isset($user_wizards))
		{
			$html = '
			<span>
			<select id="globalWizard" onchange="openGlobalWizard(this)">
				<option value=""> '.L('Wizards').' </option>
				<optgroup label="'.L('global_wizards').'">
				';
				foreach($user_wizards as $w)
				{
					$html .= '		<option value="'.$w['url'].'"> '.L($w['name']).' </option>';
				}
				$html .= '</optgroup>
				<optgroup id="objectWizards" label="'.L('object_wizards').'">
				</optgroup>
			</select>
			</span>';

			echo $html;
		}


		echo '<button type="button" id="logoutButton" rel="power" onclick="logout()">'.L('logout').'</button>';

		?>
		
	</div>

	<div id="iHeadLeft">
		<?php

		// draw Logo if available
		if(file_exists($ppath.'/objects/logo.png'))
		{
			echo '<img id="logo" style="height:27px;float:left;margin:0 10px 0 0;" src="'.$ppath.'/objects/logo.png" />';
		}


		// draw Object-Selector
		echo '<select id="objectSelect">'.
			'<option value="" data-htype=""> '.L('availabe_Objects')." </option>\n";

		foreach($objectOptions as $group => $arr)
		{
			echo '<optgroup label="'.(($group!='0')?' '.$group.'':'').'">';
			foreach($arr as $option)
			{
				$opt_state = ($option['name']==$object) ? ' selected="selected"' : '';
				if(substr($option['label'],0,1)!=='.') echo '	<option'.$opt_state.' value="'.$option['name'].'" data-htype="'.$option['htype'].'"> '.$option['label'].'</option>';
			}
			echo '</optgroup>';
		}
		echo '</select>';

		// draw Template-Selector if needed
		if ($object && count($_SESSION[$projectName]['templates'][$object]) > 1)
		{
			echo '<select id="templateSelect">'.'<option value="">'.L('availabe_Templates')."</option>\n";
			foreach($_SESSION[$projectName]['templates'][$object] as $templatename)
			{
				echo '<option value="'.$templatename.'">'.L($templatename).'</option>';
			}
			echo '</select>';
		}
		?>
	</div>
	
</div><!--iHead END-->

<div id="colLeft" class="ui-widget-content">
	<input type="text" class="sbox ui-corner-all" id="searchbox" placeholder="<?php echo L('search');?>" />
	<div id="colLeftb"> </div>
</div>

<div id="colMid" class="ui-widget-content">
	<form id="colMidb" onsubmit="return false"> </form>
</div>

<div id="colRight" class="ui-widget-content">
	<div id="colRightb"> </div>
</div>

<div class="wait"></div>
</body>
</html>

