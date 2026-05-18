<?PHP
include("conexion_bd.php");
// Programa que  graba numero oficial desde oficina  de partes 
$fechasistema = date("Y/m/d H:i"); 
$fun=$idfuncionario;
$xx=$idusuario;
$c_usuario=$cusuario;
$cbotiporig=$cbotiporig;
$sw=1;
$dia   = substr($Txtfechaofic,0,2);
$mes = substr($Txtfechaofic,3,2);
$año  = substr($Txtfechaofic,6,4);
echo "fecha " . $dia . $mes . $año  . " tipo" . $Cbo_Tipo_Docto . "porc  " .$procmin;
if ($procmin <> 0)
{
/*$buscanumofic="select a.*,b.* from documento a, tramite b where a.id_tipo_documento= $Cbo_Tipo_Docto    and b.id_seguimiento in (select min(id_seguimiento) from tramite where id_documento=a.id_documento) and b.id_procedencia=$procmin";
echo "query" . $buscanumofic ;
$rs_numofic =mssql_query($buscanumofic);
$reg_ofic =mssql_fetch_array($rs_numofic);
$tot =mssql_num_rows($rs_numofic);
echo "tot" . $tot ; 
*/}
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
