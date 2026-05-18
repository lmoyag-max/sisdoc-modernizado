<?php 
	mssql_close($cn); 	
	include("conexion_bd.php");
	$query="SELECT     *
				 FROM         funcionario f INNER JOIN
				 dependencia d ON f.id_dependencia = d.id_dependencia
				 WHERE     (f.rut = '" . $aux_rut . "') AND (f.vigencia IS NULL)";
	$rs= mssql_query($query, $cn);
	$filas = mssql_num_rows($rs);
	while ($rs2=mssql_fetch_array($rs, MYSQL_ASSOC)){
		$array_id_dep[]=$rs2["id_dependencia"];
		$array_nom_dep[]=$rs2["desc_dependencia"];
	}
?>
	<html>
	<body>
	<form name="form1" method="post" action="verifica.php">
	<input type="hidden" name="rut" value="$rut">
	<input type="hidden" name="aux_rut" value="$aux_rut">
	<input type="hidden" name="clave" value="$clave">
<?php
		for ($i=0;$i<$filas;$i++){
			echo '<a href="verifica.php?rut='.$rut.'&clave='.$clave.'&flag=1"&id_dependencia='.$array_id_dep[$i].'>'.$array_nom_dep[$i].'</a>';
		}
 ?>
	</form>
	</body>
	</html>
