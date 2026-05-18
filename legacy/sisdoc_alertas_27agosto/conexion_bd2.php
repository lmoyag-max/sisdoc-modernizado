<?
# conecta a la base de datos	
$cn = mssql_connect("BD2-MINSAL", "corpora", "corp2003") or die("El Servidor No se encuentra");
mssql_select_db("corporativo");
?>
