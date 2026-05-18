<?php 
include("conexion_bd.php");
require("class.phpmailer.php"); 

// sacar estas 2 lineas al momento de dejar definido un alias para las alertas del sisdoc 
$nombre = "Kiter " ;   
$correo= "kiter@minsal.gov.cl" ; 

//
//$nombre = "Alertas Sisdoc  " ;   
//$correo= "sisdocalertas@minsal.gov.cl" ; 

$cont=0;
$nombre= $nombre; // quien manda el correo
$cor_fun= $correo; // correo de quien manda el correo

//$nom_enc quien recibe el correo que viene con la variable nomd el nombre de la persona
//$cor_enc quien recibe el correo  que viene con la variable correod  en que está el correo de quien recibirá el correo 

//echo "nombre" . $nomd . "correo" . $correod;
// sacar al momento de dejar definida las variables que vengan del programa 
$nomd="karina Iter";
$correod= "kiter@minsal.gov.cl";

$nom_enc =$nomd ;
$cor_enc = $correod;


// envio de correo al  que tiene pendiente los documentos 
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
// datos de prueba deben venir de antes 
$iddoc=500;
$cusu="kiter";
$dusu=11;
$idfuncionario = 9 ;
$idseguim='';
// datos que vienen como arreglo doc. pendientes //
$docpend='2@500@700';
$vectorpend = split ("@",$docpend);
$largo= $vectorpend[0];
// datos que vienen como arreglo doc. vencidos  //
$docvenc='2@1500@400';
$vectorvenc = split ("@",$docvenc);
$largo1= $vectorvenc[0];

//echo "usuario" . $dusu ;
$k=1;
$sw_ok=0;
// 

//$refdocpend = "<a href=\"http://172.16.1.14/desarrollo/sisdoc/tramites_deriva.php?iddocum=" . $iddoc . "&cusuario=" . $cusu . "&idusuario=". $dusu ."\">documento</a>";

     $txt=  "<html>\n".
             "<head>\n".
             "<title>Documentos Pendientes </title>\n".
             "</head>\n".
             "<body bgcolor='#F4F4F4' text='#000000'>\n".
       	     "<center>\n".
     	     "Aviso de Documentos Pendientes y/o por Vencer del  Sr(a)  : " . $nom_enc . "<br>\n".
      	     "</center>\n".
             "<hr></hr>\n".         
     	     "<br>\n".
			 "Documentos Pendientes:   <br>\n";
	        
	for($k=1;$k <=$largo;$k++)
        {   
		    // buscando datos del documento 
			$flujo=8;
			$x=0;
			$y='';
			$prog=1;
			$busca_query = "exec buscadocofpartes '". $vectorpend[$k] . "'" ;
			$rs_query = mssql_query($busca_query,$cn);
			$reg_busca =mssql_fetch_array($rs_query);
	        $fecha_doc=strtotime($reg_busca["fecha_documento"]);
	        $fecha_doc =date("d/m/Y",$fecha_doc);	
       
            $datos = $reg_busca[desc_tipo_documento] . ' ' . $reg_busca[num_externo] . ' ' .$fecha_doc . ' '. $reg_busca[materia];			
			//despliegue en el correo de datos del documento con link al sistema 
			//$txt.="<a href=\"http://172.16.1.14/desarrollo/sisdoc/tramites_deriva.php?iddocum=".$vectorpend[$k]."&cusuario=".$cusu."&idusuario=".$dusu."\">".'Ver documento' . "</a>". '    ' .$datos .
		    //"<br>\n".
            	
			$txt.="<a href=\"#\" onCLick=\"javascript:location.replace('http://172.16.1.14/desarrollo/sisdoc/principal_correo.php?cusuario=".$cusu. "&iddocum=".$vectorpend[$k]."&tramite=".$prog."&idusuario=".$dusu."&idfuncionario=".$idfuncionario."&val_funcionario=". $x. "&val_procedencia=". $x."&val_funcionario1=". $x. "&val_destino=". $x. "&tipo_procedencia=". $y. "&tipo_destino=". $y. "&num_int=". $x.   "&flujo_ok=". $flujo. "')\">" ."Ver documento" .  "</a>". ' ' . $datos  .
            
			"<br>\n";
					
		//	"</body>".				
     // 		"</html>\n";			
     	 }
		 $k=1;
		 $txt.=   "<br>\n".
		  		  "<br>\n".		  
    		      "Documentos Vencidos:\n".
		  		  "<br>\n";		  
				  
	     	for($k=1;$k <=$largo;$k++)
        {   
		    // buscando datos del documento 
			$flujo=8;
			$x=0;
			$y='';
			$prog=1;
			$busca_query = "exec buscadocofpartes '". $vectorvenc[$k] . "'" ;
			$rs_query = mssql_query($busca_query,$cn);
			$reg_busca =mssql_fetch_array($rs_query);
	        $fecha_doc=strtotime($reg_busca["fecha_documento"]);
	        $fecha_doc =date("d/m/Y",$fecha_doc);	
       
            $datos = $reg_busca[desc_tipo_documento] . ' ' . $reg_busca[num_externo] . ' ' .$fecha_doc . ' '. $reg_busca[materia];			
			//despliegue en el correo de datos del documento con link al sistema 
			//$txt.="<a href=\"http://172.16.1.14/desarrollo/sisdoc/tramites_deriva.php?iddocum=".$vectorpend[$k]."&cusuario=".$cusu."&idusuario=".$dusu."\">".'Ver documento' . "</a>". '    ' .$datos .
		    //"<br>\n".
            	
			$txt.="<a href=\"#\" onCLick=\"javascript:location.replace('http://172.16.1.14/desarrollo/sisdoc/principal_correo.php?cusuario=".$cusu. "&iddocum=".$vectorvenc[$k]."&tramite=".$prog."&idusuario=".$dusu."&idfuncionario=".$idfuncionario."&val_funcionario=". $x. "&val_procedencia=". $x."&val_funcionario1=". $x. "&val_destino=". $x. "&tipo_procedencia=". $y. "&tipo_destino=". $y. "&num_int=". $x.   "&flujo_ok=". $flujo. "')\">" ."Ver documento" .  "</a>". ' ' . $datos  .
            "<br>\n";
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
   exit; 
}
   $ok_bien=6;
//    echo "Su mensaje ha sido enviado" . $nom_enc ; 

       //echo '<html><body onload="document.form1.submit();">';
	    //echo '<form name="form1" method="post" action="index.php">' . "\n";
	echo '</form></body></html>'  . "\n";

?> 
 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Alertas sisdoc por correo </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>

</body>
</html>
