<?PHP
include("conexion_bd.php");
$fecha = date("d/m/y"); 
$fechasistema = date("Y/m/d H:i"); 
$cod_docu=668;

$dia = substr($fecha_sistema,0,2);
$mes = substr($fecha_sistema,3,2);
$año = substr($fecha_sistema,6,4);
$Fecha_Docto = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

#-------- busca si existe el cod_docu como padre en tabla relación_documento ----------
#----- llama a la función seek_historia para que te devuelva un recordset con todos 
#------- los documentos y trámite asociados
//$ps_busca_padre = "exec dbo.ps_busca_padre 664";
//$ps_busca_padre = "select * from seek_historia(662)";
$ps_busca_padre = "select * from historia(662)";

//. $cod_docu;

$rs_busca_padre = mssql_query($ps_busca_padre,$cn); 

//$reg_busca_padre = mssql_fetch_array($rs_busca_padre);
$tot_padre = mssql_num_rows($rs_busca_padre);


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<table border="1">
<?php 
while($reg_busca_padre=mssql_fetch_array($rs_busca_padre)) { ?>
<tr>
<td>
<?php echo $reg_busca_padre["id_documento"];?>
</td>
<td>
<?php echo $reg_busca_padre["desc_tipo_documento"];?>
</td>
<td>
<?php echo $reg_busca_padre["id_seguimiento"];?>
</td>
</tr>
<?php } ?>
</table>
<?php mssql_close($cn); ?>
</body> 
</html>
