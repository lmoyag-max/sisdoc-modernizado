<?php
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;

$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;

//echo "funcionario  " . $fun . " *** cusuario  " . $cusuario . "**** xx  " . $idusuario;
//echo "flujo" . $flujook;
$flujo1=$flujook;
if ($flujook==8)
{
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
$fecha_i = date("d-m-Y");
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
	//document.form1.radiodescriptor.focus();
  }

// corregir para validar fecha de invitacion que no quede en blanco //

else 
 if (cont_arreglo != 0)
{
for (i=0; i < <?php echo $nRows;?>;i++)
 {
 // if (document.form1.casilla[i].checked)
  // {
   if (sw_invita==13 &&  document.form1.Txt_fecha_inv.value=="")
     {
 	sw_ok=false;
	i=<?php echo $nRows;?>;
	alert("Debe Ingresar Fecha de invitación ");
	document.form1.Txt_fecha_inv.focus();
     }
  // }
 }
}

//else
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
var valor="S";
selindice = document.form1.Cbo_Tipo_Docto.selectedIndex;
tipo_doc = document.form1.Cbo_Tipo_Docto.options[selindice].value;
proc=document.form1.val_procedencia.value;
tipo_p=document.form1.tipo_procedencia.value;
dest=document.form1.val_destino.value;
tipo_d=document.form1.tipo_destino.value;
num_ofi=document.form1.TxtOficial.value;
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


function fecha_invitacion(filas)
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
//    document.form1.arreglo.value=x + "@" + arreglo2;

var b = document.form1.arreglo.value
var temp = new Array();
temp = b.split('@');
x = temp[0];
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
	document.form1.action="multi_pages.php";
   	document.form1.submit();
} 
//-->
</script>
<script language="JavaScript" type="text/JavaScript">
<!--
var flujo2 = <?php echo $flujook; ?>;  
var numint = <?php echo $num_int; ?>;  

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
  
  document.form1.cmd_grabar.disabled=true;
  alert("El Documento ha sido grabado con el Nro Interno : "+ numint );
  
  var idusuario= <?php  echo $idusuario;?>;
  var cusuario="<?php  echo $cusuario;?>";
  var idfuncionario =<?php echo $idfuncionario ;?>;
  var num =<?php echo $num_int ;?>;
  var iddocum=<?php echo $iddocum;?>; // numero del id_factura   
  var origen =<?php echo $origen ;?>;
  Respuesta=confirm("Desea generar Nómina para Despachar ");
  if (Respuesta==true) { 
       document.form1.action="multi_pages_factura_deriva.php?cusuario=" +cusuario+ "&idusuario=" + idusuario+ "&iddocum=" + iddocum +  "&num_int=" + num +"&idfuncionario="+idfuncionario +"&origen="+origen  ;
        }
  else
      {
      document.form1.action="deriva_sdoc_factura_prueba.php?idusuario=" + idusuario + "&cusuario=" + cusuario +  "&iddocum=" + iddocum +  "&num_int=" + num +  "&idfuncionario="  +idfuncionario + "&numero=". numint;
        }

     
  document.form1.submit();

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

var isNS4 = (navigator.appName=="Netscape")?1:0;

