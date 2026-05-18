<?php
include("conexion_bd2.php");
//include("carga_tablas.php");
//$num_int=$folio;
//Busca datos del funcionario en bd corporativo
//$ok1=$flujook;
$ok1=$ok;
//echo "rut " . $rut . "rut enc " . $rut_encargado;
$rut=$rut;
$rut_encargado="10315892-3";
$encargado_corto=substr(rut_encargado,0,7);

$nom_encargado="LETICIA RAMIREZ ARRIAGADA";
$correo_encargado="lramirez@minsal.cl";
$fechasistema = date("Y/m/d H:i"); 
$query_fun="exec busca_datos_funcionario '" . $rut . "'";
$rs_fun=mssql_query($query_fun,$cn); 
$filas_fun = mssql_num_rows($rs_fun); 
$reg_fun=mssql_fetch_array($rs_fun);
if($filas_fun >0)
{
$rut_fun=$rut . "-" . $reg_fun[dv_fun];
$pat_fun= $reg_fun[ap_pat_fun];
$mat_fun= $reg_fun[ap_mat_fun];
$nom_fun= $reg_fun[nombres_fun];
$dir_fun= $reg_fun[direccion_fun];
$gra_fun= $reg_fun[grado_fun];
$cor_fun= $reg_fun[email_fun];
$ane_fun= $reg_fun[anexo_fun];
$car_fun= $reg_fun[desc_cargo];
$est_fun= $reg_fun[desc_estamento];
$dep_fun= $reg_fun[desc_dep];
$com_fun= $reg_fun[desc_comuna];
$reg_fun= $reg_fun[desc_region];
$nombre = ltrim($nom_fun) . " " . ltrim($pat_fun) . " " . ltrim($mat_fun);
}

// busca datos de funcionario y cargas en bd Bienestar
mssql_close($cn);
$cc = mssql_connect("bd2-minsal", "bienes", "bienes2004") or die("El Servidor No se encuentra");
      mssql_select_db("bienestar");

$query_carga="exec busca_carga '" . $rut . "'";
$rs_carga=mssql_query($query_carga,$cc);  

$query_bienestar="exec busca_bienestar '" . $rut . "'";
$rs_bienestar=mssql_query($query_bienestar);  
$reg_bienestar=mssql_fetch_array($rs_bienestar);
$filas_bienestar=mssql_num_rows($rs_bienestar);
$fecha_sol= substr($reg_bienestar[fecha_ing],0,2) . "-" . substr($reg_bienestar[fecha_ing],3,2)  . "-" . substr($reg_bienestar[fecha_ing],6,4);

$fecha_x = date("d-m-Y");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario Respuesta</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript" type="text/javascript">
var sw_ok;
var z=0;
var flujo2= <?php echo $ok1; ?>;  

if (parseInt(navigator.appVersion) > 3) {
	if (navigator.appName == "Netscape") {
		layerVar="document.layers";
		styleVar="";
	}else{
		layerVar="document.all";
		styleVar=".style";
	}
}


function muestra_cuadro()
 { 
 /*if (flujo2==0 || flujo2==1)
   {
    document.form1.cmd_grabar.disabled=true;
	document.form1.cmd_envia.disabled=false;
	alert("La respuesta  ha sido grabada");
  	}
	
  else
  if(flujo2==6)
  {
  document.form1.cmd_envia.disabled=true;
  document.form1.cmd_grabar.disabled=true;
  alert("Su respuesta ha sido enviada al funcionario");
  }
  else 
  {
  document.form1.cmd_grabar.disabled=false;
  document.form1.cmd_envia.disabled=false;
  //alert("nada ");
  }
  */	
}

function enviar_respuesta()
{
	document.form1.action="mail_respuesta.php";
	document.form1.submit();
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
	document.form1.submit();
} 

function CheckLength(length) {
if (window.event.srcElement.value.length >= length) {
   alert('El Máximo de caracteres es  250');
   return false;                         
}
}

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

function imprime() {
  MuestraEsconde('impresion','hidden');
  window.print();
  MuestraEsconde('impresion','visible');
  alert("La Impresión ha terminado");
  
  
}

//-->
</script>
<script src="js/calendario.js"></script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body bgcolor="#FFFFFF" topmargin="0" onLoad="muestra_cuadro()">

