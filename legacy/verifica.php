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
   echo "rut" . $aux_rut ;
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
        echo '<meta http-equiv=refresh content="2;URL=index.php">' . "\n";
	    echo "</Head>\n";
    	echo "<body>\n";
	    echo "<center>\n";
    	echo "<SMALL>Sistema Control de Documentos <br><br></SMALL>\n";
        echo '<p><b><font color="#000099">Usted no tiene Acceso </font></b></p>' . "\n";
        echo '<p><b><font color="#000099">Comuniquese con el Administrador del Sistema</font></b></p>' . "\n";
	    echo "</center>\n";
	   /* echo $rut  . "<br>";
	    echo $clave  . "<br>";*/
        echo "</body>\n";
        echo "</HTML>\n";
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
   mssql_close($cn); 	
  include("conexion_bd.php");
  //  $rs_usuario="exec busca_usuario '" . $usuario . "','" . $contrasena . "'";
  $rs_usuario="exec busca_funcionario_usuario'" .  $aux_rut . "'";
  $qq = mssql_query($rs_usuario,$cn);  
  $reg = mssql_fetch_array($qq);
  $x_usuario=$reg['usuario'];
  $id_usuario  =$reg['id_usuario'];
  $tipo_menu  =$reg['tipo_menu'];
  $id_funcionario=$reg['id_funcionario'];
  $Tot_usu = mssql_num_rows($qq);
  // busca si el usuario es de oficina de partes 
  $rs_depend="exec busca_dependec_ofpartes'" . $aux_rut . "'";
  //echo "<br>preg:".$rs_depend."<br>";
  $qr = mssql_query($rs_depend,$cn);
  $reg_dep =mssql_fetch_array($qr);
  $tot_dep =mssql_num_rows($qr);
  // busca si el usuario es de la oirs  30/08/2006
  $oirs_depend="exec busca_dependec_oirs'" . $aux_rut . "'";
  $qr = mssql_query($oirs_depend,$cn);
  $regdep =mssql_fetch_array($qr);
  $totoirs =mssql_num_rows($qr);
  $totoirs=0 // se agrega para el ministerio de salud
  // extrae la dependencia del usuario 19/07/2007
  $rs_dependencia="exec busca_funcionario_rut'" . $aux_rut . "'";
  $qr = mssql_query($rs_dependencia,$cn);
  $regdep =mssql_fetch_array($qr);
  $id_dependencia  =$regdep['id_dependencia'];
  
  // llama a programa de encuesta 
  $rs_busca_encuesta="exec busca_encuesta'" . $aux_rut . "'" ;
  $qe = mssql_query($rs_busca_encuesta,$cn);
  $regqe =mssql_fetch_array($qe);
  $existe=mssql_num_rows($qe);
  echo "tot_dep" . $tot_dep . "tipo_menu" . $tipo_menu . "totoirs" .$totoirs ; 
  if ($existe == 0)
  {  
  	echo '<html><body onload="document.form1.submit();">';
	  echo '<form name="form1" method="post" action="sisdoc_encuesta.php">';
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
	  echo '<input type="hidden" name="id_dependencia" value="'.$id_dependencia.'">';
	  echo '<input type="hidden" name="tipo_frame" value="1">';// tipo frame = 1 (principal)
	  // para llamar al programa que corresponda
	  echo '<input type="hidden" name="tot_dep" value="' . $tot_dep . '">';
	  echo '<input type="hidden" name="tipo_menu" value="' . $tipo_menu . '">';
	  echo '<input type="hidden" name="totoirs" value="' . $totoirs . '">';
 	  echo '<input type="hidden" name="aux_rut" value="' . $aux_rut . '">';
 	  echo "</form></body></html>";
  }	  
  else 
  {
  // fin cambio 
  
  // se comentan para efecto de la encuesta , despues se debe sacar 
  if ($tot_dep == 0)
  {
    if ($tipo_menu <>'C')
    {
	 if ($totoirs==0)  // otros funcionarios que no son de esa dependencia (17 y 44 ) 
	 {  	
	echo '<html><body onload="document.form1.submit();">';
  	echo '<form name="form1" method="post" action="principal.php">';
	}
	/*else // funcionarios que son de la dependencia  17 y 44 hecho para el ministerio de salud
  	{ //echo "3" ;
	echo '<html><body onload="document.form1.submit();">';
  	echo '<form name="form1" method="post" action="principal_oirs.php">';
	}*/
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
	echo '<input type="hidden" name="id_dependencia" value="'.$id_dependencia.'">';
	echo '<input type="hidden" name="tipo_frame" value="1">';// tipo frame = 1 (principal)
  	echo "</form></body></html>";
   }
   else
   {
    if ($totoirs==0)  // otros funcionarios que no son de esa dependencia (17 y 44 )  30-08-2006
	 {  
	 	
	echo '<html><body onload="document.form1.submit();">';
  	echo '<form name="form1" method="post" action="principal_consulta.php">';
	}
	/*else // funcionarios que son de la dependencia  17 y 44 hechos para el ministerio de salud 
	{ 
	
  	echo '<html><body onload="document.form1.submit();">';
  	echo '<form name="form1" method="post" action="principal_consulta_oirs.php">';
  	}*/
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
	echo '<input type="hidden" name="id_dependencia" value="'.$id_dependencia.'">';
	echo '<input type="hidden" name="tipo_frame" value="1">';// tipo frame = 1 (principal)
  	echo "</form></body></html>"; 
   }
 }
  else
  { 
echo '<html><body onload="document.form1.submit();">';
 echo '<form name="form1" method="post" action="menu_ofpartes.php">';
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
  echo '<input type="hidden" name="id_dependencia" value="'.$id_dependencia.'">';
  echo '<input type="hidden" name="tipo_frame" value="2">';// tipo frame = 2 (of partes)
  echo "</form></body></html>";
  }   
 }
}
	mssql_close($cn);?>
	
	