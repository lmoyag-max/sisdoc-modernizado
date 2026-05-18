<?php
include("conexion_bd.php");
include("carga_tablas.php");

global $Confidencial;
$sw_cons=2 ;
//$cusuario ="kiter";
//$idusuario=3;
//$idfuncionario=11;
$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;
$cons=$sw_cons;
// avanzada viene con 1 desde el frame_menuvars  cuando es la opcion de busqueda avanzada  
// el parametro consulta viene con 1 desde el menu del frames_menu_consultas.php //
$soloconsulta=$menucons;
//echo " 1 consulta" . $solocons ." menu cons " . $menucons. "<br>";
if  (isset($avanzada))
{
$avanzada =$avanzada;
}
else
{$avanzada =0;}
	
$avanzada =$avanzada;
$si_avanza=$avanza;
if ($sw_cons=0 )
{
$avanza=$sw_cons;}
if ($sw_cons=2)
{
$avanza =2;
}
$si_avanza=$avanza;
if($cons==1)
{
$titulo="BUSQUEDA DE DOCUMENTOS";
}
else
{
$titulo="BUSQUEDA DE DOCUMENTOS ";
}
//echo "funcionario  " . $sw_cons . " *** cusuario  " . $cusuario . "**** xx  " . $idusuario;
$flujo1=$flujook;
if ($flujook==8){
$num_int=0;}
else{

$num_int=$num_int;}
$flujo1= 0;

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
		if ($avanzada ==1 )
		{
	// solo debe tomar los que el usuario tiene acceso y ademas esten vigentes 
		 $rs_procedencia = mssql_query("select dependencia.id_dependencia, SUBSTRING(dependencia.desc_dependencia, 1, 35) AS desc_dependencia, dependencia.cod_dependencia  from dependencia, acceso where acceso.id_dependencia = dependencia.id_dependencia  and vigencia is NULL and acceso.id_usuario =$xx",$cn);		                    
		}
		else
		{
		 $rs_procedencia = mssql_query("select dependencia.id_dependencia, SUBSTRING(dependencia.desc_dependencia, 1, 35) AS desc_dependencia, dependencia.cod_dependencia  from dependencia order by desc_dependencia",$cn);
		}			   
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
//$fecha_x = date("d-m-Y");
$fecha1 =date("02-01-Y");
//$fecha1 =date("02-01-2000");
if($si_avanza==1) {
$fecha1 =date("d-m-Y");
}
$fecha2=date("d-m-Y");


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario busca documento para consultas </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript" type="text/JavaScript">
<!--
var sw_ok;
var cont_arreglo;
var z=0;
var arreglo2 ="";
var ar_descrip =new Array();
var flujo2= <?php echo $flujo1; ?>;  
var sw= <?php echo $cons; ?>; 
var fechafin=<?php echo $fecha2; ?>;
var avance= <?php echo $si_avanza; ?>; 
function mensaje() 
{ 
  if (flujo2==1 )
  {
    alert("No existen Registros");
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
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&des_d="+selindice+"&sw="+valor;
	}
}

function destino_interno()
{
var selindice, nuevalsel;
var valor="I";
nuevasel=2;
top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
}

function procedencia_interna()
{
 var selindice, nuevalsel;
var valor="I";
nuevasel= 1;
var p1= <?php echo $xx; ?>;
var p2 =<?php echo $cons;?>;
var p3 =<?php echo $avanzada;?>;

if ((p3==1 ))
// para el caso de busqueda avanzada 
{
top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor+"&pro_d="+p1;
}
else
{
// para el caso de busqueda global, derivados y cerrados 
 nuevasel =3 ;
 top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor+"&pro_d="+p1;
}
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
	}
}

