<?php

require 'inc/htaccess.php';
require('../../inc/php/pclzip.lib.php');

mkdir($ppath);
chmod($ppath, 0777);

$html = '';

// fix some access-problems of pclzip
function preExtractCallBack($p_event, &$p_header)
{
	$info = pathinfo($p_header['filename']);
	if(!isset($info['extension']))// folders are created here
	{
		$d = $info['dirname'].'/'.$info['filename'];
		mkdir($d);
		chmod($d, 0777);
		return 0;
	}
	else// files are simply extracted, 
	{
		return 1;
	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////

// if we detect a uploaded project (zip-file), we use this
if(
	$_FILES['file'] && 
	$_FILES['file']['name'] && 
	array_pop(explode('.', strval($_FILES['file']['name']))) == 'zip'
  )
{
	$tname = substr($_FILES['file']['name'], 0, -4);
	$zipPath = $_FILES['file']['tmp_name'];
}
else // we use the empty dummy-project
{
	$tname = 'dummy';
	$zipPath = 'dummy.zip';
}

// we try to extract the ZIP
$archive = new PclZip($zipPath);
if ($archive->extract(	PCLZIP_OPT_PATH, $ppath,
						PCLZIP_OPT_REMOVE_PATH, $tname,
						PCLZIP_CB_PRE_EXTRACT, 'preExtractCallBack',
						PCLZIP_OPT_SET_CHMOD, 0777
					) == 0)
{
	exit('Unrecoverable error "' . $archive->errorName(true) . '"');
}
		
// create code for __configuration.php
$config = '<?php
/**
* Configurations for "'.$_POST['wished_name'].'"
*
* @copyright MIT-License: Free for personal & commercial use. (http://opensource.org/licenses/mit-license.php) 
* @link http://cms-kit.org
* @package '.$_POST['wished_name'].'
*/

final class Configuration
{
	const BUILD 				= \''.$KITVERSION.'\';
	const CRDATE 				= \''.date(DATE_RFC822).'\';

	public static $DB_ALIAS		= array(\''.implode("','", $_POST['dbalias']).'\');
	public static $DB_TYPE 		= array(\''.implode("','", $_POST['dbtype']).'\');
	public static $DB_HOST 		= array(\''.implode("','", $_POST['dbhost']).'\');
	public static $DB_DATABASE 	= array(\''.implode("','", $_POST['dbname']).'\');
	public static $DB_PORT 		= array(\''.implode("','", $_POST['dbport']).'\');
	public static $DB_USER 		= array(\''.implode("','", $_POST['dbuser']).'\');
	public static $DB_PASSWORD 	= array(\''.implode("','", $_POST['dbpass']).'\');
}
?>
';

file_put_contents($ppath.'/objects/__configuration.php', $config);
chmod($ppath.'/objects/__configuration.php', 0776);

// 
file_put_contents($ppath.'/objects/__database.php', str_replace('###PROJECTNAME###', $_POST['wished_name'], file_get_contents($ppath.'/objects/__database.php')));


// set the session-credentials for the new project
$_SESSION[$_POST['wished_name']]['root'] = 1;
$_SESSION[$_POST['wished_name']]['lang'] = $lang;//browserLang(array('de','en'), 'en');


$html .= '<form id="frm">
<fieldset><legend>(4) "'.$_POST['wished_name'].'" '.L('created').'</legend>
<a target="_blank" href="../modeling/index.php?project='.$_POST['wished_name'].'">'.L('goto_Data_Modeling').' &rArr;</a>
<hr />
<a target="_blank" href="../db_admin/index.php?project='.$_POST['wished_name'].'">'.L('goto_DB_Admin').' &rArr;</a>
<hr />
<a target="_blank" href="../../index.php?project='.$_POST['wished_name'].'">'.L('goto_Login_Page').' &rArr;</a>
<hr />
';

$we_have_some_sqlites = false;
for ($i = 0; $i < count($_POST['dbtype']); $i++)
{
	$html .= '<p>' . $_POST['dbalias'][$i];
	
	if($_POST['dbtype'][$i] == 'sqlite' && !file_exists($ppath.'/objects/'.$_POST['dbname'][$i])) 
	{
		$we_have_some_sqlites = true;
		
		$html .= ' <button type="button" onclick="prompt(\''.L('copy_Database_Path').'\',\''.
					addslashes(realpath($ppath.'/objects').DIRECTORY_SEPARATOR.$_POST['dbname'][$i]).
					'\')">'.L('copy_Database_Path').'</button>	' . 
					hlp('sqliteDbPath') . '<hr />';
		
	}
	if($_POST['dbtype'][$i] == 'mysql')
	{
		$html .= ' <button type="button" onclick="prompt(\''.L('copy_Database_Credentials').'\',\'Name: '.$_POST['dbname'][$i].'/Password: '.$_POST['dbpass'][$i].'\')">'.L('copy_Database_Credentials').'</button>';
	}
	
	$html .= '</p>';
}

$html .= '
</fieldset>
</form>

</div>
</body>
</html>
';

// build .htaccess-File
buildHtAccess($ppath, $we_have_some_sqlites);

echo $html;
exit();
?>
