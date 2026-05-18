<?php
include("conexion_bd.php");
include("carga_tablas.php");

global $Confidencial;
$cusuario='ximena';
$idusuario=3;
$idfuncionario=3;

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
<title>formulario busca tramites pendientes</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript" type="text/JavaScript">
<!--
var sw_ok;
var cont_arreglo;
var z=0;
var arreglo2 ="";
//var ar_descrip =new Array();
//var flujo2= <?php echo $flujo1; ?>;  
//var sw= <?php echo $cons; ?>;
/*function mensaje() { 
  if (flujo2==1) {
  
  alert("No existen Registros");
  }
}*/

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
                <input name="fecha_ini" type="text" class="entradas" id="fecha_ini"   value ="<? echo $fecha_x?>" size="10" maxlength="10">
                </font></td>
              <td>Fecha Termino</td>
              <td><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                <input name="fecha_fin" type="text" class="entradas" id="fecha_fin"   size="10" maxlength="10">
                </font></td>
            </tr>
            <tr> 
              <td width="23%" height="21">&nbsp;</td>
              <td width="30%"><strong><font color="#000000" size="1" face="Times New Roman, Times, serif, Monotype Corsiva">Formato 
                dd-mm-aaaa </font></strong></td>
              <td width="16%">&nbsp;</td>
              <td width="31%"><strong><font color="#000000" size="1" face="Times New Roman, Times, serif, Monotype Corsiva">Formato 
                dd-mm-aaaa </font></strong></td>
            </tr>
          </table>
          <!--table width="100%" border="0">
            <tr> 
              <td width="13%" height="26"><font color="#000000">Fecha Inicial</font></td>
              <td width="37%"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                <input name="fecha_ini" type="text" class="entradas" id="fecha_ini" value="<?=$fecha_x?>" size="10" maxlength="10">
                <a href="javascript:show_Calendario('form1.fecha_ini');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                </font></td>
              <td width="22%">Fecha Final</td>
              <td width="28%"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                <input name="fecha_fin" type="text" class="entradas" id="fecha_fin" value="<?=$fecha_x?>" size="10" maxlength="10">
                <a href="javascript:show_Calendario('form1.fecha_fin');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                </font></td>
            </tr>
          </table-->

          <!--table width="648" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="321"><font color="#804040"><strong>ORIGEN</strong></font></td>
              <td width="320"><font color="#804040"><strong>DESTINO</strong></font></td>
            </tr>
          </table-->
          <!--table width="640" border="1" cellspacing="0" cellpadding="1">
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
                      <select name="Cbo_Func_Destino" class="combo" id="select" onChange="javascript:cambio3();">> 
                        <option value="0"> </option>
                      </select>
                      </font></td>
                  </tr>
                </table></td>
            </tr>
          </table-->
          <p>&nbsp;</p><table width="613" border="0">
            <tr> 
              <td width="167" height="41"> 
                <div align="center"> 
                  <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                  <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
                  <input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
                  <!--<input type="hidden" name="numinterno" >
                  <input type="hidden" name="numoficial" >
                  <input type="hidden" name="numexterno" >
                  <input type="hidden" name="arreglo" >
                  <input type="hidden" name="val_procedencia" >
                  <input type="hidden" name="val_funcionario" >
                  <input type="hidden" name="tipo_procedencia" >
                  <input type="hidden" name="val_funcionario1" >
                  <input type="hidden" name="val_destino" >
                  <input type="hidden" name="tipo_destino" >-->
                </div></td>
              <td width="251"> <div align="center"> 
              <!-- <input type="submit" name="Submit" value="Buscar" onClick="chequear_arreglo(<?php echo $nRows?>);ver();">-->
               <input type="submit" name="Submit" value="Buscar" ;">
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
