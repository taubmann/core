<?php
function _BLOB($arr)
{
	// atm not ready and not in modeling...
	return '<div><label for="'.$arr['name'].'">'.$arr['label'].'</label><input type="text" id="input_'.$arr['name'].'" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.htmlspecialchars($arr['value']).'" /></div>';
}
?>
