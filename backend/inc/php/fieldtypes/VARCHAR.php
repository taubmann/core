<?php
function _VARCHAR ($arr) {
	
	$data='';foreach($arr[3] as $k=>$v){$data.='data-'.$k.'="'.$v.'" ';}
	
	return '<div><label>'.$arr[0].'</label><input type="text" '.$data.'name="'.$arr[1].'" value="'.htmlspecialchars($arr[2]).'" /></div>';
}
?>
