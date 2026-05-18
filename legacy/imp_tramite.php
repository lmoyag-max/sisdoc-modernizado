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


 <script language="JavaScript" type="text/javascript">
 function imprime()
 {
/* var idusuario=<?php echo $idusuario;?>;
 var cusuario="<?php echo $cusuario; ?>";
 var fecha_ini="<?php echo $fecha_ini;?>";
 var fecha_fin="<?php echo $fecha_fin;?>";
 var Cbo_Tipo_Docto=<?php echo $Cbo_Tipo_Docto;?>;
 var Cbo_Procedencia=<?php echo $Cbo_Procedencia;?>;
 var Cbo_Func_Procedencia=<?php echo $Cbo_Func_Procedencia;?>;
 var Cbo_Destinatario=<?php echo $Cbo_Destinatario;?>;
 var Cbo_Func_Destino=<?php echo $Cbo_Func_Destino;?>;
 var idfuncionario=<?php echo $idfuncionario;?>;

 <?php
 echo 'location.href="imprime_despachados.php?idusuario=' . $idusuario .  
 "&cusuario=" . $cusuario .  "&fecha_ini=" . $fecha_ini . "&fecha_fin=" . $fecha_fin .
  "&Cbo_Tipo_Docto=" . $Cbo_Tipo_Docto . "&Cbo_Procedencia=" . $Cbo_Procedencia . "&Cbo_Func_Procedencia=" . $Cbo_Func_Procedencia .
  "&Cbo_Destinatario=" . $Cbo_Destinatario . "&Cbo_Func_Destino=" . $Cbo_Func_Destino . 
 "&idfuncionario=" . $idfuncionario.'";';  ?> 
 

/*top.window.frame_imprime_despachados.location.href="frame_imprime_despachados.php?idusuario="+idusuario+"&cusuario="+cusuario +"fecha_ini="+fecha_ini+
"fecha_fin="+fecha_fin+ "Cbo_Tipo_Docto=" + Cbo_Tipo_Docto + "Cbo_Procedencia=" + Cbo_Procedencia+ "Cbo_Func_Procedencia="+Cbo_Func_Procedencia+
"Cbo_Destinatario=" +Cbo_Destinatario + "Cbo_Func_Destino=" + Cbo_Func_Destino+"idfuncionario="+idfuncionario;
*/

window.print();

}
 </script>
<style>div.break {page-break-before:always}</style> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="/css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#FFFFFF" topmargin="0">

<table width="640" height="39" border="0" align="center" cellpadding="1" cellspacing="0" bgcolor="#FFFFFF">
  <tr bgcolor="#6699FF"> 
    <td width="192" height="31"><font color="#0000FF">&nbsp;</font></strong></div></font></td>
    <td width="299" bordercolor="#FFFFFF"><div align="center"><font color="#FFFFFF"><strong>TRAMITES 
        DESPACHADOS </strong></font></div></td>
    <td width="154">
<div align="right"><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font size="2"></font></strong></font></strong></font></strong></font></strong></font></div></td>
  </tr>
</table>
<table align="center" width="639" height="31" border="0">
  <tr> 
    <td width="216" height="27"> 
      <div align="right">Fecha Inicio</div></td>
    <td width="108"><strong><font color="#000000" ><?php echo $fecha_ini; ?> </font></strong></td>
    <td width="101">Fecha Termino</td>
    <td width="104"><strong><font color="#000000" > <?php echo $fecha_fin ?> </font></strong></td>
    <td width="88"><div align="right"><strong><font color="#000000" ><?php echo '<a href="busca_tramites_despachados.php?cusuario=' . $cusuario .
			'&idusuario=' . $xx . '&idfuncionario=' . $fun .  '&sw_fecha=' . 0 .
			'">Volver</a>'; ?></font></strong></div></td>
  </tr>
  
</table>
<?php
// ------------ Busca  tramites  con fecha de despacho que este dentro del rango de  fecha_ini y fecha_fin --
// ------------ y con estado tramite derivado y/o despachados -------------------
$dia = substr($fecha_ini,0,2);
$mes = substr($fecha_ini,3,2);
$año = substr($fecha_ini,6,4);
$fechaini = date("Y/m/d H:i", mktime(0,0,0, $mes, $dia, $año));
$dia = substr($fecha_fin,0,2);
$mes = substr($fecha_fin,3,2);
$año = substr($fecha_fin,6,4);
$fechafin = date("Y/m/d H:i", mktime(23,59,59, $mes, $dia, $año));

$consulta ="select a.id_documento,a.num_interno,a.num_oficial,a.num_externo,  
        f.desc_tipo_documento,a.materia,a.fecha_documento,b.fecha_despacho, b.id_nomina_despacho,         
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
                and  (b.id_estado_tramite = 2   )
			              
				 and (b.fecha_despacho between '$fechaini' and '$fechafin')";

