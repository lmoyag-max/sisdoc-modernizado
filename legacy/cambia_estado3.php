<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
$usua=$cusuario;
$xx=$idusuario;
$iddoc=$iddocum;
$idseg=$idseguim;
$fun=$idfuncionario;
$flujo = 8;
$numint=0;
$nombre_pantalla="";
$dedonde=$origen;
$txtnomina =$txtnomina;
 //$txtagnor  arrastra el año ingresado en documentos recepcionados 
 // txtnomina arrastara la nomina buscada 
//echo "txtagnor " . $txtagnor . "nomina". $txtnomina ; 

$fecha_x = date("d-m-Y");
$rs_documento="exec documento_referencia '" . $iddoc . "','" . $idseg . "'";
$qq = mssql_query($rs_documento,$cn); 
$rs=mssql_fetch_array($qq);
$r_observacion= $rs["observaciones"];

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>RECEPCION TRAMITE DE DOCUMENTOS</title>


<script language="JavaScript" type="text/javascript">
function cambia_observacion(objeto)
{
 if (document.form1.Cbo_Estado_Compromiso.value==2)
 {
   objeto.blur();
 }
}
function enviar_datos() 

{
//var comp = 0;
 if (document.form1.Cbo_Estado_Compromiso.value==2){
   alert ("Antes de archivar debe cambiar estado de compromiso");
  }
  else 
  {

  document.form1.compromiso.value=document.form1.Cbo_Estado_Compromiso.value;
   
   
//  document.form1.action="multi_recib.php";
//  document.form1.submit();
var cusu ='<?php echo $cusuario;?>';
//alert ("observacion" + observacion_archiva);
  if (document.form1.compromiso.value== 3)
  {
 <?php
  echo 'location.href="cierra_tramite2.php?idusuario=' . $idusuario . "&flujook=" . $flujo  . "&compromiso=" . 3 .
 "&cusuario=" . $cusuario .  "&iddocum=" . $iddocum .  "&num_int=" . $numint . 
 "&idfuncionario=" . $idfuncionario .  "&accion=" . 1 .  "&origen=" . $dedonde . "&idseguim=" . $idseguim . "&txtnomina=" . $txtnomina. "&txtagnor=" . $txtagnor .'"';?> +"&observacion=" + document.form1.observacion.value +"&observacion2=" + document.form1.observacion2.value ; 
  }
  else 
  if (document.form1.compromiso.value== 4)
  {
 <?php
  echo 'location.href="cierra_tramite2.php?idusuario=' . $idusuario . "&flujook=" . $flujo  . "&compromiso=" . 4 .
 "&cusuario=" . $cusuario .  "&iddocum=" . $iddocum .  "&num_int=" . $numint . 
 "&idfuncionario=" . $idfuncionario .  "&accion=" . 1 .  "&origen=" . $dedonde . "&idseguim=" . $idseguim . "&txtnomina=" . $txtnomina. "&txtagnor=" . $txtagnor .'"';?> +"&observacion=" + document.form1.observacion.value +"&observacion2=" + document.form1.observacion2.value  ;
  }
   else
     if (document.form1.compromiso.value== 5)
  {
   
 <?php
  echo 'location.href="cierra_tramite2.php?idusuario=' . $idusuario . "&flujook=" . $flujo  . "&compromiso=" . 5 .  "&cusuario=" . $cusuario .  "&iddocum=" . $iddocum .  "&num_int=" . $numint .
 "&idfuncionario=" . $idfuncionario .  "&accion=" . 1 .  "&origen=" . $dedonde . "&idseguim=" . $idseguim . "&txtnomina=" . $txtnomina. "&txtagnor=" . $txtagnor .'"';?> +"&observacion=" + document.form1.observacion.value +"&observacion2=" + document.form1.observacion2.value ;
  }

  }
}	  
function deriva_docto()
{


 document.form1.submit();
 <?php
  echo 'location.href="deriva_sdoc.php?idusuario=' . $idusuario . "&flujook=" . $flujo . 
 "&cusuario=" . $cusuario .  "&iddocum=" . $iddocum .  "&num_int=" . $numint . 
 "&idfuncionario=" . $idfuncionario .  "&accion=" . 1 .  "&origen=" . $dedonde . "&idseguim=" . $idseguim . "&txtnomina=" . $txtnomina. "&txtagnor=" . $txtagnor .'";';  ?> 
 
 
  /* echo 'location.href="varios_sdoc.php?idusuario=' . $idusuario . "&flujook=" . $flujo . 
 "&cusuario=" . $cusuario .  "&iddocum=" . $iddocum .  "&num_int=" . $numint . 
 "&idfuncionario=" . $idfuncionario .  "&accion=" . 1 .  "&origen=" . $dedonde . "&idseguim=" . $idseguim . "&txtnomina=" . $txtnomina. "&txtagnor=" . $txtagnor .'";';  ?> 
*/
 // para ambos destinos no salir de la pantalla 
/*<?php echo 'location.href="deriva_sdoc_ambosdestinos.php?idusuario=' . $idusuario . "&flujook=" . $flujo . 
 "&cusuario=" . $cusuario .  "&iddocum=" . $iddocum .  "&num_int=" . $numint . 
 "&idfuncionario=" . $idfuncionario .  "&accion=" . 1 .  "&origen=" . $dedonde . "&idseguim=" . $idseguim . "&txtnomina=" . $txtnomina.'";';
 ?>*/

}
  
