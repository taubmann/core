<?php
function _TIMESTAMP($arr)
{
	$ts = intval($arr[2]);
	$read = ($ts>0) ? date('d.m.Y H:i:s', $ts) : '';
	return '<div><label>'.$arr[0].'</label><input type="text" class="timestamp" id="input_'.$arr[1].'" name="'.$arr[1].'" value="'.$ts.'" onkeyup="checkForNumber(this)" /><button onclick="$(\'#input_'.$arr[1].'\').val(0);$(\'#rts'.$arr[1].'\').html(\'\');return false;" rel="trash">.</button> <span id="rts'.$arr[1].'">'.$read.'</span></div>';
}
?>
