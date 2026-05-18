<?php
include("conexion_bd.php");
include("carga_tablas.php");
$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;
$id_dependencia =$id_dependencia;
$id_tema =$id_tema ;

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
<form name="form1" method="Post" action="doc_alerta_facturas.php">
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
        <td width="643" height="217"  align="center"> <table width="76%" height="25" border="0" cellpadding="1" cellspacing="1">
            <tr> 
              <td width="244" class="texto"><strong><font  color="#000000">Tipo 
                Factura </font><font color="#000000"> 
                <select name="Cbo_tipo_facturas" class="combo" id="Cbo_tipo_facturas" >
                  <option value="0" title="-----"> </option>
                  <?
						   while($reg=mssql_fetch_array($rs_tipo_factura)){
							?>
                  <option value=<? echo $reg[id_tipo_fact] ?> ><? echo $reg[desc_tipofactura] ?></option>
                  <?
							}
						  ?>
                </select>
                </font></strong></td>
              <td width="244" class="texto"><strong>Tema Factura</strong> <strong><font color="#000000"> 
                <select name="Cbo_tema_facturas" class="combo" id="Cbo_tema_factura" >
                  <?
						   while($reg2=mssql_fetch_array($rs_tema_factura)){
							?>
                  <option value=<? echo $reg2[id_tema] ?> ><? echo $reg2[desc_tema] ?></option>
                  <?
							}
						  ?>
                </select>
                </font></strong></td>
            </tr>
            <tr> 
              <td colspan="2" class="texto"><strong><font color="#804040" face="Arial, Helvetica, sans-serif">seleccione 
                el estado de facturas que se quieren imprimir</font></strong></td>
            </tr>
          </table>
          <table width="76%" height="54" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <!--td width="149" height="52" align="center"> <div align="left"><strong>Alerta 
                  en Verde 
                  <input type="radio" name="tipo_alerta" value="radiobutton" onClick="javascript:document.form1.tipo.value=1;">
                  </strong> </div></td-->
              <td width="156" align="center"><strong>Alerta en Amarillo 
                <input type="radio" name="tipo_alerta" value="radiobutton" onClick="javascript:document.form1.tipo.value=2;">
                </strong></td>
              <td width="212" align="center"><div align="left"><strong>Alerta 
                  en Rojo 
                  <input type="radio" name="tipo_alerta" value="radiobutton" onClick="javascript:document.form1.tipo.value=3;" checked="true">
                  </strong></div></td>
            </tr>
          </table>
          <p>&nbsp;</p>
          <table width="76%" border="0">
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
	<input type="hidden" name="id_tema" value ="<? echo $id_tema ;?>" >
	<input type="hidden" name="cusuario" value = "<? echo $cusuario;?>" >
	<input type="hidden" name="id_funcionario" value ="<? echo $id_funcionario ;?>" >
  </form>
  
<?php mssql_close($cn);?>
</center>
</body>
</html>
