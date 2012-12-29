<?php
/*
 * import data from sql-dump 
 * 
*/

require 'head.php';
require ($obj_path.'__database.php');

if ( is_readable($add_path) )
{
	$import = trim(file_get_contents($add_path));
	
	if (stristr($import, 'CREATE ') || stristr($import, 'DROP '))
	{
		exit('alert("SQL must not contain CREATE- or DROP-Statements!");');
	}
	
	// remove some comments
	$import = preg_replace ("%/\*(.*)\*/%Us", '', $import);
	$import = preg_replace ("%^--(.*)\n%mU", '', $import);
	$import = preg_replace ("%^$\n%mU", '', $import);
	// split all statements
	$importArray = explode('INSERT INTO', $import);
	
	$err = array();
	foreach ($importArray as $imp)
	{
		if (strlen(trim($imp))>5)
		{
			//echo 'INSERT OR IGNORE INTO '.trim($imp) . "\n";
			try
			{
				DB::instance()->query('INSERT OR IGNORE INTO '.trim($imp));
			}
			catch(exception $e)
			{
				$err[] = $e;
			}
		}
	}
	echo 'alert("Datadump imported!'.((count($err)>0)?'\nErrors: ('.implode(', ',$err).')':'').'");';
}

?>
