<?php
// input text
function draw_string ($name, $id, $label, $placeholder, $val, $data)
{
	return '<div class="field"><label for="'.$id.'">'.$label.'</label><input type="text" class="input" name="'.$name.'" '.$data.' id="'.$id.'" value="'.htmlspecialchars($val).'" placeholder="'.$placeholder.'" /></div>';
}
