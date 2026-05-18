<?php
include("conexion_bd.php");
include("carga_tablas.php");
include("carga_des_externo.php");
$Usuario=$cusuario;
$xx= $idusuario;
$fun=$idfuncionario;
$idseg=$idseguim;
$flujo1=$flujook;
$rs_tramite = mssql_query("SELECT *  FROM detalle_facturas where id_detalle = " . $idseg, $cn);
$reg_tram = mssql_fetch_array($rs_tramite);
$dependencia=$reg_tram["id_destino"];
$distribucion=$reg_tram["id_tipo_distribucion"];
$compromiso=$reg_tram["id_tipo_compromiso"];
$diascompromiso=$reg_tram["dias_compromiso"];
if ($reg_tram["observaciones"] == null  )
{ $txtobs = '' ;}
else
{$txtobs =$reg_tram["observaciones"];}
if($reg_tram["rut_destino"]!="")
{
$rutfuncionario=$reg_tram["rut_destino"];
}
$fun=$idfuncionario;
$tipo_des=$reg_tram["tipo_destinatario"];
$flujo1=$flujook;
if($tipo_des=="I")
{
	//$rs_funcionario = mssql_query("SELECT * FROM funcionario where id_dependencia = " . $dependencia, $cn);
	$rs_funcionario = mssql_query("SELECT * FROM funcionario where id_dependencia = " . $dependencia . " and vigencia is null order by nombres,apellidos" , $cn);
	
}

$fecha_x = date("d-m-Y");
// buscando numero de factura 
$idfactura=$reg_tram[id_factura];
$query_fact="select * from facturas where id_factura =". $idfactura;
$r_f=mssql_query($query_fact);
$reg_f=mssql_fetch_array($r_f);
$numfact=$reg_f[num_factura];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario ingreso docto1</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/javascript">
<!--
//<?php echo "sasasas" . $dependencia . "\n"; ?>
var sw_ok;
var z=0;
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
 
function CheckLength(length) {
if (window.event.srcElement.value.length >= length) {
   alert('El Máximo de caracteres es  250');
   return false;                         
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
	document.form1.Cbo_Func_Destino.options.value=0;
	document.form1.Cbo_Func_Destino.disabled=true;
	}
}
function destino_interno()
{
var selindice, nuevalsel;
var valor="I";
nuevasel= 0;
top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
}

// Valida los datos antes de grabar en las tablas
function validar_datos()
{
sw_ok=true; 

if(document.form1.Cbo_Destinatario.options.value==0)
  {
 	sw_ok=false;
	alert("Falta Ingresar el Destinatario del Documento");
	document.form1.Cbo_Destinatario.focus();
  }
else
if  (document.form1.radiodestino[0].checked==true)
{
   	document.form1.tipo_destino.value="I";
}
else
if  (document.form1.radiodestino[1].checked==true)
{
	document.form1.tipo_destino.value="E";
	document.form1.val_funcionario1.value=0;
}
if(document.form1.Cbo_Func_Destino.selectedIndex==0)
{document.form1.val_funcionario1.value=0;}

if (sw_ok)
{
	document.form1.submit();
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
	document.form1.val_destino.value= nuevasel;
	top.window.frame_consultas.location.href="frame_consultas.php?cod_dep="+nuevasel+"&sw="+valor;
	document.form1.Cbo_Func_Destino.disabled=false;
	}
else
if (document.form1.radiodestino[1].checked==true){
	document.form1.val_destino.value= document.form1.Cbo_Destinatario.selectedIndex;}
	document.form1.val_funcionario1.value=0;
}

function cambio3()
{
var selindice, nuevalsel;
var valor="F";
selindice = document.form1.Cbo_Func_Destino.selectedIndex;
nuevasel = document.form1.Cbo_Func_Destino.options[selindice].value;
document.form1.val_funcionario1.value=selindice;
}

