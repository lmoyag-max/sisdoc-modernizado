<?PHP
include("conexion_bd.php");
// Rescata la IP del equipo
$ip= $REMOTE_ADDR ;
$ok=0;
$fechasistema = date("Y/m/d H:i"); 
$Fecha_Docto= substr($txtfecing,6,4) . "-" . substr($txtfecing,3,2)  . "-" . substr($txtfecing,0,2);
//$rut_f= substr($rut_c,0,-1);
//$rut_f=str_replace("-","",$rut);
//$rut_f=str_replace(".","",$rut);
$rut_f=$rut;
$rut_fun=$rut;
$rut_c=$rut_c;
$rut_enc = $encargado_corto;
//echo "eeee" . $rut_enc . " rut " . $rut;

//$rut_fun=$rut_f;
$rut_fun=$rut;
$rut_c=$rut_c;
$radio1=substr($radio1,0,1);
#-------- Modifica los datos de las Cargas Familiares----------
$op="U";
$bienestar_query = "exec ingreso_bienestar '" . $rut_f . "','" .
$Fecha_Docto . "','" . $radio1 . "','" . $rut_enc . "','" . $op . "'"; 
$rs_bien = mssql_query($bienestar_query,$cc); 
$reg_bien = mssql_fetch_array($rs_bien);
$tot_bien = mssql_num_rows($rs_bien);
$ok_bien=$reg_bien[0];

if ($ok_bien==0 or $ok_bien ==1)
{

$sw_grabar=0;
	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="respuesta.php">' . "\n";
	echo '<input type="hidden" name="flujook" value="' . 0 . '">' . "\n";
	echo '<input type="hidden" name="rut_fun" value="' . $rut_f . '">';
	echo '<input type="hidden" name="rut_enc" value="' . $rut_enc . '">';
    echo '<input type="hidden" name="txtfecing" value="' . $txtfecing . '">';
	echo '<input type="hidden" name="radio1" value="' . $estado . '">';
	echo '</form></body></html>'  . "\n";

}

mssql_close($cc);
?>
