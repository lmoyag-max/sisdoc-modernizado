<?php
include("variables.php");
include("conexion_bd.php");
include("funciones.php");
$Usuario=$cusuario;
$xx = $idusuario;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Content-Style-Type" content="text/css">
<head>
  <title>Despacho de Documentos</title>
  <script language="JavaScript">
  <!--
  var est_check=1;
  var ie = document.all?1:0;
  var ns4 = document.layers?1:0;
  
  function aceptar() 
	
{
    <!--? echo "<a href=\"cambia_estado2.php?variable1=".$qr[id_documento]."&variable2=".$qr[desc_dependencia]."&variable3=".$qr[id_destino]."\">".$qr[desc_estado_documento]."</a>";?-->

	<!--? echo "<a href=\"cambia_estado3.php?variable1=".$qr[id_documento]."&variable2=".$qr[desc_dependencia]."&variable3=".$qr[id_destino]."\">".$qr[desc_estado_documento]."</a>";?-->
<!--location.href="cambia_estado3.php?variable1="+-->
	
	document.emaildest.submit();
//	location.href="nomina.php"  ;
       alert("Los Documentos  seleccionados han sidos DESPACHADOS");
 }

  
  function chequea_todos(formu)
  {
    for (var i=0;i<formu.elements.length;i++)
    {
	  	
      var elemento = formu.elements[i]; //(e.name != 'chektodos') && (
      if (elemento.type=='checkbox')
      {
        elemento.checked = formu.chektodos.checked;
        if (formu.chektodos.checked)
        {
          cambia_color(elemento);
        }
        else
        {
          cambia_color(elemento);
        }
      }
    }
  }       
  
  
  function chequea(n) 
  {
     void(d=document);
     void(el=d.getElementsByName("chek[]"));
     for(i=0;i<el.length;i++) {
        void(el[i].checked=est_check);
  //      void(el[i].disabled=est_check);
     } 
     if(est_check==1) {
       est_check=0;
	   
      }  
      else {
	
       est_check=1;
     }
       
  } 

  
  function cambia_color(esto) 
  {
  	
     var estacheck=esto.checked;
     if (ie)
      {
        while (esto.tagName!="TR")
        {
           esto=esto.parentElement;
        }
      }
     else
      {
        while (esto.tagName!="TR")
        {
       	  esto=esto.parentNode;
        }
      }
     if(estacheck)
       esto.className = "columna1"
      else
       esto.className = "columna2";
   
  }   

  //-->
</script>

