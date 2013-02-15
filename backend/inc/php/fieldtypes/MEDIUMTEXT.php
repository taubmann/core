<?php
function _MEDIUMTEXT($arr) {
	global $field;
	return '<div class="field"><label>'.$arr['name'].'</label><textarea placeholder="'.$arr['placeholder'].'" name="'.$arr['label'].'">|</textarea></div>';
}
?>
