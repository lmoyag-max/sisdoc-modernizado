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

<title></title>

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
    <td width="278" bordercolor="#FFFFFF"><div align="center"><font color="#FFFFFF"><strong>Documentos Ingresados</strong></font></div></td>
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
    <!--td width="68"><div align="right"><strong><font color="#000000" ><?php echo '<a href="busca_ingresos.php?cusuario=' . $cusuario .
			'&idusuario=' . $xx . '&idfuncionario=' . $fun .  '&sw_fecha=' . 0 .
			'">Volver</a>'; ?></font></strong></div></td-->
  </tr>
</table>
<table width="652" align="center" border="1" cellspacing="1" cellpadding="1">
  <tr bgcolor="#3399FF"><font face="Arial, Helvetica, sans-serif"></font> 
    <td width="8%" height="33" valign="top"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>NºInterno</strong></font></div></td>
    <td width="8%" valign="top" heigth="33"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>NºOficial</strong></font></div></td>
	<td width="8%" valign="top" heigth="33"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>Tipo Documento</strong></font></div></td>
    <td width="20%" valign="top" heigth="33"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>Materia</strong></font></div></td>
    <td width="20%" valign="top" heigth="33"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>Procedencia</strong></font></div></td>
    <td width="20%" height="33"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Funcionario</strong></font></div></td>
    <td width="20%" valign="top" heigth="33"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>Destino</strong></font></div></td>
    <td width="20%" height="33"><div align="center"><font color="#FFFFFF" size="2">
	<strong>Funcionario</strong></font></div></td>
    <td width="73" valign="top" heigth="45"> <div align="center"><font color="#FFFFFF" size="2">
	<strong>Fecha despacho</strong></font></div></td>
    <td width="20%" height="33"><strong><div align="center"><font color="#FFFFFF" size="2">
	<strong>Estado Trámite</strong></font></div></td>
	 </tr>
</table>
  <?php
// ------------ Busca las ingresos  por fecha_ini y fecha_fin  -------------------
if ($Cbo_Procedencia > 0)
{
	if ($tipo_procedencia=="I"){
	  $rs_proc = mssql_query("select (desc_dependencia)descproc from dependencia  where id_dependencia =$Cbo_Procedencia",$cn);}
	else{
		  $rs_proc = mssql_query("select (desc_dependencia_externa)descproc from dependencia_externa  where id_dependencia_externa =$Cbo_Procedencia",$cn);	}
	$reg_proc = mssql_fetch_array($rs_proc);
}
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
          b.id_seguimiento,b.id_procedencia,b.id_destino, g.desc_estado_tramite,   
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
       
          from documento  a,  tramite b,  tipo_documento f , estado_tramite g         
          where  a.id_documento= b.id_documento   
                     
                and  f.id_tipo_documento= a.id_tipo_documento            
                and  (b.id_estado_tramite=2 or b.id_estado_tramite=3)
				and b.id_estado_tramite=g.id_estado_tramite 
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
//echo "consulta" . $consulta ;
$reg_doc = mssql_query($consulta);				
$Totreg = mssql_num_rows($reg_doc);			
$cont=0;
 echo '<table width="655" align="center" border="1" cellspacing="1" cellpadding="0" bgcolor="#E6EEFF">';
while($reg_ingreso=mssql_fetch_array($reg_doc))
{
$cont=$cont + 1;
if($cont ==10){
echo '</table>';
echo '<div class="break"/>';
echo '<br>';
echo '<table width="655" align="center" border="1" cellspacing="1" cellpadding="0" bgcolor="#3399FF">';
echo '<tr>';
  echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Interno</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Oficial</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Tipo Documento</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Materia</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Procedencia</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Funcionario</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Destino</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Funcionario</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha despacho</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Estado Trámite</font></strong></td>';
echo '</tr>';
echo '</table>';

echo '<table width="655" align="center" border="1" cellspacing="1" cellpadding="0" bgcolor="#E6EEFF">';
$cont=0;
}
?> 

  <tr> 
  <td align="left" valign="middle" width="5%"><font size="2">
          <?php echo $reg_documento["num_interno"];?></font>
        </td>
		 <td align="left" valign="middle" width="5%"><font size="2">
          <?php echo $reg_documento["num_oficial"];?></font>
        </td>
	    <td align="left" valign="middle" width="9%"><font size="2">
          <?php echo $reg_documento["desc_tipo_documento"];?></font>
        </td>
	    <td align="left" valign="middle" width="20%"><font size="2">
          <?php echo $reg_documento["materia"];?></font>
        </td>
         
      <td align="left" valign="middle" width="11%"><font size="2"> 
	  <?php 
	    if ($reg_documento["procedencia"]=="") {
			    $reg_documento["procedencia"]="&nbsp";} 
	  echo $reg_documento["procedencia"];?></font> 
      </td>
        <td align="left" valign="middle" width="11%"><font size="2">
          <?php 
		  if ($reg_documento["funcproced"]=="") {
			    $reg_documento["funcproced"]="&nbsp";} 
		  echo $reg_documento["funcproced"];?></font>
        </td>
		<td align="left" valign="middle" width="11%"><font size="2"> 
	  <?php 
	    if ($reg_documento["destino"]=="") {
			    $reg_documento["destino"]="&nbsp";} 
	  echo $reg_documento["destino"];?></font> 
      </td>
        <td align="left" valign="middle" width="11%"><font size="2">
          <?php 
		  if ($reg_documento["funcdestino"]=="") {
			    $reg_documento["funcdestino"]="&nbsp";} 
		  echo $reg_documento["funcdestino"];?></font>
        </td>
        <td align="left" valign="middle" width="8%"><font size="2">
          <?php $fec_doc=strtotime($reg_documento["fecha_despacho"]);
		        $fech_doc=date("d/m/Y",$fec_doc);
				echo $fech_doc;?></font>
        </td>

	    <td align="left" valign="middle" width="9%"><font size="2">
          <?php echo $reg_documento["desc_estado_tramite"];?></font>
        </td>
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
