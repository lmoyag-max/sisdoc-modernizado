<?php 
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		include("conexion_bd.php");
		// extrae las dependencias del usuario 26/11/2007
		$rs_depen_acceso="exec busca_dependencia_acceso'" . $idusu . "'";
		$qr = mssql_query($rs_depen_acceso,$cn);
   		$filas = mssql_num_rows($qr);
		$temp=1;
		$id = "\t var id_dep_acceso=new Array(";
		$nombre = "\t var nom_dep_acceso=new Array(";
		
		while($regd_a = mssql_fetch_array($qr)){
			if ($filas!=$temp){
				$id = $id. '"'.$regd_a[id].'",';
				$nombre = $nombre. '"'.$regd_a[nombre].'",';
			}
			else{
				$id = $id. '"'.$regd_a[id].'"';
				$nombre = $nombre. '"'.$regd_a[nombre].'"';
			}
			$temp++;
	  	}
		$id = $id. ');';
		$nombre = $nombre. ');';
		
		echo $id."\n";
		echo $nombre."\n";
		//echo $rs_depen_acceso."\n";
		
?>