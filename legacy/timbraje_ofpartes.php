<?php
include("conexion_bd.php");
include("carga_tablas.php");

// Modificado el 04/07/2003
$cusuario="karina";
$idusuario=3;
$idfuncionario=11;
$flujook=0;
$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;
$flujo=$flujook;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario ingreso docto1</title>
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
function documentos()
{
	if(document.form1.Cbo_Tipo_Docto.value==0) {
		if((document.form1.TxtInterno.value=="") && (document.form1.TxtOficial.value==""))
		{
		alert("Debe Ingresar al menos un Campo");
		sw_ok=false
		}
	}
	if (sw_ok)
	{ 
	document.form1.action="busca_documentos.php"
 	document.form1.submit();
	}
 }

function buscando() {
alert('Hola, espere un momento, estoy buscando');
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
<div align="center"><font color="#FFFFFF" size="4"><strong>TIMBRAJE DE DOCUMENTOS 
            </strong></font></div></td>
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
                      <td width="15%" height="41"><font color="#000000">Tipo de 
                        Docto</font></td>
                      <td width="85%"><font color="#000000"> 
                        <select name="Cbo_Tipo_Docto" class="combo" id="select5">
                           <option value="0"> </option>
						  <?
				   while($reg=mssql_fetch_array($rs_tipo_docto)){
				?>
                          <option value=<? echo $reg[id_tipo_documento] ?> ><? echo $reg[desc_tipo_documento] ?></option>
                          <?
}
?>
                        </select>
                        </font></td>
                    </tr>
                  </table>
                  <table width="100%" border="0">
                    <tr> 
                      <td width="15%" height="42"><font color="#000000"><strong>N&uacute;meros 
                        : </strong></font></td>
                      <td width="25%"><font color="#000000">Interno<font size="4" face="Arial"> 
                        <input name="TxtInterno" type="text" class="entradas" id="TxtInterno" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                        </font></font></td>
                      <td width="24%"><font color="#000000">Oficial<font size="4" face="Arial"> 
                        <input name="TxtOficial" type="text" class="entradas" id="TxtOficial" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                        </font></font></td>
						
                      <td width="36%"><font color="#000000">Externo<font color="#000000"><font size="4" face="Arial"> 
                        <input name="TxtExterno" type="text" class="entradas" id="TxtExterno" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                        </font></font></font></td>
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
                  <input type="hidden" name="cbo_tipo" value="<? echo $Cbo_Tipo_Docto;?>">
                </p></td>
              <td width="214"><div align="center">
                  <input type="submit" name="Submit" value="Modifica Documento" onClick="documentos();">
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
