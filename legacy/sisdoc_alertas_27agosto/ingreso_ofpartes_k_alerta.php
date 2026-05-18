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
$num_exp= 0;
$fun=$idfuncionario;
$sw_ext1=$sw_ext;
$tottipo =mssql_num_rows($rs_tipo_docto);
$totdes1 =mssql_num_rows($rs_cod_descriptor1);
$totdes2 =mssql_num_rows($rs_cod_descriptor2);
$totdes3 =mssql_num_rows($rs_cod_descriptor2);

$totdist  = mssql_num_rows($rs_distribucion);

//$nRowsint = mssql_num_rows($rs_dependencia);
// se cambia para que active solo los que se requieren 
$nRowsint = mssql_num_rows($rs_dependencia_ofpartes);
$nRowsext = mssql_num_rows($rs_dependencia_externa);

//echo "funcionario  " . $fun . " *** cusuario  " . $cusuario . "**** xx  " . $idusuario;
$flujo1=$flujook;
if ($flujook==8)
{$num_int=0;}
else{$num_int=$num_int;}

$rs_funcionario = mssql_query("SELECT id_dependencia FROM funcionario where id_funcionario = " . $fun, $cn);

$reg_func = mssql_fetch_array($rs_funcionario);
$Tot_fun  = mssql_num_rows($rs_funcionario);


$rs_servicio= mssql_query("SELECT * FROM descriptor order by desc_descriptor", $cn);
$nRows = mssql_num_rows($rs_servicio);
$Cbo_Estado_Docto=1;
$fecha_x = date("d-m-Y");
$fecha_i = date("d-m-Y");


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario ingreso of. de partes </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/javascript">
<!--

// Arreglo para  guardar informacion de la procedencia y poder grabar el id_procedencia y no el cod_procedencia
var arr_cod_dep=new Array();
var arr_id_dep=new Array();
var arr_nom_dep=new Array()
  <? 
  $i=0;
  while($reg_dep=mssql_fetch_array($rs_dependencia_externa))
   {
   	 echo "arr_cod_dep[" . $i . "]='" . $reg_dep[cod_dependencia_externa] . "';\n";
  	 echo "arr_id_dep[" . $i . "]='" . $reg_dep[id_dependencia_externa] . "';\n";
   	 echo "arr_nom_dep[" . $i . "]='" . $reg_dep[desc_dependencia_externa] . "';\n";
     $i=$i+1;
   }
     echo "var arr_largo =" . $i . ";";
  ?>


// Arreglo para  guardar informacion del destino y poder grabar el id_destinatario y no el cod_destinatario
var arr_cod_dest=new Array();
var arr_id_dest=new Array();
var arr_nom_dest=new Array()
  <? 
  $i=0;
 //while($reg_dest=mssql_fetch_array($rs_dependencia))
    while($reg_dest=mssql_fetch_array($rs_dependencia_ofpartes))
 {
   	//echo "arr_cod_dest[" . $i . "]='" . $reg_dest[cod_dependencia] . "';\n";
  	 // para que considere codigo nuevo 
	 echo "arr_cod_dest[" . $i . "]='" . $reg_dest[cod_dependencia_nuevo] . "';\n";
  	 echo "arr_id_dest[" . $i . "]='" . $reg_dest[id_dependencia] . "';\n";
   	 echo "arr_nom_dest[" . $i . "]='" . $reg_dest[desc_dependencia] . "';\n";
     $i=$i+1;
   }
     echo "var arr_largo_dest =" . $i . ";";
  ?>


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
// busca descripcion de tipo documento 
function busca_tipodoc()
{
var sw= 0;
  if (retorno)
  {
  if (document.form1.Txttipodoc.value!="")
  {
  largo= <?php echo $tottipo; ?>;  
  for ( i=0; i < largo; i++)
  { 
    if (document.form1.Txttipodoc.value==document.form1.Cbo_Tipo_Docto[i].value)
	{
    	sw=1;
		document.form1.Cbo_Tipo_Docto.selectedIndex = i;
	}
  }
 if (sw ==0)
	{
	alert(" Código no existe");
	document.form1.Txttipodoc.value="";
	document.form1.Txttipodoc.focus();
	}
  }
  }
}
// busca tipo de documento para layer  segun tema y reiteracion 
function busca_tipodoc2()
{ 
var sw= 0;
  if (retorno)
  {
  if (document.form2.Txttipodoc_2.value!="")
  {   
  largo= <?php echo $tottipo; ?>;  
  for ( i=0; i < largo; i++)
  { 
    
    if (document.form2.Txttipodoc_2.value==document.form2.Cbo_Tipo_Docto2[i].value)
    {
    	sw=1;
		document.form2.Cbo_Tipo_Docto2.selectedIndex = i;
	}
  }
 if (sw ==0)
	{
	alert(" Código no existe");
	document.form2.Txttipodoc_2.value="";
	document.form2.Txttipodoc_2.focus();
	}
  }
  }
}
// obtiene el codigo del tipo de documento seleccionado desde el combo
function obtiene_tipodoc()
{
document.form1.Txttipodoc.value = document.form1.Cbo_Tipo_Docto.options.value;
}

