<?php
function _FLOAT($arr) {
	$data='';
	foreach($arr['add'] as $k=>$v){$data.='data-'.$k.'="'.$v.'" ';}
	return '<div class="field"><label>'.$arr['label'].'</label><input class="input" type="number" '.$data.' id="input_'.str_replace(array('[',']'),'_',$arr['name']).'" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.floatval($arr['value']).'" onkeyup="checkForNumber(this)" /></div>';
}
?>
