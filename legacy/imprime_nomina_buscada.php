<?php
include("variables.php");
include("conexion_bd.php");

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
function imprime()
{
MuestraEsconde('impresion','hidden');
  MuestraEsconde('volver','hidden');
  window.print();
  MuestraEsconde('impresion','visible');
  MuestraEsconde('volver','visible');
   }


 </script>
<style>div.break {page-break-before:always}</style> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#FFFFFF" topmargin="0" onLoad="imprime()">

<form name="form1" method="Post"  > 
  <table width="703" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr> 
    <td width="142" height="32"></font></strong></div></td>
    <td width="310"> <div align="center"><strong><font color="#0000A0" size="3">NOMINA DE DOCUMENTOS DESPACHADOS </font></strong></div></td>
    <td width="179"><div align="right"></div></td>
  </tr>
</table>
  <table width="700" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr> 
      <td width="148" height="47"></td>
      <td width="364"> <div align="left"><strong><font color="#0000A0" size="3"><? echo "Emitido por : " . substr($nombreemisor,0,50);?> 
          </font></strong></div></td>
      <td width="178"><div align="right"></div></td>
    </tr>
  </table>
  <table width="702" align="center" border="0" cellspacing="1" cellpadding="1">
    <tr> 
	<td width="158" height="26" ><strong><font color="#0000A0" size="2"><? echo "Usuario : " . $cusuario?></font></strong></td>
    <td width="281" height="26"  valign="middle"> 
      <div align="center"><font color="#0000A0" size="3"><? echo "NOMINA : " . $nomina?></font></div></td>
    <td width="109" height="26"  valign="middle" align="right" ><div align="left"><font color="#0000A0" size="2" >Fecha 
        Despacho</font><font color="#0000A0" >:</font></div></td>
    <td width="148"  valign="middle" align="right" > <div align="left"><font color="#0000A0" size="2" ><?php echo $fechax ;?></font></div></td>
  </tr>
</table>

<table width="702" align="center" border="0" cellspacing="1" cellpadding="1">
  <tr> 
    <td width="158" height="24" ><strong></strong></td>
    <td width="281" height="24"  valign="middle"> 
      <div align="center"></div></td>
    <td width="109" height="24"  valign="middle" align="right" > 
      <div align="left"><font color="#0000A0" size="2" >Hora 
        Emisi&oacute;n</font><font color="#0000A0" >:</font></div></td>
    <td width="141"  valign="middle" align="right" > <div align="left"><font color="#0000A0" size="2" ><?php echo $horax. " " . "Hrs.";?></font></div></td>  </tr>
</table>
  <table width="700" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr> 
    <td width="157" height="59"> 
      <div id="impresion" style="position:absolute; z-index:1; visibility: visible; overflow: auto; background-color: #ffffff; layer-background-color: #ffffff; border: 1px none #000000; left: 655px; top: 168px; width: 178px; height: 39px;"> 
          <table width="42%" height="35" align="right">
          <tr> 
            <!--td align="right"><input name="button" type="button" onClick="imprime()" value="Imprime Nómina"></td-->
          </tr>
        </table>
      </div>
    </td>
	  
    <td width="350"> 
      <div id="volver" style="position:absolute; z-index:1; visibility: visible; overflow: auto; background-color: #ffffff; layer-background-color: #ffffff; border: 1px none #000000; left: 709px; top: 26px; width: 119px; height: 41px;"> 
          <table width="42%" height="35" align="right">
          <tr> 
            <td align="right"><strong><?php echo '<a href="imp_nomina_buscada.php?cusuario=' . $cusuario .
			'&idusuario=' . $xx .'&idnomina=' . $nomina . '&idfuncionario=' . $fun . '">Volver</a>'; ?></strong></td>
          </tr>
        </table>
	   </div>
		</td>  	
    <td width="189">&nbsp;</td>
  </tr>
</table>


  <?php
// ------------ Busca la nómina que se ha generado para imprimirla -------------------
	
