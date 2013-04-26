<?php
function _DATE($arr)
{
	return '<div class="field"><label>'.$arr['label'].'</label><input class="input date" type="text" id="input_'.str_replace(array('[',']'),'_',$arr['name']).'" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.$arr['value'].'" /></div>';
}
?>
