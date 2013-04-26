<?php
function _BLOB($arr)
{
	// atm not ready and not in modeling...
	return '<div class="field"><label for="'.$arr['name'].'">'.$arr['label'].'</label><input class="input" type="text" id="input_'.str_replace(array('[',']'),'_',$arr['name']).'" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.htmlspecialchars($arr['value']).'" /></div>';
}
?>
