<?php
function _TEXT($arr)
{
	$data='';foreach($arr['add'] as $k=>$v){$data.='data-'.$k.'="'.$v.'" ';}
	return '<div><label>'.$arr['label'].'</label><textarea style="width:95%;height:80px" placeholder="'.$arr['placeholder'].'" '.$data.'name="'.$arr['name'].'">'.htmlspecialchars($arr['value']).'</textarea></div>';
}
?>
