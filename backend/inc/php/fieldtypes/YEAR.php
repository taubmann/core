<?php
function _YEAR($arr)
{
	// atm just a simple numeric field with max 4 characters
	return '<div><label>'.$arr[0].'</label><input type="text" name="'.$arr[1].'" value="'.$arr[2].'" onkeyup="checkForNumber(this);if(this.value.length>4){this.style.color=\'#f00\'}" /></div>';
}
?>
