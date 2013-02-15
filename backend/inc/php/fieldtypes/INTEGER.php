<?php
function _INTEGER($arr) {
	
	$data='';foreach($arr['add'] as $k=>$v){$data.='data-'.$k.'="'.$v.'" ';}
	return '<div class="field"><label>'.$arr['label'].'</label><input type="number" '.$data.'name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.intval($arr['value']).'" onkeyup="checkForNumber(this)" /></div>';
}
?>
