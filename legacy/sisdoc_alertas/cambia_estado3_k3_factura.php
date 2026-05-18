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

$fechai =$fecha_ini;
$fechaf =$fecha_fin;
/*echo "interno" . $TxtInterno . "externo" . $TxtExterno . $oficial . $TxtOficial . "<br>";
echo "fecha_ini"  . $fecha_ini. "fecha_fin"  . $fecha_fin.  "Cbo_Tipo_Docto"  . $Cbo_Tipo_Docto ."<br>";
echo "tipo procedencia "  . $tipo_procedencia. "tipo_destino"  . $tipo_destino.  "Cbo_Procedencia"  . $Cbo_Procedencia . "<br>";
echo  "CboDestinatario" . $CboDestinatario . "TxtMateria" . $TxtMateria ."<br>";
*/
	// parametros que vienen // 
$tipodocto=$Cbo_Tipo_Docto;
$interno=$TxtInterno;
$oficial =$TxtOficial;
$externo=$TxtExterno;
$materia=$TxtMateria;
$descrip=$desc;
$cboprocedencia =$Cbo_Procedencia;
$cbodestinatario=$CboDestinatario;
$tipoprocedencia=$tipo_procedencia;
$tipodestino=$tipo_destino;


// fin parametros// 
$fecha_x = date("d-m-Y");
$nRowsint = mssql_num_rows($rs_dependencia);
$nRowsext = mssql_num_rows($rs_dependencia_externa);

$rs_documento="exec documento_referencia_factura '" . $iddoc . "','" . $idseg . "'";
$qq = mssql_query($rs_documento,$cn); 
$rs=mssql_fetch_array($qq);

//echo "documento " .   $iddoc . " seguimiento   "  . $idseg;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Reenvia  TRAMITE DE DOCUMENTOS</title>


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
var funproc='<?php echo $rs[funcproced];?>';
var procedenc=<?php echo $rs[id_procedencia];?>;

function CheckLength(length) {
if (window.event.srcElement.value.length >= length) {
   alert('El Máximo de caracteres es  250');
   return false;                         
}
}

function ver_destino()
{
if  (document.form1.radiodestino[0].checked==true )
    {
	document.form1.tipo_destino.value ="I";
	document.form1.Cbo_Func_Destino.disabled=true;
	document.form1.Cbo_Destinatario.disabled=true;
	MM_showHideLayers('LayerInt','','show');
	}
else
if  (document.form1.radiodestino[1].checked==true )
	{
	document.form1.tipo_destino.value="E";
	document.form1.Cbo_Func_Destino.disabled=true;
	document.form1.Cbo_Destinatario.disabled=true;
	MM_showHideLayers('LayerExt','','show');
	}
document.form1.val_destino.value=0;
document.form1.val_funcionario1.value=0;
sw_multiple = 1;	
}

function muestra(cod)
{
z=0;
 ar_descrip[z]= cod;
 z=z+1;
 
}       
	  
