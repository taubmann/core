<?php
function _SELINTEGER($arr)
{
	if(isset($arr['add']))
	{
		$str = '<select class="input selectbox" id="input_'.str_replace(array('[',']'),'_',$arr['name']).'" name="'.$arr['name'].'">';
		foreach($arr['add'] as $val=>$lbl)
		{
			// check alternatively for a Number-Range (min-max)
			if(strpos($val, '-', 1))
			{
				$arr['value'] = intval($arr['value']);
				$range = explode('-', $val);
				$range[0] = intval($range[0]);
				$range[1] = intval($range[1]);
				if($arr['value']<$range[0]) $arr['value']=$range[0];
				if($arr['value']>$range[1]) $arr['value']=$range[1];
				
				return '<div><label>'.$arr['label'].'</label><input type="text" readonly="readonly" style="width:40px" id="input_'.str_replace(array('[',']'),'_',$arr['name']).'" name="'.$arr['name'].'" value="'.$arr['value'].'" /><div class="slider" title="'.$arr['name'].'" alt="'.$arr['value'].'" rel="'.$val.'"></div></div>';
			}
			
			// create the options
			$str .= '<option ' . ($val==$arr['value'] ? 'selected="selected" ' : '') . ' value="'.$val.'">'.($lbl?$lbl:$val).'</option>';
		}
		return '<div class="field"><label>'.$arr['label'].'</label>'.$str.'</select></div>';
	}
	
	// Fallback
	return '<div class="field"><label>'.$arr['label'].'</label><input type="number" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.$arr['value'].'" onkeyup="checkForNumber(this)" /></div>';

}
?>
