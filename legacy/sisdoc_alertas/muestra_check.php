<?php
include("variables.php");
include("conexion_bd.php");

$caja = $casilla_despacho;
$Tot = $Totreg;
$nl = $NumLayer;
$fechasistema = date("Y/m/d H:i");      
$Usuario=$cusuario;
$xx = $idusuario;
$fun = $idfuncionario;
$op="I";

// ------------------- Genera el número de nómina para impresión ----------------------
$Nomina_query= "exec ingreso_nomina '" .  $fechasistema . "','" . $op . "'";

$rs_f5=mssql_query($Nomina_query,$cn);
$reg_f5 = mssql_fetch_array($rs_f5);
$Id_nomina = $reg_f5[0];

//  --------------- Cambia el estado de los tramites a despachados ------------------
$estado_tramite=2;
$op="D";
$val=0;
for ($i =0 ; ($i <= $Tot-1); $i++) 
   {
   	$cont=$i+1;
   	if($caja[$i]!=null) {
 		$Tramite_query = "exec mod_estado_tramite '" . $caja[$i] . "','" . $fechasistema . "','" .
		$estado_tramite . "','" . $op . "','" .  $val . "','" . $Id_nomina . "'";
		$rs_f1=mssql_query($Tramite_query,$cn);
		$reg_f1 = mssql_fetch_array($rs_f1);
		
   	}
   }

 if($cont !=0)
 {

// codigo html para pasar las variables del usuario cuando se verifica en la base de dato

	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="imp_nomina.php">';
//	echo '<form name="form1" method="post" action="imp_nominaprueba.php">';
	echo '<input type="hidden" name="cusuario" value="' . $cusuario . '">';
	echo '<input type="hidden" name="idusuario" value="' . $idusuario . '">';
	echo '<input type="hidden" name="idnomina" value="' . $Id_nomina . '">';
	echo '<input type="hidden" name="idfuncionario" value="' . $idfuncionario . '">';
	echo "</form></body></html>";
 }
 else
 { 
    //si no existe le mando otra vez a la portada 
  	echo '<html><body onload="document.form2.submit();">';
	echo '<form name="form2" method="post" action="multi_pages.php">';
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

</body>
</html>