$rs_recep="exec busca_nomina_despacho2 '" . $xx .  "','" . $nomina . "'";

$qq=mssql_query($rs_recep); 
$cont=0;
echo '<table width="650" align="center" border="1" cellspacing="0" cellpadding="0" bgcolor="#E6EEFF">';
echo '<br>';
echo '<tr>'; 
echo '<td height="45" valign="top" width="33"><div align="left"><font size="2" color="#000000"><strong>Nº Interno</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="38"><div align="left"><font size="2" color="#000000"><strong>Nº Oficial</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="50"><div align="left"><font size="2" color="#000000"><strong>Nº Externo</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="70"><div align="left"><font size="2" color="##000000"><strong>Tipo<br>Docto</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="120"><div align="left"><font size="2" color="#000000"><strong>Materia</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="60"><div align="left"><font size="2" color="#000000"><strong>Procedencia</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="60"><div align="left"><font size="2" color="#000000"><strong>Destinatario</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="50"><div align="left"><font size="2" color="#000000"><strong>Fecha Docto.</strong></font></div></td>';
//echo '<td heigth="45" valign="top" width="50"><div align="left"><font size="2" color="#000000"><strong>Tipo Distrib.</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="50"><div align="left"><font size="2" color="#000000"><strong>Observación</strong></font></div></td>';
echo '<td heigth="45" valign="top" width="50"><div align="left"><font size="2" color="#000000"><strong>Firma</strong></font></div></td>';
echo '</tr>';

while($qr=mssql_fetch_array($qq))
{?>
 
  <tr > 
    <td align="left" width="33"  valign="middle"><font size="2"><? echo $qr[num_interno];?></font></td>
    <td align="left" width="38"  valign="middle"><font size="2"><? echo $qr[num_oficial];?></font></td>
    <td align="left" width="50"  valign="middle"><font size="2"><? echo $qr[num_externo];?></font></td>
    <td align="left" width="70" valign="middle"><font size="2"><? echo $qr[desc_tipo_documento];?></font></td>
    <td align="left" width="120" valign="middle"><font size="2"><?php if ($qr["materia"]=="")
	      echo "&nbsp";
	  else echo ltrim($qr["materia"]);?></font></td>
     <td align="left" width="60" valign="middle"><font size="2"><?php if ($qr[procedencia]=="")
		           echo "&nbsp";
				   else  if ($qr[funcprocedencia]=="")
				   echo $qr[procedencia];
				   else echo $qr[procedencia] . " - " . $qr[funcprocedencia];?></font></td>   
    <td align="left" width="60" valign="middle"><font size="2"><?php if ($qr[destino]=="")
		           echo "&nbsp";
				   else  if ($qr[funcionario]=="")
				   echo $qr[destino];
				   else echo $qr[destino] . " - " . $qr[funcionario];?></font></td>
    <td align="left" width="50"  valign="middle"><font size="2"><? $fec_doc=strtotime($qr[fecha_documento]);
			   $fech_doc = date("d/m/y",$fec_doc);
			   echo $fech_doc;?></font></td>
    <!--td align="left" width="50"  valign="middle"><font size="2">
	         <? echo $qr[desc_tipo_distribucion] ; 			 ?>
			 </td-->
	<td align="left" width="50" valign="middle"><font size="2"><?php if ($qr["observaciones"]=="")
		           echo "&nbsp";
				   else echo ltrim($qr["observaciones"]);?></font></td>
	<td align="left" width="50" valign="middle"><font size="2"><?php echo "&nbsp"; ?></font></td>			   		   
    </tr>
  <?
  	}
  	?></table>
 
<input type="hidden" name="nombreemisor" >
<input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
<input type="hidden" name="idnomina" value="<? echo $idnomina;?>">
<input type="hidden" name="idusuario" value="<? echo $xx;?>">
<input type="hidden" name="nomina" value="<? echo $idnomina;?>">


</form>
</body>
</html>
