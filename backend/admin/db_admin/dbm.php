<?php
function adminer_object()
{
	include './plugins.php';
	return new AdminerPlugin( array( new AdminerFrames, new AdminerEditTextarea, new AdminerDumpXml ) );
}
// if(!defined('ADMINER_IS_INCLUDED')){ exit(''); }
// define('ADMINER_IS_INCLUDED');
include './adminer.php';
