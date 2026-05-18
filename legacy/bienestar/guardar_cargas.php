<?PHP
include("conexion_bd.php");
// Rescata la IP del equipo
$ip= $REMOTE_ADDR ;
$ok=0;
$fechasistema = date("Y/m/d H:i"); 
$Fecha_Docto= substr($Txt_fecha_doc,6,4) . "-" . substr($Txt_fecha_doc,3,2)  . "-" . substr($Txt_fecha_doc,0,2);
$op="I";
$rut_carga= substr($el_rut,0,-1);
$rut_carga=str_replace("-","",$rut_carga);
$rut_carga=str_replace(".","",$rut_carga);

$aux_dv=substr($el_rut,$largo-1,1);
$el_rut=str_replace(".","",$el_rut);
$el_rut=str_replace("-","",$el_rut);
$rut_x=$el_rut;
$rut=$rut;
$rut_fun=$rut_fun;
$rut_c=$rut_c;


#-------- Modifica los datos de las Cargas Familiares----------

$carga_query = "exec man_cargas_fam '" . $rut_fun . "','" .
$rut_carga . "','" . $aux_dv . "','" . $Fecha_Docto . "','" . $cbo_tipo . "','" .
$radiosexo . "','" . $txtpaterno . "','" . $txtmaterno . "','" .
$txtnombres . "','" .  $op . "'"; 

$rs_carga = mssql_query($carga_query,$cc); 
$reg_carga = mssql_fetch_array($rs_carga);
$tot_carga = mssql_num_rows($rs_carga);
$ok_carga=$reg_carga[0];

if ($ok_carga==0 )
{
$sw_grabar=0;
	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="ingreso_cargas.php">' . "\n";
	echo '<input type="hidden" name="ok" value="' . $sw_grabar . '">' . "\n";
	echo '<input type="hidden" name="rut" value="' . $rut_fun . '">';
	echo '<input type="hidden" name="rut_c" value="' . $rut_c . '">';
	echo '</form></body></html>'  . "\n";
}

mssql_close($cc);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">


</head>

<body></body> 
</html>