function mensaje() { 
  if (flujo2==1) {
  
  alert("El Trámite ha sido Modificado");
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

<body bgcolor="#FFFFFF" topmargin="0"onLoad="mensaje()">
<center>
<form name="form1" method="Post" action="guardar_modificacion_factura.php">
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="650" height="40">
<div align="center"><font color="#FFFFFF" size="4"><strong>MODIFICACION 
            DE TRAMITE FACTURA </strong></font></div></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#ECE9D8">
      <tr> 
        <td width="650"  align="center" bgcolor="#E6EEFF"> 
          <table width="640" border="0" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="81" height="30"><strong><font color="#0000A0">Nro. Factura</font></strong> 
                <font color="#800000">&nbsp;</font></td>
              <td width="555"><font color="#800000"><strong><?php echo $numfact;?></strong></font></td>
            </tr>
            <tr> 
              <td height="143" colspan="2"> <table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="304" class="texto"><font color="#804040"><strong>DESTINO</strong></font></td>
                  </tr>
                </table>
                <table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr> 
                    <td width="122"><div align="left"><strong>Interno 
                        <?php if($tipo_des=="I") { ?>
                        <input name="radiodestino" type="radio" onClick="javascript:destino_interno();" checked="true">
                        </strong> 
                        <?php ;} else {?>
                        <input name="radiodestino" type="radio" onClick="javascript:destino_interno();" >
                        <?php ;} ?></strong>
                        </div></td>
                    <td width="509"><strong>Externo 
                      <?php if($tipo_des=="E") { ?>
                      <input name="radiodestino" type="radio"  onClick="javascript:destino_externo();" checked="true">
                      </strong> 
                      <?php ;} else {?>
                      <input name="radiodestino" type="radio" onClick="javascript:destino_externo();" > 
                      <?php ;} ?></strong>
                      </td>
                  </tr>
                  <tr> 
                    <td>Destino</td>
                    <td><font face="Arial"> 
                      <select name="Cbo_Destinatario" class="combo" id="Cbo_Destinatario" onChange="javascript:cambio1();">
                        <option value="0"> </option>
                        <?
						if($tipo_des=="I"){
		while($reg_destino=mssql_fetch_array($rs_destino))
		{
           echo '<option value="' . $reg_destino[id_dependencia] . '"';
		   if($dependencia==$reg_destino[id_dependencia]) echo ' SELECTED';
		   echo '>' . $reg_destino[desc_dependencia] . "</option>\n";
        }
		}
		else
		{
		while($reg_des_ext=mssql_fetch_array($rs_destino_ext))
		{
           echo '<option value="' . $reg_des_ext[id_dependencia_externa] . '"';
		   if($dependencia==$reg_des_ext[id_dependencia_externa]) echo ' SELECTED';
		   echo '>' . $reg_des_ext[desc_dependencia_externa] . "</option>\n";
        }
		}
		?>
                      </select>
                      </font></td>
                  </tr>
                  <tr> 
                    <td height="33">Funcionario</td>
                    <td><font face="Arial"> 
                      <select name="Cbo_Func_Destino" class="combo" id="select5" onChange="javascript:cambio3();">
                        <option value="0"> </option>
                        <?
		while($reg_func=mssql_fetch_array($rs_funcionario))
		{
           echo '<option value="' . $reg_func[rut] . '"';
		   if($rutfuncionario==$reg_func[rut]) echo ' SELECTED';
		   echo '>' . $reg_func[nombres] . " " .  $reg_func[apellidos] . "</option>\n";
        }
		?>
                      </select>
                      </font></td>
                  </tr>
                </table></td>
            </tr>
          </table>
          <table width="640" border="0" cellspacing="1" cellpadding="1">
            <tr>
            <td width="20%">Tipo Distribuci&oacute;n</td>
            <td width="30%"><font face="Arial">
                <select name="Cbo_Tipo_Distribucion" class="combo" id="Cbo_Tipo_Distribucion">
                 <?
		while($reg_distribucion=mssql_fetch_array($rs_distribucion))
		{
           echo '<option value="' . $reg_distribucion[id_tipo_distribucion] . '"';
		   if($distribucion==$reg_distribucion[id_tipo_distribucion]) echo ' SELECTED';
		   echo '>' . $reg_distribucion[desc_tipo_distribucion] . "</option>\n";
        }
		?>			
			
                </select>
                </font></td>
            <!--
			<td width="22%">Tipo Compromiso</td>
            <td width="28%"><font face="Arial">
                <select name="Cbo_Tipo_Compromiso" class="combo" id="select14">
		<?
				while($reg_tipo_compromiso=mssql_fetch_array($rs_tipo_compromiso))
		{
           echo '<option value="' . $reg_tipo_compromiso[id_tipo_compromiso] . '"';
		   if($compromiso==$reg_tipo_compromiso[id_tipo_compromiso]) echo ' SELECTED';
		   echo '>' . $reg_tipo_compromiso[desc_tipo_compromiso] . "</option>\n";
        }
		?>						
                              </select>
              </font></td>-->
          </tr>
          <tr>
		  <input type="hidden" name="Cbo_Tipo_Compromiso" value="<?php echo 1;?>">
            <td>Estado Compromiso</td>
            <td><div align="left"><font face="Arial"><strong> En Trámite</strong></font></div></td>
            <td>D&iacute;as Compromiso</td>
            <td><input name="TxtDias"  type="text" class="entradas" value="<?php echo $diascompromiso ;?>" onBlur="valida_digito(this.value,this,2);" size="2" maxlength="2"></td>
          </tr>
        </table>
          <table width="640" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="20%"><strong><font size="2">Observaci&oacute;n</font></strong><br>
              </td>
              <td width="80%"> <?php echo '<textarea name="TxtObservacion" cols="50" 
 rows="3" class="cajatexto" onKeyPress="return CheckLength(250)">'. $txtobs .'</textarea>'; ?> 
			  </td>
            </tr>
          </table>

        
          <table width="640" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr> 
              <td height="37" width="306"> <div align="center"> 
                  <input type="hidden" name="idusuario" value="<? echo $xx;?>">
                  <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
                  <input type="hidden" name="idfuncionario" value="<? echo $fun;?>">
                  <input type="hidden" name="tipo_destino" >
                  <input type="hidden" name="val_destino" >
                  <input type="hidden" name="val_funcionario1" >
                  <input type="hidden" name="accion" value="<? echo 2;?>">
				  <input type="hidden" name="idseguim" value="<? echo $idseg;?>">
				  <input type="hidden" name="num_factura" value="<? echo $num_factura;?>">
                </div></td>
              <td width="300"><div align="center">
                  <input name="cmd_grabar" type="button" class="botones" onClick="validar_datos();" value="Grabar">
                </div></td>
              <td width="300"><div align="center" width="310"> </div></td>
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
