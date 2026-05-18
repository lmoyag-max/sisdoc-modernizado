<?php
include("conexion_bd.php");
include("carga_tablas.php");

global $Confidencial;
$Usuario=$cusuario;
$xx= $idusuario;
$tipo_destino="I";
$tipo_procedencia="E";
$val_procedencia=0;
$val_destino=0;
$fun=$idfuncionario;
//echo "sw vcvcvcvcvcvcv" . $sw_ext;
$sw_ext1=$sw_ext;
//echo "sw1 :  " . $sw_ext1;
$tottipo=mssql_num_rows($rs_tipo_docto);
$totdes =mssql_num_rows($rs_cod_descriptor);
$totdist=mssql_num_rows($rs_distribucion);
$nRowsint = mssql_num_rows($rs_dependencia);
$nRowsext = mssql_num_rows($rs_dependencia_externa);

//echo "funcionario  " . $fun . " *** cusuario  " . $cusuario . "**** xx  " . $idusuario;
$flujo1=$flujook;
if ($flujook==8){
$num_int=0;}
else{
$num_int=$num_int;}

$rs_funcionario = mssql_query("SELECT id_dependencia FROM funcionario where id_funcionario = " . $fun, $cn);

$reg_func = mssql_fetch_array($rs_funcionario);
$Tot_fun = mssql_num_rows($rs_funcionario);

/*if ($Tot_fun!=0)
{
   if ($reg_func[id_dependencia]==6)
    {
	  	$id_dependencia=$reg_func[id_dependencia]; 
		$rs_procedencia = mssql_query("select * from dependencia_externa order by desc_dependencia_externa ",$cn);
	}	

}
else 
{
 $id_dependencia=0; }
*/
$rs_servicio= mssql_query("SELECT * FROM descriptor order by desc_descriptor", $cn);
$nRows = mssql_num_rows($rs_servicio);
$Cbo_Estado_Docto=1;
$fecha_x = date("d-m-Y");
$fecha_i = date("d-m-Y");


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario ingreso docto1</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/javascript">
<!--
var sw_ok;
var sw_multiple=0;
var cont_arreglo;
var cont_arreglo1;
var z=0;
var arreglo2 ="";
var arreglo1="";
var arreglo3="";
var ar_descrip =new Array();
var doc=0;
var dep="";
var retorno;
var largo=0;


function ver_descriptor()
{
if(document.form1.descriptor.checked)
{
    document.form1.Txtdesc1.disabled=true;
    document.form1.Txtdesc2.disabled=true;
    document.form1.Txtdesc3.disabled=true;
	MM_showHideLayers('Layer1','','show')
}
else
{
    document.form1.Txtdesc1.disabled=false;
    document.form1.Txtdesc2.disabled=false;
    document.form1.Txtdesc3.disabled=false;
}
}

function busca_tipodoc()
{
  if (retorno)
  {
  if (document.form1.Txttipodoc.value!="")
  {
  largo= <?php echo $tottipo; ?>;  
  for ( i=0; i < largo; i++)
  { 
    if (document.form1.Txttipodoc.value==document.form1.Cbo_Tipo_Docto[i].value)
	{
    	document.form1.Cbo_Tipo_Docto.selectedIndex = i;
	}
  }
  }
  }
}

function busca_distribucion()
{
  if (retorno)
  {
  if (document.form1.Txtdistribucion.value!="")
  	{
  	largo= <?php echo $totdist; ?>;  
  	for ( i=0; i < largo; i++) { 
    	if (document.form1.Txtdistribucion.value==document.form1.Cbo_Tipo_Distribucion[i].value)
		{
    	document.form1.Cbo_Tipo_Distribucion.selectedIndex = i;
		}
	}
    }
  }
}
function busca_descriptor(desc,cual)
{
 var selindice, nuevalsel;
  var valor="D";
  if (desc!="")
  {
    top.window.frame_consultas.location.href="frame_consultas.php?cod="+desc+"&sw="+valor+"&cual="+cual;
  }
}
/*function busca_cod_descriptor()
{
  if (retorno)
  {
  alert ("descriptor " +  document.form1.Txtdesc1.value);
  if (document.form1.Txtdesc1.value!="")
  {
  largo= <?php echo $totdes; ?>;  
  for ( i=0; i < largo; i++)
  { 
    alert ("combo " + document.form1.Cbo_descriptor[i].value);
    if (document.form1.Txtdesc1.value==document.form1.Cbo_descriptor[i].value)
	{
    	document.form1.Cbo_descriptor.selectedIndex= i;
	}
  }
  }
  }
}*/


