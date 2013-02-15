<?php
function _WIZARDINTEGERBUTTON($arr)
{
	global $c;
	return '<div class="field"><label>'.$arr['label'].'</label><input type="hidden" id="input_'.$arr['name'].'" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.intval($arr['value']).'" /> '.
	$c->wizardButton($arr['name'],$arr['add']).'</div>';
}
?>
