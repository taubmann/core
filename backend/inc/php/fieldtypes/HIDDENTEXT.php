<?php
function _HIDDENTEXT($arr) {
	return '<input type="hidden" name="'.$arr['name'].'" value="'.htmlspecialchars($arr['value']).'" />';
}
?>
