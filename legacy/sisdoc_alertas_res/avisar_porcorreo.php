<?
 // buscando los documentos que hay que alertar por correo 
include("conexion_bd.php");
require("class.phpmailer.php"); 
$txtagno =date("Y");
 // buscando en tabla dependencias_alerta  la dependencia y los usuarios que les llegará el correo (se  rescata , id_dependencia,usuario,id_usuario)
 $rs_query="exec busca_dependencia_alerta ";
 $rs_dependencia =mssql_query($rs_query,$cn);
 while ($registro=mssql_fetch_array($rs_dependencia))
 { 
      // buscando nombre dependencia para enviar  al correo 
		$nom_depend="exec  busca_dependecinternas '". $registro["id_dependencia"] . "'" ;
		$reg_nom_depend =mssql_query($nom_depend,$cn);
		$reg_nombre =mssql_fetch_array($reg_nom_depend);
		$nombre_dependencia=$reg_nombre["desc_dependencia"];
		 //echo "nombre dependencia " . 	$nombre_dependencia . "<br>";
		
		// llamar al procedimiento que crea los arreglos que traen los datos 
     	$rs ="SELECT *,convert(varchar,d.fecha_documento,103) as fecha_dco, REPLACE(materia, CHAR(13) + CHAR(10), '') AS materia2,convert(varchar,d.fecha_sistema,112) as fecha_timbre_recepcion2 FROM tramite t
			INNER JOIN documento d ON t.id_documento= d.id_documento 
			INNER JOIN tipo_documento c ON d.id_tipo_documento= c.id_tipo_documento
			WHERE (t.id_destino = '".$registro["id_dependencia"]."' AND t.tipo_destinatario='I') AND (t.id_estado_tramite = 2 OR t.id_estado_tramite = 3) AND (YEAR(t.fecha_despacho) = ".$txtagno.")
			ORDER BY d.fecha_documento,d.id_documento";
		$rs0 =mssql_query($rs);
		$rs1=mssql_num_rows($rs0);
		$array_doc=array();$array_fecha=array();$array_id_compromiso=array();$array_dias_compromiso=array();
		while ($rs2=mssql_fetch_array($rs0, MYSQL_ASSOC))
		{
			$array_doc[]=$rs2["id_documento"];
			if ($rs2["fecha_timbre_recepcion"]==NULL)
			$array_fecha[]=$rs2["fecha_timbre_recepcion2"];
			else
			$array_fecha[]=$rs2["fecha_timbre_recepcion"];			
			$array_idseg[]=$rs2["id_seguimiento"];
			$array_nomina[]=$rs2["id_nomina_despacho"];
			$array_desc_tipo_doc[]=$rs2["desc_tipo_documento"];
			$array_num_externo[]=$rs2["num_externo"];
			$array_fecha_documento[]=$rs2["fecha_dco"];
			$array_materia[]=$rs2["materia2"];
			$array_num_interno[]=$rs2["num_interno"];
		
			if ($rs2["id_compromiso"]=="")
				$array_id_compromiso[]=9999;
			else
				$array_id_compromiso[]=$rs2["id_compromiso"];			
				$array_dias_compromiso[]=$rs2["dias_compromiso"];
		}		
		$array_verde=array();$array_amarillo=array();$array_rojo=array();
		for ($i=0;$i<$rs1;$i++)
		{
		$rs="";
		if ($array_id_compromiso[$i]==9999)
		{
			$plazo_alerta=10;
			$plazo_limite_dias=20;
		}
		else
		{
			$rs="SELECT * FROM tipo_compromiso2 WHERE id_compromiso=".$array_id_compromiso[$i]."";
			$rs0 =mssql_query($rs);
			$rs3=mssql_fetch_array($rs0, MYSQL_ASSOC);
			$plazo_alerta=$rs3["plazo_alerta"];
			$plazo_limite_dias=$rs3["plazo_limite_dias"];
		}
		$rs="SELECT COUNT(tipo_dia) AS cantidad FROM calendario where fecha BETWEEN ".$array_fecha[$i]." AND GETDATE() AND (tipo_dia = 'H')";
		$rs0 =@mssql_query($rs);
		$rs4=@mssql_fetch_array($rs0, MYSQL_ASSOC);
		$cantidad=$rs4["cantidad"];
		if ($cantidad<$plazo_alerta){
				$array_verde[]=$array_doc[$i];
				$verde_idseg[]=$array_idseg[$i];
				$verde_nomina[]=$array_nomina[$i];
				$verde_desc_tipo_doc[]=$array_desc_tipo_doc[$i];
				$verde_num_externo[]=$array_num_externo[$i];
				$verde_fecha_documento[]=$array_fecha_documento[$i];
				$verde_materia[]=$array_materia[$i];
				$verde_num_interno[]=$array_num_interno[$i];
		  }
		if (($cantidad>=$plazo_alerta) && ($cantidad<$plazo_limite_dias-2)){
				$array_amarillo[]=$array_doc[$i];
				$amarillo_idseg[]=$array_idseg[$i];
				$amarillo_nomina[]=$array_nomina[$i];
				$amarillo_desc_tipo_doc[]=$array_desc_tipo_doc[$i];
				$amarillo_num_externo[]=$array_num_externo[$i];
				$amarillo_fecha_documento[]=$array_fecha_documento[$i];
				$amarillo_materia[]=$array_materia[$i];
				$amarillo_num_interno[]=$array_num_interno[$i];
		}
		if ($cantidad>=$plazo_limite_dias-2){
				$array_rojo[]=$array_doc[$i];
				$rojo_idseg[]=$array_idseg[$i];
				$rojo_nomina[]=$array_nomina[$i];
				$rojo_desc_tipo_doc[]=$array_desc_tipo_doc[$i];
				$rojo_num_externo[]=$array_num_externo[$i];
				$rojo_fecha_documento[]=$array_fecha_documento[$i];
				$rojo_materia[]=$array_materia[$i];
				$rojo_num_interno[]=$array_num_interno[$i];
		}
	  }
	// Desde aca se empieza a  armar el arreglo para enviar datos al programa de envio de correo
	  // documentos en verde 
	  
	  $largo_verde= count($array_verde);
		$arreglo =array();
		$arreglo= $array_verde;
		 $arreglo_verde='' ;
		 if ($largo_verde==0)
		     $arreglo_verde='' ;
		 else 
		    $arreglo_verde=$largo_verde . '@' . $arreglo[0];	 
		 
		for ($i=1;$i<$largo_verde;$i++)
	     {  
		   $arreglo_verde=$arreglo_verde. '@' . $arreglo[$i];		 
		 }	
	  // documentos en amarillo 
	  $largo_amarillo= count($array_amarillo);
		$arreglo =array();
		$arreglo= $array_amarillo;
		 $arreglo_amarillo='' ;
		 if ($largo_amarillo==0)
		     $arreglo_amarillo='' ;
		 else 
		    $arreglo_amarillo=$largo_amarillo . '@' . $arreglo[0];	 
		 
		for ($i=1;$i<$largo_amarillo;$i++)
	     {  
		   $arreglo_amarillo=$arreglo_amarillo. '@' . $arreglo[$i];		 
		 }	
	  // documentos en  rojo
	     
	    $largo_rojo= count($array_rojo);
		$arreglo =array();
		$arreglo= $array_rojo;
		 $arreglo_rojo='' ;
		 if ($largo_rojo==0)
		     $arreglo_rojo='' ;
		 else 
		    $arreglo_rojo=$largo_rojo . '@' . $arreglo[0];	 
		 
		for ($i=1;$i<$largo_rojo;$i++)
	     {  
		   $arreglo_rojo=$arreglo_rojo. '@' . $arreglo[$i];		 
		 }	
		 // obteniendo correo al cual se enviaran los datos 
		  $correod =$registro["correo_usuario"];
		  // obteniendo datos del usuario para enviar al  sistema  id_funcionario, id_usuario,c_usuario
		  $rs_busca="exec  busca_usuario_id '". $registro["id_usuario"] . "'" ;
		   $qq=mssql_query($rs_busca,$cn);
		   $reg=mssql_fetch_array($qq);
 		   // rescatando el id_usuario,id_funcionario y cusuario de la persona que se le envia el correo 
		   $cusu = $reg["usuario"];
		   $dusu = $reg["id_usuario"];
		   $idfuncionario =$reg["id_funcionario"];
		   $rut =$reg["rut"];
		   // buscando nombre de la persona a  enviar correo 
		   $rs_func="exec  busca_funcionario_rut '". $rut . "'" ;
		   $qq_func=mssql_query($rs_func,$cn);
		   $func=mssql_fetch_array($qq_func);
		   $nomd ='\'' . rtrim($func["nombres"]). ' ' .  rtrim($func["apellidos"]) .'\'';
		   
		   // pasando variables a string 
		   //$cusu='\''.$cusu .'\'';
           $correod=$correod ;
		   //$nombre_dependencia ='\''.$nombre_dependencia.'\'';

			/*echo "cusu" . $cusu . "idusuario" .$dusu . "func" . $idfuncionario . "<br>";
			echo  "nomd" . $nomd . "correod" . $correod . "dep" . $nombre_dependencia ."<br>";
			echo "arreglo_rojo" . $arreglo_rojo ."<br>";
			echo "arreglo_verde" . $arreglo_verde ."<br>";
			echo "arreglo_amarillo" . $arreglo_amarillo ."<br>";
			*/
			echo "Usuario  : " . $cusu  . "   "  .  $nombre_dependencia ."<br>";  
		 // Mandar los correos a sus destinos y al coordinador general 
 		   /*echo '<html><body onload="document.form1.submit();">';
	       echo '<form name="form1" method="post" action="envia_correo_alerta2.php">' . "\n";
		   echo '<input type="hidden" name="arreglorojo" value="' . $arreglo_rojo . '">';
		   echo '<input type="hidden" name="arregloverde" value="' . $arreglo_verde . '">';
		   echo '<input type="hidden" name="arregloamarillo" value="' . $arreglo_amarillo . '">';
   		   echo '<input type="hidden" name="nomd" value="' . $nomd . '">';
   		   echo '<input type="hidden" name="dep" value="' . $nombre_dependencia . '">';
   		   echo '<input type="hidden" name="correod" value="' . $correod . '">';
   		   echo '<input type="hidden" name="cusu" value="' . $cusu . '">';
   		   echo '<input type="hidden" name="dusu" value="' . $dusu . '">';
   		   echo '<input type="hidden" name="id_funcionario" value="' . $idfun . '">';
           echo '</form>';
		   echo '</body>';
		   echo '</html>';     
         */
		//echo "lagos rojo"  . $largo_rojo . "verde" . $largo_verde . "amarillo" .$largo_amarillo . "<br>";
		if ($largo_rojo <> 0 || $largo_verde<>0 ||$largo_amarillo<>0)
		 {
		   // Dejar definido un alias para las alertas del sisdoc 
		   $nombre = "Alertas Sisdoc  " ;   
           $correo= "sisdoc@minsal.gov.cl" ; 
		   
		   $cont=0;
           $nombre= $nombre; // quien manda el correo
           $cor_fun= $correo; // correo de quien manda el correo

			//$nom_enc quien recibe el correo que viene con la variable nomd el nombre de la persona
			//$cor_enc quien recibe el correo  que viene con la variable correod  en que está el correo de quien recibirá el correo 
			$nom_enc =$nomd ;
			$cor_enc = $correod;
			
			//  cambiando los arreglos para desplegar en correo los datos 
			$arreglorojo = split ("@",$arreglo_rojo);
			$largo_rojo= $arreglorojo[0];
			$arregloverde = split ("@",$arreglo_verde);
			$largo_verde= $arregloverde[0];
			$arregloamarillo= split ("@",$arreglo_amarillo);
			$largo_amarillo= $arregloamarillo[0];

			// envio de correo al  que tiene pendiente los documentos
//			echo "nomd" . $nombre_dependencia . "correod" . $correod. "<br>"; 
			$mail = new phpmailer(); 
			$mail->IsSMTP();  // le indica al Linux usar SMTP 
			$mail->Host = "172.16.1.7";  // servidor donde está el SMTP (SENDMAIL) 
			$mail->SMTPAuth = false;     // no necesitamos autenticación SMTP 
			$mail->From =  $cor_fun; 
			$mail->FromName = $nombre; //El nombre de quien envía 
			$mail->AddAddress($cor_enc,$nom_enc);
			//copia oculta del correo
			//$mail->AddBCC("coordinador_alertas_sisdoc@minsal.gov.cl","Coordinador_alertas_sisdoc");
			$mail->WordWrap = 50;                                 //  salto de linea a los 50 caracteres 
			$mail->IsHTML(true);                                  // configura el email con formato HTML 
			$mail->Subject = "Aviso documentos por vencer  y/o vencidos "; 
			$valor=5;
			$cont=5;
			$idseguim='';
			$k=1;
			$sw_ok=0;

    		 $txt=  "<html>\n".
             "<head>\n".
             "<title>Documentos Pendientes </title>\n".
             "</head>\n".
             "<body bgcolor='#F4F4F4' text='#000000'>\n".
       	     "<center>\n".
     	     "Aviso de Documentos Pendientes y/o por Vencer de  : " . $nombre_dependencia . "<br>\n".
      	     "</center>\n".
             "<hr></hr>\n".         
     	     "<br>\n";
			 
			 if ($largo_verde <>0)
			 {
			 $txt.="Documentos en trámite:   <br>\n";
			 }
			 if($largo_verde<>0 || $largo_rojo<>0  || $largo_amarillo <>0 )
			{
			$txt.= "<table border ='1' >";
	        }
	    	for($k=1;$k <=$largo_verde;$k++)
        	{   
		    // buscando datos del documento 
			$flujo=8;
			$x=0;
			$y='';
			$prog=1;
			$busca_query = "exec buscadocofpartes '". $arregloverde[$k] . "'" ;
			$rs_query = mssql_query($busca_query,$cn);
			$reg_busca =mssql_fetch_array($rs_query);
	        $fecha_doc=strtotime($reg_busca["fecha_documento"]);
	        $fecha_doc =date("d/m/Y",$fecha_doc);       
            //$datos = $reg_busca[desc_tipo_documento] . ' ' . $reg_busca[num_externo] . ' ' .$fecha_doc . ' '. $reg_busca[materia];			
			//despliegue en el correo de datos del documento con link al sistema             	
			$txt.="<tr>
			         <td><a href=\"#\" onCLick=\"javascript:location.replace('http://172.16.1.14/desarrollo/sisdoc/principal_correo.php?cusuario=".$cusu."&iddocum=".$arregloverde[$k]."&tramite=".$prog."&idusuario=".$dusu."&idfuncionario=".$idfuncionario."&val_funcionario=". $x. "&val_procedencia=". $x."&val_funcionario1=". $x. "&val_destino=". $x. "&tipo_procedencia=". $y. "&tipo_destino=". $y. "&num_int=". $x.   "&flujo_ok=". $flujo. "')\">Ver documento</a></td>
			         <td>".$reg_busca[desc_tipo_documento]."</td>
			         <td>".$reg_busca[num_interno]."</td>
			         <td>".$reg_busca[num_externo]."</td>
			         <td>".$fecha_doc."</td>
			         <td>".substr($reg_busca[materia],0,60)."<td>
			      </tr>";					
			
     	   }
		  $k=1;
		  if ($largo_rojo<> 0)
			 {
			 $txt.=   "</table>
		           <br>\n".
		  		  "<br>\n".		  
    		      "Documentos Vencidos:\n".
		  		  "<br>\n".
				   "<table border ='1' >";		  
			$txt.="<tr><td>". 'Ver' . "</td><td>" .'Tipo doc' . "</td><td>" .'Num. Interno' ."</td><td>" .'Num. Externo' ."</td><td>" .'Fecha Doc. ' ."</td><td>".'Materia' . "</td></tr>";	  
	    	for($k=1;$k <=$largo_rojo;$k++)
	        {   
		    // buscando datos del documento 
			$flujo=8;
			$x=0;
			$y='';
			$prog=1;
			$busca_query = "exec buscadocofpartes '". $arreglorojo[$k] . "'" ;
			$rs_query = mssql_query($busca_query,$cn);
			$reg_busca =mssql_fetch_array($rs_query);
	        $fecha_doc=strtotime($reg_busca["fecha_documento"]);
	        $fecha_doc =date("d/m/Y",$fecha_doc);	    
           // $datos = $reg_busca[desc_tipo_documento] . ' ' . $reg_busca[num_externo] . ' ' .$fecha_doc . ' '. $reg_busca[materia];				
			//despliegue en el correo de datos del documento con link al sistema 
	        $txt.="<tr>	
                    <td><a href=\"#\" onCLick=\"javascript:location.replace('http://172.16.1.14/desarrollo/sisdoc/principal_correo.php?cusuario=".$cusu."&iddocum=".$arreglorojo[$k]."&tramite=".$prog."&idusuario=".$dusu."&idfuncionario=".$idfuncionario."&val_funcionario=". $x. "&val_procedencia=". $x."&val_funcionario1=". $x. "&val_destino=". $x. "&tipo_procedencia=". $y. "&tipo_destino=". $y. "&num_int=". $x.   "&flujo_ok=". $flujo. "')\">Ver documento</a></td>
                    <td>".$reg_busca[desc_tipo_documento]."</td>
			         <td>".$reg_busca[num_interno]."</td>
			        <td>".$reg_busca[num_externo]."</td>
			        <td>".$fecha_doc."</td>
			        <td>". substr($reg_busca[materia],0,60)."<td>
			      </tr>";
    	     }	
		     "</table>";
		    }
			$k=1;
		  if ($largo_amarillo<> 0)
			 {
			 $txt.=   "</table>
		           <br>\n".
		  		  "<br>\n".		  
    		      "Documentos por Vencer:\n".
		  		  "<br>\n".
				   "<table border ='1' >";		  
		
			$txt.="<tr><td>". 'Ver' . "</td><td>" .'Tipo doc' . "</td><td>" .'Num. Interno' ."</td><td>" .'Num. Externo' ."</td><td>" .'Fecha Doc. ' ."</td><td>".'Materia' . "</td></tr>";	  
	    	for($k=1;$k <=$largo_amarillo;$k++)
	        {   
		    // buscando datos del documento 
			$flujo=8;
			$x=0;
			$y='';
			$prog=1;
			$busca_query = "exec buscadocofpartes '". $arregloamarillo[$k] . "'" ;
			$rs_query = mssql_query($busca_query,$cn);
			$reg_busca =mssql_fetch_array($rs_query);
	        $fecha_doc=strtotime($reg_busca["fecha_documento"]);
	        $fecha_doc =date("d/m/Y",$fecha_doc);	    
            //$datos = $reg_busca[desc_tipo_documento] . ' ' . $reg_busca[num_externo] . ' ' .$fecha_doc . ' '. $reg_busca[materia];				
			//despliegue en el correo de datos del documento con link al sistema 
	        $txt.="<tr>	
                    <td><a href=\"#\" onCLick=\"javascript:location.replace('http://172.16.1.14/desarrollo/sisdoc/principal_correo.php?cusuario=".$cusu."&iddocum=".$arregloamarillo[$k]."&tramite=".$prog."&idusuario=".$dusu."&idfuncionario=".$idfuncionario."&val_funcionario=". $x. "&val_procedencia=". $x."&val_funcionario1=". $x. "&val_destino=". $x. "&tipo_procedencia=". $y. "&tipo_destino=". $y. "&num_int=". $x.   "&flujo_ok=". $flujo. "')\">Ver documento</a></td>
                    <td>".$reg_busca[desc_tipo_documento]."</td>
			         <td>".$reg_busca[num_interno]."</td>
			        <td>".$reg_busca[num_externo]."</td>
			        <td>".$fecha_doc."</td>
			        <td>".substr($reg_busca[materia],0,60)."<td>
			      </tr>";
    	     }	
		     "</table>";
		    }
			"</body>".				
      		"</html>\n";			

			$mail->Body = $txt;
			//la siguiente línea es muy útil agregarla para los destinatarios que no ven claramente HTML (unix, wap, etc.) 
			//$mail->AltBody = "Esto sería lo que recibe un cliente sin posibilidades de ver HTML"; 
			if(!$mail->Send()) 
			{ 
		       echo "Fallo en el envío del correo de . <p>" . $nom_enc; 
				echo "Error de tipo: " . $mail->ErrorInfo; 
			   //exit; 
			}
		   $ok_bien=6;
		      //    echo "Su mensaje ha sido enviado" . $nom_enc ; 
            } // if de largos <>0
			
    }// while $registro 
 
?>


