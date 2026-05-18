<?PHP
include("conexion_bd.php");
// Programa que graba el documento ingresado y pasa los siguientes parametros



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

$xx=$idusuario;
$c_usuario=$cusuario;
$id_funcionario=$idfuncionario;


$dia = substr($Txt_fecha_doc,0,2);
$mes = substr($Txt_fecha_doc,3,2);
$año = substr($Txt_fecha_doc,6,4);
$Fecha_fact = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

$dia = substr($Txt_fecha_recep,0,2);
$mes = substr($Txt_fecha_recep,3,2);
$año = substr($Txt_fecha_recep,6,4);
$Fecha_recep = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));


$op="I";
$flujo=0;

// para que no escriba comillas //
//$TxtMateria= str_replace("'"," ",$TxtMateria);
// para que no escriba cremillas //
$TxtMateria= str_replace("\'","",$TxtMateria);
$TxtMateria =str_replace(",",", ", $TxtMateria);
// sacando digito 
$dig_rut =strpos($el_rut,'-');
$digito =substr($el_rut,$dig_rut+1,1);

// sacando el rut 

$aux_rut=substr($el_rut,0,-1);
      
   if(strlen($aux_rut)>8) 
   {
    $aux_rut = substr($aux_rut,0,-1);
   }
   
   $aux_rut=str_replace("-","",$aux_rut);
   $aux_rut=str_replace(".","",$aux_rut);



// facturas 
$Cbo_Estado_fact=1; 
$query_factura="exec ingreso_facturas '"  . $Cbo_tema_facturas . "','" . $aux_rut . "','" . $Cbo_Estado_fact . "','"  . $num_factura . "','" .  $monto .   "','" . $TxtMateria .  "','" . $Fecha_fact.  "','" . $Fecha_recep . "','" . $fechasistema ."','" . $fechasistema ."','" .$xx . "','"   . $op . "'";
//echo $query_factura ;
$rs_fac = mssql_query($query_factura,$cn); 
$reg_fac = mssql_fetch_array($rs_fac);



$Id_Documento=$reg_fac[1];
// cambio para que no lo haga cuando el id_documento es 0 , siempre debe venir con valor para grabr en otras tablas 25/02/2007
//if ($reg_exp[0]==0)  dejaba registros en tramite con id_documento=0, faltaba la otra condición
//echo "reg_exp" . $reg_fac[0] . "doc" . $Id_Documento  . "<br>";
if (($reg_fac[0]==0) && ($Id_Documento <> 0))

{
#-------- Genera varios Tramites para el documento ingresado----------
	if ($sw_ok==0)
	{
		$id_nomina=0;
		$Cbo_Estado_Compromiso=2;
		$estado_tramite=1;
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
		$tramite_query = "exec ingreso_detalle_factura '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
		$Cbo_Estado_Compromiso . "','" . $xx . "','" . $Id_Documento . "','" . $Cbo_Procedencia . "','" .
		$vectorint[$x] . "','" . $estado_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
		$tipo_destino . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
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
		$tramite_query = "exec ingreso_detalle_factura '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
		$Cbo_Estado_Compromiso . "','" . $xx . "','" . $Id_Documento . "','" . $Cbo_Procedencia . "','" .
		$vectorext[$x] . "','" . $estado_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
		$tipo_destino ."','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
		$TxtDias . "','" . '' . "','" . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
		ltrim($TxtObservacion) . "','" . $op . "'";
	
		$rs_tram = mssql_query($tramite_query,$cn); 
		$reg_tram = mssql_fetch_array($rs_tram);
		}
	}
	else
// Tramite Normal
		{
		$tramite_query = "exec ingreso_detalle_factura '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
		$Cbo_Estado_Compromiso . "','" . $xx . "','" . $Id_Documento . "','" . $Cbo_Procedencia . "','" .
		$Cbo_Destinatario . "','" . $estado_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
		$tipo_destino . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
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
	// grabar proveedor 
	$busca_prov= "select * from proveedores where rut_prov=". $aux_rut;
	$re_prov=mssql_query($busca_prov);
	$r_pr=mssql_fetch_array($re_prov);
	if (mssql_num_rows($re_prov) ==0)
	   {
	   $proveedor= "exec ingreso_proveedor '" . $aux_rut. "','". $digito ."','" . $nombreproveed . "'";
	 
	   $r_grabpro=mssql_query($proveedor);
		
	   }
}
//}

if ($flujo==0)
    {
	
	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="ingreso_facturas_ofpartes.php">' . "\n";
		
	echo '<input type="hidden" name="idusuario" value="' . $xx . '">' . "\n";
	echo '<input type="hidden" name="cusuario" value="' . $c_usuario . '">' . "\n";
	echo '<input type="hidden" name="idfuncionario" value="' . $id_funcionario . '">' . "\n";
	echo '<input type="hidden" name="flujook" value="' . $flujo . '">' . "\n";
	echo '<input type="hidden" name="val_procedencia" value="' . $cbo_proc . '">' . "\n";
	echo '<input type="hidden" name="val_destino" value="' . $cbo_destino . '">' . "\n";
	echo '<input type="hidden" name="val_funcionario" value="' . $cbo_func_proc . '">' . "\n";
	echo '<input type="hidden" name="val_funcionario1" value="' . $cbo_func_destino . '">' . "\n";
	
	echo '</form></body></html>'  . "\n";
	}	

mssql_close($cn);
?>