function obtiene_tipodoc2()
{
document.form2.Txttipodoc_2.value = document.form2.Cbo_Tipo_Docto2.options.value;
}



function busca_distribucion()
{
  var sw=0;
  if (retorno)
  {
  if (document.form1.Txtdistribucion.value!="")
  	{
  	largo= <?php echo $totdist; ?>; 
  	for ( i=0; i < largo; i++) { 
    	if (document.form1.Txtdistribucion.value==document.form1.Cbo_Tipo_Distribucion[i].value)
		{
		sw=1;
    	document.form1.Cbo_Tipo_Distribucion.selectedIndex = i;
		}
	}
	if (sw ==0)
	{
	alert(" Código no existe");
	document.form1.Txtdistribucion.value="";
	document.form1.Txtdistribucion.focus();
	}
    }
  }
}
function obtiene_tipodis()
{
document.form1.Txtdistribucion.value = document.form1.Cbo_Tipo_Distribucion.options.value;
}
// valida que los 3 descriptores sean distintos 
function valida_iguales()
{
	if ((document.form1.Txtdesc1.value!="" && document.form1.Txtdesc2.value!="")
	||  (document.form1.Txtdesc1.value!="" && document.form1.Txtdesc3.value!="")
	||  (document.form1.Txtdesc2.value!="" && document.form1.Txtdesc3.value!=""))
	{
		if ((document.form1.Txtdesc1.value==document.form1.Txtdesc2.value)
		|| (document.form1.Txtdesc1.value==document.form1.Txtdesc3.value)
		||	(document.form1.Txtdesc2.value==document.form1.Txtdesc3.value))
		{
		  alert ("Ya esta ingresado");
		  //document.form1.Txtdesc1.focus();
		}
	}		
}
// valida que la fecha se pueda ingresar solo para el caso de las invitaciones 
function fechainv(cual)
{
//   MM_showHideLayers('Layer2','','hide');
  document.form1.Txt_fecha_inv.value="";
  if (cual ==1 &&  document.form1.Txtdesc1.value==13 )
	{
	   MM_showHideLayers('Layer2','','hide');
   	   MM_showHideLayers('Layer2','','show');
	}
	   if (cual ==2 && document.form1.Txtdesc2.value==13 )
	{
  		MM_showHideLayers('Layer2','','hide');
   	    MM_showHideLayers('Layer2','','show');
	}
  if (cual ==3 && document.form1.Txtdesc3.value==13 )
	{
	   MM_showHideLayers('Layer2','','hide');
	   MM_showHideLayers('Layer2','','show');
	}
}
function busca_docto()
// busca el documento con el que va a relacionar el que viene (si es reiteración)
{ 
   	if (document.form1.reitera.checked==true)
	   {MM_showHideLayers('Layer_docto','','show');}
     else
   	   {MM_showHideLayers('Layer_docto','','hide');}

}
//buscar documento que esté antes  con el tema (original a la reiteración)
function busca_docorigen()
{
  
 var tipodoc =document.form2.Txttipodoc_2.value;
 var externo =document.form2.numexterno.value;
 var fechadoc =document.form2.Txt_fecha_doc2.value;
 var orig=document.form1.Txtprocedencia.value;
  alert ("origen " + orig);
 var valor ="BO";
 top.window.frame_consultas.location.href="frame_consultas.php?sw="+valor+"&tipodoc=" +tipodoc+ "&origenproc =" +orig +"&externo=" +externo+"&fechadoc="+fechadoc;
 }

// 
function aceptar_enlace()
{
 document.form2.aceptaok.value ='S';
}
// busca  la descripcion del codigo ingresado  y limpia la viable fecha invitacion
function busca_cod_descriptor(cual)

