<?php
function _MEDIUMTEXT($arr) {
	global $field;
	return '<div class="field"><label>'.$arr['name'].'</label><textarea placeholder="'.$arr['placeholder'].'" id="input_'.str_replace(array('[',']'),'_',$arr['name']).'" name="'.$arr['label'].'">|</textarea></div>';
}
?>
