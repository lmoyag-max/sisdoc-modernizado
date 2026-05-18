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

// $procmin es la procedencia del primer tramite del documento 
//$buscanumofic="select * from documento where id_tipo_documento= $Cbo_Tipo_Docto  and num_oficial =$TxtOficial ";

if ($procmin <> 0)
{
//$buscanumofic="select a.*,b.* from documento a, tramite b where a.id_tipo_documento= $Cbo_Tipo_Docto  and a.num_oficial =$TxtOficial  and b.id_seguimiento in (select min(id_seguimiento) from tramite where id_documento=a.id_documento) and b.id_procedencia=$procmin";

//para que no valide existencia de numero oficial 
//$buscanumofic="select a.*,b.* from documento a, tramite b where a.id_tipo_documento= $Cbo_Tipo_Docto    and b.id_seguimiento in (select min(id_seguimiento) from tramite where id_documento=a.id_documento) and b.id_procedencia=$procmin";
//Se crea el proc. almacenacenado 18/07/2007
$buscanumofic= "exec existe_num_oficial '" . $Cbo_Tipo_Docto . "','" . $procmin . "'"; 
$rs_numofic =mssql_query($buscanumofic);
$reg_ofic =mssql_fetch_array($rs_numofic);
$tot =mssql_num_rows($rs_numofic);

}
 else 
   { $tot =0;}

   // la pregunta se saca ya que ya no se valida que  no debe existir el numero oficial , puede estar en otro documento del mismo tipo 
//if ($tot==0)
//{	
#-------- modifica solamente el numero oficial de los documentos  ----------
//$documento_query = "exec modifica_num_oficial '" . $iddocumento . "','" . $TxtOficial . "','" . $fechasistema . "'"; 
$documento_query = "exec modifica_num_oficial '" . $iddocumento . "','" . $TxtOficial . "','" . $fechasistema . "','" . $Txtfechaofic . "'"; 

$rs_doc = mssql_query($documento_query,$cn); 
$reg_doc = mssql_fetch_array($rs_doc);
//}

   echo '<html><body onload="document.form1.submit();">';
  echo '<form name="form1" method="post" action="busca_documentos_ofpartes.php">';
	echo '<input type="hidden" name="idusuario"     value="' . $xx . '">';
	echo '<input type="hidden" name="cusuario"      value="' . $c_usuario . '">';
	echo '<input type="hidden" name="idfuncionario" value="' . $fun . '">';
	echo '<input type="hidden" name="flujook"       value="' . $sw . '">';
	echo '<input type="hidden" name="TxtInterno"    value="' . $TxtInterno . '">';
	echo '<input type="hidden" name="TxtOficial"    value="' . 0 . '" >';
	echo '<input type="hidden" name="TxtExterno"    value="' . $TxtExterno . '">';
	echo '<input type="hidden" name="Cbo_Tipo_Docto" value="' . $Cbo_Tipo_Docto . '">';
	echo '<input type="hidden" name="cbotiporig"     value="' . $cbotiporig . '">';
	echo '<input type="hidden" name="Txt_fecha_ini"  value="' . $Fechaini . '">';
	echo '<input type="hidden" name="Txt_fecha_fin"  value="' . $Fechafin . '">';
	echo '<input type="hidden" name="cbo_esc_dest"   value="' . $cbo_esc_dest . '">';
   	echo '<input type="hidden" name="destino"        value="' . $destino . '">';
   	echo '<input type="hidden" name="txtnom"        value="' . $txtnom . '">';
	echo '<input type="hidden" name="iddocum"        value="' . $iddocumento . '">';

	if (($TxtOficial!= 0) and ($tot==0))
	{
	echo '<input type="hidden" name="graba_ok" value="1">';
	}
	if ($tot<>0)
	{
//	echo '<input type="hidden" name="existeoficial" value="1">';
// se gragan estas 2 lineas para efectos de que graba igual si  el numero oficial  ya existe 
	echo '<input type="hidden" name="existe_oficial" value="3">';
	echo '<input type="hidden" name="graba_ok" value="1">';
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
