<?php

/**
* basic PDO-Wrapper-Class
*/

require_once '__configuration.php';

/**
* access this object just via DB::instance([$index])
* @package  DB
* @author   Christoph Taubmann <info@cms-kit.org>
* @access   public
*/
class DB
{
	/**
	* holds the Reference to the Database (Singleton)
	* @var array $db 
	*/
	private static $db = array();
	
	/**
	* @var integer $escapecounter
	*/
	private static $escapecounter = 0; 

	/**
	* creates a Connection to the Database or creates a Pointer to the existing Connection
	* 
	* @param integer $i Index for the exisitng Database-Array (default 0)
	* @return object PDO instance
	*/
	static public function instance ( $i = 0 )
	{
		if (!isset(self::$db[$i]))
		{
				switch (Configuration::$DB_TYPE[$i])
				{
					case 'mysql':
						$conn = 'mysql:host=' . Configuration::$DB_HOST[$i] . ';dbname=' . Configuration::$DB_DATABASE[$i] . ';port=' . Configuration::$DB_PORT[$i];
					break;
					case 'sqlite':
						$conn = 'sqlite:' . dirname(__FILE__) . '/' . Configuration::$DB_DATABASE[$i];
					break;
					/*
					case 'postgresql':
						$conn = 'pgsql:host=' . Configuration::$DB_HOST[$i] . ' dbname=' . Configuration::$DB_DATABASE[$i] . ' port=' . Configuration::$DB_PORT[$i];
					break;
					case 'firebird':
						$conn = 'firebird:dbname=localhost:' . dirname(__FILE__) . '/' . Configuration::$DB_DATABASE[$i];
					break;
					case 'oracle':
						$conn = 'OCI:dbname=' . Configuration::$DB_DATABASE[$i] . ';charset=UTF-8';
					break;
					*/
				}
				
				try
				{
					self::$db[$i] = new PDO (
						$conn,
						Configuration::$DB_USER[$i],
						Configuration::$DB_PASSWORD[$i], 
						array(
							PDO::ATTR_PERSISTENT => true,
							PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
							PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
						)
					);
				}
				catch (PDOException $e)
				{
					exit('Error connecting Database: ' . $e->getMessage());
				}
		}
		return self::$db[$i];
	}
	
	/**
	* not used atm
	* used to create commaseparated queries
	* 
	* @param array $pdoparams reference to existing array which will later be executed with stmt object
	* @param array $arr list to implode
	* @return string commaseparated list with placeholders
	*/
	static public function implode ( &$pdoparams, $arr )
	{
		$tmp = array(); 
		foreach ($arr as $val)
		{ 
			$key = ':implode' . self::$escapecounter++; 
			$pdoparams[$key] = $val; 
			$tmp[] = $key; 
		} 
		return implode (',', $tmp); 
	}
	
	/**
	* used to escape Strings
	* 
	* @param string $str String to escape
	* @return string escaped String
	*/
	static public function escape ( $str )
	{
		return trim(self::instance(0)->quote($str), '\'');
	}
	
	/**
	* not used atm
	* used to create concatenated Results from Database-Queries (Database-aware)
	* @param array $arr Field-Names to concatenate
	* @return string db-concatenated String
	*/
	static public function concat ( $arr )
	{
		return null;
	}
	
	/**
	* creates a unique + incremented ID for Database-Entries
	* 
	* @return string ID based on microseconds + 3-digit random Number
	*/
	static public function uid ()
	{
		$t = gettimeofday();
		return $t['sec'] . $t['usec'] . mt_rand(100, 999);
	}
	
}
?>
