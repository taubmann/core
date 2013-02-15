<?php
function _VARCHAR ($arr) {
	
	$data='';
	foreach($arr['add'] as $k=>$v){$data.='data-'.$k.'="'.$v.'" ';}
	
	return '<div class="field"><label>'.$arr['label'].'</label><input type="text" '.$data.'name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.htmlspecialchars($arr['value']).'" /></div>';
}
?>
