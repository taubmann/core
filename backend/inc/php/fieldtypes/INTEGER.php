<?php
function _INTEGER($arr) {
	
	$data='';foreach($arr[3] as $k=>$v){$data.='data-'.$k.'="'.$v.'" ';}
	return '<div><label>'.$arr[0].'</label><input type="number" '.$data.'name="'.$arr[1].'" value="'.intval($arr[2]).'" onkeyup="checkForNumber(this)" /></div>';
}
?>
