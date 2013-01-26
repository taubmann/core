<?php
function _YEAR($arr)
{
	// atm just a simple numeric field with max 4 characters
	return '<div><label>'.$arr['label'].'</label><input type="text" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.$arr['value'].'" onkeyup="checkForNumber(this);if(this.value.length>4){this.style.color=\'#f00\'}" /></div>';
}
?>
