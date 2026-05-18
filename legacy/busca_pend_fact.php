<?php
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
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

$fecha1 =date("02-01-Y");
$fecha2=date("d-m-Y");



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario busca tramites pendientes</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript" type="text/JavaScript">
<!--
var sw_ok;
var cont_arreglo;
var z=0;
var arreglo2 ="";

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

function proc_externo()
{
var selindice, nuevalsel;
var valor="PE";
if  (document.form1.radioproc[1].checked==true)
	{
	selindice = document.form1.Cbo_Procedencia.selectedIndex;
	nuevasel = document.form1.Cbo_Procedencia.options[selindice].value;
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&des_d="+selindice+"&sw="+valor;
	document.form1.Cbo_Func_Procedencia.options.value=0;
	document.form1.Cbo_Func_Procedencia.disabled=true;
	document.form1.Cbo_Procedencia.disabled=false;
	}
}


function proc_interno()
{
var selindice, nuevalsel;
var valor="PI";
nuevasel= 1;
top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
document.form1.Cbo_Procedencia.disabled=false;
document.form1.Cbo_Func_Procedencia.disabled=false;
}



function procedencia_interna()
{
var selindice, nuevalsel;
var valor="I";
nuevasel= 1;
var p1= <?php echo $xx; ?>;
top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor+"&pro_d="+p1;
document.form1.Cbo_Procedencia.disabled=false;
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
	document.form1.Cbo_Func_Procedencia.disabled=true;
	}
}
function cambio11()
{
var selindice, nuevalsel;
var valor="";
if (document.form1.radioprocedencia[0].checked==true)
	{

	selindice = document.form1.Cbo_Procedencia.selectedIndex;
	nuevasel = document.form1.Cbo_Procedencia.options[selindice].value;
	//alert("cambio11 "+selindice);
	document.form1.val_procedencia.value= nuevasel;
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
	document.form1.Cbo_Func_Procedencia.disabled=false;
	}
else
if (document.form1.radioprocedencia[1].checked==true){
	document.form1.val_procedencia.value=  document.form1.Cbo_Procedencia.selectedIndex;
	document.form1.val_funcionario.value= 0;
	//document.form1.Cbo_Func_Procedencia.disabled=true;
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
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
	document.form1.Cbo_Func_Destino.disabled=false;
	}
else
if (document.form1.radiodestino[1].checked==true){
	document.form1.val_destino.value= document.form1.Cbo_Destinatario.selectedIndex;
	document.form1.val_funcionario1.value=0;
	}
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


function ver()
{

if (document.form1.Cbo_Procedencia.value==0  && document.form1.Cbo_Destinatario.value==0)
{
 alert('Debe Seleccionar Procedencia y/o Destinatario');
 }
 else
  {
if  (document.form1.radioprocedencia[0].checked==true)
{
   	document.form1.tipo_procedencia.value="I";
}
else if  (document.form1.radioprocedencia[1].checked==true)
{
	document.form1.tipo_procedencia.value="E";
}	

if (document.form1.radiodestino[0].checked==true)
{
   	document.form1.tipo_destino.value="I";
}
else if(document.form1.radiodestino[1].checked==true)
{
	document.form1.tipo_destino.value="E";
}
  document.form1.action="imp_pend2_fact.php" ;
 document.form1.submit();
 }
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




<script language="JavaScript" type="text/JavaScript">
<!--


function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>
<script src="js/calendario.js"></script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">


</head>

<body bgcolor="#FFFFFF" >
<center>
<form name="form1" >
    <table width="656" height="26" border="0">
      <tr> 
        <td width="719"><div align="right"><strong><font color="#0000A0" size="1"><strong><?echo "Usuario : " . $cusuario?></strong></font></strong></div></td>
      </tr>
    </table>
    <table width="656" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="650" height="40">
<div align="center">
            <p><font color="#FFFFFF" size="4"><strong>BUSQUEDA FACTURAS PENDIENTES</strong></font></p>
          </div></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#E6EEFF">
      <tr> 
        <td width="665"  align="center"> 
          <table width="100%" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="320" class="texto"><strong><font color="#804040">Ingrese 
                los campos por los que desee buscar</font></strong></td>
            <td width="322"><div align="right"><strong><font color="#0000A0" size="1"></font><font color="#0000A0" size="2"> 
                  </font></strong></div></td>
          </tr>
        </table>
          <table width="100%" border="0">
            <tr> 
              <td width="13%" height="26"><font color="#000000">Fecha Inicial</font></td>
              <td width="37%"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                <input name="fecha_ini" type="text" class="entradas" id="fecha_ini" value="<?=$fecha1?>" onBlur="chequeafecha(this,0)" size="8" maxlength="10">
                <a href="javascript:show_Calendario('form1.fecha_ini');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                </font></td>
              <td width="22%">Fecha Final</td>
              <td width="28%"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                <input name="fecha_fin" type="text" class="entradas" id="fecha_fin" value="<?=$fecha2?>" onBlur="chequeafecha(this,0)" size="8" maxlength="10">
                <a href="javascript:show_Calendario('form1.fecha_fin');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                </font></td>
            </tr>
          </table>
          <table width="648" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="321"><font color="#804040"><strong>ORIGEN</strong></font></td>
              <td width="320"><font color="#804040"><strong>DESTINO</strong></font></td>
            </tr>
          </table>
          <table width="640" border="1" cellspacing="0" cellpadding="1">
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
                      <select name="Cbo_Procedencia" class="combo" id="Cbo_Procedencia" onChange="javascript:cambio11();">
                        <option value="0"> </option>
                        <? if ($Procedencia=="I"){
		    while($reg_procedencia=mssql_fetch_array($rs_dependencia)){
			?>
                        <option value=<? echo $reg_procedencia[id_dependencia] ?> ><? echo $reg_procedencia[desc_dependencia] ?></option>
                        <?
			}}
			else {
			while($reg_procedencia=mssql_fetch_array($rs_dependencia_externa)){
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
                    <td width="160"> <select name="Cbo_Func_Procedencia" class="combo" id="Cbo_Func_Procedencia" onChange="javascript:cambio2();">
                        <option value="0"> </option>
                      </select> </td>
                  </tr>
                </table></td>
              <td width="311"><table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="105"> <div align="center"><strong>Interno 
                        <input name="radiodestino" type="radio" onClick="javascript:destino_interno();" value="1" checked>
                        </strong></div></td>
                    <td width="105"><strong>Externo 
                      <input name="radiodestino" type="radio"  value="2" onClick="javascript:destino_externo();">
                      </strong></td>
                  </tr>
                  <tr> 
                    <td width="105">Destino</td>
                    <td><font face="Arial"> 
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
                    <td width="105" height="42">Funcionario</td>
                    <td><font face="Arial"> 
                      <select name="Cbo_Func_Destino" class="combo" id="select" onChange="javascript:cambio3();">
                        <option value="0"> </option>
                      </select>
                      </font></td>
                  </tr>
                </table></td>
            </tr>
          </table>
          <p>&nbsp;</p>
          <table width="640" border="0">
            <tr> 
              <td width="213" height="51">
			  
                <div align="center"> 
				
				  <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                  <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
                  <input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
                  <input type="hidden" name="numinterno" >
                  <input type="hidden" name="numoficial" >
                  <input type="hidden" name="numexterno" >
                  <input type="hidden" name="arreglo" >
				  <input type="hidden" name="val_procedencia" >
				  <input type="hidden" name="val_funcionario" >
                  <input type="hidden" name="tipo_procedencia" >
				  <input type="hidden" name="val_funcionario1" >
				  <input type="hidden" name="val_destino" >
				  <input type="hidden" name="tipo_destino" >                         
                </div></td>
              <td width="214"> <div align="center">
                  <input type="submit" name="Submit" value="Buscar" onClick="chequear_arreglo(<?php echo $nRows?>);ver();">
                </div></td>
              <td width="213">&nbsp;</td>
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
