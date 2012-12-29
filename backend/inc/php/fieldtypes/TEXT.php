<?php
function _TEXT($arr)
{
	$data='';foreach($arr[3] as $k=>$v){$data.='data-'.$k.'="'.$v.'" ';}
	return '<div><label>'.$arr[0].'</label><textarea style="width:95%;height:80px" '.$data.'name="'.$arr[1].'">'.htmlspecialchars($arr[2]).'</textarea></div>';
}
?>
