<?php
function _MEDIUMTEXT($arr) {
	global $field;
	$field[$arr[0]] = '<div><label>'.$arr[1].'</label><textarea name="'.$arr[0].'">|</textarea></div>';
}
?>
