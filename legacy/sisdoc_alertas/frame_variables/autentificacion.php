<html> 
<head> 
<title>Autentificación Usuario</title> 
</head> 
<body> 
<h1>&nbsp;</h1> 

<form action="control.php" method="POST">
  <table width="500" border="2" align="center" cellpadding="2" cellspacing="2">
    <tr>
      <td><table width="400" border="0" align="center" cellpadding="2" cellspacing="0">
          <tr> 
            <td><div align="center"><strong><font color="#0000A0">SEGUIMIENTO 
                DE DOCUMENTO</font></strong></div></td>
          </tr>
          <tr> 
            <td><div align="center"><strong><font face="Arial"><img src="imagen/logo_chico.gif" alt="logo_chico.gif (2274 bytes)" WIDTH="95" HEIGHT="87"></font></strong></div></td>
          </tr>
        </table></td>
    </tr>
  </table>
  <table width="500" border="2" align="center" cellpadding="2" cellspacing="2">
    <tr> 
      <td><table align="center" width="400" cellspacing="2" cellpadding="2" border="0">
          <tr> 
            <td colspan="2" align="center" 
			<? if ($errorusuario=="si"){?>

bgcolor=red><span style="color:ffffff"><b>Datos incorrectos</b></span> 
              <? }else{?>
              bgcolor=#cccccc><strong>Ingrese su clave de acceso</strong> 
              <? }?>
            </td>
          </tr>
          <tr> 
            <td align="right">Usuario:</td>
            <td width="200"><input type="Text" name="usuario" size="10" maxlength="10" value="ximena"></td>
          </tr>
          <tr> 
            <td align="right">Password:</td>
            <td><input type="password" name="contrasena" size="10" maxlength="10" value="x"></td>
          </tr>
          <tr> 
            <td colspan="2" align="center"><input name="Submit" type="Submit" value="ENTRAR"></td>
          </tr>
        </table></td>
    </tr>
  </table>
  <p>&nbsp;</p>
  </form> 
</body> 
</html> 
