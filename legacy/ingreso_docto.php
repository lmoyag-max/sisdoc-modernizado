<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
$Usuario =  "Ximena";

$fecha_x = date("d-m-Y");
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Documento sin t&iacute;tulo</title>


<script language="JavaScript" type="text/javascript">
function busca(Forma) {
if (!Forma.Txt_numdoc.value=="" ) {
return (true);
}
else 
return (false);
}

function guardar(Forma) {
if (!Forma.Txt_numdoc.value=="" ) {
location.href="ingreso.php";
return (false);
}
else 
return (true);
}

function valida_digito(cadena,objeto,largo)
{	//-----------------------------
	var i;
        var allowedac;
        var retorno;
        retorno = true;
        allowedac = "0123456789";
        for ( i=0; i < cadena.length; i++ )
          if (allowedac.indexOf(cadena.charAt(i)) < 0)  {
                  retorno = false;
          }
        //if (cadena.length<largo && cadena.length>0) {
        //  retorno = false;
        //}
        if (!retorno) {
             objeto.value = "0";
             alert("Solo se aceptan números enteros")
             objeto.focus();
        }
	return retorno;
}   
function enviar_datos() 
	
{
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
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC">
<form name="form1" 
method="post"
action="guardar_ingreso.php">
  <table width="650" border="2" cellpadding="1" cellspacing="1" bordercolor="#990066" bgcolor="#FFFFCC">
    <tr>
      <td width="500" height="493" align="center"> 
	  	<table width="620" border="0" align="center" cellpadding="2" cellspacing="2">
          <tr> 
            <td align="center"><font color="#0000A0" size="4">INGRESO DE DOCUMENTOS</font></td>
          </tr>
        </table>
        <table width="620" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td align="center">
<div align="right"><strong><font color="#0000A0" size="2"><?echo "Usuario : " . $Usuario?></font></strong></div></td>
          </tr>
        </table>
        <table width="620" height="222" border="1" align="center" cellpadding="2" cellspacing="2">
          <tr> 
            <td width="609" height="208" align="center"> <div align="center"> 
                <table width="600" border="0" cellspacing="2" cellpadding="2">
                  <tr> 
                    <td width="100"><font size="2">Tipo de Docto</font> </td>
                    <td width="200"> <select name="Cbo_Tipo_Docto" id="select5">
                        <?
				   while($reg=mssql_fetch_array($rs_tipo_docto)){
				?>
                        <option value=<? echo $reg[id_tipo_documento] ?> ><? echo $reg[desc_tipo_documento] ?></option>
                        <?
}
?>
                      </select> </td>
                    <td width="100">Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                    <td width="200"><font face="Arial, Helvetica, sans-serif"> 
                      <input name="Txt_fecha_doc" type="text" id="Txt_fecha_doc2" size="10" maxlength="10" value="<?=$fecha_x?>">
                      <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                      </font></td>
                  </tr>
                  <tr> 
                    <td>Estado</td>
                    <td><select name="Cbo_Estado_Docto" id="select10">
                        <?
				   while($reg_estado_docto=mssql_fetch_array($rs_estado_docto)){
				?>
                        <option value=<? echo $reg_estado_docto[id_estado_documento] ?> ><? echo $reg_estado_docto[desc_estado_documento] ?></option>
                        <?
}
?>
                      </select></td>
                    <td>Confidencial</td>
                    <td><input name="Confidencial" type="checkbox" value="S">
                      Original 
                      <input type="checkbox" name="Original" value="S"></td>
                  </tr>
                </table>
                <table width="600" border="0" cellspacing="2" cellpadding="2">
                  <tr valign="middle"> 
                    <td width="200">Interno<font size="7" face="Arial"> 
                      <input name="TxtInterno" type="text" id="TxtInterno" size="8" maxlength="8" onBlur="valida_digito(this.value,this,8);">
                      </font></td>
                    <td width="200">Oficial<font size="7" face="Arial"> 
                      <input name="TxtOficial" type="text" id="TxtOficial" size="8" maxlength="8" onBlur="valida_digito(this.value,this,8);">
                      </font></td>
                    <td width="200">Externo<font size="7" face="Arial"> 
                      <input name="TxtExterno" type="text" id="TxtExterno2" size="8" maxlength="8" onBlur="valida_digito(this.value,this,8);">
                      </font></td>
                  </tr>
                </table>
                <table width="600" border="0" cellspacing="2" cellpadding="2">
                  <tr> 
                    <td width="100">Procedencia</td>
                    <td width="200"><font face="Arial"> 
                      <select name="Cbo_Procedencia" id="select6">
                        <?
		while($reg_procedencia=mssql_fetch_array($rs_procedencia)){
?>
                        <option value=<? echo $reg_procedencia[id_procedencia_destino] ?> ><? echo $reg_procedencia[desc_procedencia_destino] ?></option>
                        <?
}
?>
                      </select>
                      </font></td>
                    <td width="100">Destinatario</td>
                    <td width="200"><font face="Arial"> 
                      <select name="Cbo_Destinatario" id="select7">
                        <?
		while($reg_destino=mssql_fetch_array($rs_destino)){
?>
                        <option value=<? echo $reg_destino[id_procedencia_destino] ?> ><? echo $reg_destino[desc_procedencia_destino] ?></option>
                        <?
}
?>
                      </select>
                      </font></td>
                  </tr>
                </table>
                <table width="600" border="0" cellspacing="2" cellpadding="2">
                  <tr> 
                    <td width="100" height="76">Materia</td>
                    <td width="500"> <textarea name="TxtMateria" cols="60" rows="3"></textarea></td>
                  </tr>
                </table>
              </div></td>
          </tr>
        </table>
        <table width="620" border="1" align="center" cellpadding="2" cellspacing="2">
          <tr> 
            <td align="center"> <div align="center"> 
                <table width="600" border="0" cellspacing="2" cellpadding="2">
                  <tr> 
                    <td width="120">Tipo Distribucion <font face="Arial">&nbsp; </font></td>
                    <td width="150"><font face="Arial"> 
                      <select name="Cbo_Tipo_Distribucion" id="select3">
                        <?
		while($reg_distribucion=mssql_fetch_array($rs_distribucion)){
?>
                        <option value=<? echo $reg_distribucion[id_tipo_distribucion] ?> ><? echo $reg_distribucion[desc_tipo_distribucion] ?></option>
                        <?
}
?>
                      </select>
                      </font></td>
                    <td>Medio 
                      <select name="Cbo_Medio" id="Cbo_Medio">
                        <option value="P">Papel</option>
                        <option value="C">Correo</option>
                        <option value="V">Video</option>
                      </select></td>
                  </tr>
                  <tr> 
                    <td width="120">Tipo Compromiso<font face="Arial">&nbsp; </font></td>
                    <td width="150"><font face="Arial"> 
                      <select name="Cbo_Tipo_Compromiso" id="select4">
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
                    <td>Estado Compromiso<font face="Arial"> 
                      <select name="Cbo_Estado_Compromiso" id="select20">
                        <?
		while($reg_estado_compromiso=mssql_fetch_array($rs_estado_compromiso)){
?>
                        <option value=<? echo $reg_estado_compromiso[id_estado_compromiso] ?> ><? echo $reg_estado_compromiso[desc_estado_compromiso] ?></option>
                        <?
}
?>
                      </select>
                      </font></td>
                  </tr>
                </table>
                <table width="600" border="0" cellspacing="2" cellpadding="2">
                  <tr> 
                    <td width="100">Observaci&oacute;n</td>
                    <td width="500"><textarea name="textarea2" cols="60" rows="3"></textarea></td>
                  </tr>
                </table>
              </div></td>
          </tr>
        </table>
        <table width="620" border="1" align="center" cellpadding="2" cellspacing="2">
          <tr> 
            <td height="48"> <div align="center"> 
                <input type="button" name="cmd_grabar" value="Grabar" onClick="enviar_datos();">
              </div></td>
          </tr>
        </table>
        
      </td>
    </tr>
  </table>
  </form>
</body>
</html>
