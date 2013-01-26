<?php
function _WIZARDTEXT($arr)
{
	global $c;	
	return '<div><label>'.$arr['label'].'</label><textarea id="input_'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" spellcheck="false" style="width:95%;height:80px" name="'.$arr['name'].'">'.htmlspecialchars($arr['value']).'</textarea> '.
	$c->wizardButton($arr['name'],$arr['add']).'</div>';
	//<button  class="wz_'.($wz[0]=='extension'?md5($wz[3]):$wz[0]).'" rel="'.(isset($wz[1])?$wz[1]:'gear').'" onclick="getWizard(\'input_'.$arr['name'].'\',\''.$wz[0].'\''.(isset($wz[3])?',\''.$wz[3].'\'':'').')" type="button">'.(isset($wz[2])?$wz[2]:'Wizard').'</button></div>';
}
?>
