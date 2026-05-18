<?php
include("conexion_bd.php");
include("carga_tablas.php");

global $Confidencial;
/*$cusuario='ximena';
$idusuario=3;
$idfuncionario=3;
*/

$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;
$flujo1=0;

$rs_funcionario = mssql_query("SELECT id_dependencia FROM funcionario where id_funcionario = " . $fun, $cn);

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
		from dependencia, acceso where acceso.id_dependencia = dependencia.id_dependencia 
		and acceso.id_usuario =$xx",$cn    );
		$id_dependencia=0;
		$Procedencia="I"; 
      }	
}

$Cbo_Estado_Docto=1;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario busca gestion tramites</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript" type="text/JavaScript">
<!--
var sw_ok;
var cont_arreglo;
var z=0;
var arreglo2 ="";
var formato = 1 ;

function ver()
{
 document.form1.submit();
}

function popup(url,ancho,alto,scroll){
newWin=window.open(url,"popup","resize=1,toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars="+scroll+",resizable=0,width="+ancho+",height="+alto+",top=300,left=300");
   return;
newWin.focus();
}



function muestra(cod)
{
 ar_descrip[z]= cod;
 z=z+1;
 
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
 

function CheckLength(length) {
if (window.event.srcElement.value.length >= length)
 {
   alert('El Máximo de caracteres es  250');
   return false;                         
}
}

 function validarentero(formu){ 
      //intento convertir a entero. 
	  var formu;
     //si era un entero no le afecta, si no lo era lo intenta convertir 
     formu.txtdias.value = parseInt(formu.txtdias.value);
	 //Compruebo si es un valor numérico 
      if (isNaN(formu.txtdias.value)) { 
            //entonces (no es numero) devuelvo el valor cadena vacia 
			formu.txtdias.value ="";
			alert ("Debe ingresar solamente numeros");
            return formu.txtdias.value 
      }else{ 
            //En caso contrario (Si era un número) devuelvo el valor 
            return formu.txtdias.value
      } 
} 

function invFecha(nTipFormat,dFecIni){
	var dFecIni = dFecIni.replace(/-/g,"/");					// reemplaza el - por /	
	
	// primera division fecha
	var nPosUno  = ponCero(dFecIni.substr(0,dFecIni.indexOf("/")));
	// 2º divicion fecha
	var nPosDos  = ponCero(dFecIni.substr(parseInt(dFecIni.indexOf("/")) + 1,parseInt(dFecIni.lastIndexOf("/")) - parseInt(dFecIni.indexOf("/")) - 1));
	// 3º divicion fecha
	var nPosTres = ponCero(dFecIni.substr(parseInt(dFecIni.lastIndexOf("/")) + 1));

	switch(nTipFormat){
		case 1 :	//	DD/MM/YYYY
			dReturnFecha = nPosTres + "" + nPosDos + "" + nPosUno;
			break;

		case 2 :	//	MM/DD/YYYY
			dReturnFecha = nPosTres + "" + nPosUno + "" +nPosDos;
			break;

		case 3 :	//	YYYY/MM/DD
			dReturnFecha = nPosUno + "" + nPosDos + "" +nPosTres;
			break;
	
		case 4 :	//	YYYY/DD/MM
			dReturnFecha = nPosUno + "" + nPosTres + "" +nPosDos;
			break;
	}
	
	return dReturnFecha;	// retorna la fecha 	
}

// Agrega un cero delante del strPon cuando tenga solo un caracter
function ponCero(strPon){
	if(parseInt(strPon.length) < 2)
		strPon = "0" + strPon;
	return strPon;
}


function validafecha(dato)
{
	//var Fecha= new String(Cadena)	// Crea un string
	if (dato == 1)
	{var Fecha =new String(document.form1.fecha_ini.value);}
	else
	{var Fecha =new String(document.form1.fecha_fin.value);}
	
	var RealFecha= new Date()	// Para sacar la fecha de hoy
	// Cadena Año
	var Ano= new String(Fecha.substring(Fecha.lastIndexOf("-")+1,Fecha.length))
	// Cadena Mes
	var Mes= new String(Fecha.substring(Fecha.indexOf("-")+1,Fecha.lastIndexOf("-")))
	// Cadena Día
	var Dia= new String(Fecha.substring(0,Fecha.indexOf("-")))

	// Valido el año
	if (Ano !='')
	{
	if (isNaN(Ano) || Ano.length<4 || parseFloat(Ano)<1900){
        	alert('Año inválido')
            
		return false
	}
	}
	// Valido el Mes
	if (isNaN(Mes) || parseFloat(Mes)<1 || parseFloat(Mes)>12)
	{
		alert('Mes inválido')
		return false
	}
	// Valido el Dia
	if (isNaN(Dia) || parseInt(Dia)<1 || parseInt(Dia)>31){
		alert('Día inválido')
		return false
	}
	if (Mes==4 || Mes==6 || Mes==9 || Mes==11 || Mes==2) {
		if (Mes==2 && Dia > 28 || Dia>30) {
			alert('Día inválido')
			return false
		}
	}

  //para que envie los datos, quitar las  2 lineas siguientes
  //alert("Fecha correcta.")
  return false	
}
function comparaFecha(dFormat,dFecMenor, dFecMayor){
	dFecMenor = invFecha(dFormat,dFecMenor);
	dFecMayor = invFecha(dFormat,dFecMayor);
	if(dFecMenor >= dFecMayor)
		return false;
	else
		return true;
}

function validaFechafin( formulario ){
	var dFechaMenor = formulario.fecha_fin.value;
	var dFechaMayor = formulario.fecha_ini.value;
    if (dFechaMenor!='' && dFechaMayor !='')
	{ 
	  if(comparaFecha( formato,dFechaMenor,dFechaMayor) == true)
		  alert("La fecha es menor. ");
	}
	/*else
		alert("Error. La fecha NO es menor.");*/
}

function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);


