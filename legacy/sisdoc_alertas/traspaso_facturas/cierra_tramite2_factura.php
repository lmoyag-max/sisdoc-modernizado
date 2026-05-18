<?PHP
include("conexion_bd.php");
$fecha = date("d/m/y"); 
$fechasistema = date("Y/m/d H:i"); 
$cusua=$cusuario;
$cidusua=$idusuario;
$idseg = $idseguim;
$iddoc=$iddocum;
$opcion="E";
$op= 0;
$estado= 5;
// nom arrastra la nomina recepcionada y txtagnor el año //
//echo "nomina " . $nom .  "agno" . $txtagnor;
//echo $cusua;
//echo $cidusua;
 //echo "observacion " . $observacion;
//$rs_estado="exec mod_estado_tramite '" . $idseg . "','" . $fechasistema. "','" . $estado . "','" . $opcion . "','" .
//$op . "','" . $compromiso . "','" . $observacion. "'";

// para que no escriba comillas en observacion del tramite  //
$obs= str_replace("\'","",$observacion);
$obs =str_replace(",",", ", $observacion);


// se cambia para que agregue la observacion que se da cuando se archiva 
$obs2=str_replace("\'","",$observacion2);
$obs2=str_replace(",",", ",$observacion2);

if ($observacion2<>'' || $observacion2 <>NULL)
{  $obs2=$obs . "  -- Archivado por ". $cusuario .":  " . $obs2  . " ---";}
else 
   {$obs2=$obs;}

// se juntan las 2 observaciones y se dejan en uno solo 

//echo "obs" . $observacion . "observacion archiva" . $observacion_archiva . "observacion2 " . $obs2 ;
//echo "idseg" . $idseg. "fecha sistrema " . $fechasistema . "estado " . $estado . "opcion " . $opcion . "op" . $op . "compromiso" . $compromiso . "obs" . $obs . "<br>"; 

$rs_estado="exec tramite_cerrado_factura '" . $idseg . "','" . $fechasistema. "','" . $estado . "','" . $opcion . "','" .$op . "','" . $compromiso . "','" . $obs2 . "'";
$rs_est = mssql_query($rs_estado,$cn); 

 // busca los tramites del documento que no esten cerrados preguntado por el total de tramite con estado distinto
 // de cerrado
	  

$rs_tra="exec busca_tramite_factura '" . $iddoc . "','" . $idseg. "'";
$rs_tr = mssql_query($rs_tra,$cn); 


//----- saca el total de dias desde la fecha de recepcion y el dia de hoy  ----//

$busca_ant="select fecha_recepcion from detalle_facturas where id_detalle=" . $idseg ;
$r_detalle=mssql_query($busca_ant,$cn);
$rg_detalle=mssql_fetch_array($r_detalle);
$fec_doc=strtotime($rg_detalle[fecha_recepcion]);
			   $fech_doc=date("d/m/Y",$fec_doc);
                $dia_a_buscar=substr($fech_doc,6,4).substr($fech_doc,3,2). substr($fech_doc,0,2);
                $hoy =date("d/m/Y");
				$fec_hoy = substr($hoy,6,4).substr($hoy,3,2). substr($hoy,0,2);
				$dias ="exec obtiene_dias '" . $dia_a_buscar ."','" .$fec_hoy ."'"; 
				$rs_dias = mssql_query($dias,$cn);
				$rd = mssql_fetch_array($rs_dias);
				$dias=$rd["total"];

// pasa valor de dias desde que se recepciono al cierre 				
$rs_dias ="exec mod_dias_historico_factura '" . $idseg . "','" . $dias . "'"; 
$rs_d=mssql_query($rs_dias,$cn);

  
$n= mssql_num_rows($rs_tr);

  if ($n==0) 
  {
   	$rs_doc="exec cierra_factura '" . $iddoc . "'";
	$rs_dc = mssql_query($rs_doc,$cn); 
  
	}


		
echo '<html><body onload="document.form_i.submit();">';
echo '<form name="form_i" method="post" action="multi_recib.php">' . "\n";
    echo '<input type="hidden" name="cusuario" value="' . $cusua . '">'  . "\n";
    echo '<input type="hidden" name="idusuario" value="' . $cidusua . '">'  . "\n";
	echo '<input type="hidden" name="grabado" value="1">'  . "\n";
	echo '<input type="hidden" name="txtnomina" value="' . $txtnomina . '">'  . "\n";
	echo '<input type="hidden" name="txtagnor" value="' . $txtagnor. '">' . "\n";
	
    echo "</form>\n";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body></body> 
</html>
