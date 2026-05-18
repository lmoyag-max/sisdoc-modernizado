 <?
/*	autoPosteándose, para que en la 1a. ejecución se inicialicen las vars.
	#$svr = "w2k-vosorio";     # nombre de tu MS SQL server
	#$uid = "victor";      # nombre de usuario 
	#$pwd = "victor";        # password
	
	$svr = "172.16.1.13";     # nombre de tu MS SQL server
	$uid = "sa";      # nombre de usuario 
	$pwd = "sqlminsal";        # password
	$db  = "correspondencia";    # nombre de database
	$tbl = "procedencia"; # nombre de la tabla
	$SQL = "select * from ".$tbl; # el comando SQL select que se arma
*/
# setea variables como global para que esta función [hazlo()] pueda verlas
	global $svr, $uid, $pwd, $db, $tbl, $SQL, $Cbo_Tipo_Docto,$cUsuario;
?>
	
