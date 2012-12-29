<?php
function _WIZARDBUTTON($arr)
{
	global $c; 
	//$wz = explode(':', $arr[3]);
	return '<div><label>'.$arr[0].'</label><input type="hidden" id="input_'.$arr[1].'" name="'.$arr[1].'" value="'.htmlspecialchars($arr[2]).'" /> '.
	$c->wizardButton($arr[1], $arr[3]) . '</div>';
	
	//<button  class="wz_'.($wz[0]=='extension'?md5($wz[3]):$wz[0]).'" rel="'.($wz[1]?$wz[1]:'gear').'" onclick="getWizard(\'input_'.$arr[1].'\',\''.$wz[0].'\''.($wz[3]?',\''.$wz[3].'\'':'').')" type="button">'.($wz[2]?$wz[2]:'Wizard').'</button></div>';
	
}
?>
