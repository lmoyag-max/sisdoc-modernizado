<?php
include("conexion_bd.php");

$idfuncionario = $idfuncionario;
$iddocum= $iddocum;


$rs_doc="exec derivar_tramite2_ofpartes'" . $idseguim  . "','" .  $idusuario. "'" ;

$rs_tramite=mssql_query($rs_doc,$cn);   
$reg_tramite= mssql_fetch_array($rs_tramite);
$Tot= mssql_num_rows($rs_tramite);
if($Tot==0)
 {
 	echo "<script>\n";

	echo " alert('El estado del tramite no permite derivar o no tiene acceso a  este destino');\n";
 	echo "</script>\n";
	echo '<html><body onload="document.form1.submit();">';
    echo '<form name="form1" method="post" action="tramites_deriva_ofpartes.php">' . "\n";
	echo '<input type="hidden" name="idusuario" value="' . $idusuario.  '">' . "\n";
	echo '<input type="hidden" name="cusuario" value="' . $cusuario . '">' . "\n";
	echo '<input type="hidden" name="idfuncionario" value="' . $idfuncionario . '">' . "\n";
	echo '<input type="hidden" name="iddocum"  value="' . $iddocum . '">' . "\n";
	echo '<input type="hidden" name="txtnomina" value="' . $txtnomina . '">' . "\n";
	echo '<input type="hidden" name="txtagno" value="' . $txtagno . '">' . "\n";
	echo '</form></body></html>'  . "\n";
	
}
else 
{  //echo "usuario " . $idusuario . "iddocum" . $iddocum . "seg" . $idseguim ;
    echo '<html><body onload="document.form1.submit();">';
    echo '<form name="form1" method="post" action="cambia_estado3.php">' . "\n";
	echo '<input type="hidden" name="idusuario" value="' . $idusuario.  '">' . "\n";
	echo '<input type="hidden" name="cusuario" value="' . $cusuario . '">' . "\n";
	echo '<input type="hidden" name="idfuncionario" value="' . $idfuncionario . '">' . "\n";
	echo '<input type="hidden" name="idseguim" value="' . $reg_tramite[id_seguimiento] . '">' . "\n";
	echo '<input type="hidden" name="iddocum"  value="' . $reg_tramite[id_documento] . '">' . "\n";
	echo '<input type="hidden" name="txtnomina" value="' . $txtnomina . '">' . "\n";
	echo '<input type="hidden" name="txtagno" value="' . $txtagno . '">' . "\n";
	echo '</form></body></html>'  . "\n";


 }
  
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<title>Derivar_tramites</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>

</body>
</html>
