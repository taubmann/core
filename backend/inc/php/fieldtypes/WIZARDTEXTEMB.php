<?php
function _WIZARDTEXTEMB($arr)
{
	$wz = explode(':', $arr[3]);
	return '<div><label>'.$arr[0].'</label><input id="input_'.$arr[1].'" name="'.$arr[1].'" type="hidden" value="'.htmlspecialchars($arr[2]).'" />
	<iframe class="embed_frame" src="wizards/'.$wz[0].'/index.php?embed='.$arr[1].'&projectName='.$_GET['projectName'].'&objectName='.$_GET['objectName'].'&objectId='.$_GET['objectId'].
	(isset($wz[3])?'&'.$wz[3]:'').'"></iframe>';
}
?>
