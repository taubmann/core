<?php
function _HIDDENTEXT($arr) {
	return '<input type="hidden" class="input" id="input_'.str_replace(array('[',']'),'_',$arr['name']).'" name="'.$arr['name'].'" value="'.htmlspecialchars($arr['value']).'" />';
}
?>
