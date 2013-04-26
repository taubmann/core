<?php
function _CRON($arr)
{
	$data='';
	foreach($arr['add'] as $k=>$v){$data.='data-'.$k.'="'.$v.'" ';}
	return '<div class="field"><label>'.$arr['label'].'</label><input class="cron" '.$data.'type="hidden" id="input_'.str_replace(array('[',']'),'_',$arr['name']).'" name="'.$arr['name'].'" value="'.$arr['value'].'" /></div>';
}
?>
