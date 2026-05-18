<?php 
require("class.phpmailer.php"); 
$rutx=$rut;
$num_int=$num_int;
$cont=0;
//echo "num int " . $num_int;
//$num_int=40336;
// buscar datos para adjuntar
$cn = mssql_connect("bd-minsal", "sa", "sqlminsal") or die("El Servidor No se encuentra");
	mssql_select_db("intranet");
$query_fun = "select * from funcionarios_correo where rut='" . $rutx . "'";
//$rs_usu="select * from funcionarios_correo where rut='" .  $rut_a . "'";
$rs_fun= mssql_query($query_fun, $cn);
$reg_fun= mssql_fetch_array($rs_fun);
$filas_fun = mssql_num_rows($rs_fun);


//Envío correo a movilización
if($filas_fun >0)
{
$nombre= ltrim($reg_fun[3]);
$cor_fun= $reg_fun[5];
}
//$cor_enc="movilizacion@minsal.cl";
//$nom_enc="Sistema de Movilización";
$cor_enc="xmoreno@minsal.gov.cl";
$nom_enc="Sistema de Movilización";


// envio de correo a bienestar
$mail = new phpmailer(); 

$mail->IsSMTP();  // le indica al Linux usar SMTP 
$mail->Host = "172.16.1.7";  // servidor donde está el SMTP (SENDMAIL) 
$mail->SMTPAuth = false;     // no necesitamos autenticación SMTP 

$mail->From =  $cor_fun; 
$mail->FromName = $nombre; //El nombre de quien envía 
$mail->AddAddress($cor_enc,$nom_enc);
//copia oculta del correo
$mail->AddBCC("xmoreno@minsal.gov.cl","ximena");
 //A quien Dirijo el mail 
//$mail->AddAddress("enrique@corbalan.cl");            //Otro destinatario (el nombre como ven es opcional 
//$mail->AddReplyTo("ecorbalan@hotmail.cl", "Elmesmo"); //Esta es la casilla que quedará para contestar a quien recibe 

$mail->WordWrap = 50;                                 //  salto de linea a los 50 caracteres 
$mail->IsHTML(true);                                  // configura el email con formato HTML 

$mail->Subject = "Solicitud de Móvil"; 
$valor=5;
$cont=5;

$mi_body = '<a href="http://172.16.1.14/movilizacion/index_encargado.php?rut=' . $rut . '&cont=' . $cont .  '&num_int=' . $num_int . '">' . "Folio : " . $num_int . '</a>';
$mail->Body    = 
//"Solicitud de Móvil de Sr(a) : " . $nombre . "<br>"	. $mi_body;

 "<html>\n" .
              "<head>\n" .
               "<title>Solicitud de Móvil</title>\n" .
              "</head>\n" .
              "<body bgcolor='#F4F4F4' text='#000000'>" . "\n" .
       	       "<center>" . "\n" .
     	        "Solicitud de Móvil de  : " . $nombre . "<br>" .  "\n" .
     	       "</center>". "\n" .
                "<hr></hr>" . "\n" .           
     	        "<br>\n" .
                "La Solicitud de Móvil ha sido recepcionada " . "\n" . 
     		"<br>" . "\n" .
                "con el número de " . $mi_body . "\n" .
     		"<br>" ."\n".
     	      	"<br>" . "\n" .
              "</body>\n"  .
             "</html>\n"; 


if(!$mail->Send()) 
{ 
   echo "Fallo en el envío. <p>"; 
   echo "Error de tipo: " . $mail->ErrorInfo; 
   exit; 
}
else
{
// Respuesta al usuario indicandole el folio

$mail = new phpmailer(); 

$mail->IsSMTP();  // le indica al Linux usar SMTP 
$mail->Host = "172.16.1.7";  // servidor donde está el SMTP (SENDMAIL) 
$mail->SMTPAuth = false;     // no necesitamos autenticación SMTP 

//$mail->From =  "xmoreno@minsal.gov.cl"; 
$mail->From =  "movilizacion@minsal.cl"; 
$mail->FromName = "Sistema de Movilización"; //El nombre de quien envía 
$mail->AddAddress($cor_fun,$nombre); //A quien Dirijo el mail 
//$mail->AddAddress("enrique@corbalan.cl");            //Otro destinatario (el nombre como ven es opcional 
//$mail->AddReplyTo("ecorbalan@hotmail.cl", "Elmesmo"); //Esta es la casilla que quedará para contestar a quien recibe 

$mail->WordWrap = 50;                                 //  salto de linea a los 50 caracteres 
//$mail->AddAttachment("http://linux80/ximena/bienestar/ingreso.php");         // archivo adjunto 
//$mail->AddAttachment("/var/www/htdocs/sisdoc/images/mas.gif", "otronombre.gif");    // archivo adjunto y con otro nombre 
$mail->IsHTML(true);                                  // configura el email con formato HTML 

$mail->Subject = "Solicitud de Móvil"; 
$valor=5;
$cont=5;

$mail->Body    =

"<html>\n" .
              "<head>\n" .
               "<title>Recepción de solicitud</title>\n" .
              "</head>\n" .
              "<body bgcolor='#F4F4F4' text='#000000'>" . "\n" .
       	       "<center>" . "\n" .
     	        "Recepción Solicitud de  : " . $nombre . "<br>" .  "\n" .
     	       "</center>". "\n" .
                "<hr></hr>" . "\n" .           
     	        "<br>\n" .
                "Usted ha Solicitado un Vehículo a Movilización Minsal con el Folio = " . $num_int .  "\n" . 
     		"<br><br>" . "\n" .
                "Gracias por usar el Sistema" . "<br>" . "<br>"	.
			    "Sistema Desarrollado por el Depto. de Informática Minsal" .  "\n" .
     		"<br>" ."\n".
     	      	"<br>" . "\n" .
              "</body>\n"  .
             "</html>\n"; 





				

//la siguiente línea es muy útil agragarla para los destinatarios que no ven claramente HTML (unix, wap, etc.) 
//$mail->AltBody = "Esto sería lo que recibe un cliente sin posibilidades de ver HTML"; 

if(!$mail->Send()) 
{ 
   echo "Fallo en el envío. <p>"; 
   echo "Error de tipo: " . $mail->ErrorInfo; 
   exit; 
}

} 

$ok_bien=6;
//echo "Su mensaje ha sido enviado"; 
	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="ingreso_docto1.php">' . "\n";
	echo '<input type="hidden" name="ok" value="' . 6 . '">' . "\n";
	echo '<input type="hidden" name="rut" value="' . $rut . '">';
	echo '<input type="hidden" name="cusuario" value="' . $rut . '">';
	echo '<input type="hidden" name="num_int" value="' . $num_int . '">';
	echo '<input type="hidden" name="nombre" value="' . $nombre . '">' . "\n";
	echo '<input type="hidden" name="cont" value="' . $cont . '">' . "\n";
	echo '</form></body></html>'  . "\n";

?> 
 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Documento sin t&iacute;tulo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>

</body>
</html>