<center>
<form name="form1" method="Post" action="guardar_respuesta.php">
    <table width="660" height="223" border="0" cellpadding="1" cellspacing="0">
      <tr> 
        <td width="232" height="118" bgcolor="#FFFFFF"><div align="center"><img src="imagen/logo_chico.gif" width="95" height="87"><br>
          </div></td>
        <td width="424" bgcolor="#FFFFFF"><div align="right"><img src="imagen/logo%20bienestar4.jpg" width="91" height="133"></div></td>
      </tr>
      <tr bgcolor="#FFFFFF"> 
        <td height="21" bgcolor="#FFFFFF"> <div align="center"><font size="2" face="Arial, Helvetica, sans-serif">DIRECC. 
            PPTO. Y ADMINISTRACI&Oacute;N</font><font size="1" face="Arial, Helvetica, sans-serif"><br>
            <font size="2"><strong>Servicio de Bienestar</strong></font></font></div></td>
        <td>&nbsp;</td>
      </tr>
      <tr bgcolor="#FFFFFF"> 
        <td height="21" colspan="2">&nbsp;</td>
      </tr>
      <tr bgcolor="#FFFFFF"> 
        <td height="33" colspan="2" bordercolor="1"> 
          <div align="center"><font color="#000000" size="4"><strong> 
            SOLICITUD DE INGRESO A BIENESTAR</strong></font></div></td>
      </tr>
    </table>
    <table width="670" border="0" cellpadding="1" cellspacing="0" bgcolor="#FFFFCC">
      <tr> 
        <td height="623" align="center"> 
          <div align="center"> 
            <table width="660" border="0" cellspacing="1" cellpadding="1">
              <tr>
                <td><font color="#A03D4B"><strong>DATOS DEL FUNCIONARIO</strong></font></td>
              </tr>
            </table>
            <table width="660" border="1" cellpadding="1" cellspacing="1" bgcolor="#F8FCDA">
              <tr> 
                <td> 
                  <table width="650" border="0" cellpadding="1" cellspacing="1">
                    <tr> 
                      <td width="122" height="21"><font size="2" face="Arial, Helvetica, sans-serif"><strong>Rut</strong></font></td>
                      <td width="261"><font size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $rut_fun; ?> 
                        </strong></font></td>
                      <td width="259"> <div align="right"></div></td>
                    </tr>
                  </table>
                  <table width="650" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="122"><font size="2" face="Arial, Helvetica, sans-serif"><strong>Nombre</strong></font></td>
                      <td width="535"><font size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $nombre; ?></strong></font></td>
                    </tr>
                  </table>
                  <table width="650" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="122" height="24"><font size="2" face="Arial, Helvetica, sans-serif"><strong>Lugar 
                        de Trabajo</strong></font></td>
                      <td width="203"><font size="2" face="Arial, Helvetica, sans-serif"><strong><font size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $dep_fun; ?></strong></font><font color="#000000"> 
                        </font></strong></font></td>
                      <td width="102"><font size="2" face="Arial, Helvetica, sans-serif"><strong>Cargo</strong></font></td>
                      <td width="210"><font size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $car_fun; ?></strong></font></td>
                    </tr>
                    <tr> 
                      <td width="122" height="24"><font size="2" face="Arial, Helvetica, sans-serif"><strong>Grado</strong></font></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $gra_fun; ?> 
                        </strong></font></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><strong>Estamento</strong></font></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $est_fun; ?></strong></font></td>
                    </tr>
                  </table>
                  <table width="650" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="119"><font size="2" face="Arial, Helvetica, sans-serif"><strong>Anexo</strong></font></td>
                      <td width="205"><font size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $ane_fun; ?> 
                        </strong></font></td>
                      <td width="103"><font size="2" face="Arial, Helvetica, sans-serif"><strong><font color="#000000">Fecha 
                        Solicitud</font></strong></font></td>
                      <td width="210"><font size="2" face="Arial, Helvetica, sans-serif"><strong><font color="#000000"><?php echo $fecha_sol; ?></font></strong></font></td>
                    </tr>
                  </table>
                  <table width="650" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="122"><font size="2" face="Arial, Helvetica, sans-serif">Direcci&oacute;n</font></td>
                      <td width="498"><font size="2" face="Arial, Helvetica, sans-serif"><?php echo $dir_fun; ?><strong> 
                        &nbsp;</strong></font></td>
                    </tr>
                    <tr> 
                      <td width="102"><font size="2" face="Arial, Helvetica, sans-serif">Comuna</font></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><?php echo $com_fun; ?></font></td>
                    </tr>
                    <tr> 
                      <td width="102"><font size="2" face="Arial, Helvetica, sans-serif">Regi&oacute;n</font></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><?php echo $reg_fun; ?></font></td>
                    </tr>
                  </table>
                  
                </td>
              </tr>
            </table>
           
            
            <table width="660" border="0" cellspacing="1" cellpadding="1">
              <tr>
                <td height="43" valign="middle"><font size="2" face="Arial, Helvetica, sans-serif"><strong>AL 
                  CONSEJO ADMINISTRATIVO DEL SERVICIO DE BIENESTAR:</strong></font></td>
              </tr>
            </table>
            <table width="660" border="1" cellspacing="1" cellpadding="1">
              <tr> 
                <td height="86"><div align="justify"><font size="2" face="Arial, Helvetica, sans-serif">Declara 
                    conocer el Reglamento del Servicio de Bienestar, autoriza 
                    el descuento de su aporte como socio, equivalente actualmente 
                    al <strong>0,7% de su renta imponible para pensiones </strong>y 
                    de las sumas correspondientes a devoluciones, cr&eacute;ditos, 
                    pagos de pr&eacute;stamos e intereses y otros compromisos 
                    derivados de la disposiciones reglamentarias vigentes, que 
                    conoce y acepta.</font></div></td>
              </tr>
            </table>
            <table width="660" border="0" cellspacing="1" cellpadding="1">
              <tr> 
                <td height="94" valign="bottom"> 
                  <div align="right"><font size="2" face="Arial, Helvetica, sans-serif"><strong>V&ordm;B&ordm; 
                    DEPTO. RECURSOS HUMANOS</strong></font></div></td>
                <td valign="bottom">
