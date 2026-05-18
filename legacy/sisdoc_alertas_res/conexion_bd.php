<?
# conecta a la base de datos	
//$cn = mssql_connect("bd2-minsal", "sa", "sql2minsal") or die("El Servidor No se encuentra");
$cn = mssql_connect("bd3-minsal", "sa", "base3minsal") or die("El Servidor No se encuentra");

	mssql_select_db("sisdoc");
?>