<?php
include("conexion_bd.php");
include("carga_tablas.php");

// Modificado el 12/07/2003
$Usuario=$cusuario;
$xx= $idusuario;

$flujo1=$flujook;
$doc="exec busca_factura_mod '" . $iddocum . "'";
$rs_factura=mssql_query($doc);
 
$Totreg = mssql_num_rows($rs_factura);
if($Totreg>0)
 {
	$reg_doc=mssql_fetch_array($rs_factura);
	$txtdescripcion=trim($reg_doc["descripcion"]);

	$fec_doc=strtotime($reg_doc["fecha_factura"]);
	$fech_doc=date("d-m-Y",$fec_doc);
	//-- fecha timbre recepcion --// 
	$fecrec=strtotime($reg_doc["fecha_recepcion"]);	 
	$fech_rec=date("d-m-Y",$fecrec);
	$factura=$reg_doc["id_factura"];
	$tipo=$reg_doc["id_tipo_fact"];
	$tema =$reg_doc["id_tema_fact"];
		

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario modifica cabecera  factura</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/javascript">
<!--
var sw_ok;
var cont_arreglo;
var z=0;
var arreglo2 ="";
var ar_descrip =new Array();
var flujo2= <?php echo $flujo1; ?>;  

  
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
if (document.form1.Txt_fecha_timbre.value == "") 
  {
   sw_ok=false;
   alert ("Debe ingresar fecha  recepción ");
   document.form1.Txt_fecha_timbre.focus();
  }
else
if (document.form1.Txtdescripcion.value == "")
  {
 	sw_ok=false;
	alert("Falta Ingresar la descripcion de la factura");
	document.form1.Txtdescripcion.focus();
  }
if (sw_ok)
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
<!--

function CheckLength(length) {
if (window.event.srcElement.value.length >= length) {
   alert('El Máximo de caracteres es  250');
   return false;                         
}
}

function mensaje()
{ 
  if (flujo2==1) 
  {  alert("La factura ha sido Modificada");  }
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
<script src="js/calendario.js"></script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body bgcolor="#FFFFFF" onLoad="mensaje()">

<center>
<!--form name="form1" method="Post" action="guardar_modificacion.php" -->
<form name="form1" method="Post" action="guardar_modificacion_factura.php" >
    <table width="657" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="651" height="45">
<div align="center"><font color="#FFFFFF" size="4"><strong>MODIFICACION FACTURA</strong></font></div></td>
      </tr>
    </table>
    <table width="656" border="1" cellpadding="1" cellspacing="0" bgcolor="#E6EEFF">
      <tr> 
        <td width="656"  align="center"> 
          <table width="100%" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="320" class="texto"><strong><font color="#804040" face="Arial, Helvetica, sans-serif">IDENTIFICACION 
                FACTURA</font></strong></td>
            <td width="322"><div align="right"><strong><font color="#0000A0" size="2">
			<? echo "Usuario : " . $cusuario?></font></strong></div></td>
          </tr>
        </table>
          <table width="99%" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="620" height="237"  align="center"> 
                <div align="center"> 
                  <table width="100%" border="0" cellspacing="0" cellpadding="1">
                    <tr> 
                      <td height="38"><font color="#000000"><strong>Fecha Factura</strong></font></td>
                      <td height="46"><div align="left"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                          <input name="Txt_fecha_doc" type="text" class="entradas" id="Txt_fecha_doc2" value="<?php echo $fech_doc;?>" onBlur="chequeafecha(this,0)"  size="10" maxlength="10">
                          <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="imagen/icon-calen_f2.gif" width="25" height="24" border="0" name="calenda"></a> 
                          </font></div></td>
                      <td height="38" colspan="2"><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font><font color="#000000"><strong>Fecha recepci&oacute;n</strong></font><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_timbre" type="text" class="entradas" id="Txt_fecha_timbre" value="<?php echo $fech_rec;?>" onBlur="chequeafecha(this,0)"  size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_timbre');"><img src="imagen/icon-calen_f2.gif" width="25" height="24" border="0" name="calenda"></a></font></td>
                    </tr>
                    <tr> 
                      <td height="46"><font color="#000000"><strong>N&uacute;mero 
                        Factura</strong></font></td>
                      <td><font color="#000000"><font size="4" face="Arial"> </font><font color="#000000"><font size="4" face="Arial"> 
                        <input name="numfactura" type="text" class="entradas" id="numfactura" value="<?php echo $reg_doc["num_factura"];?>" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                        </font></font></font></td>
                      <td><strong>Monto</strong></td>
                      <td><font color="#000000"><font color="#000000"><font color="#000000"><font color="#000000"><font size="4" face="Arial"> 
                        <input name="monto" type="text" class="entradas" id="monto" value="<?php echo $reg_doc["monto"];?>" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                        </font><font face="Arial"><strong> (sin puntos)</strong></font></font></font><strong><font face="Arial"> 
                        </font></strong></font></font></td>
                    </tr>
                    <tr> 
                      <td width="111" height="38"><strong>Tipo factura</strong></td>
                      <td width="171" height="46"><font face="Arial">
                        <select name="tipo_factura" class="combo" id="select" >
                          <option value="0"> </option>
                          <?
							while($reg_tipo=mssql_fetch_array($rs_tipo_factura))
							{
					           echo '<option value="' . $reg_tipo[id_tipo_fact] . '"';
							   if($tipo==$reg_tipo[id_tipo_fact]) echo ' SELECTED';
							   echo '>' . $reg_tipo[desc_tipofactura] . "</option>\n";
					        }
	                       ?>
                        </select>
                        </font></td>
                      <td width="100" height="46"><font color="#000000"><strong>Tema 
                        Factura</strong></font></td>
                      <td width="248"><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp;
                        <select name="tema_factura" class="combo" id="select3" >
                          <option value="0"> </option>
                          <?
							while($reg_tema=mssql_fetch_array($rs_tema_factura))
							{
					           echo '<option value="' . $reg_tema[id_tema] . '"';
							   if($tema==$reg_tema[id_tema])  echo ' SELECTED';
							   echo '>' . $reg_tema[desc_tema] . "</option>\n";
					        }
	                       ?>
                        </select>
                        </font></td>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellpadding="1" cellspacing="0">
                    <tr> 
                      <td width="111" height="46"><strong>Rut Proveedor</strong></td>
                      <td width="523"><font face="Arial"> 
                        <input name="rut" type="text" value="<?php echo $reg_doc["rut_prov"];?>" size="9" maxlength="9" >
                        <strong>(sin d&iacute;gito y sin puntos)</strong></font></td>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellpadding="1" cellspacing="0">
                  </table>
                  <table width="100%" border="0" valign="top" cellspacing="0" cellpadding="1">
                    <tr> 
                      <td width="80" height="46"><font color="#000000"><strong><font size="2">Descripci&oacute;n</font></strong></font></td>
                      <td width="263"><font color="#000000" size="2"> 
                       <?php  echo '<textarea  name="Txtdescripcion" cols="70" rows="4" class="cajatexto"  onKeyPress="return CheckLength(250)">' . $txtdescripcion . '</textarea>'; ?> 
                        </font>                  
                
                        
                        
                        <font color="#000000" size="2">&nbsp; </font> </td>

								
						</tr>
                  </table>  
                  <p>&nbsp;</p>
                </div></td>
                   
            </tr>
          </table>
		  <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr> 
              <td height="45" width="215"> 
                <div align="center"> 
                  <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                  <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
                  <input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
                  <input type="hidden" name="idfactura" value="<? echo $factura;?>">
				  <input type="hidden" name="TxtInterno" value="<? echo $TxtInterno;?>">
				  <input type="hidden" name="Cbo_tema_factura" value="<? echo $Cbo_tema_factura;?>">
				  <input type="hidden" name="arreglo" >
				  <input type="hidden" name="accion" value="<? echo 1;?>">
                </div></td>
              <td width="170"><div align="center">
                  <!--input name="cmd_grabar" type="submit" class="botones" onClick="chequear_arreglo(<?php echo $nRows?>);validar_datos();" value="Grabar"-->
               <input name="cmd_grabar" type="submit" class="botones" onClick="validar_datos();" value="Grabar">
                </div></td>
              <td width="215">&nbsp;</td>
            </tr>
          </table>
      </td>
    </tr>
  </table>
  </form>
  
<?php
}

mssql_close($cn);
?>
</center>
</body>
</html>

