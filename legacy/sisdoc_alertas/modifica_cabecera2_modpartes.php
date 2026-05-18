<?php
include("conexion_bd.php");

// Modificado el 12/07/2003
$Usuario=$cusuario;
$xx= $idusuario;
$flujo1=$flujook;

$doc="exec busca_documento_mod '" . $iddocum . "'";
$rs_documento=mssql_query($doc);
$Totreg = mssql_num_rows($rs_documento);
//echo "flujo" . $flujook;

if($Totreg>0)
 {

	$reg_doc=mssql_fetch_array($rs_documento);
	$txtmateria=trim($reg_doc["materia"]);

	$fec_doc=strtotime($reg_doc["fecha_documento"]);
	$fech_doc=date("d-m-Y",$fec_doc);
	$fec_doc=strtotime($reg_doc["fecha_invitacion"]);
	$fech_inv=date("d-m-Y",$fec_inv);
	$fec_ofi=strtotime($reg_doc["fecha_num_oficial"]);
	$fech_ofi=date("d-m-Y",$fec_ofi);
	/*echo "fecha oficial 1 " . $reg_doc["fecha_num_oficial"]."<br>";
	echo "fecha 3  " . $fech_ofi . "<br>";
	echo "fecha oficial  2" . $fech_ofi . "<br>" ;
	*/
	if ($reg_doc["fecha_num_oficial"]==NULL)
	{ $fech_ofi =NULL; }
	$documento=$reg_doc["id_documento"];
	$qr_descriptor="SELECT b.id_descriptor  FROM descriptor a,descriptor_documento b where b.id_documento=". $documento	." and b.id_descriptor = a.id_descriptor	order by a.desc_descriptor ";
	$rs_descrip=mssql_query($qr_descriptor);
    
	$rs_servicio= mssql_query("SELECT * FROM descriptor order by desc_descriptor", $cn);
	$nRows = mssql_num_rows($rs_servicio);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario ingreso docto1</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/javascript">
<!--
var sw_ok;
var cont_arreglo;
var z=0;
var arreglo2 ="";
var ar_descrip =new Array();

  
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
function validar_datos()
{
sw_ok=true; 
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
</script>
<script language="JavaScript" type="text/JavaScript">
<!--
var flujo2= <?php echo $flujo1; ?>;  
function mensaje()
{ 
  var valor ="carga_desc";
  var registro=<?php echo $documento; ?>;
  parent.frames[0].location.href="frame_consultas.php? sw="+valor+ "&registro="+registro;  
  if (flujo2==1) {
  
  alert("El Documento ha sido Modificado");
  }
  	
  }
  function existe_exped()
// valida que exista en la tabla de expediente  //
  {
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
<!--div id="Layer4" style="position:absolute; left:647px; top:287px; width:170px; height:23px; z-index:3; visibility: visible;"> 
  <input name="bt_descriptor" type="button" class="botones" id="bt_descriptor" onClick="javascript:fnOpen_descriptor(document.form1.arreglo.value);" value="Descriptor">
</div-->

<center>
<!--form name="form1" method="Post" action="guardar_modificacion.php" -->
<form name="form1" method="Post" action="guardar_modif_numoficial.php" >
    <table width="657" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="651" height="45">
<div align="center"><font color="#FFFFFF" size="4"><strong>MODIFICACION NUMERO /FECHA OFICIAL  DE DOCUMENTOS </strong></font></div></td>
      </tr>
    </table>
    <table width="656" border="1" cellpadding="1" cellspacing="0" bgcolor="#E6EEFF">
      <tr> 
        <td width="656"  align="center"> 
          <table width="100%" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="320" class="texto"><strong><font color="#804040" face="Arial, Helvetica, sans-serif">IDENTIFICACION 
                DEL DOCUMENTO</font></strong></td>
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
                      <td width="82" height="38"><font color="#000000"><strong>Interno</strong></font></td>
                      <td height="38"><font color="#000000" face="Arial, Helvetica, sans-serif"><strong><?php echo $reg_doc["num_interno"];?></strong></font></td>
                      <td height="38" colspan="2">&nbsp;</td>
                    </tr>
                    <tr> 
                      <td width="82" height="38"><font color="#000000"><strong>Fecha 
                        Docto</strong></font></td>
                      <td width="116" height="46"><div align="left"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                         <strong><?php echo $fech_doc ;?></strong>
                          <!--input name="Txt_fecha_doc" type="text" class="entradas" id="Txt_fecha_doc2" value="<?php echo $fech_doc;?>" onBlur="chequeafecha(this,0)"  size="10" maxlength="10"-->
                          <!--a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="imagen/icon-calen_f2.gif" width="25" height="24" border="0" name="calenda"></a--> 
                          </font></div></td>
                      <td width="131" height="46"><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font><font color="#000000">&nbsp;</font></td>
                      <td width="301"><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font></td>
                        
                        <td width="156"><font color="#000000"><font color="#000000"><strong>Externo</strong><font size="2" face="Arial"> 
                       <strong><?php echo $reg_doc["num_externo"] ;?></strong>
                        <!--input name="TxtExterno" type="text" class="entradas" id="TxtExterno2" value="<?php echo $reg_doc["num_externo"];?>" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8"-->
                        </font></font><font size="2" face="Arial"> </font></font><font color="#000000">&nbsp;</font></td>
                      <td width="263"><strong>Expediente</strong> <font color="#000000" face="Arial, Helvetica, sans-serif">
                      <strong><?php echo $reg_doc["id_expediente"]; ?></strong><font size="2" face="Arial"> </font>
                        <!--input type="text" name="txtexped" value ="<?php echo  $reg_doc["id_expediente"]?>"  size="8" maxlength="10" onchange ="valida_digito(this.value,this,8);existe_exped();"-->
                        </font></td>

                    </tr>
                  </table>
                  <table width="100%" border="0" cellpadding="1" cellspacing="0">
            <tr>
                      <td width="82" height="38"><font color="#000000"><strong>N&uacute;meros</strong></font></td>
                    
               <td width="116"><font color="#000000"><strong>Oficial</strong><font size="4" face="Arial"> 
                        </font><font color="#000000"><font size="4" face="Arial"> 
                        <input name="TxtOficial" type="text" class="entradas" id="TxtOficial5" value="<?php echo $reg_doc["num_oficial"];?>" onBlur="valida_digito(this.value,this,8);" size="8" maxlength="8">
                        </font></font></font></td>
                     <td width="276" height="38" align="right"><font color="#000000"><strong>Fecha Oficial </strong></font></td>
                      <td width="216" height="46">
	       <div align="left"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                         <input name="Txt_fecha_ofi" type="text" class="entradas" id="Txt_fecha_ofi"  value=
						  "<?php    if ($reg_doc["fecha_num_oficial"]==NULL)	{echo $fech_ofi ;}
  						  	else {echo $fech_ofi ;}?>"
  						
						      onBlur="chequeafecha(this,0);" 	
  						       size="10" maxlength="10">
                          <a href="javascript:show_Calendario('form1.Txt_fecha_ofi');"><img src="imagen/icon-calen_f2.gif" width="25" height="24" border="0" name="calenda"></a> 
                          </font></div>
                          </td>
                       </tr>
                       </tr>
                  </table>
                  <table width="100%" border="0" cellpadding="1" cellspacing="0">
                  </table>
                  <table width="100%" border="0" valign="top" cellspacing="0" cellpadding="1">
                    <tr> 
                      <td width="80" height="46"><font color="#000000"><strong><font size="2">Materia</font></strong> 
                        </font></td>
                      <td width="263"><font color="#000000" size="2"> 
                       <textarea name="TxtMateria"    cols="90" rows="4" readonly=()  ><? echo $txtmateria ?></textarea>
                         </font>                  
                
                        <div id="Layer1" style="position:absolute; width:205px; height:122px; z-index:1; left: 583px; top: 108px; visibility: hidden; overflow: auto; background-color: #ECE9D8; layer-background-color: #ECE9D8; border: 1px none #000000;" class="texto"> 
                          <table width="100%" border="1" bgcolor="#E6EEFF">
						    <tr> 
                              <td height="23"> <div align="center" onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide');fecha_invitacion(<?php echo $nRows;?>)"><font color="#000000"><strong>Aceptar</strong></font></div></td>
                            </tr>
                            <tr> 
                              <td height="82"> 
		  <font face="Arial, Helvetica, sans-serif">&nbsp; 
		  </font><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; </font>
	               </td>
                            </tr>
                            <tr> 
                              <td height="23"> <!--div align="center" onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide');fecha_invitacion(<?php echo $nRows;?>)"><font color="#000000"><strong>Aceptar</strong></font></div>--</td-->
                            </tr>
                          </table>
                          <div align="right"></div>
                        </div>
                        <font color="#000000" size="2">&nbsp; </font>
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
                 <input type="hidden" name="iddocumento" value="<? echo $documento;?>">
	 <input type="hidden" name="TxtInterno" value="<? echo $TxtInterno;?>">
	 <input type="hidden" name="Cbo_Tipo_Docto" value="<? echo $Cbo_Tipo_Docto;?>">
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
/*
else
{
echo '<html><body><center> No se encontraron Datos';
}*/
mssql_close($cn);
?>
</center>
</body>
</html>