<div align="center"><font size="2" face="Arial, Helvetica, sans-serif"><strong>___________________________________</strong></font></div></td>
              </tr>
            </table>
            <table width="660" height="92" border="0" cellpadding="1" cellspacing="1">
              <tr> 
                <td height="90" valign="middle"> 
                  <div align="justify"><font color="#000000" size="2" face="Arial, Helvetica, sans-serif"><strong>Para 
                    los efectos de beneficios extensivos a su grupo familiar deja 
                    constancia que posee las <br>
                    siguientes cargas familiares registradas en el Depto. de Recursos 
                    Humanos. </strong></font></div></td>
              </tr>
            </table>
            <table width="660" border="0" cellspacing="1" cellpadding="1">
              <tr> 
                <td> <table width="650" align="center" border="1" cellspacing="1" cellpadding="0" bgcolor="#FFFFCC">
                    <?php
$cont=0;

while($reg_carga=mssql_fetch_array($rs_carga))
{
$cont=$cont + 1;
$nom_carga="";
$rut_carga="";
$nom_carga=rtrim($reg_carga[nombres_carga]) . " " . rtrim($reg_carga[ape_pat_carga]) . " " . rtrim($reg_carga[ape_mat_carga]);
$rut_carga=$reg_carga[rut_carga] . "-" . $reg_carga[dv_carga];
$fec_carga=$reg_carga[fecha_nac];
//substr($reg_bienestar[fecha_nac],0,2) . "-" . substr($reg_bienestar[fecha_nac],3,2)  . "-" . substr($reg_bienestar[fecha_nac],6,4);
if($cont==1)
{
?>
                    <tr bgcolor="#8BE686"> 
                      <td width="29"  valign="middle"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Nº</font></strong></td>
                      <td width="77"  valign="middle"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">RUT</font></strong></td>
                      <td width="324"  valign="middle"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">NOMBRE</font></strong></td>
                      <td width="39"  valign="middle"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">SEXO</font></strong></td>
                      <td width="68"  valign="middle"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">RELACION</font></strong></td>
                      <td width="92"  valign="middle"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">FEC. 
                        NAC</font></strong></td>
                    </tr>
                    <? } ?>
                    <tr bgcolor="#F8FCDA"> 
                      <td width="29"  valign="middle"><font size="2" face="Arial, Helvetica, sans-serif"><? echo $cont;?></font></td>
                      <td width="77"  valign="middle"><font size="2" face="Arial, Helvetica, sans-serif"><? echo $rut_carga;?></font></td>
                      <td width="324"  valign="middle"><font size="2" face="Arial, Helvetica, sans-serif"><? echo $nom_carga;?></font></td>
                      <td width="39"  valign="middle"><font size="2" face="Arial, Helvetica, sans-serif"><? echo $reg_carga[sexo_carga];?></font></td>
                      <td width="68"  valign="middle"><font size="2" face="Arial, Helvetica, sans-serif"><? echo $reg_carga[desc_carga];?></font></td>
                      <td width="92"  valign="middle"><font size="2" face="Arial, Helvetica, sans-serif"><? echo $fec_carga;?></font></td>
                    </tr>
                    <?  } ?>
                  </table></td>
              </tr>
            </table>
          
          </div></td>
      </tr>
      <tr> 
        <td width="664" height="40" align="center"> 
          <table width="660" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr> 
              <td height="31" width="165"> <div align="center"> 
                  <input type="hidden" name="rut" value="<? echo $rut;?>">
                  <input type="hidden" name="rut_c" value="<? echo $rut_fun;?>">
                  <input type="hidden" name="rut_encargado" value="<? echo $rut_encargado;?>">
                  <input type="hidden" name="encargado_corto" value="<? echo $encargado_corto;?>">
                  <input type="hidden" name="nom_encargado" value="<? echo $nom_encargado;?>">
                  <input type="hidden" name="correo_encargado" value="<? echo $correo_encargado;?>">
                  <input type="hidden" name="ok" value="<? 0;?>">
                </div></td>
            </tr>
          </table></td>
      </tr>
    </table>
 </form>
  
<?php mssql_close($cc);
?>
</center>
<div id="impresion" style="position:absolute; z-index:1; visibility: visible; overflow: auto; background-color: #ffffff; layer-background-color: #ffffff; border: 1px none #000000;">
    <table width="60%">
	<tr><td align="right">
    <input type="button" value="Imprime Solicitud" onClick="imprime()">
	</td>
	<td>
	<input type="button" value="Volver" onClick="<?php
	 echo "location.href='ingreso_bienestar.php?rut=" . $rut .			"&flujook=" . 7 . "&rut_fun=" . $rut_fun . "'";
	 ?>">
	</td>
	</tr>
	</table>
</div>

</body>
</html>
