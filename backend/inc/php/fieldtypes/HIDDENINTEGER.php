<?php
function _HIDDENINTEGER($arr) {
	return '<input type="hidden" name="'.$arr['name'].'" value="'.intval($arr['value']).'" />';
}
?>
