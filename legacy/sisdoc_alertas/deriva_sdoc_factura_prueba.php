<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;

$usua=$cusuario;
$xx=$idusuario;
$iddoc=$iddocum;
$idseg=$idseguim; 
$idfunc=$idfuncionario;


//echo "idfunc" . $idfunc;
//$iddoc=188;
//$idseg=26;

//echo   " &iddocum=" . $iddocum .   " &origen=" . $dedonde . " &idseguim=" . $idseguim ;
$fecha_x = date("d-m-Y");
include("dias_fijo.php");
	if ($dias_compromiso<0)
		$dias_compromiso=0;
$flag = 2 ;
$id_func_proc=0;
$id_proc=0;
$val_funcionario=0;
$val_funcionario1=0;
$dedonde=$origen;
// $txtagnor  arrastra el año  ingreado en documentos recepcionados  y $txtnomina la nomina de documentos recepcionados //

$nRowsint = mssql_num_rows($rs_dependencia);
$nRowsext = mssql_num_rows($rs_dependencia_externa);

//echo "idusu" . $idusuario . "*** usu " . $cusuario . "** acc " . $accion . "** fun " . $idfuncionario . "** docu " . $iddocum . "** seg " . $idseguim ;
$rs_documento="exec documento_referencia_factura '" . $iddocum . "','" . $idseguim . "'";
$qq = mssql_query($rs_documento,$cn); 
$rs = mssql_fetch_array($qq);


// para buscar tipo de documento a desplegar  
$docu="exec documento_referencia '" . $iddocum . "','" . $idseguim . "'";
$q = mssql_query($docu,$cn); 
$rd = mssql_fetch_array($q);
 if ($rd[desc_tipo_documento]<>'')
     { $tipo=$rd[desc_tipo_documento];}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?php $iddocum; ?>< Edicion></title>
<script language="JavaScript" type="text/javascript">

var grabaok="<?php echo $grabado; ?>";
var sw_ok;
var sw_multiple = 0;
var cont_arreglo;
var cont_arreglo1;
var z=0;
var arreglo2 ="";
var arreglo1="";
var arreglo3="";
var ar_descrip =new Array();

function CheckLength(length) {
if (window.event.srcElement.value.length >= length) {
   alert('El Máximo de caracteres es  250');
   return false;                         
}
}

function ver_destino()
{

if(document.form1.radiodestino[0].checked==true )
	{
	document.form1.tipo_destino.value ="I";
	document.form1.Cbo_Func_Destino.disabled=true;
	document.form1.Cbo_Destinatario.disabled=true;
	MM_showHideLayers('LayerInt','','show');
	}
else
if  (document.form1.radiodestino[1].checked==true)
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
 {ar_descrip[z]= cod;
 z=z+1;
 }
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
	//alert("arreglo" + document.form1.arregloint.value);
	cont_arreglo1 = x;
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
}


