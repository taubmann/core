<?php

/*
 * cms-kit Object-Generator
 * 
 * based on PHP Object-Generator (http://phpobjectgenerator.com)
 * version 2.0
 * copyright Free for personal & commercial use. (Offered under the modified BSD license)
 * 
 * */
$GLOBALS['cconfiguration'] = array(
	'author' => 'cms-kit Object-Generator ',
	'version' => '0.9 revision 2',
	'copyright' => 'Free for personal & commercial use. (Offered under the modified BSD license)',
	'link' => 'http://cms-kit.org'
);


class ObjectGenerator
{
	var $str; // php-code
	var $objectName; // Object Name
	var $elementList; // Fields
	var $savePath; // where to save
	
	
	function __construct($name, $model, $types, $savepath)
	{
		
		// Variables
		$this->objectName 	= $name;
		$this->model 		= $model;
		$this->elementList 	= $model[$name];
		$this->treeType 	= $model[$name]['ttype'];
		$this->db			= $model[$name]['db'];
		$this->manualId 	= (Configuration::$DB_INCREMENT[$this->db] == 'manual') ? true : false; // manual/auto-increment
		
		//
		$this->savePath 	= $savepath;
		$this->types 		= $types;
		
		$this->defaultFunctionCalls = array();
		
		// Function-calls
		$this->BeginObject();
		//$this->CreateConstructor();
		
		if(count($this->defaultFunctionCalls) > 0)
		{
			$this->CreateConstructor();
		}
		
		$this->CreateSingleton();
		
		$this->CreateGetFunction();
		$this->CreateGetListFunction();
		
		// Trees
		if($this->treeType=='Tree')
		{
			$this->CreateGetTreeFunction();
			$this->CreateAddTreeChildFunction();// creates multiple functions
		}
		
		// Graphs
		if($this->treeType=='Graph')
		{
			$this->CreateGetGraphFunction();
			$this->CreateAddGraphChildFunction();// creates multiple functions
		}
		
		$this->CreateSaveFunction(true);
		$this->CreateSaveNewFunction(true);
		$this->CreateDeleteFunction(true);
		//$this->CreateDeleteListFunction(true);// buggy
		$this->CreateReferencesFunction();
		$this->EndObject();
		
		// save the class
		$path = $this->savePath . 'class.' . strtolower($this->objectName) . '.php';
		file_put_contents($path, $this->str);
		chmod($path, 0776);
		
	}
	
	
	function MappingName($objectName1, $objectName2, $add='Map', $lower=true)
	{
		$array = array($objectName1, $objectName2);
		natcasesort($array);
		$str = array_shift($array) . $array[0];
		return  ($lower ? strtolower($str) : $str) . $add;
	}
	
	function CreateComments($description='', $parameterDescriptionArray='', $returnType='')
	{
		
		$this->str .= "/**\n\t* ".$description."\n";
		
 		if ($parameterDescriptionArray != '')
 		{
	 		foreach ($parameterDescriptionArray as $parameter)
	 		{
	 			$this->str .= "\t* @param ".$parameter."\n";
	 		}
 		}
 		
 		if ($returnType != '')
 		{
			$this->str .= "\t* @return ".$returnType."\n";
		}
		
	    $this->str .= "\t*/\n";
	}
	
	function CreatePreface()
	{
		$this->str .= "/*\n*\t class '".$this->objectName."' with integrated CRUD methods.";
		$this->str .= "\n*\t @author ".$GLOBALS['cconfiguration']['author'];
		$this->str .= "\n*\t @version ".$GLOBALS['cconfiguration']['versionNumber'].' rev. '.$GLOBALS['cconfiguration']['revisionNumber']." ";
		$this->str .= "\n*\t @copyright ".$GLOBALS['cconfiguration']['copyright'];
		$this->str .= "\n*\t @link ".$GLOBALS['cconfiguration']['link'];
		$this->str .= "\n*/\n";
	}
	
