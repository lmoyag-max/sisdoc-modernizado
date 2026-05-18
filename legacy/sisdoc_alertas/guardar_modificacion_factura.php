<?PHP
include("conexion_bd.php");
// Programa que graba el documento ingresado y pasa los siguientes parametros


$fechasistema = date("Y/m/d H:i"); 
$fun=$idfuncionario;
$xx=$idusuario;
$c_usuario=$cusuario;
$acc=$accion;
$op="U";
$sw=1;
// para que no escriba comillas en observacion del tramite  //
$TxtObservacion= str_replace("\'","",$TxtObservacion);
$TxtObservacion=str_replace(",",", ", $TxtObservacion);


if($accion==2)
{
/*echo "cbodest...." . $Cbo_Destinatario . "<br>" . "tipodest....." . $tipo_destino .
"<br>" . "Func....." . $Cbo_Func_Destino . "<br>" . "val1....." . $val_funcionario1 . "<br>" . "seg....." . $idseguim .
"<br>" . "acc....." . $accion;
*/
	$idseg=$idseguim;
	$tramite_query = "exec mantenedor_tramite_factura '" . $idseg . "','" . $Cbo_Destinatario . "','" .
	$Cbo_Func_Destino . "','" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
	$TxtDias . "','" . ltrim($TxtObservacion) . "','" . $tipo_destino . "','" . 
	$fechasistema .  "','" . $op . "'";
	//echo $tramite_query;
	$rs_tramite = mssql_query($tramite_query,$cn); 
	$reg_tramite = mssql_fetch_array($rs_tramite);
	
	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="modifica_tramite_factura.php">';
	echo '<input type="hidden" name="idusuario" value="' . $xx . '">';
	echo '<input type="hidden" name="cusuario" value="' . $c_usuario . '">';
	echo '<input type="hidden" name="idfuncionario" value="' . $fun . '">';
	echo '<input type="hidden" name="flujook" value="' . $sw . '">';
	echo '<input type="hidden" name="idseguim" value="' . $idseg . '">';
	echo '<input type="hidden" name="num_int" value="' . $num_int . '">';
	echo "</form></body></html>";
	
}
else
{
$numint=$num_factura;
$cbo_tema=$Cbo_tema_fact;
$dia = substr($Txt_fecha_doc,0,2);
$mes = substr($Txt_fecha_doc,3,2);
$año = substr($Txt_fecha_doc,6,4);
$Fecha_Docto = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

$dia = substr($Txt_fecha_timbre,0,2);
$mes = substr($Txt_fecha_timbre,3,2);
$año = substr($Txt_fecha_timbre,6,4);
$Fecha_rec = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));



#-------- Guarda los datos en tabla de Documento y rescata el ID ----------
/*$documento_query = "exec mantenedor_facturas '" . $idfactura . "','" . $cbotema . "','". $cbo_tipo ."','".  
$numfactura . "','".$Fecha_Docto . "','" . $Fecha_rec . "','"  .
$fechasistema . "','" . ltrim($Txtdescripcion) . "','" .  $op . "'"; 
*/

//echo "mnonto". $monto; 
/*$documento_query = "exec mantenedor_facturas2 '" . $idfactura . "','" .  $monto      . "','". $tema_factura . "','". $tipo_factura ."','".  
$numfactura . "','".$Fecha_Docto . "','" . $Fecha_rec . "','"  .
$fechasistema . "','" . ltrim($Txtdescripcion) . "','" .  $op . "'"; 
*/
$documento_query = "exec mantenedor_facturas '" . $idfactura . "','" .  $monto      . "','". $tema_factura . "','". $tipo_factura ."','".  
$numfactura . "','".$Fecha_Docto . "','" . $Fecha_rec . "','"  .
$fechasistema . "','" . ltrim($Txtdescripcion) . "','" .  $op . "','" . $rut . "'"; 

//echo "documento_query". $documento_query;
$rs_doc = mssql_query($documento_query,$cn); 
$reg_doc = mssql_fetch_array($rs_doc);
$tot_doc = mssql_num_rows($rs_doc);

	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="modifica_cabecera2_factura.php">';
	echo '<input type="hidden" name="idusuario" value="' . $xx . '">';
	echo '<input type="hidden" name="cusuario" value="' . $c_usuario . '">';
	echo '<input type="hidden" name="idfuncionario" value="' . $fun . '">';
    echo '<input type="hidden" name="flujook" value="' . $sw . '">';
	echo '<input type="hidden" name="Cbo_tema_fact" value="' . $cbo_tema . '">';
	echo '<input type="hidden" name="numfactura" value="' . $numfactura. '">';
	echo '<input type="hidden" name="iddocum" value="' . $idfactura. '">';
	echo "</form></body></html>";
	
}


mssql_close($cn);
?>

