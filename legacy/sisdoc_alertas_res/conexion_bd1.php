<?
# conecta a la base de datos	
$cn = mssql_connect("BD3_MINSAL", "sa", "base3minsal") or die("El Servidor No se encuentra");
//$cn = mssql_connect("bd3-minsal", "sa", "base3minsal") or die("El Servidor No se encuentra");

	mssql_select_db("sisdoc");
?>