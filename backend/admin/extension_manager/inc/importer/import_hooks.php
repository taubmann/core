<?php
/*
 * import hook-functions
 * 
*/

require 'head.php';

$hook_path = $bp.'/../projects/' . $projectName . '/extensions/all/hooks.php';

if(is_writable($hook_path))
{
	// try to include both hook-files
	include $bp.'/extensions/all/hooks.php';
	include $hook_path;
	
	
	$str = trim(file_get_contents($add_path));
	// remove comments
	$str = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", '', $str);
	// remove php-tags
	$str = trim(str_replace(array('<?php','?>'), '', $str));
	
	// split into functions
	$farr = explode('function ', $str);
	
	$addStr = '';
	$added = array();
	$skipped = array();
	
	//loop functions
	foreach ($farr as $a)
	{
		$fb = trim($a);// extract function-body
		$fn = trim(array_shift(explode('(',$fb)));// extract function-name
		
		if (strlen($fn)>0)
		{
			if ( !function_exists($fn) )
			{
				$addStr .= "\n\n// Hook '".$fn."' added from Extension: '" . $_GET['ext'] . "'\nfunction " . $fb . "\n";
				$added[] = $fn;
			}
			else
			{
				
				$skipped[] = $fn;
			}
		}
	}
	
	//echo $addStr;
	if (count($added)>0)
	{
		file_put_contents($hook_path, file_get_contents($hook_path) . $addStr);
	}
	
	echo 'alert("Import complete!';
		echo '\n'.count($added).' imported functions ('.implode(', ', $added).')';
		echo '\n'.count($skipped).' skipped functions ('.implode(', ', $skipped).')';
	echo '");';
	
}else{
	echo 'alert("hook.php is not writable!")';
}
