<?php
function _WIZARDVARCHAR($arr)
{
	// wizardType:wizardIcon:wizardLabel:wizardAction
	//  [extension] => Array ( [0] => gear [1] => su [2] => path=user/wizards/su )
	//$wz = explode(':', $arr['add']);
	global $c; 
	$str = '<div class="field"><label>'.$arr['label'].'</label><input id="input_'.$arr['name'].'" type="text" name="'.$arr['name'].'" placeholder="'.$arr['placeholder'].'" value="'.htmlspecialchars($arr['value']).'" /> '.
			$c->wizardButton($arr['name'], $arr['add']);
	
	//<button class="wz_'.($wz[0]=='extension'?md5($wz[3]):$wz[0]).'" rel="'.(isset($wz[1])?$wz[1]:'gear').'" onclick="getWizard(\'input_'.$arr['name'].'\',\''.$wz[0].'\''.(isset($wz[3])?',\''.$wz[3].'\'':'').')" type="button">'.(isset($wz[2])?$wz[2]:'Wizard').'</button>';
	
	
	
	// check for Image-Thumbnails (special case with existing filemanager)
	if(isset($arr['filemanager']) && strlen($arr['value'])>0)
	{
		global $projectName;
		$dirpath = realpath('../projects/' . strtolower($projectName));
		$filepath = $dirpath.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'.tmb'.DIRECTORY_SEPARATOR . md5($dirpath.DIRECTORY_SEPARATOR.$arr['value']) . '.png';
		//$str .= $filepath;
		if(file_exists($filepath))
		{
			$str .= ' <img src="../projects/' . strtolower($projectName) . '/files/.tmb/'. md5($dirpath.DIRECTORY_SEPARATOR.$arr['value']).'.png" />';
		}
	}
	
	$str .= '</div>';
	return $str;
}
?>
