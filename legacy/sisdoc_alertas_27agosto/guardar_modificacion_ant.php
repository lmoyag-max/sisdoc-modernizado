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
	$tramite_query = "exec mantenedor_tramite '" . $idseg . "','" . $Cbo_Destinatario . "','" .
	$Cbo_Func_Destino . "','" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
	$TxtDias . "','" . ltrim($TxtObservacion) . "','" . $tipo_destino . "','" . 
	$fechasistema .  "','" . $op . "'";
	$rs_tramite = mssql_query($tramite_query,$cn); 
	$reg_tramite = mssql_fetch_array($rs_tramite);

	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="modifica_tramite.php">';
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
$numint=$TxtInterno;
$numofi=$TxtOficial;
$numext=$TxtExterno;
$cbo_tipo=$Cbo_Tipo_Docto;
$dia = substr($Txt_fecha_doc,0,2);
$mes = substr($Txt_fecha_doc,3,2);
$año = substr($Txt_fecha_doc,6,4);
$Fecha_Docto = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));
if ($Txt_fecha_inv !="NULL"){
$dinv = substr($Txt_fecha_inv,0,2);
$minv = substr($Txt_fecha_inv,3,2);
$ainv = substr($Txt_fecha_inv,6,4);
$Fecha_inv = date("Y/m/d H:i", mktime(0, 0,0, $minv, $dinv, $ainv));
}

#-------- Guarda los datos en tabla de Documento y rescata el ID ----------
$documento_query = "exec mantenedor_documentos '" . $iddocumento . "','" . 
$TxtOficial . "','" . $TxtExterno . "','" .
$Fecha_Docto . "','" . 
$Fecha_inv . "','" . 
$fechasistema . "','" . ltrim($TxtMateria) . "','" . $txtexped . "','" . $op . "'"; 

$rs_doc = mssql_query($documento_query,$cn); 
$reg_doc = mssql_fetch_array($rs_doc);
$tot_doc = mssql_num_rows($rs_doc);


#-------- Borra descriptores----------
$del_descriptor="exec ingreso_descriptor '" . 0 . "','" . $iddocumento . "','" . $op . "'";
$del_desc = mssql_query($del_descriptor,$cn);
$reg_descrip = mssql_fetch_array($del_desc);

#-------- Guarda descriptores----------


$vector = split ("@",$arreglo);
$largo=0;
$largo= $vector[0];
$x=1;
$sw_ok=0;
$op="I"; 
for($x=1;$x <=$largo;$x++)

{$descriptor_query="exec ingreso_descriptor '" . $vector[$x] . "','" . $iddocumento . "','" . $op . "'";
$rs_desc = mssql_query($descriptor_query,$cn); 
$reg_desc = mssql_fetch_array($rs_desc);
$tot_desc = mssql_num_rows($rs_desc);
if ($reg_desc[0]!=0){
	$sw_ok=$sw_ok+1;}
}
	
	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="modifica_cabecera2.php">';
	echo '<input type="hidden" name="idusuario" value="' . $xx . '">';
	echo '<input type="hidden" name="cusuario" value="' . $c_usuario . '">';
	echo '<input type="hidden" name="idfuncionario" value="' . $fun . '">';
	echo '<input type="hidden" name="flujook" value="' . $sw . '">';
	echo '<input type="hidden" name="Cbo_Tipo_Docto" value="' . $cbo_tipo . '">';
	echo '<input type="hidden" name="TxtInterno" value="' . $numint . '">';
	echo '<input type="hidden" name="iddocum" value="' . $iddocumento . '">';
	echo "</form></body></html>";
}

mssql_close($cn);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body></body> 
</html>
