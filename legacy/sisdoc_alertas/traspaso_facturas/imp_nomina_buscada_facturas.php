<?php
include("variables.php");
include("conexion_bd.php");

//$cusuario='kiter';
//$idusuario=11; 
//$idfuncionario =3; 
//$idnomina =19787;
$Usuario=$cusuario;
$xx=$idusuario;
$fun=$idfuncionario;

if ($nombreemisor<> 0 )
{
 $nombreemisor = "" ;
 }
$nomina=$idnomina;
$fechasistema = date("d/m/Y H:i");      
$fechax =date("d/m/Y");
$horax   =date("H:i");

?>
<html>
<head>
<META Http-Equiv="Cache-Control" Content="no-cache">
<META Http-Equiv="Pragma" Content="no-cache">
<META Http-Equiv="Expires" Content="0">

<title></title>
<script language="JavaScript" type="text/javascript">
var xxxx; 

if (parseInt(navigator.appVersion) > 3) {
	if (navigator.appName == "Netscape") {
		layerVar="document.layers";
		styleVar="";
	}
	else
	{
		layerVar="document.all";
		styleVar=".style";
	}
}

function MuestraEsconde(LaLayer,ElAtributo) {
	if (parseInt(navigator.appVersion) > 3) {
		eval(layerVar + '["' + LaLayer + '"]' + styleVar + '.visibility = "' + ElAtributo + '"');
	}
}
function imprime() { 
 var i = document.form1.cbo_emisor.selectedIndex;
 document.form1.nombreemisor.value=document.form1.cbo_emisor.options[i].text;
 document.form1.action="imprime_nomina_buscada_facturas.php";
 document.form1.submit();

  /*MuestraEsconde('impresion','hidden');
  MuestraEsconde('volver','hidden');
  window.print();
  MuestraEsconde('impresion','visible');
  MuestraEsconde('volver','visible');
   */
}


 </script>
<style>div.break {page-break-before:always}</style> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../sisdoc_desarrollo/sisdoc_alertas/css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#FFFFFF" topmargin="0">
<?	
 $rs_proc="select dependencia.id_dependencia,substring(dependencia.desc_dependencia,1,50) as desc_dependencia, dependencia.cod_dependencia_nuevo
  from dependencia, acceso where acceso.id_dependencia=dependencia.id_dependencia and dependencia.vigencia is null and acceso.id_usuario=" . $xx.
  " order by dependencia.desc_dependencia";
 
   $qp=mssql_query($rs_proc);
//echo  "query" . $rs_proc; ?>  
<form name="form1" method="Post"  > 
<table width="720" border="0" align="center" cellpadding="1" cellspacing="1">
  <tr> 
    <td width="142" height="32"></font></strong></div></td>
    <td width="310"> <div align="center"><strong><font color="#0000A0" size="3">NOMINA DE FACTURAS DESPACHADAS </font></strong></div></td>
    <td width="179"><div align="right"></div></td>
  </tr>
</table>
<table width="720" border="0" align="center" cellpadding="1" cellspacing="1">
  <tr> 
    <td width="137" height="47"></td>
    <td width="153"> <div align="center"><strong><font color="#0000A0" size="3"><? echo "Emitido por : " ;?> </font></strong></div></td>
	<td width="200">
	<?php // para mostrar combos con las dependencias que tien acceso el usuario 
	?>
	<select name ="cbo_emisor" class="combo"  id="select" >
	<? while  ($reg_procedencia = mssql_fetch_array($qp))
	{?>
	<option value=<?echo $reg_procedencia[id_dependencia] ?>>
	<? echo $reg_procedencia[desc_dependencia] ?></option> <? }?>
	  </select>
	</td>
    <td width="217"><div align="right"></div></td>
  </tr>
