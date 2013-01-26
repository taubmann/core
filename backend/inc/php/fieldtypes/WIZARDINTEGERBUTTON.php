<?php
function _WIZARDINTEGERBUTTON($arr)
{
	global $c;
	return '<div><label>'.$arr['label'].'</label><input type="hidden" id="input_'.$arr['name'].'" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.intval($arr['value']).'" /> '.
	$c->wizardButton($arr['name'],$arr['add']).'</div>';
	//<button  class="wz_'.($wz[0]=='extension'?md5($wz[3]):$wz[0]).'" rel="'.($wz[1]?$wz[1]:'gear').'" onclick="getWizard(\'input_'.$arr['name'].'\',\''.$wz[0].'\''.($wz[3]?',\''.$wz[3].'\'':'').')" type="button">'.($wz[2]?$wz[2]:'Wizard').'</button></div>';
}
?>
