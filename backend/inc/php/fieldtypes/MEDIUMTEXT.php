<?php
function _MEDIUMTEXT($arr) {
	global $field;
	return '<div><label>'.$arr['name'].'</label><textarea placeholder="'.$arr['placeholder'].'" name="'.$arr['label'].'">|</textarea></div>';
}
?>
