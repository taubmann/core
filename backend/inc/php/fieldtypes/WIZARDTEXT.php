<?php
function _WIZARDTEXT($arr)
{
	global $c;	
	return '<div><label>'.$arr[0].'</label><textarea id="input_'.$arr[1].'" spellcheck="false" style="width:95%;height:80px" name="'.$arr[1].'">'.htmlspecialchars($arr[2]).'</textarea> '.
	$c->wizardButton($arr[1],$arr[3]).'</div>';
	//<button  class="wz_'.($wz[0]=='extension'?md5($wz[3]):$wz[0]).'" rel="'.(isset($wz[1])?$wz[1]:'gear').'" onclick="getWizard(\'input_'.$arr[1].'\',\''.$wz[0].'\''.(isset($wz[3])?',\''.$wz[3].'\'':'').')" type="button">'.(isset($wz[2])?$wz[2]:'Wizard').'</button></div>';
}
?>
