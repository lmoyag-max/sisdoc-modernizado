<?php
include ("../conex_usuario.php");
//echo print_r($_POST, true);		//DEBUG
$rut=explode('-', $guarda_rut);
$ok=0;
$qrut="SELECT rut_fun FROM funcionario WHERE rut_fun='".$rut[0]."'";
$rr=mssql_query($qrut,$cn);
$trut=mssql_num_rows($rr);

if($trut == 0){
	$ok=2;
}else{
	if($sexox=='S'){
		$sexo='';
	}else{
		$sexo=", sexo_fun='".$sexox."'";
	}
	$query="UPDATE funcionario SET marcacion_fun='".$clave."', ap_pat_fun='".$ap_pat_fun."', ap_mat_fun='".$ap_mat_fun."', nombres_fun='".$nombre."', email_fun='".$correo."'".$sexo." WHERE rut_fun='".$rut[0]."'";
	$result=mssql_query($query);
	include("../conexion_bd.php");
	$query2="UPDATE funcionario SET nombres='".$nombre."' , apellidos='".$ap_pat_fun." ".$ap_mat_fun."' WHERE rut='".$rut[0]."'"; 
	$result2=mssql_query($query2);
	$ok=1;
}

echo '<html><body onload="document.form1.submit();">';
echo '<form name="form1" method="post" action="adm_modificar_usuarios.php">';
echo '<input type="hidden" name="ok_graba" value="' . $ok. '">' . "\n";
echo '<input type="hidden" name="cusuario" value="' . $cusuario . '">' . "\n";
echo '<input type="hidden" name="idusuario" value="' . $idusuario . '">' . "\n";
echo "</form></body></html>";
mssql_close($cn);
?>