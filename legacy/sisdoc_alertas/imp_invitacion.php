<?php
include("variables.php");
include("conexion_bd.php");
$Usuario=$cusuario;
$xx=$idusuario;
$fun=$idfuncionario;
   
?>
<html>
<head>
<META Http-Equiv="Cache-Control" Content="no-cache">
<META Http-Equiv="Pragma" Content="no-cache">
<META Http-Equiv="Expires" Content="0">

<title>Imprime Invitaciones</title>

 <script language="JavaScript" type="text/javascript">
 </script>
<style>div.break {page-break-before:always}</style> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="/css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#FFFFFF" topmargin="0">

<table width="611" height="37" border="0" align="center" cellpadding="1" cellspacing="0" bgcolor="#FFFFFF">
  <tr bgcolor="#6699FF"> 
    <td width="179" height="31"><font color="#0000FF">&nbsp;</font></strong></div></font></td>
    <td width="278" bordercolor="#FFFFFF"><div align="center"><font color="#FFFFFF"><strong>INVITACIONES</strong></font></div></td>
    <td width="144"> 
      <div align="right"><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font size="2"><? echo "Usuario : " . $cusuario?></font></strong></font></strong></font></strong></font></strong></font></div></td>
  </tr>
</table>
<table width="695" height="26" border="0" align="center">
  <tr> 
    <td width="669" height="22" align="right" ><strong><font color="#0000FF"><strong></strong></font><font color="#000000" > 
      </font></strong></td>
  </tr>
</table>
<table align="center" width="631" height="38" border="0">
  <tr> 
    <td width="170" height="34">
<div align="right"><strong><font size="+1">Fecha 
        Inicio</font></strong></div></td>
    <td width="131"><strong><font color="#000000" > <?php echo $fecha_ini; ?> 
      </font></strong></td>
    <td width="133"><strong><font size="+1">Fecha Termino</font></strong></td>
    <td width="107"><strong><font color="#000000" > <?php echo $fecha_fin ?> </font></strong></td>
    <td width="68"><div align="right"><strong><font color="#000000" ><?php echo '<a href="buscainvitacion.php?cusuario=' . $cusuario .
			'&idusuario=' . $xx . '&idfuncionario=' . $fun .  '&sw_fecha=' . 0 .
			'">Volver</a>'; ?></font></strong></div></td>
  </tr>
</table>
<table width="652" align="center" border="1" cellspacing="1" cellpadding="1">
  <tr bgcolor="#3399FF"><font face="Arial, Helvetica, sans-serif"></font> 
    <td width="54" height="28" valign="top"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>NºInterno</strong></font></div></td>
    <td width="79" valign="top" heigth="45"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>NºOficial</strong></font></div></td>
	<td width="73" valign="top" heigth="45"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>NºExterno</strong></font></div></td>
    <td width="101" valign="top" heigth="45"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>Tipo Documento</strong></font></div></td>
    <td width="128" valign="top" heigth="45"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>Materia</strong></font></div></td>
    <td width="106" valign="top" heigth="45"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>Procedencia</strong></font></div></td>
    <td width="73" valign="top" heigth="45"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>Fecha Invitación</strong></font></div></td>
  </tr>
</table>
  <?php
// ------------ Busca las invitaciones por fecha_ini y fecha_fin  -------------------
$dia = substr($fecha_ini,0,2);
$mes = substr($fecha_ini,3,2);
$año = substr($fecha_ini,6,4);
$fechaini = date("Y/m/d H:i", mktime(0,0,0, $mes, $dia, $año));
$dia = substr($fecha_fin,0,2);
$mes = substr($fecha_fin,3,2);
$año = substr($fecha_fin,6,4);
$fechafin = date("Y/m/d H:i", mktime(23,59,59, $mes, $dia, $año));
$rs_doc="exec buscainvitacion '"  . $xx . "','" . $fechaini . "','" . $fechafin . "'";
$reg_doc=mssql_query($rs_doc);
$r =mssql_num_rows($reg_doc);
$cont=0;
 echo '<table width="655" align="center" border="1" cellspacing="1" cellpadding="0" bgcolor="#E6EEFF">';
while($reg_invitacion=mssql_fetch_array($reg_doc))
{
$cont=$cont + 1;
if($cont ==5){
echo '</table>';
echo '<div class="break"/>';
echo '<br>';
echo '<table width="655" align="center" border="1" cellspacing="1" cellpadding="0" bgcolor="#3399FF">';
echo '<tr>';
echo '<td height="35" valign="top" width="57"><font size="2" color="#FFFFFF"><strong>Nº Interno</strong></font></td>';
echo '<td heigth="72" valign="top" width="67"><font size="2" color="#FFFFFF"><strong>NºOficial</strong></font></td>';
echo '<td heigth="55" valign="top" width="68"><font size="2" color="#FFFFFF"><strong>NºExterno</strong></font></td>';
echo '<td heigth="30" valign="top" width="91"><font size="2" color="#FFFFFF"><strong>Tipo Documento</strong></font></td>';
echo '<td heigth="45" valign="top" width="113"><font size="2" color="#FFFFFF"><strong>Materia</strong></font></td>';
echo '<td heigth="45" valign="top" width="94"><font size="2" color="#FFFFFF"><strong>Procedencia</strong></font></td>';
echo '<td heigth="47" valign="top" width="70"><font size="2" color="#FFFFFF"><strong>Fecha Invitación</strong></font></td>';
echo '</tr>';
echo '</table>';

echo '<table width="655" align="center" border="1" cellspacing="1" cellpadding="0" bgcolor="#E6EEFF">';
$cont=0;
}
?> 

  <tr> 
  <td align="left" valign="middle" width="8%"><font size="2">
          <?php echo $reg_invitacion["num_interno"];?></font>
        </td>
        <td align="left" valign="middle" width="10%"><font size="2">
          <?php echo $reg_invitacion["num_oficial"];?></font>
        </td>
		<td align="left" valign="middle" width="10%"><font size="2">
          <?php echo $reg_invitacion["num_externo"];?></font>
        </td>
  <td align="left" valign="middle" width="13%"><font size="2"><?php echo $reg_invitacion["desc_tipo_documento"];?> </font> </td>
		
      <td align="left" valign="middle" width="16%"><font size="2"> 
        <?php if ($reg_invitacion["materia"]=="")
		           echo "&nbsp";
				   else echo $reg_invitacion["materia"];?>
        </font> </td>
       
        <td align="left" valign="middle" width="14%"><font size="2">
          <?php echo $reg_invitacion["procedencia"];?></font>
        </td>
    <td width="10%"  valign="middle"><font size="2"><? $fec_doc=strtotime($reg_invitacion["fecha_invitacion"]);
			   $fech_doc = date("d/m/Y",$fec_doc);
			   echo $fech_doc;?></font></td>
    </tr>
   
  
  <?
  	}
	mssql_close($cn);
  	?>
</table>

<table width="653" height="51" border ="0" align="center" cellpadding="0"  cellspacing="0">
  <tr> 
 <td width="653" height="24"> <div align="center"> 
        <input name="cmd_aceptar" type="button" class="botones" onClick="javascript:window.print();" value="Imprimir">
  </div>
  <strong></strong></td>
</tr>
</table>
</body>
</html>
