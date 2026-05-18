<?php
include ("../conexion_bd.php");
$ok = 0;

if(isset($ndoc)){
	mssql_query("UPDATE [tramite] SET [id_estado_tramite]=2, [fecha_recepcion]=NULL, [usuario_recepcion]=NULL WHERE [id_documento]='".$ndoc."'");
}

$Ret = mssql_rows_affected($cn);

if ($Ret > 1)
	$ok = 1;
else
	$ok = 2;
	
echo '<html><body onload="document.form1.submit();">';
echo '<form name="form1" method="post" action="adm_busca_documento.php">';
echo '<input type="hidden" name="ok_graba" value="'.$ok.'">'."\n";
echo '<input type="hidden" name="texto" value="'.$Ret.'"';
echo "</form></body></html>";
mssql_close($cn);
?>