<?php
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;
$flujo1=0;
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
var sw_multiple=0;
var cont_arreglo;
var cont_arreglo1;
var arreglo2 ="";
var arreglo1="";
var arreglo3="";
var ar_descrip =new Array();


function destino_externo()
{
var selindice, nuevalsel;
var valor="E";
if  (document.form1.radiodestino[1].checked==true)
	{
	selindice = document.form1.Cbo_Destinatario.selectedIndex;
	nuevasel = document.form1.Cbo_Destinatario.options[selindice].value;
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&des_d="+selindice+"&sw="+valor;
//	document.form1.Cbo_Func_Destino.options.value=0;
	//document.form1.Cbo_Func_Destino.disabled=true;
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
//document.form1.Cbo_Func_Destino.disabled=false;
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
	//document.form1.Cbo_Func_Procedencia.options.value=0;
	//document.form1.Cbo_Func_Procedencia.disabled=true;
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
//document.form1.Cbo_Func_Procedencia.disabled=false;
}



function procedencia_interna()
{
var selindice, nuevalsel;
var valor="I";
nuevasel= 3;
var p1= <?php echo $xx; ?>;
top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor+"&pro_d="+p1;
document.form1.Cbo_Procedencia.disabled=false;
//document.form1.Cbo_Func_Procedencia.disabled=false;
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
	//document.form1.Cbo_Func_Procedencia.disabled=true;
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
	//alert("cambio11 "+selindice+'nuevasel'+nuevasel);
	document.form1.val_procedencia.value= nuevasel;
	//top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
	//document.form1.Cbo_Func_Procedencia.disabled=false;
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
//	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
	//document.form1.Cbo_Func_Destino.disabled=false;
	}
else
if (document.form1.radiodestino[1].checked==true){
	document.form1.val_destino.value= document.form1.Cbo_Destinatario.selectedIndex;
	//document.form1.val_funcionario1.value=0;
	}
}


function cambio2()
{
var selindice, nuevalsel;
var valor="";
//selindice = document.form1.Cbo_Func_Procedencia.selectedIndex;
//nuevasel = document.form1.Cbo_Func_Procedencia.options[selindice].value;
//document.form1.val_funcionario.value=selindice;
}


