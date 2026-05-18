<?PHP
include("conexion_bd.php");
$fechasistema = date("Y/m/d H:i"); 
$idse=$idsegu;
$iddocum=$iddocu;
$xx=$idusuario;
$c_usuario=$cusuario;
$id_funcionario=$idfuncionario;
$idfun=$fun;
$tipo_procedencia =$tipo_proc;
$Cbo_Procedencia=$val_procedencia;
$Cbo_Func_Procedencia=$val_funcionario;
//$Cbo_Destinatario=$val_destino;
//$Cbo_Func_Destino=$val_funcionario1;
$ofpartes=$checkofpartes2;
$observaciones=$TxtObservacion;
$Original="N";
$op="I";
$sw = 1;
$est_tramite=$estado_tramite;

/* echo "interno" . $interno . "externo" . $externo . $oficial . $oficial . "<br>";
echo "fecha_ini"  . $fecha_ini. "fecha_fin"  . $fecha_fin.  "Cbo_Tipo_Docto"  . $tipodocto ."<br>";
echo "tipo procedencia "  . $tipoprocedencia. "tipodestino"  . $tipodestino.  "Cbo_Procedencia"  . $CboProcedencia . "<br>";
echo  "CboDestinatario" . $xdestinatario . "TxtMateria" . $materia ."<br>";	
*/


$rs_funcionario = mssql_query("SELECT id_dependencia FROM funcionario where id_funcionario = " . $id_funcionario, $cn);

$reg_func = mssql_fetch_array($rs_funcionario);
$Tot_fun = mssql_num_rows($rs_funcionario);

if ($checkofpartes=="")	{
	$tipo_despacho ="D";	}
Else	{		
	$tipo_despacho ="";	}
	
if($Cbo_Func_Procedencia==0)
{
 $Cbo_Func_Procedencia="";
}
if($Cbo_Func_Destino==0)
{
 $Cbo_Func_Destino="";
}		

#-------- Guarda los datos en tabla Tramite ----------
$id_nomina=0;
$estado_compromiso=2;
$vectorint = split ("@",$arregloint);
$largoint=0;
$largoint= $vectorint[0];
$vectorext = split ("@",$arregloext);
$largoext=0;
$largoext= $vectorext[0];
// para que no escriba comillas en observacion del tramite  //
$observaciones= str_replace("\'","",$observaciones);
$observaciones=str_replace(",",", ", $observaciones);


// Multi destino Interno
if ($largoint!=0)
{

$x=1;
$sw_ok=0;
for($x=1;$x <=$largoint;$x++)
{

$tramite_query = "exec ingreso_detalle_factura '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
$estado_compromiso . "','" . $xx . "','" . $iddocum . "','" . $Cbo_Procedencia . "','" .
$vectorint[$x] . "','" . $est_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
$tipo_destino . "','" . $Original . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
$TxtDias . "','" . ' ' . "','" . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
ltrim($observaciones) . "','" . $op . "'";
$rs_tram = mssql_query($tramite_query,$cn); 
$reg_tram = mssql_fetch_array($rs_tram);}

}
else
// Multi destino Externo
if ($largoext!=0)
{
$x=1;
$sw_ok=0;

for($x=1;$x <=$largoext;$x++)

{
$tramite_query = "exec ingreso_detalle_factura '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
$estado_compromiso . "','" . $xx . "','" . $iddocum . "','" . $Cbo_Procedencia . "','" .
$vectorext[$x] . "','" . $est_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
$tipo_destino . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
$TxtDias . "','" . ' ' . "','" . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
ltrim($observaciones) . "','" . $op . "'";
$rs_tram = mssql_query($tramite_query,$cn); 
$reg_tram = mssql_fetch_array($rs_tram);}
}
else
{
// Destino Normal
$tramite_query = "exec ingreso_detalle_factura '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
$estado_compromiso . "','" . $xx . "','" . $iddocum . "','" . $Cbo_Procedencia . "','" .
$Cbo_Destinatario . "','" . $est_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
$tipo_destino . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
$TxtDias . "','" . ' ' . "','" . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
ltrim($observaciones) . "','" . $op . "'";
$rs_tram = mssql_query($tramite_query,$cn); 
$reg_tram = mssql_fetch_array($rs_tram);
}
$tot_tram = mssql_num_rows($rs_tram);

echo '<html><body onload="document.form_i.submit();">';
echo '<form name="form_i" method="post" action="doc_enc_facturas.php">' . "\n";

echo '<input type="hidden" name="idusuario" 				value="' . $xx . '">' . "\n";
echo '<input type="hidden" name="cusuario"		 		value="' . $cusuario . '">' . "\n";
echo '<input type="hidden" name="idfuncionario"	 		value="' . $idfuncionario . '">' . "\n";
echo '<input type="hidden" name="sw_cons" 				value="' . 1 . '">' . "\n";
echo '<input type="hidden" name="grabado" 				value="1">'  . "\n";
echo '<input type="hidden" name="flujook" 					value="' . 8 .'">'  . "\n";
echo '<input type="hidden" name="num_int" 				value="' . 0 . '">'  . "\n";
echo '<input type="hidden" name="Txt_fecha_ini" 		value="' . $fecha_ini . '">' . "\n";
echo '<input type="hidden" name="Txt_fecha_fin" 		value="' . $fecha_fin . '">' . "\n";
echo '<input type="hidden" name="Cbo_Tipo_Docto" 	value="' . $tipodocto . '">' . "\n";
echo '<input type="hidden" name="TxtInterno" 				value="' . $interno . '">' . "\n";
echo '<input type="hidden" name="TxtOficial" 				value="' . $oficial . '">' . "\n";
echo '<input type="hidden" name="TxtExterno" 			value="' . $externo . '">' . "\n";
echo '<input type="hidden" name="TxtMateria" 			value="' . $materia . '">' . "\n";
echo '<input type="hidden" name="desc" 					value="' . $descrip . '">' . "\n";
echo '<input type="hidden" name="Cbo_Procedencia" 	value="' . $cboprocedencia . '">' . "\n";
echo '<input type="hidden" name="Cbo_Destinatario" 	value="' . $xdestinatario . '">' . "\n";
echo '<input type="hidden" name="tipo_procedencia" 	value="' . $tipoprocedencia . '">' . "\n";
echo '<input type="hidden" name="tipo_destino" 			value="' . $xtipodestino . '">' . "\n";
echo '<input type="hidden" name="dependencia_usuario" value="' . $reg_func[id_dependencia] . '">' . "\n";
echo '<input type="hidden" name="avanzada"					value="' . $avanzada . '">' . "\n";
echo '<input type="hidden" name="flujo1" 						value="' . 0 . '">'  . "\n";
 

echo '</form></body></html>' . "\n";

mssql_close($cn);
?>
