<?php
include("conexion_bd.php");

$ok1=$ok;
$cusuario=$cusuario;
$txtnombresol=$nombre;
$txtrutsol =$rut;
if(!isset($txtrutsol) && $ok1==0)
{
echo '<script language="javascript"> location.href="ingreso_mov.php"; </script>';
}
$rs_ocu="exec busca_ocupantes '" . $solicitud . "'";
$rs_ocupantes=mssql_query($rs_ocu);  
$rs_ve="exec busca_vehiculo ";
$rs_vehiculo=mssql_query($rs_ve);  


$rs_con="exec busca_conductor " ;
$rs_conductor=mssql_query($rs_con);  

$cc = mssql_connect("bd-minsal", "sa", "sqlminsal") or die("El Servidor No se encuentra");
	mssql_select_db("intranet");
$rs_procedencia = mssql_query("select * from departamentos order by nom_dep",$cc);
$rs_ccosto = mssql_query("select * from departamentos order by nom_dep",$cc);
$fecha_x = date("d-m-Y");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario ingreso</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript" type="text/javascript">
var sw_ok;
var cont_arreglo;
var z=0;
var arreglo2 ="";
var numint= <?php echo $num_int; ?>;  
var flujo2= <?php echo $ok1; ?>;  


if (parseInt(navigator.appVersion) > 3) {
	if (navigator.appName == "Netscape") {
		layerVar="document.layers";
		styleVar="";
	}
	else
	{
		layerVar="document.all";
		styleVar=".style";
	}
}
function imprime()
{
	MuestraEsconde('impresion','hidden');
  	window.print();
  	MuestraEsconde('impresion','visible');
	limpiar_formulario();
	
}

function limpiar_formulario()
{
document.form1.txtsolicitud.value="";
document.form1.txthorasol.value="";
document.form1.txtminutos.value="";
document.form1.txtduracion.value="";
document.form1.txtdestino.value="";
document.form1.txtcometido.value="";
document.form1.txtnombre1.value="";
document.form1.txtnombre2.value="";
document.form1.txtnombre3.value="";
document.form1.txtnombre4.value="";
document.form1.Cbo_vehiculo.value=0;
document.form1.Cbo_Conductor.value=0;
document.form1.txtpatente.value="";
document.form1.radio1[0].checked=true;
document.form1.cmd_grabar2.disabled=false;
}


function pasar_datos()
{
var valor="P";
var valor="P";
parent.frames[0].location.href="frame_consultas.php?sw="+valor+
"&txtsolicitud="+0+
"&cusuario="+document.form1.cusuario.value+
"&nombre="+document.form1.nombre.value+
"&txthorasol="+document.form1.txthorasol.value+
"&Txt_fecha_doc="+document.form1.Txt_fecha_doc.value+
"&txtminutos="+document.form1.txtminutos.value+
"&txtduracion="+document.form1.txtduracion.value+
"&Cbo_Procedencia="+document.form1.Cbo_Procedencia.value+
"&txtdestino="+document.form1.txtdestino.value+
"&txtcometido="+document.form1.txtcometido.value+
"&txtpatente="+document.form1.txtpatente.value+
"&arreglo="+document.form1.arreglo.value+
"&estado="+document.form1.estado.value+
"&Cbo_vehiculo="+document.form1.Cbo_vehiculo.value+
"&Cbo_Conductor="+document.form1.Cbo_Conductor.value;
}

function vehiculo()
{
	var selindice, nuevalsel,nomsel;
	var valor="K";
	selindice = document.form1.Cbo_vehiculo.selectedIndex;
	nuevasel = document.form1.Cbo_vehiculo.options[selindice].value;
	nomsel = document.form1.Cbo_vehiculo.options[selindice].text;
	document.form1.vehi.value=nomsel;
	parent.frames[0].location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
	}

function conductor()
{
var selindice, nuevalsel;
	selindice = document.form1.Cbo_Conductor.selectedIndex;
	nuevasel = document.form1.Cbo_Conductor.options[selindice].text;
	document.form1.cond.value=nuevasel;	}

function centro_costo()
{
var selindice, nuevalsel;
	selindice = document.form1.Cbo_ccosto.selectedIndex;
	nuevasel = document.form1.Cbo_ccosto.options[selindice].text;
	document.form1.ccosto.value=nuevasel;	}