function ver_destino()
{
if  (document.form1.radiodestino[0].checked==true )
    {
	document.form1.tipo_destino.value ="I";
	document.form1.Cbo_Func_Destino.disabled=true;
	document.form1.Cbo_Destinatario.disabled=true;
	MM_showHideLayers('LayerInt','','show');
	}
else
if  (document.form1.radiodestino[1].checked==true )
	{
	document.form1.tipo_destino.value="E";
	document.form1.Cbo_Func_Destino.disabled=true;
	document.form1.Cbo_Destinatario.disabled=true;
	MM_showHideLayers('LayerExt','','show');
	}
document.form1.val_destino.value=0;
document.form1.val_funcionario1.value=0;
sw_multiple = 1;	
}
function ver_check(filas) 
{
  var x=0;
  if(document.form1.radiodestino[0].checked==true)
  {
  for (k=0;k<filas;k++)
  {
     if (document.form1.casilla2[k].checked)
     {
	  x=x+1;
	 }
  }
  }
  else
  if(document.form1.radiodestino[1].checked==true)
  {
  for (k=0;k<filas;k++)
  {
     if (document.form1.casilla3[k].checked)
     {
	 x=x+1;
	 }
  }
  }
  if (x!=0)
  {	
	document.form1.Cbo_Func_Destino.disabled=true;
	document.form1.Cbo_Destinatario.disabled=true;
  }
  else
  {
	document.form1.Cbo_Func_Destino.disabled=false;
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
    document.form1.arreglo.value=x + "@" + arreglo2;
	cont_arreglo = x;
	
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
	//alert("arregloint  "+document.form1.arregloint.value);
}
function chequear_arregloext(filas) 
{
  var x=0;
  arregloext="";
  arreglo3="";
  for (k=0;k<filas;k++)
  {
     if (document.form1.casilla3[k].checked)
     {
       arreglo3=arreglo3+document.form1.casilla3[k].value+"@";
      x=x+1;
	  }
  }
	document.form1.arregloext.value=x + "@" + arreglo3;
	cont_arreglo1 = x;
	//alert("arregloext  "+document.form1.arregloext.value);
}
 
function despachar_datos() 
{
	document.form1.action="multi_pages_facturas.php";
   	document.form1.submit();
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

function cambio1()
{
var selindice, nuevalsel;
var valor="F";

if (document.form1.radiodestino[0].checked==true)
	{

	selindice = document.form1.Cbo_Destinatario.selectedIndex;
	nuevasel = document.form1.Cbo_Destinatario.options[selindice].value;
	document.form1.val_destino.value= nuevasel;
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
	document.form1.Cbo_Func_Destino.disabled=false;
	}
else
if (document.form1.radiodestino[1].checked==true){
	document.form1.val_destino.value= document.form1.Cbo_Destinatario.selectedIndex;}
	document.form1.val_funcionario1.value=0;
	
}

function cambio3()
{
var selindice, nuevalsel;
var valor="F";
selindice = document.form1.Cbo_Func_Destino.selectedIndex;
nuevasel  = document.form1.Cbo_Func_Destino.options[selindice].value;
document.form1.val_funcionario1.value=selindice;
}
function validar_datos()
{
sw_ok=true; 

if(document.form1.Cbo_Destinatario.options.value==0 && sw_multiple ==0)
  {
 	sw_ok=false;
	alert("Falta Ingresar el Destinatario del Documento");
	document.form1.Cbo_Destinatario.focus();
  }
else
if  (document.form1.radiodestino[0].checked==true)
{
//if(document.form1.Cbo_Procedencia.options.value==document.form1.Cbo_Destinatario.options.value)
if(procedenc==document.form1.Cbo_Destinatario.options.value)
  {
//	if(document.form1.Cbo_Func_Procedencia.options.value==document.form1.Cbo_Func_Destino.options.value)
		if(funproc==document.form1.Cbo_Func_Destino.options.value)
{	
		sw_ok=false;
		alert("El Funcionario de Procedencia debe ser distinto al de Destino");
		//document.form1.Cbo_Func_Procedencia.focus();
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
	document.form1.val_funcionario1.value="";
}

if(document.form1.Cbo_Func_Destino.selectedIndex==0)
{document.form1.val_funcionario1.value=0;}
	
if (sw_ok)
{
	document.form1.submit();
}
}

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

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC" topmargin="0"  >
<center>
<form name="form1" method="post"   action="graba_docto2_k_facturas.php">
    <table width="650" border="1" cellpadding="2" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td><div align="center"><font color="#FFFFFF" size="4"><b>REENVIO DE /TRAMITE 
            DE DOCUMENTOS</b></font></div></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#e6eeff">
      <tr> 
        <td bgcolor="#cadbff"> <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr bgcolor="#e6eeff"> 
              <td height="15" colspan="4"><font color="#7777FF"><strong>INFORMACION 
                DOCUMENTO DE REFERENCIA</strong></font></td>
            </tr>
            <tr> 
              <td width="130" height="15"><font color="#804040"><b>Tema factura</b> 
                </font></td>
              <td width="169" height="15" > <font color="#804040"><? echo $rs[desc_tema]; ?> 
                </font></td>
              <td width="73" height="15"><font color="#804040"><strong>N&ordm; 
                Factura</strong> <b></b></font></td>
              <td height="15"> <font color="#804040"><font color="#804040"><? echo $rs[num_factura];?></font> 
                </font></td>
            </tr>
            <tr> 
              <td width="130" height="18"><font color="#804040"><b>Fecha Factura<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></font></td>
              <td width="169" height="18"> <font color="#804040"> 
                <?php $fec_doc=strtotime($rs["fecha_factura"]);
		             $fech_doc=date("d/m/Y",$fec_doc);
     				echo $fech_doc;?>
                </font></td>
              <td width="73" height="18"><font color="#804040"><b>Proveedor<font size="4" face="Arial"> 
                </font></b></font></td>
              <td width="251" height="18"> <font color="#804040"><?php echo $rs[razon_social];?> 
                </font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr valign="middle"> 
              <td width="130" height="34"><font color="#804040"> <b>Estado del 
                Tr&aacute;mite</b> </font></td>
              <td width="170" height="34"><font color="#804040"><? echo $rs[desc_estado_tramite];?><b></b></font></td>
              <td width="71"><font color="#804040"><b>Monto</b></font></td>
              <td width="252"><font color="#804040"><font color="#804040"><? echo $rs[num_factura];?></font></font></td>
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
              <td width="128" height="22"><font color="#804040"><b>Descripción</b> 
                </font></td>
              <td width="505"> <font color="#804040"> <? echo $rs[descripcion];?> 
                </font></td>
            </tr>
          </table></td>
      </tr>
    </table>
		  
          
    <table width="67%" border="1" bgcolor="e6eeff">
      <tr> 
              
        <td height="32"><font color="#7777FF"><strong>TRAMITE SOLICITADO</strong></font></td>
            </tr>
          </table>
          
    <table width="650" border="0" cellpadding="1" cellspacing="1" bgcolor="e6eeff">
      <tr> 
        <td width="313"><font color="#804040"><strong>ORIGEN</strong></font></td>
        <td width="330"><font color="#804040"><strong>DESTINO</strong></font></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor ="e6eeff">
      <tr> 
        <td width="311"><table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="88">Procedencia</td>
              <td width="216"><font face="Arial">&nbsp; </font><font color="#804040"><? echo $rs[procedencia];?></font><font face="Arial">&nbsp; </font></td>
            </tr>
            <tr> 
              <td width="88">Funcionario</td>
			  <td width="216"> <font color="#804040"><? echo $rs[funcproced];?></font></td>
			</tr>
          </table></td>
        <td width="327"><table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="105"> <div align="center"><strong>Interno 
                  <input name="radiodestino" type="radio" onClick="javascript:destino_interno();" value="1" checked>
                  </strong></div></td>
              <td width="105"><strong>Externo 
                <input name="radiodestino" type="radio"  value="2" onClick="javascript:destino_externo();">
                </strong></td>
              <td width="130"><input type="button" name="boton2" value="Múltiple" onClick="javascript:ver_destino();"></td>
            </tr>
            <tr> 
              <td width="105">Destino</td>
              <td colspan="2"><font face="Arial"> 
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
              <td width="105" height="42">Funcionario</td>
              <td colspan="2"><font face="Arial"> 
                <select name="Cbo_Func_Destino" class="combo" id="select" onChange="javascript:cambio3();">
                  <option value="0"> </option>
                </select>
                </font></td>
            </tr>
          </table></td>
      </tr>
    </table>
                
    <table width="650" border="0" cellspacing="1" cellpadding="1" bgcolor="e6eeff">
      <tr> 
        <td width="22%">Tipo Distribuci&oacute;n</td>
        <td width="28%"><font face="Arial"> 
          <select name="Cbo_Tipo_Distribucion" class="combo" id="select2">
            <?
		while($reg_distribucion=mssql_fetch_array($rs_distribucion)){
?>
            <option value=<? echo $reg_distribucion[id_tipo_distribucion] ?> ><? echo $reg_distribucion[desc_tipo_distribucion] ?></option>
            <?
}
?>
          </select>
          </font></td>
        <td width="20%">Tipo Compromiso</td>
        <td width="30%"><font face="Arial"> 
          <select name="Cbo_Tipo_Compromiso" class="combo" id="select3">
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
        <td><div align="left"><font face="Arial"><strong> En Trámite</strong></font></div></td>
        <td>D&iacute;as Compromiso</td>
        <td><input name="TxtDias"  type="text" class="entradas" onBlur="valida_digito(this.value,this,2);" size="2" maxlength="2"> 
          <div id="LayerInt" style="position:absolute; width:327px; height:200px; z-index:1; left: 207px; top: 100px; visibility: hidden; overflow: auto; background-color: #E6EEFF; layer-background-color: #E6EEFF; border: 1px none #000000;" class="texto"> 
            <table width="100%" border="1" bgcolor="#E6EEFF">
              <tr> 
                <td height="32"> <div align="center" onClick="MM_showHideLayers('LayerInt','','hide');MM_showHideLayers('LayerInt','','hide');ver_check(<?php echo $nRowsint;?>)"><strong>Aceptar</strong></div></td>
              </tr>
              <tr> 
                <td height="149"> 
                  <?php 
							  $k=0;
							  while($reg_dependencia = mssql_fetch_array($rs_dependencia)) { ?>
                  <input type="checkbox" name="casilla2" value="<?php echo $reg_dependencia["id_dependencia"];?>" onClick="javascript:muestra(<?php echo $reg_dependencia["id_dependencia"];?>);"> 
                  <?php echo $reg_dependencia["desc_dependencia"]  . "<br>"; } ?> 
                </td>
              </tr>
            </table>
            <div align="right"></div>
          </div>
          <div id="LayerExt" style="position:absolute; width:341px; height:192px; z-index:1; left: 200px; top: 107px; visibility: hidden; overflow: auto;"> 
            <table width="100%" border="1" bgcolor="#E6EEFF">
              <tr> 
                <td height="27"> <div align="center" onClick="MM_showHideLayers('LayerExt','','hide');MM_showHideLayers('LayerExt','','hide');ver_check(<?php echo $nRowsext;?>)"><strong>Aceptar</strong></div></td>
              </tr>
              <tr> 
                <td height="164"> 
                  <?php 
						  	$k=0;
						  	while($reg_dependencia_externa = mssql_fetch_array($rs_dependencia_externa)) { ?>
                  <input type="checkbox" name="casilla3" value="<?php echo $reg_dependencia_externa["id_dependencia_externa"];?>" onClick="javascript:muestra(<?php echo $reg_dependencia_externa["id_dependencia_externa"];?>);"> 
                  <?php echo $reg_dependencia_externa["desc_dependencia_externa"] . "<br>"; } ?> 
                </td>
              </tr>
            </table>
            <div align="right"></div>
          </div></td>
      </tr>
    </table>
    <table width="650" border="0" cellspacing="0" cellpadding="1" bgcolor="e6eeff">
      <tr> 
        <td width="81"><strong><font size="2">Observaci&oacute;n</font></strong><br> 
        </td>
        <td width="245"><textarea name="TxtObservacion" cols="50" rows="3" class="cajatexto"  id="textarea" onkeypress="return CheckLength(250);"></textarea></td>
        <td width="318">Despachado por Oficina de Partes 
          <input name="checkofpartes22" type="checkbox" id="checkofpartes22" value="S"></td>
      </tr>
    </table>
    <table width="651" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="e6eeff">
      <tr> 
                    <td height="37" width="326"> <div align="center"> 
  				  <input type="hidden" name="idsegu" 			  value="<? echo $idseg; ?>">
                  <input type="hidden" name="iddocu" 			   value="<? echo $iddoc; ?>">
                  <input type="hidden" name="estado_tramite"    value="<? echo 1;?>">
                  <input type="hidden" name="idusuario" 			   value="<? echo $xx;?>">
                  <input type="hidden" name="cusuario" 			   value="<? echo $cusuario;?>">
                  <input type="hidden" name="idfuncionario" 	   value="<? echo $fun;?>">
                  <input type="hidden" name="tipo_proc"  		   value= "<? echo $rs[tipo_procedencia];?>">
                  <input type="hidden" name="val_procedencia"  value="<? echo  $rs[id_procedencia];?>">
                  <input type="hidden" name="val_funcionario"    value= "<? echo $rs[rut_procedencia];?>">
                  <input type="hidden" name="tipo_destino">
                  <input type="hidden" name="val_destino" >
                  <input type="hidden" name="val_funcionario1" >
                  <input type="hidden" name="checkofpartes2">
                  <input type="hidden" name="arregloint">
                  <input type="hidden" name="arregloext">
                  <input type="hidden" name="Cbo_Estado_Docto" value="<? echo 1;?>">
                  
                  
                
         <!-- variables para poder volver nuevamente a la pantalla de busqueda una vezgrabado el tramite -->
				  <input type="hidden" name="fecha_ini" 			value="<? echo $fecha_ini;?>">
				  <input type="hidden" name="fecha_fin" 		value="<? echo $fecha_fin;?>">
				  <input type="hidden" name="tipodocto" 		value="<? echo $Cbo_Tipo_Docto;?>">
				  <input type="hidden" name="interno" 			value="<? echo $TxtInterno;?>">
				  <input type="hidden" name="oficial" 				value="<? echo $TxtOficial;?>">
				  <input type="hidden" name="externo" 			value="<? echo $TxtExterno;?>">
				  <input type="hidden" name="materia" 			value="<? echo $TxtMateria;?>">
				  <input type="hidden" name="descrip" 			value="<? echo $desc;?>">
				  <input type="hidden" name="cboprocedencia" value="<? echo $Cbo_Procedencia;?>">
				  <!--input type="hidden" name="Cbo_Destinatario" value="<? echo $Cbo_Destinatario;?>"-->
				  <input type="hidden" name="tipoprocedencia" value="<? echo $tipo_procedencia;?>">
				  <input type="hidden" name="xdestinatario" 	 value="<? echo $cbodestinatario;?>">
				  <input type="hidden" name="xtipodestino" 	 value="<? echo $tipodestino;?>">
				  <input type="hidden" name="tipodestino"        value="<? echo $tipo_destino;?>">
				  <input type="hidden" name="avanzada"          value="<? echo $avanzada;?>">
				  
				  <input name="cmd_grabar" type="button" class="botones" onClick="chequear_arregloint(<?php echo $nRowsint?>);chequear_arregloext(<?php echo $nRowsext?>);validar_datos();" value="Grabar">
                  </div></td>
                    <td width="325"><div align="center" width="310"> 
                        <input name="submit2" type="button" class="botones" onClick="javascript:despachar_datos();" value="Despachar">
                      </div></td>
                  </tr>
                </table></td>
            </tr>
          </table>
          <p>&nbsp;</p></td>
      </tr>
      <tr> 
        <td> <br> </td>
      </tr>
    </table>
    </form>
  </center>
</body>
</html>
