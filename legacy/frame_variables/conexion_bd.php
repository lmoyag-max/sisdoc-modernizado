<?
# conecta a la base de datos	
$cn = mssql_connect("SISDOC\SISDOC", "sa", "huap125") or die("El Servidor No se encuentra");
	mssql_select_db("sisdoc");
?>