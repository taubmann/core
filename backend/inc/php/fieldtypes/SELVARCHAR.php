<?php
function _SELVARCHAR($arr)
{
	if(isset($arr[3]))
	{
		$str = '<select class="selectbox" name="'.$arr[1].'">';
		foreach($arr[3] as $val=>$lbl)
		{
			$str .= '<option '.($val==$arr[2]?'selected="selected" ':'').'value="'.htmlspecialchars($val).'">'.($lbl?$lbl:$val).'</option>';
		}
		$str .= '</select>';
		return '<div><label>'.$arr[0].'</label>'.$str.'</div>';
	}
	// Fallback
	return '<div><label>'.$arr[0].'</label><input type="text" name="'.$arr[1].'" value="'.$arr[2].'" /></div>';
}
?>
