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
session_start();
error_reporting(0);

// fix/sanitize GET-Parameter
foreach($_GET as $k=>$v){ $_GET[str_replace('amp;','',$k)] = preg_replace('/\W/', '', $v); }

$projects = glob('../projects/*', GLOB_ONLYDIR);

// if not needed you can delete the following 2 Redirects
if ( !file_exists('admin/super.php') ){ header('location: admin/_project_setup/superpw.php'); }// redirect to Superpassword-Input if not set
if ( count($projects) == 0 ){ header('location: admin/_project_setup/index.php'); }// redirect to Project-Setup if no project



if (isset($_GET['project']))
{
	$projectName = $_GET['project'];
	if(isset($_SESSION[$projectName]))
	{
		$_SESSION['logout'] = $projectName;
	}
	else
	{
		unset($_SESSION['logout']);
	}
	
	$_SESSION[$projectName] = null;
	unset($_SESSION[$projectName]);
	unset($_SESSION['SetupProjectName']);
}
else
{
	$projectName = '';
}

require('inc/php/functions.php');
$l = browserLang(glob('inc/locale/login/*.php'), 'en');
@include('inc/locale/login/'.$l.'.php');


?>
<!DOCTYPE html>
<html lang="<?php echo $l;?>">
<head>
<title>cms-kit login</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<meta name="robots" content="none" />

<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
<meta http-equiv="pragma" content="no-cache" />

<meta http-equiv="content-script-type" content="text/javascript">
<meta http-equiv="content-style-type" content="text/css">

<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">

<script src="inc/js/jquery.min.js"></script>