function busca_procedencia()
{
  var selindice, nuevalsel;
  var valor="TP";
  if (document.form1.Txtprocedencia.value!="")
  {
    dep=document.form1.Txtprocedencia.value;
    top.window.frame_consultas.location.href="frame_consultas.php?cod="+dep+"&sw="+valor;
  }
}

function busca_destino()
{
  var selindice, nuevalsel;
  var valor="TD";
  if (document.form1.Txtdestino.value!="")
  {
    dep=document.form1.Txtdestino.value;
    top.window.frame_consultas.location.href="frame_consultas.php?cod="+dep+"&sw="+valor;
  }
}

function ver_destino()
{

	document.form1.tipo_destino.value ="I";
	document.form1.Cbo_Destinatario.disabled=true;
	MM_showHideLayers('LayerInt','','show');
	document.form1.val_destino.value=0;
	sw_multiple = 1;	
}

function muestra(cod)
{
z=0;
 ar_descrip[z]= cod;
 z=z+1;
 
}       
  
function valida_campo() 
{
sw_ok = true;
   	if(document.form1.TxtMateria.value == "") 
	{
           sw_ok = false;
		   alert("Debe Ingresar la Materia");
           document.form1.TxtMateria.focus();
    }
return sw_ok;	
}


function valida_digito(cadena,objeto,largo)
{	//-----------------------------

	var i;
    var allowedac;
   
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
 
// ****************** Valida los datos antes de grabar en las tablas **************

function validar_datos()
{
sw_ok=true;
if(document.form1.TxtMateria.value == "")
  {
 	sw_ok=false;
	alert("Falta Ingresar la Materia del Documento");
	document.form1.TxtMateria.focus();
  }
else
if(cont_arreglo==0)
{
 if(document.form1.Txtdesc1.value=="" && document.form1.Txtdesc2.value=="" && document.form1.Txtdesc3.value=="" ) 
  { 
	sw_ok=false;
	alert("Debe Ingresar al menos un Descriptor");
	document.form1.Txtdesc1.focus();
  }
  
}  
else
if(document.form1.Cbo_Procedencia.options.value==0)
  {
 	sw_ok=false;
	alert("Falta Ingresar la Procedencia del Documento");
	document.form1.Cbo_Procedencia.focus();
  }
else
if(document.form1.Cbo_Destinatario.options.value==0 && sw_multiple ==0)
  {
 	sw_ok=false;
	alert("Falta Ingresar el Destinatario del Documento");
	document.form1.Cbo_Destinatario.focus();
  }
  
var p=document.form1.Cbo_Procedencia.options.value;
var t=document.form1.Cbo_Tipo_Docto.options.value;
var n=document.form1.TxtExterno.value;
if(sw_ok) top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+t+"&pro_d="+p+"&des_d="+n+"&sw=BP";

/*if (sw_ok)
{
document.form1.submit();
}
*/
}

function fecha_invitacion(filas)
{
var j= 0;
MM_showHideLayers('Layer2','','hide');

for (i=0; i<filas;i++)
{

  if (document.form1.casilla[i].checked){
   if (document.form1.casilla[i].value==24){
   	   MM_showHideLayers('Layer2','','show');
   }
  }	 
}
}

function ver_check(filas) 
{
  var x=0;
  
  for (k=0;k<filas;k++)
  {
     if (document.form1.casilla2[k].checked)
     {
	  x=x+1;
	 }
  }
  if (x!=0)
  {	
	document.form1.Cbo_Destinatario.disabled=true;
  }
  else
  {
	document.form1.Cbo_Destinatario.disabled=false;
  }
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
  cont_arreglo = x;
 
  if (cont_arreglo==0)
	if(document.form1.Txtdesc1.value>0)
	{
	arreglo2=arreglo2+document.form1.Txtdesc1.value+"@";
	 x=x+1;
	}
	
	if(document.form1.Txtdesc2.value>0)
	{
	arreglo2=arreglo2+document.form1.Txtdesc2.value+"@";
	 x=x+1;
	}
	
	if(document.form1.Txtdesc3.value>0)
	{
	arreglo2=arreglo2+document.form1.Txtdesc3.value+"@";
	 x=x+1;
	}
 document.form1.arreglo.value=x + "@" + arreglo2;
}

function chequear_arregloint(filas) 
{
  var x=0;
  arregloint="";
  arreglo1="";
  for (k=0;k<filas;k++)
  {
     if (document.form1.casilla2[k].checked)
     {
       arreglo1=arreglo1+document.form1.casilla2[k].value+"@";
      x=x+1;
	  }
  }
	document.form1.arregloint.value=x + "@" + arreglo1;
	cont_arreglo1 = x;

}

 
function despachar_datos() 
{
	document.form1.action="multi_pages.php";
   	document.form1.submit();
} 
//-->
</script>
<script language="JavaScript" type="text/JavaScript">
<!--
var flujo2= "<?php echo $flujook; ?>";  
var numint= "<?php echo $num_int; ?>";  

function CheckLength(length) {
	if (window.event.srcElement.value.length >= length)
	{
   		alert('El Máximo de caracteres es  250');
   		return false;                         
	}
}

function grabando(grabo_o_no)
{
  if(!grabo_o_no)
  {
     alert("documento ya existe"); 
  }
  else
  {
    document.form1.submit();
  }
}

function muestra_cuadro()
{ 
  if (flujo2==0)
  {
	//alert("sw"+document.form1.sw_ext.value);
    alert("El Documento ha sido grabado con el Nro Interno : "+ numint);
	
  }
   
}

function cargar_funcionario_procedencia_destino() {	 
   	var valor="II";
	var d2=document.form1.val_destino.value;
	var d3=document.form1.val_funcionario1.value;
	var p2=document.form1.val_procedencia.value;
	var p3=document.form1.val_funcionario.value;
	

top.window.frame_consultas.location.href="frame_consultas.php?des_d="+d2+"&des_f="+d3+"&prod="+p2+"&pro_f="+p3+"&sw="+valor;
}


function destino_externo()
{
var selindice, nuevalsel;
var valor="E";
if  (document.form1.radiodestino[1].checked==true)
	{
	selindice = document.form1.Cbo_Destinatario.selectedIndex;
	nuevasel = document.form1.Cbo_Destinatario.options[selindice].value;
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&des_d="+selindice+"&sw="+valor;
	document.form1.Cbo_Func_Destino.options.value=0;
	document.form1.Cbo_Func_Destino.disabled=true;
	document.form1.Cbo_Destinatario.disabled=false;
	}
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

function procedencia_interna()
{
var selindice, nuevalsel;
var valor="I";
nuevasel= 1;
var p1= <?php echo $xx; ?>;
top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor+"&pro_d="+p1;
document.form1.Cbo_Func_Procedencia.disabled=false;
}

function procedencia_externa()
{

var selindice, nuevalsel;
var valor="PE";
if  (document.form1.radioprocedencia[1].checked==true)
	{
	selindice = document.form1.Cbo_Procedencia.selectedIndex;
	nuevasel = document.form1.Cbo_Procedencia.options[selindice].value;
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&pro_d="+selindice+"&sw="+valor;
	//document.form1.Cbo_Func_Procedencia.options.value=0;
	document.form1.Cbo_Func_Procedencia.disabled=true;
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

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_showHideLayers() { //v6.0
  var i,p,v,obj,args=MM_showHideLayers.arguments;
  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
    if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
    obj.visibility=v; }
}
//-->
</script>
<script src="js/calendario.js"></script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body bgcolor="#FFFFFF" topmargin="0" onLoad="muestra_cuadro()">
<center>
<form name="form1" method="Post" action="guardar_ofpartes.php">
    <table width="663" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="657" height="34">
<div align="center"><font color="#FFFFFF" size="4"><strong>INGRESO 
            DE DOCUMENTOS </strong></font></div></td>
      </tr>
    </table>
    <table width="664" border="1" cellpadding="1" cellspacing="0" bgcolor="#ECE9D8">
      <tr> 
        <td width="782" align="center" bgcolor="#e6eeff"> 
          <table width="640" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="320" class="texto"><strong><font color="#804040" face="Arial, Helvetica, sans-serif">IDENTIFICACION 
                DEL DOCUMENTO</font></strong></td>
              <td width="322"><div align="right"><font color="#804040"><strong><font size="2"> 
                  <? echo "Usuario : " . $cusuario?></font></strong></font></div></td>
            </tr>
          </table>
          <table width="650" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="658" height="202" align="center"> 
                <div align="center"> 
                  <table width="640" border="0" cellspacing="0" cellpadding="1">
                    <tr> 
                      <td width="92" height="39"><font color="#000000">Tipo de 
                        Docto</font></td>
                      <td width="104"> <font color="#000000"> 
                        <input name="Txttipodoc" type="text" id="Txttipodoc" onblur="valida_digito(this.value,this,8);busca_tipodoc();" size="3" maxlength="3">
                        </font></td>
                      <td width="233"><font color="#000000"> 
                        <select name="Cbo_Tipo_Docto" class="combo" id="select2">
                          <?
				   while($reg=mssql_fetch_array($rs_tipo_docto)){
				?>
                          <option value=<? echo $reg[id_tipo_documento] ?> ><? echo $reg[desc_tipo_documento] ?></option>
                          <?
}
?>
                        </select>
                        </font></td>
                      <td width="203" valign="middle"><font color="#000000"><strong>Estado 
                        : Documento Nuevo</strong></font><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font></td>
                    </tr>
                  </table>
                  <table width="640" border="0" cellpadding="1" cellspacing="0">
                    <tr> 
                      <td width="92"><font color="#000000">Fecha Docto</font></td>
                      <td width="104"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_doc" type="text" class="entradas" id="Txt_fecha_doc2" value="<?=$fecha_x?>" size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="imagen/icon-calen_f2.gif" name="calenda" width="25" height="20" hspace="2" vspace="0" border="0" align="bottom"></a> 
                        </font></td>
                      <td width="53"> <div align="left"><font color="#000000" face="Arial, Helvetica, sans-serif">Original</font></div></td>
                      <td width="36"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                        <input name="Original" type="checkbox" value="S" checked>
                        </font></td>
                      <td width="51"><div align="left"><font color="#000000" size="2" face="Arial, Helvetica, sans-serif">Medio</font><strong><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                          </font></strong></div></td>
                      <td width="292"><div align="left"><font color="#000000"><font color="#000000"> 
                          <select name="Cbo_Medio" class="combo" id="select7">
                            <option value="P" <?php if($Cbo_Medio=="P") { echo 'SELECTED'; } ?> >Papel</option>
                            <option value="C" <?php if($Cbo_Medio=="C") { echo 'SELECTED'; } ?> >Correo</option>
                            <option value="V" <?php if($Cbo_Medio=="V") { echo 'SELECTED'; } ?> >Video</option>
                            <option value="F" <?php if($Cbo_Medio=="F") { echo 'SELECTED'; } ?> >Fax</option>
                          </select>
                          </font></font></div></td>
                    </tr>
                    <tr> 
                      <td width="92"><font color="#000000">N&ordm; Externo</font></td>
                      <td width="104"><font color="#000000"><font color="#000000"><font size="4" face="Arial"> 
                        <input name="TxtExterno" type="text" class="entradas" id="TxtExterno" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                        </font></font><font size="4" face="Arial"> </font></font></td>
                      <td width="53"> <div align="left"></div></td>
                      <td width="36"><font color="#000000"><font color="#000000"> 
                        </font></font></td>
                      <td width="51"><font color="#000000"><font color="#000000"> 
                        </font><font size="4" face="Arial"> </font></font></td>
                      <td width="292"><div align="center"><font color="#000000"><strong><font size="2">Descriptor</font></strong> 
                          <input type="radio" name="radiodescriptor" value="radiobutton"   onClick="MM_showHideLayers('Layer1','','show')">
                          </font></div></td>
                    </tr>
                  </table>
                  <table width="60%" border="0" align="left" cellpadding="1" cellspacing="0" valign="top">
                    <tr> 
                      <td width="51" height="94"><font color="#000000"><strong><font size="2">Materia</font></strong> 
                        </font></td>
                      <td width="332"><font color="#000000" size="2"> 
                        <textarea name="TxtMateria"  cols="75" rows="4" class="cajatexto" onKeyPress="return CheckLength(250)"></textarea>
                        </font> <div id="Layer2" style="position:absolute; width:200px; height:34px; z-index:1; left: 314px; top: 317px; visibility: hidden; overflow: auto; background-color: #E6EEFF; layer-background-color: #E6EEFF; border: 1px none #000000;" class="texto"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                          </font> Fecha Invitacion<font color="#000000" face="Arial, Helvetica, sans-serif"> 
                          <input name="Txt_fecha_inv" type="text" class="entradas" id="Txt_fecha_inv2"  size="10" maxlength="10">
                          <a href="javascript:show_Calendario('form1.Txt_fecha_inv');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a></font></div>
                        <font color="#000000" size="2">&nbsp; </font> <div id="Layer1" style="position:absolute; width:219px; height:115px; z-index:1; left: 600px; top: 115px; visibility: hidden; overflow: auto; background-color: #E6EEFF; layer-background-color: #E6EEFF; border: 1px none #000000;" class="texto"> 
                          <table width="100%" border="1" bgcolor="#E6EEFF">
                            <tr> 
                              <td height="82"> <font color="#000000" size="1" face="Trebuchet MS, Verdana, Arial, sans-serif"> 
                                <font face="Arial, Helvetica, sans-serif"> 
                                <?php 
								
							  $k=0;
							  while($reg_servicio = mssql_fetch_array($rs_servicio)) { ?>
                                <input type="checkbox" name="casilla" value="<?php echo $reg_servicio["id_descriptor"];?>" onClick="javascript:muestra(<?php echo $reg_servicio["id_descriptor"];?>);">
                                <?php echo $reg_servicio["desc_descriptor"];?> 
                                </font></font><font color="#000000" size="1" face="Arial, Helvetica, sans-serif"><br>
                                <?php } ?>
                                </font><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; 
                                </font> </td>
                            </tr>
                            <tr> 
                              <td height="23"> <div align="center" onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide');fecha_invitacion(<?php echo $nRows;?>)"><font color="#000000"><strong>Aceptar</strong></font></div></td>
                            </tr>
                          </table>
                          <div align="right"></div>
                        </div></td>
                    </tr>
                  </table>
                  <table width="257" border="0" align="left" cellpadding="1" cellspacing="1">
                    <tr> 
                      <td>1</td>
                      <td height="26"><font color="#000000"> 
                        <input name="Txtdesc1" type="text"  id="Txtdesc1" onBlur="valida_digito(this.value,this,2);busca_descriptor(Txtdesc1,1);" size="3" maxlength="2">
                        </font></td>
                      <td><font color="#000000">&nbsp; 
                        <input name="Descriptor1" type="text"  id="Descriptor1"  size="20" maxlength="20">
                        </font></td>
                    </tr>
                    <tr>
                      <td>2</td>
                      <td height="22"><font color="#000000"> 
                        <input name="Txtdesc2" type="text" id="Txtdesc2"  onBlur="valida_digito(this.value,this,2);busca_descriptor(Txtdesc2,2);" size="3" maxlength="2">
                        </font></td>
                      <td><font color="#000000">&nbsp; 
                        <input name="Descriptor2" type="text"  id="Descriptor2"  size="20" maxlength="20">
                        </font></td>
                    </tr>
                    <tr> 
                      <td width="10">3</td>
                      <td width="18" height="33"><font color="#000000"> 
                        <input name="Txtdesc3" type="text"  id="Txtdesc3" onBlur="valida_digito(this.value,this,2);busca_descriptor(Txtdesc3);" size="3" maxlength="2">
                        </font></td>
                      <td width="219"><font color="#000000">&nbsp; </font><font color="#000000"> 
                        <input name="Descriptor3" type="text"  id="Descriptor3"  size="20" maxlength="20">
                        </font></td>
                      <td width="167">&nbsp; </td>
                      
                        </tr>
                  </table>
                  <p>&nbsp;</p>
                  <p>&nbsp;</p>
                </div></td>
            </tr>
          </table>
		  <table width="650" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="646" class="texto"><font color="#804040" face="Arial, Helvetica, sans-serif"><strong>TRAMITE 
                DEL DOCUMENTO</strong></font></td>
          </tr>
		</table>  
          <table width="652" border="1" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="646"><table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="83" height="28">Procedencia</td>
                    <td width="45"><font face="Arial"> 
                      <input name="Txtprocedencia" type="text" id="Txtprocedencia" onblur="busca_procedencia()" size="6" maxlength="6">
                      </font></td>
                    <td width="496" colspan="2"><font face="Arial"> 
                      <select name="Cbo_Procedencia" class="combo" id="select" >
                                           
                        <? 
		    while($reg_dep=mssql_fetch_array($rs_dependencia_externa)){
			$i=$i+1;
			?>
                        <option value=<? echo $reg_dep[id_dependencia_externa] ?> ><? echo $reg_dep[desc_dependencia_externa] . $i ?></option>
                        <?
			}
			?>
                      </select>
                      </font></td>
                  </tr>
                  <tr> 
                    <td width="83" height="28">Destino</td>
                    <td width="45"> <div align="left"><font face="Arial"> 
                        <input name="Txtdestino" type="text" id="Txtdestino" size="6" maxlength="6" onblur="busca_destino()">
                        </font></div></td>
                    <td width="247"><font face="Arial">
                      <select name="Cbo_Destinatario" class="combo" id="Cbo_Destinatario">
                        
                        <?
		while($reg_destino=mssql_fetch_array($rs_destino)){
?>
                        <option value=<? echo $reg_destino[id_dependencia] ?> ><? echo $reg_destino[desc_dependencia] ?></option>
                        <?
}
?>
                      </select>
                      </font></td>
                    <td width="248"><font face="Arial">
                      <input type="button" name="boton" value="Múltiple" onClick="javascript:ver_destino();">
                      </font></td>
                  </tr>
                </table></td>
            </tr>
          </table>
          <table width="650" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="17%">Tipo Distribuci&oacute;n</td>
              <td width="11%"><font face="Arial"> 
                <input name="Txtdistribucion" type="text" id="Txtdistribucion" onBlur="valida_digito(this.value,this,4);busca_distribucion();" size="4" maxlength="4">
                </font></td>
              <td width="32%"><font face="Arial">
                <select name="Cbo_Tipo_Distribucion" class="combo" id="select9">
                  <?
		while($reg_distribucion=mssql_fetch_array($rs_distribucion)){
?>
                  <option value=<? echo $reg_distribucion[id_tipo_distribucion] ?> ><? echo $reg_distribucion[desc_tipo_distribucion] ?></option>
                  <?
}
?>
                </select>
                </font></td>
              <td width="26%">D&iacute;as Compromiso</td>
              <td width="14%"><input name="TxtDias"  type="text" class="entradas" onBlur="valida_digito(this.value,this,2);" size="2" maxlength="2"></font> 
                <div id="LayerInt" style="position:absolute; width:271px; height:160px; z-index:1; left: 433px; top: 163px; visibility: hidden; overflow: auto; background-color: #E6EEFF; layer-background-color: #E6EEFF; border: 1px none #000000;" class="texto"> 
                  <table width="100%" border="1" bgcolor="#E6EEFF">
                    <tr> 
                      <td height="129"> 
                        <?php 
							  $k=0;
							  while($reg_dependencia = mssql_fetch_array($rs_dependencia)) { ?>
                        <input type="checkbox" name="casilla2" value="<?php echo $reg_dependencia["id_dependencia"];?>" onClick="javascript:muestra(<?php echo $reg_dependencia["id_dependencia"];?>);"> 
                        <?php echo $reg_dependencia["desc_dependencia"]  . "<br>"; } ?> 
                      </td>
                    </tr>
                    <tr> 
                      <td height="24"> 
                        <div align="center" onClick="MM_showHideLayers('LayerInt','','hide');MM_showHideLayers('LayerInt','','hide');ver_check(<?php echo $nRowsint;?>)"><strong>Aceptar</strong></div></td>
                    </tr>
                  </table>
                  <div align="right"></div>
                </div></td>
            </tr>
            <tr> 
              <td>Tipo Compromiso</td>
              <td><div align="left"><font face="Arial"> 
                  <select name="Cbo_Tipo_Compromiso" class="combo" id="select4">
                    <?
					while($reg_tipo_compromiso=mssql_fetch_array($rs_tipo_compromiso)){
					?>
                    <option value=<? echo $reg_tipo_compromiso[id_tipo_compromiso] ?> ><? echo $reg_tipo_compromiso[desc_tipo_compromiso] ?> 
                    </option>
                    <?
					}
					?>
                  </select>
                  <strong> </strong></font></div></td>
              <td>&nbsp;</td>
              <td>Estado del Compromiso</td>
              <td><font face="Arial"><strong>En Trámite</strong></font></td>
            </tr>
          </table>
          <table width="651" border="0" cellspacing="0" cellpadding="1">
            <tr> 
            <td width="81"><strong><font size="2">Observaci&oacute;n</font></strong><br>
            </td>
            <td width="244"><textarea name="TxtObservacion" cols="50" rows="3" class="cajatexto"  id="textarea" onkeypress="return CheckLength(250);"></textarea></td>
              <td width="320">&nbsp;</td>
          </tr>
        </table>
        
          <table width="652" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr> 
            <td height="37" width="323"> 
              <div align="center"> 
                      
                <input type="hidden" name="estado_tramite" value="1">
                <input type="hidden" name="resuelto" value="N">
                <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
				<input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
				<input type="hidden" name="tipo_destino" value="I">
				<input type="hidden" name="tipo_procedencia" value="E">
				<input type="hidden" name="val_procedencia" >
				<input type="hidden" name="val_destino" >
				<input type="hidden" name="val_funcionario" value="0">
				<input type="hidden" name="val_funcionario1" value="0">
				<input type="hidden" name="arreglo" >
				<input type="hidden" name="num_int" value="<? echo $num_int;?>">
                <input type="hidden" name="Cbo_Estado_Docto" value="1">
				<input type="hidden" name="Cbo_Estado_Compromiso" value="2">
				<input type="hidden" name="arregloint" >
				<input type="hidden" name="arregloext" >
				<input type="hidden" name="inv" >
				<input type="hidden" name="sw_2xt">
				<!--input name="cmd_grabar" type="button" class="botones" onClick="chequear_arreglo(<?php echo $nRows?>);validar_datos();" value="Grabar"!-->
              <input name="cmd_grabar" type="button" class="botones" onClick="chequear_arreglo(<?php echo $nRows?>);chequear_arregloint(<?php echo $nRowsint?>);validar_datos();" value="Grabar">
			  </div></td>
            <td width="329"><div align="center" width="310"> 
                <input name="submit2" type="button" class="botones" onClick="javascript:despachar_datos();" value="Despachar">
				
              </div></td>
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
