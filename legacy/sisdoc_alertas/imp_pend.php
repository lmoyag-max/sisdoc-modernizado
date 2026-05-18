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

<table width="754" height="39" border="0" align="center" cellpadding="1" cellspacing="0" bgcolor="#FFFFFF">
  <tr bgcolor="#6699FF"> 
    <td width="192" height="31"><font color="#0000FF">&nbsp;</font></strong></div></font></td>
    <td width="299" bordercolor="#FFFFFF"><div align="center"><font color="#FFFFFF"><strong>DOCUMENTOS 
        PENDIENTES </strong></font></div></td>
    <td width="257"> 
      <div align="right"><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font size="2"><? echo "Usuario : " . $cusuario?></font></strong></font></strong></font></strong></font></strong></font></div></td>
  </tr>
</table>
<strong></strong> 
<table align="center" width="754" height="41" border="0">
  <tr> 
    <td width="225" height="37"> 
      <div align="right">Fecha Inicio</div></td>
    <td width="115"><strong><font color="#000000" ><?php echo $fecha_ini; ?> </font></strong></td>
    <td width="107">Fecha Termino</td>
    <td width="111"><strong><font color="#000000" > <?php echo $fecha_fin ?> </font></strong></td>
    <td width="174"><div align="right"><strong><font color="#000000" ><?php echo '<a href="busca_pend.php?cusuario=' . $cusuario .
			'&idusuario=' . $xx . '&idfuncionario=' . $fun .  '&sw_fecha=' . 0 .
			'">Volver</a>'; ?></font></strong></div></td>
  </tr>
</table>

<table width="760" align="center" border="1" cellspacing="1" cellpadding="0">
  <tr bgcolor="#3399FF">
  <!--font face="Arial, Helvetica, sans-serif"></font--> 
    <td width="55" height="36" valign="top"> 
      <div align="center"><font color="#FFFFFF" size="2">
	<strong>NºInterno</strong></font></div></td>
	<td width="53" valign="top" heigth="72"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>NºOficial</strong></font></div></td>
    <td width="57" valign="top" heigth="55"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>NºExterno</strong></font></div></td>
    <td width="64" valign="top" heigth="30"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>Tipo Documento</strong></font></div></td>
    <td width="71" valign="top" heigth="45"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>Materia</strong></font></div></td>
    <td width="61" valign="top" heigth="45"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Fecha Documento</strong></font></div></td>
    <td width="70" valign="top" heigth="45"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Procedencia</strong></font></div></td>
    <td width="69" valign="top" heigth="45"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Funcionario</strong></font></div></td>
		<td width="71" valign="top" heigth="45"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Destinatario</strong></font></div></td>
    <td width="72" valign="top" heigth="45"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Funcionario Destino</strong></font></div></td>
    <td width="71" valign="top" heigth="47"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Fecha Despacho</strong></font></div></td>
  </tr>
<!-- </table> -->
<?php
// ------------ Busca documentos pendientes por fecha_ini y fecha_fin  -------------------
$dia = substr($fecha_ini,0,2);
$mes = substr($fecha_ini,3,2);
$año = substr($fecha_ini,6,4);
$fechaini = date("Y/m/d H:i", mktime(0,0,0, $mes, $dia, $año));
$dia = substr($fecha_fin,0,2);
$mes = substr($fecha_fin,3,2);
$año = substr($fecha_fin,6,4);
$fechafin = date("Y/m/d H:i", mktime(23,59,59, $mes, $dia, $año));

$consulta ="select a.id_documento,a.num_interno,a.num_oficial,a.num_externo,  
        f.desc_tipo_documento,a.materia,a.fecha_documento,b.fecha_despacho,          
          b.id_seguimiento,b.id_procedencia,b.id_destino,    
       procedencia=             
       case  b.tipo_procedencia             
          when 'I' then             
               (select desc_dependencia from dependencia where  b.id_procedencia=id_dependencia)            
          else            
               (select desc_dependencia_externa  from dependencia_externa where  b.id_procedencia=id_dependencia_externa)            
          end,
       funcprocedencia=
          case when b.rut_procedencia =' ' or b.rut_procedencia='0' then     ''    
             else 
		(select max(rtrim(funcionario.nombres)+ ' ' + rtrim(funcionario.apellidos))
		from funcionario where 
		b.rut_procedencia =funcionario.rut )
       End,
	   destino=           
       case  b.tipo_destinatario    
          when 'I' then             
               (select desc_dependencia from dependencia where  b.id_destino=id_dependencia)            
          else            
               (select desc_dependencia_externa  from dependencia_externa where  b.id_destino=id_dependencia_externa)            
          end,
       funcdestino=
          case when b.rut_destino =' ' or b.rut_destino='0' then     ''    
             else 
		(select max(rtrim(funcionario.nombres)+ ' ' + rtrim(funcionario.apellidos))
		from funcionario where 
		b.rut_destino =funcionario.rut )
       End             
       
          from documento  a,  tramite b,  tipo_documento f         
          where  a.id_documento= b.id_documento   
                     
                and  f.id_tipo_documento= a.id_tipo_documento            
                and  b.id_estado_compromiso = 1
				 and (b.fecha_despacho between '$fechaini' and '$fechafin')";

if ($Cbo_Procedencia==0)
{ $Cbo_proc="";   }
else{
   $Cbo_proc=" and b.id_procedencia=" . $Cbo_Procedencia;
   $consulta =$consulta . $Cbo_proc;}
   		
