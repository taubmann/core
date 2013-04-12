<?php

// supported cms-kit-Drivers // ,'pgsql'
$supportedDrivers = array('sqlite','mysql');

// check against available PDO-System-Drivers
$pdoDrivers = array_intersect($supportedDrivers, PDO::getAvailableDrivers());

// DB-Template (to clone via Javascript)
$html = '
<div id="DBdefinition" style="display:none">
<span id="formBlock[[x]]">
<input id="deleteButton[[x]]" type="button" title="'.L('delete_Database_Block').'" style="float:right;color:red;" onclick="$(\'#formBlock[[x]]\').remove()" value="x" />
<h4>'.L('Database').' [[x]]</h4>
<br />
<select id="dbtype[[x]]" name="dbtype[x]" onchange="toggleFields(this, \'[[x]]\')">
	<option value="">'.L('DB_Type').'</option>';

foreach($pdoDrivers as $d)
{
	$html .= '	<option value="'.$d.'">'.strtoupper($d).'</option>';
}

$html .= '</select>
	'.hlp('dbtype', false).'
	
<div id="divDbhost[[x]]" style="display:none">'.hlp('dbhost').' 
	<span class="fr" id="putDbhost[[x]]" title="'.L('generate_localhost').'" style="display:none" onclick="$(\'#dbhost[[x]]\').val(\'localhost\')">&lArr;</span>
	<label for="dbhost[x]">'.L('DB_Host').'</label>
	<input id="dbhost[[x]]" name="dbhost[x]" type="text" placeholder="'.L('DB_Host').'" /> 
</div>
	
<div id="divDbport[[x]]" style="display:none">'.hlp('dbport').' 
	<span class="fr" id="putDbport[[x]]" title="'.L('generate_port_3306').'" style="display:none" onclick="$(\'#dbport[[x]]\').val(\'3306\')">&lArr;</span>
	<label for="dbport[x]">'.L('DB_Port').'</label>
	<input id="dbport[[x]]" name="dbport[x]" type="text" placeholder="'.L('DB_Port').'" />
</div>
	
<div id="divDbalias[[x]]">'.hlp('dbalias').'
	<label for="dbalias[x]">'.L('DB_Alias').'</label>
	<input id="dbalias[[x]]" name="dbalias[x]" type="text" title="'.L('DB_Alias').'" placeholder="'.L('DB_Alias').'" value="'.L('Database').' [[x]]" />
</div>
	
<div>'.hlp('dbname').' 
	<span class="fr" id="putDbname[[x]]" title="'.L('generate_a_random_name').'" style="display:none" onclick="randomString(20,\'dbname[[x]]\',\'.db\')">&lArr;</span>
	<label for="dbname[x]">'.L('DB_Name').'</label>
	<input id="dbname[[x]]" name="dbname[x]" type="text" placeholder="'.L('DB_Name').'" />
</div>
	
<div id="divDbuser[[x]]" style="display:none">'.hlp('dbuser').'
	<label for="dbuser[x]">'.L('DB_Username').'</label>
	<input id="dbuser[[x]]" name="dbuser[x]" type="text" placeholder="'.L('DB_Username').'" />
</div>
	
<div>'.hlp('dbpass').' 
	<span class="fr" id="putDbpass[[x]]"  title="'.L('generate_a_random_name').'" style="display:none" onclick="randomString(20,\'dbpass[[x]]\',\'\')">&lArr;</span>
	<label for="dbpass[x]">'.L('DB_Password').'</label>
	<input id="dbpass[[x]]" name="dbpass[x]" type="text" placeholder="'.L('DB_Password').'" />
</div>

<hr />
</span>
</div>';
//DB-Template END


// Form Begin
$html .= '
<form id="frm" action="index.php?sproject='.$_POST['wished_name'].'" method="post" enctype="multipart/form-data">
<fieldset>
<legend>(3) '.L('enter_Database-Credentials_for').' "'.$_POST['wished_name'].'"</legend>
';

foreach($_POST as $k=>$v) {
	$html .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
}



$html .= '

<span id="writeout"></span>

<input style="float:right;color:green;" type="button" title="'.L('add_Database').'" onclick="cloneFields()" value="(+)" />
<br />
<div>'.hlp('file', false).'</div>
<div>
	<input type="file" placeholder="'.L('ZIP_File').'" name="file" id="file" />
</div>

<input type="submit" name="generate_project" value="'.L('create').' \''.$_POST['wished_name'].'\'" />

</fieldset>
</form>

<script>
$(document).ready(function() {
	cloneFields();
	$("#deleteButton0").hide();
});
</script>

</div>
</body>
</html>
';

echo $html;

exit();
?>
