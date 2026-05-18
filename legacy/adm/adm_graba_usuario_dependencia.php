<?php
include ("../conexion_bd.php");
$ok = 0;
$query = mssql_query("SELECT id_usuario FROM usuario WHERE id_funcionario IN (SELECT id_funcionario FROM funcionario WHERE rut='".$rutx."')");
$data=mssql_fetch_array($query);

mssql_query("INSERT INTO acceso VALUES (".$data['id_usuario'].", ".$cbo_dependencia.")");

$query = mssql_query("SELECT * FROM funcionario WHERE rut='".$rutx."' AND id_dependencia='".$cbo_dependencia."'");
$data=mssql_fetch_array($query);
if(isset($data['rut'])){
	mssql_query("UPDATE funcionario SET vigencia=NULL WHERE rut='".$rutx."' AND '".$cbo_dependencia."'");
	$ok=3;
}else{
	$query = mssql_query("SELECT * FROM funcionario WHERE rut='".$rutx."'");
	$data=mssql_fetch_array($query);
	mssql_query("INSERT INTO funcionario VALUES ('".$data['rut']."', '".$cbo_dependencia."', '".$data['dig']."', '".$data['nombres']."', '".$data['apellidos']."', NULL)");
	$ok=1;
}
echo '<html><body onload="document.form1.submit();">';
echo '<form name="form1" method="post" action="adm_usuario_dependencia.php">';
echo '<input type="hidden" name="ok_graba" value="' . $ok. '">' . "\n";
echo '<input type="hidden" name="cusuario" value="' . $cusuario . '">' . "\n";
echo '<input type="hidden" name="idusuario" value="' . $idusuario . '">' . "\n";
echo "</form></body></html>";
mssql_close($cn);
?>