function bloquea()
{
	document.form1.Cbo_vehiculo.disabled=true;
	document.form1.Cbo_Conductor.disabled=true;
	document.form1.txtpatente.disabled=true;
}
function desbloquea()
{
	document.form1.Cbo_vehiculo.disabled=false;
	document.form1.Cbo_Conductor.disabled=false;
	document.form1.txtpatente.disabled=false;
}

 function busca_nombre_ant(r,d,pos)
{
var valor=pos;
var rut = r;
var dv=d;
parent.frames[0].location.href="frame_consultas.php?rut="+rut+"&sw="+valor+"&dv="+dv;
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

 function busca_nombre(nom,filas) 
{
       
	   document.form1.arreglo.value=document.form1.arreglo.value+nom + "@" ;
		z=z+1
	   
  }

// Valida los datos antes de grabar en las tablas
function validar_datos()
{
	sw_ok=true;
 if(document.form1.Txt_fecha_doc.value == "")
  {
 	sw_ok=false;
	alert("Falta Ingresar la Fecha de Solicitud");
	document.form1.Txt_fecha_doc.focus();
  }
else
 if(document.form1.txthorasol.value == "")
  {
 	sw_ok=false;
	alert("Falta Ingresar la Hora de Solicitud");
	document.form1.txthorasol.focus();
  }
else
  if(document.form1.txthorasol2.value == "")
  {
 	sw_ok=false;
	alert("Falta Ingresar la Duración de la Solicitud");
	document.form1.txthorasol2.focus();
  }
 else
  if(document.form1.txtdestino.value == "")
  {
 	sw_ok=false;
	alert("Falta Ingresar el destino de la Solicitud");
	document.form1.txtdestino.focus();
  }
 else
  if(document.form1.txtcometido.value == "")
  {
 	sw_ok=false;
	alert("Falta Ingresar el cometido de la Solicitud");
	document.form1.txtcometido.focus();
  }
  
if (sw_ok)
{
	document.form1.arreglo.value=z+"@" +document.form1.arreglo.value ;
  if(document.form1.radio1[0].checked==true)
	{
   	document.form1.estado.value="A";}
	else
	if(document.form1.radio1[1].checked==true)
	{
   	document.form1.estado.value="R";
	}  
	pasar_datos();
		
}
} 

function CheckLength(length) {
if (window.event.srcElement.value.length >= length) {
   alert('El Máximo de caracteres es  250');
   return false; }          
}
 
function despachar_datos() 
{
	document.form1.action="multi_pages.php";
   	document.form1.submit();
} 
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

function MuestraEsconde(LaLayer,ElAtributo) {
	if (parseInt(navigator.appVersion) > 3) {
		eval(layerVar + '["' + LaLayer + '"]' + styleVar + '.visibility = "' + ElAtributo + '"');
	}
}

  
  
//-->
</script>
<script src="js/calendario.js"></script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body bgcolor="#FFFFFF" >
<center>
<form name="form1" >
    <table width="650" height="30" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="620"> 
          <div align="center"><font color="#FFFFFF" size="4"><strong>SOLICITUD 
            DE VEHICULO</strong></font></div></td>
      </tr>
    </table>
    <table width="650" height="736" border="1" cellpadding="1" cellspacing="0" bgcolor="#E3EDF2">
      <tr> 
        <td width="656" align="center"> 
          <table width="610" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="320" class="texto"><strong><font color="#822015">DATOS 
                DE LA SOLICITUD</font></strong></td>
              <td width="322"><div align="right"><strong><font color="#0000A0" size="2"> 
                  </font></strong></div></td>
            </tr>
          </table>
          <table width="93%" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="650" height="169" align="center"> <div align="center"> 
                  <table width="610" border="0" cellpadding="1" cellspacing="1">
                    <tr> 
                      <td width="126" height="32">N&ordm; de Solicitud</td>
                      <td width="115"><strong>
                        <input name="txtsolicitud" type="text" disabled="true" id="txtsolicitud" size="10" maxlength="10">
                        </strong> </td>
                      <td width="70">&nbsp;</td>
                      <td width="297"><strong></strong></td>
                    </tr>
                  </table>
                  <table width="610" border="0" cellpadding="1" cellspacing="1">
                    <tr> 
                      <td width="126" height="32">Solicitado por Rut</td>
                      <td width="115"><strong><font color="#0000A0" size="2"><? echo $txtrutsol?></font></strong> 
                      </td>
                      <td width="70">Nombre</td>
                      <td width="297"><strong><font color="#0000A0" size="2"><? echo $nombre?></font></strong></td>
                    </tr>
                  </table>
                  <table width="610" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="128"><font color="#000000">Fecha Salida</font></td>
                      <td width="185"> <font color="#000000" face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_doc" type="text" class="entradas" id="Txt_fecha_doc3" value="<?=$fecha_x?>" size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="imagen/icon-calen_f2.gif" width="25" height="20" border="0" name="calenda"></a> 
                        </font><font color="#000000">&nbsp; </font></td>
                      <td width="297"> <div align="left"><font color="#000000">Hora</font><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                          <input name="txthorasol" type="text" id="txthorasol2" size="2" maxlength="2">
                          <strong>:</strong> 
                          <input name="txtminutos" type="text" id="txthorasol32" size="2" maxlength="2">
                          </font></div></td>
                    </tr>
                  </table>
                  <table width="610" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="128"><font color="#000000">Duraci&oacute;n estimada&nbsp;&nbsp;</font></td>
                      <td width="482"><font color="#000000" face="Arial, Helvetica, sans-serif"> 
                        <input name="txtduracion" type="text" id="txtduracion" size="20" maxlength="20">
                        </font></td>
                    </tr>
                    <tr> 
                      <td width="128">Centro de Costo<font face="Arial">&nbsp;</font></td>
                      <td width="482"><font face="Arial"> 
                        <select name="Cbo_Procedencia" class="combo" id="select4" >
                          <option value="0"> </option>
                          <?php             
		    while($reg_procedencia=mssql_fetch_array($rs_procedencia))
			{
			    echo "<option value=" . $reg_procedencia[cod_dep];
				if($reg_procedencia[cod_dep]==$dependencia) echo " SELECTED";
				echo ">" . $reg_procedencia[nom_dep] . "</option>\n";
            }
			?>
                        </select>
                        </font></td>
                    </tr>
                  </table>
                  <table width="610" border="0" cellpadding="1" cellspacing="1">
                    <tr> 
                      <td width="128"><font color="#000000">Destino</font><font color="#000000">&nbsp;</font><font color="#000000"><font size="4" face="Arial"> 
                        </font></font></td>
                      <td width="482"><font color="#000000" size="2"> 
                        <textarea name="txtdestino"  cols="50" rows="3" class="cajatexto" id="txtdestino" onKeyPress="return CheckLength(250)"></textarea>
                        </font></td>
                    </tr>
                  </table>
                  <table width="610" border="0" valign="top" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="128"><font color="#000000">Cometido&nbsp;</font></td>
                      <td width="482"><font color="#000000" size="2"> 
                        <textarea name="txtcometido"  cols="50" rows="3" class="cajatexto" id="txtcometido"  onKeyPress="return CheckLength(250)"></textarea>
                        </font> <font color="#000000" size="2">&nbsp; </font></td>
                    </tr>
                  </table>
                </div></td>
            </tr>
          </table>
          <table width="614" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="610" class="texto"><font color="#822015"><strong>OCUPANTES 
                DEL MOVIL</strong></font></td>
            </tr>
          </table>
          <table width="614" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td><font color="#822015"><strong>Nombres</strong>&nbsp;</font></td>
            </tr>
          </table>
          <table width="614" border="1" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="75%"><table width="438" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="26">1</td>
                    <td width="405"> <input name="txtnombre1" type="text" id="txtnombre1" size="50" maxlength="50" onChange="javascript:busca_nombre(document.form1.txtnombre1.value,1);"></td>
                  </tr>
                  <tr> 
                    <td>2</td>
                    <td><input name="txtnombre2" type="text" id="txtnombre2" size="50" maxlength="50"  onChange="javascript:busca_nombre(document.form1.txtnombre2.value,2);"></td>
                  </tr>
                  <tr> 
                    <td>3</td>
                    <td><input name="txtnombre3" type="text" id="txtnombre3" size="50" maxlength="50"  onChange="javascript:busca_nombre(document.form1.txtnombre3.value,3);"></td>
                  </tr>
                  <tr> 
                    <td>4</td>
                    <td><input name="txtnombre4" type="text" id="txtnombre4" size="50" maxlength="50"  onChange="javascript:busca_nombre(document.form1.txtnombre4.value,4);"></td>
                  </tr>
                </table></td>
            </tr>
          </table>
          <table width="614" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="610" class="texto"><font color="#822015"><strong>RESPUESTA</strong></font></td>
            </tr>
          </table>
          <table width="614" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="650" height="162" align="center"> 
                <div align="center"> 
                  <table width="610" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="120" height="32">Rut Encargado</td>
                      <td width="120"><strong><font color="#0000A0" size="2"><? echo $txtrutsol;?></font></strong> 
                      </td>
                      <td width="100">Nombre</td>
                      <td width="310"><strong><font color="#0000A0" size="2"><? echo $nombre;?></font></strong></td>
                    </tr>
                  </table>
                  <table width="610" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td height="29">Aprobada 
                        <input name="radio1" type="radio" value="1" checked Onclick="desbloquea();">
                        Rechazada 
                        <input type="radio" name="radio1" Onclick="bloquea();"> 
                        <font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font><font color="#000000">&nbsp; </font><font face="Arial">&nbsp; 
                        </font></td>
                    </tr>
                  </table>
                  <table width="610" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="118" height="38"><font color="#000000">Veh&iacute;culo</font></td>
                      <td width="231"><font face="Arial"> 
                        <select name="Cbo_vehiculo" onChange="javascript:vehiculo();">
                          <option value="0"> </option>
                          <?php             
		    while($reg_veh=mssql_fetch_array($rs_vehiculo))
			{
			    echo "<option value=" . $reg_veh[id_vehiculo];
				//if($reg_vehiculo[id_vehiculo]==$dependencia) echo " SELECTED";
				echo ">" . $reg_veh[mod_vehiculo] . "</option>\n";
            }
			?>
                        </select>
                        </font><font color="#000000" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font></td>
                      <td width="47">Patente</td>
                      <td width="241"><strong> 
                        <input name="txtpatente" type="text" id="txtpatente" size="10">
                        </strong></td>
                    </tr>
                    <tr> 
                      <td width="118">Conductor<font face="Arial">&nbsp; </font></td>
                      <td colspan="3"><font face="Arial"> 
                        <select name="Cbo_Conductor" class="combo" id="select" onChange="javascript:conductor();">
                          <option value="0"> </option>
                          <?php             
		    while($reg_con=mssql_fetch_array($rs_conductor))
			{
			    echo "<option value=" . $reg_con[rut_conductor];
			//	if($reg_conductor[cod_dep]==$dependencia) echo " SELECTED";
				echo ">" . $reg_con[nombre_conductor] . "</option>\n";
            }
			?>
                        </select>
                        </font></td>
                    </tr>
                  </table>
                </div></td>
            </tr>
          </table>
          <table width="610" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr> 
              <td height="37" width="206"> <div align="center"> 
                  <input type="hidden" name="estado" value="<? echo "S";?>">
                  <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
                  <input type="hidden" name="nombre" value="<? echo $nombre;?>">
                  <input type="hidden" name="num_int" value="<? echo $num_int;?>">
                  <input type="hidden" name="arreglo">
				  <input type="hidden" name="rut_encargado" value="<? echo $rut_encargado;?>">
                  <input type="hidden" name="cond">
                  <input type="hidden" name="vehi">
 				  <input type="hidden" name="ccosto">
				  <input type="hidden" name="numero">
				  <input type="hidden" name="txtrutsol">
				  <input type="hidden" name="ok" value="<? echo $ok;?>">
                </div></td>
              <td width="262"><div align="center"> </div></td>
              <td width="142"><div id="impresion" style="position:absolute; z-index:1; visibility: visible; overflow: auto; background-color: #E3EDF2; layer-background-color: #E3EDF2; left: 298px; top: 725px; width: 211px; height: 32px; border: 1px none #000000;"> 
                  <table width="200">
                    <tr> 
                      <td height="26" align="right" bgcolor="#E3EDF2"> 
                        <div align="center"> 
                          <input name="cmd_grabar2" type="button" class="botones" onClick="validar_datos();" value="Guardar">
                        </div></td>
                      <td align="right" bgcolor="#E3EDF2"><input name="cmd_imprimir" type="button" class="botones" onClick="imprime();" value="Imprimir">
                      </td>
                    </tr>
                  </table>
                </div></td>
            </tr>
          </table></td>
      </tr>
    </table>
  </form>
  
  <?php mssql_close($cn);?>
</center>
</body>
</html>