if ($Cbo_Func_Procedencia==0)
{ $Cbo_fproc="";   }
else{
   $Cbo_fproc=" and b.rut_procedencia=" . $Cbo_Func_Procedencia;
   $consulta =$consulta . $Cbo_fproc;}


if ($Cbo_Destinatario==0)
{ $Cbo_dest="";   }
else{
   $Cbo_dest=" and b.id_destino=" . $Cbo_Destinatario;
   $consulta =$consulta . $Cbo_dest;}
   		
if ($Cbo_Func_Destino==0)
{ $Cbo_fdest="";   }
else{
   $Cbo_fdest=" and b.rut_destino=" . $Cbo_Func_Destino;
   $consulta =$consulta . $Cbo_fdest;}
   
$orden =" order by b.fecha_despacho,f.desc_tipo_documento   ";

$consulta = $consulta .  $orden;   
			
				

$reg_doc = mssql_query($consulta);				
//$rs_doc="exec buscapendientes'"  . $fechaini . "','" . $fechafin . "'";
//$reg_doc=mssql_query($rs_doc);
$r =mssql_num_rows($reg_doc);

$cont=0;
 //echo '<table width="780" align="center" border="1" cellspacing="1" cellpadding="0" bgcolor="#E6EEFF">';
while($reg_pendientes=mssql_fetch_array($reg_doc))
{
$cont=$cont + 1;
if($cont ==5){
echo '</table>';
echo '<div class="break"/>';
echo '<br>';

echo '<table width="760" align="center" border="1" cellspacing="1" cellpadding="0" >';

echo '<tr bgcolor="#3399FF">';
echo '<td height="35" valign="top" width="59"><div align="center"><font color="#FFFFFF" size="2">
	<strong>NºInterno</strong></font></div></td>';
echo '<td heigth="72" valign="top" width="63"><div align="center"><font color="#FFFFFF" size="2">
	<strong>NºOficial</strong></font></div></td>';
echo '<td heigth="55" valign="top" width="66"><div align="center"><font color="#FFFFFF" size="2">
	<strong>NºExterno</strong></font></div></td>';
echo '<td heigth="30" valign="top" width="80"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Tipo Documento</strong></font></div></td>';
 echo '<td heigth="45" valign="top" width="100"><div align="center"><font color="#FFFFFF" size="2"><strong>Materia</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="64"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Fecha Documento</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="97"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Procedencia</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="80"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Funcionario</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="97"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Destinatario</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="80"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Funcionario Destino</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="64"><div align="center"><font color="#FFFFFF" size="2"><strong>Fecha Despacho</strong></font></div></td>';
echo '</tr>';
//echo '</table>';

//echo '<table width="780" align="center" border="top" cellspacing="1" cellpadding="0" >';

$cont=0;
}
?>
<tr bgcolor="E6EEFF">  
  <td  valign="middle"  width="55"><font size="2"><?php echo $reg_pendientes["num_interno"];?></font></td>
  <td  valign="middle"  width="53"><font size="2"><?php echo $reg_pendientes["num_oficial"];?></font></td>
  <td  valign="middle"  width="57"><font size="2"><?php echo $reg_pendientes["num_externo"];?></font></td>
  <td  valign="middle"  width="64"><font size="2"><?php echo $reg_pendientes["desc_tipo_documento"];?></font></td>
  <td  valign="middle"  width="60"><font size="2"><?php if ($reg_pendientes["materia"]=="")
        echo "&nbsp";
   else echo $reg_pendientes["materia"];?></font></td>
  <td  valign="middle" width="61"><font size="2"><?php  $fecdc = strtotime($reg_pendientes["fecha_documento"]);
	    $fecdc=date("d/m/Y",$fecdc);
		echo $fecdc;?></font></td>
  <td  valign="middle" width="70"><font size="2"><?php if ($reg_pendientes["procedencia"]=="")
        echo "&nbsp";
   else echo $reg_pendientes["procedencia"];?></font></td>
  <td  valign="middle" width="69"><font size="2"><?php if ($reg_pendientes["funcprocedencia"]=="")
        echo "&nbsp";
   else echo $reg_pendientes["funcprocedencia"];?></font></td>
  <td  valign="middle" width="71"><font size="2"><?php if ($reg_pendientes["destino"]=="")
        echo "&nbsp";
   else echo $reg_pendientes["destino"];?></font></td>
  <td  valign="middle" width="72"><font size="2"><?php if ($reg_pendientes["funcdestino"]=="")
        echo "&nbsp";
   else echo $reg_pendientes["funcdestino"];?></font></td>
  <td  valign="middle" width="71"  ><font size="2"><? $fec_doc=strtotime($reg_pendientes["fecha_despacho"]);
	   $fech_doc = date("d/m/Y",$fec_doc);
	   echo $fech_doc;?></font></td>
</tr>
<?
   }
	mssql_close($cn);
  	?>
</table>

<table width="673" height="51" border ="0" align="center" cellpadding="0"  cellspacing="0">
  <tr> 
 <td width="662" height="24"> <div align="center"> 
        <input name="cmd_aceptar" type="button" class="botones" onClick="javascript:window.print();" value="Imprimir">
  </div>
  <strong></strong></td>
</tr>
</table>
</body>
</html>
