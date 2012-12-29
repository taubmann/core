<?php
function _SELINTEGER($arr)
{
	if(isset($arr[3]))
	{
		$str = '<select class="selectbox" name="'.$arr[1].'">';
		foreach($arr[3] as $val=>$lbl)
		{
			// check alternatively for a Number-Range (min-max)
			if(strpos($val, '-', 1))
			{
				$range = explode('-', $val);
				if($arr[2]>$range[1]) $arr[2]=$range[1];
				if($arr[2]<$range[0]) $arr[2]=$range[0];
				return '<div><label>'.$arr[0].'</label><input type="text" readonly="readonly" style="width:40px" id="input_'.$arr[1].'" name="'.$arr[1].'" value="'.$arr[2].'" /><div class="slider" title="'.$arr[1].'" alt="'.$arr[2].'" rel="'.$val.'"></div></div>';
				
			}
			// create the options
			$str .= '<option ' . ($val==$arr[2] ? 'selected="selected" ' : '') . 'value="'.$val.'">'.($lbl?$lbl:$val).'</option>';
		}
		return '<div><label>'.$arr[0].'</label>'.$str.'</select></div>';
	}
	
	// Fallback
	return '<div><label>'.$arr[0].'</label><input type="number" name="'.$arr[1].'" value="'.$arr[2].'" onkeyup="checkForNumber(this)" /></div>';

}
?>
