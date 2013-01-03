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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Super-Password</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<script type="text/javascript" src="../../inc/js/jquery.min.js"></script>
<script type="text/javascript" src="inc/js/mocha.js"></script>
<style>
body{background: #eee;font:62.5% "Trebuchet MS", sans-serif;}
a{text-decoration:none;color:#000;}
#frm{position:absolute;top:50%;left:50%;width:160px;margin:-80px 0px 0px -80px;}
input{background:#fff;border:1px solid #333;padding:5px;margin:3px 0px;}
input[type=text]{width:158px;}
input, div {-moz-border-radius:5px;-webkit-border-radius:5px;-khtml-border-radius:5px;border-radius:5px;}
h3{color: #f00;}
#complexity, #results{width: 160px;padding: 2px 0;height: 20px;color: #000;font-size: 14px;text-align: center;}
#results{margin: 30px 0 20px 0;}
.default{background-color: #CCC;}
.weak{background-color: #FF5353;}
.strong{background-color: #FAD054;}
.stronger{background-color: #93C9F4;}
.strongest{background-color: #B6FF6C;}
.value{color:blue; padding-left: 10px;}
</style>

</head>
<body>
<form id="frm" style="display:none" method="post" action="superpw.php">
<?php

	require '../../inc/php/functions.php';

	if(!file_exists('../super.php'))
	{
		if(is_writable('../'))
		{
			if(isset($_POST['pass']))
			{
				file_put_contents('../super.php', '<?php'."\n// auto-generated: do not edit!\n\$super = '".crpt($_POST['pass'])."';\n");
				chmod('../super.php', 0776);
				echo '<h2>Password saved!</h2>
				<a href="index.php">Project-Setup</a>
				<hr />
				<a href="../../">Login-Page</a>';
			}
			else
			{
				echo '<input type="password" autocomplete="off" id="inputPassword" name="pass" />
				<div id="complexity" class="default">Enter a Password</div>
				<div class="block"><div id="results" class="default">Details</div><div id="details"></div></div>
				<hr /><input type="submit" value="save" />';
			}
		}
		else
		{
			echo '<h3>"backend/admin/" is not writable!</h3>';
		}
	}
	else
	{
		echo '<h3>Super-Password already exists!</h3>';
	}
	
?>
</form>

<script>
	$('#frm').css('display','block');
	$('#inputPassword').val('');
</script>
</body>
</html>
