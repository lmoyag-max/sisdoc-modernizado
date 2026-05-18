<?php
include("conexion_bd2.php");
$ok2=$flujook;
$rut_c=$rut_c;
$rut_fun=$rut_fun;
$nom_fun=$nom_fun;
$mat_fun=$mat_fun;
$pat_fun=$pat_fun;
$car_fun=$car_fun;
$reg_fi=$region;
$dir_fun=$dir_fun;
$dependencia=$dep_fun;
$gra_fun=$gra_fun;
$ane_fun=$ane_fun;
$est_fun=$est_fun;
//echo "rut " . $rut_c . "rutfun " . $rut_fun; 

$rs_region= mssql_query("select * from regiones order by reg_descripcion",$cn);
$rs_comuna= mssql_query("select * from comunas order by com_descripcion",$cn);
$rs_cargo= mssql_query("select * from cargos order by desc_cargo",$cn);
$rs_dependencia= mssql_query("select * from dependencia order by desc_larga_dep",$cn);
$rs_estamento= mssql_query("select * from estamento order by desc_estamento",$cn);

$cc = mssql_connect("bd2-minsal", "bienes", "bienes2004") or die("El Servidor No se encuentra");
	mssql_select_db("bienestar");
//$rs_procedencia = mssql_query("select * from departamentos order by nom_dep",$cc);
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
var flujo2= <?php echo $ok2; ?>;  

function muestra_cuadro()
 { 

   if (flujo2==0 || flujo2==1)
   {
    document.form1.cmd_carga.disabled=false;
	document.form1.cmd_envia.disabled=false;
	document.form1.cmd_imprimir.disabled=false;
	alert("Los Datos del Beneficiario han sido ingresados correctamente");
  	}
	
  else
  if(flujo2==6)
  {
  document.form1.cmd_carga.disabled=true;
  document.form1.cmd_envia.disabled=true;
  document.form1.cmd_grabar.disabled=true;
  document.form1.cmd_imprimir.disabled=false;
  alert("Su solicitud ha sido enviada a bienestar");
  }
  else
   if(flujo2==7)
  {
  document.form1.cmd_carga.disabled=true;
  document.form1.cmd_envia.disabled=true;
  document.form1.cmd_grabar.disabled=true;
  document.form1.cmd_imprimir.disabled=true;
  alert("Su Trámite ha concluido");
  }
  else 
  {
  document.form1.cmd_carga.disabled=true;
  document.form1.cmd_envia.disabled=true;
  document.form1.cmd_imprimir.disabled=true;
  //alert("nada ");
  }	
  }

function cargas_familiares()
{
	
	document.form1.action="ingreso_cargas.php";
	document.form1.submit();
}

function enviar_bienestar()
{
	document.form1.action="mail_bienestar.php";
	document.form1.submit();
}

function imprime_solicitud()
{
	document.form1.action="imprime_respuesta.php";
	document.form1.submit();
}


