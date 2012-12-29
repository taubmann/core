<?php
function _BLOB($arr)
{
	// atm not ready and not in modeling...
	return '<div><label>'.$arr[0].'</label><input type="text" name="'.$arr[1].'" value="'.htmlspecialchars($arr[2]).'" /></div>';
}
?>
