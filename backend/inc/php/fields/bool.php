<?php
// checkbox
function draw_bool ($name, $id, $label, $placeholder, $val, $data)
{
	
	return '<div class="field"><label for="'.$id.'">'.$label.'</label><input type="checkbox" style="clip:auto;position:static" class="input checkbox" name="'.$name.'" '.$data.' id="'.$id.'" value="1" '.($val==1?'checked="checked"':'').' /></div>';
}