function region()
{
var selindice, nuevalsel;
var valor="XX";
selindice = document.form1.cbo_region.selectedIndex;
nuevasel = document.form1.cbo_region.options[selindice].value;
document.form1.val_region.value=nuevasel;
parent.frames[0].location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
//top.windows.topFrame.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
//alert("region" + document.form1.val_region.value);
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
 
if (sw_ok)
{
	
	document.form1.ok2=0;
	document.form1.submit();
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

//-->
</script>
<script src="js/calendario.js"></script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body bgcolor="#FFFFFF" topmargin="0" onLoad="muestra_cuadro()">
<center>
<form name="form1" method="Post" action="guardar_ingreso.php">
    <table width="661" height="30" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="610" bgcolor="#009900"> 
          <div align="center"><font color="#FFFFFF" size="4"><strong>SOLICITUD 
            DE BENEFICIOS</strong></font></div></td>
      </tr>
    </table>
    <table width="661" border="1" cellpadding="1" cellspacing="0" bgcolor="#FFFFCC">
      <tr> 
        <td width="656" align="center"> 
          <table width="641" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="320" class="texto"><strong><font color="#000000">DATOS 
                DE LA SOLICITUD</font></strong></td>
            <td width="322"><div align="right"><strong><font color="#0000A0" size="2"> 
                  </font></strong></div></td>
          </tr>
        </table>
          <table width="650" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="651" height="199" align="center"> 
                <div align="center"> 
                  <table width="630" border="0" cellpadding="1" cellspacing="1">
                    <tr> 
                      <td width="110" height="32"><font size="2" face="Arial, Helvetica, sans-serif">Apellido 
                        Paterno </font></td>
                      <td width="205"><font size="2" face="Arial, Helvetica, sans-serif"><strong> 
                        <input name="txtpaterno" type="text" id="txtpaterno" value="<?php echo $pat_fun; ?>" size="25">
                        </strong> </font></td>
                      <td width="110"> 
                        <div align="right"><font size="2" face="Arial, Helvetica, sans-serif">Apellido 
                          Materno </font></div></td>
                      <td width="205"><font size="2" face="Arial, Helvetica, sans-serif"><strong> 
                        <input name="txtmaterno" type="text" id="txtmaterno" value="<?php echo $mat_fun; ?>" size="25" maxlength="30">
                        </strong></font></td>
                    </tr>
                    <tr> 
                      <td width="111" height="32"><font size="2" face="Arial, Helvetica, sans-serif">Nombres</font></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="txtnombres" type="text" id="txtnombres" value="<?php echo $nom_fun; ?>" size="25" maxlength="30">
                        </font></td>
                      <td><div align="right"><font size="2" face="Arial, Helvetica, sans-serif">Rut</font></div></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><strong> 
                        <input name="txtrut" type="text" id="txtrut" value="<?php echo $rut_c; ?>" size="12" maxlength="12">
                        </strong></font></td>
                    </tr>
                  </table>
                  <table width="630" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="110"><font size="2" face="Arial, Helvetica, sans-serif">Cargo</font></td>
                      <td width="520"><font size="2" face="Arial, Helvetica, sans-serif"> 
                        <select name="cbo_cargo" id="cbo_cargo">
                          <option value="0"> </option>
                          <?php //if ($car_fun>0)
					//{
					while($reg_cargo=mssql_fetch_array($rs_cargo))
					 {
			  		  echo "<option value=" . $reg_cargo[cod_cargo];
						if($reg_cargo[cod_cargo]==$car_fun) echo " SELECTED";
					  echo ">" . $reg_cargo[desc_cargo] . "</option>\n";
           			 }
					//}
					?>
                        </select>
                        </font></td>
                    </tr>
                    <tr> 
                      <td width="110"><font size="2" face="Arial, Helvetica, sans-serif">Lugar 
                        de trabajo</font></td>
                      <td width="520"><font size="2" face="Arial, Helvetica, sans-serif"> 
                        <select name="Cbo_Procedencia" class="combo" id="select5" >
                          <option value="0"> </option>
                          <?php             
		    while($reg_dependencia=mssql_fetch_array($rs_dependencia))
			{
			    echo "<option value=" . $reg_dependencia[codigo_dep];
				if($reg_dependencia[codigo_dep]==$dependencia) echo " SELECTED";
				echo ">" . $reg_dependencia[desc_larga_dep] . "</option>\n";
            }
			?>
                        </select>
                        </font></td>
                    </tr>
                  </table>
                  <table width="630" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="110" height="24"><font size="2" face="Arial, Helvetica, sans-serif">Grado</font></td>
                      <td width="196"><font color="#000000" size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font><font size="2" face="Arial, Helvetica, sans-serif"> 
                        <input name="txtgrado" type="text" id="txtgrado3"  value="<?php echo $gra_fun; ?>" size="4" maxlength="4">
                        </font><font color="#000000" size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font></td>
                      <td width="124"><font color="#000000" size="2" face="Arial, Helvetica, sans-serif">Fecha 
                        Solicitud</font></td>
                      <td width="200"><font color="#000000" size="2" face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_doc" type="text" class="entradas" id="Txt_fecha_doc2" value="<?=$fecha_x?>" size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="imagen/icon-calen_f2.gif" width="20" height="21" border="0" name="calenda"></a> 
                        </font><font size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font></td>
                    </tr>
                    <tr> 
                      <td width="110" height="24"><font size="2" face="Arial, Helvetica, sans-serif">Estamento</font></td>
                      <td width="196"><font size="2" face="Arial, Helvetica, sans-serif"> 
                        <select name="cbo_estamento" id="select2">
                          <option value="0"> </option>
                          <?php //if ($est_fun>0)
					//{
					while($reg_estamento=mssql_fetch_array($rs_estamento))
					 {
			  		  echo "<option value=" . $reg_estamento[cod_estamento];
						if($reg_estamento[cod_estamento]==$est_fun) echo " SELECTED";
					  echo ">" . $reg_estamento[desc_estamento] . "</option>\n";
           			 }
					//}
					?>
                        </select>
                        </font><font size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font></td>
                      <td width="124"><font size="2" face="Arial, Helvetica, sans-serif">Anexo</font></td>
                      <td width="200"><font size="2" face="Arial, Helvetica, sans-serif"> 
                        <input name="txtanexo" type="text" id="txtanexo" value="<?php echo $ane_fun; ?>" size="20" maxlength="20">
                        </font></td>
                    </tr>
                  </table>
                  
                </div></td>
            </tr>
          </table>
		  <table width="650" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td><strong>BENEFICIOS SOLICITADOS</strong></td>
            </tr>
          </table>
          <table width="650" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="651" height="199" align="center"> <div align="center"> 
                  <table width="650" border="1" cellspacing="1" cellpadding="1">
                    <tr>
                      <td width="14">&nbsp;</td>
                      <td width="188">&nbsp;</td>
                      <td width="101">&nbsp;</td>
                      <td width="101">&nbsp;</td>
                      <td width="101">&nbsp;</td>
                      <td width="106">&nbsp;</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table>
                  <p>&nbsp;</p>
                  <table width="630" border="0" cellpadding="1" cellspacing="1">
                    <tr> 
                      <td width="110" height="32"><font size="2" face="Arial, Helvetica, sans-serif">Apellido 
                        Paterno </font></td>
                      <td width="205"><font size="2" face="Arial, Helvetica, sans-serif"><strong> 
                        <input name="txtpaterno2" type="text" id="txtpaterno2" value="<?php echo $pat_fun; ?>" size="25">
                        </strong> </font></td>
                      <td width="110"> <div align="right"><font size="2" face="Arial, Helvetica, sans-serif">Apellido 
                          Materno </font></div></td>
                      <td width="205"><font size="2" face="Arial, Helvetica, sans-serif"><strong> 
                        <input name="txtmaterno2" type="text" id="txtmaterno2" value="<?php echo $mat_fun; ?>" size="25" maxlength="30">
                        </strong></font></td>
                    </tr>
                    <tr> 
                      <td width="111" height="32"><font size="2" face="Arial, Helvetica, sans-serif">Nombres</font></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"> 
                        <input name="txtnombres2" type="text" id="txtnombres2" value="<?php echo $nom_fun; ?>" size="25" maxlength="30">
                        </font></td>
                      <td><div align="right"><font size="2" face="Arial, Helvetica, sans-serif">Rut</font></div></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><strong> 
                        <input name="txtrut2" type="text" id="txtrut2" value="<?php echo $rut_c; ?>" size="12" maxlength="12">
                        </strong></font></td>
                    </tr>
                  </table>
                  <table width="630" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="110"><font size="2" face="Arial, Helvetica, sans-serif">Cargo</font></td>
                      <td width="520"><font size="2" face="Arial, Helvetica, sans-serif"> 
                        <select name="select" id="select">
                          <option value="0"> </option>
                          <?php //if ($car_fun>0)
					//{
					while($reg_cargo=mssql_fetch_array($rs_cargo))
					 {
			  		  echo "<option value=" . $reg_cargo[cod_cargo];
						if($reg_cargo[cod_cargo]==$car_fun) echo " SELECTED";
					  echo ">" . $reg_cargo[desc_cargo] . "</option>\n";
           			 }
					//}
					?>
                        </select>
                        </font></td>
                    </tr>
                    <tr> 
                      <td width="110"><font size="2" face="Arial, Helvetica, sans-serif">Lugar 
                        de trabajo</font></td>
                      <td width="520"><font size="2" face="Arial, Helvetica, sans-serif"> 
                        <select name="select" class="combo" id="select3" >
                          <option value="0"> </option>
                          <?php             
		    while($reg_dependencia=mssql_fetch_array($rs_dependencia))
			{
			    echo "<option value=" . $reg_dependencia[codigo_dep];
				if($reg_dependencia[codigo_dep]==$dependencia) echo " SELECTED";
				echo ">" . $reg_dependencia[desc_larga_dep] . "</option>\n";
            }
			?>
                        </select>
                        </font></td>
                    </tr>
                  </table>
                  <table width="630" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="110" height="24"><font size="2" face="Arial, Helvetica, sans-serif">Grado</font></td>
                      <td width="196"><font color="#000000" size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font><font size="2" face="Arial, Helvetica, sans-serif"> 
                        <input name="txtgrado2" type="text" id="txtgrado"  value="<?php echo $gra_fun; ?>" size="4" maxlength="4">
                        </font><font color="#000000" size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font></td>
                      <td width="124"><font color="#000000" size="2" face="Arial, Helvetica, sans-serif">Fecha 
                        Solicitud</font></td>
                      <td width="200"><font color="#000000" size="2" face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_doc2" type="text" class="entradas" id="Txt_fecha_doc" value="<?=$fecha_x?>" size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="imagen/icon-calen_f2.gif" width="20" height="21" border="0" name="calenda"></a> 
                        </font><font size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font></td>
                    </tr>
                    <tr> 
                      <td width="110" height="24"><font size="2" face="Arial, Helvetica, sans-serif">Estamento</font></td>
                      <td width="196"><font size="2" face="Arial, Helvetica, sans-serif"> 
                        <select name="select" id="select4">
                          <option value="0"> </option>
                          <?php //if ($est_fun>0)
					//{
					while($reg_estamento=mssql_fetch_array($rs_estamento))
					 {
			  		  echo "<option value=" . $reg_estamento[cod_estamento];
						if($reg_estamento[cod_estamento]==$est_fun) echo " SELECTED";
					  echo ">" . $reg_estamento[desc_estamento] . "</option>\n";
           			 }
					//}
					?>
                        </select>
                        </font><font size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font></td>
                      <td width="124"><font size="2" face="Arial, Helvetica, sans-serif">Anexo</font></td>
                      <td width="200"><font size="2" face="Arial, Helvetica, sans-serif"> 
                        <input name="txtanexo2" type="text" id="txtanexo2" value="<?php echo $ane_fun; ?>" size="20" maxlength="20">
                        </font></td>
                    </tr>
                  </table>
                </div></td>
            </tr>
          </table>
          <br>
          <table width="650" border="0" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="75%">&nbsp;</td>
            </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr> 
              <td width="200" height="37" align="center" valign="bottom"> <div align="center"> 
                 <?php $ok2=3; ?>	
				  <input type="hidden" name="estado" value="<? echo "S";?>">
                  <input type="hidden" name="rut" value="<? echo $rut_fun;?>">
                  <input type="hidden" name="rut_c" value="<? echo $rut_c;?>">
                  <input type="hidden" name="ok" value="<? echo $ok2;?>">
                  <input type="hidden" name="val_region">
                  <input name="cmd_grabar" type="button" class="botones" onClick="validar_datos();" value="Datos Beneficiario">
                </div></td>
              <td width="100" align="center" valign="bottom"> <input type="submit" name="cmd_carga" value="Cargas Familiares" onClick="cargas_familiares();"> 
              </td>
              <td width="100" align="center" valign="bottom"><input type="submit" name="cmd_envia" value="Enviar a Bienestar" onClick="enviar_bienestar();"></td>
              <td width="125" align="center" valign="bottom"> 
                
				<input type="submit" name="cmd_imprimir" value="Imprmir" onClick="imprime_solicitud();"></td>
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
