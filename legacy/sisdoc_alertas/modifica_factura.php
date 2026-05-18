<?php
include("conexion_bd.php");
include("carga_tablas.php");

// Modificado el 04/07/2003


$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;
$flujo=$flujook;
$fecha1 =date("02-01-Y");
$fecha2=date("d-m-Y");

$rs_fatc
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>modifica factura </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript" type="text/JavaScript">
<!--
var sw_ok;
var cont_arreglo;
var z=0;
var arreglo2 ="";
var ar_descrip =new Array();
sw_ok = true
var flujo2= <?php echo $flujo; ?>;  


function mensaje() { 
  if (flujo2==1) {
  
  alert("No existen Registros");
  }
}
function facturas()
{
	if (sw_ok)
	{ 
	document.form1.action="busca_facturas.php"
 	document.form1.submit();
	}
 }




function muestra(cod)
{
 ar_descrip[z]= cod;
 z=z+1;
 
}       
  
function valida_digito(cadena,objeto,largo)
{	//-----------------------------

	var i;
    var allowedac;
    var retorno;
    retorno = true;
    allowedac = "0123456789";
    for ( i=0; i < cadena.length; i++) { 
    if (allowedac.indexOf(cadena.charAt(i)) < 0) {
	    retorno = false; }
	}  
        
if (!retorno)
 {
   objeto.value = "0";
   alert("Solo se aceptan números enteros");
   objeto.focus();
 }
return retorno;
} 

function chequear_arreglo(filas) 
{
  var x=0;
  for (k=0;k<filas;k++)
  {
     if (document.form1.casilla[k].checked)
     {
       arreglo2=arreglo2+document.form1.casilla[k].value+"@";
      x=x+1;
	 
	  }
  }
	document.form1.arreglo.value=x + "@" + arreglo2;
	cont_arreglo = x;
	
}
 

function CheckLength(length) {
if (window.event.srcElement.value.length >= length)
 {
   alert('El Máximo de caracteres es  250');
   return false;                         
}
}

//-->
</script>




<script language="JavaScript" type="text/JavaScript">
<!--


function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>
<script src="js/calendario.js"></script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body bgcolor="#FFFFFF" onload="mensaje()">
<center>
<form name="form1" method="Post">
    <table width="650" height="26" border="0">
      <tr> 
        <td width="719"><div align="right"><strong><font color="#0000A0" size="1"><?echo "Usuario : " . $cusuario?></font></strong></div></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="657" height="45">
<div align="center"><font color="#FFFFFF" size="4"><strong>MODIFICACION FACTURAS</strong></font></div></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#E6EEFF">
      <tr> 
        <td width="665"  align="center"> 
          <table width="100%" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="320" class="texto"><strong><font color="#804040">Ingrese 
                los campos por los que desee buscar</font></strong></td>
            <td width="322"><div align="right"><strong><font color="#0000A0" size="2"> 
                  </font></strong></div></td>
          </tr>
        </table>
          <table width="640" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="658" height="76" align="center"> 
                <div align="center"> 
                  <table width="100%" border="0">
                    <tr> 
                      <td height="41">Fecha Inicial</td>
                      <td width="22%"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_ini" type="text" class="entradas" id="Txt_fecha_ini" value="<?=$fecha1?>" size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_ini');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                        </font></td>
                      <td width="21%">Fecha Factura</td>
                      <td width="42%"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_fin" type="text" class="entradas" id="Txt_fecha_doc3" value="<?=$fecha2?>" size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_ini');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                        </font></td>
                    </tr>
                    <tr> 
                      <td width="15%" height="41"><font color="#000000">Tema de 
                        Factura </font></td>
                      <td><font color="#000000"> 
                        <select name="Cbo_tema_facturas" class="combo" id="Cbo_tema_facturas" >
                          <?
						   while($reg=mssql_fetch_array($rs_tema_factura)){
							?>
                          <option value=<? echo $reg[id_tema] ?> ><? echo $reg[desc_tema] ?></option>
                          <?
							}
						  ?>
                        </select>
                        </font></td>
                      <td>Tipo Factura </td>
                      <td><font color="#000000">
                        <select name="tipo_facturas" class="combo" id="tipo_factura" >
						<option value="0"> </option>
                          <?
						   while($reg2=mssql_fetch_array($rs_tipo_factura)){
							?>
                          <option value=<? echo $reg2[id_tipo_fact] ?> ><? echo $reg2[desc_tipofactura] ?></option>
                          <?
							}
						  ?>
                        </select>
                        </font></td>
                    </tr>
                  </table>
                  <table width="100%" border="0">
                    <tr> 
                      <td width="20%" height="42"><font color="#000000"><strong>N&uacute;mero 
                        Factura</strong></font></td>
                      <td width="20%"><font color="#000000"><font size="4" face="Arial">
                        <input name="numfactura" type="text" class="entradas" id="numfactura" onBlur="valida_digito(this.value,this,12);" size="12" maxlength="12">
                        </font></font></td>
                      <td width="24%"><font color="#000000"><font size="4" face="Arial"> 
                        </font></font></td>
						
                      <td width="36%"><font color="#000000">&nbsp;</font></td>
                    </tr>
                  </table>
                  
                </div></td>
            </tr>
          </table>
		  <table width="640" border="0">
            <tr> 
              <td width="213" height="44" > 
                <p align="center"> 
                  <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                  <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
                  <input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
                  <input type="hidden" name="flujook" value="<? echo $flujo;?>">
                  <input type="hidden" name="cbo_tipo" value="<? echo $Cbo_Tipo_factura;?>">
                </p></td>
              <td width="214"><div align="center">
                  <input type="submit" name="Submit" value="Modifica Factura" onClick="facturas();">
                </div></td>
              <td width="213">&nbsp;</td>
            </tr>
          </table>
          
        </td>
    </tr>
  </table>
  </form>
  
<?php mssql_close($cn);?>
</center>
</body>
</html>
