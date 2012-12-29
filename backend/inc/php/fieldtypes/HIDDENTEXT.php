<?php
function _HIDDENTEXT($arr) {
	return '<input type="hidden" name="'.$arr[1].'" value="'.htmlspecialchars($arr[2]).'" />';
}
?>
