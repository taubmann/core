<?php
function _WIZARDTEXT($arr)
{
	global $c;	
	return '<div class="field"><label>'.$arr['label'].'</label><textarea id="input_'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" spellcheck="false" style="width:95%;height:80px" name="'.$arr['name'].'">'.htmlspecialchars($arr['value']).'</textarea> '.
	$c->wizardButton($arr['name'], $arr['add']).'</div>';
	
}
?>
