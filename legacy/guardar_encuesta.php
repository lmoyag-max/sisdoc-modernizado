<?

include("conexion_bd.php");

if ($respuesta <> 'B' && $respuesta<>'M' && $respuesta <>'A')
{ echo "<script>\n";
  echo "alert('Debe contestar encuesta');";
  echo "</script>\n";
}
else 
{
$query = "exec ingreso_encuesta_sisdoc '" . $aux_rut . "','" . $respuesta . "'"; 
$rs_doc = mssql_query($query,$cn);
$reg_doc = mssql_fetch_array($rs_doc);
$ret_doc=$reg_doc["Ret"];
if ($ret_doc!=0)
{
echo "<script>\n";
echo "alert('Error al grabar encuesta ');";
echo "</script>\n";
}

// para llamar al menu que corresponde segun el usuario 
$flujo=8;
$x=0;
$y="";
//echo $tot_dep . "menu" . $tipo_menu  . "oirs" . $totoirs ;
if ($tot_dep == 0)
  {
    if ($tipo_menu <>'C')
    {
	 if ($totoirs==0)  // otros funcionarios que no son de esa dependencia (17 y 44 ) 
	 {  	
	echo '<html><body onload="document.form1.submit();">';
  	echo '<form name="form1" method="post" action="principal.php">';
	}
	else // funcionarios que son de la dependencia  17 y 44 
  	{
	echo '<html><body onload="document.form1.submit();">';
  	echo '<form name="form1" method="post" action="principal_oirs.php">';
	}
	echo '<input type="hidden" name="cusuario" value="' . $cusuario . '">';
  	echo '<input type="hidden" name="idusuario" value="' . $idusuario . '">';
  	echo '<input type="hidden" name="idfuncionario" value="' . $idfuncionario . '">';
  	echo '<input type="hidden" name="flujo_ok" value="' . $flujo_ok . '">';
  	echo '<input type="hidden" name="val_funcionario" value="' . $val_funcionario . '">';
  	echo '<input type="hidden" name="val_procedencia" value="' . $val_procedencia . '">';
  	echo '<input type="hidden" name="val_funcionario1" value="' . $val_funcionario1 . '">';
  	echo '<input type="hidden" name="val_destino" value="' . $val_destino . '">';
  	echo '<input type="hidden" name="tipo_procedencia" value="' . $tipo_procedencia . '">';
  	echo '<input type="hidden" name="tipo_destino" value="' . $tipo_destino . '">';
  	echo '<input type="hidden" name="num_int" value="' . $num_int . '">';
	echo '<input type="hidden" name="id_dependencia" value="'.$id_dependencia.'">';
	echo '<input type="hidden" name="tipo_frame" value="'.$tipo_frame.'">';// tipo frame = 1 (principal)
  	echo "</form></body></html>";
   } // if tipo menu
   else   {
    if ($totoirs==0)  // otros funcionarios que no son de esa dependencia (17 y 44 )  30-08-2006
	 {  
	 	
	echo '<html><body onload="document.form1.submit();">';
  	echo '<form name="form1" method="post" action="principal_consulta.php">';
	}
	else // funcionarios que son de la dependencia  17 y 44 
	{ 
	
  	echo '<html><body onload="document.form1.submit();">';
  	echo '<form name="form1" method="post" action="principal_consulta_oirs.php">';
  	}
	echo '<input type="hidden" name="cusuario" value="' . $cusuario . '">';
  	echo '<input type="hidden" name="idusuario" value="' . $idusuario . '">';
  	echo '<input type="hidden" name="idfuncionario" value="' . $id_funcionario . '">';
  	echo '<input type="hidden" name="flujo_ok" value="' . $flujo_ok . '">';
  	echo '<input type="hidden" name="val_funcionario" value="' . $val_funcionario . '">';
  	echo '<input type="hidden" name="val_procedencia" value="' . $val_procedencia . '">';
  	echo '<input type="hidden" name="val_funcionario1" value="' . $val_funcionario1 . '">';
  	echo '<input type="hidden" name="val_destino" value="' . $val_destino . '">';
  	echo '<input type="hidden" name="tipo_procedencia" value="' . $tipo_procedencia . '">';
  	echo '<input type="hidden" name="tipo_destino" value="' . $tipo_destino . '">';
  	echo '<input type="hidden" name="num_int" value="' . $num_int . '">';
	echo '<input type="hidden" name="id_dependencia" value="'.$id_dependencia.'">';
	echo '<input type="hidden" name="tipo_frame" value="'.$tipo_frame.'">';// tipo frame = 1 (principal)
  	echo "</form></body></html>"; 
   }
 }

   else
   {

  echo '<html><body onload="document.form1.submit();">';
  echo '<form name="form1" method="post" action="menu_ofpartes.php">';
  echo '<input type="hidden" name="cusuario" value="' . $cusuario . '">';
  echo '<input type="hidden" name="idusuario" value="' . $idusuario . '">';
  echo '<input type="hidden" name="idfuncionario" value="' . $idfuncionario . '">';
  echo '<input type="hidden" name="flujo_ok" value="' . $flujo_ok . '">';
  echo '<input type="hidden" name="val_funcionario" value="' . $val_funcionario . '">';
  echo '<input type="hidden" name="val_procedencia" value="' . $val_procedencia . '">';
  echo '<input type="hidden" name="val_funcionario1" value="' . $val_funcionario1 . '">';
  echo '<input type="hidden" name="val_destino" value="' . $val_destino . '">';
  echo '<input type="hidden" name="tipo_procedencia" value="' . $tipo_procedencia . '">';
  echo '<input type="hidden" name="tipo_destino" value="' . $tipo_destino . '">';
  echo '<input type="hidden" name="num_int" value="' . $num_int . '">';
  echo '<input type="hidden" name="id_dependencia" value="'.$id_dependencia.'">';
  echo '<input type="hidden" name="tipo_frame"  value="'.$tipo_frame .'">';// tipo frame = 2 (of partes)
  echo "</form></body></html>";
  }   
} //else de la encuesta 
mssql_close($cn);

?>