<?php
function _WIZARDBUTTON($arr)
{
	global $c; 
	return '<div class="field"><label>'.$arr['label'].'</label><input type="hidden" id="input_'.$arr['name'].'" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.htmlspecialchars($arr['value']).'" /> '.
	$c->wizardButton($arr['name'], $arr['add']) . '</div>';
	
}
?>
