<?PHP
 
include("conexion_bd.php");
$fecha = date("d/m/y"); 
$fechasistema = date("Y/m/d H:i:s ");      
$Medio ="P";

$TxtUsuario=4;


$dia = substr($Txt_fecha_doc,0,2);
$mes = substr($Txt_fecha_doc,3,2);
$año = substr($Txt_fecha_doc,6,4);
$Fecha_Docto = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

#-------- Guarda los datos en tabla Tramite ----------

$Tramite_query= "INSERT INTO tramite (id_tipo_distribucion,id_tipo_compromiso,id_estado_compromiso," .
" id_usuario,id_documento,id_procedencia,id_destino,fecha_recepcion,fecha_despacho, observaciones," .
" fecha_sistema,fecha_update, original) VALUES " .
"('" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" . $Cbo_Estado_Compromiso . "','" . 
$TxtUsuario . "','" . $id_doc. "','" . $destino . "','" . $Cbo_Destinatario . "','" . 
$fechasistema . "','" . $fechasistema . "','" . $Observaciones . "','" . $fechasistema . "','" .
$fechasistema . "','" . $Original . "')";

//mssql_query($Tramite_query,$cn);
//echo $Tramite_query ."<br>";
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
