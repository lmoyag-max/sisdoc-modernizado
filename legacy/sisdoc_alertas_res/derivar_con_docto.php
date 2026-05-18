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
//$txtnomina=$txtnomina;
// viene txtnomina y txtagnor que trae la nomina y el año elegido en documentos recepcionados //

if ($flujook!=0){
$num_int=0;}
else{
$num_int=$num_int;}
//echo "idusu" . $idusuario . "*** usu " . $cusuario . "** acc " . $accion . "** fun " . $idfuncionario . "** docu " . $iddocum . "** seg " . $idseguim ;
$id_func_proc=0;
$id_proc=0;
$val_funcionario=0;
$val_funcionario1=0;
$nRowsint = mssql_num_rows($rs_dependencia);
$nRowsext = mssql_num_rows($rs_dependencia_externa);
//echo "rowint ..." . $nRowsint . "ext  " . $nRowsext;
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
var sw_multiple = 0;


//función para popup descriptores
function fnOpen_descriptor(arr){

	var vArreglo = arr;
	var sFeatures="dialogHeight: " + 250 + "px; dialogWidth: " + 650 + "px; dialogTop: " + 150 + "px; dialogLeft: " + 200 + "px; edge: " + "raised" + "; center: " + "yes" + "; help: " + "yes" + "; resizable: " + "no" + "; status: " + "off" + ";";


	vArreglo = window.showModalDialog("descriptor.php?arr1="+vArreglo,vArreglo,sFeatures);
	document.form1.arreglo.value = vArreglo;
	
var b = document.form1.arreglo.value
var temp = new Array();
temp = b.split('@');
x = temp[0];
cont_arreglo = x;
sw_invita=0;
//document.form1.Txt_fecha_inv.value="";
 //for (k=1;k<=x;k++){
  /*if (temp[k]==13){
     sw_invita=13;
 	muestrala(1);  }
   }*/
 if (sw_invita==0) {
        muestrala(0);
    }

}


function ver_destino()
{
if  (document.form1.radiodestino[0].checked==true)
	//&& document.form1.checkmultiple.checked==true)
	{
	document.form1.tipo_destino.value ="I";
	document.form1.Cbo_Func_Destino.disabled=true;
	document.form1.Cbo_Destinatario.disabled=true;
	MM_showHideLayers('LayerInt','','show');
	}
else
if  (document.form1.radiodestino[1].checked==true)
	// && document.form1.checkmultiple.checked==true)
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
 // para que cargue los descriptores que venian de antes //
  var valor ="carga_desc";
  var registro=<?php echo $id_doc; ?>;
  parent.frames[0].location.href="frame_consultas.php? sw="+valor+ "&registro="+registro;  
   //
  if (flujo2==0)
  {
  
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
    // alert("destino externo");		
	var valor="DE";
	var d2=document.form1.val_destino.value;
	parent.frames[0].location.href="frame_consultas.php?des_d="+d2+"&sw="+valor;	
	document.form1.Cbo_Func_Destino.disabled=true;
 } 
else 
 if(document.form1.tipo_destino.value=="I"){		
 	// alert("destino interno");
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
	document.form1.Cbo_Destinatario.disabled=false;
	document.form1.Cbo_Func_Destino.disabled=true;
	}
}

function destino_interno()
{
var selindice, nuevalsel;
var valor="I";
nuevasel= 0;
parent.frames[0].location.href="frame_consultas.php?cod_dep="+ nuevasel+"&sw="+valor;
document.form1.Cbo_Destinatario.disabled=false;
document.form1.Cbo_Func_Destino.disabled=false;
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
<title>Documento sin t&iacute;tulo</title>
<script language="JavaScript" type="text/javascript">
<!--
var sw_ok;
var cont_arreglo;
var cont_arreglo1;
var z=0;
var arreglo2 ="";
var arreglo1="";
var arreglo3="";
var ar_descrip =new Array();



function muestra(cod)
{
z=0;
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

if(document.form1.Cbo_Destinatario.options.value==0 && sw_multiple==0)
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
var selindice,tipo_doc,proc,tipo_p,dest,tipo_d, similar,x,num_ofi,fec_doc;
var valor="S";
selindice = document.form1.Cbo_Tipo_Docto.selectedIndex;
tipo_doc = document.form1.Cbo_Tipo_Docto.options[selindice].value;
proc=document.form1.val_procedencia.value;
tipo_p=document.form1.tipo_procedencia.value;
dest=document.form1.val_destino.value;
tipo_d=document.form1.tipo_destino.value;
num_ofi=document.form1.TxtOficial2.value;
fec_doc=document.form1.Txt_fecha_doc.value;
top.window.frame_consultas.location.href="frame_consultas.php?sw="+valor+
"&tipo_doc="+tipo_doc+
"&proc="+proc+
"&tipo_p="+tipo_p+
"&dest="+dest+
"&tipo_d="+tipo_d+
"&num_ofi="+num_ofi+
"&fec_doc="+fec_doc;
}
}


