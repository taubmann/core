<?php
function _MODEL($arr) {
	return '<input type="hidden" name="'.$arr['name'].'" value="'.htmlspecialchars($arr['value']).'" />';
}
?>
