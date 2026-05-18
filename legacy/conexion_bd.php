<?
# conecta a la base de datos	
//$cn = mssql_connect("bd3-minsal", "sisdoc", "sisdoc1000") or die("El Servidor No se encuentra");
$cn = mssql_connect("SISDOC\SISDOC","sa","huap125") or die("El Servidor No se encuentra");
	mssql_select_db("sisdoc");
?>
