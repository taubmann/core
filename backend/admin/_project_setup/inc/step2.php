<?php
/*
 * 
 * 
 * */

require 'inc/systeminfos.php';
require 'inc/htaccess.php';

echo '
	<form id="frm" action="index.php" method="post">
		<fieldset>
			<legend>(2) '.L('enter_Project_Name').'</legend>
			<label for="wished_name">'.L('Project_Name').'</label>
			<input name="wished_name" id="wished_name" type="text" onkeyup="clearString(this)" placeholder="'.L('Project_Name').'" />
			<input type="hidden" name="captcha_answer" value="'.$_POST['captcha_answer'].'" />
			<input type="hidden" name="pass" value="'.$_POST['pass'].'" />
			<input type="submit" value="'.L('go').'" />
			<hr />
			<div>'.htAccessChecks().'</div>
			<div>'.systemInfos().'</div>
		</fieldset>
	</form>
</div>
</body>
</html>';

exit();
?>
