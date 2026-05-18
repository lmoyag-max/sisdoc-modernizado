<?php
include("conexion_bd.php");
include("carga_tablas.php");
$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;
$id_dependencia =$id_dependencia;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario consulta alertas </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/javascript">
</script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#FFFFFF">
<center>
<form name="form1" method="Post" action="doc_alerta.php">
       <font color="#000000" face="Arial, Helvetica, sans-serif"> </font> 
    <table width="650" height="26" border="0">
      <tr> 
        <td width="100%"><div align="right"><strong><font color="#0000A0" size="1"><strong><? echo "Usuario : " . $cusuario?></strong></font></strong></div></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="676"><div align="center"><font color="#FFFFFF" size="4"><strong><? echo "Buscar Documentos en Alerta "; ?></strong></font></div></td>
      </tr>
    </table>
    <table width="649" border="1" cellpadding="1" cellspacing="0" bgcolor="#E6EEFF">
      <tr> 
        <td width="643" height="217"  align="center"> 
          <table width="76%" height="25" border="0" cellpadding="1" cellspacing="1">
            <tr> 
              <td width="489" class="texto"><strong><font color="#804040" face="Arial, Helvetica, sans-serif">seleccione 
                el estado de los documentos que se quieren imprimir</font></strong></td>
            </tr>
          </table>
          <table width="76%" height="54" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="149" height="52" align="center"> 
                <div align="left"><strong>Alerta en Verde 
                <input type="radio" name="tipo_alerta" value="radiobutton" onclick="javascript:document.form1.tipo.value=1;">
                  </strong> </div></td>
              <td width="156" align="center"><strong>Alerta en Amarillo 
                <input type="radio" name="tipo_alerta" value="radiobutton" onclick="javascript:document.form1.tipo.value=2;">
                </strong></td>
              <td width="212" align="center"><div align="left"><strong>Alerta en Rojo 
                  <input type="radio" name="tipo_alerta" value="radiobutton" onclick="javascript:document.form1.tipo.value=3;" checked="true">
                  </strong></div></td>
            </tr>
          </table>
          <p>&nbsp;</p><table width="76%" border="0">
            <tr> 
              <td width="178" height="26"> <div align="center"> </div></td>
              <td width="188"> <div align="center"> 
                  <input type="submit" name="Submit" value="Buscar" >
                </div></td>
              <td width="112">&nbsp;</td>
            </tr>
          </table></td>
      </tr>
    </table>
	<input type="hidden" name="tipo" >
	<input type="hidden" name="id_dependencia" value ="<? echo $id_dependencia ;?>" >
	<input type="hidden" name="cusuario" value = "<? echo $cusuario;?>" >
	<input type="hidden" name="id_funcionario" value ="<? echo $id_funcionario ;?>" >
  </form>
  
<?php mssql_close($cn);?>
</center>
</body>
</html>
