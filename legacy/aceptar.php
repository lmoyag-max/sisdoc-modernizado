<?PHP

include("conexion_bd.php");
$fecha = date("d/m/y"); 
$fechasistema = date("Y/m/d H:i:s ");      
$Medio ="P";
$Nomina= 1;
if ($Confidencial=="")
	$Confidencial = "N";
if ($Original=="")
	$Original = "N";
$TxtUsuario=4;


$dia = substr($Txt_fecha_doc,0,2);
$mes = substr($Txt_fecha_doc,3,2);
$año = substr($Txt_fecha_doc,6,4);
$Fecha_Docto = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

#-------- Reemplaza  los datos  de estado y fecha update en tabla de Documento ----------

mssql_query("UPDATE documento set id_estado_documento ='$Cbo_Estado_Docto', id_usuario='$TxtUsuario',fecha_update='$fechasistema' where id_documento='$iddoc'",$cn);
echo "El tramite ha sido cerrado " ;
mssql_close($cn);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Documento sin t&iacute;tulo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>

</body>
</html>
