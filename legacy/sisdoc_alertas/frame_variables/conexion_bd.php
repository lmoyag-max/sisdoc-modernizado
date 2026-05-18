<?
# conecta a la base de datos	
$cn = mssql_connect("bd2-minsal", "sisdoc", "sisdoc1000") or die("El Servidor No se encuentra");
	mssql_select_db("sisdoc");
?>