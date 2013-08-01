<?php
/**
* import Hooks
* 
* 
* - extract all Functions found in extensions/EXTENSION_NAME/[xyz]hooks.php
* - check if the Function-Name already exists
* - and if not, add to projects/YOUR_PROJECT/cms/hooks.php
* 
*/
error_reporting(0);
require 'head.php';

$hook_path = $bp.'/../projects/' . $projectName . '/extensions/cms/hooks.php';

if(is_writable($hook_path))
{
	// (try to) include both Hook-Scripts
	@include $bp.'/extensions/cms/hooks.php';
	@include $hook_path;
	
	
	$str = trim(file_get_contents($add_path));
	
	// remove Comments
	$str = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", '', $str);
	
	// remove php-Tags
	$str = trim(str_replace(array('<?php','?>'), '', $str));
	
	// split into Function-Blocks
	$farr = explode('function ', $str);
	
	$addStr = '';
	$added = array();
	$skipped = array();
	
	// loop Function-Blocks
	foreach ($farr as $a)
	{
		// extract Function-Body
		$fb = trim($a);
		// extract Function-Name
		$fn = trim(array_shift(explode('(',$fb)));
		
		if (strlen($fn)>0)
		{
			if ( !function_exists($fn) )
			{
				$addStr .= "\n\n// Hook '".$fn."' added from Extension: '" . $_GET['ext'] . "'\nfunction " . $fb . "\n\n";
				$added[] = $fn;
			}
			else
			{
				$skipped[] = $fn;
			}
		}
	}
	
	if (count($added)>0)
	{
		file_put_contents($hook_path, file_get_contents($hook_path) . $addStr);
	}
	
	echo 'alert("Import complete!';
		echo '\n'.count($added).' imported Functions ('.implode(', ', $added).')';
		echo '\n'.count($skipped).' skipped Functions ('.implode(', ', $skipped).')';
	echo '");';
	
}
else
{
	echo 'alert("hook.php is not writable!")';
}
?>
