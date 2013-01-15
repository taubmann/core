<?php

include 'login_src.php';

// get Pic-of-the-Day from your Array
$bg = $loginpics[(date('z') % count($loginpics))];

header('Content-type: text/javascript');
echo '
$(function()
{
	$.backstretch("'.$bg['src'].'",{
		afterLoad: function(){
			$(\'#head_right\').html(\'<a title="'.$bg['title'].'" target="_blank" href="'.$bg['link'].'">Image by '.$bg['author'].', © '.$bg['copyright'].'</a>\');
			$("body, #head_right a").css("color", "'.$bg['linkcolor'].'");
		}
	});
});';

?>
