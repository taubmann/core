<!DOCTYPE html>
<html lang="<?php echo $lang;?>">
<head>
<title>cms-kit-backend: <?php echo $projectName;?></title>
<meta charset="utf-8" />

<meta name="robots" content="none" />

<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
<meta http-equiv="pragma" content="no-cache" />
<meta name="viewport" content="width=device-width, initial-scale=1"> 

<meta http-equiv="content-script-type" content="text/javascript">
<meta http-equiv="content-style-type" content="text/css">

<script>if(!window.JSON){document.writeln('<script src="inc/js/json2.min.js"><\/script>')}</script>

<script>
	<?php echo 'var  lang="'.$lang.'", langLabels={'.$jsLangLabels.'}, userId="'.$_SESSION[$projectName]['special']['user']['id'].'", projectName="'.$projectName.'";';?>

	<?php echo 'var store=((top.window.name && top.window.name.substr(0,1)=="{") ? JSON.parse(top.window.name) : JSON.parse(\''.$_SESSION[$projectName]['settings'].'\'));';?>
	
	if(store['lastPage']){ window.location.hash=store['lastPage'] }
	
</script>

<link rel="stylesheet" type="text/css" id="mainTheme" href="inc/css/<?php echo end($_SESSION[$projectName]['config']['theme'])?>/jquery-ui.css" />
<link rel="stylesheet" type="text/css" id="baseTheme" href="inc/css/<?php echo end($_SESSION[$projectName]['config']['theme'])?>/style.css" />

<link rel="stylesheet" type="text/css" href="inc/css/mobiscroll.min.css" />

<link rel="icon" type="image/png" href="inc/css/icon.png" />

<script src="inc/js/jquery.min.js"></script>
<script src="inc/js/jquery-ui.js"></script>

<script src="inc/locale/<?php echo $lang;?>1.js" ></script>

<style>
@import url('inc/css/simplify.css');
body{font-size: 101%;}
#colHome, #colLeft, #colMid, #colRight {position: none;width: auto;}
#mainlist2 li a{margin-right: 10px;}
#mainlist li, #mainlist2 li {min-height: 25px;padding: 5px;}
.list li{padding-top: 20px;}
.list li a, .ui-selectee{padding: 10px;}
.list .ui-icon{display: inline-block;}

#wrapper .ui-tabs-nav .ui-icon {display: inline-block;}
#dialog1, #dialog2 {position:absolute;background:#eee;}
#dialog1 {width:200px;top:20%;left:50%;margin-left:-150px;z-index:501;}

#relSel select{font-weight: bold;}
#referenceSelect option {padding:10px;}

/* hide wizard-buttons (wizards not supporting mobile-devices) */
.wz_wysiwyg
	{display:none}



</style>
</head>
<body id="body">
	
<!-- mini-overlay -->
<div id="dialog1" style="display:none">
	<button style="float:right" onclick="$('#dialog1').hide();$('#dialogb1').html('')" rel="closethick">.</button>
	<div id="dialogb1"></div>
</div>

<!-- maxi-overlay -->
<div id="dialog2" style="display:none">
	<button style="float:right" onclick="$('#dialog2').hide();$('#dialogb2').attr('src','about:blank')" rel="closethick">.</button>
	<iframe id="dialogb2" style="clear:right;width:100%;border:0px none"></iframe>
</div>

<!-- status-messagebox -->
<div id="messagebox"></div>

<div id="wrapper">
<ul id="tabUl">
	<li style="float:right"><a id="lbl_wizard" href="#wizard" title="wizards"><span class="ui-icon ui-icon-gear"></span></a></li>
	<li><a id="lbl_home" href="#home">Bereiche</a></li>
	<li><a id="lbl_left" href="#left">&nbsp;</a></li>
	<li><a id="lbl_mid" href="#mid">&nbsp;</a></li>
	<li><a id="lbl_right" href="#right">&nbsp;</a></li>
</ul>
<div id="wizard">
<ul class="list">
<li><a href="javascript:logout()"><span class="ui-icon ui-icon-power"></span> Logout</a></li>
<?php
if(isset($user_wizards))
{
	//$html = '<span><select id="globalWizard" onchange="openGlobalWizard(this)"><option value="">'.L('User_Wizards').'</option>';
	foreach($user_wizards as $w)
	{
		echo '<li><a href="javascript:getFrame(template(\''.$w['url'].'\',window))"><span class="ui-icon ui-icon-gear"></span> '.$w['label'].'</a></li>';
	}
}

?>
</ul>
<ul id="objectWizards" class="list"></ul>
</div>
<div id="home">
	<ul class="list">
	<?php
	foreach($objectOptions as $option)
	{
		echo '<li><a id="object_'.$option['name'].'" data-label="'.$option['label'].'" data-htype="'.$option['htype'].'" data-fields="'.$option['addField'].'" href="javascript:selectObject(\''.$option['name'].'\',\''.$option['label'].'\',\''.$option['htype'].'\',\''.$option['addField'].'\')">'.$option['label'].'</a></li>';
	}
	?>	
	</ul>
</div>

<div id="left">
	<input type="text" class="sbox ui-corner-all" id="searchbox" placeholder="<?php echo L('search');?>" />
	<div id="colLeftb"> </div>
</div>

<div id="mid">
	<form id="colMidb" class="list" onsubmit="return false"> </form>
	<div id="relSel" class="list" style="margin-top:20px;"></div>
	<div id="actRel"></div>
</div>

<div id="right">
	<div id="colRightb"> </div>
</div>



<script>

</script>
</body>
</html>