function ver()
{
var fecha=<?php echo $fecha1 ;?>;
var sw_pasa=0;

if (document.form1.Txt_fecha_ini.value=='')
  {
     alert ("Debe ingresar fecha inicio ");
     sw_pasa = 1;
     document.form1.Txt_fecha_ini.focus()
     return false ;
  }
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
if (sw_pasa==0)
  {	
    if (sw==0)
    {
       //alert("deriva");	
       document.form1.action="multi_derivacion.php?menu=23";
       document.form1.submit();
    }
   else
   if (sw==1) 
    {
      document.form1.action="doc_enc.php?avanza=<?php echo $si_avanza;?>&solocons=<?php echo $menucons ;?>";
      document.form1.submit();
   }
  else
     if (sw==2 ) 
     {
//     document.form1.action="informe2_p1.php" ;
     document.form1.action="informe2_1.php" ;
      document.form1.submit();
     }
 }
   return true;
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
//	document.form1.arreglo.value=x + "@" + arreglo2;
	//cont_arreglo = x;
	var b = document.form1.arreglo.value
	var temp = new Array();
	temp = b.split('@');
	x = temp[0];
	cont_arreglo = x;
	
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
function muestrala(p)
{

if (p==0) {
    MM_showHideLayers('Layer2','','hide');  }
        else{
MM_showHideLayers('Layer2','','show');   	
	}
}
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
 /*for (k=1;k<=x;k++){
  if (temp[k]==13){
     sw_invita=13;
 	muestrala(1);  }
   }*/
 if (sw_invita==0) {
        muestrala(0);
    }

}




