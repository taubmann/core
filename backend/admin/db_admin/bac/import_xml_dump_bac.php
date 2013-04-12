<?php
session_start();
//error_reporting(0);
error_reporting(E_ALL ^ E_NOTICE);
$projectName = preg_replace('/[^-\w]/', '', strtolower($_GET['project']));

$level = ((substr(basename(dirname(__FILE__)),0,1)!=='_') ? 1 : 2);

if (!$_SESSION[$projectName]['root'] >= $level) exit('you are not allowed to access this Service!');

$ppath = '../../../projects/' . $projectName . '/objects/';

if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {

echo '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<style>body{padding:50px;font:.8em sans-serif;}</style>
</head>
<body><p><a href="index.php?project='.$projectName.'">back to Index</a></p>';


// https://forums.digitalpoint.com/threads/import-xml-to-mysql-database-using-php.550665

//$fp = fopen("-var-www-cmskit-projects-kfk-objects-XgHKviP4shrD8DZlRZ71-db-2.xml","r") or die("Error reading XML data.");
$fp = fopen($_FILES['userfile']['tmp_name'], "r") or die("Error reading XML-Data!");

$eltype = null;
$elname = null;
$obj = null;

$insideitem = false;
$tag = '';
$str = '';

function startElement($parser, $type, $attrs)
{
	global $insideitem, $tag, $ppath, $obj, $elname;
	
	//$eltype = $type;
	$tag = $type;
	$elname = $attrs['NAME'];
	$insideitem = false;
	
	// create a new Object
	if ($type == "TABLE")
	{
		require_once $ppath. 'class.' . $attrs['NAME'] . '.php';
		echo "new Entry in: <b>" . $attrs['NAME'] . "</b> ( ";
		$obj = new $attrs['NAME']();
	}
	
}

function endElement($parser, $type)
{
	global $str, $insideitem, $tag, $obj, $elname;
	
	//$tag = '';
	//$elname = '';
	$insideitem = false;
	if($tag == "COLUMN")
	{
		$obj->{$elname} = $str;
		/*if($elname=='model') 
		{
			$obj->model = 'dada';
			echo 'xxx';
		}*/
		echo $elname . ' (' . strlen($str) .'), ';
		$str = '';
	}
	// save to DB
	if ($type == "TABLE")
	{
		$obj->Save();
		$obj = null;
		echo ")<hr>";
	}
}

function getData($parser, $data)
{
	global $str, $insideitem, $tag, $obj, $elname;
	if($tag == "COLUMN")
	{
		$str .= $data;
		if ($insideitem)
		{
			//$obj->$elname .= $data;
		}
		else
		{
			
			//$obj->$elname = $data;
		}
	}
	$insideitem = true;
}

$xml_parser = xml_parser_create();
xml_set_element_handler($xml_parser, "startElement", "endElement");
xml_set_character_data_handler($xml_parser, "getData");

while ($data = fread($fp, 1024)) // read 5MB
{
	xml_parse($xml_parser, $data, feof($fp)) 
	or die(
		sprintf(
			"XML-Error: %s at Line %d",
			xml_error_string(xml_get_error_code($xml_parser)),
			xml_get_current_line_number($xml_parser)
		)
	);
}
fclose($fp);
xml_parser_free($xml_parser);

echo '</body></html>';

}else{ //if no FILE show Upload-Form
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />

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
	width: 400px;
	margin: -200px 0 0 -210px;
}
</style>

</head>
<body>

<div id="wrapper">
<a href="index.php?project=<?php echo $projectName?>">back</a>
<h4>Import XML-Dump into your DB</h4>
<p>(Export via Adminer)</p>
<form enctype="multipart/form-data" action="import_xml_dump.php?project=<?php echo $projectName?>" method="post" onsubmit="return askUser()">
	<!-- <input type="hidden" name="MAX_FILE_SIZE" value="6000000" />-->
	Diese Datei hochladen: <input name="userfile" type="file" />
	<input type="submit" value="import" />
</form>
</div>
<script>

function askUser()
{
	var q = confirm('really upload this File and (possibly) overwrite Data?');
	return q;
}

</script>
</body>
</html>
<?php
} // else end
?>
