<?php
/**
* simple Script showing the whole Directory at once
* taken from: https://github.com/mattpass/dirTree
* (c) Standard Open Source Initiative MIT License
*/
require '../../inc/php/functions.php';
require 'inc/path.php';

$path = $mainpath[2] . $_GET['ext'];

?>

<!DOCTYPE html>
<head>
<title>show directory</title>
<style>
body {
	font-family: sans-serif;
	background: #eee;
}
ul {
	list-style-type: none;
}
</style>

<script src="../../inc/js/jquery.min.js"></script>
<script>
$(function ()
{
	$('ul').prev().css({'color':'#555','cursor':'pointer','font-weight':'bold'}).append('/').on('click', function() {
		$(this).next().toggle('slow');
	});
	$('#dirtree>ul ul').hide();
});

</script>

</head>
<body>
<?php


$excludedFileFolders = array('.git', 'locale');

// Function to sort given values alphabetically
function alphasort($a, $b)
{
	return strcasecmp($a->getPathname(), $b->getPathname());
}

// Class to put forward the values for sorting
class SortingIterator implements IteratorAggregate
{
	private $iterator = null;
	public function __construct(Traversable $iterator, $callback)
	{
		$array = iterator_to_array($iterator);
		usort($array, $callback);
		$this->iterator = new ArrayIterator($array);
	}
	public function getIterator()
	{
		return $this->iterator;
	}
}

// strpos on an array of needles
function strposa($haystack, $needles=array(), $offset=0)
{
        $chr = array();
        foreach($needles as $needle)
        {
                $res = strpos($haystack, $needle, $offset);
                if ($res !== false) $chr[$needle] = $res;
        }
        if(empty($chr)) return false;
        return min($chr);
}


// Get a full list of dirs & files and begin sorting using above class & function
$objectList = new SortingIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST), 'alphasort');

// With that done, create arrays for out final ordered list and a temp array of files to copy over
$finalArray = $tempArray =  array();

// To start, push folders from object into finalArray, files into tempArray
foreach ($objectList as $objectRef)
{
	$fileFolderName = rtrim(substr($objectRef->getPathname(), strlen($path)),"..");
	if ($objectRef->getFilename()!="." && $fileFolderName[strlen($fileFolderName)-1] != "/" && !strposa($fileFolderName, $excludedFileFolders) )
	{
			$fileFolderName!="/" && is_dir($path.$fileFolderName) ? array_push($finalArray, $fileFolderName) : array_push($tempArray, $fileFolderName);
	}
}

// Now push root files onto the end of finalArray and splice from the temp, leaving only files that reside in subsirs
for ($i=0;$i<count($tempArray);$i++)
{
	if (count(explode("/",$tempArray[$i]))==2) {
		array_push($finalArray,$tempArray[$i]);
		array_splice($tempArray,$i,1);
		$i--;
	}
}

// Lastly we push remaining files into the right subdirs in finalArray
for ($i=0;$i<count($tempArray);$i++)
{
	$insertAt = array_search(dirname($tempArray[$i]),$finalArray)+1;
	for ($j=$insertAt;$j<count($finalArray);$j++)
	{
		if (	strcasecmp(dirname($finalArray[$j]), dirname($tempArray[$i]))==0 &&
			strcasecmp(basename($finalArray[$j]), basename($tempArray[$i]))<0 ||
			strstr(dirname($finalArray[$j]),dirname($tempArray[$i])))
		{
			$insertAt++;
		}
	}
	array_splice($finalArray, $insertAt, 0, $tempArray[$i]);
}

// Finally, we have our ordered list, so display in a UL
echo "<ul id=\"dirtree\">\n<li>".$_GET['ext']."</li>\n";
$lastPath="";
for ($i=0;$i<count($finalArray);$i++)
{
	$fileFolderName = $finalArray[$i];
	$thisDepth = count(explode("/",$fileFolderName));
	$lastDepth = count(explode("/",$lastPath));
	if ($thisDepth > $lastDepth) {echo "<ul>\n";}
	if ($thisDepth < $lastDepth)
	{
		for ($j=$lastDepth;$j>$thisDepth;$j--)
		{
			echo "</ul>\n";
		}
	}
	echo "<li>".basename($fileFolderName)."</li>\n";
	$lastPath = $fileFolderName;
}
echo "</ul>\n</ul>\n";
?>
</body>
</html>