</table>
<table width="720" align="center" border="0" cellspacing="1" cellpadding="1">
  <tr> 
	<td width="158" height="26" ><strong><font color="#0000A0" size="2"><? echo "Usuario : " . $cusuario?></font></strong></td>
    <td width="281" height="26"  valign="middle"> 
      <div align="center"><font color="#0000A0" size="3"><? echo "NOMINA : " . $nomina?></font></div></td>
    <td width="109" height="26"  valign="middle" align="right" ><div align="left"><font color="#0000A0" size="2" >Fecha 
        Despacho</font><font color="#0000A0" >:</font></div></td>
    <td width="159"  valign="middle" align="right" > <div align="left"><font color="#0000A0" size="2" ><?php echo $fechax ;?></font></div></td>
  </tr>
</table>

<table width="720" align="center" border="0" cellspacing="1" cellpadding="1">
  <tr> 
    <td width="158" height="24" ><strong></strong></td>
    <td width="281" height="24"  valign="middle"> 
      <div align="center"></div></td>
    <td width="109" height="24"  valign="middle" align="right" > 
      <div align="left"><font color="#0000A0" size="2" >Hora 
        Emisi&oacute;n</font><font color="#0000A0" >:</font></div></td>
    <td width="159"  valign="middle" align="right" > <div align="left"><font color="#0000A0" size="2" ><?php echo $horax. " " . "Hrs.";?></font></div></td>  </tr>
</table>
<table width="720" border="0" align="center" cellpadding="1" cellspacing="1">
  <tr> 
    <td width="157" height="59"> 
      <div id="impresion" style="position:absolute; z-index:1; visibility: visible; overflow: auto; background-color: #ffffff; layer-background-color: #ffffff; border: 1px none #000000; left: 658px; top: 165px; width: 178px; height: 39px;"> 
          <table width="42%" height="35" align="right">
          <tr> 
            <td align="right"><input name="button" type="button" onClick="imprime()" value="Imprime Nómina"></td>
          </tr>
        </table>
      </div>
    </td>
	  
    <!--td width="350"> 
      <div id="volver" style="position:absolute; z-index:1; visibility: visible; overflow: auto; background-color: #ffffff; layer-background-color: #ffffff; border: 1px none #000000; left: 709px; top: 26px; width: 119px; height: 41px;"> 
          <table width="42%" height="35" align="right">
          <tr> 
            <td align="right"><strong><?php echo '<a href="multi_pages_facturas.php?cusuario=' . $cusuario .
			'&idusuario=' . $xx . '&idfuncionario=' . $fun . '">Volver</a>'; ?></strong></td>
          </tr>
        </table>
	   </div>
			
    </td-->
		  	
    <td width="203">&nbsp;</td>
  </tr>
</table>

  <?php
// ------------ Busca la nómina que se ha generado para imprimirla -------------------
	
//$rs_recep="exec busca_nomina_despacho '" . $xx .  "','" . $nomina . "'";
$rs_recep="exec busca_nomina_despacho2_facturas '" . $xx .  "','" . $nomina . "'";

$qq=mssql_query($rs_recep); 