//-->
</script>
<script src="js/calendario.js"></script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<!--body bgcolor="#FFFFFF" onload="mensaje()"-->
<body bgcolor="#FFFFFF" >
<center>
<form name="form1" method="Post" action="busca_doctogab_informe2.php">

  <div id="Layer4" style="position:absolute; left:436px; top:147px; width:170px; height:23px; z-index:3; visibility: visible;"> 
  </div>

    <font color="#000000" face="Arial, Helvetica, sans-serif"> </font> 
    <table width="650" height="26" border="0">
      <tr> 
        <td width="100%"><div align="right"><strong><font color="#0000A0" size="1"><strong><?echo "Usuario : " . $cusuario?></strong></font></strong></div></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="676"><div align="center"><font color="#FFFFFF" size="4"><strong><? echo $titulo; ?></strong></font></div></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#E6EEFF">
      <tr> 
        <td width="676"  align="center"> 
          <table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="562" class="texto"><strong><font color="#804040" face="Arial, Helvetica, sans-serif">Ingrese 
                los campos por los que desea buscar</font></strong></td>
              <td width="75"><div align="right"><strong><font color="#0000A0" size="1"></font><font color="#0000A0" size="2"> 
                  </font></strong></div></td>
            </tr>
          </table>
          <table width="100%" height="149" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="637" height="147" align="center"> 
                <div align="center"> 
                  <table width="100%" border="0">
                    <tr> 
                      <td width="16%"><font color="#000000">Tipo de Docto</font></td>
                      <td width="84%"><font color="#000000"> 
                        <select name="Cbo_Tipo_Docto" class="combo" id="select5">
                          <option value="0"> </option>
                        <?   while($reg=mssql_fetch_array($rs_tipo_docto)){ ?>  
                          <option value=<? echo $reg[id_tipo_documento] ?> >  
                          <? echo $reg[desc_tipo_documento] ?></option><? }?>   
                        </select>
                        </font><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font></td>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="16%" height="26"><font color="#000000">Fecha 
                        Inicial</font></td>
                      <td width="18%"><p><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                          <input name="Txt_fecha_ini" type="text" class="entradas" id="Txt_fecha_ini3"  onBlur="chequeafecha(this,0)"  size="8" maxlength="10">
	          <a href="javascript:show_Calendario('form1.Txt_fecha_ini');"><img src="../imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a></font><font color="#000000" face="Arial, Helvetica, sans-serif"><strong></strong> 
                          </font></p></td>
                       <td width="12%"><font>&nbsp;</font>Fecha Final</td>
                      <td width="31%"> <font color="#000000" face="Arial, Helvetica, sans-serif">
					  <!--strong><?php echo $fecha2 ; ?></strong--></font> 
                        <input name="Txt_fecha_fin" type="text" class="entradas" id="Txt_fecha_fin2" value="<?=$fecha2?>" onBlur="chequeafecha(this,0)"  size="8" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_fin');"><img src="../imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a>
                      <td width="23%"></font></tr>
                  </table>
                  <strong></strong> 
                
                </div></td>
            </tr>
          </table>
    
    <table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="313" height="17"><font color="#804040"><strong>ORIGEN</strong></font></td>
              <td width="328"><font color="#804040"><strong>DESTINO</strong></font></td>
            </tr>
          </table>
          <table width="100%" border="1" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="320" height="96"> 
                <table width="320" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="101"><strong>Interno 
                      <?php if($Procedencia=="I") { ?>
                      <input name="radioprocedencia" type="radio" onClick="javascript:procedencia_interna();" checked="true">
                      </strong> 
                      <?php ;} else {?>
                      <input name="radioprocedencia" type="radio" onClick="javascript:procedencia_interna();" > 
                      <?php ;} ?>
                    </td>
                    <td width="212"><strong>Externo 
                      <?php if($Procedencia=="E") { ?>
                      <input name="radioprocedencia" type="radio" onClick="javascript:procedencia_externa();" checked="true">
                      </strong> 
                      <?php ;} else {?>
                      <input name="radioprocedencia" type="radio" onClick="javascript:procedencia_externa();"> 
                      <?php ;} ?>
                    </td>
                  </tr>
                  <tr> 
                    <td width="101">Procedencia</td>
                    <td width="212"><font face="Arial">&nbsp; </font></td>
                  </tr>
                  <tr> 
                    <td colspan="2"><font face="Arial">
                      <select name="Cbo_Procedencia" class="combo" id="select4" >
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
                </table></td>
              <td width="323"><table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="86"> <div align="left"><strong>Interno 
                        <input name="radiodestino" type="radio" onClick="javascript:destino_interno();" value="1" checked>
                        </strong></div></td>
                    <td width="221"><strong>Externo 
                      <input name="radiodestino" type="radio"  value="2" onClick="javascript:destino_externo();">
                      </strong></td>
                  </tr>
                  <tr> 
                    <td width="86">Destino</td>
                    <td width="221"><font face="Arial">&nbsp; </font></td>
                  </tr>
                  <tr> 
                    <td colspan="2"><font face="Arial">
                      <select name="Cbo_Destinatario" class="combo" id="select6" >
                        <option value="0"> </option>
                        <?
		while($reg_destino=mssql_fetch_array($rs_dest)){
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
          <table width="100%" border="0">
            <tr> 
              <td width="213">
                <div align="center"> 
                  <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                  <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
                  <input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
                  <input type="hidden" name="numinterno">
                  <input type="hidden" name="sw_cons" value="<? echo $cons;?>">
                  <input type="hidden" name="numoficial" >
                  <input type="hidden" name="numexterno" >
                  <input type="hidden" name="arreglo" >
                  <input type="hidden" name="tipo_procedencia" >
                  <input type="hidden" name="tipo_destino" >
                  <input type="hidden" name="Procedencia" >
                  <input type="hidden" name="dependencia_usuario" value="<? echo $reg_func[id_dependencia];;?>">
                 <input type="hidden" name="avanza"   value="<? echo $si_avanza;?>">
                  <input type="hidden" name="avanzada"   value="<? echo $avanzada;?>">
                  <input type="hidden" name="materia"   value="<? echo $TxtMateria;?>">
                  <input type="hidden" name="soloconsulta">
                  <input type="hidden" name="menucons" value="<?echo $menucons;?>">
                  <input type="hidden" name="solocons" value="<?echo $menucons;?>">
                  
                </div></td>
              <td width="214"> <div align="center">
                  <input type="submit" name="Submit" value="Buscar" onclick=" ver();">
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
