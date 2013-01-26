<?php
function _WIZARDTEXTEMB($arr)
{
	$wz = explode(':', $arr['add']);
	return '<div><label>'.$arr['label'].'</label><input id="input_'.$arr['name'].'" name="'.$arr['name'].'" type="hidden" placeholder="'.$arr['placeholder'].'" value="'.htmlspecialchars($arr['value']).'" />
	<iframe class="embed_frame" src="wizards/'.$wz[0].'/index.php?embed='.$arr['name'].'&projectName='.$_GET['projectName'].'&objectName='.$_GET['objectName'].'&objectId='.$_GET['objectId'].
	(isset($wz[3])?'&'.$wz[3]:'').'"></iframe>';
}
?>