function cambio3()
{
var selindice, nuevalsel;
var valor="F";
//selindice = document.form1.Cbo_Func_Destino.selectedIndex;
//nuevasel = document.form1.Cbo_Func_Destino.options[selindice].value;
//document.form1.val_funcionario1.value=selindice;
}
function ver_destino()
{
if  (document.form1.radiodestino[0].checked==true )
    {
	document.form1.tipo_destino.value ="I";
	//document.form1.Cbo_Func_Destino.disabled=true;
	document.form1.Cbo_Destinatario.disabled=true;
	MM_showHideLayers('LayerInt','','show');
	}
else
if  (document.form1.radiodestino[1].checked==true )
	{
	document.form1.tipo_destino.value="E";
	//document.form1.Cbo_Func_Destino.disabled=true;
	document.form1.Cbo_Destinatario.disabled=true;
	MM_showHideLayers('LayerExt','','show');
	}
document.form1.val_destino.value=0;
//document.form1.val_funcionario1.value=0;
sw_multiple = 1;	
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

  document.form1.tipodest.value=document.form1.tipo_destino.value;
//alert(document.form1.tipodest.value);
  document.form1.action="imp_ingresos.php" ;
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
	//document.form1.Cbo_Func_Destino.disabled=true;
	document.form1.Cbo_Destinatario.disabled=true;
  }
  else
  {
//	document.form1.Cbo_Func_Destino.disabled=false;
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
	document.form1.arregloint.value=x + "@" + arreglo2;
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
<script src="../js/calendario.js"></script>
<link href="../css/estilo_doc.css" rel="stylesheet" type="text/css">


</head>

<body bgcolor="#FFFFFF" >
<center>
<form name="form1" >
    <table width="656" height="26" border="0">
      <tr> 
        <td width="719"><div align="right"><strong><font color="#0000A0" size="1"><strong><?echo "Usuario : " . $cusuario?></strong></font></strong></div></td>
      </tr>
    </table>
    <table width="681" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr> 
        <td width="675" height="40"> <div align="center"> 
            <p><font color="#FFFFFF" size="4"><strong>BUSQUEDA DOCUMENTOS INGRESADOS 
              </strong></font></p>
          </div></td>
      </tr>
     
    </table>
    <table width="720" border="1" cellpadding="1" cellspacing="0" bgcolor="#E6EEFF">
      <tr> 
        <td width="710"  align="center"> <table width="95%" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="356" class="texto"><strong><font color="#804040">Ingrese 
                los campos por los que desee buscar</font></strong></td>
              <td width="330"><div align="right"><strong><font color="#0000A0" size="1"></font><font color="#0000A0" size="2"> 
                  </font></strong></div></td>
            </tr>
          </table>
          <table width="95%" border="0">
            <tr> 
              <td width="13%" height="26"><font color="#000000">Fecha Inicial</font></td>
              <td width="37%"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                <input name="fecha_ini" type="text" class="entradas" id="fecha_ini" value="<?=$fecha1?>" onBlur="chequeafecha(this,0)" size="8" maxlength="10">
                <a href="javascript:show_Calendario('form1.fecha_ini');"><img src="../imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                </font></td>
              <td width="14%">Fecha Final</td>
              <td width="36%"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                <input name="fecha_fin" type="text" class="entradas" id="fecha_fin" value="<?=$fecha2?>" onBlur="chequeafecha(this,0)" size="8" maxlength="10">
                <a href="javascript:show_Calendario('form1.fecha_fin');"><img src="../imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                </font></td>
            </tr>
          </table>
          <table width="95%" border="0">
            <tr> 
              <td width="16%"><font color="#000000">Tipo de Docto</font></td>
              <td width="84%"><font color="#000000"> 
                <select name="Cbo_Tipo_Docto" class="combo" id="select5">
                  <option value="0"> </option>
                  <?   while($reg=mssql_fetch_array($rs_tipo_docto)){ ?>
                  <option value=<? echo $reg[id_tipo_documento] ?> > <? echo $reg[desc_tipo_documento] ?></option>
                  <?}?>
                </select>
                </font><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; 
                </font><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; 
                </font></td>
            </tr>
          </table>
          <table width="688" border="1" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="327" height="100"> <table width="100%" border="0" cellspacing="1" cellpadding="1">
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
                </table></td>
              <td width="372"><table width="99%" height="64" border="0" cellpadding="1" cellspacing="1">
                  <tr> 
                    <td width="117"> <div align="center"><strong>Interno 
                        <input name="radiodestino" type="radio" onClick="javascript:destino_interno();" value="1" checked>
                        </strong></div></td>
                    <td width="234"><strong>Externo 
                      <input name="radiodestino" type="radio"  value="2" onClick="javascript:destino_externo();">
                      <font face="Arial"> 
                      <!--input name="boton" type="button" class="botones" onClick="javascript:ver_destino();" value="Múltiple"-->
                      </font> </strong></td>
                  </tr>
                  <tr> 
                    <td width="117" height="38">Destino</td>
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
                </table></td>
            </tr>
          </table>
      	  <div id="LayerInt" style="position:absolute; width:327px; height:166px; z-index:1; left: 235px; top: 292px; visibility: hidden; overflow: auto; background-color: #E6EEFF; layer-background-color: #E6EEFF; border: 1px none #000000;" class="texto"> 
            <table width="100%" border="1" bgcolor="#E6EEFF">
              <tr> 
                <td height="32"> <div align="center" onClick="MM_showHideLayers('LayerInt','','hide');MM_showHideLayers('LayerInt','','hide');ver_check(<?php echo $nRowsint;?>)"><strong>Aceptar</strong></div></td>
              </tr>
              <tr> 
                   <td height="159"> 
                        <?php 
							  $k=0;
							  while($reg_dependencia = mssql_fetch_array($rs_dependencia)) {  echo $reg_dependencia["id_dependencia"]. "<br>";?>
                        	  <input type="checkbox" name="casilla2" value="<?php echo $reg_dependencia["id_dependencia"];?>" onClick="javascript:muestra(<?php echo $reg_dependencia["id_dependencia"];?>);"> 
                        <?php echo $reg_dependencia["desc_dependencia"]  . "<br>"; } ?>
						</td>
                    </tr>
                  </table>
            <div align="right"></div>
          </div>
          <div id="LayerExt" style="position:absolute; width:326px; height:166px; z-index:1; left: 234px; top: 288px; visibility: hidden; overflow: auto;"> 
            <table width="100%" border="1" bgcolor="#E6EEFF">
              <tr> 
                <td height="27"> <div align="center" onClick="MM_showHideLayers('LayerExt','','hide');MM_showHideLayers('LayerExt','','hide');ver_check(<?php echo $nRowsext;?>)"><strong>Aceptar</strong></div></td>
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
            </table>
            <div align="right"></div>
          </div></td>
        <p>&nbsp;</p>
        <table width="640" border="0">
          <tr> 
            <td width="213" height="51"> <div align="center"> 
                <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
                <input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
                <input type="hidden" name="arreglo" >
                <input type="hidden" name="val_procedencia" >
                <input type="hidden" name="val_funcionario" >
                <input type="hidden" name="tipo_procedencia" >
                <input type="hidden" name="tipodest" >
				<input type="hidden" name="tipo_destino" >
                <input type="hidden" name="val_funcionario1" >
                <input type="hidden" name="val_destino" >
                <input type="hidden" name="tipo_destino" >
                <input type="hidden" name="arregloint" >
				<input type="hidden" name="arregloext"  >
				
                
              </div></td>
            <td width="214"> <div align="center"> 
                <input type="submit" name="Submit" value="Buscar" onClick="chequear_arreglo(<?php echo $nRows?>);ver();">
              </div></td>
            <td width="213">&nbsp;</td>
          </tr>
        </table>
      </tr>
    </table>
  </form>
  
<?php mssql_close($cn);?>
</center>
</body>
</html>
