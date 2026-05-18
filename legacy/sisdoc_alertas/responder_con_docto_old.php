<?php
include("conexion_bd.php");
include("carga_tablas.php");

global $Confidencial;
$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;
$flujo1=$flujook;
$id_doc=$iddocum;
$id_tra=$idseguim;
$est=3;
$txtnomina=$txtnomina;

if ($flujook!=0){
$num_int=0;}
else{
$num_int=$num_int;}
//echo "idusu" . $idusuario . "*** usu " . $cusuario . "** acc " . $accion . "** fun " . $idfuncionario . "** docu " . $iddocum . "** seg " . $idseguim ;
$id_func_proc=0;
$id_proc=0;
$val_funcionario=0;
$val_funcionario1=0;

$rs_funcionario = mssql_query("SELECT id_dependencia FROM funcionario where id_funcionario = " . $idfuncionario, $cn);
$reg_func = mssql_fetch_array($rs_funcionario);
$Tot_fun = mssql_num_rows($rs_funcionario);

$rs_ref="exec busca_doc_referencia '" . $iddocum . "','" . $idseguim . "'";
//$rs_ref="exec documento_referencia '" . $iddocum . "','" . $idseguim . "'";

$rs_referencia=mssql_query($rs_ref); 
$reg_ref = mssql_fetch_array($rs_referencia);
$Totref = mssql_num_rows($rs_referencia);
$tipo_proc=$reg_ref[tipo_destinatario];
//echo "tipo " . $Totref;
$id_proc=$reg_ref[id_destino];
if($reg_ref[rut_destino]!=0){
$id_func_proc=$reg_ref[rut_destino];}

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

var flujo2= <?php echo $flujook; ?>;  
var numint= <?php echo $num_int; ?>;  


