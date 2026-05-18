<?php
include("conexion_bd.php");
include("carga_tablas.php");

global $Confidencial;
$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;
$flujo1=$flujook;
if ($flujook==8){
$num_int=0;}
else{
$num_int=$num_int;}

$rs_funcionario = mssql_query("SELECT id_dependencia FROM funcionario where id_funcionario = " . $fun, $cn);

$reg_func = mssql_fetch_array($rs_funcionario);
$Tot_fun = mssql_num_rows($rs_funcionario);

if ($Tot_fun!=0)
{
   if ($reg_func[id_dependencia]==6)
      {
	  	$id_dependencia=$reg_func[id_dependencia]; 
		$rs_procedencia = mssql_query("select * from dependencia_externa order by desc_dependencia_externa ",$cn);
		$Procedencia="E";
	   }
   else
      {
		$rs_procedencia = mssql_query("select dependencia.*
		from dependencia, acceso where acceso.id_dependencia = dependencia.id_dependencia and acceso.id_usuario =$xx",$cn);
		$id_dependencia=0;
		$Procedencia="I"; 
	  }	
}
else 
{
 $id_dependencia=0; }

$rs_servicio= mssql_query("SELECT * FROM descriptor order by desc_descriptor", $cn);
$nRows = mssql_num_rows($rs_servicio);
$Cbo_Estado_Docto=1;
$fecha_x = date("d-m-Y");

?>
<SCRIPT  language="JavaScript">
<!--
var flujo2= <?php echo $flujook; ?>;  
var numint= <?php echo $num_int; ?>;  

function CheckLength(length) {
if (window.event.srcElement.value.length >= length) {
   alert('El Máximo de caracteres es  250');
   return false;                         
}
}

function cambio()
{
var selindice, nuevalsel;
var valor="";
if (document.form1.radioprocedencia[0].checked==true)
{
selindice = document.form1.Cbo_Procedencia.selectedIndex;
nuevasel = document.form1.Cbo_Procedencia.options[selindice].value;
document.form1.val_procedencia.value= nuevasel;
top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
}
else
if (document.form1.radioprocedencia[1].checked==true)
{
document.form1.val_procedencia.value=  document.form1.Cbo_Procedencia.selectedIndex;
document.form1.val_funcionario.value= 0;
document.form1.Cbo_Func_Procedencia.disabled=true;
}
}

function cambio1()
{
var selindice, nuevalsel;
var valor="F";
if (document.form1.radiodestino[0].checked==true)
	{

	selindice = document.form1.Cbo_Destinatario.selectedIndex;
	nuevasel = document.form1.Cbo_Destinatario.options[selindice].value;
	document.form1.val_destino.value= nuevasel;
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+ nuevasel+"&sw="+valor;
	document.form1.Cbo_Func_Destino.disabled=false;
	}
else
if (document.form1.radiodestino[1].checked==true){
	document.form1.val_destino.value= document.form1.Cbo_Destinatario.selectedIndex;}
	document.form1.val_funcionario1.value=0;
	
}


function cambio2()
{
var selindice, nuevalsel;
var valor="";
selindice = document.form1.Cbo_Func_Procedencia.selectedIndex;
nuevasel = document.form1.Cbo_Func_Procedencia.options[selindice].value;
document.form1.val_funcionario.value=selindice;
}

function cambio3()
{
var selindice, nuevalsel;
var valor="F";
selindice = document.form1.Cbo_Func_Destino.selectedIndex;
nuevasel = document.form1.Cbo_Func_Destino.options[selindice].value;
document.form1.val_funcionario1.value=selindice;
}


function muestra_cuadro() { 
  if (flujo2==0) {
  
  alert("El Documento ha sido grabado con el Nro Interno : "+ numint);
  }
  else 
  {
  	ver_combos();
  }	
  }
function ver_combos() { 
if(document.form1.tipo_procedencia.value=="I" && document.form1.tipo_destino.value=="I"){ 
		cargar_funcionario_procedencia_destino();
}
else
  if(document.form1.tipo_procedencia.value=="I"&&document.form1.tipo_destino.value=="E"){		
	var valor="IE";
	var p1=<?php echo $xx; ?>;
	var p2= document.form1.Cbo_Procedencia.selectedIndex;
	var p3=document.form1.val_procedencia.value;
	var d3=document.form1.val_funcionario.value;
	var d2=document.form1.val_destino.value;
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+ p1+"&des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;	
	document.form1.Cbo_Func_Destino.disabled=true;
 } 
else 
 if(document.form1.tipo_procedencia.value=="E"&&document.form1.tipo_destino.value=="I"){		
	var valor="EI";
	var p2= document.form1.Cbo_Destinatario.selectedIndex;
	var p3=document.form1.val_destino.value;
	var d3=document.form1.val_funcionario1.value;
	var d2=document.form1.val_procedencia.value;
	top.window.frame_consultas.location.href="frame_consultas.php?des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;	
	document.form1.Cbo_Func_procedencia.disabled=true;
	document.form1.Cbo_Func_destino.disabled=false;
 }  
else 
 if(document.form1.tipo_procedencia.value=="E"&&document.form1.tipo_destino.value=="E"){		
	var valor="EE";
	var d2= document.form1.Cbo_Destinatario.selectedIndex;
	var d3=document.form1.val_destino.value;
	var p3=document.form1.Cbo_Procedencia.selectedIndex;
	var p2=document.form1.val_procedencia.value;
	top.window.frame_consultas.location.href="frame_consultas.php?des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;	
	document.form1.Cbo_Func_procedencia.disabled=true;
	document.form1.Cbo_Func_destino.disabled=true;
	document.form1.Cbo_Func_Procedencia.options[selindice].value=0;
	document.form1.Cbo_Func_Destino.options[selindice].value=0;
}  
}	 

function cargar_funcionario_procedencia_destino() {	 
   	var valor="II";
	var d2=document.form1.val_destino.value;
	var d3=document.form1.val_funcionario1.value;
	var p2=document.form1.val_procedencia.value;
	var p3=document.form1.val_funcionario.value;
	top.window.frame_consultas.location.href="frame_consultas.php?des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;
}


function destino_externo()
{
var selindice, nuevalsel;
var valor="E";
if  (document.form1.radiodestino[1].checked==true)
	{
	selindice = document.form1.Cbo_Destinatario.selectedIndex;
	nuevasel = document.form1.Cbo_Destinatario.options[selindice].value;
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+ nuevasel+"&des_d="+selindice+"&sw="+valor;
	document.form1.Cbo_Func_Destino.options.value=0;
	document.form1.Cbo_Func_Destino.disabled=true;
	}
}

function destino_interno()
{
var selindice, nuevalsel;
var valor="I";
nuevasel= 0;
top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+ nuevasel+"&sw="+valor;
}

function procedencia_interna()
{
var selindice, nuevalsel;
var valor="I";
nuevasel= 1;
top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+ nuevasel+"&sw="+valor;
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
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+ nuevasel+"&pro_d="+selindice+"&sw="+valor;
	//document.form1.Cbo_Func_Procedencia.options.value=0;
	document.form1.Cbo_Func_Procedencia.disabled=true;
	}
}
//-->
</script>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario ingreso docto1</title>
<script language="JavaScript" type="text/javascript">

