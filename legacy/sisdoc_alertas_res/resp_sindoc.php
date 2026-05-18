<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;

$usua=$cusuario;
$xx=$idusuario;
$iddoc=$iddocum;
$idseg=$idseguim; 
$txtnomina=$txtnomina;
/*$iddoc=188;
$idseg=26;*/
$id_func_proc=0;
$id_proc=0;
$val_funcionario=0;
$val_funcionario1=0;
$fecha_x = date("d-m-Y");
//echo "idusu" . $idusuario . "*** usu " . $cusuario . "** acc " . $accion . "** fun " . $idfuncionario . "** docu " . $iddocum . "** seg " . $idseguim ;
$rs_documento="exec documento_referencia '" . $iddocum . "','" . $idseguim . "'";
$qq = mssql_query($rs_documento,$cn); 
$rs = mssql_fetch_array($qq);


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?php $iddocum; ?>< Edicion></title>


<script language="JavaScript" type="text/javascript">
var grabaok="<?php echo $grabado; ?>";

function carga() {
  if (grabaok=="1"){
  alert(" Respuesta Grabada");}
}

function enviar_datos() 
{
 if (document.form1.Cbo_Estado_Compromiso.value==2){
   alert ("Antes de archivar debe cambiar estado de compromiso");
  }
  else {
   
  document.form1.compromiso.value=document.form1.Cbo_Estado_Compromiso.value;
  document.form1.submit();
  }
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
 	  
function validar_datos()
{
var p3=document.form1.val_destino.value;
var ok ="T";
if (p3==0)
{
  ok = "F";
  grabaok="0"; 
  alert(" Falta ingresar destinatario");
}
if  (document.form1.radiodestino[0].checked==true)
   {
   	document.form1.tipo_destino.value="I";
	
   }
   else{
       if  (document.form1.radiodestino[1].checked==true)
       {
	    document.form1.tipo_destino.value="E";
    	document.form1.val_funcionario1.value=0;
	   }	
	   }
if (ok =="T"){
  document.form1.action="graba_sindoc.php";
  // document.form1.action="nada.php";
  
   document.form1.submit();
 }  
}

function pp()
{
alert("mensaje");
  document.form1.action="nada.php";
  
   document.form1.submit();
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
	// envia mensaje para recordar al usuario que debe siempre indicar el destino que realmente corresponde cuando va fuera  y no como oficina de partes 
		if (nuevasel ==6)
	  { 		
	      Respuesta=confirm("¿El destino  final es externo?  ");
          if (Respuesta==true) 
          { 
      	    alert (" Debe indicar  el destino como externo ");
            document.form1.val_destino.value= 0;
	        document.form1.Cbo_Destinatario.selectedIndex=0;
	        document.form1.Cbo_Func_Destino.options.value=0;
			document.form1.Cbo_Func_Destino.disabled=true;
			document.form1.val_funcionario.value= 0;
 			document.form1.Cbo_Destinatario.focus();
			
			}
	  }    	      
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
nuevasel = document.form1.Cbo_Func_Destino.options[selindice].value;
document.form1.val_funcionario1.value=nuevasel;
}


function muestra_cuadro() { 
  if (flujo2==0) {
  alert("El Documento ha sido grabado");
  }
  else 
  {
  	ver_combos();
  }	
  }
  
function ver_combos() { 
if(document.form1.tipo_procedencia.value=="I" && document.form1.tipo_destino.value=="I"){ 
	var valor="II";
	var d2=document.form1.val_destino.value;
	var d3=document.form1.val_funcionario1.value;
	var p2=document.form1.val_procedencia.value;
	var p3=document.form1.val_funcionario.value;
	top.window.frame_consultas.location.href="frame_consultas.php?des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;
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

function cargar_funcionario_procedencia() {	 
   	var valor="P";
	var p2=document.form1.val_procedencia.value;
	var p3=document.form1.val_funcionario.value;
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+ p2+"&sw="+valor+"&fw="+p3;
}

function cargar_funcionario_destino() {	 
  if(document.form1.val_destino.value>0)  {
   	var valor="D";
	var d2=document.form1.val_destino.value;
	var d3=document.form1.val_funcionario1.value;
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+d2+"&sw="+valor+"&xw="+d3;
  }
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
selindice = document.form1.Cbo_Destinatario.selectedIndex;
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
function despachar_datos() 
{
	document.form1.action="multi_pages.php";
   	document.form1.submit();
} 

</script>
<!--meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"-->
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
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

<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC" topmargin="0"  onLoad="carga()">
<center>
<form name="form1" method="post" >
    <table width="640" border="1" cellpadding="1" cellspacing="0">
      <tr>
        <td width="634" height="20" bgcolor="#6699FF"><div align="center"><font color="#FFFFFF" size="4"><strong> 
            RESPONDER SIN DOCUMENTO</strong></font></div></td>
      </tr>
    </table>
    <table width="634" border="1" cellpadding="1" cellspacing="0" bgcolor="#E6EEFF">
      <tr bgcolor="#ECE9D8"> 
        <td bgcolor="#cadbff"> <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr bgcolor="#e6eeff"> 
              <td height="15" colspan="6"><font color="#7777FF"><strong>INFORMACION 
                DOCUMENTO DE REFERENCIA</strong></font></td>
            </tr>
            <tr> 
              <td width="133" height="15"><font color="#804040"><b>Tipo de Docto</b> 
                </font></td>
              <td width="149" height="15" > <font color="#804040"><? echo $rs[desc_tipo_documento]; ?> 
                </font></td>
              <td width="82" height="15"><font color="#804040"><strong>N&ordm; 
                Interno</strong> <b></b></font></td>
              <td height="15"> <font color="#804040"><font color="#804040"><? echo $rs[num_interno];?></font> 
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
              <td width="133" height="18"><font color="#804040"><b>Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></font></td>
              <td width="149" height="18"> <font color="#804040"> 
                <?php $fec_doc=strtotime($rs["fecha_documento"]);
		             $fech_doc=date("d/m/Y",$fec_doc);
     				echo $fech_doc;?>
                </font></td>
              <td width="82" height="18"><font color="#804040"><b>N&ordm; Oficial<font size="4" face="Arial"> 
                </font></b></font></td>
              <td width="67" height="18"> <font color="#804040"><?php echo $rs[num_oficial];?> 
                </font></td>
              <td width="57"><font color="#804040"><b>Original</b></font></td>
              <td width="109"><font color="#804040"><font color="#804040"><? echo $rs[original];?></font></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr valign="middle"> 
              <td width="133" height="18"><font color="#804040"> <b>Estado del 
                Tr&aacute;mite</b> </font></td>
              <td width="151" height="18"><font color="#804040"><? echo $rs[desc_estado_tramite];?><b></b></font></td>
              <td width="79" height="18"> <font color="#804040"><strong>N&ordm; 
                Externo </strong></font></td>
              <td width="244" height="18"><font color="#804040"><font size="4" face="Arial"> 
                </font><font color="#804040"> </font><font color="#804040"><? echo $rs[num_externo]; ?></font><font size="4" face="Arial"> 
                </font></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr> 
              <td width="132" height="18"><font color="#804040"><b>Procedencia</b></font></td>
              <td width="152" height="20"> <font color="#804040"><? echo $rs[procedencia];?> 
                </font><font color="#804040">&nbsp;</font> <font color="#804040"> 
                <font color="#804040"> </font> </font></td>
              <td width="79"><font color="#804040"><b>Funcionario</b></font></td>
              <td width="244"><font color="#804040"><? echo $rs[funcproced];?></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr> 
              <td width="136" height="18"><font color="#804040"><b>Materia</b> 
                </font></td>
              <td width="497"> <font color="#804040"> <? echo $rs[materia];?> 
                </font></td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td width="628" height="30" align="center"> 
          <div align="left">
            <table width="100%" border="0" cellspacing="1" cellpadding="1">
              <tr> 
                <td width="621"><font color="#800000"><strong>TRAMITE DEL DOCUMENTO</strong></font></td>
              </tr>
            </table>
            <table width="100%" border="0" cellspacing="1" cellpadding="1">
              <tr> 
                <td width="304" height="17"><font color="#800040"><strong>ORIGEN</strong></font></td>
                <td width="309"><font color="#800040"><strong>DESTINO</strong></font></td>
              </tr>
            </table>
          </div>
          <table width="100%" border="1" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="305" height="89"> <table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="133"> 
                      <?php if($rs[tipo_procedencia]=="I"){
					       echo "<b>Interno</b>";}
					      else{
					       echo "<b>Externo</b>";} 
					  ?>
                    </td>
                  </tr>
                  <tr> 
                    <td>Procedencia</td>
                    <td width="172"><? echo $rs[destino]?></td>
                  </tr>
                  <tr> 
                    <td>Funcionario</td>
                    <td><? echo $rs[funcdestino]?></td>
                  </tr>
                </table></td>
              <td width="308" height="89"> <table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="115"> <div align="center"><strong>Interno 
                        <input name="radiodestino" type="radio"  value ="1" checked onClick="javascript:destino_interno();">
                        </strong></div></td>
                    <td width="184"><strong>Externo 
                      <input name="radiodestino" type="radio"  value="2" onClick="javascript:destino_externo();">
                      </strong> </td>
                  </tr>
                  <tr> 
                    <td height="20">Destinatario </td>
                    <td height="20"><font face="Arial"> 
                      <select name="Cbo_Destinatario" id="Cbo_Destinatario" onChange="javascript:cambio1();">
                        <option value="0"> </option>
                        <? while($reg_destino=mssql_fetch_array($rs_destino)){ ?>
                        <option value=<? echo $reg_destino[id_dependencia] ?> > 
                        <? echo $reg_destino[desc_dependencia] ?> </option>
                        <? }?>
                      </select>
                      </font></td>
                  </tr>
                  <tr> 
                    <td>Funcionario</td>
                    <td><font face="Arial"> 
                      <select name="Cbo_Func_Destino" id="select" onChange="javascript:cambio3();">
                      </select>
                      </font> </td>
                  </tr>
                </table></td>
            </tr>
          </table>
          <table width="100%" border="1" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="134">Tipo Distribuci&oacute;n</td>
              <td width="173"> <font face="Arial"> 
                <select name="Cbo_Tipo_Distribucion" id="select9">
                  <? while($reg_distribucion=mssql_fetch_array($rs_distribucion)){?>
                  <option value=<? echo $reg_distribucion[id_tipo_distribucion] ?> > 
                  <? echo $reg_distribucion[desc_tipo_distribucion] ?> </option>
                  <? }?>
                </select>
                </font></td>
              <td width="118">Tipo Compromiso</td>
              <td width="191"> <font face="Arial"> 
                <select name="Cbo_Tipo_Compromiso" id="select14">
                  <?
					while($reg_tipo_compromiso=mssql_fetch_array($rs_tipo_compromiso)){
					?>
                  <option value=<? echo $reg_tipo_compromiso[id_tipo_compromiso] ?> > 
                  <? echo $reg_tipo_compromiso[desc_tipo_compromiso] ?> </option>
                  <?
					}
					?>
                </select>
                </font></td>
            </tr>
            <tr> 
              <td>Estado Compromiso</td>
              <td> <font face="Arial"> <strong>En Tr&aacute;mite</strong></font></td>
              <td>D&iacute;as Compromiso</td>
              <td><input name="TxtDias" type="text"  size="2" maxlength="2" onBlur="valida_digito(this.value,this,2);"></td>
            </tr>
          </table>
          <table width="100%" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td><strong>Observaci&oacute;n</strong></td>
              <td><textarea name="TxtObservacion" cols="70" rows="3"  id="textarea2"></textarea></td>
              <td>Despachado por Oficina de Partes 
                <input name="checkofpartes22" type="checkbox" id="checkofpartes22" value="S"></td>
            </tr>
          </table>
          <table width="100%" border="0" align="center" cellpadding="2" cellspacing="2">
            <tr> 
              <td height="48" width="306"> <div align="center"> 
                  <input type="hidden" name="idsegu" value ="<? echo $idseg; ?>">
                  <input type="hidden" name="iddocu" value ="<? echo $iddoc; ?>">
                  <input type="hidden" name="estado_tramite" value="<? echo 1;?>">
                  <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                  <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
                  <input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
                  <input type="hidden" name="tipo_destino" value= "<? echo $rs[tipo_destinatario];?>">
                  <input type="hidden" name="tipo_proc"  value= "<? echo $rs[tipo_procedencia];?>">
                  <input type="hidden" name="val_procedencia"  value ="<? echo  $rs[id_destino];?>">
                  <input type="hidden" name="val_funcionario"  value = "<? echo $rs[rut_destino];?>">
                  <input type="hidden" name="val_destino"  value  "<? echo  $rs[id_procedencia];?>">
                  <input type="hidden" name="val_funcionario1"  value ="<? echo  $rs[rut_procedencia];?>">
                  <input type="hidden" name="checkofpartes2">
                  <input type="hidden" name="Cbo_Estado_Docto" value="<? echo 1;?>">
                  <input type="hidden" name="txtnomina" value="<? echo $txtnomina;?>">
                  <input type="hidden" name="txtagnor" value="<? echo $txtagnor;?>">
                  <input name="cmd_grabar" type="button" class="botones" onClick="validar_datos();" value="Grabar">
                </div></td>
              <td width="300"> <div align="center" width="310"> 
                  <input name="submit2" type="button" class="botones" onClick="javascript:despachar_datos();" value="Despachar">
                </div></td>
            </tr>
          </table></td>
      </tr>
    </table>
  </form>
  </center>
</body>
</html>
