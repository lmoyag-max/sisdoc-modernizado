<?php
include("variables.php");
include("conexion_bd.php");
//include("funciones.php");
$Usuario=$cusuario;
$xx=$idusuario;
$nomina=$idnomina;
$fechasistema = date("d/m/Y H:i");      
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Documento sin t&iacute;tulo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript">

function buscar(){

if (document.formulario1.txtnomina.value != ""){
	document.formulario1.action="consulta_tramite.php";
	document.formulario1.submit();
 	 }

}


function validarentero(formu){ 
      //intento convertir a entero. 
	  var formu;
     //si era un entero no le afecta, si no lo era lo intenta convertir 
     formu.txtnomina.value = parseInt(formu.txtnomina.value);
	 //Compruebo si es un valor numérico 
      if (isNaN(formu.txtnomina.value)) { 
            //entonces (no es numero) devuelvo el valor cadena vacia 
			formu.txtnomina.value ="";
			alert ("No es valor numerico");
            return formu.txtnomina.value 
      }else{ 
            //En caso contrario (Si era un número) devuelvo el valor 
            return formu.txtnomina.value
      } 
} 
</script>
</head>

<body>
<p>&nbsp;</p>
<form name="form1" method="post" action="">
  <table width="75%" border="0">
    <tr> 
      <td> <p align="center"><font face="Verdana, Arial, Helvetica, sans-serif"><b><font size="3" color="#0000FF">BUSQUEDA 
          DE DOCUMENTOS </font></b></font></p></td>
    </tr>
    <tr> 
      <td align="center"> <div align="right"><strong><font color="#0000A0" size="2"> 
          <? echo "Usuario :" . $cusuario?> </font></strong></div></td>
    </tr>
  </table>
  <table width="75%" border="1">
    <tr> 
      <td height="30">Ingrese el N&uacute;mero Interno 
        <!--input type="text" name="txtnomina" size="8" maxlength="8" ;"-->
        <input type="text" name="txtnomina" size="8" maxlength="8" onBlur="validarentero(formulario1);"> 
        <font size="2" face="Arial"> 
        <input type="button" name="cmd_busca" value="Buscar" onClick="buscar();">
        </font> </td>
      <td height="30"><strong> <?php echo "Total Registros : " . $Totreg ?> </strong></td>
      <td height="30"><strong> <?php echo "Total de Páginas : " . $NumPag ?> </strong></td>
    </tr>
  </table>
</form>
<p>&nbsp;</p>
</body>
</html>
