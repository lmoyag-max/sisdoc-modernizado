<?php
include("variables.php");
include("conexion_bd.php");
include("funciones.php");
$caja = $casilla_despacho;
$Tot = $Totreg;
$nl = $NumLayer;
$fechasistema = date("Y/m/d H:i");      


echo "<br>";
echo $Tot;
echo "<br>";
echo $nl;
echo "<br>";
$Nomina_query= "INSERT INTO nomina_despacho (fecha_sistema) VALUES " .
"('" . $fechasistema . "')";

mssql_query($Nomina_query,$cn);

#-------- rescata el ID de Documento----------
$rs_f5= mssql_query("SELECT @@IDENTITY AS 'Identity'", $cn);
$reg_f5 = mssql_fetch_array($rs_f5);
$Id_Nomina = $reg_f5[Identity];
$estado_tramite = 2;
echo $Id_Nomina;

for ($i = 0; ($i <= $Tot-1); $i++) 
   {
   if($caja[$i]!=null) {
   
   
   
   
   
   
   echo "valor" .$caja[$i];
   echo "i";
   }
   else
   {
   echo "nulo " . $i;
   }
   }
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
<table width="75%" border="1">
  <tr>
    <td width="29%">&nbsp;</td>
    <td width="21%">&nbsp;</td>
    <td width="25%">&nbsp;</td>
    <td width="25%">&nbsp;</td>
  </tr>
</table>
</body>
</html>
