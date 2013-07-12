<?php
// textarea
function draw_text ($name, $id, $label, $placeholder, $val, $data)
{
	return '<div class="field"><label for="'.$id.'">'.$label.'</label><textarea class="input" name="'.$name.'" '.$data.' id="'.$id.'" placeholder="'.$placeholder.'">'.htmlspecialchars($val).'</textarea></div>';
}
