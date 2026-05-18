<?PHP
include("conexion_bd.php");
// Programa que graba el documento ingresado y pasa los siguientes parametros


/*echo "$cbo_proc= " . $val_procedencia . "cbo_func_proc= ". $val_funcionario ."$cbo_destino=" . $val_destino . "<br>";
echo "$cbo_func_destino= " . $val_funcionario1 . "$td=" .$tipo_destino . "$tp=" . $tipo_procedencia . "<br>"; 
*/


$fechasistema   = date("Y/m/d H:i"); 
$fun=$idfuncionario;
$cbo_proc= $val_procedencia;
$cbo_func_proc= $val_funcionario;
$cbo_destino= $val_destino;
$cbo_func_destino= $val_funcionario1;
$td=$tipo_destino;
$tp=$tipo_procedencia; 
$flujo = 0;
if($Cbo_Func_Procedencia==0)
	{
	 $Cbo_Func_Procedencia="";
	}
if($Cbo_Func_Destino==0)
	{
	 $Cbo_Func_Destino="";
	}
if ($checkofpartes=="")
	{
		$tipo_despacho ="D";
	}
Else
	{		
	$tipo_despacho ="";
	}	
if ($Original=="")
	{
	$Original = "N"; 
	}
if ($txtexp=="")
	{
    $txtexp ="N";
	}
else 
	{
    $txtexp     ="S";
	}
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
$numexp=$txtexped;
$codibm =$Txtidcodibm;
// para que no escriba comillas //
//$TxtMateria= str_replace("'"," ",$TxtMateria);
// para que no escriba cremillas //
$TxtMateria= str_replace("\'","",$TxtMateria);
$TxtMateria =str_replace(",",", ", $TxtMateria);



// para que no escriba comillas en observacion del tramite  //
$TxtObservacion= str_replace("\'","",$TxtObservacion);
$TxtObservacion=str_replace(",",", ", $TxtObservacion);



// graba  en tabla de expediente y rescata el id de expediente para luego grabarlo en la tabla de documentos 
/*$expediente_query ="exec ingreso_expediente '"  . $Cbo_Tipo_Docto . "','" . $Cbo_Estado_Docto . "','" .  $xx . "','"  . $TxtInterno . "','" . $TxtOficial . "','" . $TxtExterno . "','" . $Original . "','" . $Cbo_Medio . "','" . $tipo_despacho. "','" . $resuelto . "','" . $Fecha_Docto . "','" . $fechasistema . "','" . $fechasistema . "','" .  $Fecha_Inv . "','" . ltrim($TxtMateria) . "','" . $Cbo_Procedencia  .  "','" . $txtexp . "','" . $numexp . "','" . $txtdescrip. "','" . $op . "'";
*/

// version  para que considere tabla de numero_interno1 y rescate numero interno
$codibm='';
//$expediente_query ="exec ingreso_expediente2 '"  . $Cbo_Tipo_Docto . "','" . $Cbo_Estado_Docto . "','" .  $xx . "','"  . $TxtInterno . "','" . $TxtOficial . "','" . $TxtExterno //. "','" . $Original . "','" . $Cbo_Medio . "','" . $tipo_despacho. "','" . $resuelto . "','" . $Fecha_Docto . "','" . $fechasistema . "','" . $fechasistema . "','" .  $Fecha_Inv . "','" . //ltrim($TxtMateria) . "','" . $Cbo_Procedencia  .  "','" . $tp . "','" . $txtexp . "','" . $numexp . "','" . $txtdescrip. "','" . $codibm . "','" . $op . "'";
$expediente_query ="exec ingreso_expediente2_nuevo '"  . $Cbo_Tipo_Docto . "','" . $Cbo_Estado_Docto . "','" .  $xx . "','"  . $TxtInterno . "','" . $TxtOficial . "','" . $TxtExterno . "','" . $Original . "','" . $Cbo_Medio . "','" . $tipo_despacho. "','" . $resuelto . "','" . $Fecha_Docto . "','" . $fechasistema . "','" . $fechasistema . "','" .  $Fecha_Inv . "','" . ltrim($TxtMateria) . "','" . $Cbo_Procedencia  .  "','" . $tp . "','" . $txtexp . "','" . $numexp . "','" . $txtdescrip. "','" . $codibm . "','" . $op . "'";
// solo para prueba del numero interno 
//$expediente_query ="exec interno '"  . $Cbo_Tipo_Docto . "','" . $Cbo_Estado_Docto . "','" .  $xx . "','"  . $TxtInterno . "','" . $TxtOficial . "','" . $TxtExterno . "','" . $Original . "','" . $Cbo_Medio . "','" . $tipo_despacho. "','" . $resuelto . "','" . $Fecha_Docto . "','" . $fechasistema . "','" . $fechasistema . "','" .  $Fecha_Inv . "','" . ltrim($TxtMateria) . "','" . $Cbo_Procedencia  .  "','" . $tp . "','" . $txtexp . "','" . $numexp . "','" . $txtdescrip. "','" . $codibm . "','" . $op . "'";

//echo "prueba" . $expediente_query;
$rs_exp = mssql_query($expediente_query,$cn); 
$reg_exp = mssql_fetch_array($rs_exp);


