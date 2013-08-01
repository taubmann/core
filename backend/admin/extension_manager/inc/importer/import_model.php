<?php
/*
 * add objects to the model
 * explode by base64_encoded string
 * </objects>  =>  PC9vYmplY3RzPg
 * PC9vYmplY3RzPgo
*/
error_reporting(0);
require 'head.php';

$obj_xml = $obj_path.'__modelxml.php';


if ( is_writable($obj_xml) && is_readable($add_path) )
{
	require ($obj_xml);
	
	
	$xml = simplexml_load_file($add_path);
	
	$addstr = '';
	$add_el = array();
	$excl_el = array();
	
	// check, if object already exists
	foreach ($xml as $element)
	{
		// the object is already in the system -> skip
		if(strstr($model, '<object name="'.$element['name'].'"'))
		{
			$excl_el[] = $element['name'];
		}
		// the object is new -> add
		else
		{
			$addstr .= $element->asXML() . PHP_EOL;
			$add_el[] = $element['name'];
		}
	}
	
	// add new Objects to model
	$blocks = explode('</objects>', $model);
	$phpout = "<?php\n\$model = <<<EOD\n" . array_shift($blocks) . $addstr . '</objects>'.PHP_EOL."EOD;\n?>";
	
	// write to disk
	file_put_contents($obj_xml, $phpout);
	
	// output
	echo 'alert("added Objects: ('.implode(', ',$add_el).'),\nskipped Objects: ('.implode(', ',$excl_el).')");';
	echo 'window.open("../modeling/index.php?project='.$projectName.'", "modeling");';
	
}
else
{
	echo 'alert("could not load/save Model");';
}

?>
