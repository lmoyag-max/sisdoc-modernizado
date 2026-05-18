<?php
include("conexion_bd2.php");
//$flujo=8;

$flujook=$cont;
$rut_fun=$rut_funcionario;
$y="";
$rut=$rut;
echo "cont " . $cont;
 echo "<br>";
 echo "rut_fun " . $rut_fun . "rut_c   " . $rut;
$x=0;
   $aux_rut=substr($rut,0,-1);
      
   if(strlen($aux_rut)>8) 
   {
    $aux_rut = substr($aux_rut,0,-1);
   }
   
   $aux_rut=str_replace("-","",$aux_rut);
   $query_rut_s = "select * from funcionario where rut_fun='" . $aux_rut . "'";
  // $query_rut = "select * from funcionarios where rut='" . $aux_rut . "' and clave='" . $clave . "'";
   $rs_rut_s= mssql_query($query_rut_s, $cn);
   $filas_rut_s = mssql_num_rows($rs_rut_s);
   $reg_rut_s= mssql_fetch_array($rs_rut_s);
  
   if($filas_rut_s==0)
	   {
		echo "<HTML>\n";
		echo "<Head>\n";
		echo '<meta http-equiv=refresh content="1;URL=index_encargado.php?cont=1">' . "\n";
		echo "</Head>\n";
		echo "</HTML>\n";
		
		}
		else
		{
	   $query_rut = "select * from funcionario where rut_fun='" . $aux_rut . "' and marcacion_fun='" . $clave . "'";
	  // $query_rut = "select * from funcionarios where rut='" . $aux_rut . "' and clave='" . $clave . "'";
	   $rs_rut= mssql_query($query_rut, $cn);
	   $filas = mssql_num_rows($rs_rut);
	   $reg_rut= mssql_fetch_array($rs_rut);
	   if($filas==0)
	   {
			echo "<HTML>\n";
			echo "<Head>\n";
			echo '<meta http-equiv=refresh content="2;URL=index.php?cont=2">' . "\n";
			echo "</Head>\n";
			echo "</HTML>\n";
	   }
    
 // busca por rut en tabla de usuario de sisdoc //
 
  		else
		  {
			echo '<html><body onload="document.form1.submit();">';
			echo '<form name="form1" method="post" action="respuesta.php">';
			echo '<input type="hidden" name="rut_fun" value="' . $rut_fun . '">';
			echo '<input type="hidden" name="rut_enc" value="' . $aux_rut . '">';
			echo '<input type="hidden" name="flujook" value="' . 5 . '">';
			echo "</form></body></html>";
			}
}

//}
	mssql_close($cn);?>