<?php
include ("../conexion_bd.php");
echo print_r($_POST, true);
$ok = 0;
$qdep = "SELECT id_dependencia FROM dependencia WHERE desc_dependencia='" . $nombre . "'";
$rdep = mssql_query($qdep, $cn);
$tdep = mssql_num_rows($rdep);
if ($tdep == 0) {
		if ($vigenciax == 'S'){
        	$vigenciax = 'NULL';
		}else{
			$vigenciax = "'N'";
		}
        $query = "INSERT INTO [sisdoc].[dbo].[dependencia] VALUES ('".$nombre."' ,NULL ,NULL ,'S', ".$vigenciax.", NULL)";
        $result = mssql_query($query);
        if ($Ret == 0){
			$ok = 1;
			$query2 = "SELECT id_dependencia FROM dependencia WHERE desc_dependencia='" . $nombre . "'";
       		$result2 = mssql_fetch_array(mssql_query($query2));
			$result3 = mssql_query("UPDATE dependencia SET cod_numinterno=".$result2[0]." WHERE desc_dependencia='" . $nombre . "'");
			if ($Ret != 0)
				$ok = 2;
		}
} else {
	$ok = 3;
}
echo '<html><body onload="document.form1.submit();">';
echo '<form name="form1" method="post" action="adm_ingreso_dependencias.php">';
echo '<input type="hidden" name="ok_graba" value="' . $ok . '">' . "\n";
echo "</form></body></html>";
mssql_close($cn);
?>