{
var largo= 0;
	 if (cual == 1)
	{
	   if (document.form1.Txtdesc1.value!="")
  		{
		  largo= <?php echo $totdes1; ?>;  
		  for ( i=0; i < largo; i++)
		  { 
		    if (document.form1.Txtdesc1.value==document.form1.Cbo_descriptor1[i].value)
			{
	    	document.form1.Cbo_descriptor1.selectedIndex= i;
			}
		  }
	    }
		if (document.form1.Cbo_descriptor1.selectedIndex==0 && document.form1.Txtdesc1.value !="")
		{
		  alert("Descriptor no existe");
		  document.form1.Cbo_descriptor1.options[1].focus();
		  //document.form1.Cbo_descriptor1.options[0].value='0';
		 // document.form1.Cbo_descriptor1.options[0].text="";
		  document.form1.Txtdesc1.focus();			
		}
	}
	else if (cual == 2)
	{
	   if (document.form1.Txtdesc2.value!="")
  		{
		  largo= <?php echo $totdes2; ?>;  
		  for ( i=0; i < largo; i++)
		  { 
		    if (document.form1.Txtdesc2.value==document.form1.Cbo_descriptor2[i].value)
			{
	    	document.form1.Cbo_descriptor2.selectedIndex= i;
			}
		  }
	   }
	  if (document.form1.Cbo_descriptor2.selectedIndex==0 && document.form1.Txtdesc2.value !="")
		{
		  alert("Descriptor no existe");
		  //document.form1.Cbo_descriptor2.options[0].value='0';
		  //document.form1.Cbo_descriptor2.options[0].text="";
		  document.form1.Cbo_descriptor2.options[1].focus();
		  document.form1.Txtdesc2.focus();			
		}
	}	  	   
	else if (cual == 3)
	{
	   if (document.form1.Txtdesc3.value!="")
  		{
		  largo= <?php echo $totdes3; ?>;  
		  for ( i=0; i < largo; i++)
		  { 
		    if (document.form1.Txtdesc3.value==document.form1.Cbo_descriptor3[i].value)
			{
	    	document.form1.Cbo_descriptor3.selectedIndex= i;
			}
		  }
	   }
	   if (document.form1.Cbo_descriptor3.selectedIndex==0 && document.form1.Txtdesc3.value !="")
		{
		  alert("Descriptor no existe");
//		  document.form1.Cbo_descriptor3.options[0].value='0';
	//	  document.form1.Cbo_descriptor3.options[0].text="";
		document.form1.Cbo_descriptor3.options[1].focus();
		  document.form1.Txtdesc3.focus();			
		}	   
	}		   
	if (document.form1.Txtdesc1.value != 13 && document.form1.Txtdesc2.value != 13 && document.form1.Txtdesc3.value!= 13)
	 {
	 
	 document.formfecha.Txt_fecha_inv.value="";
	 //alert ("fecha" + document.formfecha.Txt_fecha_inv.value); 
	 }

}
// permite seleccionar desde el combo el descriptor 1 
function obtiene_desc1()
{
document.form1.Txtdesc1.value = document.form1.Cbo_descriptor1.options.value;
  valida_iguales();
  busca_cod_descriptor(1);
  fechainv(1);
  if (document.form1.Txtdesc1.value!=13 && document.form1.Txtdesc2.value != 13 && document.form1.Txtdesc3.value != 13)
  {
    MM_showHideLayers('Layer2','','hide');
   }

}

function obtiene_desc2()
{
document.form1.Txtdesc2.value = document.form1.Cbo_descriptor2.options.value;
valida_iguales();
  busca_cod_descriptor(2);
  fechainv(2);
 if (document.form1.Txtdesc1.value!=13 && document.form1.Txtdesc2.value != 13 && document.form1.Txtdesc3.value != 13)
  {
    MM_showHideLayers('Layer2','','hide');
   }
}

function obtiene_desc3()
{
document.form1.Txtdesc3.value = document.form1.Cbo_descriptor3.options.value;
valida_iguales();
  busca_cod_descriptor(3);
  fechainv(3);
 if (document.form1.Txtdesc1.value!=13 && document.form1.Txtdesc2.value != 13 && document.form1.Txtdesc3.value != 13)
  {
    MM_showHideLayers('Layer2','','hide');
   }
}

// permite sacar la fecha de pantalla cuando no es invitacion y deja como descripcion el 1ero de la tabla 
function cambia_descriptor(cual)
{
if (document.form1.Txtdesc1.value!=13 && document.form1.Txtdesc2.value != 13 && document.form1.Txtdesc3.value != 13)
{

  MM_showHideLayers('Layer2','','hide');
}
if (cual==1)
{
document.form1.Cbo_descriptor1.value=document.form1.Cbo_descriptor1.options[0].value='0';
}
else if (cual ==2)
{

document.form1.Cbo_descriptor2.value=document.form1.Cbo_descriptor2.options[0].value='0';
}
else if (cual ==3)
{
document.form1.Cbo_descriptor3.value=document.form1.Cbo_descriptor3.options[0].value='0';
}

}
function busca_tema()
{
  var selindice, nuevalsel;
  var valor="BT";
  //alert ("busca tema");
  if (document.form1.Txttema.value!="")
  {
    tema=document.form1.Txttema.value;
    dep=document.form1.Txtprocedencia.value;
    top.window.frame_consultas.location.href="frame_consultas.php?cod="+tema+"&sw="+valor+"&dep=" +dep;
  }  

}

function obtiene_tema()
{
var valor="OTE";
document.form1.Txttema.value = document.form1.Cbo_tema.options.value;
tema=document.form1.Txttema.value;
dep=document.form1.Txtprocedencia.value;
top.window.frame_consultas.location.href="frame_consultas.php?cod="+tema+"&sw="+valor+"&dep=" +dep;
}


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

