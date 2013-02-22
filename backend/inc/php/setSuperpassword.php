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
$styles = glob('../css/*', GLOB_ONLYDIR);
$topt = '';
foreach($styles as $style)
{
	if(file_exists($style.'/preview.png'))
	{
		$name = basename($style);//array_pop( explode('/', $style) );
		$topt .= '<option value="'.$name.'">'.$name.'</option>';
	}
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>set new Super-Password</title>
<meta charset="utf-8" />

<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript" src="../js/jquery.plugin_password_strength.js"></script>
<script type="text/javascript" src="../js/gpw.js"></script>
<style>
body{background: #eee;font:72.5% "Trebuchet MS", sans-serif;}
a{text-decoration:none;color:#000;}
#frm{position:absolute;top:50%;left:50%;width:160px;margin:-80px 0px 0px -80px;}
input, select{background:#fff;border:1px solid #333;padding:5px;margin:3px 0px;}
input[type=text]{width:158px;}
input, div {-moz-border-radius:5px;-webkit-border-radius:5px;-khtml-border-radius:5px;border-radius:5px;}

h3{color: #f00;}


.password_strength   {padding: 0 5px; display: inline-block;}
.password_strength_1 {background-color: #fcb6b1;}
.password_strength_2 {background-color: #fccab1;}
.password_strength_3 {background-color: #fcfbb1;}
.password_strength_4 {background-color: #dafcb1;}
.password_strength_5 {background-color: #bcfcb1;}

</style>

</head>
<body>
<form id="frm" style="display:none" method="post" action="setSuperpassword.php">
<?php

require 'functions.php';



if(!file_exists('../super.php'))
{
if(is_writable('../'))
{
if(isset($_POST['pass']))
{
$templates = glob('./tpl_*.php');
$tpl = array();
foreach($templates as $template)
{
	$tpl[] = basename($template);
}
file_put_contents('../super.php', '<?php
// auto-generated: do not edit!
$super = array(\''.$_POST['salt'].'\', \''.crpt($_POST['pass'], $_POST['salt']).'\');
$config = array(
	\'theme\' => array(\''.$_POST['theme'].'\'),
	\'backend_templates\' => array(\'inc/php/'.implode("','inc/php/", $tpl).'\'),
	\'autolog\' => array(0)
);
');
			
			chmod('../super.php', 0776);
			echo '<h2>Password saved!</h2>
			<a href="../../">Login-Page</a>';
		}
		else
		{
			echo '<h4>set Super-Password</h4>
			<input type="password" autocomplete="off" id="inputPassword" name="pass" />
			<input type="hidden" name="salt" id="salt" value="x" />
			<h4>default Theme</h4>
			<select name="theme">'.$topt.'</select>
			<hr /><input type="submit" value="save" />';
		}
	}
	else
	{
		echo '<h3>"backend/inc/" is not writable!</h3>';
	}
}
else
{
	echo '<h3>Super-Password already exists!</h3>';
}



?>
</form>

<script>
	
	$(function() {
		$('#frm').attr('autocomplete', 'off').css('display','block');
		$('#inputPassword').val('');
		var opts = {
			'minLength' : 8,
			'texts' : {
				1 : '<?php echo L('Password_too_weak')?>',
				2 : '<?php echo L('Password_weak')?>',
				3 : '<?php echo L('Password_ok')?>',
				4 : '<?php echo L('Password_strong')?>',
				5 : '<?php echo L('Password_very_strong')?>'
			}
		};
		$('#inputPassword').password_strength(opts);
		
		// generate a new random Salt
		$('#salt').val(GPW.complex(12));
	});
</script>
</body>
</html>
