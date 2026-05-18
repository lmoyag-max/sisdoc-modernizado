<?php
include("conexion_bd.php");

$consulta=" select a.fecha_documento,a.id_documento
                   from documento a
			    where             a.id_documento =3400 
			 		     and (a.fecha_documento between '01/01/2004' and '05/05/2007') ";

$rs_doc1=$consulta;
$rs_doc2=mssql_query($rs_doc1);

$reg_documento=mssql_fetch_array($rs_doc2);
$rs_fecha=mssql_query("select convert(varchar(10),fecha_documento,105)   from documento where id_documento= " . $reg_documento[id_documento], $cn);
$rs_reg=mssql_fetch_array($rs_fecha);

echo $rs_reg[0] . "<br>";

//echo "fecha" . "dfsdfsdfsdf";
?>