function obtiene_procedencia()
{
var valor="OT";
document.form1.Txtprocedencia.value = document.form1.Cbo_Procedencia.options.value;
document.form1.Txttema.value=0;
document.form1.TxtDias.value = 0;
dep=document.form1.Txtprocedencia.value;
top.window.frame_consultas.location.href="frame_consultas.php?cod="+dep+"&sw="+valor;
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

function obtiene_destinatario()
{
document.form1.Txtdestino.value = document.form1.Cbo_Destinatario.options.value;
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
if (document.form1.Txt_fecha_doc.value < document.form1.Txt_fecha_timbre.value)
 { sw_ok=false;
   alert ("Fecha de documento no debe ser menor a fecha de timbre de recepción");
   document.form1.Txt_fecha_timbre.focus();
 }
 else
if (document.form1.Txt_fecha_timbre.value =="")
 { sw_ok=false;
   alert ("Debe ingresar fecha de Recepción");
   document.form1.Txt_fecha_timbre.focus();
 }
else
if(document.form1.TxtMateria.value == "")
  {
 	sw_ok=false;
	alert("Falta Ingresar la Materia del Documento");
	document.form1.TxtMateria.focus();
  }
else
//if(cont_arreglo==0)
//{
 if(document.form1.Txtdesc1.value=="" && document.form1.Txtdesc2.value=="" && document.form1.Txtdesc3.value=="" ) 
  
  { 
	sw_ok=false;
	alert("Debe Ingresar al menos un Descriptor");
	document.form1.Txtdesc1.focus();
  }
  else
  if ((document.form1.Txtdesc1.value==13  || document.form1.Txtdesc2.value==13 || document.form1.Txtdesc3.value==13 )&&    		 document.formfecha.Txt_fecha_inv.value =="") 
  
  {
   sw_ok=false;
   alert ("Debe ingresar fecha de invitacion");
   document.formfecha.Txt_fecha_inv.focus();
  } 
//}  
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
var fecha = document.form1.Txt_fecha_doc.value;
var tipo =	"E";

//alert ('procedencia ' + p + 'tipo documento ' + t + 'txtexterno ' + n + ' tipo procedencia ' + tipo+ "fecha "+ fecha);
if(sw_ok) top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+t+"&pro_d="+p+"&des_d="+n+"&tip="+tipo+"&fec="+fecha+"&sw=BP";

/*if (sw_ok)
{
document.form1.submit();
}
*/
}

function valida_dd()
{

    var valor ="VD";
	var dia =document.form1.TxtDias.value;
	var comp =document.form1.idcomp.value;
	top.window.frame_consultas.location.href="frame_consultas.php?sw="+valor+"&dd="+dia+"&idcomp="+comp;
}



function fecha_invitacion(filas)
{
var j= 0;
MM_showHideLayers('Layer2','','hide');

for (i=0; i<filas;i++)
{

  if (document.form1.casilla[i].checked)
  {
     if (document.form1.casilla[i].value==13)
	 {
    //    alert("invitacion, " + document.form1.casilla[i].value );
	   //document.form1.inv.value = document.form1.casilla[i].value;
	   //j= 1;
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
 /* for (k=0;k<filas;k++)
  {
     if (document.form1.casilla[k].checked)
     {
       arreglo2=arreglo2+document.form1.casilla[k].value+"@";
	   x=x+1;
	  }
  }
  */
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
  	document.form1.Txt_fecha_inv.value=document.formfecha.Txt_fecha_inv.value;
    document.form1.cbo_esc_proc.value=arr_id_dep[document.form1.Cbo_Procedencia.selectedIndex];
    document.form1.cbo_esc_dest.value=arr_id_dest[document.form1.Cbo_Destinatario.selectedIndex];
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
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor+"&numero="+numero;
}

function chequeafecha_inv(objeto,calendario) 
{
  var campodefecha = objeto;
  if (chkfecha_inv(objeto,calendario) == false) 
  {
     campodefecha.select();
     alert("Formato de fecha Incorrecto\r\rUtilice el siguiente formato:\r       05-10-1988\r       día-mes-año")
     campodefecha.value='';
     campodefecha.focus();
     return false;
  }
  else 
  {
     return true;
  }
}
function chkfecha_inv(objeto,cualcalen) 
{
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
function valida_fecha()
{
if (document.form1.Txt_fecha_doc.value < document.form1.Txt_fecha_timbre.value)
 { alert ("Fecha de documento no debe ser menor a fecha de timbre de recepción");
   document.form1.Txt_fecha_timbre.focus();
  }
}
function chequeafecha_docto(objeto,calendario) 
{
  var campodefecha = objeto;
  if (chkfecha_docto(objeto,calendario) == false) 
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
function chkfecha_docto(objeto,cualcalen) 
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
     campodefecha.value = cero_dia+intday + "-" + cero_mes+intMonth + "-" + strYear;
   }
   
   return true;
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
<script src="/js/calendario.js"></script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body bgcolor="#FFFFFF" topmargin="0" onLoad="muestra_cuadro()">
<center>
<!--form name="form1" method="Post" action="guardar_ofpartes.php"-->
<form name="form1" method="Post" action="guardar_ofpartes_k_alerta.php">


    <table width="663" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="657" height="34">
<div align="center"><font color="#FFFFFF" size="4"><strong>INGRESO DE DOCUMENTOS 
            OF. DE PARTES</strong></font></div></td>
      </tr>
    </table>
    <table width="702" height="664" border="1" cellpadding="1" cellspacing="0" bgcolor="#ECE9D8">
      <tr> 
        <td width="697" align="center" bgcolor="#e6eeff"> 
          <table width="640" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="320" class="texto"><strong><font color="#804040" face="Arial, Helvetica, sans-serif">IDENTIFICACION 
                DEL DOCUMENTO</font></strong></td>
              <td width="322"><div align="right"><font color="#804040"><strong><font size="2"> 
                  <? echo "Usuario : " . $cusuario?></font></strong></font></div></td>
            </tr>
          </table>
          <table width="675" height="85" border="1">
            <tr> 
              <td width="127" height="28"> Expediente Nuevo </td>
              <td width="158"><input type="checkbox" name="txtexp" value="checkbox" onclick="bloquea_expediente()" ></td>
              <td width="178">Asocia al Expediente N&ordm;. </td>
              <td width="184"> <input type="text" name="txtexped" value ="<?php echo  $num_exp?>"  size="10" maxlength="10" onchange ="valida_digito(this.value,this,8);exped_ing();"> 
                <input type="button" name="Buscar" value="Buscar" onClick ="Buscar_expediente();"> 
              </td>
            </tr>
            <tr> 
              <td height="49">Descripci&oacute;n</td>
              <td colspan="3"><font color="#000000" size="2"> 
                <textarea name="txtdescrip" cols="80" rows="2" class="cajatexto" onKeyPress="return CheckLength(100)"  ><?php echo $descexped; ?></textarea>
                </font></td>
            </tr>
          </table>
          <table width="677" height="278" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="561" height="29" align="center"><div align="right"><font color="#000000"><strong>Fecha 
                  Timbre Recepci&oacute;n</strong></font><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                  <input name="Txt_fecha_timbre" type="text" class="entradas" id="Txt_fecha_timbre2" value="<?=$fecha_x?>" onBlur="chequeafecha_docto(this,0)" size="8" maxlength="10">
                  <a href="javascript:show_Calendario('form1.Txt_fecha_rec');"><img src="imagen/icon-calen_f2.gif" name="calenda" width="25" height="20" hspace="2" vspace="0" border="0" align="bottom"></a></font></div></td>
              <td width="106" align="center"><div align="left"></div></td>
            </tr>
            <tr> 
              <td height="218" colspan="2" align="center"> <div align="center"> 
                  <table width="640" border="0" cellspacing="0" cellpadding="1">
                    <tr> 
                      <td width="92" height="33"><font color="#000000">Tipo de 
                        Docto</font></td>
                      <td width="59"> <font color="#000000"> 
                        <input name="Txttipodoc" type="text" id="Txttipodoc2" onBlur="valida_digito(this.value,this,8);busca_tipodoc();" size="3" maxlength="3">
                        </font></td>
                      <td width="218"><font color="#000000"> 
                        <select name="Cbo_Tipo_Docto" class="combo" id="select" onChange="obtiene_tipodoc()">
                          <? while($reg=mssql_fetch_array($rs_tipo_docto)){?>
                          <option value=<? echo $reg[id_tipo_documento] ?> ><? echo $reg[desc_tipo_documento] ?></option>
                          <? }?>
                        </select>
                        </font></td>
						
                      <td width="263" valign="middle"><font color="#000000"><strong>Estado 
                        : Documento Nuevo</strong></font><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font></td>
                    </tr>
                  </table>
                  <table width="640" border="0" cellpadding="1" cellspacing="0">
                    <tr> 
                      <td width="85" height="41"><font color="#000000">Fecha Docto</font></td>
                      <td width="96"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_doc" type="text" class="entradas" id="Txt_fecha_doc" value="<?=$fecha_x?>" onBlur="chequeafecha_docto(this,0);valida_fecha();" size="8" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"> 
                        <img src="imagen/icon-calen_f2.gif" name="calenda" width="25" height="20" hspace="2" vspace="0" border="0" align="bottom"> 
                        </a> </font></td>
                      <td width="54"> <div align="left"><font color="#000000" face="Arial, Helvetica, sans-serif">Original</font></div></td>
                      <td width="29"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                        <input name="Original" type="checkbox" value="S" checked>
                        </font></td>
                      <td width="42"> <div align="left"> <font color="#000000" size="2" face="Arial, Helvetica, sans-serif">Medio</font> 
                          <strong><font color="#000000" face="Arial, Helvetica, sans-serif"></font></strong> 
                        </div></td>
                      <td><div align="left"><font color="#000000"><font color="#000000"> 
                          <select name="Cbo_Medio" class="combo" id="select7">
                            <option value="P" <?php if($Cbo_Medio=="P") { echo 'SELECTED'; } ?> >Papel</option>
                            <option value="C" <?php if($Cbo_Medio=="C") { echo 'SELECTED'; } ?> >Correo</option>
                            <option value="V" <?php if($Cbo_Medio=="V") { echo 'SELECTED'; } ?> >Video</option>
                            <option value="F" <?php if($Cbo_Medio=="F") { echo 'SELECTED'; } ?> >Fax</option>
                          </select>
                          </font></font></div></td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr> 
                      <td width="85" height="23"><font color="#000000">N&ordm; 
                        Externo</font></td>
                      <td width="96"><font color="#000000"><font color="#000000"><font size="4" face="Arial"> 
                        <input name="TxtExterno" type="text" class="entradas" id="TxtExterno" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                        </font></font><font size="4" face="Arial"> </font></font></td>
                      <td width="54"><div align="left"></div></td>
                      <td width="29"><font color="#000000"><font color="#000000"> 
                        </font></font></td>
                      <td width="42"><font color="#000000"><font color="#000000"> 
                        </font><font size="4" face="Arial"> </font></font></td>
                      <td width="84"><div align="center"><font color="#000000"> 
                          </font></div></td>
                      <td width="236"><font color="#000000"><strong><font size="2">Descriptores</font></strong></font></td>
                    </tr>
                  </table>
                  <table width="60%" border="0" align="left" cellpadding="1" cellspacing="0" valign="top">
                    <tr> 
                      <td width="51" height="110"><font color="#000000"><strong><font size="2">Materia</font></strong> 
                        </font></td>
                      <td width="332"><font color="#000000" size="2"> 
                        <textarea name="TxtMateria"  cols="75" rows="4" class="cajatexto" onKeyPress="return CheckLength(250)"></textarea>
                        </font> <font color="#000000" size="2">&nbsp; </font> 
                      </td>
                    </tr>
                  </table>
                  <table width="257" border="0" align="left" cellpadding="1" cellspacing="1">
                    <tr> 
                      <td>1</td>
                      <td height="33"><font color="#000000"> 
                        <input name="Txtdesc1" type="text"  id="Txtdesc1" onBlur="valida_digito(this.value,this,2);valida_iguales();busca_cod_descriptor(1);fechainv(1);" onchange="cambia_descriptor(1);"  size="3" maxlength="3">
                        </font></td>
                      <td><font color="#000000"> 
                        <select name="Cbo_descriptor1" class="combo" id="select8" onChange="obtiene_desc1()">
                          <option value="0"> </option>
                          <?						
				         while($reg1=mssql_fetch_array($rs_cod_descriptor1)){?>
                          <option value=<? echo $reg1[id_descriptor] ?> > <? echo $reg1[desc_descriptor] ?></option>
                          <? }?>
                        </select>
                        </font></td>
                    </tr>
                    <tr> 
                      <td>2</td>
                      <td height="33"><font color="#000000"> 
                        <input name="Txtdesc2" type="text"  id="Txtdesc2" onBlur="valida_digito(this.value,this,2);valida_iguales();busca_cod_descriptor(2);fechainv(2);"  onchange="cambia_descriptor(2);" size="3" maxlength="3">
                        </font></td>
                      <td><font color="#000000"> 
                        <select name="Cbo_descriptor2" class="combo" id="select9" onChange="obtiene_desc2()">
                          <option value="0"> </option>
                          <?
				   while($reg2=mssql_fetch_array($rs_cod_descriptor2)){?>
                          <option value=<? echo $reg2[id_descriptor] ?> > <? echo $reg2[desc_descriptor] ?></option>
                          <? }?>
                        </select>
                        </font></td>
                    </tr>
                    <tr> 
                      <td width="9">3</td>
                      <td width="24" height="39"><font color="#000000"> 
                        <input name="Txtdesc3" type="text"  id="Txtdesc3" onBlur="valida_digito(this.value,this,2);valida_iguales();busca_cod_descriptor(3);fechainv(3);" onchange="cambia_descriptor(3);" size="3" maxlength="3">
                        </font></td>
                      <td width="214"><p><font color="#000000"> 
                          <select name="Cbo_descriptor3" class="combo" id="select10" onChange="obtiene_desc3()">
                            <option value="0"> </option>
                            <?
				   while($reg3=mssql_fetch_array($rs_cod_descriptor3)){?>
                            <option value=<? echo $reg3[id_descriptor] ?> > <? echo $reg3[desc_descriptor] ?></option>
                            <? }?>
                          </select>
                          </font></p></td>
                    </tr>
                  </table>
                  <p>&nbsp;</p>
                  <p>&nbsp;</p>
                </div></td>
            </tr>
          </table>
		  <table width="653" height="25" border="0" cellpadding="1" cellspacing="1">
            <tr> 
              <td width="649" height="23" class="texto"><font color="#804040" face="Arial, Helvetica, sans-serif"><strong>TRAMITE 
                DEL DOCUMENTO</strong></font></td>
          </tr>
		</table>  
          <table width="673" border="1" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="667" height="83"> 
                <table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="78" height="36">Procedencia</td>
                    <td width="41"><font face="Arial"> 
                      <input name="Txtprocedencia" type="text" id="Txtprocedencia3" onBlur="busca_procedencia();" size="6" maxlength="6">
                      </font></td>
                    <td width="164"><font face="Arial"> 
                      <select name="Cbo_Procedencia" class="combo" id="select3" onChange="obtiene_procedencia();">	
                        <script>
                           for(i=0;i<arr_largo;i++)
                           {
                                document.write('<option value="'+arr_cod_dep[i]+'">'+arr_nom_dep[i]+'</option>');
                            }    
                        </script>
                      </select>
                      </font></td>
                    <td width="76"> <font color="#000000">&nbsp; <font face="Arial">&nbsp;</font></font></td>
                    <td width="292"><font face="Arial">&nbsp; </font><font color="#000000">&nbsp; 
                      </font><font face="Arial">&nbsp; </font><font color="#000000">&nbsp; 
                      </font></td>
                  </tr>
                  <tr>
                    <td height="28"><font color="#000000"><strong>Tema</strong> 
                      <font face="Arial"></font></font></td>
                    <td><font color="#000000"> 
                      <input name="Txttema" type="text" id="Txttema5" onBlur="valida_digito(this.value,this,8);busca_tema();" size="3" maxlength="3">
                      </font></td>
                    <td><font face="Arial">
                      <select name="Cbo_tema" class="combo" id="select15" onchange="obtiene_tema()" >
                        <option value="0" text="---Seleccione Tema--- "> </option>
                        <? /* while($reg=mssql_fetch_array($rs_tema)){?>
                        <option value=<? echo $reg[id_tema] ?>><? echo $reg[desc_tema] ?></option>
                        <? }*/?>
                      </select>
                      </font></td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr> 
                    <td width="78" height="39">Destino</td>
                    <td width="41"> <div align="left"><font face="Arial"> 
                        <input name="Txtdestino" type="text" id="Txtdestino2" size="6" maxlength="6" onBlur="busca_destino();">
                        </font></div></td>
                    <td width="164"><font face="Arial"> 
                      <select name="Cbo_Destinatario" class="combo" id="select5" onChange="obtiene_destinatario();">
                        <script>
                           for(i=0;i<arr_largo_dest;i++)
                           {
                                document.write('<option value="'+arr_cod_dest[i]+'">'+arr_nom_dest[i]+'</option>');
                            }    
                          </script>
                      </select>
                      </font></td>
                    <td width="76"><font face="Arial"> 
                      <input type="button" name="boton" value="Múltiple" onClick="javascript:ver_destino();">
                      </font></td>
                  </tr>
                </table></td>
            </tr>
          </table>
          <table width="674" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="22%" height="34">Tipo Distribuci&oacute;n</td>
              <td width="13%"><font face="Arial"> 
                <input name="Txtdistribucion" type="text" id="Txtdistribucion" onBlur="valida_digito(this.value,this,4);busca_distribucion();" size="4" maxlength="4">
                </font></td>
              <td width="24%"><font face="Arial">
                <select name="Cbo_Tipo_Distribucion" class="combo" id="select9" onChange="obtiene_tipodis()">
                  <? while($reg_distribucion=mssql_fetch_array($rs_distribucion)){?>
                  <option value=<? echo $reg_distribucion[id_tipo_distribucion] ?> >
				  <? echo $reg_distribucion[desc_tipo_distribucion] ?></option>
                  <? }?>
                </select>
                </font></td>
            <!-- ************************************** Antes ********************************************************* -->				
            <!--<td width="20%">Tipo Compromiso</td>
            <td width="30%"><font face="Arial">
                <select name="Cbo_Tipo_Compromiso" class="combo" id="select14">
                  <?
					//while($reg_tipo_compromiso=mssql_fetch_array($rs_tipo_compromiso)){
					?>
                  <option value=<? //echo $reg_tipo_compromiso[id_tipo_compromiso] ?> ><? //echo $reg_tipo_compromiso[desc_tipo_compromiso] ?> 
                </option>
                <?
					//}
					?>
              </select>
              </font></td> -->
<!-- ***************************************************************************************************** -->	
			<input type="hidden" name="Cbo_Tipo_Compromiso" value="1">
             <td width="20%">D&iacute;as Compromiso</td>
		     <td width="18%"><input name="TxtDias"  type="text"  value="0" class="entradas" onBlur="valida_digito(this.value,this,2);valida_dd();"  size="2" maxlength="2"></font> 
                <div id="LayerInt" style="position:absolute; width:289px; height:187px; z-index:1; left: 541px; top: 508px; visibility: hidden; overflow: auto; background-color: #E6EEFF; layer-background-color: #E6EEFF; border: 1px none #000000;" class="texto"> 
                  <table width="100%" border="1" bgcolor="#E6EEFF">
                    <tr> 
                        <td> <div align="center" onClick="MM_showHideLayers('LayerInt','','hide');MM_showHideLayers('LayerInt','','hide');ver_check(<?php echo $nRowsint;?>)"><strong>Aceptar</strong></div>
					</td></tr>
					<tr>	 
                      <td height="129"> 
                           <script>
                           for(i=0;i<arr_largo_dest;i++)
                           {
                         document.write('<input type="checkbox" name="casilla2" value="'+arr_id_dest[i]+'">'+ arr_nom_dest[i]+'<br>' );
                            }
                       </script>					   
                      </td>
                    </tr>
                    <!--tr> 
                      <td height="24"> 
                        <div align="center" onClick="MM_showHideLayers('LayerInt','','hide');MM_showHideLayers('LayerInt','','hide');ver_check(<?php echo $nRowsint;?>)"><strong>Aceptar</strong></div></td>
                    </tr-->
                  </table>
                  <div align="right"></div>
                </div></td>
            </tr>
            <tr> 
              <!--td height="41">Tipo Compromiso</td>
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
                  <strong> </strong></font></div></td-->
              <!--td>&nbsp;</td-->
              <td>Estado del Compromiso</td>
              <td><font face="Arial"><strong>En Trámite</strong></font></td>
            </tr>
          </table>
          <table width="668" border="0" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="81" height="73"><strong><font size="2">Observaci&oacute;n</font></strong><br>
            </td>
            <td width="244"><textarea name="TxtObservacion" cols="50" rows="3" class="cajatexto"  id="textarea" onkeypress="return CheckLength(250);"></textarea></td>
              <td width="328">&nbsp;</td>
          </tr>
        </table>
        
          <table width="652" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr> 
              <td height="63" width="323"> 
                <div align="center"> 
                 <input type="hidden" name="cbo_esc_proc">     
                 <input type="hidden" name="cbo_esc_dest">     
                <input type="hidden" name="estado_tramite" value="1">
                <input type="hidden" name="resuelto" value="N">
                <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
                  <input type="hidden" name="val_funcionario" value="0">
                  <input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
				<input type="hidden" name="tipo_destino" value="I">
                  <input type="hidden" name="tipo_procedencia" value="E">
				<input type="hidden" name="val_procedencia" >
				<input type="hidden" name="val_destino" >
                  <input type="hidden" name="val_funcionario1" value="0">
				<input type="hidden" name="arreglo" >
				<input type="hidden" name="num_int" value="<? echo $num_int;?>">
                <input type="hidden" name="Cbo_Estado_Docto" value="1">
				<input type="hidden" name="Cbo_Estado_Compromiso" value="2">
				<input type="hidden" name="arregloint" >
				<input type="hidden" name="arregloext" >
				<input type="hidden" name="inv" >
				<input type="hidden" name="sw_2xt">
				<input type="hidden" name="Txt_fecha_inv">
				<input type="hidden" name="totexped" >
				<input type="hidden" name="descexped" >
				<input type="hidden" name="num_exp" >
				<input type="hidden" name="menu"   value ="<? echo 2 ;?>">
				<input type="hidden" name="idcomp" >
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
  <div id="Layer2" style="position:absolute; width:238px; height:60px; z-index:1; left: 575px; top: 368px; visibility: hidden; overflow: auto; background-color: #E6EEFF; layer-background-color: #E6EEFF; border: 1px none #000000;" class="texto"> 
    <form name="formfecha">
      <font color="#000000" size="2"><strong>Fecha Invitaci&oacute;n</strong></font><strong><font color="#000000" face="Arial, Helvetica, sans-serif">
      <input name="Txt_fecha_inv" type="text" class="entradas" id="Txt_fecha_inv"  onBlur="chequeafecha_inv(this,0)"  size="8" maxlength="10" >
      <a href="javascript:show_Calendario('formfecha.Txt_fecha_inv');"> <img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"> 
      </a></font> </strong> 
    </form>
  </div>
  <?php mssql_close($cn);?>
</center>
</body>
</html>
