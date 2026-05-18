<?
# conecta a la base de datos	
$cc = mssql_connect("bd2-minsal", "bienes", "bienes2004") or die("El Servidor No se encuentra");
	mssql_select_db("bienestar");
?>