var sw_ok;
var cont_arreglo;
var z=0;
var arreglo2 ="";
var ar_descrip =new Array();

function muestra(cod)
{
 {ar_descrip[z]= cod;
 z=z+1;
 }
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
 
// Valida los datos antes de grabar en las tablas
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
 	sw_ok=false;
	alert("Debe Ingresar al menos un Descriptor");
	document.form1.radiodescriptor.focus();
  }
else
if(document.form1.Cbo_Procedencia.options.value==0)
  {
 	sw_ok=false;
	alert("Falta Ingresar la Procedencia del Documento");
	document.form1.Cbo_Procedencia.focus();
  }
else
if  (document.form1.radiodestino[0].checked==true)
{
if(document.form1.Cbo_Procedencia.options.value==document.form1.Cbo_Destinatario.options.value)
  {
	if(document.form1.Cbo_Func_Procedencia.options.value==document.form1.Cbo_Func_Destino.options.value)
	{	
		sw_ok=false;
		alert("El Funcionario de Procedencia debe ser distinto al de Destino");
		document.form1.Cbo_Func_Procedencia.focus();
	}
  }		
}	
if  (document.form1.radiodestino[0].checked==true)
{
   	document.form1.tipo_destino.value="I";
}
else
if  (document.form1.radiodestino[1].checked==true)
{
	document.form1.tipo_destino.value="E";
	document.form1.val_funcionario1.value=0;
}

