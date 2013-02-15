<?php
function _BOOL($arr)
{
	return '<div class="field"><label for="'.$arr['name'].'">'.$arr['label'].'</label><input type="hidden" id="input_'.$arr['name'].'" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.intval($arr['value']).'" /><label for="cb_'.$arr['name'].'">&nbsp;</label><input type="checkbox" id="cb_'.$arr['name'].'" class="checkbox" onchange="$(\'#input_'.$arr['name'].'\').val(this.checked?1:0)" '.($arr['value']==1?'checked="checked"':'').' /></div><br style="clear:both" />';
}
?>
