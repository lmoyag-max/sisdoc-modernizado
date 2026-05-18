<?php 
require("class.phpmailer.php"); 
$rut=$rut;
$rut_c=$rut_c;
// buscar datos para adjuntar
$cn = mssql_connect("BD2-MINSAL", "corpora", "corp2003") or die("El Servidor No se encuentra");
mssql_select_db("corporativo");
$query_fun = "select * from funcionario where rut_fun='" . $rut . "'";
$rs_fun= mssql_query($query_fun, $cn);
$filas_fun = mssql_num_rows($rs_fun);
$reg_fun= mssql_fetch_array($rs_fun);
if($filas_fun >0)
{
$pat_fun= $reg_fun[ap_pat_fun];
$mat_fun= $reg_fun[ap_mat_fun];
$nom_fun= $reg_fun[nombres_fun];
$cor_fun= $reg_fun[email_fun];
$nombre = ltrim($nom_fun) . " " . ltrim($pat_fun) . " " . ltrim($mat_fun);
}



// envio de correo a bienestar
$mail = new phpmailer(); 

$mail->IsSMTP();  // le indica al Linux usar SMTP 
$mail->Host = "172.16.1.7";  // servidor donde está el SMTP (SENDMAIL) 
$mail->SMTPAuth = false;     // no necesitamos autenticación SMTP 

$mail->From =  $cor_fun; 
$mail->FromName = $nombre; //El nombre de quien envía 
$mail->AddAddress("xmoreno@minsal.cl", "ximena"); //A quien Dirijo el mail 
//$mail->AddAddress("enrique@corbalan.cl");            //Otro destinatario (el nombre como ven es opcional 
//$mail->AddReplyTo("ecorbalan@hotmail.cl", "Elmesmo"); //Esta es la casilla que quedará para contestar a quien recibe 

$mail->WordWrap = 50;                                 //  salto de linea a los 50 caracteres 
//$mail->AddAttachment("http://linux80/ximena/bienestar/ingreso.php");         // archivo adjunto 
//$mail->AddAttachment("/var/www/htdocs/sisdoc/images/mas.gif", "otronombre.gif");    // archivo adjunto y con otro nombre 
$mail->IsHTML(true);                                  // configura el email con formato HTML 

$mail->Subject = "Solicitud de ingreso a Bienestar"; 
$valor=5;
$cont=5;
//$mi_body = '<a href="http://linux80/ximena/bienestar/respuesta.php?rut=' . $rut . '">' . "Rut : " . $rut_c . '</a>';

//$mi_body = '<a href="http://linux80/ximena/bienestar/respuesta.php?rut=' . $rut . '&flujook=' . $valor . '">' . "Rut : " . $rut_c . '</a>';
//$mi_body = '<a href="http://linux80/ximena/bienestar/index_encargado.php?cont=' . $cont .  '">' . "Rut : " . $rut_c . '</a>';
$mi_body = '<a href="http://linux80/ximena/bienestar/index_encargado.php?rut_funcionario=' . $rut . '&cont=' . $cont . '">' . "Rut : " . $rut_c . '</a>';

//$mi_body = $mi_body . '<p><h1>Hola Ximena</h1></p>';
$mail->Body    = "Solicitud de ingreso a Bienestar Sr(a) : " . $nombre . "<br>"	. $mi_body;

//$mail->Body    = "Este es un mensaje de prueba con HTML en el body <b>Saludos!</b>"; 

//la siguiente línea es muy útil agragarla para los destinatarios que no ven claramente HTML (unix, wap, etc.) 
$mail->AltBody = "Esto sería lo que recibe un cliente sin posibilidades de ver HTML"; 

if(!$mail->Send()) 
{ 
   echo "Fallo en el envío. <p>"; 
   echo "Error de tipo: " . $mail->ErrorInfo; 
   exit; 
} 
$ok_bien=6;
//echo "Su mensaje ha sido enviado"; 
	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="ingreso_bienestar.php">' . "\n";
	echo '<input type="hidden" name="flujook" value="' . $ok_bien . '">' . "\n";
	echo '<input type="hidden" name="rut_fun" value="' . $rut . '">';
	echo '<input type="hidden" name="rut_c" value="' . $rut_c . '">';
	
	echo '<input type="hidden" name="nom_fun" value="' . $txtnombres . '">' . "\n";
	echo '<input type="hidden" name="mat_fun" value="' . $txtmaterno . '">';
	echo '<input type="hidden" name="pat_fun" value="' . $txtpaterno . '">';
	echo '<input type="hidden" name="dir_fun" value="' . $txtdireccion . '">' . "\n";
	echo '<input type="hidden" name="ane_fun" value="' . $txtanexo . '">';
	echo '<input type="hidden" name="gra_fun" value="' . $txtgrado . '">';
	echo '<input type="hidden" name="region" value="' . $cbo_region . '">';
	echo '<input type="hidden" name="comuna" value="' . $cbo_comuna . '">';
	echo '<input type="hidden" name="car_fun" value="' . $cbo_cargo . '">';
	echo '<input type="hidden" name="dep_fun" value="' . $Cbo_Procedencia. '">';
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
