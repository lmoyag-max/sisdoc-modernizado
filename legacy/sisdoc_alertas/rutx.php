<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
$fecha_x = date("d-m-Y");

if ($txtusuario=="") 
{
	$sw=0;
	}
	else
	{
	
	$qr = mssql_query("select * from usuario where usuario = '$txtusuario' and  clave = '$txtclave' ");
	$nrows = mssql_num_rows($qr);
	$reg_f5 = mssql_fetch_array($qr);
	$x_usuario=$reg_f5[id_usuario];
	if ($nrows ==0) {
		echo '<blockquote>';
		echo '<div align="center">';
    	echo '<p><b><font color="#000099">Usted no tiene Acceso al Sistema</font></b></p>';
    	echo '</blockquote>';
		$sw=0;
	}
	else
	{
		$cUsuario= $reg_f5[usuario];
		$idUsuario=$reg_f5[id_usuario];
		echo $cUsuario;
		echo $idUsuario;
		$sw=1;?>
		<script language="JavaScript" type="text/javascript">
		   location.href="ingreso_Docto1.php?=<?=$cUsuario?>&<?=$idUsuario?>";
		 //  location.href="ingreso_Docto1.php?usuario_aux="+$cUsuario.value+"&usuario_id="+$idUsuario.value;
 

		   
		   </script>
		   <?
   }
 }
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Documento sin t&iacute;tulo</title>
<script language="JavaScript" type="text/javascript">
function deriva_docto() 
{
if  (document.form1.Cbo_Estado_Docto.value == 2)
	{
	alert("Debe Recepcionar Documento y luego Derivar");
	}
else
  {
   location.href="pp.php"  ;
   }
 }  
function enviar_datos() 
   {
     if(x_usuario == ' ')
	 {
       alert("Debe Ingresar el Usuario");
     }
     else
	 {
	 	if(sw==0){
			location.href="rutx.php"  ;
	 }
   }
 
 </script>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1"
 method="post" 
 action=""
 >
  <table width="443" border="2" cellspacing="1" cellpadding="1">
    <tr> 
      <td width="169">Usuario</td>
      <td width="259"><input name="txtusuario" type="text" id="txtusuario" size="10" maxlength="10"></td>
    </tr>
    <tr> 
      <td>Clave</td>
      <td><input name="txtclave" type="text" id="txtclave" size="10" maxlength="10"></td>
    </tr>
  </table>
  <table width="443" border="2" cellspacing="1" cellpadding="1">
    <tr>
      <td width="433"><div align="center">
          <input type="submit" name="cmd_grabar" value="Validar" onclick="enviar_datos()">
        </div></td>
    </tr>
  </table>
  <p>&nbsp;</p>
</form>
</body>
</html>