if  (document.form1.radioprocedencia[1].checked==true)
{
	document.form1.tipo_procedencia.value="E";
	document.form1.val_funcionario.value=0;
}
else
if  (document.form1.radioprocedencia[0].checked==true)
{
   	document.form1.tipo_procedencia.value="I";
}
	
if(document.form1.Cbo_Func_Procedencia.selectedIndex==0)
{document.form1.val_funcionario.value=0;}

if(document.form1.Cbo_Func_Destino.selectedIndex==0)
{document.form1.val_funcionario1.value=0;}
	
	
if (sw_ok)
{
	document.form1.submit();
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
	document.form1.arreglo.value=x + "@" + arreglo2;
	cont_arreglo = x;
}

 
function despachar_datos() 
{
	document.form1.action="multi_pages.php";
   	document.form1.submit();
} 

</script>


<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script src="js/calendario.js"></script>

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

<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body bgcolor="#FFFFFF" topmargin="0" onLoad="muestra_cuadro()">
<center>
<form name="form1" method="Post" action="guardar_ingreso.php"> 
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#ECE9D8">
      <tr> 
      <td width="630" align="center"> 
        <table width="620" border="0" align="center" cellpadding="2" cellspacing="2">
          <tr> 
            <td align="center"><font color="#0000A0" size="4">INGRESO DE DOCUMENTOS 
              </font></td>
          </tr>
        </table>
        <table width="620" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr> 
            <td align="center"> <div align="right"><strong></strong></div></td>
          </tr>
        </table>
        <table width="626" border="0"  align="center" cellspacing="1" cellpadding="1">
          <tr> 
            <td width="310" class="texto"><strong><font color="#0080C0">Identificaci&oacute;n 
              del Documento</font></strong></td>
            <td width="311"><div align="right"><strong><font color="#0000A0" size="1"><? echo "Usuario : " . $Usuario?></font></strong></div></td>
          </tr>
        </table>
          <table height="171" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="621" height="169" align="center"> 
                <div align="center"> 
                  <table width="100%" border="1" cellspacing="0" cellpadding="1">
                    <tr> 
                    <td width="99">Tipo de Docto</td>
                    <td width="114"> <select name="Cbo_Tipo_Docto" class="combo" id="select5">
                          <?
				   while($reg=mssql_fetch_array($rs_tipo_docto)){
				?>
                          <option value=<? echo $reg[id_tipo_documento] ?> ><? echo $reg[desc_tipo_documento] ?></option>
                        <?
}
?>
                      </select> </td>
                    <td width="80">Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                      <td width="309" valign="middle"><font face="Arial, Helvetica, sans-serif"> 
                        &nbsp;&nbsp; 
                        <input name="Txt_fecha_doc" type="text" class="entradas" id="Txt_fecha_doc2" value="<?=$fecha_x?>" size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                        &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; 
                        <input name="Original" type="checkbox" value="S" checked>
                        Original </font></td>
                  </tr>
                  <tr> 
                    <td>Estado</td>
                    <td><p><strong>Documento Nuevo</strong></p></td>
                      <td>Medio</td>
                    <td>