<link rel="icon" type="image/png" href="inc/css/icon.png" />
<style>
	body{font:90% "Trebuchet MS", sans-serif;}
	body, a{text-decoration:none;color:#000;}
	#form, #error, #msg{position:absolute;top:50%;left:50%;width:160px;margin:-80px 0px 0px -80px;}
	#error, #msg{margin-top:-170px;padding:5px;height:50px;font-weight:bold;border:2px solid;filter:Alpha(opacity=40);opacity:0.6;-moz-opacity:0.6;}
	#error{background:#fcc;color:#f00;border-color:#f00;}
	#msg{background:#fcc;color:#333;border-color:#ccc;display:none;}
	#msg span{cursor:pointer;}
	input, button, select {background:#fff;border:1px solid #333;padding:5px;margin:3px 0px;}
	input, button, #error, #msg {-moz-border-radius:5px;-webkit-border-radius:5px;-khtml-border-radius:5px;border-radius:5px;}
	button{cursor:pointer;}
	input[type=text], input[type=password], select {width:158px;}
	#captcha{position:absolute;height:30px;z-index:10;cursor:pointer;}
</style>

</head>
<body>
<noscript><h2><?php echo L('javascript_not_activated');?></h2></noscript>
<?php
	if(isset($_GET['error']) && L($_GET['error'])){ echo '<div id="error">'.L($_GET['error']).'</div>'; }
?>

<div id="msg"><img src="inc/login/spinner-mini.gif" /> please wait!<hr /></div>
<span id="head_right" style="float:right"><img src="inc/login/logo.png" /></span>

<form id="form" style="display:none" method="post" action="backend.php">
	<input type="hidden" id="lang" name="lang" value="<?php echo $l;?>" />
	<input type="hidden" id="template" name="template" value="0" />

<?php

echo '	'.L('title').'<br />';

if (count($projects)==1)
{
	echo '	<input type="hidden" name="project" value="'.basename($projects[0]).'" />';
}
else
{
	// you can decide wether to show a selectbox showing all your Projects OR a simple input-field

	/* Selectbox showing available Projects 
		echo '	<select name="project" id="project"><option value="">'.L('project_name').'</option>';
		foreach($projects as $p){ $n=basename($p);echo '		<option '.($n==$projectName?'selected="selected" ':'').'value="'.$n.'">'.L($n).'</option>'; }
		echo '	</select>';
	*/

	/* simple Input-Field */
	echo '
	<p>
		<label for="project">'.L('project_name').'</label>
		<input type="text" id="project" name="project" placeholder="'.L('project_name').'" value="'.$projectName.'" /><br />
	</p>';
	
}
?>
	<p>
		<label for="name"><?php echo L('user_name');?></label>
		<input type="text" id="name" name="name" placeholder="<?php echo L('user_name');?>" />
	</p>
	<p style="display:none" id="mailline">
		<label for="mail"><?php echo L('mail');?></label>
		<input type="text" id="mail" name="mail" placeholder="<?php echo L('mail');?>" />
	</p>
	<p id="passline">
		<label for="pass"><?php echo L('password');?></label>
		<input type="password" autocomplete="off" id="pass" name="pass" placeholder="<?php echo L('password');?>" />
	</p>
	
	<p>
		<button type="submit" title="login" style="float:right;margin-right:-10px;">
			<img src="inc/login/0_ok.png" />
		</button>
		<!-- Password-Reminder 
		<button type="button" title="<?php echo L('forgot_password');?>" onclick="setForgotten()">
			<img src="inc/login/0_mail.png" />
		</button>
		-->
		<button type="button" title="<?php echo L('bookmark');?>" onclick="setHash()">
			<img src="inc/login/0_bm.png" />
		</button>
	</p>
</form>

<img onclick="this.src='inc/php/captcha.php?x='+Math.random()" id="captcha" src="inc/login/blank.png" />

<script>
	
top.window.name = null;//
$('#form').show();//
var msgNo = 0;//


// activate "forgot-password"-Mode
function setForgotten() {
	var q = confirm('<?php echo L('to_request_access_enter_your_credentials');?>');
	if(q)
	{
		$('#passline').hide();
		$('#mailline').show();
		$('#form').attr('action', 'extensions/user/wizards/remember/index.php');
	}
}
// save username+password as bookmark-url
function setHash() {
	window.location = '?project='+$('#pname').val()+'#name='+$('#name').val()+'&pass='+$('#pass').val();
}

// show processing for Logout-Hooks
function msg(str) {
	var b = $('#msg');
	b.show();
	if(!str) return;
	b.html( b.html() + '<span title="'+str+'">('+msgNo+')</span> ');
	--msgNo;
	if(msgNo<=0) {
		b.style.backgroundColor= '#cfc';
		b.html('all Jobs done!<hr' + b.html().split('<hr').pop());
	}
}

// hide label-elements if placeholders are avalable
if('placeholder' in document.createElement('input')) {
	$('label').hide();
}


// "bookmarkable" credentials (Hashes are invisible for the Server!)
var h = window.location.hash.substr(1);
if(h.length>0) {
	var p = h.split('&');
	for(var i=0,j=p.length; i<j; ++i) {
		var a = p[i].split('='), el=$('#'+a[0]);
		if(el) el.val(a[1]);
	}
}else {// clear inputs if there is no hash
	window.setTimeout(function() {
		$('#pass').val('');
		$('#mail').val('');
	}, 1000);
}

// warn user if activated capsLock
$('#pass').keypress(function(e)
{
    var s = String.fromCharCode( e.which );
    if ( s.toUpperCase() === s && s.toLowerCase() !== s && !e.shiftKey ) {
        $('#msg').html('<?php echo L('capsLock_is_active');?>');
        $('#msg').show();
    }else {
		$('#msg').hide();
	}
});

// detect Touchscreen-Devices
if(('ontouchstart' in document.documentElement) || $('#template').val()==1) {
	$('#template').val('1');
}else {// fetch Background-Image for Desktop-Devices
	document.writeln('<script src="inc/js/jquery.backstretch.js"><\/script>');
	var now = new Date();
	document.writeln('<script src="inc/login/x_of_the_day.php?t='+now.getFullYear()+'_'+now.getMonth()+'_'+now.getDay()+'"><\/script>');
}

</script>

<?php 
	if(isset($_SESSION['logout'])) {
		@include '../projects/'.$_SESSION['logout'].'/ext/all/logout.php';
	}
?>

</body>
</html>
