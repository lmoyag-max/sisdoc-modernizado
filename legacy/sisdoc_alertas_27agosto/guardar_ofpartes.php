<?PHP
include("conexion_bd.php");
// Programa que graba el documento ingresado y pasa los siguientes parametros
$fechasistema = date("Y/m/d H:i"); 
$fun=$idfuncionario;
$cbo_proc= $val_procedencia;
$cbo_func_proc= $val_funcionario;
$cbo_destino= $val_destino;
$cbo_func_destino= $val_funcionario1;
$td=$tipo_destino;
$tp=$tipo_procedencia; 
$flujo = 0;
$Cbo_Func_Procedencia="";
$Cbo_Func_Destino="";
$tipo_despacho ="D";
if ($Original=="") {
	$Original = "N"; }
/*echo "val arreglo " . $arreglo . "\n";
echo "<br>" . "\n";
echo "val func proc  " . $val_funcionario . "\n";
echo "<br>" . "\n";
echo "val des  " . $val_destino . "\n";
echo "<br>" . "\n";
echo "val func des  " . $val_funcionario1 . "\n";
echo "<br>" . "\n";
echo "tipo des " . $tipo_destino . "\n";
echo "<br>" . "\n";
echo "flujo " . $flujo . "\n";
*/
	
$xx=$idusuario;
$c_usuario=$cusuario;
$id_funcionario=$idfuncionario;
$dia = substr($Txt_fecha_doc,0,2);
$mes = substr($Txt_fecha_doc,3,2);
$año = substr($Txt_fecha_doc,6,4);
$Fecha_Docto = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));
if ($Txt_fecha_inv <> "")
{
$dia = substr($Txt_fecha_inv,0,2);
$mes = substr($Txt_fecha_inv,3,2);
$año = substr($Txt_fecha_inv,6,4);
$Fecha_Inv = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));
}

$op="I";
$flujo=0;
$TxtInterno=0;

#-------- Guarda los datos en tabla de Documento y rescata el ID ----------
$documento_query = "exec ingreso_documentos '" . $Cbo_Tipo_Docto . "','" . $Cbo_Estado_Docto . "','" . 
$xx . "','"  . $TxtInterno . "','" . $TxtOficial . "','" . $TxtExterno . "','" . $Original . "','" . 
$Cbo_Medio . "','" . $tipo_despacho. "','" . $resuelto . "','" . $Fecha_Docto . "','" . 
$fechasistema . "','" . $fechasistema . "','" .  $Fecha_Inv . "','" .
ltrim($TxtMateria) . "','" .  $op. "'"; 
  /*
  Grabar numero interno por tipo de documento

  $documento_query = "exec ingreso_documento '" . $Cbo_Tipo_Docto . "','" . $Cbo_Estado_Docto . "','" . 
  $xx . "','"  . $TxtInterno . "','" . $TxtOficial . "','" . $TxtExterno . "','" . $Original . "','" . 
  $Cbo_Medio . "','" . $tipo_despacho. "','" . $resuelto . "','" . $Fecha_Docto . "','" . 
  $fechasistema . "','" . $fechasistema . "','" . ltrim($TxtMateria) . "','" . $Cbo_Procedencia  .  "','" . $op. "'"; 
  */
  
 
$rs_doc = mssql_query($documento_query,$cn); 
$reg_doc = mssql_fetch_array($rs_doc);
$tot_doc = mssql_num_rows($rs_doc);
$Id_Documento=$reg_doc[1];
$num_interno=$reg_doc[2];

if ($reg_doc[0]==0){
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

#-------- Genera varios Tramites para el documento ingresado----------
if ($sw_ok==0){

$id_nomina=0;
$Cbo_Estado_Compromiso=2;
$vectorint = split ("@",$arregloint);
$largoint=0;
$largoint= $vectorint[0];


// Multi destino Interno
if ($largoint!=0)
{

$x=1;
$sw_ok=0;
for($x=1;$x <=$largoint;$x++)
{
$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
$Cbo_Estado_Compromiso . "','" . $xx . "','" . $Id_Documento . "','" . $Cbo_Procedencia . "','" .
$vectorint[$x] . "','" . $estado_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
$tipo_destino . "','" . $Original . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
$TxtDias . "','" . ' ' . "','" . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
ltrim($TxtObservacion) . "','" . $op . "'";
$rs_tram = mssql_query($tramite_query,$cn); 
$reg_tram = mssql_fetch_array($rs_tram); }
}
else

// Tramite Normal
{
$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
$Cbo_Estado_Compromiso . "','" . $xx . "','" . $Id_Documento . "','" . $Cbo_Procedencia . "','" .
$Cbo_Destinatario . "','" . $estado_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
$tipo_destino . "','" . $Original . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
$TxtDias . "','" . '' . "','" . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
ltrim($TxtObservacion) . "','" . $op . "'";
$rs_tram = mssql_query($tramite_query,$cn); 
$reg_tram = mssql_fetch_array($rs_tram);}

}
$tot_tram = mssql_num_rows($rs_tram);


if ($reg_desc[0]!=0){
	$flujo=1;
	}
}

if ($flujo==0){
    echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="ingreso_ofpartes.php">' . "\n";
	echo '<input type="hidden" name="idusuario" value="' . $xx . '">' . "\n";
	echo '<input type="hidden" name="cusuario" value="' . $c_usuario . '">' . "\n";
	echo '<input type="hidden" name="idfuncionario" value="' . $id_funcionario . '">' . "\n";
	echo '<input type="hidden" name="flujook" value="' . $flujo . '">' . "\n";
	echo '<input type="hidden" name="val_procedencia" value="' . $cbo_proc . '">' . "\n";
	echo '<input type="hidden" name="val_destino" value="' . $cbo_destino . '">' . "\n";
	echo '<input type="hidden" name="val_funcionario" value="' . $cbo_func_proc . '">' . "\n";
	echo '<input type="hidden" name="val_funcionario1" value="' . $cbo_func_destino . '">' . "\n";
	echo '<input type="hidden" name="num_int" value="' . $num_interno . '">' . "\n";
	echo '</form></body></html>'  . "\n";

}	

mssql_close($cn);
?>