function cambio1()
{
var selindice, nuevalsel;
var valor="F";
if (document.form1.radiodestino[0].checked==true)
	{

	selindice = document.form1.Cbo_Destinatario.selectedIndex;
	nuevasel = document.form1.Cbo_Destinatario.options[selindice].value;
	document.form1.val_destino.value= nuevasel;
	parent.frames[0].location.href="frame_consultas.php?cod_dep="+ nuevasel+"&sw="+valor;
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
/*if(document.form1.tipo_procedencia.value=="I" && document.form1.tipo_destino.value=="I"){ 
		cargar_funcionario_procedencia_destino();
}
else
  if(document.form1.tipo_procedencia.value=="I"&&document.form1.tipo_destino.value=="E"){		
*/
  if(document.form1.tipo_destino.value=="E"){
     alert("destino externo");		
	var valor="DE";
	var d2=document.form1.val_destino.value;
	parent.frames[0].location.href="frame_consultas.php?des_d="+d2+"&sw="+valor;	
	document.form1.Cbo_Func_Destino.disabled=true;
 } 
else 
 if(document.form1.tipo_destino.value=="I"){		
 	 alert("destino interno");
	var valor="DI";
	var p2= document.form1.Cbo_Destinatario.selectedIndex;
	var p3=document.form1.val_destino.value;
	var d3=document.form1.val_funcionario1.value;
	//var d2=document.form1.val_procedencia.value;
	parent.frames[0].location.href="frame_consultas.php?des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;	
	//document.form1.Cbo_Func_procedencia.disabled=true;
	document.form1.Cbo_Func_destino.disabled=false;
 }
 }  
/*else 
 if(document.form1.tipo_procedencia.value=="E"&&document.form1.tipo_destino.value=="E"){		
	var valor="EE";
	var d2= document.form1.Cbo_Destinatario.selectedIndex;
	var d3=document.form1.val_destino.value;
	var p3=document.form1.Cbo_Procedencia.selectedIndex;
	var p2=document.form1.val_procedencia.value;
	parent.frames[0].location.href="frame_consultas.php?des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;	
	document.form1.Cbo_Func_procedencia.disabled=true;
	document.form1.Cbo_Func_destino.disabled=true;
	document.form1.Cbo_Func_Procedencia.options[selindice].value=0;
	document.form1.Cbo_Func_Destino.options[selindice].value=0;
} */ 
	 

function cargar_funcionario_procedencia_destino() {	 
   	var valor="II";
	var d2=document.form1.val_destino.value;
	var d3=document.form1.val_funcionario1.value;
	var p2=document.form1.val_procedencia.value;
	var p3=document.form1.val_funcionario.value;
	parent.frames[0].location.href="frame_consultas.php?des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;
}


function destino_externo()
{
var selindice, nuevalsel;
var valor="E";
if  (document.form1.radiodestino[1].checked==true)
	{
	selindice = document.form1.Cbo_Destinatario.selectedIndex;
	nuevasel = document.form1.Cbo_Destinatario.options[selindice].value;
	parent.frames[0].location.href="frame_consultas.php?cod_dep="+ nuevasel+"&des_d="+selindice+"&sw="+valor;
	document.form1.Cbo_Func_Destino.options.value=0;
	document.form1.Cbo_Func_Destino.disabled=true;
	}
}

function destino_interno()
{
var selindice, nuevalsel;
var valor="I";
nuevasel= 0;
parent.frames[0].location.href="frame_consultas.php?cod_dep="+ nuevasel+"&sw="+valor;
}

function procedencia_interna()
{
var selindice, nuevalsel;
var valor="I";
nuevasel= 1;
parent.frames[0].location.href="frame_consultas.php?cod_dep="+ nuevasel+"&sw="+valor;
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
	parent.frames[0].location.href="frame_consultas.php?cod_dep="+ nuevasel+"&pro_d="+selindice+"&sw="+valor;
	//document.form1.Cbo_Func_Procedencia.options.value=0;
	document.form1.Cbo_Func_Procedencia.disabled=true;
	}
}
</script>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Responder con Documento</title>
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
if(document.form1.Cbo_Destinatario.options.value==0)
  {
 	sw_ok=false;
	alert("Falta Ingresar el Destinatario del Documento");
	document.form1.Cbo_Destinatario.focus();
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
<STYLE type=text/css>

A { COLOR: blue; FONT-FAMILY: verdana,arial,helvetica,sans-serif; FONT-SIZE: 11px; TEXT-DECORATION: none
}

A:hover { COLOR: red; TEXT-DECORATION: none
}
</style>
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC" topmargin="0" onLoad="muestra_cuadro()">
<form name="form1" method="Post" action="grabar_ingreso.php"> 
<center>
    <table width="663" border="0" align="center" cellpadding="2" cellspacing="2">
      <tr> 
        <td width="655" align="center" bgcolor="#6699FF"><font color="#FFFFFF" size="4"><strong>RESPONDER 
          CON DOCUMENTO</strong></font></td>
      </tr>
    </table>
	
    <table width="659" border="0" bgcolor="#ECE9D8">
	
      <tr> 
        <td width="649" bgcolor="#E6EEFF">
		   <table width="652" border="1" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="644"  bgcolor="#cadbff"> 
                <table width="100%" border="0" cellspacing="1" cellpadding="2">
                  <tr bgcolor="#e6eeff"> 
                    <td height="15" colspan="6"><font color="#7777FF"><strong>INFORMACION 
                      DOCUMENTO DE REFERENCIA</strong></font><font color="#AA291C">&nbsp;</font></td>
                  </tr>
                  <tr> 
                    <td width="127" height="15"><font color="#804040"><b>Tipo 
                      de Docto</b> </font></td>
                    <td width="169" height="15" > <font color="#804040"><? echo $reg_ref[desc_tipo_documento]; ?> 
                      </font></td>
                    <td width="69" height="15"><font color="#804040"><strong>N&ordm; 
                      Interno</strong> <b></b></font></td>
                    <td height="15"> <font color="#804040"><font color="#804040"><? echo $reg_ref[num_interno];?></font> 
                      </font></td>
                    <td height="15"><font color="#804040"><b>Medio</b></font></td>
                    <td height="15"><font color="#804040"> 
                      <? 
                If($reg_ref["medio"]=="P")
                {
		   		echo "Papel";
				}
				else
				if ($reg_ref["medio"]=="C")
				{
		   		echo "Copia";
		 		}
				else
				if ($reg_ref["medio"]=="F")
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
                    <td width="127" height="18"><font color="#804040"><b>Fecha 
                      Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></font></td>
                    <td width="169" height="18"> <font color="#804040"> 
                      <?php $fec_doc=strtotime($reg_ref["fecha_documento"]);
		             $fech_doc=date("d/m/Y",$fec_doc);
     				echo $fech_doc;?>
                      </font></td>
                    <td width="69" height="18"><font color="#804040"><b>N&ordm; 
                      Oficial<font size="4" face="Arial"> </font></b></font></td>
                    <td width="83" height="18"> <font color="#804040"><?php echo $reg_ref[num_oficial];?> 
                      </font></td>
                    <td width="49"><font color="#804040"><b>Original</b></font></td>
                    <td width="113"><font color="#804040"><b><font color="#804040"><?echo $rs[original];?></font></b></font></td>
                  </tr>
                </table>
                <table width="100%" border="0" cellpadding="2" cellspacing="1">
                  <tr valign="middle"> 
                    <td width="127" height="18"><font color="#804040"> <b>Estado 
                      del Tr&aacute;mite</b> </font></td>
                    <td width="169" height="18"><font color="#804040"><?echo $reg_ref[desc_estado_tramite];?><b></b></font></td>
                    <td width="70" height="18"> <font color="#804040"><strong>N&ordm; 
                      Externo </strong></font></td>
                    <td width="254" height="18"><font color="#804040"><font size="4" face="Arial"> 
                      </font><font color="#804040"> </font><font color="#804040"><? echo $reg_ref[num_externo]; ?></font><font size="4" face="Arial"> 
                      </font></font></td>
                  </tr>
                </table>
                <table width="100%" border="0" cellspacing="1" cellpadding="2">
                  <tr> 
                    <td width="126" height="18"><font color="#804040"><b>Procedencia</b></font></td>
                    <td width="170" height="20"> <font color="#804040"><? echo $reg_ref[procedencia];?> 
                      </font><font color="#804040">&nbsp;</font> <font color="#804040"> 
                      <font color="#804040"> </font> </font></td>
                    <td width="70"><font color="#804040"><b>Funcionario</b></font></td>
                    <td width="254"><font color="#804040"><? echo $reg_ref[nombre_procedencia];?></font></td>
                  </tr>
                </table>
                <table width="100%" border="0" cellpadding="2" cellspacing="1">
                  <tr> 
                    <td width="126" height="18"><font color="#804040"><b>Materia</b> 
                      </font></td>
                    <td width="504"> <font color="#804040"> <? echo $reg_ref[materia];?> 
                      </font></td>
                  </tr>
                </table>
                </td>
            </tr>
          </table>
          
          <table width="100%" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="626"><strong><font color="#804040">IDENTIFICACION DEL 
                DOCUMENTO NUEVO</font></strong></td>
            </tr>
          </table>
          <table width="100%" border="1" cellpadding="1" cellspacing="0" bgcolor="#E6EEFF">
            <tr> 
              <td width="627" align="center" bgcolor="#E6EEFF"> <div align="center"> 
                  <table width="100%" border="0" cellspacing="0" cellpadding="1">
                    <tr> 
                      <td width="99"><font size="2">Tipo de Docto</font> </td>
                      <td width="132"> <select name="Cbo_Tipo_Docto" id="select2">
                          <?
				   while($reg=mssql_fetch_array($rs_tipo_docto)){
				?>
                          <option value=<? echo $reg[id_tipo_documento] ?> ><? echo $reg[desc_tipo_documento] ?></option>
                          <?
}
?>
                        </select> </td>
                      <td width="108">Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                      <td width="277"><font face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_doc" type="text" class="entradas" id="Txt_fecha_doc" value="<?=$fecha_x?>" size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                        </font></td>
                    </tr>
                    <tr> 
                      <td>Estado</td>
                      <td><p><strong>Documento Nuevo</strong></p></td>
                      <td>Original 
                        <input name="Original" type="checkbox" value="S" checked></td>
                      <td>Medio 
                        <select name="Cbo_Medio" id="select4">
                          <option value="P" <?php if($Cbo_Medio=="P") { echo 'SELECTED'; } ?> >Papel</option>
                          <option value="C" <?php if($Cbo_Medio=="C") { echo 'SELECTED'; } ?> >Correo</option>
                          <option value="V" <?php if($Cbo_Medio=="V") { echo 'SELECTED'; } ?> >Video</option>
                          <option value="F" <?php if($Cbo_Medio=="F") { echo 'SELECTED'; } ?> >Fax</option>
                        </select> </td>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellpadding="1" cellspacing="0">
                    <tr> 
                      <td width="16%"><strong>N&uacute;meros : </strong></td>
                      <td width="9%">Oficial<font size="4" face="Arial">&nbsp;</font></td>
                      <td width="13%"><font size="4" face="Arial"> 
                        <input name="TxtOficial2" type="text" class="entradas" id="TxtOficial24" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                        </font></td>
                      <td width="10%">Externo<font size="4" face="Arial">&nbsp;</font></td>
                      <td width="52%"><font size="4" face="Arial"> 
                        <input name="TxtExterno2" type="text" class="entradas" id="TxtExterno22" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                        </font></td>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellpadding="1" cellspacing="0">
                    <tr> 
                      <td width="10%"><strong>Materia</strong> </td>
                      <td width="60%"><textarea name="TxtMateria" cols="70" rows="3" onBlur="valida_campo();"></textarea> 
                        <div id="Layer1" style="position:absolute; width:229px; height:142px; z-index:1; left: 308px; top: 263px; visibility: hidden; overflow: auto;"> 
                          <table width="100%" border="1" cellpadding="1" cellspacing="0" bgcolor="#E6EEFF">
						  <tr> 
                              <td height="25"> 
                                <div align="center" onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide')"><strong>Aceptar</strong></div></td>
                            </tr>
                            <tr> 
                              <td height="116"> 
                                <?php 
							  $k=0;
							  while($reg_servicio = mssql_fetch_array($rs_servicio)) { ?>
                                <input type="checkbox" name="casilla" value="<?php echo $reg_servicio["id_descriptor"];?>" onClick="javascript:muestra(<?php echo $reg_servicio["id_descriptor"];?>);"> 
                                <?php echo $reg_servicio["desc_descriptor"];?> 
                                <br> 
                                <?php } ?>
                              </td>
                            </tr>
                            <!--<tr> 
                              <td height="25"> 
                                <div align="center" onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide')"><strong>aceptar</strong></div></td>
                            </tr>-->
                          </table>
                        </div></td>
                      <td width="30%">Descriptores 
                        <input type="radio" name="radiodescriptor" value="radiobutton" onClick="MM_showHideLayers('Layer1','','show')"> 
                      </td>
                    </tr>
                  </table>
                </div></td>
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="304"><font color="#804040"><strong>TRAMITE DEL DOCUMENTO</strong></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="322"><font color="#804040"><strong>ORIGEN</strong></font></td>
              <td width="321"><font color="#804040"><strong>DESTINO</strong></font></td>
            </tr>
          </table>
          <table width="100%" border="1" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="305"><table width="305" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="128">Tipo</td>
                    <td width="164"><strong> 
                      <?php if($tipo_proc=="I") { echo "Interno";}
							else { echo "Externo";}?>
                      </strong></td>
                  </tr>
                  <tr> 
                    <td>Procedencia</td>
                    <td><strong><?php echo $reg_ref[destino];?></strong></td>
                  </tr>
                  <tr> 
                    <td>Funcionario</td>
                    <td> <strong><?php echo $reg_ref[nombre_destino];?></strong></td>
                  </tr>
                </table></td>
              <td width="308"><table width="305" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="115"><div align="center"><strong>Interno 
                        <input name="radiodestino" type="radio" onClick="javascript:destino_interno();" value="1" checked>
                        </strong></div></td>
                    <td width="183"><strong>Externo 
                      <input name="radiodestino" type="radio"  value="2" onClick="javascript:destino_externo();">
                      </strong></td>
                  </tr>
                  <tr> 
                    <td>Destinatario</td>
                    <td><font face="Arial"> 
                      <select name="Cbo_Destinatario" id="select6" onChange="javascript:cambio1();">
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
                    <td>Funcionario</td>
                    <td><font face="Arial"> 
                      <select name="Cbo_Func_Destino" id="select7" onChange="javascript:cambio3();">> 
                      </select>
                      </font></td>
                  </tr>
                </table></td>
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="0" cellpadding="1">
            <tr> 
              <td>Tipo Distribuci&oacute;n</td>
              <td><font face="Arial"> 
                <select name="Cbo_Tipo_Distribucion" id="select8">
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
                <select name="Cbo_Tipo_Compromiso" id="select10">
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
              <td><font face="Arial"> <strong>En Tr&aacute;mite</strong></font></td>
              <td>D&iacute;as Compromiso</td>
              <td><input name="TxtDias"  type="text" class="entradas" size="4" maxlength="2"></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="1" cellspacing="0">
            <tr> 
              <td><strong>Observaci&oacute;n</strong></td>
              <td><textarea name="TxtObservacion" cols="70" rows="3" id="textarea"></textarea></td>
              <td>Despachado por Oficina de Partes 
                <input name="checkofpartes2" type="checkbox" id="checkofpartes23" value="S"></td>
            </tr>
          </table>
          <table width="100%" border="0" align="center" cellpadding="2" cellspacing="2">
            <tr> 
              <td height="48" width="306"> <div align="center"> 
                  <input type="hidden" name="estado_tramite" value="<? echo 1;?>">
                  <input type="hidden" name="resuelto" value="<? echo "N";?>">
                  <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                  <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
                  <input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
                  <input type="hidden" name="iddocum" value="<? echo $iddocum;?>">
                  <input type="hidden" name="idseguim" value="<? echo $idseguim;?>">
                  <input type="hidden" name="tipo_destino" >
                  <input type="hidden" name="tipo_procedencia" value="<? echo $tipo_proc;?>">
                  <input type="hidden" name="val_procedencia" value="<? echo $id_proc;?>">
                  <input type="hidden" name="val_destino" >
                  <input type="hidden" name="val_funcionario" value="<? echo $id_func_proc;?>">
                  <input type="hidden" name="val_funcionario1" >
                  <input type="hidden" name="accion" value="<? echo $accion;?>">
                  <input type="hidden" name="arreglo" >
                  <input type="hidden" name="num_int" value="<? echo $num_int;?>">
                  <input type="hidden" name="Cbo_Estado_Docto" value="<? echo 1;?>">
                  <input type="hidden" name="responde" value="<? echo 1;?>">
                  <input type="hidden" name="txtnomina" value="<? echo $txtnomina;?>">
                  <input name="cmd_grabar" type="button" class="botones" onClick="chequear_arreglo(<?php echo $nRows?>);validar_datos();" value="Grabar">
                </div></td>
              <td width="300"><div align="center" width="310"> 
                  <input name="submit2" type="button" class="botones" onClick="javascript:despachar_datos();" value="Despachar">
                </div></td>
            </tr>
          </table></td>
      </tr>
    </table>
    
    </center>
  </form>
  <?php mssql_close($cn);?>	
</body>
</html>
