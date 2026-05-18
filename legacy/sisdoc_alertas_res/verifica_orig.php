<?php
include("conex_usuario.php");
   $aux_rut=substr($rut,0,-1);
   //echo $aux_rut  . "<br>";
   
   if(strlen($aux_rut)>8) {
   	$aux_rut = substr($aux_rut,0,-1);
   }
   $aux_rut=str_replace("-","",$aux_rut);
   
   $query_rut = "select * from usuarios where c_rut='" . $aux_rut . "' and c_clave='" . $clave . "'";
   $rs_rut= mssql_query($query_rut, $cn);
   $filas = mssql_num_rows($rs_rut);
   $reg_rut= mssql_fetch_array($rs_rut);
   if($filas==0)
   //if($filas!=8)
   {
        echo "<HTML>\n";
        echo "<Head>\n";
        echo '<meta http-equiv=refresh content="2;URL=index.php">' . "\n";
	    echo "</Head>\n";
    	echo "<body>\n";
	    echo "<center>\n";
    	echo "<SMALL>Comisiones Reforma<br><br></SMALL>\n";
        echo '<p><b><font color="#000099">Usted no tiene Acceso</font></b></p>' . "\n";
	    echo "</center>\n";
	      echo "<br>" . $aux_rut  . "<br>";
	      echo $rut  . "<br>";
	      echo $clave  . "<br>";
        echo "</body>\n";
        echo "</HTML>\n";
   }
   echo "<HTML>\n";
	echo '<body  onLoad="document.paso.submit();">' . "\n";
	//echo '<form name="paso" method="post" action="reforma.php">' . "\n";
	echo '<form name="paso" method="post" action="reforma.php">' . "\n";
	echo '<input type="hidden" name="rut" value="' . $rut . '">' . "\n";
	echo '<input type="hidden" name="aux_rut" value="' . $aux_rut . '">' . "\n";
	echo '<input type="hidden" name="clave" value="' . $clave . '">' . "\n";
	echo "</form>\n";
	echo "</body>\n";
	echo "</HTML>\n";
   }
	
mssql_close($cn);?>