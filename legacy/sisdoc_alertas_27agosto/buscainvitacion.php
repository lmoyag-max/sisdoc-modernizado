<?php
include("variables.php");
include("conexion_bd.php");
include("funciones.php");
// busca las invitaciones que han llegado a un departamento y que la invitación sea para el rango de dias que se ingres apor pantalla 
//si es usuario de oficina de partes muestra todas las que han llegado , en caso de ser otro usuario sólo 
// despliega los que han llegado a las dependencias que el tiene acceso. 
$usuario=$cusuario;
$xx = $idusuario;
$fun=$idfuncionario;
if($sw_fecha==0)
{
$fechai =date("02-01-Y");
$fechaf=date("d-m-Y");
}
else
{
$fechai =date("02-01-Y");
$fechaf=date("d-m-Y");
}
?>
<html>
<head>
<META Http-Equiv="Cache-Control" Content="no-cache">
<META Http-Equiv="Pragma" Content="no-cache">
<META Http-Equiv="Expires" Content="0"> 
<title>Busqueda de Invitaciones por Fecha</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript">
<!--
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
// -->

function MM_findObj(n, d) { //v4.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && document.getElementById) x=document.getElementById(n); return x;
}

function MM_showHideLayers() { //v3.0
  var a,i,p,v,obj,args=MM_showHideLayers.arguments;
   ocultalayer(args[3],args[4]);
  for (i=0; i<(args.length-4); i+=3) 
  if ((obj=MM_findObj(args[i]))!=null) 
      { v=args[i+2];
    if (obj.style) 
	    { obj=obj.style; v=(v=='show')?'visible':(v='hide')?'hidden':v; }
    obj.visibility=v; }		
  }
//-->
function ocultalayer(idlay,totlay){
var idlay, a;

	for (a=1; (a<=totlay); a++){
		nomlay = "layer" + a;
		document.all[nomlay].style.visibility="hidden";
		//	queda pendiente esta consulta --	if (navigator.appName == "Microsoft Internet Explorer") 
			
         }
	}
	
	
</script>

<script language="JavaScript">
<!--

//-->

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





function imp_invitacion()
{
  document.formulario2.action="imp_invitacion.php";
    document.formulario2.submit();
}
function buscar()
{
	 document.formulario1.action="buscainvitacion.php"
  	 document.formulario1.submit();
  
 }

 
</script>
<script src="js/calendario.js"></script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
<STYLE type=text/css>

A { COLOR: blue; FONT-FAMILY: verdana,arial,helvetica,sans-serif; FONT-SIZE: 11px; TEXT-DECORATION: none
}

A:hover { COLOR: red; TEXT-DECORATION: none
}
</style>
</head>
<body   bgcolor="#FFFFFF" text="#000000" topmargin="0"  >
<center >
  
    <form name="formulario1" method="post" >
    <table width="650" border="1" cellpadding="2" cellspacing="0" bgcolor="#3399FF">
      <tr> 
        <td> <p align="center"><b><font size="4" color="#FFFFFF">BUSQUEDA DE INVITACIONES</font></b></p></td>
      </tr>
    </table>
    <table width="650" border="0">
      <tr> 
        <td width="89"><div align="left"><strong>Fecha Inicio</strong></div></td>
        <td width="97"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
          <input name="fecha_ini" type="text" class="entradas" id="fecha_ini" value="<?php echo $fechai; ?>" onBlur="chequeafecha(this,0)" size="8" maxlength="10">
          <a href="javascript:show_Calendario('formulario1.fecha_ini');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
          </font></td>
        <td width="111"><div align="right"><strong>Fecha Termino</strong></div></td>
        <td width="97"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
          <input name="fecha_fin" type="text" class="entradas" id="fecha_fin" value="<?php echo $fechaf; ?>" onBlur="chequeafecha(this,0)" size="8" maxlength="10">
          <a href="javascript:show_Calendario('formulario1.fecha_fin');"><img src="imagen/icon-calen_f2.gif" width="25" height="19" border="0" name="calenda"></a> 
          </font></td>
        <td width="101"> <div align="center"><font size="2" face="Arial"> 
            <input name="cmd_busca" type="button" class="botones" onClick="buscar();" value="Buscar">
            </font></div></td>
        <td width="144"><div align="right"><font size="2" face="Arial">
            <input type="hidden" name="idusuario" value="<? echo $xx;?>">
            <input type="hidden" name="cusuario" value="<? echo $usuario;?>">
            <input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
            <input type="hidden" name="sw_fecha" value="<? echo 1;?>">
            </font><font color="#0000A0"><strong><?php echo "Usuario: " . $cusuario; ?></strong></font></div></td>
      </tr>
    </table>
    
   
    <br>
   
 
  </form>	    
  
</center>  

</body>
</html>
<? //if ($txtnomina != 0){ 
// permite cargar el detalle de la nomina que se desea buscar // 
if ($sw_fecha==0)   // permite dejar para la primera entrada que  muestre las invitaciones entre ese rango 02/01año actual  a a la fecha de hoy 
   { $fecha_ini =$fechai; $fecha_fin =$fechafin;}
   
$dia = substr($fecha_ini,0,2);
$mes = substr($fecha_ini,3,2);
$año = substr($fecha_ini,6,4);
$fechaini = date("Y/m/d H:i", mktime(0,0,0, $mes, $dia, $año));
$dia = substr($fecha_fin,0,2);
$mes = substr($fecha_fin,3,2);
$año = substr($fecha_fin,6,4);
$fechafin = date("Y/m/d H:i", mktime(23,59,59, $mes, $dia, $año));


