<?
# conecta a la base de datos	
//$cn = mssql_connect("10.8.222.131", "sa", "c1sC0") or die("El Servidor No se encuentra");
$cn = mssql_connect("SISDOC\SISDOC","sa","huap125") or die("El Servidor No se encuentra");
//$cn = mssql_connect("bd3-minsal", "sa", "base3minsal") or die("El Servidor No se encuentra");

	mssql_select_db("sisdoc");
?>