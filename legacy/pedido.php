<?php
	include("conexion_bd.php");
	$preg="SELECT DISTINCT t.id_documento AS n_doc, *
				FROM         tramite t LEFT OUTER JOIN
									  documento d ON t.id_documento = d.id_documento
				WHERE     (t.tipo_procedencia = 'E') AND (t.id_destino = '162') AND (t.fecha_sistema BETWEEN '01-01-2008' AND '31-12-2008') AND (d.id_tipo_documento = '1' OR
									  d.id_tipo_documento = '5' OR
									  d.id_tipo_documento = '10') AND (t.id_procedencia = '4' OR
									  t.id_procedencia = '5' OR
									  t.id_procedencia = '6')
				ORDER BY t.fecha_sistema";
	$preg1="SELECT * FROM estado_tramite";				
				
	$rs0 =mssql_query($preg);
	$rs1=mssql_num_rows($rs0);
	echo $preg."<br>".$rs1."<br>";
	while ($rs2=mssql_fetch_array($rs0, MYSQL_ASSOC)){	
		echo $rs2["n_doc"]."<br>";
	}
	
echo phpinfo();	
?>
 