<!-- link rel="stylesheet" href="subSilver.css" type="text/css" -->
<style type="text/css">
<!--
/* Main table cell colours and backgrounds */
td.row1	{ background-color: #EFEFEF; }
td.row2	{ background-color: #DEE3E7; }
td.row3	{ background-color: #D1D7DC; }

/* row cells - the blue and silver gradient backgrounds */

tr.columna1	{
	font-weight : bold;
	background-color: #C3D6E6;
}

tr.columna2	{
	background-color: #EFEFEF;
}

-->
</style>
  
</head>
<body>
   <form name="emaildest" method="post" action="nomina.php">
  
  <table width="748" border="1" cellspacing="1" cellpadding="1">
    <tr>
      <td width="748">
	  <table width="748" border="0" cellspacing="1" cellpadding="1">
          <tr> 
            <td  align="right"><font color="#0000A0" size="2">Martes 1 Abril 2003</font></td>
          </tr>
          <tr> 
            <td><div align="center"><strong><font color="#0000A0" size="4">DESPACHO DE DOCUMENTOS</font></strong></div></td>
          </tr>
          <tr> 
            <td align="right"><strong><font color="#0000A0" size="3"><? echo "Usuario : " . $Usuario?></font></strong></td>
          </tr>
        </table></td>
    </tr>
  </table>
  
  <table width="756" cellpadding="3" cellspacing="1" border="2"  class="forumline">
  <tr class="columna2"> 
  <td align="left" valign="top" width="50"><strong><span class="gensmall">
  <font size="2">Id Docto</font></span></strong></td>
  
  <td align="left" valign="top" width="56"> <strong><span class="gensmall">
  <font size="2">N&uacute;mero Interno</font></span></strong></td>
  
  <td align="left" valign="top" width="56"> <strong><span class="gensmall">
  <font size="2">N&uacute;mero Oficial</font></span></strong></td>
  
  <td align="left" valign="top" width="79"><strong><span class="gensmall">
  <font size="2">Tipo Documento</font></span></strong></td>
  
  <td align="left" valign="top" width="185"> <strong><span class="gensmall">
  <font size="2">Materia</font></span></strong></td>
  
  <td align="left" valign="top" width="80"> <strong><span class="gensmall">
  <font size="2">Fecha Documento</font></span></strong></td>
  
  <td align="left" valign="top" width="104"> <strong><span class="gensmall">
  <font size="2">Destinatario</font></span></strong></td>
  
  <td align="left" valign="top" width="73"> <strong><span class="gensmall">
  <font size="2">Despachar</font></span></strong></td>
  </tr>
  </table>
  
  <!--    Tabla  Opción Todos        -->
  <table width="756" cellpadding="3" cellspacing="1" border="2" class="forumline">
  <tr class="columna2">
      <td align="left" valign="middle" width="746"> <span class="gensmall"><font size="2"> 
        <strong>1 - 10 de 30</strong></font></span></td>
  </tr>
  </table>
  
   
  <!--    Tabla  con detalle        -->
  <table width="756" cellpadding="3" cellspacing="1" border="2" class="forumline">
   <?php
   $rs_recep="select a.id_documento,a.num_interno,a.num_oficial,a.fecha_documento,a.id_estado_documento,
   a.materia, b.desc_estado_documento, c.desc_procedencia_destino, g.desc_dependencia,
   d.id_destino, h.desc_tipo_documento 
   from documento  a, estado_documento b, procedencia_destino c, tramite d ,usuario e,
   funcionario f, dependencia g, tipo_documento h
   where  a.id_documento= d.id_documento 
   and a.id_estado_documento=b.id_estado_documento
   and d.id_procedencia =c.id_procedencia_destino
   and d.id_usuario = 4
   and a.id_estado_documento= 1
   and a.id_usuario = e.id_usuario 
   and e.id_funcionario = f.id_funcionario
   and g.id_dependencia = d.id_destino
   and a.id_tipo_documento = h.id_tipo_documento
   order by  a.fecha_documento, h.desc_tipo_documento  ";
   $qq=mssql_query($rs_recep);
   $k=1;
   while($qr=mssql_fetch_array($qq))
    {
	?>
    <tr class="columna2">
	
	 <td align="left" valign="middle" width="50">
     <span class="gensmall"><font size="2"><? echo $qr[id_documento];?></font></span>
     </td>
	
	 <td align="left" valign="middle" width="57">
     <span class="gensmall"><font size="2"><? echo $qr[num_interno];?></font></span>
     </td>
	 
	 <td align="left" valign="middle" width="57">
     <span class="gensmall"><font size="2"><? echo $qr[num_oficial];?></font></span>
     </td>
	 
	 <td align="left" valign="middle" width="78">
     <span class="gensmall"><font size="2"><? echo $qr[desc_tipo_documento];?></font></span>
     </td>
	 
	 <td align="left" valign="middle" width="183">
     <span class="gensmall"><font size="2"><? if ($qr[materia] =="") echo "&nbsp";
	 else echo $qr[materia];?></font></span>
	 </td>
    	 
	 <td align="left" valign="middle" width="81">
     <span class="gensmall"><font size="2"><? $fec_doc=strtotime($qr[fecha_documento]);
     $fech_doc = date("d/m/Y",$fec_doc);
     echo $fech_doc;?></font></span>
     </td>
	 
	  <td align="left" valign="middle" width="105"> <span class="gensmall"><font size="2"><? echo $qr[desc_dependencia];?></font></span> 
      </td>
	 
	 <td align="left" valign="middle" width="74">
    <input class="post" type="checkbox" name="chek[]" value="<? $k;?>" onClick="cambia_color(this);">
     </td>
     </tr>
    <?
		$k++;
  	}
  	?>	
  </table>
  <table width="756" cellpadding="3" cellspacing="1" border="2" class="forumline">
  <tr class="columna2">
      <td align="left" valign="middle" width="550"> <span class="gensmall"></span></td>
  <td align="left" valign="middle" width="106"> <span class="gensmall"><font size="2">
  <strong>Despachar Todos</strong></font></span></td>
      <td align="left" valign="middle" width="74"> <input class="post" type="checkbox" name="chektodos" onClick="chequea_todos(document.emaildest);chequea(est_check);" value="t"></td>
  </tr>
  </table>
  <table width="756" cellpadding="3" cellspacing="1" border="2">
    <tr> 
      <td width="592" height="58"> <div align="center"> 
          <p>
            <input type="hidden" name="idusuario" value="<? echo $xx;?>">
            <input type="hidden" name="cusuario" value="<? echo $Usuario;?>">
            <input type="submit" name="Submit" value="Genera N&oacute;mina de Impresi&oacute;n" onClick="javascript:aceptar();">
          </p>
          
        </div>
      <td width="141"><strong><font size="2">1 2 3&nbsp;&nbsp; Siguiente &nbsp;&nbsp;Atr&aacute;s</font></strong></tr>
  </table>	
 </form>
</body>
</html>