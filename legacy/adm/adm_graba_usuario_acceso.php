<?php
include ("../conexion_bd.php");
$ok = 0;
if($acceso=='Y'){
	mssql_query("DELETE FROM acceso WHERE id_usuario=(SELECT id_usuario FROM usuario WHERE rut IN (SELECT rut FROM funcionario WHERE rut='".$rutx."' AND id_dependencia='".$depx."')) AND id_dependencia='".$depx."'");
}
if ($vigenciax == 'S'){
	$vigenciax = 'NULL';
}else{
	$vigenciax = "'N'";
}
$query = "UPDATE funcionario SET vigencia=".$vigenciax." WHERE rut='".$rutx."' AND id_dependencia='".$depx."'";
$result = mssql_query($query);
if ($Ret == 0)
	$ok = 1;
else
	$ok = 2;
echo '<html><body onload="document.form1.submit();">';
echo '<form name="form1" method="post" action="adm_usuario_acceso.php">';
echo '<input type="hidden" name="ok_graba" value="'.$ok.'">'."\n";
echo "</form></body></html>";
mssql_close($cn);
?>