<?php
function _SELVARCHAR($arr)
{
	if(count($arr['add'])>0)
	{
		$str = '<select class="input selectbox" id="input_'.str_replace(array('[',']'),'_',$arr['name']).'" name="'.$arr['name'].'">';
		foreach($arr['add'] as $val=>$lbl)
		{
			$str .= '<option '.($val==$arr['value']?'selected="selected" ':'').'value="'.htmlspecialchars($val).'">'.($lbl?$lbl:$val).'</option>';
		}
		$str .= '</select>';
		return '<div class="field"><label>'.$arr['label'].'</label>'.$str.'</div>';
	}
	// Fallback
	return '<div class="field"><label>'.$arr['label'].'</label><input type="text" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.$arr['value'].'" /></div>';
}
?>
