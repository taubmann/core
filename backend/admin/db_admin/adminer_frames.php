<?php
function adminer_object()
{
	include './frames.php';
	return new AdminerPlugin( array( new AdminerFrames, new AdminerEditTextarea ) );
}
include './adminer.php';