$rs_doc="exec buscainvitacion " . $xx . ",'" . $fechaini . "','" . $fechafin . "'";


$rs_documento=mssql_query($rs_doc);   
echo "invitaciones " . $rs_doc;
$Totreg = mssql_num_rows($rs_documento);
$NumPag= intval($Totreg/10);
if(fmod($Totreg,10)==0) 
  { 
  $NumPag = $NumPag;
  }
else
  {
  $NumPag=$NumPag + 1;
  }
if ($Totreg ==0 && $sw_fecha !=0) 
{
 
     echo '<script>' ; 
	 echo 'alert("No hay Invitaciones dentro de este Rango de Fechas")';
	 echo '</script>' ; 

 }
 else {  
 if($Totreg !=0)
 {
 

 ?>
 <body   bgcolor="#FFFFFF" text="#000000" topmargin="0" >
<center >
 <form name="formulario2" method="post"  >
    
    <table width="650" border="0">
      <tr>
        <td><div align="left"><strong><?php echo "Total de Páginas : " . $NumPag ?></strong></div></td>
        <td><div align="right"><strong><?php echo "Total Registros : " . $Totreg ?></strong></div></td>
      </tr>
    </table>
    <table width="650" border="0">
      <tr>
        <td width="580">
          <?php
		  echo "<div align='left'><b>";
     		        for ($i = 1; $i <= $NumPag; $i++)
			 {
			
		 echo "<img src='botones/boton" . $i . ".gif' width='44' height='16'". 
 "onClick=\"MM_showHideLayers('layer" . $i . "','','show',$i, $NumPag)\">"; 
            
			 } 
			 echo "</b></div>";
		    ?>
        </td>
		
		<td width="70"> <input name="cmd_imp" type="button" class="botones" onClick="imp_invitacion();" value="Imprimir"></td>
      </tr>
    </table>
   
    <!--p><font size="2"><?php echo $reg_documento["desc_tipo_documento"];?></font></p-->
	  
    <?php 
	      $Corre = 0;
		  $NumLayer = 0;
		  while($reg_documento = mssql_fetch_array($rs_documento)) { 
		  
		  if(fmod($Corre,10)==0) 
		  { 
		  $NumLayer = $NumLayer + 1;
		  if($NumLayer==1){
  		 echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:130px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: visible">';
		   }
		   else
		   {
		 echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:130px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: hidden">';
		   }
		   
		  
 
	echo "<table width='650' border='1' cellpadding='1' cellspacing='0' bgcolor='#E6EEFF'>"; 
	
	echo '<tr bgcolor="#6699FF">';
    echo '<td width="5%" height="33"><strong><font color="#FFFFFF" size="2">Num</font></strong></td>';
	 echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha Invitación</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Interno</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Oficial</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Tipo Documento</font></strong></td>';
    echo '<td width="100%" height="33"><strong><font color="#FFFFFF" size="2">Materia</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Procedencia</font></strong></td>';
    //echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Funcionario</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha Despacho</font></strong></td>';
	echo '</tr>';
		 	 
		  }
		  $Corre =  $Corre + 1;
		  ?>
    
	<td><?php echo $Corre;?></font></td>
	
         <td align="left" valign="middle" width="8%"><font size="2">
          <?php $fec_rec=strtotime($reg_documento["fecha_invitacion"]);
		        $fech_rec=date("d/m/Y",$fec_rec);
				echo $fech_rec;?></font></td>
	   </td>
        <td align="left" valign="middle" width="8%"><font size="2">
          <?php echo $reg_documento["num_interno"];?></font>
        </td>
        <td align="left" valign="middle" width="8%"><font size="2">
          <?php echo $reg_documento["num_oficial"];?></font>
        </td>
		
	    
      <td align="left" valign="middle" width="8%"><font size="2"><?php echo $reg_documento["desc_tipo_documento"];?> </font> </td>
		
      <td align="left" valign="middle" width="100%"><font size="2"> 
        <?php if ($reg_documento["materia"]=="")
		           echo "&nbsp";
				   else echo $reg_documento["materia"];?>
        </font> </td>
       
        <td align="left" valign="middle" width="20%"><font size="2">
          <?php echo $reg_documento["procedencia"];?></font>
        </td>
        <td align="left" valign="middle" width="8%"><font size="2">
          <?php $fec_rec=strtotime($reg_documento["fecha_despacho"]);
		        $fech_rec=date("d/m/Y",$fec_rec);
				echo $fech_rec;?></font></td>

    </tr>
     <?php if(fmod($Corre,10)==0) { 
	 echo "</table>";
	 echo "</div>";  } ?>
    <?php } ?></table>
    </div> 
	<!--?php echo $NumLayer ?-->
	<p>&nbsp;</p>
    <p>&nbsp;</p>
    <table width="650"  border="0">
      <tr> 
        <td height="23" > 
          <div align="left"></div>
          <div align="left"> 
            <input type="hidden" name="Totreg2" value="<?php echo $Totreg; ?>">
            <input type="hidden" name="NumLayer2" value="<?php echo $NumLayer; ?>">
            <input type="hidden" name="idusuario" value="<? echo $xx;?>">
            <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
			<input type="hidden" name="idfuncionario" value="<? echo $idfuncionario;?>">
			<input type="hidden" name="fecha_ini" value="<? echo $fecha_ini;?>">
            <input type="hidden" name="fecha_fin" value="<? echo $fecha_fin;?>">
            
          </div></td>
      </tr>
    </table>
    <br>
<? } 
   }
   ?>
	</form>	    
	</center>
	
	</body>

