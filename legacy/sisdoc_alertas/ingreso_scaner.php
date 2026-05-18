<?php
include("conexion_bd.php");
include("carga_tablas.php");


$flujook = 0 ;
$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;
$flujo=$flujook;
$fecha1 =date("02-01-Y");
$fecha2=date("d-m-Y");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario ingreso docto1</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript" type="text/JavaScript">
<!--

// Para que considere todas las dependencias del Ministerio 
var arr_cod_dest=new Array();
var arr_id_dest=new Array();
var arr_nom_dest=new Array()
  <? 
  $i=0;
 while($reg_dest=mssql_fetch_array($rs_dependencia))
  {
   	 echo "arr_cod_dest[" . $i . "]='" . $reg_dest[cod_dependencia] . "';\n";
  	 echo "arr_id_dest[" . $i . "]='" . $reg_dest[id_dependencia] . "';\n";
   	 echo "arr_nom_dest[" . $i . "]='" . $reg_dest[desc_dependencia] . "';\n";
     $i=$i+1;
   }
     echo "var arr_largo_dest =" . $i . ";";
  ?>
////////////////////////////
var sw_ok;
var cont_arreglo;
var z=0;
var arreglo2 ="";
var ar_descrip =new Array();
sw_ok = true
var flujo2= <?php echo $flujo; ?>;  
var aviso= "<?php echo $mensaje; ?>";  
function mensaje() { 
//  if (flujo2==1) {
  if (aviso=="0") {
  
  alert("No existen documentos ");
  }
}
function busca_destino()
{
  var selindice, nuevalsel;
  var valor="PI";
  if (document.form1.Txtdestino.value!="")
  {
  
    dep=document.form1.Txtdestino.value;
    top.window.frame_consultas.location.href="frame_consultas.php?cod="+dep+"&sw="+valor;
  }
}

function obtiene_destinatario()
{
document.form1.Txtdestino.value = document.form1.Cbo_Destinatario.options.value;
}
function destino_interno()
{
var selindice, nuevalsel;
var valor="I";
nuevasel= 0;
top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
document.form1.Cbo_Destinatario.disabled=false;
document.form1.Cbo_Func_Destino.disabled=false;
}

function documentos()
{
	/*if(document.form1.Cbo_Tipo_Docto.value==0) {
		if((document.form1.TxtInterno.value=="") && (document.form1.TxtOficial.value==""))
		{
		alert("Debe Ingresar al menos un Campo");
		sw_ok=false
		}
	}*/
	if (sw_ok)
	{ 
 	document.form1.submit();
	document.form1.action="busca_documentos_ofpartes.php"
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
<div align="center"><font color="#FFFFFF" size="4"><strong>INGRESO NUMERO OFICIAL 
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
              <td width="658" height="76" align="center"> <div align="center"> 
                  <table width="100%" border="0">
                    <tr> 
                      <td width="667" height="41">Fecha Inicial</td>
                      <td width="22%"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_ini" type="text" class="entradas" id="Txt_fecha_ini" value="<?=$fecha1?>" size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_ini');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                        </font></td>
                      <td width="21%">Fecha T&eacute;rmino</td>
                      <td width="42%"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_fin" type="text" class="entradas" id="Txt_fecha_doc3" value="<?=$fecha2?>" size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_ini');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                        </font></td>
                    </tr>
                    <tr> 
                      <td height="33"><font color="#000000">Tipo de Docto</font></td>
                      <td colspan="3"><font color="#000000"> 
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
                  <table width="100%" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="104" height="28">Procedencia</td>
                      <td width="36"><font face="Arial"> 
                        <input name="Txtdestino" type="text" id="Txtdestino2" size="6" maxlength="6" onBlur="busca_destino()">
                        </font></td>
                      <td width="484" colspan="2"><font face="Arial"> 
                        <select name="Cbo_Destinatario" class="combo" id="select2" onChange="obtiene_destinatario();">
                          <script>
                           for(i=0;i<arr_largo_dest;i++)
                           {
                                document.write('<option value="'+arr_cod_dest[i]+'">'+arr_nom_dest[i]+'</option>');
                            }    
                          </script>
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
				  <input type="hidden" name="grabaok" value="0">
				  <input type="hidden" name="mensaje" value="1">
				</p></td>
              <td width="214"><div align="center">
                  <input type="submit" name="Submit" value="Busca documento" onClick="documentos();">
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
