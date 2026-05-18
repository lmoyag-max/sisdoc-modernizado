<?php
include("conexion_bd2.php");
$flujo=8;
//$flujook=$cont;
 $x=0;
$y="";
	$rut_c=$rut_c;
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
		echo '<meta http-equiv=refresh content="1;URL=index.php?cont=1">' . "\n";
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
		   $rut_c=$reg_rut[rut_fun] . "-" . $reg_rut[dv_fun];
		 	echo '<html><body onload="document.form1.submit();">';
			echo '<form name="form1" method="post" action="ingreso.php">';
			echo '<input type="hidden" name="rut_fun" value="' . $reg_rut[rut_fun] . '">';
			echo '<input type="hidden" name="rut" value="' . $rut_c . '">';
			echo '<input type="hidden" name="flujo_ok" value="' . $flujo . '">';
			echo '<input type="hidden" name="pat_fun" value="' . $reg_rut[ap_pat_fun] . '">';
			echo '<input type="hidden" name="mat_fun" value="' . $reg_rut[ap_mat_fun] . '">';
			echo '<input type="hidden" name="nom_fun" value="' . $reg_rut[nombres_fun] . '">';
			echo '<input type="hidden" name="dir_fun" value="' . $reg_rut[direccion_fun] . '">';
			echo '<input type="hidden" name="reg_f" value="' . $reg_rut[reg_codigo] . '">';
			echo '<input type="hidden" name="com_fun" value="' . $reg_rut[com_codigo] . '">';
			echo '<input type="hidden" name="gra_fun" value="' . $reg_rut[grado_fun] . '">';
			echo '<input type="hidden" name="ane_fun" value="' . $reg_rut[anexo_fun] . '">';
			echo '<input type="hidden" name="car_fun" value="' . $reg_rut[cargo_fun] . '">';
			echo '<input type="hidden" name="dep_fun" value="' . $reg_rut[cod_dependencia] . '">';
			echo '<input type="hidden" name="est_fun" value="' . $reg_rut[cod_estamento] . '">';
			echo "</form></body></html>";
			}
			
}

	mssql_close($cn);?>