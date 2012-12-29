<?php
function _BOOL($arr)
{
	return '<div><label for="input_'.$arr[1].'">'.$arr[0].'</label><input type="hidden" id="input_'.$arr[1].'" name="'.$arr[1].'" value="'.intval($arr[2]).'" /><label for="cb_'.$arr[1].'">&nbsp;</label><input type="checkbox" id="cb_'.$arr[1].'" class="checkbox" onchange="$(\'#input_'.$arr[1].'\').val(this.checked?1:0)" '.($arr[2]==1?'checked="checked"':'').' /></div><br style="clear:both" />';
}
?>
