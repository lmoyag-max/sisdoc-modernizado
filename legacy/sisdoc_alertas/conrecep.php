<? 
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");

$qr = mssql_query("select * from usuario where usuario = '$usuario' and  clave = '$contrasena' ");
	
	$reg_f5 = mssql_fetch_array($qr);
	$x_usuario=$reg_f5[usuario];
	$id_usuario=$reg_f5[id_usuario];
	$nrows = mssql_num_rows($qr);
	if ($nrows !=0) {

// codigo html para pasar las variables del usuario cuando se verifica en la base de dato

	echo '<html><body onload="document.form1.submit();">';
//	 echo '<html><body onload="alert(' . "'" . $cusuario . "'" . ');">'; 
	
	echo '<form name="form1" method="post" action="multi_recep.php">';
	echo '<input type="hidden" name="cusuario" value="' . $x_usuario . '">';
	echo '<input type="hidden" name="idusuario" value="' . $id_usuario . '">';
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
	echo '<form name="form2" method="post" action="recepcion.php">';
	echo '<input type="hidden" name="errorusuario" value="' . $error . '">';
	echo "</form></body></html>";
} 
//mssql_free_result($qr); 
mssql_close($cn); 
?> 