/*If ($reg_exp[0] ==0  )
 { 
    
    echo '<script>';
    echo 'alert("Esta Procedencia con este tipo de documento no tienen correlativo ")';
    echo '</script>';
}    
else
 { 
*/

$Id_Documento=$reg_exp[1];
$num_interno=$reg_exp[2];
$numexp= $reg_exp[3];

// para casos de tipos de documentos que no estan registrados en numero_interno1 //
//  se grabar el documento pero con numero interno 1    //
//echo "documento" . $Id_Documento . "numero interno " . $num_interno . "exp" . $numexp . "total" . $total ;;

/* 
$documento_query = "exec Ingreso_docto2 '" . $Cbo_Tipo_Docto . "','" . $Cbo_Estado_Docto . "','" . 
  $xx . "','"  . $TxtInterno . "','" . $TxtOficial . "','" . $TxtExterno . "','" . $Original . "','" . 
  $Cbo_Medio . "','" . $tipo_despacho. "','" . $resuelto . "','" . $Fecha_Docto . "','" . 
  $fechasistema . "','" . $fechasistema . "','" .  $Fecha_Inv . "','" . ltrim($TxtMateria) . "','" . $Cbo_Procedencia  .  "','" . $numexp .  "','" . $op. "'"; 
  echo $documento_query;  
 
$rs_doc = mssql_query($documento_query,$cn); 
$reg_doc = mssql_fetch_array($rs_doc);
$tot_doc = mssql_num_rows($rs_doc);
$Id_Documento=$reg_doc[1];
$num_interno=$reg_doc[2];
*/
// cambio para que no lo haga cuando el id_documento es 0 , siempre debe venir con valor para grabr en otras tablas 25/02/2007
//if ($reg_exp[0]==0)  dejaba registros en tramite con id_documento=0, faltaba la otra condición
if (($reg_exp[0]==0) && ($Id_Documento <> 0))

{
  #-------- Guardar descriptores----------
  $vector = split ("@",$arreglo);
  $largo=0;
  $largo= $vector[0];

  $x=1;
  $sw_ok=0;
  for($x=1;$x <=$largo;$x++)
  {
     $descriptor_query="exec ingreso_descriptor '" . $vector[$x] . "','" . $Id_Documento . "','" . $op . "'";
     $rs_desc = mssql_query($descriptor_query,$cn); 
     $reg_desc = mssql_fetch_array($rs_desc);
     $tot_desc = mssql_num_rows($rs_desc);
     if ($reg_desc[0]!=0)
	 {   $sw_ok=$sw_ok+1;}
     }
  
#-------- Genera varios Tramites para el documento ingresado----------
	if ($sw_ok==0)
	{
		$id_nomina=0;
		$Cbo_Estado_Compromiso=2;
		$vectorint = split ("@",$arregloint);
		$largoint=0;
		$largoint= $vectorint[0];
		$vectorext = split ("@",$arregloext);
		$largoext=0;
		$largoext= $vectorext[0];

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
		$reg_tram = mssql_fetch_array($rs_tram); 
		}
	}
	else
// MUlti destino Externo
	if ($largoext!=0)
	{
		$x=1;
		$sw_ok=0;
		for($x=1;$x <=$largoext;$x++)
		{
		$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
		$Cbo_Estado_Compromiso . "','" . $xx . "','" . $Id_Documento . "','" . $Cbo_Procedencia . "','" .
		$vectorext[$x] . "','" . $estado_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
		$tipo_destino . "','" . $Original . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
		$TxtDias . "','" . '' . "','" . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
		ltrim($TxtObservacion) . "','" . $op . "'";
	
		$rs_tram = mssql_query($tramite_query,$cn); 
		$reg_tram = mssql_fetch_array($rs_tram);
		}
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
		$reg_tram = mssql_fetch_array($rs_tram);
		}

}
	$tot_tram = mssql_num_rows($rs_tram);
	if ($reg_desc[0]!=0)
	{
	$flujo=1;
	}
}
//}

if ($flujo==0)
    {
	
  	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="ingreso_docto2_2.php">' . "\n";
		
	echo '<input type="hidden" name="idusuario" value="' . $xx . '">' . "\n";
	echo '<input type="hidden" name="cusuario" value="' . $c_usuario . '">' . "\n";
	echo '<input type="hidden" name="idfuncionario" value="' . $id_funcionario . '">' . "\n";
	echo '<input type="hidden" name="flujook" value="' . $flujo . '">' . "\n";
	echo '<input type="hidden" name="val_procedencia" value="' . $cbo_proc . '">' . "\n";
	echo '<input type="hidden" name="val_destino" value="' . $cbo_destino . '">' . "\n";
	echo '<input type="hidden" name="val_funcionario" value="' . $cbo_func_proc . '">' . "\n";
	echo '<input type="hidden" name="val_funcionario1" value="' . $cbo_func_destino . '">' . "\n";
	echo '<input type="hidden" name="num_int" value="' . $num_interno . '">' . "\n";
	echo '<input type="hidden" name="txtexped" value="' . $numexp . '">' . "\n";
	
	echo '</form></body></html>'  . "\n";
	}	

mssql_close($cn);
?>