function buscar()
{
//if( (document.form1.fecha_ini.value!='')  && (document.form1.fecha_fin.value =='') )
if( ((document.form1.fecha_ini.value=='') && (document.form1.fecha_fin.value !='')) || ((document.form1.fecha_ini.value != '') && (document.form1.fecha_fin.value =='')) )
  {
    alert("Falta ingresar fecha ");
	document.form1.fecha_ini.focus();
  }
  else
   if (document.form1.txtdias.value =='' && document.form1.fecha_fin.value =='' &&    document.form1.fecha_ini.value =='')
   {
    alert("No hay datos a buscar");
   }
  else
  {
    document.form1.submit();
  }
}



function chequeafecha(objeto,calendario) 
{
  var campodefecha = objeto;
  if (chkfecha(objeto,calendario) == false) 
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

function chkfecha(objeto,cualcalen) 
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

<script src="js/calendario.js"></script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">


</head>

<body bgcolor="#FFFFFF" >
<center>
<form name="form1" method="Post" action="doc_gestion.php">
    <table width="638" height="26" border="0">
      <tr> 
        <td width="719"><div align="right"><strong><font color="#0000A0" size="1"><strong><?echo "Usuario : " . $cusuario?></strong></font></strong></div></td>
      </tr>
    </table>
    <table width="635" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="629" height="40">
<div align="center">
            <p><font color="#FFFFFF" size="4"><strong>BUSQUEDA DOCUMENTOS ATRASADOS</strong></font></p>
          </div></td>
      </tr>
    </table>
    <table width="633" border="1" cellpadding="1" cellspacing="0" bgcolor="#E6EEFF">
      <tr> 
        <td width="627" height="194"  align="center"> 
          <table width="95%" border="0">
            <tr> 
              <td width="46%" height="33"> 
                <div align="right"><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp;N&ordm; 
                  D&iacute;as </font></div></td>
              <td width="54%"> 
                <input name="txtdias" type="text" id="txtdias" size="3" maxlength="3" onblur="validarentero(form1);"></td>
            </tr>
          </table>
          <table width="95%" border="0">
            <tr> 
              <td height="24"> 
                <div align="right">Fecha Inicio</div></td>
              <td><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                <!--input name="fecha_ini" type="text" class="entradas" id="fecha_ini"  onblur="validafecha(1)" size="10" maxlength="10"-->
    <input name="fecha_ini" type="text" class="entradas" id="fecha_ini"  onBlur="chequeafecha(this,0)"  size="8" maxlength="10">
                </font></td>
              <td>Fecha Termino</td>
              <td><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                <!--input name="fecha_fin" type="text" class="entradas" id="fecha_fin"  onblur="validaFechafin(form1);validafecha(2)" size="10" maxlength="10"-->
				<input name="fecha_fin" type="text" class="entradas" id="fecha_fin" onBlur="chequeafecha(this,0)" size="8" maxlength="10">
			   </font></td>
            </tr>
            <tr> 
              <td width="23%" height="21">&nbsp;</td>
              <td width="30%"><strong><font color="#000000" size="1" face="Times New Roman, Times, serif, Monotype Corsiva">Formato dd-mm-aaaa </font></strong></td>
              <td width="16%">&nbsp;</td>
              <td width="31%"><strong><font color="#000000" size="1" face="Times New Roman, Times, serif, Monotype Corsiva">Formato dd-mm-aaaa </font></strong></td>
            </tr>
          </table>
          <p>&nbsp;</p><table width="613" border="0">
            <tr> 
              <td width="167" height="41"> 
                <div align="center"> 
                  <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                  <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
                  <input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
                </div></td>
              <td width="251"> <div align="center"> 
               <!--<input type="submit" name="Submit" value="Buscar" > -->
			   <input type="button" value="Buscar" onclick ="buscar()">
                </div></td>
              <td width="181">&nbsp;</td>
            </tr>
          </table>
          <p>&nbsp;</p> </td>
    </tr>
  </table>
  </form>
  
<?php mssql_close($cn);?>
</center>
</body>
</html>
