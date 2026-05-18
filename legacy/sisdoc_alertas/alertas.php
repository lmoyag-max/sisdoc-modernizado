<?php
header('Content-type: application/json; charset=utf-8');

require_once('PHPMailer\class.phpmailer.php');
require 'PHPMailer\PHPMailerAutoload.php';

include("conexion_bd.php");

$query = "EXEC busca_alertas_vigente";
$qq = mssql_query($query,$cn); 

for ($i = 0; $i < mssql_num_rows( $qq ); ++$i)
{
	$data = mssql_fetch_row($qq);
	
	$id_seguimiento 	= $data[1];
	$fecha_sistema 		= $data[3];
	$dias_compromiso 	= $data[4];
	$dias_transcurridos = $data[5];
	$dias_restantes 	= $data[6];
	$observaciones 		= $data[7];
	$email 				= $data[8];
	
	if($dias_restantes <= -1) {
		$dias_restantes = "PLAZO NO CUMPLIDO!";
	} else if ($dias_restantes == 0) {
		$dias_restantes = "HOY!";
	}

	$mensaje = '
	<style type="text/css">
		p.contenido
		{
			font-family: verdana,arial,sans-serif;
			font-size:12px;
			color: #669;
			border-top: 1px solid transparent;
		}
		
		#texto
		{
			font-family: verdana,arial,sans-serif;
			font-size:15px;
			color: #669;
			border-top: 1px solid transparent;
		}

		#pie
		{
			font-family: verdana,arial,sans-serif;
			font-size:10px;
			color: #669;
			border-top: 1px solid transparent;
		}
	</style>
	<br>
	<center>
		<h3 id="texto">Estimado(a), se informa la siguiente situacion por atender:</h3><br>

		<h3 id="texto">Nro. Seguimiento: '.$id_seguimiento.'</h3><br>
		<h3 id="texto">Descripcion: '.$observaciones.'</h3><br>
		<h3 id="texto">Fecha de solicitud: '.$fecha_sistema.'</h3><br>
		<h3 id="texto">Plazo: '.$dias_compromiso.' dias.</h3><br>
		<h3 id="texto">Tiempo restante: '.$dias_restantes.'.</h3><br><br>
			<h4 id="pie">
				Importante: Este correo ha sido generado de manera automatica, por favor no responda.
			</h4>
	</center>';
	
	$mail = new PHPMailer(); 
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->Host = "mail.minsal.cl";
	$mail->Port = 25;
	$mail->IsHTML(true);
	$mail->Username = "sisdoc.huap@redsalud.gob.cl";
	$mail->Password = "sh.123456";
	
	$mail->setFrom('sisdoc.huap@redsalud.gob.cl', 'SISDOC');
	$mail->AddAddress($email);
	//$mail->AddAddress("nicolas.carcamo@redsalud.gob.cl");
	$mail->Subject = 'SISDOC - Atencion '.$id_seguimiento.' ';
	$mail->Body = $mensaje;
	
	if(!$mail->send()) {
		echo "Error. ";	
		echo $mail->ErrorInfo;
	} else {
		echo "Correo enviado correctamente.";
	}
	
}
	 
?>