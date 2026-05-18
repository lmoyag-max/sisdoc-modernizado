<?php
include("variables.php");
include("conexion_bd.php");
include("funciones.php");
$caja = $casilla_despacho;
$Tot = $Totreg;
$nl = $NumLayer;
$fechasistema = date("Y/m/d H:i");
$Usuario=$cusuario;
$xx = $idusuario;

#-------- rescata el ID de Documento----------
$rs_f5= mssql_query("SELECT @@IDENTITY AS 'Identity'", $cn);
$reg_f5 = mssql_fetch_array($rs_f5);
$Id_nomina = $reg_f5[Identity];
$estado_tramite=3;

for ($i =0 ; ($i <= $Tot-1); $i++) 
   {
   $cont=$i+1;
   if($caja[$i]!=null) 
   	{
		$opcion="R";
		$op= 0;
		$compromiso="";
		$idseg = $caja[$i];
		//$tramite_query="exec mod_estado_tramite '" . $idseg . "','" . $fechasistema . "','" . $estado_tramite . "','" . $opcion . "','" .$op . "','" . $compromiso. "'";
	    // se cambia para que tome el usuario que rececpciona la nomina y lo deja en  campo nuevo "usuario_recepcion"
		$tramite_query="exec mod_estado_tramite_recep '" . $idseg . "','" . $fechasistema . "','" . $estado_tramite . "','" . $opcion . "','" .$op . "','" . $compromiso.  "','" .$xx. "'";
		$rs_est= mssql_query($tramite_query,$cn);
		
		// para relacionar documento con factura 
	   	
		// buscando el id_documento para  hacer enlace con factura 
		$busca_doc="select id_documento,id_procedencia,id_destino  from tramite where id_seguimiento = ". $idseg ;
		$r_busca_doc=mssql_query($busca_doc,$cn);
		$reg_busca_doc=mssql_fetch_array($r_busca_doc);
		$documento = $reg_busca_doc[0]; // resacata id_documento 
		$origen =$reg_busca_doc[1];
		$dest =$reg_busca_doc[2];
		 //echo "doc". $documento ;
		 // buscando con que factura está asociada 
		$busca_relacion="select id_factura from relacion_documento_factura where id_documento= ". $documento ;
		$r_busca_relacion=mssql_query($busca_relacion,$cn);
		$reg_busca_relacion =mssql_fetch_array($r_busca_relacion);
		$factura = $reg_busca_relacion[0];
		 //echo "factura" . $factura ;
		// cambiando estado en la fatura el estado del tramite 
		$estado_tramite = 3;  //cambia el esatdo a 3
		$op=2 ; // viene con ese estado 
		$doc_query="exec mod_estado_det_factura '".$factura ."','". $documento ."','". $fechasistema ."','" .$origen ."','".	$xx . "','".$estado_tramite .  "','".$dest. "','". $op . "'";
		//echo $doc_query;
		$r_busca_doc=mssql_query($doc_query,$cn);
		
		
		
	}
   }

if($cont !=0)
 {
 //echo "entra >0";
// codigo html para pasar las variables del usuario cuando se verifica en la base de dato
   	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="multi_recib.php">';
	// para que tome el año que venia de antes 
//  	echo '<form name="form1" method="post" action="cambio_recib.php">';

	echo '<input type="hidden" name="cusuario" value="' . $cusuario . '">';
	echo '<input type="hidden" name="idusuario" value="' . $idusuario . '">';
	echo '<input type="hidden" name="idfuncionario" value="' . $idfuncionario . '">';
	echo '<input type="hidden" name="txtagnor"  value="' . $txtagno . '">';
 	// para que tome la nomina seleccionada desde pantalla de documentos por recepcionar 
	echo '<input type="hidden" name="txtnomina" value="' . $nominarecepcionada . '">';
	echo "</form></body></html>";

}
else { 
echo '<html><body onload="document.form2.submit();">';
	echo '<form name="form2" method="post" action="multi_recep.php">';
	// para que tome el año que venia de antes 
	//echo '<form name="form2" method="post" action="cambio_recep.php">';
	echo '<input type="hidden" name="txtagno" value="' . $txtagno . '">';
	echo "</form></body></html>";
	
}
   
mssql_close($cn);

   
?>
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
// -->
</script>
</head>

<body bgcolor="#FFFFFF" text="#000000">
<!--table width="75%" border="1">
  <tr>
    <td width="29%">&nbsp;</td>
    <td width="21%">&nbsp;</td>
    <td width="25%">&nbsp;</td>
    <td width="25%">&nbsp;</td>
  </tr>
</table-->
</body>
</html>
