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

<table width="712" height="39" border="0" align="center" cellpadding="1" cellspacing="0" bgcolor="#FFFFFF">
  <tr bgcolor="#6699FF"> 
    <td width="192" height="31"><font color="#0000FF">&nbsp;</font></strong></div></font></td>
    <td width="299" bordercolor="#FFFFFF"><div align="center"><font color="#FFFFFF"><strong>DOCUMENTOS 
        PENDIENTES </strong></font></div></td>
    <td width="215"> 
      <div align="right"><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font size="2"><? echo "Usuario : " . $cusuario?></font></strong></font></strong></font></strong></font></strong></font></div></td>
  </tr>
</table>
<strong></strong> 
<table align="center" width="720" height="41" border="0">
  <tr> 
    <td width="225" height="37"> 
      <div align="right">Fecha Inicio</div></td>
    <td width="115"><strong><font color="#000000" ><?php echo $fecha_ini; ?> </font></strong></td>
    <td width="107">Fecha Termino</td>
    <td width="111"><strong><font color="#000000" > <?php echo $fecha_fin ?> </font></strong></td>
    <td width="155"><div align="right"><strong><font color="#000000" ><?php echo '<a href="busca_pend.php?cusuario=' . $cusuario .
			'&idusuario=' . $xx . '&idfuncionario=' . $fun .  '&sw_fecha=' . 0 .
			'">Volver</a>'; ?></font></strong></div></td>
  </tr>
</table>

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
                and  b.id_estado_tramite = 3
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
?>
<div class="break"/>
<table width="669" border="1" align="center" >
  <tr> 
    <td width="659" height="169" bgcolor="#9CCBED"> 
      <table width="100%" border="0" cellpadding="2" cellspacing="2" bgcolor="#C3D6E6">
        <tr> 
          <td width="97"><strong>Tipo de Docto</strong></td>
          <td width="166"><font size="2"><?php echo $reg_pendientes["desc_tipo_documento"];?></font></td>
          <td width="81"><b>Fec. Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></td>
          <td width="51"> <font size="2"> 
            <?php  $fecdc = strtotime($reg_pendientes["fecha_documento"]);
	           $fecdc=date("d/m/Y",$fecdc);
		        echo $fecdc;?>
            </font> </td>
          <td width="104"><b>Fec. Despacho</b></td>
          <td width="123"><font size="2">
            <? $fec_doc=strtotime($reg_pendientes["fecha_despacho"]);
	   $fech_doc = date("d/m/Y",$fec_doc);
	   echo $fech_doc;?>
            </font> </td>
        </tr>
      </table>
      <table width="100%" border="0" align="center" cellpadding="1" cellspacing="0" bgcolor="#C3D6E6">
        <tr valign="middle"> 
          <td width="121"><b><i>Num. Interno<font size="4" face="Arial"> </font></i></b></td>
          <td width="96"> <font size="2"><?php echo $reg_pendientes["num_interno"];?></font> 
          </td>
          <td width="98"><b><i> Num. Oficial</i></b><font size="4" face="Arial">&nbsp; 
            </font></td>
          <td width="95"> <font size="2"><?php echo $reg_pendientes["num_oficial"];?></font> 
          </td>
          <td width="126"><b><i>Num. Externo<font size="4" face="Arial"> </font></i></b></td>
          <td width="133"> <font size="2"><?php echo $reg_pendientes["num_externo"];?></font> 
          </td>
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
          <td width="84%"> <font size="2"> 
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
$cont=0;
}
?>
<table width="671" border="1" align="center" >
  <tr> 
    <td width="661" height="142" bgcolor="#9CCBED"> <table width="100%" border="0" cellpadding="2" cellspacing="2" bgcolor="#C3D6E6">
        <tr> 
          <td width="97"><strong>Tipo de Docto</strong></td>
          <td width="166"><font size="2"><?php echo $reg_pendientes["desc_tipo_documento"];?></font> 
          </td>
          <td width="81"><b>Fec. Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></td>
          <td width="51"> <font size="2"> 
            <?php  $fecdc = strtotime($reg_pendientes["fecha_documento"]);
	    $fecdc=date("d/m/Y",$fecdc);
		echo $fecdc;?>
            </font> </td>
          <td width="104"><b>Fec. Despacho</b></td>
          <td width="123"><font size="2"> 
            <? $fec_doc=strtotime($reg_pendientes["fecha_despacho"]);
	   $fech_doc = date("d/m/Y",$fec_doc);
	   echo $fech_doc;?>
            </font> </td>
        </tr>
      </table>
      <table width="100%" border="0" align="center" cellpadding="1" cellspacing="0" bgcolor="#C3D6E6">
        <tr valign="middle"> 
          <td width="121"><b><i>Num. Interno<font size="4" face="Arial"> </font></i></b></td>
          <td width="142"> <font size="2"><?php echo $reg_pendientes["num_interno"];?></font> 
          </td>
          <td width="103"><b><i>Num. Oficial</i></b><font size="4" face="Arial">&nbsp; 
            </font></td>
          <td width="65"> <font size="2"><?php echo $reg_pendientes["num_oficial"];?></font> 
          </td>
          <td width="118"><b><i>Num. Externo<font size="4" face="Arial"> </font></i></b></td>
          <td width="120"> <font size="2"><?php echo $reg_pendientes["num_externo"];?></font> 
          </td>
        </tr>
      </table>
      <table width="100%" border="0" cellpadding="2" cellspacing="2" bgcolor="#C3D6E6">
        <tr> 
          <td width="16%"><strong>Procedencia</strong></td>
          <td width="30%"><font size="2">
            <?php if ($reg_pendientes["procedencia"]=="")
        echo "&nbsp";
   else echo $reg_pendientes["procedencia"];?>
            </font> </td>
          <td width="16%"><b>Funcionario<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></td>
          <td width="38%"> <font size="2"> 
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
          <td width="84%"> <font size="2"> 
            <?php if ($reg_pendientes["materia"]=="")
        echo "&nbsp";
   else echo $reg_pendientes["materia"];?>
            </font></td>
        </tr>
      </table>
      <p>&nbsp;</p></td>
  </tr>
</table>
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
