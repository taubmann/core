<?php
function _TIMESTAMP($arr)
{
	$ts = intval($arr['value']);
	$read = ($ts>0) ? date('d.m.Y H:i:s', $ts) : '';
	return '<div class="field"><label>'.$arr['label'].'</label><input type="text" class="timestamp" id="input_'.$arr['name'].'" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.$ts.'" onkeyup="checkForNumber(this)" /><button onclick="$(\'#input_'.$arr['name'].'\').val(0);$(\'#rts'.$arr['name'].'\').html(\'\');return false;" rel="trash">.</button> <span id="rts'.$arr['name'].'">'.$read.'</span></div>';
}
?>
