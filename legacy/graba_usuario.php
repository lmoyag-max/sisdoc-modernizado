<?
require("class.phpmailer.php"); 
$aux_rut=substr($el_rut,0,-1);
      
   if(strlen($aux_rut)>8) 
   {
    $aux_rut = substr($aux_rut,0,-1);
   }
   
   $aux_rut=str_replace("-","",$aux_rut);
    $aux_rut=str_replace(".","",$aux_rut);
	$largo=strlen($el_rut);
	$dv=substr($el_rut,$largo-1);
  
// buscando  si rut si ya existe 
include ("conex_usuario.php");
$ok =0;
$qrut="select rut_fun from funcionario where rut_fun=". $aux_rut;
$rr=mssql_query($qrut,$cn);
$trut=mssql_num_rows($rr);
if ($trut == 0)
{
$op='I';   /// graba en corporativo en tabla funcionario 
$query="exec  ingreso_usuario'". $aux_rut."','". $dv."','".$nombre."','".$ap_pat_fun."','".$ap_mat_fun."','".$sexox."','".$correo."','".$cbo_dependencia."','".$vigenciax."','".$clave."','".$op . "'";
$result=mssql_query($query);
if ($Ret==0)
   $ok=1;
 if ($ok==1 )  
    {   include("conexion_bd.php");  // graba en el sisdoc en tabla funcionario, usuario,acceso 
	    $query2="exec  ingreso_usuario_funcionario'" .$aux_rut."','".$dv."','".$nombre."','".$ap_pat_fun."','".$ap_mat_fun."','".$sexox."','".$cbo_dependencia."','".$vigenciax."','".$clave."','".$op. "'"; 
		$result2=mssql_query($query2);
		if ($Ret==0)
		   $ok=1;
    }   

   
}   
else 
 {    if ($vigenciax=='S')
           $vigenciax='NULL';
		    echo "vigencia" . $vigenciax;
      $op='M';
      $query="exec  ingreso_usuario_funcionario'". $aux_rut."','". $dv."','".$nombre."','".$ap_pat_fun."','".$ap_mat_fun."','".$sexox."','".$correo."','".$cbo_dependencia."','".$vigenciax."','".$clave ."','".$op . "'";
      $result=mssql_query($query);
      if ($Ret==0)
         $ok="3";
	   if ($ok==3 )  
	    {   include("conexion_bd.php");  // graba en el sisdoc en tabla funcionario, usuario,acceso 
	    $query2="exec  modifica_usuario_funcionario'" .$aux_rut."','".$dv."','".$nombre."','".$ap_pat_fun."','".$ap_mat_fun."','".$sexox."','".$cbo_dependencia."','".$vigenciax."','".$clave."','".$op. "'"; 
		$result2=mssql_query($query2);
		if ($Ret==0)
		   $ok=1;
      }   	 
     
 }

if ($ok==1 && $op=='I')
{
 // mandando correo a usuario 
$nom =$nombre . ' ' . $ap_pat_fun ;
$mail = new phpmailer(); 

$mail->IsSMTP();  // le indica al Linux usar SMTP 
$mail->Host = "webmail.redsalud.gov.cl";  // servidor donde está el SMTP (SENDMAIL) 
$mail->SMTPAuth = false;     // no necesitamos autenticación SMTP 

$mail->From =  "admin.ssmc@redsalud.gov.cl"; 
$mail->FromName = "Administrador sisdoc"; //El nombre de quien envía 
$mail->AddAddress($correo,$nom); //A quien Dirijo el mail 
//$mail->AddAddress('kiter@minsal.cl','karina'); //A quien Dirijo el mail 

$mail->WordWrap = 50;                                 //  salto de linea a los 50 caracteres 
$mail->IsHTML(true);                                  // configura el email con formato HTML 

$mail->Subject = "Acceso al sisdoc"; 
$valor=5;
$cont=5;

$mail->Body    =

"<html>\n" .
              "<head>\n" .
               "<title>Acceso al Sisdoc</title>\n" .
              "</head>\n" .
              "<body bgcolor='#F4F4F4' text='#000000'>" . "\n" .
       	       "<center>" . "\n" .
     	        " Estimado(a)   : " . $nom . "<br>" .  "\n" .
     	       "</center>". "\n" .
                "<hr></hr>" . "\n" .           
     	        "<br>\n" .
                " Usted  ha sido ingresado como usuario al Sistema de Correspondencia con la clave " . $clave . "\n" . 
     		"<br><br>" . "\n" .
                "Atentamente," . "<br>" . 
			    "Administrador del Sistema de correspondencia" .  "\n" .
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
else 
 	{
	echo '<script>';
	echo 'alert("Ha sido enviado un correo al usuario , informando su acceso y clave de ingreso")';
	echo '</script>';
 	echo " ";
	}
}



/// fin correo 
echo '<html><body onload="document.form1.submit();">';
echo '<form name="form1" method="post" action="ingreso_usuarios.php">';
echo '<input type="hidden" name="ok_graba" value="' . $ok. '">' . "\n";
echo '<input type="hidden" name="cusuario" value="' . $cusuario . '">' . "\n";
echo '<input type="hidden" name="idusuario" value="' . $idusuario . '">' . "\n";
echo "</form></body></html>";

mssql_close($cn);

?>