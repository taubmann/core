<?php
// textarea
function draw_number ($name, $id, $label, $placeholder, $val, $data)
{
	return '<div class="field"><label for="'.$id.'">'.$label.'</label><input type="text" class="input num" name="'.$name.'" '.$data.' id="'.$id.'" value="'.floatval($val).'" placeholder="'.$placeholder.'" /></div>';
}
