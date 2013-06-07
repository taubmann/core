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
error_reporting(1);
session_regenerate_id();

// fix/sanitize GET-Parameter
foreach($_GET as $k=>$v){ $_GET[str_replace('amp;','',$k)] = preg_replace('/\W/', '', $v); }

$projects = glob('../projects/*', GLOB_ONLYDIR);

// if not needed you can delete the following 2 Redirects
if ( !file_exists('inc/super.php') ){ header('location: inc/php/setSuperpassword.php'); } // redirect to Superpassword-Input if not set
if ( count($projects) == 0 ){ header('location: admin/_project_setup/index.php'); }	// redirect to Project-Setup if no project

$logout = false;
if (isset($_GET['project']))
{
	$projectName = $_GET['project'];
	if(isset($_SESSION[$projectName]))
	{
		$logout = true;
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
<title><?php echo $projectName.' backend-login on '.$_SERVER['SERVER_NAME']?></title>
<meta charset="utf-8" />
<meta name="robots" content="none" />
<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="content-script-type" content="text/javascript">
<meta http-equiv="content-style-type" content="text/css">
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">

<!--[if lt IE 9]>
    <script src="inc/js/jquery1.min.js"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
    <script src="inc/js/jquery2.min.js"></script>
<!--<![endif]-->

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
	input, button, select, #error, #msg {border-radius:5px;}
	button{cursor:pointer;}
	button span{display:inline-block;width:16px;height:16px;background-image:url("inc/css/smoothness/images/ui-icons_222222_256x240.png");}
	input[type=text], input[type=password], select {-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;width:158px;}
	#captcha{position:absolute;height:30px;z-index:10;cursor:pointer;}
	#reset_button, #register_button{display:none}
	
</style>

</head>
<body>

<noscript>
	<h2>
		<?php echo L('javascript_not_activated');?>
	</h2>
</noscript>

<?php
	if (isset($_GET['error']) && L($_GET['error']))
	{
		echo '<div id="error">'.L($_GET['error']).'</div>';
	}
?>

<div id="msg">
	<img src="inc/login/spinner-mini.gif" /> <?php echo L('please_wait');?>
	<hr />
</div>

<span id="head_right" style="float:right">
	<img src="inc/login/logo.png" />
</span>

<form id="form" style="display:none" method="post" action="backend.php">
	<input type="hidden" id="lang" name="lang" value="<?php echo $l;?>" />
	<input type="hidden" id="client" name="client" value="" />

	<?php
	echo '	'.L('login').'<br />';
	// you can decide wether to show a selectbox showing all your Projects OR a simple input-field
	if (count($projects)==1)
	{
		echo '	<input type="hidden" id="project" name="project" value="'.basename($projects[0]).'" />';
	}
	else
	{
		/* Selectbox showing all available Projects */
		if (in_array($_SERVER['SERVER_NAME'], array('localhost','127.0.0.1')))
		{
			echo '	<select name="project" id="project"><option value="">'.L('project_name').'</option>';
			foreach($projects as $p){ $n=basename($p);echo '		<option '.($n==$projectName?'selected="selected" ':'').'value="'.$n.'">'.L($n).'</option>'; }
			echo '	</select>';
		}
		else
		{
			/* simple Input-Field */
			echo '
			<p>
				<label for="project">'.L('project_name').'</label>
				<input type="text" id="project" name="project" placeholder="'.L('project_name').'" value="'.$projectName.'" /><br />
			</p>';

		}

	}
	?>
	<p>
		<label for="name"><?php echo L('user_name');?></label>
		<input type="text" id="name" name="name" placeholder="<?php echo L('user_name');?>" />
	</p>
	<p id="passline">
		<label for="pass"><?php echo L('password');?></label>
		<input type="password" autocomplete="off" id="pass" name="pass" placeholder="<?php echo L('password');?>" />
	</p>
	
	<p>
		<button type="submit" title="login" style="float:right;margin-right:-10px;">
			<span style="background-position: -64px -144px"></span>
		</button>
		
		<button id="register_button" type="button" title="<?php echo L('register');?>">
			<span style="background-position: -144px -96px"></span>
		</button>
		
		<button id="reset_button" type="button" title="<?php echo L('forgot_password');?>">
			<span style="background-position: -80px -96px"></span>
		</button>
		
		<button id="sethash_button" type="button" title="<?php echo L('bookmark');?>">
			<span style="background-position: -224px -96px"></span>
		</button>
	</p>
	<p id="additional_buttons"></p>
</form>

<img id="captcha" src="inc/login/blank.png" />

<script type="text/javascript">

var msgNo = 0;//
var logout = <?php echo ($logout?'true':'false');?>;// 

// show processing for Logout-Hooks
function msg(str)
{
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

function loadProjectJs(name)
{
	if(name.length>2)
	{
		project = name;
		$.getScript('../projects/'+name+'/extensions/cms/login.js');
	}
}

$(document).ready(function()
{
	project = $('#project').val();// get the Project-Name if set by $_GET
	top.window.name = null;// clear the window.name (Storage for User-Settings)
	$('#form').show();// show the main Form (hidden if JS is deactivated)
	
	// hide Labels if html5-Placeholders are available
	if('placeholder' in document.createElement('input')){ $('label').hide() }
	
	// Listener to get a new Captcha-Image
	$('#captcha').on('click', function(){ $(this).attr('src', 'inc/php/captcha.php?x='+Math.random()) });
	
	// "bookmarkable" Credentials (Hashes are invisible for the Server)
	h = window.location.hash.substr(1);
	if(h.length>0)
	{
		var p = h.split('&');
		for(var i=0,j=p.length; i<j; ++i)
		{
			var a = p[i].split('='), el=$('#'+a[0]);
			if(el) el.val(a[1]);
		}
	}
	else// clear inputs if there is no hash
	{
		window.setTimeout(function()
		{
			$('#pass').val('');
			$('#mail').val('');
		}, 1000);
	}
	// Listener to save Credentials as Bookmark-Url
	$('#sethash_button').on('click', function(){ window.location = '?project='+$('#project').val()+'#name='+$('#name').val()+'&pass='+$('#pass').val() })

	// warn the User if capsLock is activated
	$('#pass').on('keypress', function(e)
	{
		var s = String.fromCharCode( e.which );
		if ( s.toUpperCase() === s && s.toLowerCase() !== s && !e.shiftKey )
		{
			$('#msg').html('<?php echo L('capsLock_is_active');?>');
			$('#msg').show();
		}
		else
		{
			$('#msg').hide();
		}
	});
	
	// (re)load the additional Functions of the Project
	$('#project').on('blur', function(){ loadProjectJs($(this).val()) });
	loadProjectJs(project);
	
	// detect Touchscreen-Devices
	if (('ontouchstart' in document.documentElement) || $('#template').val()==1)
	{
		$('#template').val('1');
	}
	else // fetch Background-Image for Desktop-Devices
	{
		$.getScript('inc/js/jquery.backstretch.js',function(){
			var now = new Date();
			$.getScript('inc/login/x_of_the_day.php?t='+now.getFullYear()+'_'+now.getMonth()+'_'+now.getDay());
		});
	}
});


</script>


</body>
</html>
