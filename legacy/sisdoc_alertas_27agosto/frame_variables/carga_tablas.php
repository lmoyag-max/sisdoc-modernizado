<?php
# carga de tablas maestras
$rs_usuario = mssql_query("select * from usuario" );
$rs_tipo_docto = mssql_query("select * from tipo_documento");
$rs_procedencia = mssql_query("select * from dependencia order by desc_dependencia");
$rs_destino = mssql_query("select * from dependencia order by desc_dependencia");
$rs_estado_docto = mssql_query("select * from estado_documento where id_estado_documento <= 2");
$rs_distribucion = mssql_query("select * from tipo_distribucion");
$rs_tipo_compromiso = mssql_query("select * from tipo_compromiso");
$rs_estado_compromiso = mssql_query("select * from estado_compromiso");
	?>
	
