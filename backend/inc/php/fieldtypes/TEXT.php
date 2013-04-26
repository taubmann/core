<?php
function _TEXT($arr)
{
	$data='';foreach($arr['add'] as $k=>$v){$data.='data-'.$k.'="'.$v.'" ';}
	return '<div class="field"><label>'.$arr['label'].'</label><textarea class="input" style="width:95%;height:80px" placeholder="'.$arr['placeholder'].'" '.$data.' id="input_'.str_replace(array('[',']'),'_',$arr['name']).'" name="'.$arr['name'].'">'.htmlspecialchars($arr['value']).'</textarea></div>';
}
?>
