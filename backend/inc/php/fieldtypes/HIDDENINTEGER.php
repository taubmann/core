<?php
function _HIDDENINTEGER($arr) {
	return '<input class="input" type="hidden" id="input_'.str_replace(array('[',']'),'_',$arr['name']).'" name="'.$arr['name'].'" value="'.intval($arr['value']).'" />';
}
?>
