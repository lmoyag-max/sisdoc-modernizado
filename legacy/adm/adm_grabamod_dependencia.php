<?php
include ("../conexion_bd.php");
$ok = 0;
$qdep = "SELECT id_dependencia FROM dependencia WHERE id_dependencia='" . $cbo_dependencia . "'";
$rdep = mssql_query($qdep, $cn);
$tdep = mssql_num_rows($rdep);
if ($tdep == 1) {
		if ($vigenciax == 'S'){
        	$vigenciax = 'NULL';
		}else{
			$vigenciax = "'N'";
		}
        $query = "UPDATE dependencia SET desc_dependencia='".$nombre."' , vigencia=".$vigenciax." WHERE id_dependencia='".$cbo_dependencia."'";
        $result = mssql_query($query);
        if ($Ret == 0){
			$ok = 1;
		}else{
			$ok = 2;
		}
} else {
	$ok = 3;
}
echo '<html><body onload="document.form1.submit();">';
echo '<form name="form1" method="post" action="adm_modificar_dependencias.php">';
echo '<input type="hidden" name="ok_graba" value="'.$ok.'">'."\n";
echo "</form></body></html>";
mssql_close($cn);
?>