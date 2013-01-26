<?php
function _FLOAT($arr) {
	$data='';foreach($arr['add'] as $k=>$v){$data.='data-'.$k.'="'.$v.'" ';}
	return '<div><label>'.$arr['label'].'</label><input type="number" '.$data.'name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.floatval($arr['value']).'" onkeyup="checkForNumber(this)" /></div>';
}
?>
