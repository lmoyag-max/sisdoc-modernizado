<?php
include("variables.php");
include("conexion_bd.php");
$Usuario=$cusuario;
$xx=$idusuario;
$fun=$idfuncionario;
 
if ($Cbo_Procedencia > 0){
	if ($tipo_procedencia=="I"){
	  $rs_proc = mssql_query("select (desc_dependencia)descproc from dependencia  where id_dependencia =$Cbo_Procedencia",$cn);
	
	}
	else{
		  $rs_proc = mssql_query("select (desc_dependencia_externa)descproc from dependencia_externa  where id_dependencia_externa =$Cbo_Procedencia",$cn);
	}
	$reg_proc = mssql_fetch_array($rs_proc);
}
?>


<html>
<head>
<META Http-Equiv="Cache-Control" Content="no-cache">
<META Http-Equiv="Pragma" Content="no-cache">
<META Http-Equiv="Expires" Content="0">

<title>Imprime Pendientes</title>

 <script language="JavaScript" type="text/javascript">
 </script>
<style>div.break {page-break-before:always}</style> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="/css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#FFFFFF" topmargin="0">

<table width="640" height="39" border="0" align="center" cellpadding="1" cellspacing="0" bgcolor="#FFFFFF">
  <tr bgcolor="#6699FF"> 
    <td width="192" height="31"><font color="#0000FF">&nbsp;</font></strong></div></font></td>
    <td width="299" bordercolor="#FFFFFF"><div align="center"><font color="#FFFFFF"><strong>FACTURAS
        PENDIENTES </strong></font></div></td>
    <td width="154"> 
      <div align="right"><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font size="2"><? echo "Usuario : " . $cusuario?></font></strong></font></strong></font></strong></font></strong></font></div></td>
  </tr>
</table>
<strong></strong> 
<table align="center" width="639" height="35" border="0">
  <tr> 
    <td width="216" height="31"> 
      <div align="right">Fecha Inicio</div></td>
    <td width="108"><strong><font color="#000000" ><?php echo $fecha_ini; ?> </font></strong></td>
    <td width="101">Fecha Termino</td>
    <td width="104"><strong><font color="#000000" > <?php echo $fecha_fin ?> </font></strong></td>
    <td width="88"><div align="right"><strong><font color="#000000" ><?php echo '<a href="busca_pend_fact.php?cusuario=' . $cusuario .
			'&idusuario=' . $xx . '&idfuncionario=' . $fun .  '&sw_fecha=' . 0 .
			'">Volver</a>'; ?></font></strong></div></td>
  </tr>
</table>

<?php
// ------------ Busca documentos pendientes por fecha_ini y fecha_fin  , busca los socumentos no recepcionados y los que no han tenido respuesta desde el destino -------------------
$dia = substr($fecha_ini,0,2);
$mes = substr($fecha_ini,3,2);
$año = substr($fecha_ini,6,4);
$fechaini = date("Y/m/d H:i", mktime(0,0,0, $mes, $dia, $año));
$dia = substr($fecha_fin,0,2);
$mes = substr($fecha_fin,3,2);
$año = substr($fecha_fin,6,4);
$fechafin = date("Y/m/d H:i", mktime(23,59,59, $mes, $dia, $año));

//$consulta ="exec busca_facturas_pendientes  " .  $Cbo_Procedencia . "," . $Cbo_Destinatario .  ", '" .$fechaini ."' , '" .$fechafin . "'" ;
$consulta ="exec busca_facturas_pendientes_2  " .  $Cbo_Procedencia . "," . $Cbo_Destinatario .  ", '" .$fechaini ."' , '" .$fechafin . "'" ;


$reg_doc = mssql_query($consulta);				
$r =mssql_num_rows($reg_doc);
if ($r ==0)
{
echo '<script>';
echo 'alert("No Existen Documentos")';
echo '</script>'; 
}



$cont=0;
 //echo '<table width="780" align="center" border="1" cellspacing="1" cellpadding="0" bgcolor="#E6EEFF">';
