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
$Cbo_Destinatario=$val_destino;
$Cbo_Func_Destino=$val_funcionario1;
$ofpartes=$checkofpartes2;
$observaciones=$TxtObservacion;
$Cbo_Estado_Compromiso = 2 ;
$Original="N";
$op="I";
$sw = 1;
$txtnomina=$txtnomina;

if ($checkofpartes=="")	{
	$tipo_despacho ="D";	}
Else	{		
	$tipo_despacho ="";	}	

#-------- Guarda los datos en tabla Tramite ----------
// para que no escriba comillas en observacion del tramite  //
$observaciones= str_replace("\'","",$observaciones);
$observaciones=str_replace(",",", ", $observaciones);

$est_tramite= 1;
$id_nomina=0;
$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
$Cbo_Estado_Compromiso . "','" . $xx . "','" . $iddocum . "','" . $Cbo_Procedencia . "','" .
$Cbo_Destinatario . "','" . $est_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
$tipo_destino . "','" . $Original . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
$TxtDias . "','" . '' . "','" . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
ltrim($observaciones) . "','" . $op . "'";
$rs_tram = mssql_query($tramite_query,$cn); 
$reg_tram = mssql_fetch_array($rs_tram);

$est_tramite=5;

/* Rescato id del tramite */
$rs_f5= mssql_query("SELECT @@IDENTITY AS 'Identity'", $cn);
$reg_f5 = mssql_fetch_array($rs_f5);
$idtram = $reg_f5[Identity];


/*   Cambio de estados en tramites y  posible cierre del documento */

$opcion="E";
$op= 1;
$estado= 5;
$compromiso=2;


/*----modifica estado del tramite anterior ----*/
$rs_estado="exec mod_estado_tramite '" . $idse. "','" . $fechasistema. "','" . $estado . "','" . $opcion . "','" .
$op . "','" . $compromiso. "'";
$rs_est = mssql_query($rs_estado,$cn); 


/* busca los tramites del documento si se han cerrado */

$rs_tra="exec busca_tramite '" . $iddocum . "','" . $idse .  "'";
$rs_tr = mssql_query($rs_tra,$cn); 
  
$n= mssql_num_rows($rs_tr);
//echo "seg.... " . $rs_tr["id_seguimiento"] ;
if ($n==0)
{
	
  	$rs_doc="exec cierra_documento '" . $iddocum . "'";
	$rs_dc = mssql_query($rs_doc,$cn); 
	//echo "Se ha cerrado el documento";
   } 




    echo '<html><body onload="document.form_i.submit();">';
	
    echo '<form name="form_i" method="post" action="resp_sindoc.php" >' . "\n";
    echo '<input type="hidden" name="cusuario" value="' . $c_usuario . '">'  . "\n";   
    echo '<input type="hidden" name="idusuario" value="' . $xx . '">'  . "\n";
    echo '<input type="hidden" name="iddocum" value="' . $iddocum . '">'  . "\n";
	echo '<input type="hidden" name="grabado" value="1">'  . "\n";
	echo '<input type="hidden" name="idseguim" value="' . $idse . '">'  . "\n";
	echo '<input type="hidden" name="txtnomina" value="' . $txtnomina . '">'  . "\n";
	echo "</form>\n";
	

mssql_close($cn);
?>

</script>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body></body> 
</html>
