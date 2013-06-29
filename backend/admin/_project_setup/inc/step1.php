<?php

echo '
	<form id="frm" action="index.php" method="post">
		<fieldset>
			<legend>(1) '.L('enter_Super_Password').'</legend>
			<label for="pass">'.L('enter_Super_Password').'</label>
			<input name="pass" type="password" autocomplete="off" placeholder="'.L('enter_Super_Password').'" />
			<br /><br />
			<img height="30" src="'.$backend.'inc/php/captcha.php?x='.time().'" />
			<br />
			<label for="captcha_answer">'.L('enter_Result').'</label>
			<input name="captcha_answer" type="text" placeholder="'.L('enter_Result').'" />
			<br />
			<input type="submit" value="'.L('go').'" />
		</fieldset>
	</form>
</div>
</body>
</html>';

exit();
?>