while($reg_pendientes=mssql_fetch_array($reg_doc) and $cont < 5)
{
  $cont=$cont + 1;
  if($cont ==4)
 { 
      echo '<div class="break"/>';
      $cont=1;
  }
?>
<table width="646" border="1" align="center" height="100">
  <tr> 
    <td width="642" height="206" bgcolor="#9CCBED"> 
       
      <table width="100%"   border="1" cellpadding="2" cellspacing="2" bgcolor="#C3D6E6">
       <td align="right"><?php echo '<a href="tramites.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . 
	  	'&iddocum=' . $reg_pendientes["id_documento"] . '&idseguim=' . $reg_pendientes["id_seguimiento"] .
		'&idfuncionario=' . $idfuncionario . '">Ver trámites</a>' ;?> 
		</td></table>
		<table width="100%" border="0" cellpadding="2" cellspacing="2" bgcolor="#C3D6E6">
        <tr>
          <td width="101"><strong>Tipo de Docto</strong></td>
          <td width="150"><font size="2"><?php  echo  $reg_pendientes["desc_tipo_documento"];?></font></td>
          <td width="82"><b>Fec. Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></td>
          <td width="52"> <font size="2"> 
            <?php  $fecdc = strtotime($reg_pendientes["fecha_documento"]);
	           $fecdc=date("d/m/Y",$fecdc);
		        echo $fecdc;?>
            </font> </td>
         <td width="108"><b>Fec. Despacho</b></td>
          <td width="105"><font size="2">
            <? $fec_doc=strtotime($reg_pendientes["fecha_despacho"]);
	 				  $fech_doc = date("d/m/Y",$fec_doc);
	   				  echo $fech_doc;?>
            </font> </td>
        </tr>
        
      </table>
      <table width="100%" border="0" align="center" cellpadding="1" cellspacing="0" bgcolor="#C3D6E6">
        <tr valign="middle"> 
          <td width="121"><b><i>Num. Interno<font size="4" face="Arial"> </font></i></b></td>
          <td width="96"> <font size="2"><?php echo $reg_pendientes["num_interno"];?></font> </td>
          <td width="98"><b><i> Num. Oficial</i></b><font size="4" face="Arial">&nbsp; </font></td>
          <td width="95"> <font size="2"><?php echo $reg_pendientes["num_oficial"];?></font></td>
          <td width="126"><b><i>Num. Externo<font size="4" face="Arial"> </font></i></b></td>
          <td width="133"> <font size="2"><?php echo $reg_pendientes["num_externo"];?></font></td>
        </tr>
      </table>
      <table width="100%" border="0" cellpadding="2" cellspacing="2" bgcolor="#C3D6E6">
        <tr> 
          <td width="18%"><strong>Procedencia</strong></td>
            <td width="26%"><font size="2">
              <?php if ($reg_pendientes["procedencia"]=="")
        	echo "&nbsp";
   		else echo $reg_pendientes["procedencia"];?>
              </font> </td>
          <td width="19%"><b>Funcionario<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></td>
          <td width="37%"> <font size="2"> 
            <?php if ($reg_pendientes["funcprocedencia"]=="")
        	echo "&nbsp";
   		else echo $reg_pendientes["funcprocedencia"];?>
            </font> </td>
        </tr>
        <tr> 
          <td><strong>Destinatario</strong></td>
          <td><font size="2">
            <?php if ($reg_pendientes["destino"]=="")
        	echo "&nbsp";
   		else echo $reg_pendientes["destino"];?>
            </font> </td>
          <td><b>Funcionario<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></td>
          <td> <font size="2">
            <?php if ($reg_pendientes["funcdestino"]=="")
        	echo "&nbsp";
   		else echo $reg_pendientes["funcdestino"];?>
            </font> </td>
        </tr>
      </table>
      <table width="100%" border="1" cellpadding="1" cellspacing="0" bgcolor="#C3D6E6">
        <tr> 
          <td width="16%"><b>Materia</b></td>
          <td width="84%" height="60" valign="top"> <font size="2"> 
            <?php if ($reg_pendientes["materia"]=="")
        	echo "&nbsp";
   		else echo $reg_pendientes["materia"];?>
            </font></td>
        </tr>
        
      </table>
       
      <p>&nbsp;</p></td>
  </tr>
</table>
</tr>
<?
   }
  mssql_close($cn);
?>
<table width="664" height="60" border ="0" align="center" cellpadding="0"  cellspacing="0">
  <tr> 
    <td width="662" height="60"> <div align="center"> 
        <input name="cmd_aceptar" type="button" class="botones" onClick="javascript:window.print();" value="Imprimir">
      </div>
      <strong></strong></td>
  </tr>
</table>
</body>
</html>
