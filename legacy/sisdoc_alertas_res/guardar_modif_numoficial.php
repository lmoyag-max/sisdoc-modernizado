<?PHP
include("conexion_bd.php");
// Programa que modifica el numero oficial y fecha oficial ingresado por modulo de moficicacion desde oficina de partes


$fechasistema = date("Y/m/d H:i"); 
$fun=$idfuncionario;
$xx=$idusuario;
$c_usuario=$cusuario;
$sw=1;
if ($Txt_fecha_ofi <> null   )
{
$dia   = substr($Txt_fecha_ofi,0,2);
$mes = substr($Txt_fecha_ofi,3,2);
$año  = substr($Txt_fecha_ofi,6,4);

$Txtfechaofic = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));
}
else 
 $Txtfechaofic ="" ;
 
#-------- modifica numero oficial  y /o fecha oficial desde modulo de modificacion  ----------
$documento_query = "exec modifica_num_oficial '" . $iddocumento . "','" . $TxtOficial . "','" . $fechasistema . "','" . $Txtfechaofic . "'"; 
 $rs_doc = mssql_query($documento_query,$cn); 
$reg_doc = mssql_fetch_array($rs_doc);
$tot_doc = mssql_num_rows($rs_doc);

echo '<html><body onload="document.form1.submit();">';
echo '<form name="form1" method="post" action="modifica_cabecera2_modpartes.php">';
echo '<input type="hidden" name="idusuario" value="' . $xx . '">';
echo '<input type="hidden" name="cusuario" value="' . $c_usuario . '">';
echo '<input type="hidden" name="idfuncionario" value="' . $fun . '">';
echo '<input type="hidden" name="flujook" value="' . $sw . '">';
echo '<input type="hidden" name="Cbo_Tipo_Docto" value="' . $cbo_tipo . '">';
echo '<input type="hidden" name="TxtInterno" value="' . $numint . '">';
echo '<input type="hidden" name="iddocum" value="' . $iddocumento . '">';
echo "</form></body></html>";

mssql_close($cn);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body></body> 
</html>
