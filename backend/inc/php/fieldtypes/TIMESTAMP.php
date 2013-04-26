<?php
function _TIMESTAMP($arr)
{
	$ts = intval($arr['value']);
	$date = ($ts>0) ? date('Y-m-d H:i:s', $ts) : '';
	
	return '<div class="field"><label>'.$arr['label'].'</label>
	<input type="text" class="input timestamp" id="'.$arr['name'].'" value="'.$date.'" placeholder="'.$arr['placeholder'].'" />
	<input type="hidden" id="input_'.str_replace(array('[',']'),'_',$arr['name']).'" name="'.$arr['name'].'"  value="'.$ts.'" />
	<button onclick="$(\'#input_'.$arr['name'].'\').val(0);$(\'#'.$arr['name'].'\').val(\'\');return false;" rel="trash">.</button>
	</div>';
}
?>
