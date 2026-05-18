<?PHP
include("conexion_bd.php");
$fecha = date("d/m/y"); 
$fechasistema = date("Y/m/d H:i"); 
$cbo_proc= $val_procedencia;
$cbo_func_proc= $val_funcionario;
$cbo_destino= $val_destino;
$cbo_func_destino= $val_funcionario1;
$td=$tipo_destino;
$tp=$tipo_procedencia; 
$id_doc=$iddocant;
$id_tra=$idtraant;
echo "td " . $td . "tp " . $tp . "cbopro " .  $cbo_proc . "fupro " . $cbo_func_proc ;
"<br>";
echo "id_doc " . $id_doc . "idtra " . $id_tra . "cbodes " . $cbo_destino .  "fudes " . $cbo_func_destino ;
if ($checkofpartes=="")
	{
	$tipo_despacho ="D";
	}
Else
	{		
	$tipo_despacho ="";
	}	
if ($Original=="") {
	$Original = "N"; }
	
$xx=$idusuario;
$c_usuario=$cusuario;
$id_funcionario=$idfuncionario;
$dia = substr($Txt_fecha_doc,0,2);
$mes = substr($Txt_fecha_doc,3,2);
$año = substr($Txt_fecha_doc,6,4);
$Fecha_Docto = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

$op="I";
$flujo=0;
#-------- Guarda los datos en tabla de Documento ----------
$documento_query = "exec ingreso_documentos '" . $Cbo_Tipo_Docto . "','" . $Cbo_Estado_Docto . "','" . 
$xx . "','"  . $TxtInterno . "','" . $TxtOficial . "','" . $TxtExterno . "','" . $Original . "','" . 
$Cbo_Medio . "','" . $tipo_despacho. "','" . $resuelto . "','" . $Fecha_Docto . "','" . 
$fechasistema . "','" . $fechasistema . "','" . ltrim($TxtMateria) . "','" . $op . "'"; 

$rs_doc = mssql_query($documento_query,$cn); 
$reg_doc = mssql_fetch_array($rs_doc);
$tot_doc = mssql_num_rows($rs_doc);

if ($reg_doc[0]==0){

#-------- rescata el ID de Documento----------
$rs_f5= mssql_query("SELECT @@IDENTITY AS 'Identity'", $cn);
$reg_f5 = mssql_fetch_array($rs_f5);
$Id_Documento = $reg_f5[Identity];

#-------- Guardar descriptores----------
$vector = split ("@",$arreglo);
$largo=0;
$largo= $vector[0];
$x=1;
$sw_ok=0;
for($x=1;$x <=$largo;$x++)

{$descriptor_query="exec ingreso_descriptor '" . $vector[$x] . "','" . $Id_Documento . "','" . $op . "'";

$rs_desc = mssql_query($descriptor_query,$cn); 
$reg_desc = mssql_fetch_array($rs_desc);
$tot_desc = mssql_num_rows($rs_desc);
if ($reg_desc[0]!=0){
	$sw_ok=$sw_ok+1;}
}

if ($sw_ok==0){
#-------- Guarda los datos en tabla Tramite ----------
$id_nomina=0;
$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
$Cbo_Estado_Compromiso . "','" . $xx . "','" . $Id_Documento . "','" . $cbo_proc . "','" .
$Cbo_Destinatario . "','" . $estado_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
$tipo_destino . "','" . $Original . "','" . $cbo_func_proc . "','" . $Cbo_Func_Destino . "','" .
$TxtDias . "','" . ' ' .  "','"  . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
ltrim($TxtObservacion) . "','" . $op . "'";
$rs_tram = mssql_query($tramite_query,$cn); 
$reg_tram = mssql_fetch_array($rs_tram);
$tot_tram = mssql_num_rows($rs_tram);

}
$estado=4;
$opc="E";
$comp=0;
$op = 1;

$tram_query = "exec mod_estado_tramite '" . $id_tra . "','" . $fechasistema . "','" .
$estado . "','" . $opc . "','" .  $op .  "','" . $comp . "'";
$rs_tram.close;
$reg_tram.close;
$rs_tram = mssql_query($tram_query,$cn); 
$reg_tram = mssql_fetch_array($rs_tram);

$hijo_query = "exec relacion_doc '" . $id_doc . "','" . $Id_Documento . "'";
$rs_hijo = mssql_query($hijo_query,$cn); 
$reg_hijo = mssql_fetch_array($rs_hijo);

if ($reg_desc[0]!=0){
	$flujo=1;
	}
}

if ($flujo==0){
//$flujo=5;
echo '<html><body onload="vuelve_ingreso();">';
//echo '<html><body onload="document.form1.submit();">';
//	 echo '<html><body onload="alert(' . "'" . $cusuario . "'" . ');">'; 
	
	echo '<form name="form1" method="post" action="ingreso_con_docto.php">';
	echo '<input type="hidden" name="idusuario" value="' . $xx . '">';
	echo '<input type="hidden" name="cusuario" value="' . $c_usuario . '">';
	echo '<input type="hidden" name="idfuncionario" value="' . $id_funcionario . '">';
	echo '<input type="hidden" name="flujook" value="' . $flujo . '">';
	echo '<input type="hidden" name="val_procedencia" value="' . 0 . '">';
	echo '<input type="hidden" name="val_destino" value="' . 0 . '">';
	echo '<input type="hidden" name="val_funcionario" value="' . 0 . '">';
	echo '<input type="hidden" name="val_funcionario1" value="' . 0 . '">';
	echo '<input type="hidden" name="iddocant" value="' . 0 . '">';
	echo '<input type="hidden" name="idtraant" value="' . 0 . '">';
	
	echo "</form></body></html>";
}

mssql_close($cn);
?>
<SCRIPT  language="JavaScript">
var flujook= <?php echo $flujo; ?>;  
var val_procedencia=<?php echo $cbo_proc; ?>;
var val_funcionario=<?php echo $cbo_func_proc; ?>;
var val_destino=<?php echo $cbo_destino; ?>;
var val_funcionario1=<?php echo $cbo_func_destino; ?>;
var tipo_procedencia="<?php echo $tp; ?>";
var tipo_destino="<?php echo $td; ?>";
var iddocant=<?php echo $id_doc; ?>;
var idtraant=<?php echo $id_tra; ?>;

function vuelve_ingreso() { 
  if (flujook==0) {
    document.form1.submit();}
  else{
  	  history.go(-1);
  	  }
}


</script>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body></body> 
</html>
