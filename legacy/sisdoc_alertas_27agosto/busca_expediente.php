<?php
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
/*$cusuario='ximena';
$idusuario=3;
$idfuncionario=3;*/


$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;
$sw_cons=0;
$cons=$sw_cons;

// la variable menu viene con 1 para documentos de departamentos y 2 para oficina de partes //

//echo "funcionario  " . $sw_cons . " *** cusuario  " . $cusuario . "**** xx  " . $idusuario;
$flujook=8 ;
$flujo1=$flujook;

if ($flujook==8){
$num_int=0;}
else{
$num_int=$num_int;}

$Cbo_Estado_Docto=1;
$fecha1 =date("02-01-Y");
$fecha2=date("d-m-Y");
//$fecha_x = date("d-m-Y");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario ingreso docto1</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript" type="text/JavaScript">
<!--
var sw_ok;
var cont_arreglo;
var z=0;
var arreglo2 ="";
var ar_descrip =new Array();
//var flujo2= <?php echo $flujo1; ?>;  
var sw= <?php echo $cons; ?>;
function mensaje() { 

  /*alert ("flujo2"+$flujo2);
  if (flujo2==1) {
  
  alert("No existen Registros");
  }*/
}


function ver()
{

if(sw==0)
{}
else
{
//alert("exp_enc");

document.form1.action="expedientes.php" ;
/*document.form1.action="exp_enc.php" ;*/
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

<body bgcolor="#FFFFFF" onload="mensaje()">
<center>
<form name="form1" method="Post" action="expedientes.php">
    <table width="656" height="31" border="0">
      <tr> 
        <td width="586" height="27"></td>
         <td width="60"><div align="right"><strong><font color="#0000A0" size="2">
		 <?  
		 if ($menu ==1 )
		 {
		  echo "<a href=\"ingreso_docto2.php?cusuario=".$cusuario."&idusuario=".$idusuario ."&idfuncionario=".$idfuncionario."&flujook=". 8 ."\">".Volver."</a>";?></font></strong></div></td>
		<? }
		else 
		 {	
		  echo  "<a href=\"ingreso_ofpartes_k.php?cusuario=".$cusuario."&idusuario=".$idusuario ."&idfuncionario=".$idfuncionario."&flujook=". 8 ."\">".Volver."</a>";?></font></strong></div></td>
		  <? }?> 
		  
		<!--div align="right"><strong><font color="#0000A0" size="1"><strong> <?echo "Usuario : " . $cusuario?></strong></font></strong> 
        </div-->
		
      </tr>
    </table>
    <table width="656" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="650"><div align="center"><font color="#FFFFFF" size="4"><strong>BUSQUEDA 
            EXPEDIENTES </strong></font></div></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#E6EEFF">
      <tr> 
        <td width="665"  align="center"> 
          <table width="100%" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="320" class="texto"><strong><font color="#804040">Ingrese 
                los campos por los que desee buscar</font></strong></td>
            <td width="322">
            <div align="right"><strong><font color="#0000A0" size="1">
            </font><font color="#0000A0" size="2"> 
            </font></strong>
            </div></td>
          </tr>
        </table>
          <table width="650" height="114" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="658" height="112" align="center"> 
                <div align="center"> 
                  <table width="100%" border="0">
                    <tr> 
                      <td width="20%"><font color="#000000">Fecha inicial</font></td>
                      <td width="30%"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_ini" type="text" class="entradas" id="Txt_fecha_ini" value="<?=$fecha1?>" size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_ini');">
                        <img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda">
                        </a> 
                        </font></td>
                      <td width="15%">Fecha Final</td>
                    <td width="35%"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                      <input name="Txt_fecha_fin" type="text" class="entradas" id="Txt_fecha_doc3" value="<?=$fecha2?>" size="10" maxlength="10">
                       <a href="javascript:show_Calendario('form1.Txt_fecha_fin');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                        </font></td>
                    </tr>
                  </table>
                  <table width="100%" border="0" valign="top" cellspacing="2" cellpadding="2">
                    <tr> 
                      <td width="122"><font color="#000000" size="2">Descripcion</font></td>
                      <td width="508"><font color="#000000" size="2"> 
                        <input name="txtdesc" Type="text" class="entradas"  size="40" maxlength="80">
                        </font> <font color="#000000" size="2">&nbsp; </font></td>
                    </tr>
                  </table>
                </div></td>
            </tr>
          </table>
		  <table width="640" border="0">
            <tr> 
              <td width="213">
<div align="center"> 
                  <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                  <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
                  <input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
                  <input type="hidden" name="arreglo" >
                  <input type="hidden" name="sw_cons" value="<? echo $cons;?>">
				  <input type="hidden" name="menu" >
                  
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
