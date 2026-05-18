<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
$usua=$cusuario;
$xx=$idusuario;
$iddoc=$iddocum;
$idseg=$idseguim;
$fun=$idfuncionario;
$flujo = 8;
$numint=0;
$nombre_pantalla="";
//echo $idfuncionario . "*** docu" . $iddocum . "** seg " . $idseguim;
//echo "idusu" . $idusuario . "*** usu " . $cusuario . "** acc " . $accion . "** fun " . $idfuncionario . "** docu " . $iddocum . "** seg " . $idseguim ;

$fecha_x = date("d-m-Y");
$rs_documento="exec documento_referencia '" . $iddoc . "','" . $idseg . "'";
$qq = mssql_query($rs_documento,$cn); 
$rs=mssql_fetch_array($qq);

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>documento de referencia </title>


<script language="JavaScript" type="text/javascript">
</script>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC" topmargin="0">
<center>
<form name="form1" method="post"  >
    <table width="650" border="1" cellpadding="2" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td><div align="center"><font color="#FFFFFF" size="4"><b>DOCUMENTO DE 
            REFERENCIA </b></font></div></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#e6eeff">
      <tr> 
        <td width="633" bgcolor="#cadbff"> <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr bgcolor="#e6eeff"> 
              <td height="15" colspan="5"><font color="#7777FF"><strong>INFORMACION 
                DOCUMENTO DE REFERENCIA</strong></font></td>
              <td height="15"><div align="right"><strong><font color="#0000A0" size="1"><? echo "Usuario : " . $cusuario?></font></strong></div></td>
            </tr>
            <tr> 
              <td width="129" height="15"><font color="#804040"><b>Tipo de Docto</b> 
                </font></td>
              <td width="171" height="15" > <font color="#804040"><? echo $rs[desc_tipo_documento]; ?> 
                </font></td>
              <td width="72" height="15"><font color="#804040"><strong>N&ordm; 
                Interno</strong> <b></b></font></td>
              <td height="15"> <font color="#804040"><font color="#804040"><? echo $rs[num_interno]?></font> 
                </font></td>
              <td height="15"><font color="#804040"><b>Medio</b></font></td>
              <td height="15"><font color="#804040"> 
                <? 
                If($rs["medio"]=="P")
                {
		   		echo "Papel";
				}
				else
				if ($rs["medio"]=="C")
				{
		   		echo "Copia";
		 		}
				else
				if ($rs["medio"]=="F")
		    	{
		    	echo "Fax";
		    	}   
				else
		 		{
	 		    echo "Video";
		 		}
				
		 		?>
                </font> </td>
            </tr>
            <tr> 
              <td width="129" height="18"><font color="#804040"><b>Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></font></td>
              <td width="171" height="18"> <font color="#804040"> 
                <?php $fec_doc=strtotime($rs["fecha_documento"]);
		             $fech_doc=date("d/m/Y",$fec_doc);
     				echo $fech_doc;?>
                </font></td>
              <td width="72" height="18"><font color="#804040"><b>N&ordm; Oficial<font size="4" face="Arial"> 
                </font></b></font></td>
              <td width="80" height="18"> <font color="#804040"><?php echo $rs[num_oficial];?> 
                </font></td>
              <td width="51"><font color="#804040"><b>Original</b></font></td>
              <td width="110"><font color="#804040"><font color="#804040"><? echo $rs[original];?></font></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr valign="middle"> 
              <td width="126" height="18"><font color="#804040"> <b>Estado del 
                Tr&aacute;mite</b> </font></td>
              <td width="168" height="18"><font color="#804040"><? echo $rs[desc_estado_tramite];?><b></b></font></td>
              <td width="72" height="18"> <font color="#804040"><strong>N&ordm; 
                Externo </strong></font></td>
              <td width="259" height="18"><font color="#804040"><font size="4" face="Arial"> 
                </font><font color="#804040"> </font><font color="#804040"><? echo $rs[num_externo]; ?></font><font size="4" face="Arial"> 
                </font></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr> 
              <td width="128" height="18"><font color="#804040"><b>Procedencia</b></font></td>
              <td width="171" height="20"> <font color="#804040"><? echo $rs[procedencia];?> 
                </font><font color="#804040">&nbsp;</font> <font color="#804040"> 
                <font color="#804040"> </font> </font></td>
              <td width="74"><font color="#804040"><b>Funcionario</b></font></td>
              <td width="250"><font color="#804040"><? echo $rs[funcproced];?></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr> 
              <td width="128" height="22"><font color="#804040"><b>Materia</b> 
                </font></td>
              <td width="505"> <font color="#804040"> <? echo $rs[materia];?> 
                </font></td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td height="26"> 
          <div align="right"><strong><font color="#000000" ><?php echo '<a href="doc_gestion.php?cusuario=' . $cusuario .
			'&idusuario=' . $xx . '&idfuncionario=' . $fun .  '&txtdias=' . $xdias . '"><u>Volver</u></a>'; ?></font></strong></div></td>
		
		<br> 
          <table width="645" border="0">
            <tr> 
              <td width="551" rowspan="2"><div align="center"> 
                  <input type="hidden" name="cusuario" value="<? echo $usua;?>">
                  <input type="hidden" name="idusuario" value="<? echo $xx;?>" >
                  <input type="hidden" name="idfuncionario" value="<? echo $idfuncionario;?>" >
                  <input type="hidden" name="iddocum" value="<? echo $iddoc;?>" >
                  <input type="hidden" name="idseguim" value="<? echo $idseg;?>" >
				  </div>
                         </table><td width="7"></td>
      </tr>
    </table>
    </form>
  </center>
</body>
</html>
