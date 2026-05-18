<?PHP
include("conexion_bd.php");
// Programa que elimina los documentos que estan generados
$fechasistema = date("Y/m/d H:i"); 
$id_usu = $idusuario;
$id_doc = $iddocum;
$id_seg = $idseguim;
$tipo_doc=$Cbo_Tipo_Docto;
$xx=$idusuario;
$c_usuario=$cusuario;
$id_funcionario=$idfuncionario;
$dia = substr($Txt_fecha_Doc,0,2);
$mes = substr($Txt_fecha_Doc,3,2);
$año = substr($Txt_fecha_Doc,6,4);
$Fecha_Docto = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));
$fechaini=$Txt_fecha_ini;
$fechafin=$Txt_fecha_fin;

// Busca la cantidad de tramites que tiene el documento
$rs_tram="exec busca_estado_tramite '" . $id_doc . "'";
$rs_tramite=mssql_query($rs_tram);  
$reg_tram = mssql_fetch_array($rs_tramite);
$Totreg = mssql_num_rows($rs_tramite);

if ($reg_tram["tramites"]==0)
{
	$grabado =0;
	
}
else
{
	$grabado=1;
		
}


if($grabado==0)
{
$op="I";
$flujo=0;
$tipo_elim="D";
$sw_grabado = 0;
#-------- Guarda los datos en tabla de Respaldo_Documento ----------

// Busca los datos del documento y tramite para llenar la tabla respaldo_documento
$datos_query = "exec busca_datos '" .  $id_seg . "'" ; 
$rs_datos = mssql_query($datos_query,$cn); 
$reg_datos = mssql_fetch_array($rs_datos);
$Totdatos = mssql_num_rows($rs_datos);

// Guarda los datos en la tabla respaldo_documento
$documento_query = "exec ing_respaldo_docto '" . $id_doc . "','" . $reg_datos["id_tipo_documento"] . "','" . 
$reg_datos["id_usuario"] . "','"  . $reg_datos["num_interno"] . "','" . $reg_datos["num_oficial"] . "','" .
$reg_datos["num_externo"] . "','" . $reg_datos["fecha_documento"] . "','" .  $reg_datos["fecha_sistema"] . "','" .
ltrim($reg_datos["materia"]) . "','" . $id_seg . "','" . $reg_datos["id_procedencia"] . "','" .
$reg_datos["id_destino"]  . "','" . $reg_datos["tipo_procedencia"] . "','" .
$reg_datos["tipo_destinatario"] . "','" . $reg_datos["rut_procedencia"]  . "','" . 
$reg_datos["rut_destino"] . "','" . $reg_datos["fecha2"] . "','" . 
ltrim($reg_datos["observaciones"]) . "','" .
$tipo_elim . "','" . $fechasistema . "'";
 
//$reg_datos.close;
//$rs_datos.close;
$rs_doc = mssql_query($documento_query,$cn);
$reg_doc = mssql_fetch_array($rs_doc);
$ret_doc=$reg_doc["ret"];
    // si se respaldo bien va a eliminar los descriptores,tramites y documento 
	if ($ret_doc==0)
	{
	$eliminacion_query = "exec elimina_documentos_tramites '". $id_doc . "','" . $id_seg . "','" . $tipo_elim . "'"; 
	
	$rs_elim = mssql_query($eliminacion_query,$cn); 
	$reg_elim = mssql_fetch_array($rs_elim);
	$ret_elim=$reg_elim["Ret"];
	
	if($ret_elim==0)
	{
		$sw_grabado =0;
		/*$fec_doc=strtotime($reg_datos[fecha_documento]);
		$fech_doc=date("d/m/Y",$fec_doc);
     		*/
     		$año = substr($reg_datos["fecha_documento"],6,5);
		$Fecha_Docto = date("Y/m/d ", mktime(0, 0,0, $mes, $dia, $año));
//		echo "año" . $año . "fecha  " . $reg_datos["fecha_documento"] . 
                    
		/*$num_interno_query = "exec actualiza_num_int '" . $reg_datos["id_procedencia"] . "','" .
		$reg_datos["tipo_procedencia"] . "','" . $reg_datos["num_interno"] . "','" . $reg_datos["id_tipo_documento"] . "'"; 
	                */
	    $num_interno_query = "exec actualiza_num_int '" . $reg_datos["id_procedencia"] . "','" . 
		$reg_datos["tipo_procedencia"] . "','" . $reg_datos["num_interno"] . "','" . $reg_datos["id_tipo_documento"] . "','" . $año . "'"; 
	
		$rs_int = mssql_query($num_interno_query,$cn); 
		$reg_int = mssql_fetch_array($rs_int);
		$ret_int=$reg_int["Ret"];
		
	}
	}
}
else
{
$sw_grabado = 1;
}
	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="doc_enc_el.php">' . "\n";
	echo '<input type="hidden" name="idusuario" value="' . $xx . '">' . "\n";
	echo '<input type="hidden" name="cusuario" value="' . $c_usuario . '">' . "\n";
	echo '<input type="hidden" name="idfuncionario" value="' . $id_funcionario . '">' . "\n";
	echo '<input type="hidden" name="flujook" value="' . $flujo . '">' . "\n";
	echo '<input type="hidden" name="sw_grabado" value="' . $sw_grabado . '">' . "\n";
	echo '<input type="hidden" name="sw_cons" value="' . 1 . '">' . "\n";
	echo '<input type="hidden" name="Cbo_Procedencia" value="' . $Cbo_Procedencia . '">' . "\n";
	echo '<input type="hidden" name="Cbo_Destinatario" value="' . $Cbo_Destinatario . '">' . "\n";
	echo '<input type="hidden" name="TxtMateria" value="' . $TxtMateria . '">' . "\n";
	echo '<input type="hidden" name="tipo_destino" value="' . $tipo_destino . '">' . "\n";
	echo '<input type="hidden" name="tipo_procedencia" value="' . $tipo_procedencia . '">' . "\n";
	echo '<input type="hidden" name="Txt_fecha_ini" value="' . $fechaini . '">' . "\n";
	echo '<input type="hidden" name="Txt_fecha_fin" value="' . $fechafin . '">' . "\n";
	echo '<input type="hidden" name="Cbo_Tipo_Docto" value="' . $tipo_doc . '">' . "\n";
	echo '<input type="hidden" name="TxtOficial" value="' . $TxtOficial . '">' . "\n";
	echo '<input type="hidden" name="TxtInterno" value="' . $TxtInterno . '">' . "\n";
	echo '<input type="hidden" name="TxtExterno" value="' . $TxtExterno . '">' . "\n";
	
	echo '</form></body></html>'  . "\n";



mssql_close($cn);
?>
