<? 
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
$flujo=8;
$x=0;
$y="";
$x1=166;
$x2=6;
//$qr ="select * from usuario where usuario = $usuario and  clave =$contrasena ";
//$rs_usu=mssql_query($qr);  
//$qr = mssql_query("select * from usuario where usuario = '$usuario' and  clave = '$contrasena' ",$cn);

$rs_usuario="select * from usuario where usuario= '$usuario'
and clave= '$contrasena' ";
$qr=mssql_query($rs_usuario);   

	
	$reg_f5 = mssql_fetch_array($qr);
	$x_usuario=$reg_f5[usuario];
	$id_usuario=$reg_f5[id_usuario];
	$id_funcionario=$reg_f5[id_funcionario];
	$Tot_usu = mssql_num_rows($qr);
	
	
if ($Tot_usu!=0)
{	
		
		 
//	if ($nrows !=0) {

// codigo html para pasar las variables del usuario cuando se verifica en la base de dato

	echo '<html><body onload="document.form1.submit();">';
//	 echo '<html><body onload="alert(' . "'" . $cusuario . "'" . ');">'; 
	
	
	echo '<form name="form1" method="post" action="ingreso_responder.php">';
	echo '<input type="hidden" name="cusuario" value="' . $x_usuario . '">';
	echo '<input type="hidden" name="idusuario" value="' . $id_usuario . '">';
	echo '<input type="hidden" name="idfuncionario" value="' . $id_funcionario . '">';
	echo '<input type="hidden" name="flujo_ok" value="' . $flujo . '">';
	echo '<input type="hidden" name="val_funcionario" value="' . $x . '">';
	echo '<input type="hidden" name="val_procedencia" value="' . $x . '">';
	echo '<input type="hidden" name="val_funcionario1" value="' . $x . '">';
	echo '<input type="hidden" name="val_destino" value="' . $x . '">';
	echo '<input type="hidden" name="tipo_procedencia" value="' . $y . '">';
	echo '<input type="hidden" name="tipo_destino" value="' . $y . '">';
	echo '<input type="hidden" name="iddocant" value="' . $x1 . '">';
	echo '<input type="hidden" name="idtraant" value="' . $x2 . '">';
	echo '<input type="hidden" name="num_int" value="' . $x . '">';
	echo "</form></body></html>";
	
	
	//header("Location: ingreso_Docto1.php"); 
}else { 
/*	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="autentificacion.php">';
	echo '<input type="hidden" name="errorusuario" value="' . "si" . '">';
	echo "</form></body></html>";
*/ 
    //si no existe le mando otra vez a la portada 
   // header("Location: index.php?errorusuario=si"); 
	//echo "malo";
	$error="si";
	echo "Error de Datos";
	echo '<html><body onload="document.form2.submit();">';
	echo '<form name="form2" method="post" action="autentificacion.php">';
	echo '<input type="hidden" name="errorusuario" value="' . $error . '">';
	echo "</form></body></html>";
} 
//mssql_free_result($qr); 
mssql_close($cn); 
?> 