function similar(dato)
{
    if (dato==0)
	{
	document.form1.submit();
	}
	else
	var answer=confirm("¿Existen Documentos Similares, Desea Grabar el Documento que esta ingresando?")
	if (answer)
	{
	document.form1.submit();
	//window.location.href="frame_menuvars";
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
	//document.form1.arreglo.value=x + "@" + arreglo2;
	//cont_arreglo = x;
	var b = document.form1.arreglo.value
	var temp = new Array();
	temp = b.split('@');
	x = temp[0];
	cont_arreglo = x;
	

	//alert("arreglo desc "+document.form1.arreglo.value);
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
	document.form1.action="multi_pages.php";
   	document.form1.submit();
} 
 function chequeafecha(objeto,calendario) 
{
  var campodefecha = objeto;
  if (chkfecha(objeto,calendario) == false) 
  {
     campodefecha.select();
     
     if(ano_ok) 
     {
       alert("Formato de fecha Incorrecto\r\rUtilice el siguiente formato:\r       05-10-1988\r       día-mes-año");
     }
     else
     {
        alert("Año incorrecto");
     }
     //alert("Formato de fecha Incorrecto\r\rUtilice el siguiente formato:\r       05-10-1988\r       día-mes-año");
     campodefecha.value='';
     campodefecha.focus();
     return false;
  }
  else 
  {
     return true;
  }
}
function chkfecha(objeto,cualcalen) 
{
  var fechactual = new Date();
  var agno = fechactual.getYear();
  var agno_ante_num= agno*1 - 1;
  var agno_anterior= agno_ante_num + '';
	
   //var strDatestyle = "US"; //tipo gringo
   var strDatestyle = "EU";  //tipo europeo
   var strDate;
   var strDateArray;
   var strDay;
   var strMonth;
   var strYear;
   var intday;
   var intMonth;
   var intYear;
   var booFound = false;
   var campodefecha = objeto;
   var strSeparatorArray = new Array("-","/");
   var intElementNr;
   var err = 0;
   var strMonthArray = new Array(12);
   ano_ok=true;
   strMonthArray[0] = "Jan";
   strMonthArray[1] = "Feb";
   strMonthArray[2] = "Mar";
   strMonthArray[3] = "Apr";
   strMonthArray[4] = "May";
   strMonthArray[5] = "Jun";
   strMonthArray[6] = "Jul";
   strMonthArray[7] = "Aug";
   strMonthArray[8] = "Sep";
   strMonthArray[9] = "Oct";
   strMonthArray[10] = "Nov";
   strMonthArray[11] = "Dec";
   strDate = campodefecha.value;
   if (strDate.length < 1) 
   {
     return true;
   }
   for (intElementNr = 0; intElementNr < strSeparatorArray.length; intElementNr++) 
   {
     if (strDate.indexOf(strSeparatorArray[intElementNr]) != -1) 
     {
        strDateArray = strDate.split(strSeparatorArray[intElementNr]);
        if (strDateArray.length != 3) 
        {
           err = 1;
           return false;
        }
        else 
        {
           strDay = strDateArray[0];
           strMonth = strDateArray[1];
           strYear = strDateArray[2];
        }
        booFound = true;
     }
   }
   if (booFound == false) 
   {
     if (strDate.length>5) 
     {
        strDay = strDate.substr(0, 2);
        strMonth = strDate.substr(2, 2);
        strYear = strDate.substr(4);
     }
     else 
     {
       err = 1;
       return false;
     }
   }
   if (strYear.length == 2) 
   {
      strYear = '20' + strYear;
   }
   //if (strYear !='2004' && strYear !='2003') 
   if (strYear !=agno && strYear !=agno_anterior) 
   {
      ano_ok=false;
      return false;
   }
  
   
   // US style
   if (strDatestyle == "US") 
   {
     strTemp = strDay;
     strDay = strMonth;
     strMonth = strTemp;
   }
   intday = parseInt(strDay, 10);
   if (isNaN(intday))
   {
     err = 2;
     return false;
   }
   intMonth = parseInt(strMonth, 10);
   if (isNaN(intMonth))
   {
     for (i = 0;i<12;i++) 
     {
       if (strMonth.toUpperCase() == strMonthArray[i].toUpperCase()) 
       {
         intMonth = i+1;
         strMonth = strMonthArray[i];
         i = 12;
       }
     }
     if (isNaN(intMonth)) 
     {
       err = 3;
       return false;
     }
   }
   intYear = parseInt(strYear, 10);
   if (isNaN(intYear)) 
   {
     err = 4;
     return false;
   }
   if (intMonth>12 || intMonth<1) 
   {
     err = 5;
     return false;
   }
   if ((intMonth == 1 || intMonth == 3 || intMonth == 5 || intMonth == 7 || intMonth == 8 || intMonth == 10 || intMonth == 12) && (intday > 31 || intday < 1)) 
   {
     err = 6;
     return false;
   }
   if ((intMonth == 4 || intMonth == 6 || intMonth == 9 || intMonth == 11) && (intday > 30 || intday < 1)) 
   {
     err = 7;
     return false;
   }
   if (intMonth == 2) 
   {
     if (intday < 1) 
     {
       err = 8;
       return false;
     }
     if (bisiesto(intYear) == true) 
     {
        if (intday > 29) 
        {
          err = 9;
          return false;
        }
     }
     else 
     {
       if (intday > 28) 
       {
         err = 10;
         return false;
       }
     }
   }
   cero_dia='';
   cero_mes='';
   if(intday<10) cero_dia='0';
   if(intMonth<10) cero_mes='0';
   if (strDatestyle == "US") {
     campodefecha.value = strMonthArray[intMonth-1] + " " + intday+" " + strYear;
   }
   else 
   {
     //campodefecha.value = intday + "-" + strMonthArray[intMonth-1] + "-" + strYear;
     campodefecha.value = cero_dia+intday + "-" + cero_mes+intMonth + "-" + strYear;
   }
   /*if(cualcalen==0) {
     dp.setDate( new Date( strYear, intMonth-1, intday));
   }
   if(cualcalen==1) {
     dp1.setDate( new Date( strYear, intMonth-1, intday));
   }   
   if(cualcalen==2) {
     dp2.setDate( new Date( strYear, intMonth-1, intday));
   } */  
   return true;
}
function bisiesto(intYear) 
{
  if (intYear % 100 == 0) 
  {
    if (intYear % 400 == 0) 
    { 
    	return true; 
    }
  }
  else 
  {
    if ((intYear % 4) == 0)
    {
      return true;
    }
  }
  return false;
}

//-->
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
function muestrala(p)
{

if (p==0) {
    MM_showHideLayers('Layer2','','hide');  }
        else{
MM_showHideLayers('Layer2','','show');   	
	}
}

//-->
</script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC" topmargin="0" onLoad="muestra_cuadro()">
<div id="Layer4" style="position:absolute; left:593px; top:264px; width:170px; height:23px; z-index:3; visibility: visible;"> 
</div>
<form name="form1" method="Post" action="grabar_ingreso.php">
<!--form name="form1" method="Post" action="grabar_ingreso_prueba.php"-->
<center>
    <table width="650" border="0" cellpadding="4" cellspacing="0" bgcolor="#3399FF">
      <tr>
      <td><div align="center"><font color="#FFFFFF" size="4"><strong>DERIVAR CON DOCUMENTO</strong></font></div></td>
    </tr>
  </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#ECE9D8">
      <tr> 
        <td  bgcolor="#cadbff">
		 <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr bgcolor="#e6eeff"> 
              <td height="15" colspan="6"><font color="#7777FF"><strong>INFORMACION 
                DOCUMENTO DE REFERENCIA</strong></font><font color="#AA291C">&nbsp;</font></td>
            </tr>
            <tr> 
              <td width="129" height="15"><font color="#804040"><b>Tipo de Docto</b> 
                </font></td>
              <td width="169" height="15" > <font color="#804040"><? echo $reg_ref[desc_tipo_documento]; ?> 
                </font></td>
              <td width="77" height="15"><font color="#804040"><strong>N&ordm; 
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
              <td width="129" height="18"><font color="#804040"><b>Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></font></td>
              <td width="169" height="18"> <font color="#804040"> 
                <?php $fec_doc=strtotime($reg_ref["fecha_documento"]);
		             $fech_doc=date("d/m/Y",$fec_doc);
     				echo $fech_doc;?>
                </font></td>
              <td width="77" height="18"><font color="#804040"><b>N&ordm; Oficial<font size="4" face="Arial"> 
                </font></b></font></td>
              <td width="77" height="18"> <font color="#804040"><?php echo $reg_ref[num_oficial];?> 
                </font></td>
              <td width="50"><font color="#804040"><b>Original</b></font></td>
              <td width="111"><font color="#804040"><font color="#804040"><? echo $reg_ref[original];?></font></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr valign="middle"> 
              <td width="128" height="18"><font color="#804040"> <b>Estado del 
                Tr&aacute;mite</b> </font></td>
              <td width="170" height="18"><font color="#804040"><?echo $reg_ref[desc_estado_tramite];?><b></b></font></td>
              <td width="77" height="18"> <font color="#804040"><strong>N&ordm; 
                Externo </strong></font></td>
              <td width="248" height="18"><font color="#804040"><font size="4" face="Arial"> 
                </font><font color="#804040"> </font><font color="#804040"><? echo $reg_ref[num_externo]; ?></font><font size="4" face="Arial"> 
                </font></font></td>
             <!--<td width="77"><font color="#804040"><strong>Id.CodIbm</strong></font></td>
              <td width="85"><font color="#804040"><font color="#804040"><? echo $rs[idcodibm]; ?></font></font></td>-->
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr> 
              <td width="127" height="18"><font color="#804040"><b>Procedencia</b></font></td>
              <td width="171" height="20"> <font color="#804040"><? echo $reg_ref[procedencia];?> 
                </font><font color="#804040">&nbsp;</font> <font color="#804040"> 
                <font color="#804040"> </font> </font></td>
              <td width="74"><font color="#804040"><b>Funcionario</b></font></td>
              <td width="251"><font color="#804040"><? echo $reg_ref[nombre_procedencia];?></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr> 
              <td width="127" height="18"><font color="#804040"><b>Materia</b> 
                </font></td>
              <td width="503"> <font color="#804040"> <? echo $reg_ref[materia];?> 
                </font></td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td width="643" align="center" bgcolor="#e6eeff">
<table width="100%" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="626"><strong><font color="#804040">IDENTIFICACION DEL 
                DOCUMENTO NUEVO</font></strong></td>
            </tr>
          </table>
          <table width="100%" border="1" cellpadding="1" cellspacing="0" bgcolor="#FFFFFF">
            <tr> 
              <td width="631" align="center" bgcolor="#e6eeff"> <div align="center"> 
                  <table width="100%" border="0" cellspacing="0" cellpadding="1">
                    <tr> 
                      <td width="100"><font size="2">Tipo de Docto</font> </td>
                      <td width="145">
					   <select name="Cbo_Tipo_Docto" id="select5">
                          <?
				   while($reg=mssql_fetch_array($rs_tipo_docto)){
				?>
                          <option value=<? echo $reg[id_tipo_documento] ?> ><? echo $reg[desc_tipo_documento] ?></option>
                          <?
}
?>
                        </select> </td>
                      <td width="92">Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                      <td width="283"><font face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_doc" type="text" class="entradas" id="Txt_fecha_doc2" value="<?=$fecha_x?>"  onBlur="chequeafecha(this,0)" size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                        </font></td>
                    </tr>
                    <tr> 
                      <td>Estado</td>
                      <td><p><strong>Documento Nuevo</strong></p></td>
                      <td>Original 
                        <input name="Original" type="checkbox" value="S" checked></td>
                      <td>Medio 
                        <select name="Cbo_Medio" id="select3">
                          <option value="P" <?php if($Cbo_Medio=="P") { echo 'SELECTED'; } ?> >Papel</option>
                          <option value="C" <?php if($Cbo_Medio=="C") { echo 'SELECTED'; } ?> >Correo</option>
                          <option value="V" <?php if($Cbo_Medio=="V") { echo 'SELECTED'; } ?> >Video</option>
                          <option value="F" <?php if($Cbo_Medio=="F") { echo 'SELECTED'; } ?> >Fax</option>
                        </select> </td>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td> <table width="394" align="left"  height="30" border="0" cellspacing="1" cellpadding="1">
                          <tr> 
                            <td width="74"> <div align="justify"><strong>N&uacute;meros 
                                : <font size="4" face="Arial"> </font></strong></div></td>
                            <td width="137"><div align="justify">Oficial<font size="4" face="Arial"> 
                                <input name="TxtOficial2" type="text" class="entradas" id="TxtOficial2" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                                </font></div></td>
                            <td width="173"><div align="justify">Externo<font size="4" face="Arial"> 
                                <input name="TxtExterno2" type="text" class="entradas" id="TxtExterno" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                                </font></div></td>
                          </tr>
                        </table>
                        <div id="Layer1" style="position:absolute; width:235px; height:124px; z-index:1; left: 244px; top: 220px; visibility: hidden; overflow: auto;"> 
                          <table width="100%" border="1" bgcolor="#E6EEFF">
						     <tr> 
                              <td height="25"> 
                                <div align="center" onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide')"><strong>Aceptar</strong></div></td>
                            </tr>
                            <tr> 
                              <td height="105"> 
                                <?php 
							  $k=0;
							  while($reg_servicio = mssql_fetch_array($rs_servicio)) { ?>
                                <input type="checkbox" name="casilla" value="<?php echo $reg_servicio["id_descriptor"];?>" onClick="javascript:muestra(<?php echo $reg_servicio["id_descriptor"];?>);"> 
                                <?php echo $reg_servicio["desc_descriptor"];?> 
                                <br> 
                                <?php } ?>
                              </td>
                            </tr>
                           <!-- <tr> 
                              <td height="25"> 
                                <div align="center" onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide')"><strong>Aceptar</strong></div></td>
                            </tr>-->
                          </table>
                          <div align="right"></div>
                        </div></td>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellpadding="1" cellspacing="0">
                    <tr> 
                      <td width="10%"><strong>Materia</strong></td>
                      <td width="50%"><textarea name="TxtMateria"   cols="70" rows="3" onBlur="valida_campo();" ><? echo $reg_ref[materia]?></textarea></td>
                      <!--td width="40%">
					    Descriptores 
                        <!--input type="radio" name="radiodescriptor" value="radiobutton" onClick="MM_showHideLayers('Layer1','','show')"-->
                        <!--input name="bt_descriptor" type="button" class="botones" id="bt_descriptor" onClick="javascript:fnOpen_descriptor(document.form1.arreglo.value);" value="Descriptor">
						</td-->
						
                      <td width="40%"> 
					  <input name="bt_descriptor" type="button" class="botones" id="bt_descriptor" onClick="javascript:fnOpen_descriptor(document.form1.arreglo.value);" value="Descriptor"></td>
                    </tr>
                  </table>
                </div></td>
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="621"><font color="#804040"><strong>TRAMITE DEL DOCUMENTO</strong></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="315"><font color="#804040"><strong>ORIGEN</strong></font></td>
              <td width="155"><font color="#804040"><strong>DESTINO</strong></font> 
              </td>
              <td width="160"><font color="#804040">&nbsp;</font></td>
            </tr>
          </table>
          <table width="100%" border="1" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="305"><table width="100%" border="0" cellspacing="1" cellpadding="1">
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
              <td width="308"><table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="94"><div align="left"><strong>Interno 
                        <input name="radiodestino" type="radio"  value ="1" checked onClick="javascript:destino_interno();">
                        </strong></div></td>
                    <td width="108"><strong>Externo 
                      <input name="radiodestino" type="radio"  value ="2"  onClick="javascript:destino_externo();">
                      </strong></td>
                    <td width="109"><strong> 
                      <input type="button" name="boton" value="Múltiple" onclick="ver_destino();">
                      </strong></td>
                  </tr>
                  <tr> 
                    <td>Destino</td>
                    <td colspan="2"><font face="Arial"> 
                      <select name="Cbo_Destinatario" id="Cbo_Destinatario" onChange="javascript:cambio1();">
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
                    <td colspan="2"><font face="Arial"> 
                      <select name="Cbo_Func_Destino" id="select2" onChange="javascript:cambio3();">> 
                      </select>
                      </font></td>
                  </tr>
                </table></td>
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="22%">Tipo Distribuci&oacute;n</td>
              <td width="28%"><font face="Arial"> 
                <select name="Cbo_Tipo_Distribucion" id="select9">
                  <?
		while($reg_distribucion=mssql_fetch_array($rs_distribucion)){
?>
                  <option value=<? echo $reg_distribucion[id_tipo_distribucion] ?> ><? echo $reg_distribucion[desc_tipo_distribucion] ?></option>
                  <?
}
?>
                </select>
                </font></td>
              <td width="21%">Tipo Compromiso</td>
              <td width="29%"><font face="Arial"> 
                <select name="Cbo_Tipo_Compromiso" id="select14">
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
              <td><input name="TxtDias"  type="text" class="entradas" size="4" maxlength="2"> 
                <div id="LayerInt" style="position:absolute; width:317px; height:179px; z-index:1; left: 180px; top: 300px; visibility: hidden; overflow: auto;"> 
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
                   <!-- <tr> 
                      <td height="23"> <div align="center" onClick="MM_showHideLayers('LayerInt','','hide');MM_showHideLayers('LayerInt','','hide');ver_check(<?php echo $nRowsint;?>)"><strong>Aceptar</strong></div></td>
                    </tr>-->
                  </table>
                  <div align="right"></div>
                </div>
                <div id="LayerExt" style="position:absolute; width:317px; height:179px; z-index:1; left: 180px; top: 300px; visibility: hidden; overflow: auto;"> 
                  <table width="100%" border="1" bgcolor="#E6EEFF">
                    <tr> 
                      <td height="27"> 
                        <div align="center" onClick="MM_showHideLayers('LayerExt','','hide');MM_showHideLayers('LayerExt','','hide');ver_check(<?php echo $nRowsext;?>)"><strong>Aceptar</strong></div></td>
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
                  <!--  <tr> 
                      <td height="27"> 
                        <div align="center" onClick="MM_showHideLayers('LayerExt','','hide');MM_showHideLayers('LayerExt','','hide');ver_check(<?php echo $nRowsext;?>)"><strong>Aceptar</strong></div></td>
                    </tr>-->
                  </table>
                  <div align="right"></div>
                </div></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="1" cellspacing="0">
            <tr> 
              <td><strong>Observaci&oacute;n</strong></td>
              <td><textarea name="TxtObservacion" cols="70" rows="3" id="textarea"></textarea></td>
              <td>Despachado por Oficina de Partes 
                <input name="checkofpartes2" type="checkbox" id="checkofpartes22" value="S"></td>
            </tr>
          </table>
          <br> <table width="100%" border="0" cellpadding="1" cellspacing="0">
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
                  <input type="hidden" name="arregloint" >
                  <input type="hidden" name="arregloext" >
                  <input type="hidden" name="num_int" value="<? echo $num_int;?>">
                  <input type="hidden" name="txtnomina" value="<? echo $txtnomina;?>">
				  <input type="hidden" name="txtagnor" value="<? echo $txtagnor;?>">
				  <input type="hidden" name="Cbo_Estado_Docto" value="<? echo 1;?>">
                  <input name="cmd_grabar" type="button" class="botones" onClick="chequear_arreglo(<?php echo $nRows?>);chequear_arregloint(<?php echo $nRowsint?>);chequear_arregloext(<?php echo $nRowsext?>);validar_datos();" value="Grabar">
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