function carga() {
  if (grabaok=="1"){
  
  alert(" Documento Derivado");}
  var proc= <?php echo $origen; ?>;
  var iddoc= <?php echo $iddocum; ?>;
  var idseg =<?php echo $idseguim; ?>;
  var nu =<?php echo $num_int;?>;
  var tipo_docu ="<? echo $tipo ;?>";
 // if ( proc==43) // se saca parq eu cada departamento pueda ingresar memo pero el seguimiento despues se sigue por el modulo de factura .
   //{
      var l =0;
      Respuesta=confirm("¿Desea generar documento para obtener correlativo? ");
	  var numint=0;
	  var f=8;
      if (Respuesta==true) 
	    {
	     document.form1.action="ingreso_documento_refrendacion.php?num_int="+numint + "&flujook=" +f + "&iddocum=" +iddoc + "&idseguim=" +idseg+"&origen="+ proc;
		 
         document.form1.submit();
         }
		  if (nu != "" && nu!= null && nu !=0)
				{    				
				datos ="Se adjunta "+  tipo_docu + " Nº "+ nu;
				document.form1.TxtObservacion.value = datos;
				}		 
   //}

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
  var selindice, nuevasel;
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
var doc =<?php echo $iddoc;?>;
if (p3==0 && sw_multiple==0)
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

//var nomina="<?php echo $txtnomina ;?>"
var nomina=1;
//var procedencia = "<?php echo $rs[destino]; ?>"
 
var procedencia = "<?php echo substr($rs[destino],0,35); ?>";
var agno = "<?php echo $txtagnor ;?>";
var selindice = document.form1.Cbo_Destinatario.selectedIndex;
var nuevo_destino = document.form1.Cbo_Destinatario.options[selindice].text;
  if (procedencia==nuevo_destino) {
      Respuesta=confirm("Desea generar Nómina para Despachar ");
      if (Respuesta==true) { 
         nomina=1; 
         }
         else
         {
         nomina=0;
        }
   }

 document.form1.action="graba_der_sindoc_factura.php?nom="+nomina + "&flag =" +document.form1.flag.value+ "&txtagnor =" + agno;
  document.form1.submit();
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
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
	document.form1.Cbo_Func_Destino.disabled=false;
	// envia mensaje para recordar al usuario que debe siempre indicar el destino que realmente corresponde cuando va fuera  y no como oficina de partes 
	if (nuevasel ==6)
	  { 		
	      Respuesta=confirm("¿El destino  final es externo?  ");
          if (Respuesta==true) 
          { 
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
	//parent.frames[0].location.href="frame_sup.php?cod_dep="+ p1+"&des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;	
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
	//parent.frames[0].location.href="frame_sup.php?des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;	
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
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+ p2+"&sw="+valor+"&fw="+p3;
//	parent.frames[0].location.href="frame_sup.php?cod_dep="+d2+"&sw="+valor+"&xw="+d3;
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
	document.form1.Cbo_Destinatario.disabled=false;

	}
}

function destino_interno()
{
var selindice, nuevalsel;
var valor="I";
nuevasel= 0;
selindice = document.form1.Cbo_Destinatario.selectedIndex;
top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+ nuevasel+"&sw="+valor;
document.form1.Cbo_Destinatario.disabled=false;
document.form1.Cbo_Func_Destino.disabled=false;
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
	document.form1.Cbo_Func_Procedencia.disabled=true;
	}
}
function despachar_datos() 
{
	document.form1.action="multi_pages_facturas.php";
   	document.form1.submit();
} 
function ambos_destinos()
{
var otro =<?php echo $flag;?>;
if (document.form1.txtambos.checked )
  { document.form1.flag.value =1;  }
  else
   {document.form1.flag.value=otro; }
 
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!---
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
<link href="../css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC" topmargin="0" onLoad="carga()">
<form name="form1" method="post" >
<center>
    <table width="646" border="0" bgcolor="#3399FF">
      <tr>
      <td width="638"><div align="center"><font color="#FFFFFF" size="4"><b>DERIVAR SIN DOCUMENTO</b></font></div></td>
    </tr>
  </table>
  
    <table width="644" border="1" cellpadding="0" cellspacing="0" bgcolor="#ECE9D8">
      <tr> 
        <td width="640"  bgcolor="#e6eeff"> 
        <tr> 
        <td bgcolor="#cadbff"> <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr bgcolor="#e6eeff"> 
              <td height="15" colspan="4"><font color="#7777FF"><strong>INFORMACION 
                DOCUMENTO DE REFERENCIA</strong></font></td>
            </tr>
            <tr> 
              <td width="134" height="15"><font color="#804040"><b>Tema_factura</b></font></td>
              <td width="151" height="15" > <font color="#804040"><? echo $rs[desc_tema]; ?> 
                </font></td>
              <td width="84" height="15"><font color="#804040"><strong>N&ordm; 
                Factura</strong></font></td>
              <td height="15"> <font color="#804040"><font color="#804040"><? echo $rs[num_factura];?></font> 
                </font></td>
            </tr>
            <tr> 
              <td width="134" height="18"><font color="#804040"><b>Fecha Factura</b></font></td>
              <td width="151" height="18"> <font color="#804040"> 
                <?php $fec_doc=strtotime($rs["fecha_factura"]);
		             $fech_doc=date("d/m/Y",$fec_doc);
     				echo $fech_doc;?>
                </font></td>
              <td width="84" height="18"><font color="#804040"><b>Proveedor</b></font></td>
              <td width="250" height="18"> <font color="#804040"><?php echo $rs[razon_social];?> 
                </font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr valign="middle"> 
              <td width="137" height="18"><font color="#804040"> <b>Estado del 
                Tr&aacute;mite</b> </font></td>
              <td width="156" height="18"><font color="#804040"><? echo $rs[desc_estado_tramite];?><b></b></font></td>
              <td width="76" height="18"> <font color="#804040"><strong>Monto</strong></font></td>
              <td width="250" height="18"><font color="#804040"><font size="4" face="Arial"> 
                </font><font color="#804040"> </font><font color="#804040"><? echo $rs[monto]; ?></font><font size="4" face="Arial"> 
                </font></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr> 
              <td width="136" height="18"><font color="#804040"><b>Procedencia</b></font></td>
              <td width="155" height="20"> <font color="#804040"><? echo $rs[procedencia];?> 
                </font><font color="#804040">&nbsp;</font> <font color="#804040"> 
                <font color="#804040"> </font> </font></td>
              <td width="78"><font color="#804040"><b>Funcionario</b></font></td>
              <td width="250"><font color="#804040"><? echo $rs[funcproced];?></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr> 
              <td width="136" height="18"><font color="#804040"><b>Descripci&oacute;n</b></font></td>
              <td width="493"> <font color="#804040"> <? echo $rs[descripcion];?> 
                </font></td>
            </tr>
          </table></td>
      </tr>
      <!--/table!--></tr>
      <tr> 
        <td align="center" bgcolor="#E6EEFF"> <table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="304" height="19"><font color="#800000"><strong>TRAMITE 
                DEL DOCUMENTO</strong></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="304"><font color="#800040"><strong>ORIGEN</strong></font></td>
              <td width="309"><font color="#800040"><strong>DESTINO</strong></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="1" cellspacing="0">
            <tr> 
              <td> <table width="100%" border="1" cellspacing="0" cellpadding="0">
                  <tr> 
                    <td width="305" height="93"> <table width="100%" border="0" cellspacing="1" cellpadding="1">
                        <tr> 
                          <td width="128"> 
                            <?php if($rs[tipo_procedencia]=="I"){
					       			echo "<b>Interno</b>";}
					      			else{
					       			echo "<b>Externo</b>";} 
					        ?>
                          </td>
                        </tr>
                        <tr> 
                          <td>Procedencia</td>
                          <td><? echo $rs[destino]?></td>
                        </tr>
                        <tr> 
                          <td>Funcionario</td>
                          <td><? echo $rs[funcdestino]?></td>
                        </tr>
                      </table></td>
                    <td width="308" height="93"> <table width="100%" border="0" cellspacing="1" cellpadding="1">
                        <tr>
                        <!-- variable destino viene del programa de l grabar  que trae el ultimo  destino que se ha seleccionado en caso que sea con opcion de ambos -->
 
                               <td width="94" height="22"> <div align="left"><strong>Interno
                             <input name="radiodestino" type="radio"  value="1" checked  onClick="javascript:destino_interno();">
                              </strong></div></td>
                          <td width="107"><strong>Externo 
                              <input name="radiodestino" type="radio"  value="2" onClick="javascript:destino_externo();">
	            </strong> </td>
                              <td width="107"><strong> 
                            <input type="button" name="boton" value="Múltiple" onClick="ver_destino();">
                            </strong></td>
                            <td width="141" height="24"> <strong>Ambos destinos</strong> </td>
                          <td width="107"><strong>
                           <input type="checkbox" name="txtambos" value="checkbox" onClick="ambos_destinos()" >
                           </strong></td>
                         
                        </tr>
                        <tr> 
                          <td height="20">Destinatario </td>
                          <td height="20" colspan="2"><font face="Arial"> 
                            <select name="Cbo_Destinatario" id="select2" onChange="javascript:cambio1();">
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
                          <td colspan="2"><font face="Arial"> 
                            <select name="Cbo_Func_Destino" id="select3" onChange="javascript:cambio3();">
                            </select>
                            </font> </td>
                        </tr>
                      </table></td>
                  </tr>
                </table>
                <table width="100%" border="0" cellspacing="0" cellpadding="1">
                  <tr> 
                    <td width="144">Tipo Distribuci&oacute;n</td>
                    <td width="174"> <font face="Arial"> 
                      <select name="Cbo_Tipo_Distribucion" id="select4">
                        <? while($reg_distribucion=mssql_fetch_array($rs_distribucion)){?>
                        <option value=<? echo $reg_distribucion[id_tipo_distribucion] ?> > 
                        <? echo $reg_distribucion[desc_tipo_distribucion] ?> </option>
                        <? }?>
                      </select>
                      </font></td>
                    <!--<td width="136">Tipo Compromiso</td>
                    <td width="186"> <font face="Arial"> 
                      <select name="Cbo_Tipo_Compromiso" id="select5">
                        <?
				//	while($reg_tipo_compromiso=mssql_fetch_array($rs_tipo_compromiso)){
					?>
                        <option value=<?// echo $reg_tipo_compromiso[id_tipo_compromiso] ?> > 
                        <? //echo $reg_tipo_compromiso[desc_tipo_compromiso] ?> 
                        </option>
                        <?
					//}
					?>
                      </select>
                      </font></td> -->
					  <input type="hidden" name="Cbo_Tipo_Compromiso" value="1">
					  <td width="136">Días Compromiso</td>
					  <td width="186"><input name="TxtDias" type="text"  size="2" maxlength="2" value="<?php echo $dias_compromiso ?>"> </td><!-- onBlur="valida_digito(this.value,this,2);" -->
                  </tr>
                  <tr> 
                    <td>Estado Compromiso</td>
                    <td> <font face="Arial"> <strong>En Tr&aacute;mite</strong></font></td>
                    <td></td>
                    <td>
                      <div id="LayerInt" style="position:absolute; width:281px; height:173px; z-index:1; left: 179px; top: 123px; visibility: hidden; overflow: auto;"> 
                        <table width="100%" border="1" bgcolor="#E6EEFF">
                        <tr> 
                            <td height="23"> <div align="center" onClick="MM_showHideLayers('LayerInt','','hide');MM_showHideLayers('LayerInt','','hide');ver_check(<?php echo $nRowsint;?>)"><strong>Aceptar</strong></div></td>
                          </tr>
                          <tr> 
                           <td height="159"> 
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
                      <div id="LayerExt" style="position:absolute; width:275px; height:191px; z-index:1; left: 182px; top: 121px; visibility: hidden; overflow: auto;"> 
                        <table width="100%" border="1" bgcolor="#E6EEFF">
                          <tr> 
                            <td height="23"> <div align="center" onClick="MM_showHideLayers('LayerExt','','hide');MM_showHideLayers('LayerExt','','hide');ver_check(<?php echo $nRowsext;?>)"><strong>Aceptar</strong></div></td>
                          </tr>
                       
                          <tr> 
                            <td height="159"> 
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
                <table width="100%" border="0">
                  <tr> 
                    <td><strong>Observaci&oacute;n</strong></td>
                    <td><textarea name="TxtObservacion"   cols="70" rows="3" id="textarea2" onKeyPress="return CheckLength(250)"></textarea></td>
                    <td>Despachado por Oficina de Partes 
                      <input name="checkofpartes22" type="checkbox" id="checkofpartes222" value="S"></td>
                  </tr>
                </table></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="1" cellspacing="0">
            <tr> 
              <td height="48" width="306"> <div align="center"> 
                  <input type="hidden" name="idsegu"         	value="<? echo $idseg; ?>">
                  <input type="hidden" name="iddocu" 			value="<? echo $iddoc; ?>">
                  <input type="hidden" name="estado_tramite" 	value="<? echo 1;?>">
                  <input type="hidden" name="idusuario" 		value="<? echo $xx;?>">
                  <input type="hidden" name="cusuario" 			value="<? echo $cusuario;?>">
                  <input type="hidden" name="idfuncionario" 	value="<? echo $idfunc;?>">
                  <input type="hidden" name="tipo_proc"  		value= "<? echo $rs[tipo_destinatario];?>">
                  <input type="hidden" name="val_procedencia"  	value="<? echo  $rs[id_destino];?>">
                  <input type="hidden" name="val_funcionario"  	value="<? echo $rs[rut_destino];?>">
                  <input type="hidden" name="tipo_destino">
                  <input type="hidden" name="val_destino" >
                  <input type="hidden" name="val_funcionario1" >
                  <input type="hidden" name="checkofpartes2">
                  <input type="hidden" name="arregloint">
                  <input type="hidden" name="arregloext">
                  <input type="hidden" name="Cbo_Estado_Docto"  value="<? echo 1;?>">                  
                  <input type="hidden" name="origen" 			value="<? echo $dedonde;?>">
                  <input type="hidden" name="txtnomina" 		value="<? echo $txtnomina;?>">
                  <input type="hidden" name="txtagnor" 			value="<? echo $txtagnor;?>">
                  <input type="hidden" name="flag" 				value="<? echo 1;?>">    
                   
                  <input name="cmd_grabar" type="button" class="botones" onClick="chequear_arregloint(<?php echo $nRowsint?>);chequear_arregloext(<?php echo $nRowsext?>);validar_datos();" value="Grabar">
                </div></td>
              <td width="300"> <div align="center" width="310"> 
                  <input name="submit2" type="button" class="botones" onClick="javascript:despachar_datos();" value="Despachar">
                </div></td>
            </tr>
          </table></td>
      </tr>
    </table>
  </center>
  </form>
</body>
</html>
