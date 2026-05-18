// JavaScript Document

var sw_ok;
var cont_arreglo;
var z=0;
var arreglo2 ="";
var ar_descrip =new Array();

function muestra(cod)
{
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
	document.form1.Atipo_destino.value="E";
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