if ($Cbo_Tipo_Docto==0)
{ $Cbo_doc="";   }
else
{
   $Cbo_doc=" and a.id_tipo_documento=" . $Cbo_Tipo_Docto;
   $consulta =$consulta . $Cbo_doc;
}

if ($Cbo_Procedencia==0)
{ $Cbo_proc="";   }
else
{
   $Cbo_proc=" and b.id_procedencia=" . $Cbo_Procedencia;
   $consulta =$consulta . $Cbo_proc;
   $consulta = $consulta . " and b.tipo_procedencia =" . "'$tipo_procedencia'" ;
}
   		
if ($Cbo_Func_Procedencia==0)
{ $Cbo_fproc="";   }
else{
   $Cbo_fproc=" and b.rut_procedencia=" . $Cbo_Func_Procedencia;
   $consulta =$consulta . $Cbo_fproc;}


if ($Cbo_Destinatario==0)
{ $Cbo_dest="";   }
else{
   $Cbo_dest=" and b.id_destino=" . $Cbo_Destinatario;
   $consulta =$consulta . $Cbo_dest;
   $consulta = $consulta . " and b.tipo_destinatario =" . "'$tipo_destino'" ;
}
   		
if ($Cbo_Func_Destino==0)
{ $Cbo_fdest="";   }
else{
   $Cbo_fdest=" and b.rut_destino=" . $Cbo_Func_Destino;
   $consulta =$consulta . $Cbo_fdest;}

$orden =" order by b.fecha_despacho,f.desc_tipo_documento   ";

$consulta = $consulta .  $orden;   
			
				
//echo "consulta" . $consulta ;

$reg_doc = mssql_query($consulta);				
$r =mssql_num_rows($reg_doc);
if ($r ==0)
{
echo '<script>';
echo 'alert("No Existen Documentos")';
echo '</script>'; 
}



$cont=0;?>
<table align="center" width="639" height="24" border="0">
  <tr> 
    <td width="21" height="20">&nbsp;</td>
    <td width="319">&nbsp;</td>
    <td width="136"><strong>Total Tr&aacute;mites:</strong></td>
    <td width="118"><strong><?php echo $r;?></strong>&nbsp;</td>
    <td width="23">&nbsp;</td>
  </tr>
</table>
<?php 
 while($reg_pendientes=mssql_fetch_array($reg_doc) and $cont < 5)
{
  $cont=$cont + 1;
  if($cont ==4)
 { 
      echo '<div class="break"/>';
      $cont=1;
  }
?>
<table width="631" border="1" align="center" height="100">
  <tr> 
    <td width="621" height="206" bgcolor="#9CCBED"> 
      <table width="100%" border="0" cellpadding="2" cellspacing="2" bgcolor="#C3D6E6">
        <tr> 
          <td width="126"><strong>Tipo de Docto</strong></td>
          <td width="241"><font size="2"> 
            <?php  echo  $reg_pendientes["desc_tipo_documento"];?>
            </font></td>
          <td width="111"><b>Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></td>
          <td width="117"> <font size="2"> 
            <?php  $fecdc = strtotime($reg_pendientes["fecha_documento"]);
	           $fecdc=date("d/m/Y",$fecdc);
		        echo $fecdc;?>
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
      <table width="100%" border="0" cellpadding="2" cellspacing="2" bgcolor="#C3D6E6">
        <tr> 
          <td width="113"><strong>Fecha Despacho</strong></td>
          <td width="146"><font size="2"> 
            <? $fec_doc=strtotime($reg_pendientes["fecha_despacho"]);
	   $fech_doc = date("d/m/Y",$fec_doc);
	   echo $fech_doc;?>
            </font></td>
          <td width="112"><b>N&ordm; N&oacute;mina</b></td>
          <td width="224"><font size="2"> 
            <?  echo  $reg_pendientes["id_nomina_despacho"];?>
            </font></td>
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
        <!--input name="cmd_aceptar" type="button" class="botones" onClick="javascript:window.print();" value="Imprimir"-->
        <input name="cmd_aceptar" type="button" class="botones" onClick="javascript:imprime();" value="Imprimir">
				  <input type="hidden" name="fecha_ini" >
                  <input type="hidden" name="fecha_fin" >
                  <input type="hidden" name="Cbo_Tipo_Docto" >
                  <input type="hidden" name="Cbo_Procedencia" >
				  <input type="hidden" name="Cbo_Func_Procedencia" >
				  <input type="hidden" name="Cbo_Destinatario" >
                  <input type="hidden" name="Cbo_Func_Destino" >
				  <input type="hidden" name="cusuario" >
				  <input type="hidden" name="idusuario" >
				  <input type="hidden" name="idfuncionario" >                         
                   </div>
      <strong></strong></td>
  </tr>
</table>
</body>
</html>