function deriva_con_docto()
{
 document.form1.submit();
 
<?php echo 'location.href="derivar_con_docto.php?idusuario=' . $idusuario . "&flujook=" . $flujo . 
 "&cusuario=" . $cusuario .  "&iddocum=" . $iddocum .  "&num_int=" . $numint . 
 "&idfuncionario=" . $idfuncionario .  "&accion=" . 1 . "&idseguim=" . $idseguim ."&txtnomina=" . $txtnomina. "&txtagnor=" . $txtagnor . '";';?> 
}

function responde_con_docto()
{
 document.form1.submit();
 
<?php echo 'location.href="responder_con_docto.php?idusuario=' . $idusuario . "&flujook=" . $flujo . 
 "&cusuario=" . $cusuario .  "&iddocum=" . $iddocum .  "&num_int=" . $numint . 
 "&idfuncionario=" . $idfuncionario .  "&accion=" . 2 .  "&idseguim=" . $idseguim . "&txtnomina=" . $txtnomina. "&txtagnor=" . $txtagnor .   '";';?> 
}

function respuesta_docto() 
{
  document.form1.submit();
 <?php echo 'location.href="resp_sindoc.php?idusuario=' . $idusuario . "&flujook=" . $flujo . 
 "&cusuario=" . $cusuario .  "&iddocum=" . $iddocum .  "&num_int=" . $numint . 
 "&idfuncionario=" . $idfuncionario .  "&accion=" . 1 . "&idseguim=" . $idseguim . "&txtnomina=" . $txtnomina. "&txtagnor=" . $txtagnor . '";';?> 
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
function abre_observacion_archivar()
{
document.form1.compromiso.value=document.form1.Cbo_Estado_Compromiso.value;
 if (document.form1.Cbo_Estado_Compromiso.value==2){
   alert ("Antes de archivar debe cambiar estado de compromiso");
  }
  else 
  if (document.form1.compromiso.value ==3 || document.form1.compromiso.value ==4 || document.form1.compromiso.value==5)
     {  MM_showHideLayers('Layer1','','show');}

}
function CheckLength(length) {
if (window.event.srcElement.value.length >= length) {
   alert('El Máximo de caracteres es  150');
   return false;                         
}
}
var isNS4 = (navigator.appName=="Netscape")?1:0;

function valida_caracter()
// valida  que el caracter ingresado no sea comilla simple , doble o salto de carro  19/06/2008 
// codigo 13 =salto de carro, 34=comilla doble , 39 =  apostrofe//
{
if(!isNS4)
 { 
 if ( event.keyCode==39 || event.keyCode==13  )
 { alert("No debe ingresar apóstrofe  o enter " );
 event.returnValue = false;
  }
  }
 else
 { 
  if (event.which==39 || event.which==13) 
   { alert("No debe ingresar apóstrofe   o enter " );
   return false;
   }
 }
}



</script>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC" topmargin="0">
<center>
<!--form name="form1" method="post" -->
<form name="form1" method="post" >
<!--form name="form1" method="post" action="cierra_tramite2_k.php"-->

    <table width="650" border="1" cellpadding="2" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td><div align="center"><font color="#FFFFFF" size="4"><b>RECEPCION/TRAMITE 
            DE DOCUMENTOS</b></font></div></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#e6eeff">
      <tr> 
        <td bgcolor="#cadbff"> <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr bgcolor="#e6eeff"> 
              <td height="15" colspan="5"><font color="#7777FF"><strong>INFORMACION 
                DOCUMENTO DE REFERENCIA</strong></font></td>
              <td height="15"><div align="right"><strong><font color="#0000A0" size="1"><? echo "Usuario : " . $cusuario?></font></strong></div></td>
            </tr>
            <tr> 
              <td width="129" height="15"><font color="#804040"><b>Tipo de Docto</b> 
                </font></td>
              <td width="171" height="15" > <font color="#804040"><? echo $rs[desc_tipo_documento]; ?> 
                </font></td>
              <td width="72" height="15"><font color="#804040"><strong>N&ordm; 
                Interno</strong> <b></b></font></td>
              <td height="15"> <font color="#804040"><font color="#804040"><? echo $rs[num_interno];?></font> 
                </font></td>
              <td height="15"><font color="#804040"><b>Medio</b></font></td>
              <td height="15"><font color="#804040"> 
                <? 
                If($rs["medio"]=="P")
                {
		   		echo "Papel";
				}
				else
				if ($rs["medio"]=="C")
				{
		   		echo "Copia";
		 		}
				else
				if ($rs["medio"]=="F")
		    	{
		    	echo "Fax";
		    	}   
				else
		 		{
	 		    echo "Video";
		 		}
		 		?>
                </font> </td>
            </tr>
            <tr> 
              <td width="129" height="18"><font color="#804040"><b>Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></font></td>
              <td width="171" height="18"> <font color="#804040"> 
                <?php $fec_doc=strtotime($rs["fecha_documento"]);
		             $fech_doc=date("d/m/Y",$fec_doc);
     				echo $fech_doc;?>
                </font></td>
              <td width="72" height="18"><font color="#804040"><b>N&ordm; Oficial<font size="4" face="Arial"> 
                </font></b></font></td>
              <td width="80" height="18"> <font color="#804040"><?php echo $rs[num_oficial];?> 
                </font></td>
              <td width="56"><font color="#804040"><b>Original</b></font></td>
              <td width="105"><font color="#804040"><font color="#804040"><? echo $rs[original];?></font></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr valign="middle"> 
              <td width="128" height="18"><font color="#804040"> <b>Estado del 
                Tr&aacute;mite</b> </font></td>
              <td width="172" height="18"><font color="#804040"><? echo $rs[desc_estado_tramite];?><b></b></font></td>
              <td width="73" height="18"> <font color="#804040"><strong>N&ordm; 
                Externo </strong></font></td>
              <td width="250" height="18"><font color="#804040"><font size="4" face="Arial"> 
                </font><font color="#804040"> </font><font color="#804040"><? echo $rs[num_externo]; ?></font><font size="4" face="Arial"> 
                </font></font></td>
              <!--<td width="57"><font color="#804040"><strong>Id.CodIbm</strong></font></td>
              <td width="105"><font color="#804040"><font color="#804040"><? echo $rs[idcodibm]; ?></font></font></td>-->
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr> 
              <td width="128" height="18"><font color="#804040"><b>Procedencia</b></font></td>
              <td width="171" height="20"> <font color="#804040"><? echo $rs[procedencia];?> 
                </font><font color="#804040">&nbsp;</font> <font color="#804040"> 
                <font color="#804040"> </font> </font></td>
              <td width="74"><font color="#804040"><b>Funcionario</b></font></td>
              <td width="250"><font color="#804040"><? echo $rs[funcproced];?></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr> 
              <td width="128" height="22"><font color="#804040"><b>Materia</b> 
                </font></td>
              <td width="505"> <font color="#804040"> <? echo $rs[materia];?> 
                </font></td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td> 
          <table width="100%" border="0">
            <tr> 
              <td><font color="#7777FF"><strong>TRAMITE SOLICITADO</strong></font></td>
            </tr>
          </table>
          <table width="100%" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td> <table width="100%" border="0" cellpadding="2" cellspacing="2">
                  <tr> 
                    <td width="18%"><strong><font color="#804040">Destinatario</font></strong></td>
                    <td width="29%" height="18"   valign="5" > <strong><font color="#804040"><? echo $rs[destino];?> 
                      </font></strong></td>
                    <td width="23%" height="18"   valign="5" ><strong><font color="#804040">Funcionario</font></strong></td>
                    <td width="30%" height="18"   valign="5" > <strong><font color="#804040"><? echo $rs[funcdestino];?> 
                      </font></strong></td>
                  </tr>
                  <tr> 
                    <td><strong><font color="#804040">Tipo Distribucion</font></strong></td>
                    <td   valign="5" height="18" > <strong><font color="#804040"><? echo $rs[desc_tipo_distribucion];?> 
                      </font></strong></td>
                    <td   valign="5" height="18" ><strong><font color="#804040">Dias 
                      Compromiso</font></strong></td>
                    <td   valign="5" height="18" > <strong><font color="#804040"><? echo $rs[dias_compromiso];?> 
                      </font></strong></td>
                  </tr>
                  <tr> 
                    <td><strong><font color="#804040">Tipo Compromiso</font></strong></td>
                    <td   valign="5" height="32" > <strong><font color="#804040"><? echo $rs[desc_tipo_compromiso];?> 
                      </font></strong></td>
                    <td   valign="5" height="32" ><strong><font color="#804040">Estado 
                      Compromiso</font></strong></td>
                    <td   valign="5" height="32" > <strong><font color="#804040"> 
                      <!--? echo $rs[desc_estado_compromiso];?-->
                      <font face="Arial"> 
                      <select name="Cbo_Estado_Compromiso" id="select2" >
                        <?
				 while($reg_estado_compromiso=mssql_fetch_array($rs_estado_compromiso)){
				?>
                        <option value=<? echo $reg_estado_compromiso[id_estado_compromiso] ?> > 
                        <? echo $reg_estado_compromiso[desc_estado_compromiso] ?> 
                        </option>
                        <?
								}
								?>
                      </select>
                      </font></font> </strong></td>
                  </tr>
                </table>
                <table width="100%" border="0">
                  <tr> 
                    <td><strong><font color="#804040">Observaciones</font></strong></td>
                    <td> 
					 <!--<textarea name ="observacion" cols="70" rows="4" onFocus="cambia_observacion(this);"><? echo $rs[observaciones]?></textarea>-->
					 <? // se cambia para que no se toque la observacion que viene //?>
					 <textarea name ="observacion" cols="70" rows="4"  readonly onFocus="cambia_observacion(this);"><? echo $rs[observaciones]?></textarea>
					 </td>
                  </tr>
                </table></td>
            </tr>
          </table>
          <br> <table border="0">
            <tr> 
              <td width="89" rowspan="2"><div align="center"> 
                  <input name="cmd_grabar" type="button" class="botones" onClick="abre_observacion_archivar();" value="Archivar">
                  <input type="hidden" name="cusuario" value="<? echo $usua;?>">
                  <input type="hidden" name="idusuario" value="<? echo $xx;?>" >
                  <input type="hidden" name="idfuncionario" value="<? echo $idfuncionario;?>" >
                  <input type="hidden" name="iddocum" value="<? echo $iddoc;?>" >
                  <input type="hidden" name="idseguim" value="<? echo $idseg;?>" >
                  <input type="hidden" name="compromiso">
                  <input type="hidden" name="origen"   value="<? echo $dedonde;?>" >
                  <input type="hidden" name="nom"	   value ="<? echo $txtnomina;?>">
				  <input type="hidden" name="txtagnor" value="<? echo $txtagnor;?>">
	  
                </div></td>
              <td width="273"> <div align="center"> 
                  <input name="cmd_responder1" type="button" class="boton_grande" onClick="respuesta_docto();" value="Responder sin documento">
                </div></td>
              <td width="273"> <div align="center"> 
                  <input name="cmd_deriva" type="button" class="boton_grande" onClick="deriva_docto();" value="Deriva sin documento">
                </div></td>
            </tr>
            <tr> 
              <td> <div align="center"> 
                  <input name="cmd_responder2" type="button" class="boton_grande" onClick="responde_con_docto();" value="Responder con documento">
                </div></td>
              <td> <div align="center"> 
                  <input name="cmd_grabar2" type="button" class="boton_grande" onClick="deriva_con_docto();" value="Deriva con documento">
                </div></td>
            </tr>
          </table></td>
      </tr>
    </table>
  	<div id="Layer1" style="position:absolute; width:355px; height:123px; z-index:1; left: 289px; top: 135px; visibility: hidden; overflow: auto; background-color: #E6EEFF; layer-background-color: #E6EEFF; border: 1px none #000000;" class="texto"> 
      <td><strong>Observación de archiva</strong>r </td> 
      <table width="93%" align="left"   border="1" bgcolor="#E6EEFF">
        <tr>
          <td height="20">
			  <div onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide');enviar_datos();">
              <div align="right"><strong>Archivar</strong></div>
            </div></td>
          <td>
		  <div onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide');">
		  <div align="left"><strong>Cancelar</strong></div>
		  </div></td>
          <textarea name ="observacion2" cols="75" rows="5" onKeyPress="valida_caracter();return CheckLength(120)" ></textarea>
        </tr>
      </table>
      
    </div>
					 					 
    </form>
  </center>
</body>
</html>
