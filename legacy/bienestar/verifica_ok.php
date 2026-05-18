<?php
include("conexion_bd2.php");
$flujo=8;
$x=0;
$y="";
	$rut_c=$rut_c;
   $aux_rut=substr($rut,0,-1);
      
   if(strlen($aux_rut)>8) 
   {
    $aux_rut = substr($aux_rut,0,-1);
   }
   
   $aux_rut=str_replace("-","",$aux_rut);
   echo "rut " . $aux_rut;
   $query_rut = "select * from funcionario where rut_fun='" . $aux_rut . "' and marcacion_fun='" . $clave . "'";
  // $query_rut = "select * from funcionarios where rut='" . $aux_rut . "' and clave='" . $clave . "'";
   $rs_rut= mssql_query($query_rut, $cn);
   $filas = mssql_num_rows($rs_rut);
   $reg_rut= mssql_fetch_array($rs_rut);
   if($filas==0)
   //if($filas!=8)
   {
        echo "<HTML>\n";
        echo "<Head>\n";
        echo '<meta http-equiv=refresh content="2;URL=index.php?cont=1">' . "\n";
	    echo "</Head>\n";
    /*	echo "<body>\n";
	    echo "<center>\n";
    	echo "<SMALL>Sistema de Bienestar <br><br></SMALL>\n";
        echo '<p><b><font color="#000099">Usted no tiene Acceso</font></b></p>' . "\n";
	    echo "</center>\n";
	    echo $rut  . "<br>";
	    echo $clave  . "<br>";
        echo "</body>\n";
      */  echo "</HTML>\n";
   }
   /*echo "<HTML>\n";
	echo '<body  onLoad="document.paso.submit();">' . "\n";
	echo '<form name="paso" method="post" action="reforma.php">' . "\n";
	echo '<form name="paso" method="post" action="reforma.php">' . "\n";
	echo '<input type="hidden" name="rut" value="' . $rut . '">' . "\n";
	echo '<input type="hidden" name="aux_rut" value="' . $aux_rut . '">' . "\n";
	echo '<input type="hidden" name="clave" value="' . $clave . '">' . "\n";
	echo "</form>\n";
	echo "</body>\n";
	echo "</HTML>\n";*/
 
 // busca por rut en tabla de usuario de sisdoc //
 
  else
  {
  /* mssql_close($cn); 	
  include("conexion_bd.php");
  //$rs_usuario="exec busca_usuario '" . $usuario . "','" . $contrasena . "'";
  $rs_usuario="exec busca_funcionario_usuario'" .  $aux_rut . "'";
  $qq = mssql_query($rs_usuario,$cn);  
  $reg = mssql_fetch_array($qq);
  */
  
  $rut_c=$reg_rut[rut_fun] . "-" . $reg_rut[dv_fun];
  echo "rut_completo " . $reg_rut[reg_codigo];
  /*echo "total " . $Tot_usu . "<br>";
  echo "usuario " . $x_usuario . "<br>";
  echo "id_usuario " . $id_usuario . "<br>";*/
  
  
  	echo '<html><body onload="document.form1.submit();">';
	//echo '<form name="form1" method="post" action="principal.php">';
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



	/*echo '<input type="hidden" name="idfuncionario" value="' . $id_funcionario . '">';
	
	echo '<input type="hidden" name="val_funcionario" value="' . $x . '">';
	echo '<input type="hidden" name="val_procedencia" value="' . $x . '">';
	echo '<input type="hidden" name="val_funcionario1" value="' . $x . '">';
	echo '<input type="hidden" name="val_destino" value="' . $x . '">';
	echo '<input type="hidden" name="tipo_procedencia" value="' . $y . '">';
	echo '<input type="hidden" name="tipo_destino" value="' . $y . '">';
	echo '<input type="hidden" name="num_int" value="' . $x . '">';
	*/
	echo "</form></body></html>";
}
	mssql_close($cn);?>