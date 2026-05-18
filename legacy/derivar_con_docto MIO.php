<?php
include("conexion_bd.php");
include("carga_tablas.php");

global $Confidencial;
$id_doc=162;
$id_tra=2;

$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;
$flujo1=$flujook;
$rs_referencia ="select a.id_documento,a.id_tipo_documento,a.id_estado_documento,a.materia,
a.num_interno,a.num_oficial,a.num_externo,b.*,c.desc_tipo_documento,
procedencia =
case b.tipo_procedencia
when 'I' then
	(select desc_dependencia from dependencia where b.id_procedencia=id_dependencia)
else
 	(select desc_dependencia_externa from dependencia_externa where b.id_procedencia=id_dependencia_externa)
end
from documento a, tramite b, tipo_documento c 
where a.id_documento=162
and b.id_documento=a.id_documento
and b.id_seguimiento=2
and b.id_estado_tramite = 3
and a.id_tipo_documento=c.id_tipo_documento ";

$reg_ref = mssql_fetch_array($rs_referencia);
$Tot_ref = mssql_num_rows($rs_referencia);

// saca el id del funcionario
$rs_funcionario = mssql_query("select id_dependencia from funcionario
where id_funcionario =" . $fun,$cn);
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

function cambio()
{
var selindice, nuevalsel;
var valor="";
if (document.form1.radioprocedencia[0].checked==true)
{
selindice = document.form1.Cbo_Procedencia.selectedIndex;
nuevasel = document.form1.Cbo_Procedencia.options[selindice].value;
document.form1.val_procedencia.value= nuevasel;
parent.frames[0].location.href="frame_sup.php?cod_dep="+nuevasel+"&sw="+valor;
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
	parent.frames[0].location.href="frame_sup.php?cod_dep="+ nuevasel+"&sw="+valor;
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
	parent.frames[0].location.href="frame_sup.php?des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;
}
else
  if(document.form1.tipo_procedencia.value=="I"&&document.form1.tipo_destino.value=="E"){		
	var valor="IE";
	var p1=<?php echo $xx; ?>;
	var p2= document.form1.Cbo_Procedencia.selectedIndex;
	var p3=document.form1.val_procedencia.value;
	var d3=document.form1.val_funcionario.value;
	var d2=document.form1.val_destino.value;
	parent.frames[0].location.href="frame_sup.php?cod_dep="+ p1+"&des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;	
	document.form1.Cbo_Func_Destino.disabled=true;
 } 
else 
 if(document.form1.tipo_procedencia.value=="E"&&document.form1.tipo_destino.value=="I"){		
	var valor="EI";
	var p2= document.form1.Cbo_Destinatario.selectedIndex;
	var p3=document.form1.val_destino.value;
	var d3=document.form1.val_funcionario1.value;
	var d2=document.form1.val_procedencia.value;
	parent.frames[0].location.href="frame_sup.php?des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;	
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
	parent.frames[0].location.href="frame_sup.php?des_d="+d2+"&des_f="+d3+"&pro_d="+p2+"&pro_f="+p3+"&sw="+valor;	
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
	parent.frames[0].location.href="frame_sup.php?cod_dep="+ p2+"&sw="+valor+"&fw="+p3;
}

function cargar_funcionario_destino() {	 
  if(document.form1.val_destino.value>0)  {
   	var valor="D";
	var d2=document.form1.val_destino.value;
	var d3=document.form1.val_funcionario1.value;
	parent.frames[0].location.href="frame_sup.php?cod_dep="+d2+"&sw="+valor+"&xw="+d3;
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
	parent.frames[0].location.href="frame_sup.php?cod_dep="+ nuevasel+"&des_d="+selindice+"&sw="+valor;
	document.form1.Cbo_Func_Destino.options.value=0;
	document.form1.Cbo_Func_Destino.disabled=true;
	}
}

function destino_interno()
{
var selindice, nuevalsel;
var valor="I";
nuevasel= 0;
parent.frames[0].location.href="frame_sup.php?cod_dep="+ nuevasel+"&sw="+valor;
}

function procedencia_interna()
{
var selindice, nuevalsel;
var valor="I";
nuevasel= 1;
parent.frames[0].location.href="frame_sup.php?cod_dep="+ nuevasel+"&sw="+valor;
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
	parent.frames[0].location.href="frame_sup.php?cod_dep="+ nuevasel+"&pro_d="+selindice+"&sw="+valor;
	//document.form1.Cbo_Func_Procedencia.options.value=0;
	document.form1.Cbo_Func_Procedencia.disabled=true;
	}
}
//-->
</script>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Documento sin t&iacute;tulo</title>
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
if(document.form1.TxtInterno.value == ""  && document.form1.TxtOficial.value == "")
  {
 	sw_ok=false;
	alert("Falta Ingresar el Número Interno o el Número Oficial");
	document.form1.TxtInterno.focus();
  }	
else	
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
 
 
/**
* fichero para mostrar un calendario en una ventana flotante
* Autor: desconocido
* Adaptacion, explicaciones y cambios: Luciano Moreno - HTMLWeb  (http://www.htmlweb.net
*/

    /**
    * definimos los dias festivos por el indice en la matriz: sabado y domingo, y les asignamos un color de fondo a sus celdas
    */
    var festivos = [5,6];
    var festivosColor = "#DCE6FE";
    /**
    * definimos el tamaño y familia de las fuentes
    */
    var familia_fuente = "Tahoma";
    var size_fuente = 1;

    /**
    * declaramos las cariables globales ahora, que va a contener la fecha del sistema del usuario 
    * y calculo, que usaremos luego
    */
    var ahora = new Date();
    var calculo;
    
    /**
    * averiguamos el navegador del usuario y lo asignamos a una veriable especifica
    */
    if (document.layers)
        isNav = true;
    else if (document.all)
        isIE = true;

    /**
    * declaramos la matriz de meses del Calendarioio, como una propiedad del objeto Calendarioio
    */
    Calendario.Meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

    /**
    * definimos los dias de cada mes para año normal
    */
    Calendario.DiasMes = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    /**
    * definimos los dias de cada mes para año bisiesto
    */
    Calendario.BisiestoDiasMes = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    /**
    * funcion principal de definicion del objeto Calendario
    */
    function Calendario(p_item, p_WinCal, mes, anyo, formato)
    {
        /**
        * si no se elige fecha, no hace nada
        */
        if ((mes == null) && (anyo == null))	
            return;
	    if (p_WinCal == null)
		    this.gWinCal = calculo;
	    else
		    this.gWinCal = p_WinCal;
	
	    if (mes == null)
        {
	        this.dameMes = null;
            this.dameNumeroMes = null;
            this.dameAnyo = true;
        }
        else
        {
	        this.dameMes = Calendario.get_month(mes);
            this.dameNumeroMes = new Number(mes);
            this.dameAnyo = false;
        }

	    /**
        * configuramos el formato del calendario
        */
        this.gYear = anyo;
	    this.gFormat = formato;
	    this.gBGColor = "red";
	    this.gFGColor = "black";
	    this.gTextColor = "black";
	    this.gHeaderColor = "white";
	    this.gReturnp_item = p_item;
    }

    Calendario.get_month = Calendario_get_month;
    Calendario.get_diasdelmes = Calendario_get_diasdelmes;
    Calendario.calcula_mes_anyo = Calendario_calcula_mes_anyo;
    Calendario.print = Calendario_print;

    /**
    * obtenemos el numero del mes
    */
    function Calendario_get_month(monthNo) 
    {
	    return Calendario.Meses[monthNo];
    }

   /** 
	* vemos si el año es bisiesto o no, para asignar los dias correspondientes a febrero
	*/
    function Calendario_get_diasdelmes(monthNo, anyo) 
    {	
        if ((anyo % 4) == 0) 
        {
            if ((anyo % 100) == 0 && (anyo % 400) != 0)
                return Calendario.DiasMes[monthNo];	
		     return Calendario.BisiestoDiasMes[monthNo];
	     }
         else
		     return Calendario.DiasMes[monthNo];
    }

    /**
    * funcion para incrementoementar o decrementar 1 mes o 1 año al pulsar las dobles flechas
    * la variable incrementoemento establece el aumento o disminucion en 1 unidad (se puede cambiar)
    */
    function Calendario_calcula_mes_anyo(mes, anyo, incremento) 
    {
	    var ret_arr = new Array();	
	    if (incremento == -1) 
        {
		    /**
            * hacia atras
            */
	        if (mes == 0) 
            {
                ret_arr[0] = 11;
		    	ret_arr[1] = parseInt(anyo) - 1;
	    	}	
            else 
            {
	    		ret_arr[0] = parseInt(mes) - 1;
	    		ret_arr[1] = parseInt(anyo);
		    }
	    }
        /**
        * hacia adelante
        */ 
        else if (incremento == 1) 
        {
		    if (mes == 11) 
            {
			    ret_arr[0] = 0;
    			ret_arr[1] = parseInt(anyo) + 1;
    		}
    		else
            {
			    ret_arr[0] = parseInt(mes) + 1;
    			ret_arr[1] = parseInt(anyo);
    		}
    	}	
	    return ret_arr;
    }

    /**
    * funcion para imprimir el calendario
    */
    function Calendario_print()
    {
	    calculo.print();
    }

    /**
    * añadimos propiedades al objeto Calendario mediante el metodo prototype
    */
    Calendario.prototype.getMonthlyCalendarioCode = function() 
    {
	    var vCode = "";
	    var vHeader_Code = "";
	    var vData_Code = "";
	
	   /**
       *  dibujamos la tabla del calendario en la ventana flotante, Se rellena con filas que definimos luego
       */
//	    vCode = vCode + "<TABLE BORDER=1 BGCOLOR=\"" + this.gBGColor + "\">";
		 vCode = vCode + "<TABLE BORDER=1 >";	
	    vHeader_Code = this.cal_header();
	    vData_Code = this.cal_data();
	    vCode = vCode + vHeader_Code + vData_Code;	
	    vCode = vCode + "</TABLE>";	
	    return vCode;
    }

    Calendario.prototype.show = function() 
    {
	    var vCode = "";	
    	this.gWinCal.document.open();
	    /**
        * definimos la cedana que nos pintara la pagina dentro de la ventana flotante
        */
	    this.wwrite("<html>");
	    this.wwrite("<head><title>Calendario</title>");
	    this.wwrite("</head>");
	    this.wwrite("<body " + 
		"link=\"" + this.gLinkColor + "\" " + 
		"vlink=\"" + this.gLinkColor + "\" " +
		"alink=\"" + this.gLinkColor + "\" " +
		"text=\"" + this.gTextColor + "\">");
	    this.wwriteA("<FONT FACE='" + familia_fuente + "' SIZE=2><B>");
    	this.wwriteA(this.dameMes + " " + this.gYear);
    	this.wwriteA("</B><BR>");    	
	    var prevMMYYYY = Calendario.calcula_mes_anyo(this.dameNumeroMes, this.gYear, -1);
    	var prevMM = prevMMYYYY[0];
    	var prevYYYY = prevMMYYYY[1];
	    var nextMMYYYY = Calendario.calcula_mes_anyo(this.dameNumeroMes, this.gYear, 1);
	    var nextMM = nextMMYYYY[0];
	    var nextYYYY = nextMMYYYY[1];	
	    this.wwrite("<TABLE WIDTH='100%' BORDER=0 CELLSPACING=0 CELLPADDING=0><TR><TD ALIGN=center>");
	    this.wwrite("[<A STYLE='font-size:10px;text-decoration:none;' HREF=\"" +
		"javascript:window.opener.Build(" + 
		"'" + this.gReturnp_item + "', '" + this.dameNumeroMes + "', '" + (parseInt(this.gYear)-1) + "', '" + this.gFormat + "'" +
		");" +
		"\"><<<\/A>]</TD><TD ALIGN=center>");
	    this.wwrite("[<A STYLE='font-size:10px;text-decoration:none;' HREF=\"" +
		"javascript:window.opener.Build(" + 
		"'" + this.gReturnp_item + "', '" + prevMM + "', '" + prevYYYY + "', '" + this.gFormat + "'" +
		");" +
		"\"><<\/A>]</TD><TD ALIGN=center>");
	    this.wwrite("[<A STYLE='font-size:10px;text-decoration:none;' HREF=\"javascript:window.print();\">Imprimir</A>]</TD><TD ALIGN=center>");
	    this.wwrite("[<A STYLE='font-size:10px;text-decoration:none;' HREF=\"" +
		"javascript:window.opener.Build(" + 
		"'" + this.gReturnp_item + "', '" + nextMM + "', '" + nextYYYY + "', '" + this.gFormat + "'" +
		");" +
		"\">><\/A>]</TD><TD ALIGN=center>");
	    this.wwrite("[<A STYLE='font-size:10px;text-decoration:none;' HREF=\"" +
		"javascript:window.opener.Build(" + 
		"'" + this.gReturnp_item + "', '" + this.dameNumeroMes + "', '" + (parseInt(this.gYear)+1) + "', '" + this.gFormat + "'" +
		");" +
		"\">>><\/A>]</TD></TR></TABLE><BR>");
	    vCode = this.getMonthlyCalendarioCode();
	    this.wwrite(vCode);
	    this.wwrite("</font></body></html>");
	    this.gWinCal.document.close();
    }

    /**
    * funcion que define las propiedades de la ventana flotante, escribe dentro 
    * el codigo inicial y la a bre
    */
    Calendario.prototype.showY = function() 
    {
	    var vCode = "";
    	var i;
    	var vr, vc, vx, vy;
    	var vxf = 285;		
    	var vyf = 200;		
    	var vxm = 10;		
    	var vym;
    	if (isIE)	
            vym = 75;
    	else if (isNav)	
            vym = 25;	
    	this.gWinCal.document.open();
    	this.wwrite("<html>");
    	this.wwrite("<head><title>Calendario</title>");
    	this.wwrite("<style type='text/css'>\n<!--");
     	for (i=0; i<12; i++) 
        {
            vc = i % 3;
    		if (i>=0 && i<= 2)	vr = 0;
    		if (i>=3 && i<= 5)	vr = 1;
    		if (i>=6 && i<= 8)	vr = 2;
    		if (i>=9 && i<= 11)	vr = 3;		
    		vx = parseInt(vxf * vc) + vxm;
    		vy = parseInt(vyf * vr) + vym;
    		this.wwrite(".lclass" + i + " {position:absolute;top:" + vy + ";left:" + vx + ";}");
    	}
	    this.wwrite("-->\n</style>");
    	this.wwrite("</head>");
    	this.wwrite("<body " + 
		"link=\"" + this.gLinkColor + "\" " + 
		"vlink=\"" + this.gLinkColor + "\" " +
		"alink=\"" + this.gLinkColor + "\" " +
		"text=\"" + this.gTextColor + "\">");
    	this.wwrite("<FONT FACE='" + familia_fuente + "' SIZE=1><B>");
    	this.wwrite("Year : " + this.gYear);
    	this.wwrite("</B><BR>");
	    var prevYYYY = parseInt(this.gYear) - 1;
    	var nextYYYY = parseInt(this.gYear) + 1;	
    	this.wwrite("<TABLE WIDTH='100%' BORDER=1  CELLSPACING=0 CELLPADDING=0 BGCOLOR='#ffcc66'><TR><TD ALIGN=center>");
    	this.wwrite("[<A HREF=\"" +
		"javascript:window.opener.Build(" + 
		"'" + this.gReturnp_item + "', null, '" + prevYYYY + "', '" + this.gFormat + "'" +
		");" +
		"\" alt='Prev Year'><<<\/A>]</TD><TD ALIGN=center>");
	    this.wwrite("[<A HREF=\"javascript:window.print();\">Imprimir</A>]</TD><TD ALIGN=center>");
    	this.wwrite("[<A HREF=\"" +
    	"javascript:window.opener.Build(" + 
		"'" + this.gReturnp_item + "', null, '" + nextYYYY + "', '" + this.gFormat + "'" +
		");" +
		"\">>><\/A>]</TD></TR></TABLE><BR>");
    	var j;
    	for (i=11; i>=0; i--) 
        { 
    		if (isIE)
    			this.wwrite("<DIV ID=\"layer" + i + "\" CLASS=\"lclass" + i + "\">");
    		else if (isNav)
    			this.wwrite("<LAYER ID=\"layer" + i + "\" CLASS=\"lclass" + i + "\">");
    		this.dameNumeroMes = i;
    		this.dameMes = Calendario.get_month(this.dameNumeroMes);
    		vCode = this.getMonthlyCalendarioCode();
    		this.wwrite(this.dameMes + "/" + this.gYear + "<BR>");
    		this.wwrite(vCode);
    		if (isIE)
     			this.wwrite("</DIV>");
    		else if (isNav)
    			this.wwrite("</LAYER>");
    	}
    	this.wwrite("</font><BR></body></html>");
    	this.gWinCal.document.close();
    }

    /**
    * funciones que pintan el string de las filas y celdas
    */
    Calendario.prototype.wwrite = function(wtext) 
    {
	    this.gWinCal.document.writeln(wtext);
    }

    Calendario.prototype.wwriteA = function(wtext) 
    {
	    this.gWinCal.document.write(wtext);
    }

    /**
    * funcion que crea el string con las diferentes filas y celdas del calendario en la ventana flotante
    */
    Calendario.prototype.cal_header = function() 
    {
	    var vCode = "";	
       	vCode = vCode + "<TR BGCOLOR='#330066'>";
    	vCode = vCode + "<TD WIDTH='14%'><FONT SIZE='1' FACE='" + familia_fuente + "' COLOR='" + this.gHeaderColor + "'><B>Lunes</B></FONT></TD>";
    	vCode = vCode + "<TD WIDTH='14%'><FONT SIZE='1' FACE='" + familia_fuente + "' COLOR='" + this.gHeaderColor + "'><B>Martes</B></FONT></TD>";
    	vCode = vCode + "<TD WIDTH='14%'><FONT SIZE='1' FACE='" + familia_fuente + "' COLOR='" + this.gHeaderColor + "'><B>Miercoles</B></FONT></TD>";
    	vCode = vCode + "<TD WIDTH='14%'><FONT SIZE='1' FACE='" + familia_fuente + "' COLOR='" + this.gHeaderColor + "'><B>Jueves</B></FONT></TD>";
    	vCode = vCode + "<TD WIDTH='14%'><FONT SIZE='1' FACE='" + familia_fuente + "' COLOR='" + this.gHeaderColor + "'><B>Viernes</B></FONT></TD>";
    	vCode = vCode + "<TD WIDTH='16%'><FONT SIZE='1' FACE='" + familia_fuente + "' COLOR='" + this.gHeaderColor + "'><B>S&aacute;bado</B></FONT></TD>";
        vCode = vCode + "<TD WIDTH='14%'><FONT SIZE='1' FACE='" + familia_fuente + "' COLOR='" + this.gHeaderColor + "'><B>Domingo</B></FONT></TD>";
       vCode = vCode + "</TR>";	
    	return vCode;
    }

    /**
    * funcion que calculas las partes de la fecha actual y crea las celdas con los dias
    */
    Calendario.prototype.cal_data = function()
    {
	    var vDate = new Date();
    	vDate.setDate(1);
    	vDate.setMonth(this.dameNumeroMes);
    	vDate.setFullYear(this.gYear);
    	var vFirstDay=vDate.getDay()-1;
    	var vDay=1;
    	var vLastDay=Calendario.get_diasdelmes(this.dameNumeroMes, this.gYear);
    	var vOnLastDay=0;
    	var vCode = "";
    	vCode = vCode + "<TR ALIGN= center BGCOLOR='#F6F9FE'>";
    	/**
        * primera semana del mes
        */
        
        /**
        * si el primer dia de la semana cae en domingo
        */
        if(vFirstDay==-1)
        {
            for (i=0; i<6; i++) 
            {
		        vCode = vCode + "<TD WIDTH='14%'" + this.write_festivos_string(i) + "><FONT SIZE='2' FACE='" + familia_fuente + "'>&nbsp;</FONT></TD>";
	        }
            for (j=6; j<7; j++) 
            {
    		    vCode = vCode + "<TD WIDTH='14%'" + this.write_festivos_string(j) + "><FONT SIZE='2' FACE='" + familia_fuente + "'>" + 
			    "<A HREF='#' " + 
			    "onClick=\"self.opener.document." + this.gReturnp_item + ".value='" + 
			    this.format_data(vDay) + 
			    "';window.close();\">" + 
			    this.format_day(vDay) + 
			    "</A>" + 
			    "</FONT></TD>";
    		    vDay=vDay+1;
    	   }
        }
        /**
        * si no cae en domingo
        */
        else
        {
            for (i=0; i<vFirstDay; i++) 
            {
		        vCode = vCode + "<TD WIDTH='14%'" + this.write_festivos_string(i) + "><FONT SIZE='2' FACE='" + familia_fuente + "'>&nbsp;</FONT></TD>";
	        }
    	for (j=vFirstDay; j<7; j++) 
        {
    		vCode = vCode + "<TD WIDTH='14%'" + this.write_festivos_string(j) + "><FONT SIZE='2' FACE='" + familia_fuente + "'>" + 
			"<A HREF='#' " + 
			"onClick=\"self.opener.document." + this.gReturnp_item + ".value='" + 
			this.format_data(vDay) + 
			"';window.close();\">" + 
			this.format_day(vDay) + 
			"</A>" + 
			"</FONT></TD>";
    		vDay=vDay+1;
    	}
        }
    	vCode = vCode + "</TR>";
    	for (k=2; k<7; k++) 
        {
		    vCode = vCode + "<TR ALIGN= center BGCOLOR='#F6F9FE'>";
            for (j=0; j<7; j++) 
            {
			    vCode = vCode + "<TD WIDTH='14%'" + this.write_festivos_string(j) + "><FONT SIZE='2' FACE='" + familia_fuente + "'>" + 
                "<A HREF='#' " + 
				"onClick=\"self.opener.document." + this.gReturnp_item + ".value='" + 
				this.format_data(vDay) + 
				"';window.close();\">" + 
	    		this.format_day(vDay) + 
		    	"</A>" + 
			    "</FONT></TD>";
			    vDay=vDay + 1;
        		if (vDay > vLastDay) 
                {
				    vOnLastDay = 1;
        			break;
	            }
		    }
		    if (j == 6)
			    vCode = vCode + "</TR>";
            if (vOnLastDay == 1)
	        	break;
         }
	     for (m=1; m<(7-j); m++) 
         {
		     if (this.dameAnyo)
			     vCode = vCode + "<TD WIDTH='14%'" + this.write_festivos_string(j+m) + 
			     "><FONT SIZE='2' FACE='" + familia_fuente + "' COLOR='gray'> </FONT></TD>";
		    else
			    vCode = vCode + "<TD WIDTH='14%'" + this.write_festivos_string(j+m) + 
			    "><FONT SIZE='2' FACE='" + familia_fuente + "' COLOR='gray'>" + m + "</FONT></TD>";
	     }	
	     return vCode;
     }

    /**
    * metodo para formatear el dia actual
    */
    Calendario.prototype.format_day = function(vday) 
    {
	    var vNowDay = ahora.getDate();
    	var vNowMonth = ahora.getMonth();
    	var vNowYear = ahora.getFullYear();
    	if (vday == vNowDay && this.dameNumeroMes == vNowMonth && this.gYear == vNowYear)
	    	return ("<FONT COLOR=\"RED\"><B>" + vday + "</B></FONT>");
    	else
    		return (vday);
    }

    /**
    * metodo para formatear los dias festivos
    */
    Calendario.prototype.write_festivos_string = function(vday) 
    {
	    var i;
	    for (i=0; i<festivos.length; i++) 
        {
		    if (vday == festivos[i])
			    return (" BGCOLOR=\"" + festivosColor + "\"");
	    }	
	    return "";
    }

    /**
    * metodo para formatear el resto de los dias en las diferentes formas posibles
    */
    Calendario.prototype.format_data = function(p_day) 
    {
    	var vData;
    	var vMonth = 1 + this.dameNumeroMes;
    	vMonth = (vMonth.toString().length < 2) ? "0" + vMonth : vMonth;
    	var vMon = Calendario.get_month(this.dameNumeroMes).substr(0,3).toUpperCase();
    	var vFMon = Calendario.get_month(this.dameNumeroMes).toUpperCase();
    	var vY4 = new String(this.gYear);
    	var vY2 = new String(this.gYear.substr(2,2));
    	var vDD = (p_day.toString().length < 2) ? "0" + p_day : p_day;
    	switch (this.gFormat) 
        {
		    case "MM\/DD\/YYYY" :
		    	vData = vMonth + "\/" + vDD + "\/" + vY4;
			    break;
		    case "MM\/DD\/YY" :
			   vData = vMonth + "\/" + vDD + "\/" + vY2;
			    break;
		    case "MM-DD-YYYY" :
			    vData = vMonth + "-" + vDD + "-" + vY4;
			    break;
		    case "MM-DD-YY" :
			   vData = vMonth + "-" + vDD + "-" + vY2;
               break;
		    case "DD\/MON\/YYYY" :
			    vData = vDD + "\/" + vMon + "\/" + vY4;
    			break;
    		case "DD\/MON\/YY" :
    			vData = vDD + "\/" + vMon + "\/" + vY2;
    			break;
    		case "DD-MON-YYYY" :
    			vData = vDD + "-" + vMon + "-" + vY4;
    			break;
    		case "DD-MON-YY" :
    			vData = vDD + "-" + vMon + "-" + vY2;
    			break;
    		case "DD\/MONTH\/YYYY" :
    			vData = vDD + "\/" + vFMon + "\/" + vY4;
    			break;
    		case "DD\/MONTH\/YY" :
    			vData = vDD + "\/" + vFMon + "\/" + vY2;
    			break;
    		case "DD-MONTH-YYYY" :
    			vData = vDD + "-" + vFMon + "-" + vY4;
    			break;
    		case "DD-MONTH-YY" :
    			vData = vDD + "-" + vFMon + "-" + vY2;
    			break;
    		case "DD\/MM\/YYYY" :
    			vData = vDD + "\/" + vMonth + "\/" + vY4;
    			break;
    		case "DD\/MM\/YY" :
    			vData = vDD + "\/" + vMonth + "\/" + vY2;
    			break;
    		case "DD-MM-YYYY" :
        		vData = vDD + "-" + vMonth + "-" + vY4;
    			break;
    		case "DD-MM-YY" :
    			vData = vDD + "-" + vMonth + "-" + vY2;
    			break;
    		default :
			vData = vMonth + "\/" + vDD + "\/" + vY4;
	    }
	    return vData;
    }

    /**
    * funcion que formatea los colores de los textos y fondo del calendario
    */
    function Build(p_item, mes, anyo, formato) 
    {
	    var p_WinCal = calculo;
	    gCal = new Calendario(p_item, p_WinCal, mes, anyo, formato);
    	gCal.gBGColor="red";
    	gCal.gLinkColor="black";
    	gCal.gTextColor="black";
    	gCal.gHeaderColor="white";
    	if (gCal.dameAnyo)	
            gCal.showY();
    	else
            gCal.show();
    }

   /**
   * funcion que muestra el calendario en la ventana flotante
    */
    function show_Calendario() 
    {
	    /* *
		* mes : 0-11 para Enero-Diciembre; 12para todos los meses.
		* anyo	: con 4 digitos
		* formato:formato de fechas (mm/dd/yyyy, dd/mm/yy, ...)
		* item	: devuelve el Item.
	    */
    	p_item = arguments[0];
      	if (arguments[1] == null)
	    	mes = new String(ahora.getMonth());
    	else
    		mes = arguments[1];
       	if (arguments[2] == "" || arguments[2] == null)
    		anyo = new String(ahora.getFullYear().toString());
    	else
    		anyo = arguments[2];    	
        if (arguments[3] == null)
    		formato = "DD-MM-YYYY";
	    else
		    formato = arguments[3];
	    /**
        * OJO: CONFIGURAR AQUI EL TAMAÑO DE LA VENTANA FLOTANTE
        */
        vWinCal = window.open("", "Calendario", "width=400,height=210,status=no,resizable=no,top=200,left=200");
	    vWinCal.opener = self;
	    calculo = vWinCal;
	    Build(p_item, mes, anyo, formato);
    }
    /**
    * OJO: CONFIGURAR AQUI EL FORMATO DE LA FECHA
    */
    function show_yearly_Calendario(p_item, anyo, formato) 
    {
	    /**
        * formato por defecto
        */
	    if (anyo == null || anyo == "")
		    anyo = new String(ahora.getFullYear().toString());
	    if (formato == null || formato == "")
		    formato = "DD-MM-YYYY";
	    var vWinCal = window.open("", "Calendario", "scrollbars=no");
	    vWinCal.opener = self;
	    calculo = vWinCal;
	    Build(p_item, null, anyo, formato);
    }

/**
* fin del fichero
*/
  
  
  
  
 </script>
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
</head>

<body onLoad="muestra_cuadro()" bgcolor="#FFFFFF" text="#000000" link="#CCCCCC">
<form name="form1" method="Post" action="guardar_ingreso.php"> 
  <table width="650" border="2" cellpadding="1" cellspacing="1" bordercolor="#990066" bgcolor="#FFFFCC">
      <tr> 
      <td width="500" height="493" align="center"> <table width="620" border="0" align="center" cellpadding="2" cellspacing="2">
          <tr> 
            <td align="right"><font color="#0000A0" size="2"><? echo date("d/m/Y H:m"); ?></font></td>
          </tr>
        </table>
        <table width="620" border="0" align="center" cellpadding="2" cellspacing="2">
          <tr> 
            <td align="center"><font color="#0000A0" size="4">INGRESO DE DOCUMENTOS</font></td>
          </tr>
        </table>
        <table width="620" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr> 
            <td align="center"> <div align="right"><strong><font color="#0000A0" size="3"><? echo "Usuario : " . $Usuario?></font></strong></div></td>
          </tr>
        </table>
        <table width="620" border="1" cellspacing="1" cellpadding="1">
          <tr> 
            <td><strong><em><font color="#FF0000">Identificaci&oacute;n del Documento 
              de Referencia</font></em></strong></td>
            <td><a href="documento_de_referencia.htm"><font color="#FF0000">Ver 
              detalle</font></a> </td>
          </tr>
        </table>
        <table width="620" border="1" cellspacing="1" cellpadding="1">
          <tr>
            <td height="111">
<table width="620" border="1" cellspacing="1" cellpadding="1">
                <tr>
                  <td>Tipo Documento</td>
                  <td><font size="2"><?php echo $reg_ref[desc_tipo_documento];?></font></td>
                  <td>Procedencia</td>
                  <td><font size="2"><?php echo $reg_ref[procedencia];?></font></td>
                  <td>Fecha Documento</td>
                  <td><font size="2"><? $fec_doc=strtotime($reg_ref[fecha_documento]);
			   $fech_doc = date("d/m/Y",$fec_doc);
			   echo $fech_doc;?></font></td>
                </tr>
              </table>
              <table width="620" border="0" cellspacing="1" cellpadding="1">
                <tr>
                  <td>N&uacute;meros:</td>
                </tr>
              </table>
              <table width="620" border="1" cellspacing="1" cellpadding="1">
                <tr>
                  <td>Interno</td>
                  <td><font size="2"><?php echo $reg_ref[num_interno];?></font></td>
                  <td>Oficial</td>
                  <td><font size="2"><?php echo $reg_ref[num_oficial];?></font></td>
                  <td>Externo</td>
                  <td><font size="2"><?php echo $reg_ref[num_externo];?></font></td>
                </tr>
              </table>
              
            </td>
          </tr>
        </table>
        <div align="left"><strong> </strong> </div>
        <table width="633" border="0"  align="center" cellspacing="1" cellpadding="1">
          <tr> 
            <td width="626"><strong><em><font color="#0080C0">Identificaci&oacute;n 
              del Documento Nuevo</font></em></strong></td>
		  </tr>
        </table>
        <table width="626" height="266" border="1" align="center" cellpadding="2" cellspacing="2">
          <tr> 
            <td width="621" height="260" align="center"> <div align="center"> 
                <table width="618" border="1" cellspacing="2" cellpadding="2">
                  <tr> 
                    <td width="96"><font size="2">Tipo de Docto</font> </td>
                    <td width="189"> <select name="Cbo_Tipo_Docto" id="select5">
                        <?
				   while($reg=mssql_fetch_array($rs_tipo_docto)){
				?>
                        <option value=<? echo $reg[id_tipo_documento] ?> ><? echo $reg[desc_tipo_documento] ?></option>
                        <?
}
?>
                      </select> </td>
                    <td width="111">Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                    <td width="196"><font face="Arial, Helvetica, sans-serif"> 
                      <input name="Txt_fecha_doc" type="text" id="Txt_fecha_doc2" size="10" maxlength="10" value="<?=$fecha_x?>">
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
                      </select> </td>
                  </tr>
                </table>
                <table width="620" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td><strong>N&uacute;meros</strong> <table width="600" align="center"  height="45" border="1" cellspacing="1" cellpadding="1">
                        <tr valign="middle"> 
                          <td width="200">Interno<font size="4" face="Arial"> 
                            <input name="TxtInterno" type="text" id="TxtInterno" size="8" maxlength="8" onBlur="valida_digito(this.value,this,8);">
                            </font></td>
                          <td width="200">Oficial<font size="4" face="Arial"> 
                            <input name="TxtOficial" type="text" id="TxtOficial" size="8" maxlength="8" onBlur="valida_digito(this.value,this,8);">
                            </font></td>
                          <td width="200">Externo<font size="4" face="Arial"> 
                            <input name="TxtExterno" type="text" id="TxtExterno2" size="8" maxlength="8" onBlur="valida_digito(this.value,this,8);">
                            </font></td>
                        </tr>
                      </table>
                      <div id="Layer1" style="position:absolute; width:193px; height:187px; z-index:1; left: 42px; top: 281px; visibility: hidden; overflow: auto;"> 
                        <table width="100%" border="1" bgcolor="#CCCCCC">
                          <tr> 
                            <td height="82"> 
                              <?php 
							  $k=0;
							  while($reg_servicio = mssql_fetch_array($rs_servicio)) { ?>
                              <input type="checkbox" name="casilla" value="<?php echo $reg_servicio["id_descriptor"];?>" onClick="javascript:muestra(<?php echo $reg_servicio["id_descriptor"];?>);"> 
                              <?php echo $reg_servicio["desc_descriptor"];?> <br> 
                              <?php } ?>
                            </td>
                          </tr>
                          <tr> 
                            <td height="23"> <div align="right" onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide')"><strong>cerrar</strong></div></td>
                          </tr>
                        </table>
                        <div align="right"></div>
                      </div></td>
                  </tr>
                </table>
                <table width="620" border="0" valign="top" cellspacing="2" cellpadding="2">
                  <tr> 
                    <td width="612"><strong>Materia</strong> <textarea name="TxtMateria" cols="70" rows="3" onblur="valida_campo();"></textarea> 
                    </td>
                  </tr>
                </table>
                <table width="626" border="1" align="left" valign="top" cellspacing="2" cellpadding="2">
                  <tr> 
                    <td>Descriptores 
					
                      <input type="radio" name="radiodescriptor" value="radiobutton" onClick="MM_showHideLayers('Layer1','','show')"></td>
                  </tr>
                </table>
              </div></td>
          </tr>
        </table>
		<br>
        <table width="626" border="0" cellspacing="1" cellpadding="1">
          <tr> 
            <td width="304"><font color="#0080C0"><strong><em>Trámite Del Documento</em></strong></font></td>
          </tr>
		</table>  
        <table width="626" border="1" cellspacing="1" cellpadding="1">
          <tr> 
            <td width="304"><font color="#800040"><strong>ORIGEN</strong></font></td>
            <td width="309"><font color="#800040"><strong>DESTINO</strong></font></td>
          </tr>
        </table>
        <table width="626" border="1" cellspacing="2" cellpadding="1">
          <tr> 
            <td width="305"><table width="305" border="0" cellspacing="1" cellpadding="1">
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
                    <select name="Cbo_Procedencia" id="select" onChange="javascript:cambio();">
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
                  <td><select name="Cbo_Func_Procedencia" id="select" onChange="javascript:cambio2();">
				
				      </select> </td>
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
                  <td>&nbsp;</td>
                  <td><font face="Arial"> 
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
                  <td><font face="Arial"> 
                    <select name="Cbo_Func_Destino" id="select" onChange="javascript:cambio3();">>
                    </select>
                    </font></td>
                </tr>
              </table></td>
          </tr>
        </table>
        <br>
        <table width="626" border="1" cellspacing="2" cellpadding="1">
          <tr>
            <td>Tipo Distribuci&oacute;n</td>
            <td><font face="Arial">
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
            <td>Tipo Compromiso</td>
            <td><font face="Arial">
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
            <td><font face="Arial">
              <select name="Cbo_Estado_Compromiso" id="select13">
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
            <td><input name="TxtDias"  type="text" size="4" maxlength="2"></td>
          </tr>
        </table>
        <table width="626" border="1" cellspacing="1" cellpadding="1">
          <tr> 
            <td width="618">Despachado por Oficina de Partes 
              <input name="checkofpartes2" type="checkbox" id="checkofpartes2" value="S"></td>
          </tr>
        </table>
        <table width="626" border="1" cellspacing="1" cellpadding="1">
          <tr> 
            <td width="618"><strong>Observaci&oacute;n</strong><br> 
              <textarea name="TxtObservacion" cols="70" rows="3" id="TxtObservacion"></textarea></td>
          </tr>
        </table>
        
        <table width="626" border="1" align="center" cellpadding="2" cellspacing="2">
          <tr> 
            <td height="48" width="306"> <div align="center"> 
                      
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
                <input type="hidden" name="Cbo_Estado_Docto" value="<? echo 1;?>">
                <input type="button" name="cmd_grabar" value="Grabar" onClick="chequear_arreglo(<?php echo $nRows?>);validar_datos();">
              </div></td>
            <td width="300"><div align="center" width="310"> 
                <input type="button" name="submit2" value="Despachar" onClick="javascript:despachar_datos();">
				
              </div></td>
          </tr>
        </table></td>

    </tr>
  </table>
  </form>
  <?php mssql_close($cn);?>	
</body>
</html>
