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
$Cbo_Func_Destino=$val_funcionario1;
$ofpartes=$checkofpartes2;
$observaciones=$TxtObservacion;
$Original="N";
$op="I";
$sw = 1;
$est_tramite=$estado_tramite;
$dedonde=$origen;
//echo "nomina de grabar" . $nom ;
$nomina=$nom;
if($nomina==0) {
       $est_tramite=6;
}
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
$Totreg =0;
// En caso que entre nuevamente a grabar pero de la 1era pantalla no hay problema //
/// buscar los tramites que se han hecho en  caso que sean para ambos destinos ///
if ($flag==1)
{
$consulta=" select * from tramite where id_usuario=$xx  and tipo_destinatario =". "'$tipo_destino'" .  " and tipo_procedencia =". "'$tipo_procedencia'" . " and id_documento = " . $iddocum . " and  id_procedencia = " . $Cbo_Procedencia . " and id_estado_tramite =" . 1   ;
//echo $consulta ;
$rs_doc=$consulta;
$rs_documento=mssql_query($rs_doc);
$Totreg = mssql_num_rows($rs_documento);
}
if ($Totreg <>0)
{
	echo "<script>\n";
	echo " alert('Ya existen  trámites con este tipo de destino');\n";
 	echo "</script>\n";
	echo '<html><body onload="document.form_i.submit();">';
	echo '<form name="form_i" method="post" action="deriva_sdoc.php">' . "\n";
    echo '<input type="hidden" name="destino" 		value= "' . $tipo_destino . '">'  . "\n";   
  	echo '<input type="hidden" name="grabado" 		value="2">'  . "\n";
	echo '<input type="hidden" name="cusuario" 		value= "' . $c_usuario . '">'  . "\n";   
	echo '<input type="hidden" name="idusuario" 	value="' . $xx . '">'  . "\n";
	echo '<input type="hidden" name="iddocum" 		value="' . $iddocum . '">'  . "\n";
	echo '<input type="hidden" name="idseguim" 		value="' . $idse . '">'  . "\n";
	echo '<input type="hidden" name="idfuncionario" value="' . $id_funcionario . '">'  . "\n";
	echo '<input type="hidden" name="txtnomina" 	value="' . $txtnomina . '">'  . "\n";
	echo '<input type="hidden" name="txtagnor" 		value="' . $txtagnor . '">'  . "\n";
	echo '<input type="hidden" name="origen" 		value="' . $origen . '">'  . "\n";
	echo '<input type="hidden" name="flag" 			value="' . $flag . '">'  . "\n";
	echo "</form></body></html>;\n";
}
else
{ 	

/// fin de consulta ////

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
	$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
	$estado_compromiso . "','" . $xx . "','" . $iddocum . "','" . $Cbo_Procedencia . "','" .
	$vectorint[$x] . "','" . $est_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
	$tipo_destino . "','" . $Original . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
	$TxtDias . "','" . '' . "','" . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
	ltrim($observaciones) . "','" . $op . "'";
	$rs_tram = mssql_query($tramite_query,$cn); 
	$reg_tram = mssql_fetch_array($rs_tram);}
       echo "1" ;
}
else
// Multi destino Externo
if ($largoext!=0)
{
$x=1;
$sw_ok=0;
for($x=1;$x <=$largoext;$x++)

{
	$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
	$estado_compromiso . "','" . $xx . "','" . $iddocum . "','" . $Cbo_Procedencia . "','" .
	$vectorext[$x] . "','" . $est_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
	$tipo_destino . "','" . $Original . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
	$TxtDias . "','" .'' . "','" . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
	ltrim($observaciones) . "','" . $op . "'";
	$rs_tram = mssql_query($tramite_query,$cn); 
	$reg_tram = mssql_fetch_array($rs_tram);}
	echo "2";
}
else
{
// Destino Normal
if ($est_tramite!= 6)
	{
	$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
	$estado_compromiso . "','" . $xx . "','" . $iddocum . "','" . $Cbo_Procedencia . "','" .
	$Cbo_Destinatario . "','" . $est_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
	$tipo_destino . "','" . $Original . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
	$TxtDias . "','" . '' . "','" . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
	ltrim($observaciones) . "','" . $op . "'";
	$rs_tram = mssql_query($tramite_query,$cn); 
	$reg_tram = mssql_fetch_array($rs_tram);
	echo "3" ;
	}
else
	{
	$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
	$estado_compromiso . "','" . $xx . "','" . $iddocum . "','" . $Cbo_Procedencia . "','" .
	$Cbo_Destinatario . "','" . $est_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
	$tipo_destino . "','" . $Original . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
	$TxtDias . "','" . $fechasistema . "','" . $fechasistema . "','" . $fechasistema . "','" . $fechasistema . "','" .
	ltrim($observaciones) . "','" . $op . "'";
	$rs_tram = mssql_query($tramite_query,$cn); 
	$reg_tram = mssql_fetch_array($rs_tram);
	 echo "5";
	}
}
$tot_tram = mssql_num_rows($rs_tram);

$opcion="E";
$op= 1;
$estado= 4;
$compromiso=2;

	/*----modifica estado del tramite anterior ----*/
$rs_estado="exec mod_estado_tramite '" . $idse. "','" . $fechasistema. "','" . $estado . "','" . $opcion . "','" .
$op . "','" . $compromiso. "'";
$rs_est = mssql_query($rs_estado,$cn); 


echo '<html><body onload="document.form_i.submit();">';
if ($flag ==2)
{
echo '<form name="form_i" method="post" action="multi_recib.php">' . "\n";
echo '<input type="hidden" name="txtagnor" 	value="' . $txtagnor . '">';
echo '<input type="hidden" name="txtnomina" value="' . $txtnomina. '">';
}

else 

if ($flag==1)
{
echo '<form name="form_i" method="post" action="deriva_sdoc.php">' . "\n";
echo '<input type="hidden" name="destino" 	value= "' . $tipo_destino . '">'  . "\n";   
}
echo '<input type="hidden" name="grabado" 		value="2">'  . "\n";
echo '<input type="hidden" name="cusuario" 		value= "' . $c_usuario . '">'  . "\n";   
echo '<input type="hidden" name="idusuario" 	value="' . $xx . '">'  . "\n";
echo '<input type="hidden" name="iddocum" 		value="' . $iddocum . '">'  . "\n";
echo '<input type="hidden" name="idseguim" 		value="' . $idse . '">'  . "\n";
echo '<input type="hidden" name="idfuncionario" value="' . $id_funcionario . '">'  . "\n";
echo '<input type="hidden" name="txtnomina" 	value="' . $txtnomina . '">'  . "\n";
echo '<input type="hidden" name="txtagnor" 		value="' . $txtagnor . '">'  . "\n";
echo '<input type="hidden" name="origen" 		value="' . $origen . '">'  . "\n";
echo '<input type="hidden" name="flag" 			value="' . $flag . '">'  . "\n";
echo "</form></body></html>;\n";

}
mssql_close($cn);
?>
