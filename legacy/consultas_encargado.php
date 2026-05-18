<?php 
   include("conex_usuario.php");
//   echo "sw" . $sw . "rutx" . $rutx;  // se recibe rut y dependencia 
   if ($sw=="BUS")  // buscando rut en el corporativo
       {	    
	     $rut="select * from funcionario where rut_fun ='" . $rutx."'";
		 $rs=mssql_query($rut,$cn);
 		 $t =mssql_num_rows($rs);
		 $registro=mssql_fetch_array($rs);
		 if ($t>0)
		  {    
 		     echo "<script>\n";
		     echo " parent.mainFrame.document.form1.nombre.value='" .     $registro[nombres_fun] ."';\n";	 
			 echo " parent.mainFrame.document.form1.ap_pat_fun.value='" . $registro[ap_pat_fun] ."';\n";	 
			 echo " parent.mainFrame.document.form1.ap_mat_fun.value='" . $registro[ap_mat_fun] ."';\n";	 
 			 echo " parent.mainFrame.document.form1.correo.value='" .     $registro[email_fun] ."';\n";	 
 			 echo " parent.mainFrame.document.form1.clave.value='" .      $registro[marcacion_fun] ."';\n";	 
			 // buscando sexo persona 
			  if ($registro[sexo_fun]=='M')
    			{
				 $d=true;
				 $v='S';
				 
				 echo " parent.mainFrame.document.form1.sexo[0].checked='" . $d . "';\n";
		     echo " parent.mainFrame.document.form1.sexox.value= '" . $v ."';\n";
				 
    			}
		    if ($registro[sexo_fun]=='F')
    			{
			      $d=true;	
				  $v='F';
				 echo " parent.mainFrame.document.form1.sexo[1].checked='" . $d . "';\n";
			     echo " parent.mainFrame.document.form1.sexox.value= '" . $v ."';\n";
  			 	 
			    }
			  echo "</script>\n";
			 mssql_close($cn);
			 // obtenciendo campo de dependencia
			  ///obteniendo valores de los combo dependencia
			    include("conexion_bd.php");
				  // buscando usuario en funcionario				 
				  $busca_fun="select * from funcionario where rut='" . $rutx."' and vigencia is null ";				  
				  $r_fun=mssql_query($busca_fun,$cn);
				  $registro_fun=mssql_fetch_array($r_fun);
				  $i=1;
				  $dep="select * from dependencia where vigencia is null  order by desc_dependencia ";
				  $rper=mssql_query($dep,$cn);
			    
			      echo "<script>\n";
			      while($reg_per=mssql_fetch_array($rper))
				  {  
					  if($registro_fun["id_dependencia"]==$reg_per["id_dependencia"]) 
						echo " parent.mainFrame.form1.cbo_dependencia.selectedIndex='" . $i ."';\n";
						echo " parent.mainFrame.form1.cbo_dependencia.options[" . $i . "].selected;\n";
			  	    $i = $i + 1;	
				  }
			     echo "</script>\n";
		   		
			 // buscando vigencia 
	
			  if ($registro_fun["vigencia"]==NULL)
    			{
				 $d=true;
				 $v='S';
				 echo "<script>\n"; 
				 echo " parent.mainFrame.document.form1.vigencia[0].checked='" . $d . "';\n";
			     echo " parent.mainFrame.document.form1.vigenciax.value= '" . $v ."';\n";
				 echo "</script>\n";

    			}
		    if ($registro_fun["vigencia"]=='N')
    			{
			      $d=true;	
				  $v='N';
				 echo "<script>\n";
				 echo " parent.mainFrame.document.form1.vigencia[1].checked='" . $d . "';\n";
			     echo " parent.mainFrame.document.form1.vigenciax.value= '" . $v ."';\n";
  			 	 echo "</script>\n";   

			    }
 mssql_close($cn);
          }

	   }
?>   