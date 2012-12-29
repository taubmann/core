<?php
function _HIDDENINTEGER($arr) {
	return '<input type="hidden" name="'.$arr[1].'" value="'.intval($arr[2]).'" />';
}
?>