function valida_caracter()
// valida  que el caracter ingresado no sea comilla simple , doble o salto de carro  19/06/2008 
// codigo 13 =salto de carro, 34=comilla doble , 39 =  apostrofe//
{
if(!isNS4)
 { 
 if ( event.keyCode==39 || event.keyCode==13  )
 { alert("No debe ingresar apóstrofe  o enter " );
 event.returnValue = false;
  }
  }
 else
 { 
  if (event.which==39 || event.which==13) 
   { alert("No debe ingresar apóstrofe   o enter " );
   return false;
   }
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
<script src="js/calendario.js"></script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>
<body bgcolor="#FFFFFF" topmargin="0" onLoad="muestra_cuadro()">
<div id="Layer4" style="position:absolute; left:647px; top:287px; width:170px; height:23px; z-index:3; visibility: visible;"> 
  <input name="bt_descriptor" type="button" class="botones" id="bt_descriptor" onClick="javascript:fnOpen_descriptor(document.form1.arreglo.value);" value="Descriptor">
</div>
<center>
<form name="form1" method="Post" action="guardar_ingreso2_prueba.php">
    <table width="700" border="1" cellpadding="1" cellspacing="0" bgcolor="#ECE9D8">
      <tr> 
        <td width="693" align="center" bgcolor="#e6eeff"> <table width="100%" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
            <tr> 
              <td width="650" height="34"> <div align="center"><font color="#FFFFFF" size="4"><strong>INGRESO 
                  DE DOCUMENTOS </strong></font></div></td>
            </tr>
          </table>
          <table width="100%" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="320" class="texto"><strong><font color="#804040">IDENTIFICACION 
                DEL DOCUMENTO</font></strong></td>
              <td width="322"><div align="right"><font color="#804040"><strong><font size="2"> 
                  <? echo "Usuario : " . $cusuario?></font></strong></font></div></td>
            </tr>
          </table>
          <table width="100%" height="78" border="1" cellpadding="0" cellspacing="0">
            <tr valign="middle"> 
              <td width="141" height="24"> Expediente Nuevo </td>
              <td width="176" height="24"> 
                <input type="checkbox" name="txtexp" value="checkbox" onClick="bloquea_expediente()" >
              </td>
              <td width="177" height="24">Asocia al Expediente N&ordm;. </td>
              <td width="190" height="24"> 
                <input name="txtexped" type="text" class="entradas" onchange ="valida_digito(this.value,this,8);exped_ing();" value ="<?php echo  $num_exp?>"  size="10" maxlength="10">
                <font color="#000000" size="2">
                <input name="Buscar" type="button" class="botones" onClick ="Buscar_expediente();" value="Buscar">
                </font> </td>
            </tr>
            <tr> 
              <td height="47">Descripci&oacute;n</td>
              <td colspan="3"><font color="#000000" size="2"> 
                <textarea name="txtdescrip" cols="60" rows="2" class="cajatexto" onKeyPress="return CheckLength(100)"  ><?php echo $descexped; ?></textarea>
                </font></td>
            </tr>
          </table>
          <table width="100%" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="630" height="220" align="center"> 
                <div align="center"> 
                  <table width="100%" border="0" cellspacing="0" cellpadding="1">
                    <tr> 
                      <td width="103" height="20"><font color="#000000">Tipo de 
                        Docto</font></td>
                      <td width="120" height="20"> <font color="#000000"> 
                        <select name="Cbo_Tipo_Docto" class="combo" id="select5" onclick="desbloquea_grabar();">
                          <?
						   while($reg=mssql_fetch_array($rs_tipo_docto)){
							?>
	                         <option value=<? echo $reg[id_tipo_documento] ?> ><? echo $reg[desc_tipo_documento] ?></option>
    	                     <?
							}
						  ?>
                        </select>
                        </font></td>
                     <td width="114" height="20">
					<div align="right"><font color="#000000">Fecha Docto</font></strong></div> </td>
                      <td height="20" colspan="2" valign="middle"><font color="#000000" face=		"Arial, Helvetica, sans-serif"> 
                        &nbsp;&nbsp; 
                        <input name="Txt_fecha_doc" type="text" class="entradas" id="Txt_fecha_doc3" value="<?=$fecha_x?>" onBlur="chequeafecha(this,0)" size="8" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="../../sisdoc/sisdoc_alertas/imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                        &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; 
                        <input name="Original" type="checkbox" value="S" checked>
                        </font><font color="#000000">Original </font></td>
                    </tr>
                    <tr> 
                      <td height="20"><font color="#000000">Estado</font></td>
                      <td height="20">
<p><font color="#000000"><strong>Documento Nuevo</strong></font></p></td>
                      <td height="20">
<div align="right"><font color="#000000">Medio</font></div></td>
                      <td width="98" height="20"> <font color="#000000"> 
                        <select name="Cbo_Medio" class="combo" id="select2">
                          <option value="P" <?php if($Cbo_Medio=="P") { echo 'SELECTED'; } ?> >Papel</option>
                          <option value="C" <?php if($Cbo_Medio=="C") { echo 'SELECTED'; } ?> >Correo</option>
                          <option value="V" <?php if($Cbo_Medio=="V") { echo 'SELECTED'; } ?> >Video</option>
						  <option value="F" <?php if($Cbo_Medio=="F") { echo 'SELECTED'; } ?> >Fax</option>
                         <option value="G" <?php if($Cbo_Medio=="G") { echo 'SELECTED'; } ?> > Virtual</option>
                        </select>
                        </font></td>
                      <td width="193" height="20">&nbsp;</td>
                    </tr>
                  </table>
                  <table width="100%" border="0">
                    <tr> 
                      <td width="16%" height="21"><font color="#000000"><strong>N&uacute;meros 
                        : </strong></font></td>
                      <td width="20%"><font color="#000000">Oficial<font size="4" face="Arial"> 
                        <input name="TxtOficial" type="text" class="entradas" id="TxtOficial" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                        </font></font></td>
                      <td width="32%"><font color="#000000">Externo<font size="4" face="Arial"> 
                        <input name="TxtExterno" type="text" class="entradas" id="TxtExterno" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                        </font></font></td>
                      <!--<td width="32%">Id. CodIbm <font color="#000000"><font size="4" face="Arial"> 
                        </font><font color="#000000" face="Arial, Helvetica, sans-serif"></font><font color="#000000"><font size="4" face="Arial">
                        <input name="Txtidcodibm" type="text" class="entradas" id="Txtidcodibm" onBlur="valida_digito(this.value,this,8);" size="10" maxlength="10">
                        </font></font><font size="4" face="Arial"> </font></font></td>-->
                    </tr>
                  </table>
                  <table width="100%" border="0" valign="top" cellspacing="2" cellpadding="2">
                    <tr> 
                      <td width="60" height="100"><font color="#000000"><strong><font size="2">Materia</font></strong> 
                        </font></td>
                      <td><font color="#000000" size="2"> 
                        <textarea name="TxtMateria"  cols="60" rows="4" class="cajatexto" onKeyPress="valida_caracter();return CheckLength(250)"></textarea>
                        </font> <div id="Layer2" style="position:absolute; width:188px; height:35px; z-index:1; left: 631px; top: 315px; visibility: hidden; overflow: auto; background-color: #E6EEFF; layer-background-color: #E6EEFF; border: 1px none #000000;" class="texto"> 
                          <p><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                            </font> Fecha Invitacion<font color="#000000" face="Arial, Helvetica, sans-serif"> 
                            <input name="Txt_fecha_inv" type="text" class="entradas" id="Txt_fecha_inv" size="10" maxlength="10" >
                            <a href="javascript:show_Calendario('form1.Txt_fecha_inv');"><img src="../../sisdoc/sisdoc_alertas/imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a></font></p>
                        </div>
                        <div id="Layer1" style="position:absolute; width:219px; height:115px; z-index:1; left: 479px; top: 250px; visibility: hidden; overflow: auto; background-color: #E6EEFF; layer-background-color: #E6EEFF; border: 1px none #000000;" class="texto"> 
                          <table width="100%" border="1" bgcolor="#E6EEFF">
							 <tr> 
                             <td height="23"> <div align="center" onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide');fecha_invitacion(<?php echo $nRows;?>)"><font color="#000000"><strong>Aceptar</strong></font></div></td>
   							  
                            </tr>

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
                           <!-- <tr> 
                             <td height="23"> <div align="center" onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide');fecha_invitacion(<?php echo $nRows;?>)"><font color="#000000"><strong>Aceptar</strong></font></div></td>
   							  
                            </tr>-->
                          </table>
                          <div align="right"></div>
                        </div>
                        <font color="#000000" size="2">&nbsp; </font>
                        <div id="Layer3" style="position:absolute; left:663px; top:300px; width:112px; height:22px; z-index:2; visibility: hidden; overflow: hidden;"><font color="#000000">Descriptores 
                          <input type="radio" name="radiodescriptor" value="radiobutton"   onClick="MM_showHideLayers('Layer1','','show')">
                          </font></div> 
                        
                      </td>
                      <td width="205"> <p>&nbsp; </p></td>
                    </tr>
                  </table>
                </div></td>
            </tr>
          </table>
		  <table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="304" class="texto"><font color="#804040"><strong>TRAMITE 
                DEL DOCUMENTO</strong></font></td>
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
            <td width="254"><textarea name="TxtObservacion" cols="50" rows="3" class="cajatexto"  id="textarea" onKeyPress="return CheckLength(250);"></textarea></td>
            <td width="346">Despachado por Oficina de Partes 
              <input name="checkofpartes2" type="checkbox" id="checkofpartes22" value="S"></td>
          </tr>
        </table>
        
          <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr> 
            <td height="37" width="347"> 
			
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
				<input type="hidden" name="num_int" value="<?php echo $num_int;?>">
                <input type="hidden" name="Cbo_Estado_Docto" value="<?php echo 1;?>">
				<input type="hidden" name="Cbo_Estado_Compromiso" value="<?php echo 2;?>">
				<input type="hidden" name="arregloint" >
				<input type="hidden" name="arregloext" >
				<input type="hidden" name="inv" >
				<input type="hidden" name="totexped" >
				<input type="hidden" name="descexped" >
				<input type="hidden" name="num_exp" >
				<input type="hidden" name="fechainv" value "<? echo $Txt_fecha_inv;?>">
				<input type="hidden" name="menu" value = "<? echo 1  ;?>">
				<input type="hidden" name="iddocum" value = "<? echo $iddocum ;?>">
				<input type="hidden" name="idseguim" value = "<? echo $idseguim ;?>">
				<input type="hidden" name="origen" value = "<? echo $origen ;?>">
				
				<input name="cmd_grabar" type="button" class="botones" onClick="chequear_arreglo(<?php echo $nRows?>);chequear_arregloint(<?php echo $nRowsint?>);chequear_arregloext(<?php echo $nRowsext?>);validar_datos();" value="Grabar">
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
