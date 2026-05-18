<?
# conecta a la base de datos	
//$cn = mssql_connect("BD3-MINSAL", "corpora", "corp2003") or die("El Servidor No se encuentra");
$cn=mssql_connect("SISDOC\SISDOC","sa","huap125") or die("El servidor No se encuentra");
mssql_select_db("corporativo");
?>
