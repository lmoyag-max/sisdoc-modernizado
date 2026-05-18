<?php
	session_start();
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
   
 // busca por rut en tabla de usuario de sisdoc //
 
  	else{
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
		$tipo_alertas=$reg[tipo_alertas]; // new sobre alertas
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
$totoirs =0; // se cambia para el caso que la instalacion no sea minsal
 		// extrae la dependencia del usuario 19/07/2007
		$rs_dependencia="exec busca_funcionario_rut'" . $aux_rut . "'";
		$qr = mssql_query($rs_dependencia,$cn);
		$regdep =mssql_fetch_array($qr);
		$id_dependencia  =$regdep[id_dependencia];
		
		//
		// echo "dependencia". $id_dependencia . "usuario" . $id_usuario. "totoirs". $totoirs . "tipo alertas" . $tipo_alertas;
  
  
  // fin cambio 
  
		if ($tot_dep == 0){
			if ($tipo_menu <>'C')
			{
				if ($totoirs==0)
				{  // otros funcionarios que no son de esa dependencia (17 y 44 ) 
					if ($tipo_alertas=='A')
					   {
						echo '<html><body onload="document.form1.submit();">';
						echo '<form name="form1" method="post" action="sisdoc_alertas/principal.php">';
					    }
					else{
						echo '<html><body onload="document.form1.submit();">';
						echo '<form name="form1" method="post" action="principal.php">';
					    }
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
				} // $totoirs == 0
				else  // funcionarios que son de la dependencia  17 y 44  se saca el 22/11/2010 para que considere a todos por igual
				
				     if ($tipo_alertas=='A')
				  {
				//	echo '<html><body onload="document.form1.submit();">';
				//	echo '<form name="form1" method="post" action="sisdoc_alertas/principal_oirs.php">';
	               }
				   else{    // usuario oris sin alerta  y sin consulta
			//	    echo '<html><body onload="document.form1.submit();">';
			//		echo '<form name="form1" method="post" action="principal_oirs.php">';
			       }			
			/*	    echo '<input type="hidden" name="cusuario" value="' . $x_usuario . '">';
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
					echo '<input type="hidden" name="tipo_frame" value="5">';
					echo "</form></body></html>";
			*/			
			} //$tipo_menu <> C
			else //acceso a  perfil de consulta 
				if ($totoirs==0)
				{  // otros funcionarios que no son de esa dependencia (17 y 44 )  30-08-2006
					if ($tipo_alertas=='A')
					{
						echo '<html><body onload="document.form1.submit();">';
						echo '<form name="form1" method="post" action="sisdoc_alertas/principal_consulta.php">';
					}
				   else 
				     {
				   		echo '<html><body onload="document.form1.submit();">';
						echo '<form name="form1" method="post" action="principal_consulta.php">';
					}
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
					echo '<input type="hidden" name="tipo_frame" value="3">';// tipo frame = 3 (principal)  	
					echo "</form></body></html>"; 
					
	            }   // $totoirs=0
				else
			{}	  // funcionarios que son de la dependencia  17 y 44 
                  if ($tipo_alertas =='A')
				  {
					//echo '<html><body onload="document.form1.submit();">';
					//echo '<form name="form1" method="post" action="sisdoc_alertas/principal_consulta_oirs.php">';
				   }
				   else
				   {
					//echo '<html><body onload="document.form1.submit();">';
					//echo '<form name="form1" method="post" action="principal_consulta_oirs.php">';
				   }
				/*echo '<input type="hidden" name="cusuario" value="' . $x_usuario . '">';
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
				echo '<input type="hidden" name="tipo_frame" value="4">';// tipo frame = 4 (principal)  	
				echo "</form></body></html>"; 
			*/	  }
  		
		else{
			echo '<html><body onload="document.form1.submit();">';
			echo '<form name="form1" method="post" action="sisdoc_alertas/menu_ofpartes.php">';
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
	
	mssql_close($cn);?>
	
	