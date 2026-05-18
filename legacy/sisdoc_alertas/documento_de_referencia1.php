<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
$Usuario = $usuario;
$id_doc =$iddocum;
$id_seg=$idseguim;
$fecha_x = date("d-m-Y");
$estado=3;
//echo "idusu" . $idusuario . "*** usu " . $cusuario . "** docu " . $iddocum . "** seg " . $idseguim .
"titulo" . $nombrepan;

$referencia_query="exec busca_doc_referencia '"  . $iddocum . "','" . $idseguim . "'";

$rs_ref = mssql_query($referencia_query,$cn); 
$reg_ref = mssql_fetch_array($rs_ref);
$tot_ref = mssql_num_rows($rs_ref);

// while($reg_ref = mssql_fetch_array($rs_ref))
//{
if($reg_ref[medio]=="P") { 
$medio="Papel";}
else
if($reg_ref[medio]=="V") { 
$medio="Video";}
else
if($reg_ref[medio]=="C") { 
$medio="Correo";}


   

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>
Documento sin t&iacute;tulo</title>
<script language="JavaScript" type="text/javascript">
 </script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC">
<form name="form1" 
method="post">
  <table width="581" border="1" align="center" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
    <tr> 
      <td width="575"><div align="center"><b><font color="#FFFFFF" size="4" face="Arial, Helvetica, sans-serif">DETALLE 
          DOCUMENTO DE REFERNECIA</font></b></div></td>
    </tr>
  </table>
  <table width="584"  border="1" align="center" cellpadding="1" cellspacing="0" bordercolor="#ECE9D8" bgcolor="#ECE9D8">
    <tr> 
      <td width="607"  valign="top"> 
        <table width="570" border="0" align="center" cellpadding="1" cellspacing="1">
          <tr>
            <td width="566"><div align="right"><strong><font color="#0000A0" size="2"><?php echo "Usuario : " . $cusuario?></font></strong></div></td>
          </tr>
        </table>
        <table width="575" height="106" border="1" align="center" cellpadding="2" cellspacing="2">
          <tr> 
            <td width="563" height="100"> <table width="563" border="0" cellspacing="1" cellpadding="1">
                <tr> 
                  <td width="117"><font color="#000000">Tipo Documento</font></td>
                  <td width="9"><strong> : </strong></td>
                  <td width="137"><strong><font color="#000000"><?php echo $reg_ref[desc_tipo_documento]; ?></font></strong></td>
                  <td width="80"><div align="center"><strong>N&uacute;meros :</strong></div></td>
                  <td width="64"><font color="#333333">Interno</font></td>
                  <td width="9"><strong> : </strong></td>
                  <td width="125"><strong><font color="#000000"><?php echo $reg_ref[num_interno]; ?> 
                    </font></strong></td>
                </tr>
              </table>
              <table width="563" border="0" cellspacing="1" cellpadding="1">
                <tr> 
                  <td width="115"><font color="#333333">Estado</font></td>
                  <td width="9"><strong> : </strong></td>
                  <td width="223"><strong><b><?php echo $reg_ref[desc_estado_tramite]; ?></b></strong></td>
                  <td width="60"><font color="#000000">Oficial</font></td>
                  <td width="9"><strong> : </strong></td>
                  <td width="128"><strong><font color="#000000"><?php echo $reg_ref[num_oficial]; ?></font></strong></td>
                </tr>
              </table>
              <table width="563" border="0" cellspacing="1" cellpadding="1">
                <tr> 
                  <td width="119"><font color="#333333">Fecha Documento</font></td>
                  <td width="9"><strong> : </strong></td>
                  <td width="219"><strong><font color="#000000"> <b> 
                    <?php $fec_doc=strtotime($reg_ref["fecha_documento"]);
		        $fech_doc=date("d/m/Y",$fec_doc);
				echo $fech_doc;?>
                    </b></font></strong></td>
                  <td width="61"><font color="#000000">Externo</font></td>
                  <td width="10"><strong> : </strong></td>
                  <td width="126"><strong><font color="#000000"><?php echo $reg_ref[num_externo]; ?></font></strong></td>
                </tr>
                <tr> 
                  <td>Procedencia </td>
                  <td><strong>:</strong></td>
                  <td><strong><?php echo $reg_ref[destino]; ?></strong></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr> 
                  <td>Funcionario</td>
                  <td><strong>:</strong></td>
                  <td><b><?php echo $reg_ref[nombre_destino]; ?></b></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
              </table>
              <table width="563" border="0" cellspacing="1" cellpadding="1">
                <tr> 
                  <td width="125"><strong>Materia :</strong></td>
                  <td width="488"><?php echo $reg_ref[materia];?></td>
                </tr>
              </table></td>
          </tr>
        </table>
        
      </td>
    </tr>
    <?
 // 	}
  	?>
	  </table>
  </form>
</body>
</html>
