<?php
function _DATE($arr)
{
	return '<div class="field"><label>'.$arr['label'].'</label><input class="date" type="text" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.$arr['value'].'" /></div>';
}
?>
