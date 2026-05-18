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
//echo $cusua;
//echo $cidusua;

//$rs_estado="exec mod_estado_tramite '" . $idseg . "','" . $fechasistema. "','" . $estado . "','" . $opcion . "','" .
//$op . "','" . $compromiso. "'";
// permite modificar estado del tramite a cerrado  (estado=5 ) y cambiar la observacion si la persona lo hace en pantalla 
$rs_estado="exec tramite_cerrado '" . $idseg . "','" . $fechasistema. "','" . $estado . "','" . $opcion . "','" .
$op . "','" . $compromiso. "','" . $observacion. "'";


$rs_est = mssql_query($rs_estado,$cn); 

 // busca los tramites del documento que no esten cerrados preguntado por el total de tramite con estado distinto
 // de cerrado
	  
/*$rs_tra="exec busca_tramite '" . $iddoc . "','" . $idseg. "'";
$rs_tr = mssql_query($rs_tra,$cn); 
  
$n= mssql_num_rows($rs_tr);
  if ($n==0) {*/
  
  	$rs_doc="exec cierra_documento '" . $iddoc . "'";
	$rs_dc = mssql_query($rs_doc,$cn); 
	//echo "Se ha cerrado el documento";
	//$nom="";
	// busca documentos relacionados al documento iddoc//
  	$rs_dd="exec busca_relacion '" . $iddoc . "'";
	$rs_dc = mssql_query($rs_dd,$cn);
	$num = mssql_num_rows($rs_dc); 
	 if ($num!=0)
	 {
	  while($reg_doc= mssql_fetch_array($rs_dc)) 
	  { 
	    $rs_cie="exec cierra_relacion_docto '" . $iddoc . "','" . $reg_doc["id_documento"]. "'";
	    $rs_c = mssql_query($rs_cie,$cn); 
	   } 
	 }
    
	
//	echo '<html><body onload="document.form_i.submit();">';
//	echo '<form name="form_i" method="post" action="multi_recib.php">' . "\n";
    	echo '<input type="hidden" name="cusuario" value="' . $cusua . '">'  . "\n";
                  echo '<input type="hidden" name="idusuario" value="' . $cidusua . '">'  . "\n";
	echo '<input type="hidden" name="grabado" value="1">'  . "\n";
	//echo '<input type="hidden" name="nomina" value="' . $nom . '">'  . "\n";
	
    echo "</form>\n";
  //  }

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body></body> 
</html>
