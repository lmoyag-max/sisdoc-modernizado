<?php 
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
?> 
