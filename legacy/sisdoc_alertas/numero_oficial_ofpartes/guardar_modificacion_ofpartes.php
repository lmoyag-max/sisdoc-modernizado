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
$Txtfechaofic = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));
	
/*echo "Cbo_Tipo_Docto" . $Cbo_Tipo_Docto . "<br>" ;
echo "TxtInterno" . $TxtInterno . "<br>" ;
echo "TxtOficial" . $TxtOficial. "<br>";
echo "TxtExterno"  . $TxtExterno . "<br>";
echo "Cbo_Tipo_Docto" . $Cbo_Tipo_Docto;
echo "Txt_fecha_ini"  . $Txt_fecha_ini;
echo "Txt_fecha_fin"  . $Txt_fecha_fin;
*/	
//	echo  "procedencia " . $cbo_esc_dest;

$buscanumofic="select * from documento where id_tipo_documento= $Cbo_Tipo_Docto and num_oficial =$TxtOficial";
$rs_numofic =mssql_query($buscanumofic);
$reg_ofic =mssql_fetch_array($rs_numofic);
$tot =mssql_num_rows($rs_numofic);
if ($tot==0)
{	
#-------- modifica solamente el numero oficial de los documentos  ----------
//$documento_query = "exec modifica_num_oficial '" . $iddocumento . "','" . $TxtOficial . "','" . $fechasistema . "'"; 
$documento_query = "exec modifica_num_oficial '" . $iddocumento . "','" . $TxtOficial . "','" . $fechasistema . "','" . $Txtfechaofic . "'"; 
$rs_doc = mssql_query($documento_query,$cn); 
$reg_doc = mssql_fetch_array($rs_doc);
}
                echo '<html><body onload="document.form1.submit();">';
       	echo '<form name="form1" method="post" action="busca_documentos_ofpartes.php">';
//echo "original" . $cbotiporig . "despues" . $Cbo_Tipo_Docto. "otro"  . $cbo_tipo;

	echo '<input type="hidden" name="idusuario" value="' . $xx . '">';
	echo '<input type="hidden" name="cusuario" value="' . $c_usuario . '">';
	echo '<input type="hidden" name="idfuncionario" value="' . $fun . '">';
	echo '<input type="hidden" name="flujook" value="' . $sw . '">';
	echo '<input type="hidden" name="TxtInterno" value="' . $TxtInterno . '">';
	echo '<input type="hidden" name="TxtOficial" value="' . 0 . '" >';
	echo '<input type="hidden" name="TxtExterno" value="' . $TxtExterno . '">';
	//echo <!-- '<input type="hidden" name="Cbo_Tipo_Docto" value="' . $Cbo_Tipo_Docto . '">';-->
	echo '<input type="hidden" name="Cbo_Tipo_Docto" value="' . $cbotiporig . '">';
	echo '<input type="hidden" name="Cbo_Tipo_Docto" value="' . $cbo_tipo . '">';
	echo '<input type="hidden" name="Txt_fecha_ini" value="' . $Fechaini . '">';
	echo '<input type="hidden" name="Txt_fecha_fin" value="' . $Fechafin . '">';
	echo '<input type="hidden" name="cbo_esc_dest" value="' . $cbo_esc_dest . '">';
   	echo '<input type="hidden" name="destino" value="' . $destino . '">';
	echo '<input type="hidden" name="iddocum" value="' . $iddocumento . '">';
	if (($TxtOficial!= 0) and ($tot==0))
	{
	echo '<input type="hidden" name="grabaok" value="1">';
	}
	if ($tot<>0)
	{
	echo '<input type="hidden" name="existeoficial" value="1">';
	}
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
