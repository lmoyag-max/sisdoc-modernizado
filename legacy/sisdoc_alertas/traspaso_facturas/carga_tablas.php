<?php
# carga de tablas maestras
$rs_usuario = mssql_query("select * from usuario" );
$rs_tipo_docto = mssql_query("select * from tipo_documento order by desc_tipo_documento");
$rs1_tipo_docto = mssql_query("select * from tipo_documento order by desc_tipo_documento"); // para cargar combo de layer en ingreso de of partes 
$rs_estado_docto = mssql_query("select * from estado_documento where id_estado_documento <= 2");
$rs_distribucion = mssql_query("select * from tipo_distribucion order by desc_tipo_distribucion");
$rs_tipo_compromiso = mssql_query("select * from tipo_compromiso order by desc_tipo_compromiso");
$rs_estado_compromiso = mssql_query("select * from estado_compromiso ");
$rs_cod_descriptor1 = mssql_query("select * from descriptor order by desc_descriptor");
$rs_cod_descriptor2 = mssql_query("select * from descriptor order by desc_descriptor");
$rs_cod_descriptor3 = mssql_query("select * from descriptor order by desc_descriptor");
$rs_estado_tramite =mssql_query("select * from estado_tramite order by desc_estado_tramite");

$rs_procedencia =mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia
from dependencia order by desc_dependencia");

// mssql_query("select * from dependencia order by desc_dependencia");

//$rs_destino = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia
//from dependencia order by desc_dependencia");

// solo los vigentes de la tabla 
$rs_destino = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia_nuevo
from dependencia  where vigencia is NULL order by desc_dependencia");

//$rs_dependencia = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia
//from dependencia  order by desc_dependencia");

// solo los vigentes de la tabla 
$rs_dependencia = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia_nuevo
from dependencia   where vigencia is NULL order by desc_dependencia");

// todos los de la tabla dependencia 

$rs_dest = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia_nuevo
from dependencia   order by desc_dependencia");


$rs_dependencia_externa = mssql_query("select id_dependencia_externa, SUBSTRING(desc_dependencia_externa, 1, 35) AS desc_dependencia_externa, cod_dependencia_externa
from dependencia_externa order by desc_dependencia_externa");
//("select * from dependencia_externa order by desc_dependencia_externa");


//$rs_dependencia_ofpartes = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia from dependencia  where ofpartes ='S' order by desc_dependencia");

// para que considere  a oficina de partes le aparezcan los codigos nuevos 
$rs_dependencia_ofpartes = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 40) AS desc_dependencia, cod_dependencia_nuevo from dependencia  where ofpartes ='S' order by desc_dependencia");

// para considerar los temas de las alertas 
$rs_tema = mssql_query("select id_tema, SUBSTRING(desc_tema, 1, 70) AS desc_tema from temas_alertas order by desc_tema");


// para considerar en las alertas para los usuarios execpto oficina de partes 
$rs_dias_compromiso=mssql_query("select id_dias,dias_compromiso  from dias_compromiso_alertas order by dias_compromiso");


// para el modulo de facturas 
$rs_tema_factura=mssql_query("select * from temas_facturas order by desc_tema");
$rs_proveedor=mssql_query("select * from proveedores order by razon_social");
// para el modulo de facturas 
$rs_tipo_factura=mssql_query("select * from tipo_facturas order by desc_tipofactura");


?>
	
