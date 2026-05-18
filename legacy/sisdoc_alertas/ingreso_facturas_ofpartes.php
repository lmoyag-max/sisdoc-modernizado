<?php
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;

$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;

//echo "funcionario  " . $fun . " *** cusuario  " . $cusuario . "**** xx  " . $idusuario;

$flujo1=$flujook;
if ($flujook==8){
$num_int=0;}
else{
$num_int=$num_int;}


$nRowsint = mssql_num_rows($rs_dependencia);
$nRowsext = mssql_num_rows($rs_dependencia_externa);
$rs_funcionario = mssql_query("SELECT id_dependencia FROM funcionario where id_funcionario = " . $fun, $cn);
$reg_func = mssql_fetch_array($rs_funcionario);
$Tot_fun = mssql_num_rows($rs_funcionario);

if ($Tot_fun!=0)
{
   if ($reg_func[id_dependencia]==6)
      {
	  	$id_dependencia=$reg_func[id_dependencia]; 
		$rs_procedencia = mssql_query("select id_dependencia_externa, SUBSTRING(desc_dependencia_externa, 1, 35) AS desc_dependencia_externa, cod_dependencia_externa
		from dependencia_externa order by desc_dependencia_externa ",$cn);
		//select * from dependencia_externa order by desc_dependencia_externa ",$cn);
		$Procedencia="E";
	   }
   else
      {
		//$rs_procedencia = mssql_query("select dependencia.id_dependencia, SUBSTRING(dependencia.desc_dependencia, 1, 35) AS desc_dependencia, dependencia.cod_dependencia
		//from dependencia, acceso  where acceso.id_dependencia = dependencia.id_dependencia and acceso.id_usuario =$xx",$cn);
		
		// muestra las dependencias que dependen del usuario y que demas estan vigentes 
		
		$rs_procedencia = mssql_query("select dependencia.id_dependencia, SUBSTRING(dependencia.desc_dependencia, 1, 35) AS desc_dependencia, dependencia.cod_dependencia_nuevo
		from dependencia, acceso  where acceso.id_dependencia = dependencia.id_dependencia  and dependencia.vigencia is null and acceso.id_usuario =$xx",$cn);
		
		//select dependencia.* from dependencia, acceso where acceso.id_dependencia = dependencia.id_dependencia and acceso.id_usuario =$xx",$cn);
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
$fecha_r = date("d-m-Y");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario ingreso docto1</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">



<script language="JavaScript" type="text/javascript">
<!--
var ano_ok=true;
var sw_ok;
var sw_multiple=0;
var cont_arreglo;
var cont_arreglo1;
var z=0;
var arreglo2 ="";
var arreglo1="";
var arreglo3="";
var ar_descrip =new Array();
var sw_invita=0;

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
document.form1.Txt_fecha_inv.value="";
 for (k=1;k<=x;k++){
  if (temp[k]==13){
     sw_invita=13;
 	muestrala(1);  }
   }
 if (sw_invita==0) {
        muestrala(0);
    }

}


function desbloquea_grabar()
{
document.form1.cmd_grabar.disabled=false;
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
  
function valida_campo() 
{
sw_ok = true;
   	if(document.form1.TxtMateria.value == "") 
	{
           sw_ok = false;
		   alert("Debe Ingresar la Descripción");
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
	alert("Falta Ingresar descripción de la factura");
	document.form1.TxtMateria.focus();
  }
else
 if (document.form1.el_rut.value=='' || document.form1.el_rut.value== null)
    {	
	sw_ok=false;
	alert("debe ingresar rut del proveedor ");
	document.form1.el_rut.focus();

	}
else 
 if (document.form1.num_factura.value=='' || document.form1.num_factura.value==null)
    {	sw_ok=false;
	alert("debe ingresar numero factura ");
	document.form1.num_factura.focus();

	}
else 
 if (document.form1.monto.value=='' || document.form1.monto.value==null)
    {	sw_ok=false;
	alert("debe ingresar monto  factura ");
	document.form1.monto.focus();

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
	document.form1.val_funcionario1.value="";
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
var selindice,tipo_doc,proc,tipo_p,dest,tipo_d, similar,x;
var valor="SFO";
//selindice = document.form1.Cbo_tema_facturas.selectedIndex;
//temas = document.form1.Cbo_tema_facturas.options[selindice].value;
proc=document.form1.val_procedencia.value;
tipo_p=document.form1.tipo_procedencia.value;
dest=document.form1.val_destino.value;
tipo_d=document.form1.tipo_destino.value;
num_fac=document.form1.num_factura.value;
fec_doc=document.form1.Txt_fecha_doc.value;
rt=document.form1.el_rut.value;

top.window.frame_consultas.location.href="frame_consultas.php?sw="+valor+
//"&temas="+temas+
"&proc="+proc+
"&tipo_p="+tipo_p+
"&dest="+dest+
"&tipo_d="+tipo_d+
"&num_fac="+num_fac+
"&fec_doc="+fec_doc+
"&rut="+rt;
}
}

function similar(dato)
{   
    if (dato==0)
	{
	document.form1.submit();
	}
	else
	var answer=confirm("¿Ya existe un documento igual, Desea Grabar Factura que esta ingresando?")
	if (answer)
	{
	document.form1.submit();
	//window.location.href="frame_menuvars";
	}
}


/*function fecha_invitacion(filas)
{
var j= 0;
MM_showHideLayers('Layer2','','hide');

for (i=0; i<filas;i++)
 {
  if (document.form1.casilla[i].checked)
   {
   document.form1.Txt_fecha_inv.value="";
   if (document.form1.casilla[i].value==13){
    //    alert("invitacion, " + document.form1.casilla[i].value );
	   //document.form1.inv.value = document.form1.casilla[i].value;
	   //j= 1;
	   MM_showHideLayers('Layer2','','show'); }
   }
 }
}*/


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

/*function chequear_arreglo(filas) 
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
//    document.form1.arreglo.value=x + "@" + arreglo2;

var b = document.form1.arreglo.value
var temp = new Array();
temp = b.split('@');
x = temp[0];
cont_arreglo = x;
	
}*/

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
//-->
</script>
<script language="JavaScript" type="text/JavaScript">
<!--
var flujo2 = <?php echo $flujook; ?>;  
//var numint = <?php echo $num_int; ?>;  


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
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
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

function muestra_cuadro() 
{ 
  if (flujo2==0) {
//  document.form1.cmd_grabar.disabled=true;
//  alert("El Documento ha sido grabado con el Nro Interno : "+ numint );
  alert("La Factura a sido ingresada al sistema ");
  }
  else 
  {
  	ver_combos();
  }	
  <?php 
  if(isset($bloquea))
  {
    echo "document.form1.txtdescrip.disabled=true;\n";
    echo "document.form1.txtexped.disabled= false;\n";
    echo "document.form1.Buscar.disabled=false;\n";
  }
  ?>
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
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+p1+"&des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;	
	document.form1.Cbo_Func_Destino.disabled=true;
 } 
else 
 if(document.form1.tipo_procedencia.value=="E"&&document.form1.tipo_destino.value=="I"){	

	
	var valor="EI";
	var p2= document.form1.Cbo_Destinatario.selectedIndex;
	var p3=document.form1.val_destino.value;
	var d3=document.form1.val_funcionario1.value;
	var d2=document.form1.val_procedencia.value;
	

top.window.frame_consultas.location.href="frame_consultas.php?des_d="+d2+"&des_f="+d3+"&prod="+p2+"&pro_f="+p3+"&sw="+valor;	
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
	

top.window.frame_consultas.location.href="frame_consultas.php?des_d="+d2+"&des_f="+d3+"&prod="+p2+"&pro_f="+p3+"&sw="+valor;	
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
function bloquea_expediente()
// bloquea descripcion en caso de asociar expediente  o bloquea ingreso numero de expediente 
// si es expediente nuevo 
{
 if (document.form1.txtexp.checked )
 {
  document.form1.txtexped.value= 0 ;
  document.form1.txtexped.disabled= true ;
  document.form1.Buscar.disabled=true;
  document.form1.txtdescrip.disabled=false;
  document.form1.txtdescrip.value="" ;
  document.form1.txtdescrip.focus();
   
 }
 else 
 { 
  document.form1.txtdescrip.disabled=true;
  document.form1.txtexped.disabled= false ;
  document.form1.Buscar.disabled=false;
  
 } 
  
}
function Buscar_expediente()
// pasa al formulario en que muestra los expedientes existentes y seleccionar el que se desea 
{
	document.form1.action="busca_expediente.php";
	document.form1.submit();
}
function activa_descripc()
{
if (document.form1.descripc.value==1)
	{
	document.form1.txtdescrip.disabled=true;
	}
}
/*function mensaje_exp()
// valida si el  numero ingresado esta en la tabla de expediente
{
	if (document.form1.totexped.value==0  )
		{
		alert ("No existe expediente ");
		document.form1.txtdescrip.disabled=true;
		document.form1.txtexped.value=0;
		document.form1.txtexped.focus();
		}
}*/
function exped_ing()
// busca el numero de expediente ingresado en pantalla 
{
/*	document.form1.action="busca_exp.php";
	document.form1.submit();*/
	var nuevalsel;
	var numero;
	var valor=0;
	nuevasel= "X";
	numero =document.form1.txtexped.value;
	//alert ( nuevasel+ valor+ numero );
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor+"&numero="+numero;

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

//--------------------------------------
//function verificarRutGeneral
//Objetivo: Verificacion del rut ingresado. 
// si la accion no es envio solo formatea 
//Parametro(s):(input)String con Rut, int tipo, int action
//Uso: desde pagina
//Requiere:  limpiarRut, verificarRut, soloNumeros, enviarRut, formatearRut
//--------------------------------------

function verificarRutGeneral(strRut, tipo, action){
	
	if (strRut==""){
		alert("Debe ingresar el RUT.");
		submitcount=0;
		document.form1.el_rut.focus();
		document.form1.el_rut.select();
		}
	else{	
		strRut=limpiarRut(strRut);
		if (verificarRut(strRut)){			
			if (action==1){ //envia
			}else{ //formatea
				formatearRut(strRut);
			}
		}
	}
}
//--------------------------------------
//function limpiarRut
//Objetivo: Solo Limpieza de RUT de ceros a la izquierda, guiones y puntos(deja digitos y k)
//Parametro(s):(input)String con Rut
//(output) String con rut limpio
//Uso: desde fnc. verificarRutGeneral
//Requiere:  
//--------------------------------------
function limpiarRut(strRut){	
	document.form1.rut.value = document.form1.el_rut.value;
	var digVerif ="";
	var digVerifIn ="";
	var straux ="";
	var rutsgnp = "";
	while((new Number(strRut.charAt(0))==0)&&(strRut!="")){
		strRut=strRut.substring(1,strRut.length);		
	}
	for (i=0; i < strRut.length; i++)
	{
		if ((strRut.charAt(i) != ".") && (strRut.charAt(i) != "-") && (strRut.charAt(i)!=" "))
			rutsgnp= rutsgnp + strRut.charAt(i);
	   }
	return rutsgnp;
}
//--------------------------------------
//function soloNumeros
//Objetivo: Verifica que existan solo numeros.
//Parametro(s):(input)String 
//(output) 1 sin son solo numeros, 0 en caso contrario
//Uso: desde fnc. verificarRut y verificarRutGeneral
//Requiere:  
//--------------------------------------
function soloNumeros(strIn) {
  var Nros="1234567890";
  var CrtrAux;
  var iaux=0;
  for (var i=0; i < strIn.length; i++)
  {
    CrtrAux = strIn.charAt(i);
    if (Nros.indexOf(CrtrAux) != -1)
      iaux++;
  }
  if ((iaux != strIn.length) || (strIn.length==0)){
   	return 0
	}
  else
    return 1;
}
function digitoVerificador(strRut) {
    var Largo, LargoN, i, Total;
    var Numero="", Verif, Carac, CaracVal;
    var tmpRut,intTmp;
    
    tmpRut = strRut;
    Largo = tmpRut.length;
    LargoN = 0;
    for(i=0;i<Largo;i++) {
        Carac = parseInt(tmpRut.charAt(i),10);
        if(Carac >=0 && Carac <=9) {
			Numero+=tmpRut.charAt(i);
            LargoN++;
	 	}
    }
	Total=0;
    for(i=LargoN-1;i>=0;i--) {
		if((LargoN - i) < 7) {
		   intTmp=LargoN - i + 1;
		} else {
		   intTmp=LargoN - i - 5;
		}
        Total+= parseInt(Numero.charAt(i),10) * intTmp 
    }
    
    CaracVal = 11 - (Total % 11)
    
    if(CaracVal==10) {
       return('K');
	}
	
	if(CaracVal >=0 && CaracVal <=9) {
       return(CaracVal);
	}
	
	if(CaracVal==11) {
	   return(0);
    }
}

//--------------------------------------
//function verificarRut
//Objetivo: Solo Verificacion del string RUT
//Parametro(s):(input)String con Rut
//(output) true si el rut es correcto, false si no
//Uso: desde fnc. verificarRutGeneral
//Requiere:  fnc. solonumeros, digitoVerificador
//--------------------------------------
function verificarRut(strRut)
{	
	if (strRut != "")  
	{
	   straux = strRut.substring(strRut.length-1,strRut.length);
	   if (straux == "k") 
		 digVerifIn = straux.toUpperCase()
	   else
		 digVerifIn = straux;
	   straux = strRut.substring(0,strRut.length-1);
	   if (soloNumeros(straux) == 0)
		 digVerif = "KX"
	   else
		 digVerif = digitoVerificador(straux);

	   if(digVerif == digVerifIn){
			accionInterna=1;
			return true;
			}
	   else 
	   {
		   alert("RUT incorrecto.");
		   submitcount=0;
		   document.form1.el_rut.value="";
		   document.form1.el_rut.select();
		   document.form1.el_rut.focus();
		   accionInterna=0;		   
		   return false;
		}
	} 
	else
	{	alert("Debe ingresar el RUT.");
		submitcount=0;
		document.form1.el_rut.value="";
		document.form1.el_rut.select();
		document.form1.el_rut.focus();
		return false;
	}
}

//--------------------------------------
//function formatearRut
//Objetivo: Formateo del RUT
//Parametro(s):(input)String con Rut
//(output) imprime rut formateado en cuadro de texto
//Uso: desde fnc. verificarRutGeneral
//Requiere:  
//--------------------------------------
function formatearRut(strRut){
	straux = strRut.substring(strRut.length-1,strRut.length);
	rutsgnp= strRut.substring(0,strRut.length-1);

	strAuxArray = new Array(0,0,0);
	strAuxArray[0]=rutsgnp.substring(rutsgnp.length-3,rutsgnp.length);
	strAuxArray[1]=rutsgnp.substring(rutsgnp.length-6,rutsgnp.length-3);
	strAuxArray[2]=rutsgnp.substring(0,rutsgnp.length-6);
	i=0;
	rutsgnp="-"+straux;
	for (i=0; i < 3; i++){
		if (strAuxArray[i]==""){
			i=3;
		}else{
			if (i>0){
				rutsgnp="."+rutsgnp;
			}
			rutsgnp=strAuxArray[i]+rutsgnp;
		}
	}
	document.form1.el_rut.value=rutsgnp;
	document.form1.el_rut.blur();
}

 function busca_rut(rut)
 // saca del rut original el guion y los puntos para lkuego consultar por el en la tabla de donantes  por medio del frame escondido 
{
 if (rut !='')
 {
 var rut ;
 var colecta ;
 var punto = rut.replace('.',''); 
 var punto =punto.replace('.','');
 var posicion = punto.indexOf('-');
 var guion =punto.replace('-','');
 var rut= guion.substring(0,posicion);
 var valor = rut;
 document.form1.rutx.value=valor;
var sw='BPR';
// document.form1.txtnomprim.value =document.form1.txtnomprim.value;
 top.window.frame_consultas.location.href="frame_consultas.php?sw="+sw+ "&rutx=" + valor;
 }
 else 
  
 {
  alert("Debe ingresar rut de proveedor ");
  document.form1.el_rut.focus();
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
<script src="../js/calendario.js"></script>
<link href="../css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>
<body bgcolor="#FFFFFF" topmargin="0" onLoad="muestra_cuadro()">
<center>
<form name="form1" method="Post" action="guardar_ingreso2_facturas.php">
    <table width="779" border="1" cellpadding="1" cellspacing="0" bgcolor="#ECE9D8">
      <tr> 
        <td width="773" align="center" bgcolor="#e6eeff"> <table width="100%" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
            <tr> 
              <td width="650" height="34"> <div align="center"><font color="#FFFFFF" size="4"><strong>INGRESO 
                  DE FACTURAS</strong></font></div></td>
            </tr>
          </table>
          <table width="100%" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="320" class="texto"><strong><font color="#804040">IDENTIFICACION 
                FACTURA </font></strong></td>
              <td width="322"><div align="right"><font color="#804040"><strong><font size="2"> 
                  <? echo "Usuario : " . $cusuario?></font></strong></font></div></td>
            </tr>
          </table>
          <table width="100%" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="760" height="192" align="center"> 
                <div align="center"> 
                  <table width="100%" border="0" cellspacing="0" cellpadding="1">
                    <tr> 
                      <!--td width="160" height="20"><font color="#000000">Temas 
                        </font><font color="#000000">de Factura </font></td>
                      <td width="169" height="20"> <div align="left"><font color="#000000"> 
                          <select name="Cbo_tema_facturas" class="combo" id="Cbo_tema_facturas" >
                            <?
						   while($reg=mssql_fetch_array($rs_tema_factura)){
							?>
                            <option value=<? echo $reg[id_tema] ?> ><? echo $reg[desc_tema] ?></option>
                            <?
							}
						  ?>
                          </select>
                          </font></div></td-->
                      <td width="77" height="20"> <div align="right"><font color="#000000">Fecha 
                          Docto</font></strong></div></td>
                      <td width="151" height="20" valign="middle"><font color="#000000" face=		"Arial, Helvetica, sans-serif"> 
                        &nbsp;&nbsp; 
                        <input name="Txt_fecha_doc" type="text" class="entradas" id="Txt_fecha_doc" value="<?=$fecha_x?>" onBlur="chequeafecha(this,0)" size="8" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                        &nbsp;&nbsp; &nbsp;&nbsp;</font><font color="#000000">&nbsp; 
                        </font></td>
                      <td width="97" valign="middle">Fecha Recepci&oacute;n</td>
                      <td width="101" valign="middle"><font color="#000000" face=		"Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_recep" type="text" class="entradas" id="Txt_fecha_recep" value="<?=$fecha_r?>" onBlur="chequeafecha(this,0)" size="8" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_recep');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                        </font></td>
                    </tr>
                    <tr> 
                      <td height="20">Num. Factura </td>
                      <td height="20"><font color="#000000"> 
                        <input name="num_factura" type="text" id="num_factura5" onBlur="valida_digito(this.value,this,12);" size="15" maxlength="15">
                        </font></td>
                      <td height="20"><div align="right"></div></td>
                      <td height="20" colspan="3"><font color="#000000">&nbsp; 
                        </font></td>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellspacing="0" cellpadding="1">
                    <tr> 
                      <td width="158" height="20">Rut Proveedor<font color="#000000">&nbsp; 
                        </font></td>
                      <td width="163"><font color="#000000"> 
                        <input name="el_rut" type="text" id="el_rut"   onChange="verificarRutGeneral(document.form1.el_rut.value,0,0);" onBlur="busca_rut(document.form1.el_rut.value);" size="12" maxlength="12">
                        </font></td>
                      <td width="424" height="20"> <p><font color="#000000"> 
                          <input name="nombreproveed" type="text" id="nombreproveed"   size="60" maxlength="60">
                          </font><font color="#000000"> </font><font color="#000000"> 
                          </font></p></td>
                    </tr>
                  </table>
                  <table width="100%" border="0" valign="top" cellspacing="2" cellpadding="2">
                    <tr> 
                      <td width="78" height="90"><font color="#000000"><strong><font size="2">Descripci&oacute;n</font></strong> 
                        </font></td>
                      <td width="281"><font color="#000000" size="2"> 
                        <textarea name="TxtMateria"  cols="60" rows="4" class="cajatexto" onKeyPress="return CheckLength(250)"></textarea>
                        </font> <font color="#000000" size="2">&nbsp; </font> 
                      </td>
                      <td width="100"> <div align="right">Monto $ <font size="1"></font></div></td>
                      <td width="203"> <font color="#000000">&nbsp; 
                        <input type="text" name="monto" onBlur="valida_digito(this.value,this,12);">
                        </font><strong>(Ingresar sin puntos)<font color="#000000"> 
                        </font></strong></td>
                    </tr>
                  </table>
                </div></td>
            </tr>
          </table>
		  <table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="304" class="texto"><font color="#804040"><strong>TRAMITE 
                </strong></font></td>
          </tr>
		</table>  
          <table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="304"><font color="#804040"><strong>ORIGEN</strong></font></td>
              <td width="309"><font color="#804040"><strong>DESTINO</strong></font></td>
            </tr>
          </table>
          <table width="100%" border="1" cellspacing="0" cellpadding="1">
            <tr> 
            <td width="305"><table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="160"><strong>Interno 
                      <?php if($Procedencia=="I") { ?>
                      <input name="radioprocedencia" type="radio" onClick="javascript:procedencia_interna();" checked="true">
                    </strong> 
                    <?php ;} else {?>
                    <input name="radioprocedencia" type="radio" onClick="javascript:procedencia_interna();" >
                      <?php ;} ?>
                    </td>
                    <td width="160"><strong>Externo 
                      <?php if($Procedencia=="E") { ?>
                      <input name="radioprocedencia" type="radio" onClick="javascript:procedencia_externa();" checked="true">
					</strong>
					
					<?php ;} else {?>
					<input name="radioprocedencia" type="radio" onClick="javascript:procedencia_externa();">
                     <?php ;} ?> 				
				</td>
                </tr>
                <tr> 
                    <td width="160">Procedencia</td>
                    <td width="160"><font face="Arial"> 
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
                    <td width="160">Funcionario</td>
                    <td width="160">
<select name="Cbo_Func_Procedencia" class="combo" id="select" onChange="javascript:cambio2();">
				  	<option value="0"> </option>
                      </select>
                    </td>
                </tr>
              </table></td>
            <td width="311"><table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="73">
<div align="center"><strong>Interno 
                        <input name="radiodestino" type="radio" onClick="javascript:destino_interno();" value="1" checked>
                        </strong></div></td>
                    <td width="119"><strong>Externo 
                      <input name="radiodestino" type="radio"  value="2" onClick="javascript:destino_externo();">
                      </strong></td>
                    <td width="119"><input name="boton" type="button" class="botones" onClick="javascript:ver_destino();" value="Múltiple"></td>
                  </tr>
                  <tr> 
                    <td width="73">Destino</td>
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
                    <td width="73" height="42">Funcionario</td>
                    <td colspan="2"><font face="Arial"> 
                      <select name="Cbo_Func_Destino" class="combo" id="select" onChange="javascript:cambio3();"><option value="0"> </option>
                      </select>
                      </font></td>
                  </tr>
                </table></td>
          </tr>
        </table>
          <table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr>
            <td width="22%">Tipo Distribuci&oacute;n</td>
            <td width="28%"><font face="Arial">
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
            <td width="20%">Tipo Compromiso</td>
            <td width="30%"><font face="Arial">
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
            <td><div align="left"><font face="Arial"><strong> En Trámite</strong></font></div></td>
            <td>D&iacute;as Compromiso</td>
            <td><input name="TxtDias"  type="text" class="entradas" onBlur="valida_digito(this.value,this,2);" size="2" maxlength="2">
	      	<div id="LayerInt" style="position:absolute; width:327px; height:166px; z-index:1; left: 450px; top: 259px; visibility: hidden; overflow: auto; background-color: #E6EEFF; layer-background-color: #E6EEFF; border: 1px none #000000;" class="texto"> 
                  <table width="100%" border="1" bgcolor="#E6EEFF">
                    <tr> 
                      <td height="32"> 
                        <div align="center" onClick="MM_showHideLayers('LayerInt','','hide');MM_showHideLayers('LayerInt','','hide');ver_check(<?php echo $nRowsint;?>)"><strong>Aceptar</strong></div></td>
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
                    <!--tr> 
                      <td height="32"> 
                        <div align="center" onClick="MM_showHideLayers('LayerInt','','hide');MM_showHideLayers('LayerInt','','hide');ver_check(<?php echo $nRowsint;?>)"><strong>Aceptar</strong></div></td>
                    </tr-->
                  </table>
                  <div align="right"></div>
                </div>
				<div id="LayerExt" style="position:absolute; width:326px; height:166px; z-index:1; left: 450px; top: 259px; visibility: hidden; overflow: auto;"> 
                  <table width="100%" border="1" bgcolor="#E6EEFF">
				  <tr> 
                      <td height="27"> 
                        <div align="center" onClick="MM_showHideLayers('LayerExt','','hide');MM_showHideLayers('LayerExt','','hide');ver_check(<?php echo $nRowsext;?>)"><strong>Aceptar</strong></div></td>
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
                    <!--tr> 
                      <td height="27"> 
                        <div align="center" onClick="MM_showHideLayers('LayerExt','','hide');MM_showHideLayers('LayerExt','','hide');ver_check(<?php echo $nRowsext;?>)"><strong>Aceptar</strong></div></td>
                    </tr-->
                  </table>
                  <div align="right"></div>
                </div></td>
          </tr>
        </table>
          <table width="100%" border="0" cellspacing="0" cellpadding="1">
            <tr> 
            <td width="88"><strong><font size="2">Observaci&oacute;n</font></strong><br>
            </td>
            <td width="254"><textarea name="TxtObservacion" cols="50" rows="3" class="cajatexto"  id="textarea" onkeypress="return CheckLength(250);"></textarea></td>
              <td width="346">&nbsp;</td>
          </tr>
        </table>
        
          <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr> 
            <td height="37" width="347"> 
			
              <div align="center"> 
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
				<input type="hidden" name="num_int" value="<?php echo $num_int;?>">
                <input type="hidden" name="Cbo_Estado_Docto" value="<?php echo 1;?>">
				<input type="hidden" name="Cbo_Estado_Compromiso" value="<?php echo 2;?>">
				<input type="hidden" name="arregloint" >
				<input type="hidden" name="arregloext" >
				<input type="hidden" name="inv" >
			  <input type="hidden" name="rut" >			  
				  <input type="hidden" name="rutx" >
	           <input  type="hidden" name="Cbo_tema_facturas" value="<?php echo 0;?>">
    


                  <input type="hidden" name="menu" value = "<? echo 1  ;?>">
				<input name="cmd_grabar" type="button" class="botones" onClick="chequear_arregloint(<?php echo $nRowsint?>);chequear_arregloext(<?php echo $nRowsext?>);validar_datos();" value="Grabar">
			  </div></td>
            <td width="347"><div align="center" width="310"> 
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