$cont=0;
echo '<table width="700" align="center" border="1" cellspacing="0" cellpadding="0" bgcolor="#E6EEFF">';
while($qr=mssql_fetch_array($qq))
{
$cont=$cont + 1;
if($cont ==30){
echo '</table>';
echo '<div class="break"/>';
echo '<br>';
echo '<table width="700" align="center" border="1" cellspacing="0" cellpadding="0" bgcolor="#3399FF">';
echo '<tr>'; 
echo '<td height="45" valign="top" width="33"><div align="left"><font size="2" color="#FFFFFF"><strong>Nº Factura</strong></font></div></td>';
echo '<td height="45" valign="top" width="33"><div align="left"><font size="2" color="#FFFFFF"><strong>Proveedor</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="70"><div align="left"><font size="2" color="#FFFFFF"><strong>Tema factura</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="150"><div align="left"><font size="2" color="#FFFFFF"><strong>Descripción</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="70"><div align="left"><font size="2" color="#FFFFFF"><strong>Procedencia</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="70"><div align="left"><font size="2" color="#FFFFFF"><strong>Destinatario</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="50"><div align="left"><font size="2" color="#FFFFFF"><strong>Fecha factura</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="70"><div align="left"><font size="2" color="#FFFFFF"><strong>Observación</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="70"><div align="left"><font size="2" color="#FFFFFF"><strong>Firma</strong></font></div></td>';
echo '</tr>';
echo '</table>';

echo '<table width="700" align="center" border="1" cellspacing="0" cellpadding="0" bgcolor="#E6EEFF">';
$cont=0;
}
?> 
<?php
if($cont==1)
{

echo '</table>';
echo '<div class="break"/>';
echo '<br>';
echo '<table width="700" align="center" border="1" cellspacing="0" cellpadding="0" bgcolor="#3399FF">';
echo '<tr>'; 
echo '<td height="45" valign="top" width="33"><div align="left"><font size="2" color="#FFFFFF"><strong>Nº Factura</strong></font></div></td>';
echo '<td height="45" valign="top" width="33"><div align="left"><font size="2" color="#FFFFFF"><strong>Proveedor</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="70"><div align="left"><font size="2" color="#FFFFFF"><strong>Tema factura</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="150"><div align="left"><font size="2" color="#FFFFFF"><strong>Descripción</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="70"><div align="left"><font size="2" color="#FFFFFF"><strong>Procedencia</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="70"><div align="left"><font size="2" color="#FFFFFF"><strong>Destinatario</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="50"><div align="left"><font size="2" color="#FFFFFF"><strong>Fecha factura</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="70"><div align="left"><font size="2" color="#FFFFFF"><strong>Observación</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="70"><div align="left"><font size="2" color="#FFFFFF"><strong>Firma</strong></font></div></td>';
echo '</tr>';
echo '</table>';

echo '<table width="700" align="center" border="1" cellspacing="0" cellpadding="0" bgcolor="#E6EEFF">';
}

?>
   <tr> 
    <td align="left" width="45"  valign="middle"><font size="2"><? echo $qr[num_factura];?></font></td>
    <td align="left" width="45"  valign="middle"><font size="2"><? echo $qr[razon_social];?></font></td>
    <td align="left" width="70" valign="middle"><font size="2"><? echo $qr[desc_tema];?></font></td>
    <td align="left" width="150" valign="middle"><font size="2"><?php if ($qr["descripcion"]=="")
	      echo "&nbsp";
	  else echo ltrim($qr["descripcion"]);?></font></td>
     <td align="left" width="70" valign="middle"><font size="2"><?php if ($qr[procedencia]=="")
		           echo "&nbsp";
				   else  if ($qr[funcprocedencia]=="")
				   echo $qr[procedencia];
				   else echo $qr[procedencia] . " - " . $qr[funcprocedencia];?></font></td>   
    <td align="left" width="70" valign="middle"><font size="2"><?php if ($qr[destino]=="")
		           echo "&nbsp";
				   else  if ($qr[funcionario]=="")
				   echo $qr[destino];
				   else echo $qr[destino] . " - " . $qr[funcionario];?></font></td>
    <td align="left" width="50"  valign="middle"><font size="2"><? $fec_doc=strtotime($qr[fecha_factura]);
			   $fech_doc = date("d/m/y",$fec_doc);
			   echo $fech_doc;?></font></td>
	<td align="left" width="70" valign="middle"><font size="2"><?php if ($qr["observaciones"]=="")
		           echo "&nbsp";
				   else echo ltrim($qr["observaciones"]);?></font></td>
	<td align="left" width="80" valign="middle"><font size="2"><?php echo "&nbsp"; ?></font></td>			   		   
    </tr>
   
  
  
  <?
  	}
  	?>
</table>

<table width="700" height="51" border ="0" align="center" cellpadding="0"  cellspacing="0">
  <tr> 
 <td width="662" height="24"> <div align="center"> <br>
      </div>
  <strong></strong></td>
</tr>
</table>
<input type="hidden" name="nombreemisor" >
<input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
<input type="hidden" name="idnomina" value="<? echo $idnomina;?>">
<input type="hidden" name="idusuario" value="<? echo $xx;?>">

</form>
</body>
</html>
