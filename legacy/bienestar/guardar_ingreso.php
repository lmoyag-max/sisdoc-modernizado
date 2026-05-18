<?PHP
include("conexion_bd2.php");
// Rescata la IP del equipo
$ok2=$ok;
$ip= $REMOTE_ADDR ;
$fechasistema = date("Y/m/d H:i"); 
$Fecha_Docto= substr($Txt_fecha_doc,6,4) . "-" . substr($Txt_fecha_doc,3,2)  . "-" . substr($Txt_fecha_doc,0,2);
$op="U";

#-------- Modifica los datos para el Funcionario ----------
$funcionario_query = "exec man_funcionario '" . $rut . "','" .
$txtpaterno . "','" . $txtmaterno . "','" . $txtnombres . "','" .
ltrim($txtdireccion) . "','" . $cbo_comuna. "','" . $cbo_region . "','" .
$Fecha_Docto . "','" . $cbo_cargo . "','" . $txtgrado  . "','" . $txtanexo . "','" . 
$Cbo_Procedencia . "','"  . $cbo_estamento . "','"  . $op . "'"; 

$rs_doc = mssql_query($funcionario_query,$cn); 
$reg_doc = mssql_fetch_array($rs_doc);
$tot_doc = mssql_num_rows($rs_doc);
$ok1=$reg_doc[0];
if($ok1==0 )
{
mssql_close($cn);
$cc= mssql_connect("bd2-minsal", "bienes", "bienes2004") or die("El Servidor No se encuentra");
	mssql_select_db("bienestar");
$estado="S";
$op="I";
// Guardar los datos del funcionario en tabla bienestar

$bienestar_query = "exec ingreso_bienestar '" . $rut . "','" .
$Fecha_Docto . "','" . $estado . "'," . "null" . ",'" .   $op . "'"; 
$rs_bien = mssql_query($bienestar_query,$cc); 
$reg_bien = mssql_fetch_array($rs_bien);
$tot_bien = mssql_num_rows($rs_bien);
$ok_bien=$reg_bien[0];
//echo "ok bien " . $ok_bien;

if ($ok_bien==0 or $ok_bien ==1)
{

// se devuelve a la pantalla de ingreso de funcionarios 
	
	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="ingreso_bienestar.php">' . "\n";
	echo '<input type="hidden" name="flujook" value="' . $ok_bien . '">' . "\n";
	echo '<input type="hidden" name="rut_fun" value="' . $rut . '">';
	echo '<input type="hidden" name="rut_c" value="' . $rut_c . '">';
	
	echo '<input type="hidden" name="nom_fun" value="' . $txtnombres . '">' . "\n";
	echo '<input type="hidden" name="mat_fun" value="' . $txtmaterno . '">';
	echo '<input type="hidden" name="pat_fun" value="' . $txtpaterno . '">';
	echo '<input type="hidden" name="dir_fun" value="' . $txtdireccion . '">' . "\n";
	echo '<input type="hidden" name="ane_fun" value="' . $txtanexo . '">';
	echo '<input type="hidden" name="gra_fun" value="' . $txtgrado . '">';
	echo '<input type="hidden" name="region" value="' . $cbo_region . '">';
	echo '<input type="hidden" name="comuna" value="' . $cbo_comuna . '">';
	echo '<input type="hidden" name="car_fun" value="' . $cbo_cargo . '">';
	echo '<input type="hidden" name="dep_fun" value="' . $Cbo_Procedencia . '">';
	echo '<input type="hidden" name="est_fun" value="' . $cbo_estamento . '">';
	echo '</form></body></html>'  . "\n";
	mssql_close($cc);
	
}
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">


</head>

<body></body> 
</html>
