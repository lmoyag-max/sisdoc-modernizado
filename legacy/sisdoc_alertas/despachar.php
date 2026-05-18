<?php
include("variables.php");
include("conexion_bd.php");
include("funciones.php");

?>
<html>
<head>
<title>Recepcion</title>

 <script language="JavaScript" type="text/javascript">
function aceptar() 
	
{
    <!--? echo "<a href=\"cambia_estado2.php?variable1=".$qr[id_documento]."&variable2=".$qr[desc_dependencia]."&variable3=".$qr[id_destino]."\">".$qr[desc_estado_documento]."</a>";?-->

	<!--? echo "<a href=\"cambia_estado3.php?variable1=".$qr[id_documento]."&variable2=".$qr[desc_dependencia]."&variable3=".$qr[id_destino]."\">".$qr[desc_estado_documento]."</a>";?-->
<!--location.href="cambia_estado3.php?variable1="+-->
	
	location.href="nomina.php"  ;
       alert("Los Documentos  seleccionados han sidos DESPACHADOS");
 }

 </script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

</head>
<body>

  
<table width="735" border ="2"  cellspacing="0" cellpadding="0" bgcolor="#FFFFCC">
  <tr> 
    <td height="35" colspan="2" align="center" valign="top"> <blockquote> 
        <p><font color="#0000A0" size="4">DESPACHO DE DOCUMENTOS </font></p>
      </blockquote></td>
  </tr>
  <tr> 
    <td width="516" height="35" align="center" valign="top">&nbsp;</td>
    <td width="211" align="center" valign="center"> <form name="form2" method="post" action="">
        <div align="right"><strong>Todos </strong> 
          <input type="radio" name="radiobutton" value="false">
        </div>
      </form></td>
  </tr>
</table>
    
  
<table width="735" border="2" cellspacing="0" cellpadding="0" bgcolor="#FFFFCC">
  <tr> 
    <td height="40"  border = "2" 
	valign="top" bgcolor="#FFFFCC" width="70"2"><font size="2"><strong>Nro.Interno</strong></font> </font> </b> 
    </td>
    <td valign="top" width="100"><b><font size="2" >Tipo Docto.</font> </b> 
    <td valign="top" width="200"><b><font size="2" >Materia</font> </b> 
    <td valign="top" width="70"> <b><font size="2">Fecha Documento</font> </b> 
    <td valign="top" width="150"><b><font size="2">Procedencia </font></b> 
    <td valign="top" width="150"><b><font size="2">Destinatario</font> </b> 
    <td valign="top" width="50"><b><font size="2">Despachar </font> </b> </tr>
  <?php
	
 $rs_recep="select a.num_interno,a.fecha_documento,a.id_estado_documento,a.materia,
           b.desc_estado_documento, 
		   c.desc_procedencia_destino,
		   g.desc_dependencia,
		   d.id_destino, h.desc_tipo_documento 
   from documento  a, estado_documento b, procedencia_destino c, tramite d ,usuario e,
   funcionario f, dependencia g, tipo_documento h
   where  a.id_documento= d.id_documento 
   
   and a.id_estado_documento=b.id_estado_documento
   and d.id_procedencia =c.id_procedencia_destino
   and d.id_usuario = $idusuario
   and a.id_estado_documento= 1
   and a.id_usuario = e.id_usuario 
   and e.id_funcionario = f.id_funcionario
   and g.id_dependencia = d.id_destino
   and a.id_tipo_documento = h.id_tipo_documento
   order by  a.fecha_documento,h.desc_tipo_documento  ";
   $qq=mssql_query($rs_recep);
   while($qr=mssql_fetch_array($qq))
    {
	?>
  <tr> 
    <td valign="top" border="1" height="50" width="70"><font size="2"><? echo $qr[num_interno];?></font> 
    </td>
    <td valign="top" width="100"><font size="2"><? echo $qr[desc_tipo_documento];?></font> 
    </td>
    <td valign="top" width="200"><font size="2">
	<? if ($qr[materia] =="") echo "&nbsp";
	else echo $qr[materia];?></font></td>
    <td valign="top" width="70"><font size="2">
	 <?$fec_doc=strtotime($qr[fecha_documento]);
      $fech_doc = date("d/m/Y",$fec_doc);
      echo $fech_doc;?></font></td>
    <td valign="top" width="150" ><font size="2"><? echo $qr[desc_procedencia_destino];?> 
    </td>
    <td valign="top" width="150" ><font size="2"><? echo $qr[desc_dependencia];?> </td>
    <td valign="top" width="50" ><form name="form1" method="post" action="">
        <input type="checkbox" name="checkbox" value="checkbox">
      </form> </td>
  </tr>
  <tr> 
    <td height="2" width="70"></td>
    <td width="65" height="2"></td>
    <td width="250" height="2"></td>
    <td width="70" height="2"></td>
    <td width="150" height="2"></td>
    <td width="150" height="2"></td>
    <td width="50" height="2"></td>
  </tr>
  <?
  	}
  	?>
</table>
<table width="735" border ="1"  cellspacing="0" cellpadding="0" bgcolor="#FFFFCC" height="51">
  <tr> 
            <td height="24"> 
              <div align="center"> 
                <input type="button" name="cmd_aceptar" value="Aceptar" onClick="aceptar();">
              </div>
            </td>
          </tr>
  </table>
</body>
</html>
