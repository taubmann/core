<?php
function _SELINTEGER($arr)
{
	if(isset($arr['add']))
	{
		$str = '<select class="selectbox" name="'.$arr['name'].'">';
		foreach($arr['add'] as $val=>$lbl)
		{
			// check alternatively for a Number-Range (min-max)
			if(strpos($val, '-', 1))
			{
				$range = explode('-', $val);
				if($arr['value']>$range[1]) $arr['value']=$range[1];
				if($arr['value']<$range[0]) $arr['value']=$range[0];
				return '<div><label>'.$arr['label'].'</label><input type="text" readonly="readonly" style="width:40px" id="input_'.$arr['name'].'" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.$arr['value'].'" /><div class="slider" title="'.$arr['name'].'" alt="'.$arr['value'].'" rel="'.$val.'"></div></div>';
				
			}
			// create the options
			$str .= '<option ' . ($val==$arr['value'] ? 'selected="selected" ' : '') . ' value="'.$val.'">'.($lbl?$lbl:$val).'</option>';
		}
		return '<div><label>'.$arr['label'].'</label>'.$str.'</select></div>';
	}
	
	// Fallback
	return '<div><label>'.$arr['label'].'</label><input type="number" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.$arr['value'].'" onkeyup="checkForNumber(this)" /></div>';

}
?>
