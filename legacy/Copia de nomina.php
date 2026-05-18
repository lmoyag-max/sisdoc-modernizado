<?php
include("variables.php");
include("conexion_bd.php");
//include("funciones.php");
$Usuario=$cusuario;
$xx=$idusuario;
$nomina=$idnomina;
$fechasistema = date("Y/m/d H:i");      
?>
<html>
<head>
<title>Nómina de Despacho de Documentos</title>

 <script language="JavaScript" type="text/javascript">
function aceptar() 
{

location.href="cambia_estado3.php?variable1=<?=$qr[id_documento]?>&variable2=<?=$qr[desc_dependencia]?>&variable3=<?=$qr[id_destino]?>&variable4=<?=$qr[desc_estado_documento]?>";
}
 </script>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

</head>
<body>
<?php
	
 $rs_proc="select usuario.id_funcionario,dependencia.desc_dependencia
   from usuario,dependencia, funcionario
   where  usuario.id_usuario= $xx
   and funcionario.id_funcionario = usuario.id_funcionario 
   and funcionario.id_dependencia=dependencia.id_dependencia ";
   $qp=mssql_query($rs_proc);
   $rs=mssql_fetch_array($qp);
  
?>  
<table width="741" border ="1"  cellspacing="0" cellpadding="0" bgcolor="#FFFFCC">
  <tr> 
    <td width="737 height="28" align="center" valign="top">
	<table width="733" border="0" cellspacing="1" cellpadding="1">
        <tr>
          <td><div align="right"><font color="#0000A0" size="2">Martes 1 Abril 2003</font></div></td>
        </tr>
        <tr>
          <td><div align="center"><font color="#0000A0" size="4">NOMINA DE DOCUMENTOS 
              DESPACHADOS</font></div></td>
        </tr>
        <tr>
          <td><div align="right"><strong><font color="#0000A0" size="3"><? echo "Usuario : " . $Usuario?></font></strong></div></td>
        </tr>
      </table></td>
  </tr>
 </table>
   
   <table width="737" border ="1"  cellspacing="0" cellpadding="0" bgcolor="#FFFFCC">
  <tr> 
    <td height="28" align="center" valign="top">
	<table width="735" border="0" cellspacing="1" cellpadding="1">
        <tr>
          <td width="731"><div align="center"><strong><font color="#0000A0" size="3"><? echo "Procedencia : " . $rs[desc_dependencia]?></font></strong></td>
        </tr>
        <tr>
          <td><div align="center"><font color="#0000A0" size="3"><? echo "NOMINA : " . $nomina?></font></div></td>
        </tr>
        
      </table>
 <table width="737" border="2" cellspacing="0" cellpadding="0" bgcolor="#FFFFCC">
  <tr> 
    <td width="61" height="29" valign="top" bgcolor="#FFFFCC" heigth="45" border = "2"><strong><font size="2">Nº 
      Interno </font></font></strong></td>
    <td heigth="45" valign="top" width="55"><strong><font size="2">NºOficial
      </font></strong></td>
    <td heigth="45" valign="top" width="102"><font size="2"><strong>Tipo Documento</strong></font></td>
    <td heigth="45" valign="top" width="261"><font size="2"><strong>Materia</strong></font></td>
    <td heigth="45" valign="top" width="164"><font size="2"><strong>Destinatario</strong></font></td>
    <td heigth="45" valign="top" width="78"><font size="2"><strong>Fecha Docto.</strong></font></td>
  </tr>
  <?php
	
$rs_recep="exec busca_nomina_despacho '" . $xx .  "','" . $nomina . "'";

$qq=mssql_query($rs_recep);
while($qr=mssql_fetch_array($qq))
{
?> 
  <tr> 
    <td width="61"  valign="top"><? echo $qr[num_interno];?></td>
    <td width="55"  valign="top"><? echo $qr[num_oficial];?></td>
    <td width="102" valign="top"><? echo $qr[desc_tipo_documento];?></td>
    <td width="261" valign="top"><?php if ($qr["materia"]=="")
		           echo "&nbsp";
				   else echo $qr["materia"];?></font>
    <td width="164" valign="top"><? echo $qr[destino]?></td>
    <td width="78"  valign="top"><? $fec_doc=strtotime($qr[fecha_documento]);
			   $fech_doc = date("d/m/Y",$fec_doc);
			   echo $fech_doc;?></td>
    </tr>
   
  <tr> 
    <td width="61"  height="2"></td>
    <td width="55"  height="2"></td>
    <td width="102" height="2"></td>
    <td width="261" height="2"></td>
    <td width="164" height="2"></td>
    <td width="78"  height="2"></td>
  </tr>
  <?
  	}
  	?>
</table>
    <table width="737" border ="1"  cellspacing="0" cellpadding="0" bgcolor="#FFFFCC" height="51">
      <tr> 
        <td height="24"> <div align="center"> 
            <input type="button" name="cmd_aceptar" value="Imprimir" onClick="aceptar();">
          </div>
          <strong></strong></td>
      </tr>
    </table>
</body>
</html>
