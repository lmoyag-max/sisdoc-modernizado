<?php
include("conex_usuario.php");
$flujo=8;
$x=0;
$y="";

   $aux_rut=substr($rut,0,-1);
      
   if(strlen($aux_rut)>8) 
   {
    $aux_rut = substr($aux_rut,0,-1);
   }
   
   $aux_rut=str_replace("-","",$aux_rut);
  // echo "rut" . $aux_rut ;
   $query_rut = "select * from funcionario where rut_fun='" . $aux_rut . "' and marcacion_fun='" . $clave . "'";
  // $query_rut = "select * from funcionarios where rut='" . $aux_rut . "' and clave='" . $clave . "'";
   $rs_rut= mssql_query($query_rut, $cn);
   $filas = mssql_num_rows($rs_rut);
   $reg_rut= mssql_fetch_array($rs_rut);
   if($filas==0)
  
   {
        echo "<HTML>\n";
        echo "<Head>\n";
        echo '<meta http-equiv=refresh content="2;URL=index.php">' . "\n";
	    echo "</Head>\n";
    	echo "<body>\n";
	    echo "<center>\n";
    	echo "<SMALL>Sistema Control de Documentos <br><br></SMALL>\n";
        echo '<p><b><font color="#000099">Usted no tiene Acceso </font></b></p>' . "\n";
        echo '<p><b><font color="#000099">Comuniquese con el Administrador del Sistema</font></b></p>' . "\n";
	    echo "</center>\n";
	 
        echo "</body>\n";
        echo "</HTML>\n";
   }
   // busca por rut en tabla de usuario de sisdoc //
 
  else
  {
   mssql_close($cn); 	
  include("conexion_bd.php");
  //  $rs_usuario="exec busca_usuario '" . $usuario . "','" . $contrasena . "'";
  $rs_usuario="exec busca_funcionario_usuario'" .  $aux_rut . "'";
  $qq = mssql_query($rs_usuario,$cn);  
  $reg = mssql_fetch_array($qq);
  $x_usuario=$reg[usuario];
  $id_usuario  =$reg[id_usuario];
  $tipo_menu  =$reg[tipo_menu];
  $id_funcionario=$reg[id_funcionario];

// echo "usuario" . $x_usuario . "id" . $id_usuario . "func" . $id_funcionario ; 
  $Tot_usu = mssql_num_rows($qq);
  $rs_depend="exec busca_dependec_ofpartes'" . $aux_rut . "'";
  $qr = mssql_query($rs_depend,$cn);
  $reg_dep =mssql_fetch_array($qr);
  $tot_dep =mssql_num_rows($qr);
  if ($tot_dep == 0)
  {
  	
  
	echo '<html><body onload="document.form1.submit();">';
  	echo '<form name="form1" method="post" action="principal.php">';
  	echo '<input type="hidden" name="cusuario" value="' . $x_usuario . '">';
  	echo '<input type="hidden" name="idusuario" value="' . $id_usuario . '">';
  	echo '<input type="hidden" name="idfuncionario" value="' . $id_funcionario . '">';
  	echo '<input type="hidden" name="flujo_ok" value="' . $flujo . '">';
  	echo '<input type="hidden" name="val_funcionario" value="' . $x . '">';
  	echo '<input type="hidden" name="val_procedencia" value="' . $x . '">';
  	echo '<input type="hidden" name="val_funcionario1" value="' . $x . '">';
  	echo '<input type="hidden" name="val_destino" value="' . $x . '">';
  	echo '<input type="hidden" name="tipo_procedencia" value="' . $y . '">';
  	echo '<input type="hidden" name="tipo_destino" value="' . $y . '">';
  	echo '<input type="hidden" name="num_int" value="' . $x . '">';
  	echo "</form></body></html>";
}
}
	mssql_close($cn);?>