<?php
function _DATETIME($arr)
{
	return '<div class="field"><label>'.$arr['label'].'</label><input class="datetime" type="text" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.$arr['value'].'" /></div>';
}
?>