<select name="Cbo_Medio" class="combo" id="select3">
                        <option value="P" <?php if($Cbo_Medio=="P") { echo 'SELECTED'; } ?> >Papel</option>
                        <option value="C" <?php if($Cbo_Medio=="C") { echo 'SELECTED'; } ?> >Correo</option>
                        <option value="V" <?php if($Cbo_Medio=="V") { echo 'SELECTED'; } ?> >Video</option>
                      </select>
                      </td>
                  </tr>
                </table>
                <table width="607" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="603"> 
                      <table width="600" align="left"  height="0" border="0" cellspacing="1" cellpadding="1">
                          <tr> 
                          <td width="101"> 
                            <div align="justify"><strong>N&uacute;meros 
                              : <font size="4" face="Arial"> </font></strong></div></td>
                          <td width="189"><div align="justify">Oficial<font size="4" face="Arial"> 
                                <input name="TxtOficial" type="text" class="entradas" id="TxtOficial" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                              </font></div></td>
                          <td width="272"><div align="justify">Externo<font size="4" face="Arial"> 
                                <input name="TxtExterno" type="text" class="entradas" id="TxtExterno2" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                              </font></div></td>
                        </tr>
                      </table>
                      </td>
                  </tr>
                </table>
                <table width="610" border="0" valign="top" cellspacing="2" cellpadding="2">
                  <tr> 
                    <td width="72"><strong><font size="2">Materia</font></strong> 
                    </td>
                      <td width="301"><font size="2"> 
                        <textarea name="TxtMateria"  cols="50" rows="3" class="cajatexto" onBlur="valida_campo();" onKeyPress="return CheckLength(250)"></textarea>
                        </font>
                        <div id="Layer1" style="position:absolute; width:117px; height:126px; z-index:1; left: 592px; top: 109px; visibility: hidden; overflow: auto; background-color: #ECE9D8; layer-background-color: #ECE9D8; border: 1px none #000000;" class="texto"> 
                          <table width="100%" border="1" bgcolor="#ECE9D8">
                            <tr> 
                              <td height="82"> <font size="1" face="Trebuchet MS, Verdana, Arial, sans-serif"> 
                                <font face="Arial, Helvetica, sans-serif"> 
                                <?php 
							  $k=0;
							  while($reg_servicio = mssql_fetch_array($rs_servicio)) { ?>
                                <input type="checkbox" name="casilla" value="<?php echo $reg_servicio["id_descriptor"];?>" onClick="javascript:muestra(<?php echo $reg_servicio["id_descriptor"];?>);">
                                <?php echo $reg_servicio["desc_descriptor"];?> 
                                </font></font><font size="1" face="Arial, Helvetica, sans-serif"><br>
                                <?php } ?>
                                </font><font face="Arial, Helvetica, sans-serif">&nbsp; 
                                </font> </td>
                            </tr>
                            <tr> 
                              <td height="23"> <div align="center" onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide')"><strong>Aceptar</strong></div></td>
                            </tr>
                          </table>
                          <div align="right"></div>
                        </div>
                        <font size="2">&nbsp; </font></td>
                    <td width="217">Descriptores 
                      <input type="radio" name="radiodescriptor" value="radiobutton" onClick="MM_showHideLayers('Layer1','','show')"></td>
                  </tr>
                </table>
                
              </div></td>
          </tr>
        </table>
		<table width="626" border="0" cellspacing="1" cellpadding="1">
          <tr> 
            <td width="304" class="texto"><font color="#0080C0"><strong>Trámite 
              Del Documento</strong></font></td>
          </tr>
		</table>  
        <table width="626" border="1" cellspacing="1" cellpadding="1">
          <tr> 
            <td width="304"><font color="#800040"><strong>ORIGEN</strong></font></td>
            <td width="309"><font color="#800040"><strong>DESTINO</strong></font></td>
          </tr>
        </table>
          <table width="626" border="1" cellspacing="0" cellpadding="1">
            <tr> 
            <td width="305"><table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                  <td width="128"><strong>Interno 
                    <?php if($Procedencia=="I") { ?>
                    <input name="radioprocedencia" type="radio" onClick="javascript:procedencia_interna();" checked="true">
                    </strong> 
                    <?php ;} else {?>
                    <input name="radioprocedencia" type="radio" onClick="javascript:procedencia_interna();" > 
                    <?php ;} ?>
                  </td>
                  <td width="164"><strong>Externo
				    <?php if($Procedencia=="E") { ?>
                    <input name="radioprocedencia" type="radio" onClick="javascript:procedencia_externa();" checked="true">
					</strong>
					
					<?php ;} else {?>
					<input name="radioprocedencia" type="radio" onClick="javascript:procedencia_externa();">
                     <?php ;} ?> 				
				</td>
                </tr>
                <tr> 
                  <td>Procedencia</td>
                  <td><font face="Arial"> 
                      <select name="Cbo_Procedencia" class="combo" id="select" onChange="javascript:cambio();">
                        <option value="0"> </option>
                      <? if ($Procedencia=="I"){
		    while($reg_procedencia=mssql_fetch_array($rs_procedencia)){
			?>
                      <option value=<? echo $reg_procedencia[id_dependencia] ?> ><? echo $reg_procedencia[desc_dependencia] ?></option>
                      <?
			}}
			else {
			while($reg_procedencia=mssql_fetch_array($rs_procedencia)){
			?>
                      <option value=<? echo $reg_procedencia[id_dependencia_externa] ?> ><? echo $reg_procedencia[desc_dependencia_externa] ?></option>
                      <?
			}}
			?>
                    </select>
                    </font></td>
                </tr>
                <tr> 
                  <td>Funcionario</td>
                  <td><select name="Cbo_Func_Procedencia" class="combo" id="select" onChange="javascript:cambio2();">
                      </select> </td>
                </tr>
              </table></td>
            <td width="311"><table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                  <td width="115"><div align="center"><strong>Interno 
                      <input name="radiodestino" type="radio" onClick="javascript:destino_interno();" value="1" checked>
                      </strong></div></td>
                  <td width="183"><strong>Externo 
                    <input name="radiodestino" type="radio"  value="2" onClick="javascript:destino_externo();">
                    </strong></td>
                </tr>
                <tr> 
                    <td>Destino</td>
                  <td><font face="Arial"> 
                      <select name="Cbo_Destinatario" class="combo" id="Cbo_Destinatario" onChange="javascript:cambio1();">
                        <option value="0"> </option>
					  <?
		while($reg_destino=mssql_fetch_array($rs_destino)){
?>
                      <option value=<? echo $reg_destino[id_dependencia] ?> ><? echo $reg_destino[desc_dependencia] ?></option>
                      <?
}
?>
                    </select>
                    </font></td>
                </tr>
                <tr> 
                    <td height="42">Funcionario</td>
                  <td><font face="Arial"> 
                      <select name="Cbo_Func_Destino" class="combo" id="select" onChange="javascript:cambio3();">> 
                      </select>
                    </font></td>
                </tr>
              </table></td>
          </tr>
        </table>
          <table width="626" border="1" cellspacing="0" cellpadding="1">
            <tr>
            <td>Tipo Distribuci&oacute;n</td>
            <td><font face="Arial">
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
            <td>Tipo Compromiso</td>
            <td><font face="Arial">
                <select name="Cbo_Tipo_Compromiso" class="combo" id="select14">
                  <?
					while($reg_tipo_compromiso=mssql_fetch_array($rs_tipo_compromiso)){
					?>
                  <option value=<? echo $reg_tipo_compromiso[id_tipo_compromiso] ?> ><? echo $reg_tipo_compromiso[desc_tipo_compromiso] ?> 
                </option>
                <?
					}
					?>
              </select>
              </font></td>
          </tr>
          <tr>
            <td>Estado Compromiso</td>
            <td><font face="Arial">
                <select name="Cbo_Estado_Compromiso" class="combo" id="select13">
                  <?
					while($reg_estado_compromiso=mssql_fetch_array($rs_estado_compromiso)){
					?>
                  <option value=<? echo $reg_estado_compromiso[id_estado_compromiso] ?> ><? echo $reg_estado_compromiso[desc_estado_compromiso] ?></option>
                <?
					}
					?>
              </select>
              </font></td>
            <td>D&iacute;as Compromiso</td>
            <td><input name="TxtDias"  type="text" class="entradas" onBlur="valida_digito(this.value,this,2);" size="2" maxlength="2"></td>
          </tr>
        </table>
          <table width="626" border="1" cellspacing="0" cellpadding="1">
            <tr> 
            <td width="79"><strong><font size="2">Observaci&oacute;n</font></strong><br>
            </td>
            <td width="236"><textarea name="TxtObservacion" cols="50" rows="3" class="cajatexto"  id="textarea" onkeypress="return CheckLength(250);"></textarea></td>
            <td width="297">Despachado por Oficina de Partes 
              <input name="checkofpartes2" type="checkbox" id="checkofpartes22" value="S"></td>
          </tr>
        </table>
        
          <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr> 
            <td height="37" width="306"> 
              <div align="center"> 
                      
                <input type="hidden" name="estado_tramite" value="<? echo 1;?>">
                <input type="hidden" name="resuelto" value="<? echo "N";?>">
                <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
				<input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
				<input type="hidden" name="tipo_destino" >
				<input type="hidden" name="tipo_procedencia" >
				<input type="hidden" name="val_procedencia" >
				<input type="hidden" name="val_destino" >
				<input type="hidden" name="val_funcionario" >
				<input type="hidden" name="val_funcionario1" >
				<input type="hidden" name="arreglo" >
				<input type="hidden" name="num_int" value="<? echo $num_int;?>">
                <input type="hidden" name="Cbo_Estado_Docto" value="<? echo 1;?>">
                <input name="cmd_grabar" type="button" class="botones" onClick="chequear_arreglo(<?php echo $nRows?>);validar_datos();" value="Grabar">
              </div></td>
            <td width="300"><div align="center" width="310"> 
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