	// -------------------------------------------------------------
	function BeginObject()
	{
		$this->str = "<?php\n";
		$this->str .= $this->CreatePreface();
		
		//
		$this->str .= "\ninclude_once('__database.php');";
		
		// create include of mapping-classes
		//print_r($this->elementList['rel']);
		if(isset($this->elementList['rel']))
		{
			foreach ($this->elementList['rel'] as $key => $attr)
			{
				if ($attr == 's')
				{
					$this->str .= "\ninclude_once('class.".strtolower($this->MappingName($this->objectName, $key)).".php');";
				}
			}
		}
		
		
		$this->str .= "\n\n\nclass ".$this->objectName."\n{\n\t";
		$x = 0;
		$tmp = array();
		$tmp2 = array();
		
		
		$db_type = Configuration::$DB_TYPE[$this->db];
		$this->str .="/**\n\t";
		$this->str .=" * @const int DB (Database-Type: $db_type)\n\t";
		$this->str .=" */\n\t";
		$this->str.="const DB = $this->db;";
		$this->str.="\n\t\n\t";
		
		$this->str .="/**\n\t";
		$this->str .=" * @var object _INSTANCE_\n\t";
		$this->str .=" */\n\t";
		$this->str.="static private \$_INSTANCE_ = null;";
		$this->str.="\n\t\n\t";
		
		
		
		foreach ($this->elementList['col'] as $key => $attr)
		{
			$unq = 0;
			$quote = intval($this->types[$attr['type']]['quote']);
			
			// if it is the Table-ID
			if($key=='id')
			{
				//$key = strtolower($this->objectName).'Id';
				$this->types[$attr['type']]['quote'] = 1;
				$unq = 1;
				if($this->manualId) $quote=1;
			}
			
			$tmp[] = "\n\t\t\t'".$key."'=>array('unique'=>".$unq.", 'quote'=>".$quote.")";
			
			$mod = $this->model[$this->objectName]['col'][$key];
			
			$this->str .= "/**\n\t";
			$this->str .= (isset($mod['comment']) ? " * " . $mod['comment'] . "\n\t" : '');
			//Configuration::$DB_TYPE[$this->db]
			
			$this->str .= " * @var ". $this->types[$attr['type']]['php'] . ' $' . stripcslashes($key) . ' (Database: '.$this->types[$attr['type']][$db_type].")\n\t";
			$this->str .= " */\n\t";
			
			
			// Variable
			$this->str .= "public $".$key." = ";
			
			// set a default-value if defined
			if(isset($mod['default']) && $mod['default']!='__EMPTY_STRING_')
			{
				if(substr($mod['default'], 0, 9)=='function:')
				{
					$this->str .= "''";
					$this->defaultFunctionCalls[$key] = substr($mod['default'], 9);
				}
				else
				{
					$this->str .= (is_numeric($mod['default']) ? floatval($mod['default']) : "'".$mod['default']."'");
				}
			}
			else
			{
				$this->str .= "''";// no default, set empty String
			}
			
			$this->str .=";\n\t";
			// Variable END
			
			$this->str .="\n\t";
		
			$x++;
		}
		
		
		$tmp2[] = "\n\t\t\t'".$this->objectName."' => array('" . implode("','", array_keys($this->elementList['col'])) . "')";
		
		// define siblings + childs
		if(isset($this->elementList['rel']))
		{
			foreach ($this->elementList['rel'] as $key => $attr)
			{
				if ($attr == 's' || $attr == 'c')
				{
					$this->str .="/**\n\t";
					$this->str .=" * @var array \$_".strtolower($key)."List List of $key objects\n\t";
					$this->str .=" */\n\t";
					$this->str.="private \$_".strtolower($key)."List = array();\n\t";
					$this->str.="\n\t";
				}
				
				if($this->model[$key]['col'])
				{
					$tmp2[] = "\n\t\t\t'".$key."' => array('" . implode("','", array_keys($this->model[$key]['col'])) . "')";
				}
			}
		}
		
		
		$this->str .="\n\t/**\n\t";
		$this->str .=" * @var array \$__columns Definitions of Relation-Fields for '".$this->objectName."' \n\t";
		$this->str .=" */";
		$this->str .= "\n\tprivate \$__columns = array(" . implode(', ', $tmp2) . " );";
		
	}
	
	
	function CreateSingleton()
	{
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Singleton-Function to define the Object ".$this->objectName." only once",
											array(),
											"object \$".$this->objectName."");
		$this->str .= "\tpublic static function instance ()\n\t{";
		$this->str .= "\n\t\tif (!isset(self::\$_INSTANCE_))\n\t\t{";
		$this->str .= "\n\t\t\tself::\$_INSTANCE_ = new ".$this->objectName."();";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\treturn self::\$_INSTANCE_;";
		$this->str .= "\n\t}";
	}
	
	// -------------------------------------------------------------
	function CreateConstructor()
	{
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Function to predefine the Object ".$this->objectName,
											array(),
											'');
		$this->str .= "\tfunction __construct()\n\t{";
		
		foreach($this->defaultFunctionCalls as $k => $v)
		{
			$this->str .="\n\t\t\$this->".$k." = ".$v.";";
		}
		
		$this->str .= "\n\t}";
	}
	
	
	// -------------------------------------------------------------
	function CreateGetFunction()
	{
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Gets object from database", 
						array( (($this->manualId)?'string':'int') ." \$".strtolower($this->objectName)."Id"),
						"object \$".$this->objectName);
		
		$this->str .="\tfunction Get (\$id)\n\t{";
		$this->str .= "\n\t\t\$query = 'SELECT * FROM `".strtolower($this->objectName)."` WHERE `id`=:id LIMIT 1;';";
		$this->str .= "\n\t\t\$prepare = DB::instance($this->db)->prepare(\$query);";
		
		
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\ttry";
		$this->str .= "\n\t\t{";
		
		$this->str .= "\n\t\t\t\$prepare->execute(array(':id'=>\$id));";//
		$this->str .= "\n\t\t\t\$result = \$prepare->fetch();\n\t\t";
		
		foreach ($this->elementList['col'] as $key => $attr)
		{
			$this->str .= "\n\t\t\t\$this->".$key." = \$result->".$key.";";
		}
		$this->str .= "\n\t\n\t\t\treturn \$this;";
		
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\tcatch (Exception \$e)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\treturn '[['.\$e->getMessage().']]';";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\t";
		
		
		
		
		$this->str .= "\n\t}";
	}
	
	
	// -------------------------------------------------------------
	function CreateGetListFunction()
	{
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Returns a sorted array of objects that match given conditions", 
						array(	"mixed \$fcv {(\"field\", \"comparator\", \"value\"), (\"field\", \"comparator\", \"value\"), ...}",
								"array \$sortBy",
								"integer \$limit 0 means unlimited",
								"integer \$offset"
							),
						"array \$".strtolower($this->objectName)."List");
		
		$this->str .= "\tfunction GetList (\$fcv = array(), \$sortBy = array(), \$limit = 0, \$offset = 0)\n\t{";
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\t\$".strtolower($this->objectName)."List = array();";
		$this->str .= "\n\t\t\$bindings = array();";
		$this->str .= "\n\t\t\$query = 'SELECT * FROM `".strtolower($this->objectName)."`';";
		
		$this->str .= "\n\t\tif (sizeof(\$fcv) > 0)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$whereArray = array();";
		
		$this->str .= "\n\t\t\tforeach (\$fcv as \$a)";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\tif (count(\$a) === 1 && is_string(\$a[0]))";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\$whereArray[] = trim(DB::instance($this->db)->quote(\$a[0]), '\'');";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t\telse";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\tif (is_array(\$a[0]))";
		$this->str .= "\n\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\$orArray = array();";
		$this->str .= "\n\t\t\t\t\t\tforeach (\$a as \$o)";
		$this->str .= "\n\t\t\t\t\t\t{";
		
		$this->str .= "\n\t\t\t\t\t\t\tif (in_array(\$o[2], \$this->__columns['".$this->objectName."'], true))";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '`' . \$o[2] . '`';";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '?';";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$bindings[] = \$o[2];";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\t\$orArray[] = '`' . \$o[0] . '` ' . \$o[1] . ' ' . \$value;";
		$this->str .= "\n\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\$whereArray[] = '(' . implode(' OR ', \$orArray) . ')';";
		$this->str .= "\n\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t{";
		
		$this->str .= "\n\t\t\t\t\t\t\tif (in_array(\$a[2], \$this->__columns['".$this->objectName."'], true))";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '`' . \$a[2] . '`';";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '?';";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$bindings[] = \$a[2];";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\$whereArray[] = '`' . \$a[0] . '` ' . \$a[1] . ' ' . \$value;";
		
		$this->str .= "\n\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t\t\$query .= ' WHERE ' . implode(' AND ', \$whereArray);";
		$this->str .= "\n\t\t}";
		
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\t\$sortByArray = array();";
		$this->str .= "\n\t\tforeach (\$sortBy as \$field => \$direction)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\tif (strtolower(\$direction) === 'asc' || strtolower(\$direction) === 'desc')";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\t\$sortByArray[] = '`' . trim(DB::instance($this->db)->quote(\$field), '\'') . '` ' . \$direction;";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\tif (!isset(\$sortBy['id']))";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$sortByArray[] = '`id` ASC';";
		$this->str .= "\n\t\t}";
		
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\t\$sqlLimit = (intval(\$limit) > 0 ? ' LIMIT ' . \$limit : '');";
		$this->str .= "\n\t\t\$sqlOffset = (intval(\$offset) > 0 ? ' OFFSET ' . \$offset : '');";
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\t\$query .= ' ORDER BY ' . implode(', ', \$sortByArray) . \$sqlLimit . \$sqlOffset;";
		
		
		$this->str .= "\n\t\ttry";
		$this->str .= "\n\t\t{";
		
		$this->str .= "\n\t\t\t\$prepare = DB::instance($this->db)->prepare(\$query);";
		$this->str .= "\n\t\t\t\$prepare->execute(\$bindings);";
		$this->str .= "\n\t\t\t\$list = \$prepare->fetchAll();";
		
		$this->str .= "\n\t\t\t";
		//$this->str .= "\n\t\t\t\$thisObjectName = get_class(\$this);";
		$this->str .= "\n\t\t\tforeach (\$list as \$key => \$lst)";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\t\$".$this->objectName." = new ".$this->objectName."();";// define new object
		//$this->str .= "\n\t\t\t\t\$".$this->objectName." = new \$thisObjectName();";// define new object
		//$this->str .= "\n\t\t\t\t\$".strtolower($this->objectName)." = \$thisObjectName::instance();";// define new object
		$this->str .= "\n\t\t\t\tforeach (\$lst as \$key => \$val)";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\$".$this->objectName."->\$key = \$val;";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\$".$this->objectName."List[] = \$".$this->objectName.";";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t\treturn \$".$this->objectName."List;";
		
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\tcatch (Exception \$e)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\treturn '[['.\$e->getMessage().']]';";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\t";
		
		$this->str .= "\n\t}";
		
		
	}
	
	// not used atm
	/*function CreateGetTreeAsListFunction()
	{
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Returns a sorted array of objects that match given conditions", 
											array("multidimensional array {(\"field\", \"comparator\", \"value\"), (\"field\", \"comparator\", \"value\"), ...}","array \$sortBy","int \$limit","int \$offset"),
											"array \$".strtolower($this->objectName)."List");
		
		$this->str .= "\tfunction GetList (\$fcv = array(), \$sortBy = array(), \$limit = 0, \$offset = 0)\n\t{";
		$this->str .= "\n\t\t";
		
		$this->str .= "\n\t\t\$list = \$this->GetTreeList(\$fcv);";
		$this->str .= "\n\t\t\$list2 = array();";
		$this->str .= "\n\t\t\$cnt = count(\$list);";
		$this->str .= "\n\t\tfor (\$i=\$offset; \$i<\$cnt; \$i++)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\tif (\$i >= \$limit)";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\tbreak;";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t\t\$list2[] = \$list[\$i];";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\treturn \$list2;";
		$this->str .= "\n\t}";
		
		
	}*/
	
	
	/*
	 * see 
	 * http://www.klempert.de/nested_sets (german)
	 * */
	// creates a Function to get the (Sub-)Tree from a Nested-Set Tree
	function CreateGetTreeFunction()
	{
		$n = strtolower($this->objectName);
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Returns a hierachical tree-array of objects that match given conditions", 
											array(	"mixed \$fcv {(\"field\", \"comparator\", \"value\"), (\"field\", \"comparator\", \"value\"), ...}",
													"array \$sort",
													"integer \$limit default unlimited",
													"integer \$offset default no Offset",
													(($this->manualId)?'string':'integer')." \$parentId 0 means top Level",
													"integer \$depth per default limited to 99 Levels", 
												),
											"array \$".$n."List");
		
		
		$this->str .= "\tfunction GetTreeList (\$fcv = array(), \$sort = array(), \$limit = 0, \$offset = 0, \$parentId = 0, \$depth = 99)\n\t{";
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\t\$".$n."List = array();";
		$this->str .= "\n\t\t\$bindings = array(\$parentId);";
		//$this->str .= "\n\t\t\$query = 'SELECT a.*, count(a.*)-1 AS treelevel FROM `".strtolower($this->objectName)."` AS a JOIN `".strtolower($this->objectName)."` AS b ON a.treeleft BETWEEN b.treeleft AND b.treeright AND a.treeright BETWEEN b.treeleft AND b.treeright WHERE a.`treeparentid` = ?';";
		
		
		$this->str .= "\n\t\tif (\$depth == 1)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$query = 'SELECT c.*, 0 AS treelevel, ((c.treeright-c.treeleft-1)/2) AS treechilds FROM `".$n."` c WHERE c.treeparentid = ? ';";
		$this->str .= "\n\t\t\t\$sqlGroup = '';";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\telse";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$query = 'SELECT c.*, count(b.id)-1 AS treelevel, (c.treeright-c.treeleft-1)/2 AS treechilds FROM `".$n."` AS a, `".$n."` AS b, `".$n."` AS c WHERE c.treeleft BETWEEN b.treeleft AND b.treeright AND c.treeleft BETWEEN a.treeleft AND a.treeright AND  a.treeparentid = ?';";
		$this->str .= "\n\t\t\t\$sqlGroup = ' GROUP BY c.treeleft';";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\tif (sizeof(\$fcv) > 0)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$whereArray = array();";
		
		$this->str .= "\n\t\t\tforeach (\$fcv as \$a)";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\tif (count(\$a) === 1 && is_string(\$a[0]))";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\$whereArray[] = trim(DB::instance($this->db)->quote(\$a[0]), '\'');";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t\telse";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\tif (is_array(\$a[0]))";
		$this->str .= "\n\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\$orArray = array();";
		$this->str .= "\n\t\t\t\t\t\tforeach (\$a as \$o)";
		$this->str .= "\n\t\t\t\t\t\t{";
		
		$this->str .= "\n\t\t\t\t\t\t\tif (in_array(\$o[2], \$this->__columns['".$this->objectName."'], true))";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '`' . \$o[2] . '`';";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '?';";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$bindings[] = \$o[2];";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\t\$orArray[] = 'c.`' . \$o[0] . '` ' . \$o[1] . ' ' . \$value;";
		$this->str .= "\n\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\$whereArray[] = '(' . implode(' OR ', \$orArray) . ')';";
		$this->str .= "\n\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t{";
		
		$this->str .= "\n\t\t\t\t\t\t\tif (in_array(\$a[2], \$this->__columns['".$this->objectName."'], true))";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '`' . \$a[2] . '`';";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '?';";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$bindings[] = \$a[2];";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\$whereArray[] = 'c.`' . \$a[0] . '` ' . \$a[1] . ' ' . \$value;";
		
		$this->str .= "\n\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t\t\$query .= ' AND ' . implode(' AND ', \$whereArray);";
		$this->str .= "\n\t\t}";
		
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\t\$sortArray = array();";
		$this->str .= "\n\t\tforeach (\$sort as \$field => \$dir)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\tif (strtolower(\$dir) === 'asc' || strtolower(\$dir) === 'desc')";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\t\$sortArray[] = 'c.' . preg_replace('/\W/','', \$field) . ' ' . \$dir;";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\t\$sortArray[] = 'c.treeleft';";

		
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\t\$sqlLimit = (intval(\$limit) > 0 ? ' LIMIT ' . \$limit : '');";
		$this->str .= "\n\t\t\$sqlOffset = (intval(\$offset) > 0 ? ' OFFSET ' . \$offset : '');";
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\t\$query .= \$sqlGroup . ' ORDER BY ' . implode(',', \$sortArray) . \$sqlLimit . \$sqlOffset;";// 
		
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\ttry";
		$this->str .= "\n\t\t{";
		
		$this->str .= "\n\t\t\t\$prepare = DB::instance($this->db)->prepare(\$query);";
		$this->str .= "\n\t\t\t\$prepare->execute(\$bindings);";
		$this->str .= "\n\t\t\t\$list = \$prepare->fetchAll();";
		
		$this->str .= "\n\t\t\t";
		//$this->str .= "\n\t\t\t\$thisObjectName = get_class(\$this);";
		$this->str .= "\n\t\t\tforeach (\$list as \$key => \$lst)";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\tif (\$lst->treelevel < \$depth)";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\$".$n." = new ".$n."();";// define new object
		//$this->str .= "\n\t\t\t\t\t\$".$n." = \$thisObjectName::instance();";// define new object
		$this->str .= "\n\t\t\t\t\tforeach (\$lst as \$key => \$val)";
		$this->str .= "\n\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\$".$n."->\$key = \$val;";
		$this->str .= "\n\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\$".$n."List[] = \$".$n.";";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t}";
		
		
		$this->str .= "\n\t\t\treturn \$".$n."List;";
		
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\tcatch (Exception \$e)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\treturn '[['.\$e->getMessage().']]';";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\t";
		
		$this->str .= "\n\t}";
	}
	
	
	
	/*  see: http://stackoverflow.com/questions/889527/mysql-move-node-in-nested-set
		Change positions of node and all it's sub nodes into negative values, which are equal to current ones by module.
		Move all positions "up", which are more, that pos_right of current node.
		Move all positions "down", which are more, that pos_right of new parent node.
		Change positions of current node and all it's subnodes, so that it's now will be exactly "after" (or "down") of new parent node.
		
		step 1: temporary "remove" moving node (setting to negative)
		step 2 (2 queries): decrease left and/or right position values of currently 'lower' items (and parents)
		step 3 (2 queries): increase left and/or right position values of future 'lower' items (and parents)
		step 4: move node (and it's subnodes)
		* and update it's parent-id (as a prepared statement)
		* if there is any predecessor we have to "swap"
	*/
	
	function CreateAddTreeChildFunction()
	{
		$n = strtolower($this->objectName);
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Associates a Node/Branch of a Tree as Child to a Parent-Object", 
											array("object \$child", "object \$predecessor (not used atm)"),
											'');
		
		$this->str .= "\tfunction Add".$n." (\$child, \$predecessor=false)\n\t{";
		$this->str .= "\n\t\t";
		
		
		$this->str .= "\n\t\t// Recursion-Check";
		$this->str .= "\n\t\tforeach (DB::instance($this->db)->query('SELECT `id` FROM `".$n."` WHERE `treeleft` BETWEEN ' . intval(\$child->treeleft) . ' AND ' . intval(\$child->treeright)) as \$row)\n\t\t{";
		$this->str .= "\n\t\t\tif(\$row->id == \$this->id)\n\t\t\t{\n\t\t\t\treturn false;\n\t\t\t}";
		$this->str .= "\n\t\t}";
		
		
		// insert-actions
		$this->str .= "\n\t\t\$size = \$child->treeright - \$child->treeleft + 1;";
	
		$this->str .= "\n\t\t\$sql = array (
			'UPDATE `".$n."` SET `treeleft` = 0-(`treeleft`), `treeright` = 0-(`treeright`) WHERE `treeleft` >= ' . intval(\$child->treeleft) . ' AND `treeright` <= ' . intval(\$child->treeright),
			'UPDATE `".$n."` SET `treeleft` = `treeleft` - ' . \$size . ' WHERE `treeleft` > ' . intval(\$child->treeright),
			'UPDATE `".$n."` SET `treeright` = `treeright` - ' . \$size . ' WHERE `treeright` > ' . intval(\$child->treeright),
			'UPDATE `".$n."` SET `treeleft` = `treeleft` + ' . \$size . ' WHERE `treeleft` >= ' . intval(\$this->treeright > \$child->treeright ? \$this->treeright - \$size : \$this->treeright),
			'UPDATE `".$n."` SET `treeright` = `treeright` + ' . \$size . ' WHERE `treeright` >= ' . intval(\$this->treeright > \$child->treeright ? \$this->treeright - \$size : \$this->treeright),
			'UPDATE `".$n."` SET `treeleft` = 0-(`treeleft`)+' . (\$this->treeright > \$child->treeright ? \$this->treeright - \$child->treeright - 1 : \$this->treeright - \$child->treeright - 1 + \$size) . ', `treeright` = 0-(`treeright`)+' . (\$this->treeright > \$child->treeright ? \$this->treeright - \$child->treeright - 1 : \$this->treeright - \$child->treeright - 1 + \$size) . ' WHERE `treeleft` <= ' . (0-\$child->treeleft) . ' AND `treeright` >= ' . (0-\$child->treeright),
		);";
		$this->str .= "\n\t\tif (\$predecessor)\n\t\t{";
		$this->str .= "\n\t\t\t//\$sql[] = 'UPDATE `".$n."` SET `treeleft`=`treeleft`-'.\$x.', `treeright`=`treeright`-'.\$x.' WHERE `treeleft` BETWEEN ';";
		$this->str .= "\n\t\t\t//\$sql[] = '';";
		$this->str .= "\n\t\t}";
		
		$this->str .= "\n\t\ttry";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\tforeach (\$sql as \$query)\n\t\t\t{";
		$this->str .= "\n\t\t\t\t\$prepare = DB::instance($this->db)->query(\$query);";
		$this->str .= "\n\t\t\t}";
		
		$this->str .= "\n\t\t\t\$prepare = DB::instance($this->db)->prepare('UPDATE `".$n."` SET `treeparentid`=? WHERE `id`=?');";
		$this->str .= "\n\t\t\t\$prepare->execute(array(\$this->id, \$child->id));";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\tcatch(Exception \$e)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\treturn '[['.\$e->getMessage().']]';";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\treturn true;";
		$this->str .= "\n\t}";
		// Add-Function end
		
		
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Associates a Parent-Object to a Tree-Node/Branch", 
											array("object \$parent", "object \$predecessor (not used atm)"),'');
		$this->str .= "\tfunction Set".$n." (\$parent, \$predecessor=false)\n\t{";
		$this->str .= "\n\t\t\$parent->Add".$n."(\$this, \$predecessor=false);";
		$this->str .= "\n\t}";
		$this->str .= "\n\t\n\t";
		
		/*
		Detach Tree-Child/Branch
			step 1: set parentid=0 and temporary "remove" moving node
			step 2 (2 queries): decrease left and/or right position values of currently 'lower' items (and parents)
			step 3: move node (and it's subnodes) to the end and remove parentid from the first child
		*/
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Removes a Node/Branch from a Parent-Object", 
											array("object \$parent", "object \$predecessor"),'');
		$this->str .= "\tfunction Remove".$n." (\$child)\n\t{";
		
		
		$this->str .= "\n\t\t\$size = \$child->treeright - \$child->treeleft + 1;";
		$this->str .= "\n\t\t\$max = intval(DB::instance($this->db)->query('SELECT MAX(`treeright`) AS m FROM `".$n."`')->fetch()->m);";
	
		$this->str .= "\n\t\t\$sql = array (
			'UPDATE `".$n."` SET `treeleft` = 0-(`treeleft`), `treeright` = 0-(`treeright`) WHERE `treeleft` >= ' . intval(\$child->treeleft) . ' AND `treeright` <= ' . intval(\$child->treeright),
			'UPDATE `".$n."` SET `treeleft` = `treeleft` - ' . \$size . ' WHERE `treeleft` > ' . intval(\$child->treeright),
			'UPDATE `".$n."` SET `treeright` = `treeright` - ' . \$size . ' WHERE `treeright` > ' . intval(\$child->treeright),
			'UPDATE `".$n."` SET `treeleft` = 0-(`treeleft`)-' . intval(\$child->treeleft) . '+' . (\$max-\$size+1) . ', `treeright` = 0-(`treeright`)-' . intval(\$child->treeleft) . '+' . (\$max-\$size+1) . ' WHERE `treeright` < 0'
		);";
		
		$this->str .= "\n\t\t\t";
		$this->str .= "\n\t\tforeach (\$sql as \$query)\n\t\t{";
		$this->str .= "\n\t\t\tDB::instance($this->db)->query(\$query);";
		$this->str .= "\n\t\t}";
		
		$this->str .= "\n\t\t\t";
		$this->str .= "\n\t\t\$prepare = DB::instance($this->db)->prepare('UPDATE `".$n."` SET `treeparentid` = 0 WHERE `id` = ?');";
		$this->str .= "\n\t\t\$prepare->execute(array(\$child->id));";
		$this->str .= "\n\t\treturn true;";
		$this->str .= "\n\t}";
		$this->str .= "\n\t\n\t";
		
	}
	
	
	
		
	/*
	 * ziel:
	 * einen Objekt-Array des (Teil-)Graphen
	 * der Tiefe Z
	 * unterhalb des Knotens X
	 * 
	 * 
	 * */
	
	// creates a function to get the (Sub-)Graph
	function CreateGetGraphFunction()
	{
		$n = strtolower($this->objectName);
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Returns a hierachical array of objects that match given conditions", 
											array(	"mixed \$fcv {(\"field\", \"comparator\", \"value\"), (\"field\", \"comparator\", \"value\"), ...}",
													"array \$sort",
													"integer \$limit default unlimited",
													"integer \$offset default no Offset",
													(($this->manualId)?'string':'integer')." \$parentId 0 means top Level",
													"integer \$depth per default limited to 99 Levels", 
											),
											"array \$".$n."List");
		
		$this->str .= "\tfunction GetTreeList (\$fcv = array(), \$sort = array(), \$limit = 0, \$offset = 0, \$parentId = 0, \$depth = 99)\n\t{";
		$this->str .= "\n\t\t";
		
		$this->str .= "\n\t\t\$".$n."List = array();";
		$this->str .= "\n\t\t\$bindings = array();";
		$this->str .= "\n\t\t\$startDepth = 0;";
		$this->str .= "\n\t\t\$query = 'SELECT c.*, m.hops AS treelevel, (SELECT COUNT(id)-1 FROM `".$n."matrix` WHERE pid = m.id) AS treechilds FROM `".$n."` c JOIN `".$n."matrix` m ON (c.id = m.id) WHERE m.pid';";
		$this->str .= "\n\t\tif (\$parentId != 0)\n\t\t{";
		$this->str .= "\n\t\t\t\$query .= ' = ?';";
		$this->str .= "\n\t\t\t\$bindings[] = \$parentId;";
		$this->str .= "\n\t\t\t\$startDepth = 1;";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\telse";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$query .= ' NOT IN (SELECT id FROM `".$n."matrix` WHERE hops > 0) AND m.hops < 1';";
		$this->str .= "\n\t\t}";
		
		$this->str .= "\n\t\tif (sizeof(\$fcv) > 0)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$whereArray = array();";
		
		$this->str .= "\n\t\t\tforeach (\$fcv as \$a)";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\tif (count(\$a) === 1 && is_string(\$a[0]))";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\$whereArray[] = trim(DB::instance($this->db)->quote(\$a), '\'');";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t\telse if (is_array(\$a))";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\tif (is_array(\$a[0]))";
		$this->str .= "\n\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\$orArray = array();";
		$this->str .= "\n\t\t\t\t\t\tforeach (\$a as \$o)";
		$this->str .= "\n\t\t\t\t\t\t{";
		
		$this->str .= "\n\t\t\t\t\t\t\tif (in_array(\$o[2], \$this->__columns['".$this->objectName."'], true))";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '`' . \$o[2] . '`';";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '?';";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$bindings[] = \$o[2];";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\t\$orArray[] = 'a.`' . \$o[0] . '` ' . \$o[1] . ' ' . \$value;";
		$this->str .= "\n\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\$whereArray[] = '(' . implode(' OR ', \$orArray) . ')';";
		$this->str .= "\n\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t{";
		
		$this->str .= "\n\t\t\t\t\t\t\tif (in_array(\$a[2], \$this->__columns['".$this->objectName."'], true))";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '`' . \$a[2] . '`';";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '?';";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$bindings[] = \$a[2];";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\$whereArray[] = 'a.`' . \$a[0] . '` ' . \$a[1] . ' ' . \$value;";
		
		$this->str .= "\n\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t\t\$query .= ' AND ' . implode(' AND ', \$whereArray);";
		$this->str .= "\n\t\t}";
		
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\t\$sortArray = array();";
		$this->str .= "\n\t\tforeach (\$sort as \$field => \$dir)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\tif (strtolower(\$dir) === 'asc' || strtolower(\$dir) === 'desc')";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\t\$sortArray[] = 'c.`' . preg_replace('/\W/','', \$field) . '` ' . \$dir;";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\t\$sortArray[] = 'm.`pid` ASC, m.`sort` ASC, m.`hops` ASC';";
		
		
		//$this->str .= "\n\t\t\$query .= ' ORDER BY ' . ((count(\$sortByArray)>0) ? implode(', ', \$sortByArray) : 'm.`pid` ASC, m.`sort` ASC, m.`hops` ASC');";
		//$this->str .= "\n\t\t\$query .= ' ORDER BY m.`pid` ASC, m.`sort` ASC, m.`hops` ASC';";
		
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\t\$sqlLimit = (intval(\$limit) > 0 ? ' LIMIT ' . \$limit : '');";
		$this->str .= "\n\t\t\$sqlOffset = (intval(\$offset) > 0 ? ' OFFSET ' . \$offset : '');";
		
		$this->str .= "\n\t\t";
		
		
		$this->str .= "\n\t\t\$query .= ' ORDER BY ' . implode(',', \$sortArray) . \$sqlLimit . \$sqlOffset;";
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\t\$object = get_class(\$this);";
		$this->str .= "\n\t\ttry";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$prepare = DB::instance($this->db)->prepare(\$query);";
		
		$this->str .= "\n\t\t\t\$prepare->execute(\$bindings);";
		$this->str .= "\n\t\t\t\$list = \$prepare->fetchAll();";
		
		$this->str .= "\n\t\t\t";
		//$this->str .= "\n\t\t\t\$thisObjectName = get_class(\$this);";
		$this->str .= "\n\t\t\tforeach (\$list as \$key => \$lst)";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\tif (\$lst->treelevel >= \$startDepth && \$lst->treelevel <= \$depth)";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\$".$n." = new ".$n."();";// define new object
		//$this->str .= "\n\t\t\t\t\t\$".$n." = \$thisObjectName::instance();";// define new object
		$this->str .= "\n\t\t\t\t\tforeach (\$lst as \$key => \$val)";
		$this->str .= "\n\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\$".$n."->\$key = \$val;";
		$this->str .= "\n\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\$".$n."List[] = \$".$n.";";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t}";
		
		
		$this->str .= "\n\t\t\treturn \$".$n."List;";
		
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\tcatch (Exception \$e)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\treturn '[['.\$e->getMessage().']]';";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\t";
		
		$this->str .= "\n\t}";
	}
	
	// hänge kind an eltern-element
	function CreateAddGraphChildFunction()
	{
		$n = strtolower($this->objectName);
		
		
		$this->str .= "\n\t\n\t";
		$this->str .= "\n\t\n\t";
		
		$this->str .= $this->CreateComments("Associates a Node/Branch of a Graph to a Parent-Object", 
											array("object \$child", "object \$predecessor (not used atm)"),'');
		$this->str .= "\tfunction Add".$n." (\$child, \$predecessor=false)\n\t{";
		$this->str .= "\n\t\treturn \$this->setTreeMapping(\$child, \$predecessor, false);";
		$this->str .= "\n\t}";
		$this->str .= "\n\t\n\t";
		
		$this->str .= $this->CreateComments("Associates a Parent-Object to a Node/Branch as Child", 
											array("object \$parent", "object \$predecessor"),'');
		$this->str .= "\tfunction Set".$n." (\$parent, \$predecessor=false)\n\t{";
		$this->str .= "\n\t\treturn \$parent->setTreeMapping(\$this, \$predecessor, false);";
		$this->str .= "\n\t}";
		$this->str .= "\n\t\n\t";
		
		$this->str .= $this->CreateComments("Removes a Node/Branch from a Parent-Object", 
											array("object \$child"),'');
		$this->str .= "\tfunction Remove".$n." (\$child)\n\t{";
		$this->str .= "\n\t\treturn \$this->setTreeMapping(\$child, false, true);";
		$this->str .= "\n\t}";
		$this->str .= "\n\t\n\t";
		
		
		// internal function to handle the three functions above
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Associates a Node/Branch of a Graph to a Parent-Object", 
											array("object \$child", "object \$predecessor (not used atm)", "bool \$delete"),
											'');
		
		$this->str .= "\tprivate function setTreeMapping (\$child, \$predecessor=false, \$delete=false)\n\t{";
		
		$this->str .= "\n\t\t\$parents = array();";
		$this->str .= "\n\t\t";
		
		// get all parents from parent-element (including itself)
		$this->str .= "\n\t\t// get all parents from parent-element (including itself)";
		$this->str .= "\n\t\t\$prepare = DB::instance($this->db)->prepare('SELECT `pid`, `hops` FROM `".$n."matrix` WHERE `id`=?');";
		$this->str .= "\n\t\t\$prepare->execute(array(\$this->id));";
		$this->str .= "\n\t\twhile (\$row = \$prepare->fetch())\n\t\t{";
		$this->str .= "\n\t\t\t// Recursion-Check";
		$this->str .= "\n\t\t\tif (\$row->pid == \$child->id)";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\treturn false;";
		$this->str .= "\n\t\t\t}";
		
		
		$this->str .= "\n\t\t\t\$parents[] = array(\$row->pid, \$row->hops);";
		//$this->str .= "\n\t\t\t";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\t";
		
		// get all childs from child-element (including itself)
		$this->str .= "\n\t\t// get all childs from child-element (including itself)";
		$this->str .= "\n\t\t\$prepare0 = DB::instance($this->db)->prepare('SELECT `id`, `hops` FROM `".$n."matrix` WHERE `pid`=?');";
		$this->str .= "\n\t\t\$prepare0->execute(array(\$child->id));";
		$this->str .= "\n\t\tif (\$delete)\n\t\t{";
		$this->str .= "\n\t\t\t\$prepare1 = DB::instance($this->db)->prepare('DELETE FROM `".$n."matrix` WHERE `pid`=? AND `id`=? AND `hops`=?');";
		$this->str .= "\n\t\t}\n\t\telse\n\t\t{";
		$this->str .= "\n\t\t\t\$prepare1 = DB::instance($this->db)->prepare('INSERT INTO `".$n."matrix` (`pid`, `id`, `hops`, `sort`) VALUES (?,?,?,0)');";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\twhile (\$row = \$prepare0->fetch())\n\t\t{";
		$this->str .= "\n\t\t\tforeach (\$parents as \$p)\n\t\t\t{";
		$this->str .= "\n\t\t\t\ttry\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\$prepare1->execute(array(\$p[0], \$row->id, (\$p[1]+\$row->hops+1)));";
		$this->str .= "\n\t\t\t\t}\n\t\t\t\tcatch (Exception \$e)\n\t\t\t\t{\n\t\t\t\t\treturn false;\n\t\t\t\t}";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\t";
		
		$this->str .= "\n\t\treturn true;";
		$this->str .= "\n\t}";
	}
	
	
	
	// -------------------------------------------------------------
	function CreateSaveFunction($deep = false)
	{
		$n = strtolower($this->objectName);
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Saves the Object to the Database",
											array('bool $deep'),
											(($this->manualId)?'string':'int')." \$id");
		if ($deep)
		{
			$this->str .= "\tfunction Save (\$deep = true)\n\t{";
		}
		else
		{
			$this->str .= "\tfunction Save ()\n\t{";
		}
		$this->str .= "\n\t\t\$prepare = DB::instance($this->db)->prepare('SELECT `id` FROM `".$n."` WHERE `id`=? LIMIT 1;');";
		$this->str .= "\n\t\t\$prepare->execute(array(\$this->id));";//
		$this->str .= "\n\t\t\$result = \$prepare->fetchAll();\n\t\t";
		
		$this->str .= "\n\t\tif (count(\$result) === 1)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$mid = \$this->id;";
		$this->str .= "\n\t\t\t\$query = 'UPDATE `".$n."` SET ";
		
		
		$tmp0 = array();
		$tmp1 = array();
		foreach ($this->elementList['col'] as $key => $attr)
		{
			
			if($key != 'id') {
				$tmp0[] = "`".strtolower($key)."`=:".$key;
			}
			$tmp1[] = "':".$key."'=>\$this->".$key;
		}
		$this->str .= implode(', ', $tmp0);
		
		$this->str .= " WHERE `id`=:id';";
		$this->str .= "\n\t\t\t\$map = array(".implode(', ', $tmp1).");";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\telse";
		$this->str .= "\n\t\t{";
		
		//
		$tmp0 = array();
		$tmp1 = array();
		$tmp2 = array();
		
		// add manual ID-Insertion
		if($this->manualId)
		{
			$tmp0[] = '`id`';
			$tmp1[] = ":id";
			$tmp2[] = "':id'=>\$mid";
			$this->str .= "\n\t\t\t\$mid = DB::uid();";
		}
		
		
		if($this->treeType=='Tree')
		{
			$this->str .= "\n\t\t\t";
			$this->str .= "\n\t\t\t\$this->treeparentid = 0;";
			$this->str .= "\n\t\t\t\$this->treeleft = intval(DB::instance($this->db)->query('SELECT MAX(`treeright`) AS m FROM `".$n."`')->fetch()->m) + 1;";
			$this->str .= "\n\t\t\t\$this->treeright = \$this->treeleft + 1;";
			$this->str .= "\n\t\t\t";
		}
		
		
		
		$this->str .= "\n\t\t\t\$query = 'INSERT INTO `".$n."` (";
		
		foreach ($this->elementList['col'] as $key => $attr)
		{
			if($key != 'id') {
				$tmp0[] = '`'.$key.'`';
				$tmp1[] = ':'.$key;
				$tmp2[] = "':".$key."'=>\$this->".$key;
			}
		}
		$this->str .= implode(', ', $tmp0);
		$this->str .= ") VALUES (";
		$this->str .= implode(', ', $tmp1);
		$this->str .= ")';";
		$this->str .= "\n\t\t\t\$map = array(".implode(', ', $tmp2).");";
		
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\t";
		
		$this->str .= "\n\t\ttry";
		$this->str .= "\n\t\t{";
		
		$this->str .= "\n\t\t\t\$prepare = DB::instance($this->db)->prepare(\$query);";
		$this->str .= "\n\t\t\t\$prepare->execute(\$map);";
		$this->str .= "\n\t\t\tif (!isset(\$mid))";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\t\$mid = DB::instance($this->db)->lastInsertId();";
		$this->str .= "\n\t\t\t}";
		
		$this->str .= "\n\t\t\t\$this->id = \$mid;";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\tcatch (Exception \$e)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\treturn '[['.\$e->getMessage().']]';";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\t";
		
		
		if ($deep)
		{
			$this->str .= "\n\t\tif (\$deep)";
			$this->str .= "\n\t\t{";
			if(isset($this->elementList['rel']))
			{
				foreach ($this->elementList['rel'] as $key => $attr)
				{
					if ($attr == 'c')
					{
						$this->str .= "\n\t\t\tforeach (\$this->_".strtolower($key)."List as $".strtolower($key).")";
						$this->str .= "\n\t\t\t{";
						$this->str .= "\n\t\t\t\t\$".strtolower($key)."->".$n."id = \$this->id;";
						$this->str .= "\n\t\t\t\t\$".strtolower($key)."->Save(\$deep);";
						$this->str .= "\n\t\t\t}";
					}
					else if ($attr == 's')
					{
						$this->str .= "\n\t\t\tforeach (\$this->_".strtolower($key)."List as $".strtolower($key).")";
						$this->str .= "\n\t\t\t{";
						$this->str .= "\n\t\t\t\t\$".strtolower($key)."->Save();";
						
						$this->str .= "\n\t\t\t\t\$map = new ".$this->MappingName($this->objectName, $key, 'Map', false)."();";
						$this->str .= "\n\t\t\t\t\$map->AddMapping(\$this, \$".$key.");";
						$this->str .= "\n\t\t\t}";
					}
				}
			}
			$this->str .= "\n\t\t}";
		}
		
		
		if($this->treeType=='Graph')
		{
			$this->str .= "\n\t\t";
			$this->str .= "\n\t\tif (count(\$result) == 0)";
			$this->str .= "\n\t\t{";
			$this->str .= "\n\t\t\t\$prepare = DB::instance($this->db)->prepare('INSERT INTO `".$n."matrix` (`pid`, `id`, `hops`, `sort`) VALUES (:id, :id, 0, 0)');";
			$this->str .= "\n\t\t\t\$prepare->execute(array(':id'=>\$this->id));";
			$this->str .= "\n\t\t}";
		}
		
		$this->str .= "\n\t\treturn \$this->id;";
		$this->str .= "\n\t}";
	}
	

	
	// -------------------------------------------------------------
	function CreateSaveNewFunction($deep = false)
	{
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Clones the object and saves it to the database",
											array('bool $deep'),
											(($this->manualId)?'string':'int')." \$id");
		if ($deep)
		{
			$this->str .="\tfunction SaveNew (\$deep = false)\n\t{";
		}
		else
		{
			$this->str .="\tfunction SaveNew ()\n\t{";
		}
		$this->str .= "\n\t\t\$this->id = '';";
		
		if ($deep)
		{
			$this->str .= "\n\t\treturn \$this->Save(\$deep);";
		}
		else
		{
			$this->str .= "\n\t\treturn \$this->Save();";
		}
		$this->str .= "\n\t}";
	}

	
	// -------------------------------------------------------------
	function CreateDeleteFunction($deep = false)
	{
		$n = strtolower($this->objectName);
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Deletes the object from the database",
											array('bool $deep','bool $across'),
											"bool successfull deleted");
		if ($deep)
		{
			$this->str .= "\tfunction Delete (\$deep = false, \$across = false)\n\t{";
		}
		else
		{
			$this->str .= "\tfunction Delete ()\n\t{";
		}
		
		if ($deep)
		{
			
				
			$tmp = '';
			if(isset($this->elementList['rel']))
			{
				foreach ($this->elementList['rel'] as $key => $attr)//$this->typeList as $type
				{
					if ($attr == 'c')
					{
						$tmp .= "\n\t\t\t$".strtolower($key)."List = \$this->Get".strtolower($key)."List();";
						$tmp .= "\n\t\t\tforeach ($".strtolower($key)."List as $".strtolower($key).")";
						$tmp .= "\n\t\t\t{";
						$tmp .= "\n\t\t\t\t\$".strtolower($key)."->Delete(\$deep, \$across);";
						$tmp .= "\n\t\t\t}";
					}
				}
			}
			if ($tmp != '')
			{
				$this->str .= "\n\t\tif (\$deep)";
				$this->str .= "\n\t\t{";
				$this->str .= $tmp;
				$this->str .= "\n\t\t}";
			}
			
			
			$tmp = $tmp1 = '';
			if(isset($this->elementList['rel']))
			{
				foreach ($this->elementList['rel'] as $key => $attr)
				{
					if ($attr == 's')
					{
						$tmp .= "\n\t\t\t$".strtolower($key)."List = \$this->Get".strtolower($key)."List();";
						$map = $this->MappingName($this->objectName, $key, 'Map', false);
						$tmp .= "\n\t\t\t\$map = new ".$map."();";
						$tmp .= "\n\t\t\t\$map->RemoveMapping(\$this);";
						$tmp .= "\n\t\t\tforeach (\$".strtolower($key)."List as \$".strtolower($key).")";
						$tmp .= "\n\t\t\t{";
						$tmp .= "\n\t\t\t\t\$".strtolower($key)."->Delete(\$deep, \$across);";
						$tmp .= "\n\t\t\t}";
						
						$tmp1 .= "\n\t\t\t\$map = new ".$map."();";
						$tmp1 .= "\n\t\t\t\$map->RemoveMapping(\$this);";
					}
				}
			}
			if ($tmp != '')
			{
				$this->str .= "\n\t\tif (\$across)";
				$this->str .= "\n\t\t{";
				$this->str .= $tmp;
				$this->str .= "\n\t\t}";
				$this->str .= "\n\t\telse";
				$this->str .= "\n\t\t{";
				$this->str .= $tmp1;
				$this->str .= "\n\t\t}";
			}
			
		}
		
		
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\ttry";
		$this->str .= "\n\t\t{";
		
		$this->str .= "\n\t\t\t\$prepare = DB::instance($this->db)->prepare('DELETE FROM `".$n."` WHERE `id`=:id');";
		$this->str .= "\n\t\t\t\$prepare->execute(array(':id'=>\$this->id));";
		
		if($this->treeType=='Tree')
		{
			$this->str .= "\n\t\t\t";
			$this->str .= "\n\t\t\t\$prepare0 = DB::instance($this->db)->prepare('UPDATE `".$n."` SET `treeparentid`=:pid WHERE `treeparentid`=:id');";
			$this->str .= "\n\t\t\t\$prepare0->execute(array(':pid'=>\$this->treeparentid,':id'=>\$this->id));";
			$this->str .= "\n\t\t\t\$prepare1 = DB::instance($this->db)->prepare('UPDATE `".$n."` SET `treeleft`=`treeleft`-1, `treeright`=`treeright`-1 WHERE `treeleft` BETWEEN :lft AND :rgt');";
			$this->str .= "\n\t\t\t\$prepare1->execute(array(':lft'=>\$this->treeleft,':rgt'=>\$this->treeright));";
			$this->str .= "\n\t\t\t\$prepare2 = DB::instance($this->db)->prepare('UPDATE `".$n."` SET `treeleft`=`treeleft`-2 WHERE `treeleft`>:rgt');";
			$this->str .= "\n\t\t\t\$prepare2->execute(array(':rgt'=>\$this->treeright));";
			$this->str .= "\n\t\t\t\$prepare3 = DB::instance($this->db)->prepare('UPDATE `".$n."` SET `treeright`=`treeright`-2 WHERE `treeright`>:rgt');";
			$this->str .= "\n\t\t\t\$prepare3->execute(array(':rgt'=>\$this->treeright));";
		}
		
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\tcatch (Exception \$e)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\treturn '[['.\$e->getMessage().']]';";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\t";
		
		if($this->treeType=='Graph')
		{
			$this->str .= "\n\t\ttry";
			$this->str .= "\n\t\t{";
			$this->str .= "\n\t\t\t\$prepare0 = DB::instance($this->db)->prepare('SELECT `id` FROM `".$n."matrix` WHERE `pid`=?');";
			$this->str .= "\n\t\t\t\$prepare0->execute(array(\$this->id));";
			$this->str .= "\n\t\t\t\$bindings = array();";
			$this->str .= "\n\t\t\t\$placeholders = array();";
			$this->str .= "\n\t\t\twhile (\$row = \$prepare0->fetch())\n\t\t\t{";
			$this->str .= "\n\t\t\t\t\$bindings[] = \$row->id;";
			$this->str .= "\n\t\t\t\t\$placeholders[] = '?';";
			$this->str .= "\n\t\t\t}";
			
			$this->str .= "\n\t\t\t\$prepare2 = DB::instance($this->db)->prepare('DELETE FROM `".$n."matrix` WHERE `pid` IN (' . implode(',', \$placeholders) . ')');";
			$this->str .= "\n\t\t\t\$prepare2->execute(\$bindings);";
			$this->str .= "\n\t\t}";
			$this->str .= "\n\t\tcatch (Exception \$e)";
			$this->str .= "\n\t\t{";
			$this->str .= "\n\t\t\treturn '[['.\$e->getMessage().']]';";
			$this->str .= "\n\t\t}";
			$this->str .= "\n\t\t";
		}
		
		
		$this->str .= "\n\t\treturn true;";
		$this->str .= "\n\t}";
	}
	
	
	
	// --------------------- NOT USED ATM ----------------------------------------
	function CreateDeleteListFunction($deep = false)
	{
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Deletes a list of objects that match given conditions",
											array("multidimensional array {(\"field\", \"comparator\", \"value\"), (\"field\", \"comparator\", \"value\"), ...}","bool \$deep","bool \$across"));
		if ($deep)
		{
			$this->str .= "\tfunction DeleteList (\$fcv, \$deep = false, \$across = false)\n\t{";
		}
		else
		{
			$this->str .= "\tfunction DeleteList (\$fcv)\n\t{";
		}
		$this->str .= "\n\t\tif (sizeof(\$fcv) > 0)";
		$this->str .= "\n\t\t{";
		
		if ($deep)
		{
			$this->str .= "\n\t\t\tif (\$deep || \$across)";
			$this->str .= "\n\t\t\t{";
			$this->str .= "\n\t\t\t\t\$objectList = \$this->GetList(\$fcv);";
			$this->str .= "\n\t\t\t\tforeach (\$objectList as \$object)";
			$this->str .= "\n\t\t\t\t{";
			$this->str .= "\n\t\t\t\t\t\$object->Delete(\$deep, \$across);";
			$this->str .= "\n\t\t\t\t}";
			$this->str .= "\n\t\t\t}";
			$this->str .= "\n\t\t\telse";
			$this->str .= "\n\t\t\t{";
			
		}
		
		$this->str .= "\n\t\t\t\t\$whereArray = array();";
		$this->str .= "\n\t\t\t\t\$bindings = array();";
		$this->str .= "\n\t\t\t\tforeach (\$fcv as \$a)";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\tif (count(\$a) === 1 && is_string(\$a[0]))";
		$this->str .= "\n\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\$whereArray[] = trim(DB::instance($this->db)->quote(\$a), '\'');";
		$this->str .= "\n\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\telse if (is_array(\$a))";
		$this->str .= "\n\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\tif (is_array(\$a[0]))";
		$this->str .= "\n\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\$orArray = array();";
		$this->str .= "\n\t\t\t\t\t\t\tforeach (\$a as \$o)";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		
		$this->str .= "\n\t\t\t\t\t\t\t\tif (in_array(\$o[2], \$this->__columns['".$this->objectName."']))";
		$this->str .= "\n\t\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\t\$value = '`' . \$o[2] . '`';";
		$this->str .= "\n\t\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\t\$value = '?';";
		$this->str .= "\n\t\t\t\t\t\t\t\t\t\$bindings[] = \$o[2];";
		$this->str .= "\n\t\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$orArray[] = '`' . \$o[0] . '` ' . \$o[1] . ' ' . \$value;";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\t\$whereArray[] = '(' . implode(' OR ', \$orArray) . ')';";
		$this->str .= "\n\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t\t{";
		
		$this->str .= "\n\t\t\t\t\t\t\tif (in_array(\$a[2], \$this->__columns['".$this->objectName."']))";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '`' . \$a[2] . '`';";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '?';";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$bindings[] = \$a[2];";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\t\$whereArray[] = '`' . \$a[0] . '` ' . \$a[1] . ' ' . \$value;";
		
		$this->str .= "\n\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\$query = 'DELETE FROM `".strtolower($this->objectName)."` WHERE ' . implode(' AND ', \$whereArray);";
		
		
		
		$this->str .= "\n\t\t\t\t";
		$this->str .= "\n\t\t\t\ttry";
		$this->str .= "\n\t\t\t\t{";
		
		$this->str .= "\n\t\t\t\t\t\$prepare = DB::instance($this->db)->prepare(\$query);";
		$this->str .= "\n\t\t\t\t\t\$prepare->execute(\$bindings);";
		
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t\tcatch (Exception \$e)";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\treturn '[['.\$e->getMessage().']]';";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t\t";
		
		if ($deep)
		{
			$this->str .= "\n\t\t\t}";
		}
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t}";
	}
	
	/////////////////////////////////////////////////////////////////////////////////////////////////	
	
	// -------------------------------------------------------------
	function CreateReferencesFunction()
	{
		if(isset($this->elementList['rel']))
		{
			foreach ($this->elementList['rel'] as $key => $attr)
			{
				if ($attr == 'c')
				{
					$this->CreateGetChildrenFunction($key);
					$this->CreateSetChildrenFunction($key);
					$this->CreateAddChildFunction($key);
				}
				if ($attr == 'p')
				{
					//$name = substr($key,0,-2);
					$this->CreateGetParentFunction($key);
					$this->CreateSetParentFunction($key);
				}
				if ($attr == 's')
				{
					$this->CreateGetAssociationsFunction($key);
					$this->CreateSetAssociationsFunction($key);
					$this->CreateAddAssociationFunction($key);
					
					
					// create the Mapping-Class
					if($this->savePath)
					{
						new ObjectMap($this->objectName, $key, $this->savePath, $this->manualId, $this->db);
					}
					
				}
			}
		}
	}
	
	// Relations Children
	// -------------------------------------------------------------
	function CreateGetChildrenFunction($child)
	{
		$childLower = strtolower($child);
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Gets a list of $child objects associated to this one", 
											array("multidimensional array {(\"field\", \"comparator\", \"value\"), (\"field\", \"comparator\", \"value\"), ...}","array \$sortBy","int \$limit","int \$offset"),
											"array of $child objects");
		
		$this->str .= "\tfunction Get".$child."List (\$fcv = array(), \$sortBy = array(), \$limit = 0, \$offset = 0)\n\t{";
		$this->str .= "\n\t\t\$".$childLower." = new ".$child."();";
		$this->str .= "\n\t\t\$fcv[] = array('".strtolower($this->objectName)."id', '=', \$this->id);";
		$this->str .= "\n\t\t\$dbObjects = \$".$childLower."->GetList(\$fcv, \$sortBy, \$limit, \$offset);";
		$this->str .= "\n\t\treturn \$dbObjects;";
		$this->str .= "\n\t}";
	}
	
	// -------------------------------------------------------------
	function CreateSetChildrenFunction($child)
	{
		$childLower = strtolower($child);
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Makes this the parent of all $child objects in the $child List array. Any existing $child will become orphan(s)",
											array('array List of objects'),
											"null");
		
		$this->str .= "\tfunction Set".$child."List (&\$list)\n\t{";
		$this->str .= "\n\t\t\$this->_".$childLower."List = array();";
		$this->str .= "\n\t\t\$existing".$child."List = \$this->Get".$child."List();";
		$this->str .= "\n\t\tforeach (\$existing".$child."List as \$".$childLower.")";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$".$childLower."->".strtolower($this->objectName)."id = '';";
		$this->str .= "\n\t\t\t\$".$childLower."->Save(false);";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\t\$this->_".$childLower."List = \$list;";
		$this->str .= "\n\t}";
	}
	// -------------------------------------------------------------
	function CreateAddChildFunction($child)
	{
		$childLower = strtolower($child);
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Associates the $child object to this one",
											array('object $'.$childLower),
											'');
											
		$this->str .= "\tfunction Add".$childLower." (&\$".$childLower.")\n\t{";
		$this->str .= "\n\t\t\$".$childLower."->".strtolower($this->objectName)."id = \$this->id;";
		$this->str .= "\n\t\t\$found = false;";
		$this->str .= "\n\t\tforeach (\$this->_".$childLower."List as \$".$childLower."2)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\tif (\$".$childLower."->id == \$".$childLower."2->id)";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\t\$found = true;";
		$this->str .= "\n\t\t\t\tbreak;";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\tif (!\$found)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$this->_".$childLower."List[] = \$".$childLower.";";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t}";
	}
	
	// Relations Parents
	
	// -------------------------------------------------------------
	function CreateGetParentFunction($parent)
	{
		$parentLower = strtolower($parent);
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Gets the parent object \"$parent\" if any",
											'',
											"object $parent");
		
		$this->str .= "\tfunction Get".$parent." ()\n\t{";
		$this->str .= "\n\t\t\$".$parentLower." = new ".$parent."();";
		$this->str .= "\n\t\treturn $".$parentLower."->Get(\$this->".$parentLower."id);";
		$this->str .= "\n\t}";
	}
	
	
	// -------------------------------------------------------------
	function CreateSetParentFunction($parent)
	{
		$parentLower = strtolower($parent);
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Associates the parent object \"$parent\" to this one",
											array('object $'.$parent.' the new Parent-Object'),
											'');
		$this->str .= "\tfunction Set".$parent." (&\$".$parentLower.")\n\t{";
		$this->str .= "\n\t\t\$this->".$parentLower."id = $".$parentLower."->id;";
		$this->str .= "\n\t}";
	}

	


	// Relations {Many-Many} functions
	
	//-------------------------------------------------------------
	function CreateGetAssociationsFunction($sibling)
	{
		$siblingLower = strtolower($sibling);
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Returns a sorted array of objects that match given conditions", 
											array(	"mixed \$fcv {(\"field\", \"comparator\", \"value\"), (\"field\", \"comparator\", \"value\"), ...}",
													"array \$sortBy",
													"int \$limit",
													"int \$offset"),
											"array \$".$siblingLower."List");
		
		$this->str .= "\tfunction Get".$sibling."List (\$fcv = array(), \$sortBy = array(), \$limit = 0, \$offset = 0)\n\t{";
		
		$this->str .= "\n\t\t\$".$siblingLower."List = array();";
		$this->str .= "\n\t\t\$bindings = array(\$this->id);";
		$this->str .= "\n\t\t\$query = 'SELECT DISTINCT * FROM `".$siblingLower."` a INNER JOIN `".strtolower($this->MappingName($this->objectName, $sibling))."` m ON m.".$siblingLower."id = a.id WHERE m.".strtolower($this->objectName)."id = ?';";//'.\$this->".strtolower($this->objectName)."Id;";
		
		
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\tif (sizeof(\$fcv) > 0)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$whereArray = array();";
		
		$this->str .= "\n\t\t\tforeach (\$fcv as \$a)";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\tif (count(\$a) === 1 && is_string(\$a[0]))";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\$whereArray[] = trim(DB::instance($this->db)->quote(\$a), '\'');";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t\telse if (is_array(\$a))";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\tif (is_array(\$a[0]))";
		$this->str .= "\n\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\$orArray = array();";
		$this->str .= "\n\t\t\t\t\t\tforeach (\$a as \$o)";
		$this->str .= "\n\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\tif (in_array(\$o[2], \$this->__columns['".$sibling."'], true))";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '`' . \$o[2] . '`';";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '?';";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$bindings[] = \$o[2];";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\t\$orArray[] = '`' . \$o[0] . '` ' . \$o[1] . ' ' . \$value;";
		$this->str .= "\n\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\$whereArray[] = '(' . implode(' OR ', \$orArray) . ')';";
		$this->str .= "\n\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t{";
		
		$this->str .= "\n\t\t\t\t\t\t\tif (in_array(\$a[2], \$this->__columns['".$sibling."'], true))";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '`' . \$a[2] . '`';";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\telse";
		$this->str .= "\n\t\t\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$value = '?';";
		$this->str .= "\n\t\t\t\t\t\t\t\t\$bindings[] = \$a[2];";
		$this->str .= "\n\t\t\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t\t\t\$whereArray[] = '`' . \$a[0] . '` ' . \$a[1] . ' ' . \$value;";
		
		$this->str .= "\n\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t\t\$query .= ' AND ' . implode(' AND ', \$whereArray);";
		
		$this->str .= "\n\t\t}";
		
		
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\t\$sortByArray = array();";
		$this->str .= "\n\t\tforeach (\$sortBy as \$field => \$direction)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\tif (strtolower(\$direction) === 'asc' || strtolower(\$direction) === 'desc')";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\t\$sortByArray[] = 'a.' . trim(DB::instance($this->db)->quote(\$field), '\'') . ' ' . \$direction;";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t}";
		
		$this->str .= "\n\t\tif (!isset(\$sortBy['".$siblingLower."id']))";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$sortByArray[] = 'm.".$siblingLower."sort ASC, a.id ASC';";
		$this->str .= "\n\t\t}";
		
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\t\$sqlLimit = (intval(\$limit) > 0 ? ' LIMIT '.\$limit : '');";
		$this->str .= "\n\t\t\$sqlOffset = (intval(\$offset) > 0 ? ' OFFSET '.\$offset : '');";
		
		$this->str .= "\n\t\t\$query .= ' ORDER BY ' . implode(', ', \$sortByArray) . \$sqlLimit . \$sqlOffset;";
		
		$this->str .= "\n\t\t\$prepare = DB::instance($this->db)->prepare(\$query);";
		$this->str .= "\n\t\t\$prepare->execute(\$bindings);";
		$this->str .= "\n\t\t\$list = \$prepare->fetchAll();";
		
		$this->str .= "\n\t\t";
		$this->str .= "\n\t\tforeach (\$list as \$key => \$lst)";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\t\$".$siblingLower." = new ".$sibling."();";
		
		$this->str .= "\n\t\t\tforeach (\$lst as \$key => \$val)";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\t\$".$siblingLower."->\$key = \$val;";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t\t\$".$siblingLower."List[] = \$".$siblingLower.";";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t\treturn \$".$siblingLower."List;";
		
		
		$this->str .= "\n\t}";
	}
	
	// -------------------------------------------------------------
	function CreateSetAssociationsFunction($sibling)
	{
		$siblingLower = strtolower($sibling);
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Creates mappings between this and all objects in the $sibling List array. Any existing mapping will become orphan(s)",
											array('array $'.$siblingLower.'List'),
											'');
		
		
		$this->str .= "\tfunction Set".$sibling."List (&\$".$siblingLower."List)\n\t{";
		
		
		$this->str .= "\n\t\t\$map = new ".$this->MappingName($this->objectName, $sibling,'Map',false)."();";
		$this->str .= "\n\t\t\$map->RemoveMapping(\$this);";
		$this->str .= "\n\t\t\$this->_".$siblingLower."List = \$".$siblingLower."List;";
		$this->str .= "\n\t}";
	}
	
	// -------------------------------------------------------------
	function CreateAddAssociationFunction($sibling)
	{
		$siblingLower = strtolower($sibling);
		
		$this->str .= "\n\t\n\t";
		$this->str .= $this->CreateComments("Associates the $sibling object to this one",'',"");
		$this->str .= "\tfunction Add".$sibling." (&\$".$siblingLower.")\n\t{";
		$this->str .= "\n\t\tif (\$".$siblingLower." instanceof ".$sibling.")";
		$this->str .= "\n\t\t{";
		$this->str .= "\n\t\t\tif (in_array(\$this, \$".$siblingLower."->".$this->objectName."List, true))";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\treturn false;";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t\telse";
		$this->str .= "\n\t\t\t{";
		$this->str .= "\n\t\t\t\t\$found = false;";
		$this->str .= "\n\t\t\t\tforeach (\$this->_".$siblingLower."List as \$".$siblingLower."2)";
		$this->str .= "\n\t\t\t\t{";
		
		$this->str .= "\n\t\t\t\t\tif (\$".$siblingLower."->id == \$".$siblingLower."2->id)";
		$this->str .= "\n\t\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\t\$found = true;";
		$this->str .= "\n\t\t\t\t\t\tbreak;";
		$this->str .= "\n\t\t\t\t\t}";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t\tif (!\$found)";
		$this->str .= "\n\t\t\t\t{";
		$this->str .= "\n\t\t\t\t\t\$".$siblingLower."->".$this->objectName."sort = count(\$this->_".$siblingLower."List);";
		$this->str .= "\n\t\t\t\t\t\$this->_".$siblingLower."List[] = \$".$siblingLower.";";
		$this->str .= "\n\t\t\t\t}";
		$this->str .= "\n\t\t\t}";
		$this->str .= "\n\t\t}";
		$this->str .= "\n\t}";
	}
	
	
	
	// -------------------------------------------------------------
	function EndObject()
	{
		$this->str .= "\n}\n?>";
	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////// Create Mapping Object Code ///////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


class ObjectMap
{
 	var $mstr;

	var $object1;
	var $object2;
	var $savePath = false;
	
	
	function MappingName($objectName1, $objectName2, $add='Map', $lower=true, $as_array=false)
	{
		$array = array($objectName1, $objectName2);
		natcasesort($array);
		$str = array_shift($array) . $array[0];
		return  ($lower ? strtolower($str) : $str) . $add;
	}
	
	
	function __construct($object1, $object2, $save=false, $manualId, $db)
	{
		
		// enforce alphabetical Order of Object-Names
		$arr = array($object1, $object2);
		natcasesort($arr);
		$this->object1 = array_shift($arr);
		$this->object2 = array_shift($arr);
		
		$this->manualId  = $manualId;
		$this->db = $db;
		
		
		$this->savePath = $save;
		
		$this->BeginObject();
		
		
		$this->CreateSingleton();
		
		$this->CreateSaveFunction();
		$this->CreateAddMappingFunction();
		$this->CreateRemoveMappingFunction();
		
		$this->EndObject();
		
		
		$path = $this->savePath . 'class.' . strtolower($this->object1 . $this->object2) . 'map.php';
		file_put_contents($path, $this->mstr);
		chmod($path, 0766);
		
		
	}
	
	// -------------------------------------------------------------
	function BeginObject()
	{
		
		$t = (($this->manualId)?'string':'int');
		
		$this->mstr  = "<?php\n";
		$this->mstr .= $this->CreatePreface();
		// we have the right alphabetical order here ;-)
		$this->mstr .= "\nclass ".$this->object1 . $this->object2."Map\n{";
		
		$this->mstr .="\n\t\n\t/**\n\t";
		$this->mstr .=" * @const int DB\n\t";
		$this->mstr .=" */\n\tconst DB = $this->db;";
		
		$this->mstr .="\n\t\n\t/**\n\t";
		$this->mstr .=" * @var object _INSTANCE_\n\t";
		$this->mstr .=" */\n\t";
		$this->mstr .="static private \$_INSTANCE_ = null;";

		
		$this->mstr .="\n\t\n\t/**\n\t";
		$this->mstr .=" * @var $t ".$this->object1."id\n\t";
		$this->mstr .=" */\n\tpublic \$".$this->object1."id = '';";
		
		$this->mstr .="\n\t\n\t/**\n\t";
		$this->mstr .=" * @var $t ".$this->object2."id\n\t";
		$this->mstr .=" */\n\tpublic \$".$this->object2."id = '';";
		
		$this->mstr .="\n\t\n\t/**\n\t";
		$this->mstr .=" * @var int ".$this->object1."sort\n\t";
		$this->mstr .=" */\n\tpublic \$".$this->object1."sort = 0;";
		
		$this->mstr .="\n\t\n\t/**\n\t";
		$this->mstr .=" * @var int ".$this->object2."sort\n\t";
		$this->mstr .=" */\n\tpublic \$".$this->object2."sort = 0;";
		
	}
	
	function CreateSingleton()
	{
		$this->mstr .= "\n\t\n\t";
		$this->mstr .= $this->CreateComments("Singleton-Function to define the Object ".$this->object1 . $this->object2."Map only once",
											array(),
											"object ".$this->object1 . $this->object2."Map");
		$this->mstr .= "\tpublic static function instance ()\n\t{";
		$this->mstr .= "\n\t\tif (!isset(self::\$_INSTANCE_))\n\t\t{";
		$this->mstr .= "\n\t\t\tself::\$_INSTANCE_ = new ".$this->object1 . $this->object2."Map();";
		$this->mstr .= "\n\t\t}";
		$this->mstr .= "\n\t\treturn self::\$_INSTANCE_;";
		$this->mstr .= "\n\t}";
	}
	
	// -------------------------------------------------------------
	function CreatePreface()
	{
		$this->mstr .= "/*\n*\t class for Mapping Objects '".$this->object1."' and '".$this->object2."' with integrated CRUD methods.";
		$this->mstr .= "\n*\t @author ".$GLOBALS['cconfiguration']['author'];
		$this->mstr .= "\n*\t @version ".$GLOBALS['cconfiguration']['versionNumber'].' rev. '.$GLOBALS['cconfiguration']['revisionNumber']." ";
		$this->mstr .= "\n*\t @copyright ".$GLOBALS['cconfiguration']['copyright'];
		$this->mstr .= "\n*\t @link ".$GLOBALS['cconfiguration']['link'];
		$this->mstr .= "\n*/";
	}
	
	
	function CreateComments($description='', $parameterDescriptionArray='', $returnType='')
	{
		
		$this->mstr .= "/**\n\t* ".$description."\n";
		
 		if ($parameterDescriptionArray != '')
 		{
	 		foreach ($parameterDescriptionArray as $parameter)
	 		{
	 			$this->mstr .= "\t* @param ".$parameter."\n";
	 		}
 		}
 		
 		if ($returnType != '')
 		{
			$this->mstr .= "\t* @return ".$returnType."\n";
		}
		
	    $this->mstr .= "\t*/\n";
	}
	
	
	
	
	// -------------------------------------------------------------
	function CreateSaveFunction()
	{
		
		$o1 = strtolower($this->object1);
		$o2 = strtolower($this->object2);
		$on = implode('', array($o1, $o2));
		
		
		$this->mstr .= "\n\t\n\t";
		$this->mstr .= $this->CreateComments("Physically saves the Mapping to the Database", '', '');
		
		$this->mstr .= "\tfunction Save()\n\t{";
		$this->mstr .= "\n\t\t\$query = 'SELECT `".$o1."id` FROM `".$on."map` WHERE `".$o1."id`=:".$o1."id AND `".$o2."id`=:".$o2."id LIMIT 1';";
		
		$this->mstr .= "\n\t\t\$prepare = DB::instance($this->db)->prepare(\$query);";
		$this->mstr .= "\n\t\t\$prepare->execute(array(':".$o1."id'=>\$this->".$o1."id, ':".$o2."id'=>\$this->".$o2."id));";
		$this->mstr .= "\n\t\t\$result = \$prepare->fetchAll();\n\t\t";
		
		$this->mstr .= "\n\t\tif (count(\$result) === 0)";
		$this->mstr .= "\n\t\t{";
		$this->mstr .= "\n\t\t\t\$query = 'INSERT INTO `".$on."map` (`".$o1."id`, `".$o2."id`, `".$o1."sort`, `".$o2."sort`) VALUES (:".$o1."id, :".$o2."id, :".$o1."sort, :".$o2."sort)';";
		
		$this->mstr .= "\n\t\t\t";
		$this->mstr .= "\n\t\t\ttry";
		$this->mstr .= "\n\t\t\t{";
		
		$this->mstr .= "\n\t\t\t\t\$prepare = DB::instance($this->db)->prepare(\$query);";
		$this->mstr .= "\n\t\t\t\t\$prepare->execute(array(':".$o1."id'=>\$this->".$o1."id, ':".$o2."id'=>\$this->".$o2."id,':".$o1."sort'=>\$this->".$o1."sort, ':".$o2."sort'=>\$this->".$o2."sort));";
		
		
		$this->mstr .= "\n\t\t\t}";
		$this->mstr .= "\n\t\t\tcatch(Exception \$e)";
		$this->mstr .= "\n\t\t\t{";
		$this->mstr .= "\n\t\t\t\treturn '[['.\$e->getMessage().']]';";
		$this->mstr .= "\n\t\t\t}";
		$this->mstr .= "\n\t\t}";
		$this->mstr .= "\n\t}";
	}
	
	// -------------------------------------------------------------
	function CreateAddMappingFunction()
	{
		
		$o1 = strtolower($this->object1);
		$o2 = strtolower($this->object2);
		
		$this->mstr .= "\n\t\n\t";
		$this->mstr .= $this->CreateComments("Creates a Mapping between the two Objects", 
											array("object $this->object1 \$object", "object $this->object2 \$otherObject"),
											'object Save or false');
		$this->mstr .= "\tfunction AddMapping(\$object, \$otherObject)\n\t{";
		$this->mstr .= "\n\t\tif (\$object instanceof ".$this->object1." && \$object->id != '')";
		$this->mstr .= "\n\t\t{";
		$this->mstr .= "\n\t\t\t\$this->".$o1."id = \$object->id;";
		$this->mstr .= "\n\t\t\t\$this->".$o2."id = \$otherObject->id;";
		$this->mstr .= "\n\t\t\t\$this->".$o1."sort = intval(\$object->".$o2."sort);";
		$this->mstr .= "\n\t\t\t\$this->".$o2."sort = intval(\$otherObject->".$o1."sort);";
		$this->mstr .= "\n\t\t\treturn \$this->Save();";
		$this->mstr .= "\n\t\t}";
		$this->mstr .= "\n\t\telse if (\$object instanceof ".$this->object2." && \$object->id != '')";
		$this->mstr .= "\n\t\t{";
		$this->mstr .= "\n\t\t\t\$this->".$o2."id = \$object->id;";
		$this->mstr .= "\n\t\t\t\$this->".$o1."id = \$otherObject->id;";
		$this->mstr .= "\n\t\t\t\$this->".$o2."sort = intval(\$object->".$o1."sort);";
		$this->mstr .= "\n\t\t\t\$this->".$o1."sort = intval(\$otherObject->".$o2."sort);";
		$this->mstr .= "\n\t\t\treturn \$this->Save();";
		$this->mstr .= "\n\t\t}";
		$this->mstr .= "\n\t\telse";
		$this->mstr .= "\n\t\t{";
		$this->mstr .= "\n\t\t\treturn false;";
		$this->mstr .= "\n\t\t}";
		$this->mstr .= "\n\t}";
	}

	// -------------------------------------------------------------
	function CreateRemoveMappingFunction()
	{
		
		// already sorted
		$o1 = strtolower($this->object1);
		$o2 = strtolower($this->object2);
		$on = $o1.$o2;
		
		
		$this->mstr .= "\n\t\n\t";
		$this->mstr .= $this->CreateComments("Removes the Mapping between the two Objects", 
											array("object \$object", "object \$otherObject"),
											'');
		
		$this->mstr .= "\tfunction RemoveMapping (\$object, \$otherObject = null)\n\t{";
		
		$this->mstr .= "\n\t\tif (\$object instanceof ".$this->object1.")";
		$this->mstr .= "\n\t\t{";
		$this->mstr .= "\n\t\t\t\$query = 'DELETE FROM `".$on."map` WHERE `".$o1."id` = ?';";
		$this->mstr .= "\n\t\t\t\$bindings = array(\$object->id);";
		
		$this->mstr .= "\n\t\t\tif (\$otherObject != null && \$otherObject instanceof ".$this->object2.")";
		$this->mstr .= "\n\t\t\t{";
		$this->mstr .= "\n\t\t\t\t\$query .= ' AND `".$o2."id` = ?';";
		$this->mstr .= "\n\t\t\t\t\$bindings[] = \$otherObject->id;";
		$this->mstr .= "\n\t\t\t}";
		
		$this->mstr .= "\n\t\t}";
		$this->mstr .= "\n\t\telse if (\$object instanceof ".$this->object2.")";
		$this->mstr .= "\n\t\t{";
		$this->mstr .= "\n\t\t\t\$query = 'DELETE FROM `".$on."map` WHERE `".$o2."id` = ?';";
		$this->mstr .= "\n\t\t\t\$bindings = array(\$object->id);";
		
		$this->mstr .= "\n\t\t\tif (\$otherObject != null && \$otherObject instanceof ".$this->object1.")";
		$this->mstr .= "\n\t\t\t{";
		$this->mstr .= "\n\t\t\t\t\$query .= ' AND `".$o1."id` = ?';";
		$this->mstr .= "\n\t\t\t\t\$bindings[] = \$otherObject->id;";
		$this->mstr .= "\n\t\t\t}";
		
		$this->mstr .= "\n\t\t}";
		
		
		$this->mstr .= "\n\t\t";
		$this->mstr .= "\n\t\ttry";
		$this->mstr .= "\n\t\t{";
		
		$this->mstr .= "\n\t\t\t\$prepare = DB::instance($this->db)->prepare(\$query);";
		$this->mstr .= "\n\t\t\t\$prepare->execute(\$bindings);";
		
		$this->mstr .= "\n\t\t}";
		$this->mstr .= "\n\t\tcatch (Exception \$e)";
		$this->mstr .= "\n\t\t{";
		$this->mstr .= "\n\t\t\treturn '[['.\$e->getMessage().']]';";
		$this->mstr .= "\n\t\t}";
		$this->mstr .= "\n\t\t";
		$this->mstr .= "\n\t}";
	}

	
	// -------------------------------------------------------------
	function EndObject()
	{
		$this->mstr .= "\n}\n?>\n";
	}
	
	
}

?>
