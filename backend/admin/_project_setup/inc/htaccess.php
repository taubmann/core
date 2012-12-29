<?php

// Print Setting-Interface

function htAccessChecks()
{
	$html  = '';
	// start of DIV
	$html .= '<h4 style="cursor:pointer" onclick="$(\'#htxs\').toggle()">'.L('htaccess-Settings').'</h4>
			 <div id="htxs" style="display:none">';
	
	// enable Security-Settings to jail malicious Developments 
	$html .= '<p>'.hlp('htx_jail').'<input type="checkbox" name="htx_jail" /> '.L('enable_Security-Settings').'</p>';
	// gzip-Compression (faster delivery, more processing)
	$html .= '<p>'.hlp('htx_gzip').'<input type="checkbox" name="htx_gzip" /> '.L('enable_gzip-Compression').'</p>';
	// 404-Mapping
	$html .= '<p><i>'.L('how_to_catch_nonexisting_Paths').':</i><br />';
	$html .= hlp('htx_err_nothing').'<input type="radio" name="htx_err" value="x" /> '.L('do_nothing').'<br />';
	$html .= hlp('htx_err_rewrite').'<input type="radio" name="htx_err" value="rewrite" /> '.L('rewrite_to').' index.php?PARAM=...<br />';
	$html .= hlp('htx_err_404').'<input type="radio" name="htx_err" value="404" /> '.L('show_404-Page').'</p>';
	// Timezone-Settings
	if (($handle = fopen('inc/timezones.txt', "r")) !== false) {
		$tz = array();
		while (($data = fgetcsv($handle, 1000, ' ')) !== false) { $tz[] = $data[1].'">'.$data[1]; }
		sort($tz);
		
		$html .= '<p>'.hlp('htx_set_timezone').'<i>'.L('Timezone').'</i><br />
				 <select name="timezone"><option value="">'.L('not_set').'</option>
				 <option value="'.implode('</option><option value="', $tz).'</option>
				 </select></p>';
		fclose($handle);
	}
	
	
	// end of DIV
	$html .= '</div>';
	
	return $html;
	
}

/*
 * create the .htaccess-content for this directory
 * 
 * */
function buildHtAccess($path, $sqlite_file)
{

// empty htaccess-String
$str = '';

// document root
$dp = realpath($_SERVER['DOCUMENT_ROOT']).'/';
$dp = str_replace('\\', '/', $dp);
$dp = str_replace('//', '/', $dp);

// absolute path
$ap = realpath($path).'/';
$ap = str_replace('\\', '/', $ap);
$ap = str_replace('//', '/', $ap);

// relative path beginning fom document-root
$rp = substr($ap, strlen($dp));

if(isset($_POST['htx_err']))
{
// 404-Mapping -> rewrite to GET-Parameter
if($_POST['htx_err']=='rewrite') { 
	$str .= '
# rewrite virtual File-Paths to $_GET["PARAM"]
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?PARAM=$1 [L]
';
}
// 404-Mapping -> Show 404-Page
if($_POST['htx_err']=='404') { 

	$str .= '
# show 40x Error-Pages
ErrorDocument 401 /'.$rp.'extensions/error/401.php
ErrorDocument 402 /'.$rp.'extensions/error/402.php
ErrorDocument 403 /'.$rp.'extensions/error/403.php
ErrorDocument 404 /'.$rp.'extensions/error/404.php
';
}
}// $_POST['htx_err']

// enable gzip-Compression
if(isset($_POST['htx_gzip'])) {
	
	// get System-Informations
	ob_start();
	phpinfo(INFO_MODULES);
	$contents = ob_get_contents();
	ob_end_clean();
	
	if(strpos($contents, 'mod_deflate')) {
		$str .= '
# compress web-output via gzip
<IfModule mod_deflate.c>
	SetOutputFilter DEFLATE
	SetEnvIfNoCase Request_URI \.(?:gif|jpe?g|png)$ no-gzip dont-vary
	SetEnvIfNoCase Request_URI  \.(?:exe|t?gz|zip|gz2|sit|rar)$ no-gzip dont-vary
</IfModule>
';
	}
	
	if(strpos($contents, 'mod_gzip')) {
		$str .= '
<IfModule mod_gzip.c>
	mod_gzip_on       Yes   
	mod_gzip_dechunk  Yes   
	mod_gzip_item_include file      \.(html?|txt|md|css|js|php|pl)$   
	mod_gzip_item_include handler   ^cgi-script$   
	mod_gzip_item_include mime      ^text/.*   
	mod_gzip_item_include mime      ^application/x-javascript.*   
	mod_gzip_item_exclude mime      ^image/.*   
	mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*   
</IfModule>
';
	}
}

// enable Security-Settings to jail malicious Developments
if(isset($_POST['htx_jail'])) { 
	$str .= '
# increase performance by disabling allowoverride
AllowOverride None

# limit Access for Project-Roots
php_value open_basedir ' . $ap . ':/tmp
php_value disable_functions phpinfo

# disable directory browsing
Options All -Indexes

# secure htaccess file
<Files .htaccess>
	order allow,deny
	deny from all
</Files>
';

if($sqlite_file) { 
	$str .= '
# secure database file
<Files db>
	order allow,deny
	deny from all
</Files>
';
}

} // jail END

if(isset($_POST['timezone']) && $_POST['timezone']!='') {
	$str .= '
# Timezone Offset
SetEnv TZ '.$_POST['timezone'].'
';
}

if(strlen($str)>0) {
	file_put_contents($path.'/.htaccess', $str);
	chmod($path.'/.htaccess', 0776); // set to 644
}

} // buildHtAccess END

?>
