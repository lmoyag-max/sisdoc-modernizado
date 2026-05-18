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
$fechaxx= date("Y/m/d H:i");
 //$txtagnor  arrastra el año ingresado en documentos recepcionados 
 // txtnomina arrastara la nomina buscada 
//echo "txtagnor " . $txtagnor . "nomina". $txtnomina ; 

$fecha_x = date("d-m-Y");
//echo "factura" .$iddoc . "detalle" .  $idseg ;
$rs_documento="exec documento_referencia_factura '" . $iddoc . "','" . $idseg . "'";
$qq = mssql_query($rs_documento,$cn); 
$rs=mssql_fetch_array($qq);
$r_observacion= $rs["observaciones"];


// sacando dependencia a la cual pertenece el usuario 
$u="select * from usuario where id_usuario=". $xx;
$ru=mssql_query($u);
$reu=mssql_fetch_array($ru);
$func=$reu["id_funcionario"];
$v='N';
 
$s="select id_dependencia from funcionario where vigencia is null and id_funcionario=" . $func;
// echo $s;
$r=mssql_query($s);
$dr=mssql_fetch_array($r);
$depusu=$dr["id_dependencia"];

// averiguando si la nomina está rececpcionada por administracion interna si esta recpcionada o derivada 
$re_a="select * from detalle_facturas where  id_estado_tramite  in (3,4,6)  and  id_detalle=" . $idseg;
$r_adm=mssql_query($re_a);
$reg_admin=mssql_fetch_array($r_adm);
$existe_rec=mssql_num_rows($r_adm);


//verifica si existe tema asociado por administracion interna
$f=0;
$re_t="select * from facturas where  id_tema_fact=". $f . " and id_factura =" . $iddoc ;
$r_tem=mssql_query($re_t);
$reg_tem=mssql_fetch_array($r_tem);
$existe_tema=mssql_num_rows($r_tem);
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
   
var cusu ='<?php echo $cusuario;?>';
var estado=<?php echo $estado;?>;
/*if (estado==2)
  alert("Debe recepcionar antes el trámite");
else   
  if (document.form1.compromiso.value== 3)
  {
*/
var sw="AF";
 var valor=<?php echo $idseg;?>;
 var usu=<?php echo $idusuario;?>;
 top.window.frame_consultas.location.href="frame_consultas.php?sw="+sw+"&seg="+valor+ "&usua="+usu;

if (document.form1.t.value==1)
{  
 <?php
  echo 'location.href="cierra_tramite2_factura.php?idusuario=' . $idusuario . "&flujook=" . $flujo  . "&compromiso=" . 3 .
 "&cusuario=" . $cusuario .  "&iddocum=" . $iddocum .  "&num_int=" . $numint . 
 "&idfuncionario=" . $idfuncionario .  "&accion=" . 1 .  "&origen=" . $dedonde . "&idseguim=" . $idseguim . "&txtnomina=" . $txtnomina. "&txtagnor=" . $txtagnor .'"';?> +"&observacion=" + document.form1.observacion.value +"&observacion2=" + document.form1.observacion2.value ; 
  }
  else 
  if (document.form1.compromiso.value== 4)
  {
 <?php
  echo 'location.href="cierra_tramite2_factura.php?idusuario=' . $idusuario . "&flujook=" . $flujo  . "&compromiso=" . 4 .
 "&cusuario=" . $cusuario .  "&iddocum=" . $iddocum .  "&num_int=" . $numint . 
 "&idfuncionario=" . $idfuncionario .  "&accion=" . 1 .  "&origen=" . $dedonde . "&idseguim=" . $idseguim . "&txtnomina=" . $txtnomina. "&txtagnor=" . $txtagnor .'"';?> +"&observacion=" + document.form1.observacion.value +"&observacion2=" + document.form1.observacion2.value  ;
  }
   else
     if (document.form1.compromiso.value== 5)
  {
   
 <?php
  echo 'location.href="cierra_tramite2_factura.php?idusuario=' . $idusuario . "&flujook=" . $flujo  . "&compromiso=" . 5 .  "&cusuario=" . $cusuario .  "&iddocum=" . $iddocum .  "&num_int=" . $numint .
 "&idfuncionario=" . $idfuncionario .  "&accion=" . 1 .  "&origen=" . $dedonde . "&idseguim=" . $idseguim . "&txtnomina=" . $txtnomina. "&txtagnor=" . $txtagnor .'"';?> +"&observacion=" + document.form1.observacion.value +"&observacion2=" + document.form1.observacion2.value ;
  }

  }
}
	  
function deriva_docto()
{
//var estado =<?php  echo $estado;?>; 
// se debe recepcionar automatico en caso que venga sin recepcionar 
 var sw="AF";
 var valor=<?php echo $idseg;?>;
 var usu=<?php echo $idusuario;?>;
 var fecha='<?php echo $fechaxx;?>';
  
 top.window.frame_consultas.location.href="frame_consultas.php?sw="+sw+"&seg="+valor+ "&usua="+usu +"&fecha="+fecha;

if (document.form1.t.value==1)
{  
 document.form1.submit();
 <?php
  echo 'location.href="deriva_sdoc_factura_prueba.php?idusuario=' . $idusuario . "&flujook=" . $flujo . 
 "&cusuario=" . $cusuario .  "&iddocum=" . $iddocum .  "&num_int=" . $numint . 
 "&idfuncionario=" . $idfuncionario .  "&accion=" . 1 .  "&origen=" . $dedonde . "&idseguim=" . $idseguim . "&txtnomina=" . $txtnomina. "&txtagnor=" . $txtagnor .'";';  ?> 
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
function abre_observacion_archivar()
{
document.form1.compromiso.value=document.form1.Cbo_Estado_Compromiso.value;
 if ((document.form1.Cbo_Estado_Compromiso.value==2)  )
   alert ("Antes de archivar debe cambiar estado de compromiso, el trámite no debe estar cerrado y debe estar recepcionada ");
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
function recepcion()
{  
 // ventana_tema();
  var sw="RF";
  //MM_showHideLayers('tema_factura','','hide');  
  var doc =<?php echo $iddoc;?>;
  var seg=<?php echo $idseg;?>;
  //alert (doc + "seg" +seg);
  var usuario =<?php echo $xx;?>;
  top.window.frame_consultas.location.href="frame_consultas.php?sw="+sw+"&doc="+doc+"&seg="+seg + "&usu=" + usuario;

  
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
function busca_destino()
{ 
 usu=<?php echo $depusu ;?>;
  var doc =<?php echo $iddoc;?>;
  var seg=<?php echo $idseg;?>;
  var usuario =<?php echo $xx;?>;
 //alert("entra" + usu );
  if (usu == 5)
  {
    conf=confirm("Está Seguro del tema seleccionado?")
    if (conf)
      {  
	    var  sw="IF";
		 tema =document.form3.Cbo_tema_facturas.value ;
        top.window.frame_consultas.location.href="frame_consultas.php?sw="+sw+"&doc="+doc+"&usu=" + usuario+ "&tema=" + tema ;
        MM_showHideLayers('tema_factura','','hide'); 
	  }
	 else 
	      MM_showHideLayers('tema_factura','','hide');   
 
  }
}
 function ventana_tema()
 {
 
 usu=<?php echo $depusu ;?>;
  var doc =<?php echo $iddoc;?>;
  var existe_recepcion =<?php echo $existe_rec;?>;
  if ((usu ==5)  && (existe_recepcion==0))
    {
	//var  sw="BFF";
     //top.window.frame_consultas.location.href="frame_consultas.php?sw="+sw+"&doc="+doc ;
	 //if (document.form1.temfac.value <>  0 )
       MM_showHideLayers('tema_factura','','show'); 
	   
	}
  else   if ((usu==5 )&& (existe_recepcion== 1))
	  {   MM_showHideLayers('tema_factura','','hide');   
 }
 }

function salir_tema()
 {
   var tema =<?php echo $existe_tema;?>;
   
   if (tema != 0)
    MM_showHideLayers('tema_factura','','hide'); 
	else 
     { alert ("Debe ingresar tema ");
       MM_showHideLayers('tema_factura','','show'); 
	  } 
 }


</script>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC" topmargin="0" >
<center>
<form name="form1" method="post" >
    <table width="650" border="1" cellpadding="2" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td><div align="center"><font color="#FFFFFF" size="4"><b>RECEPCION/TRAMITE 
            DE FACTURAS</b></font></div></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#e6eeff">
      <tr> 
        <td bgcolor="#cadbff"> <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr bgcolor="#e6eeff"> 
              <td height="15" colspan="4"><font color="#7777FF"><strong>INFORMACION 
                DOCUMENTO DE REFERENCIA</strong></font></td>
            </tr>
            <tr> 
              <td width="130" height="15"><font color="#804040"><b>Tema factura</b> 
                </font></td>
              <td width="169" height="15" > <font color="#804040"><? echo $rs[desc_tema]; ?> 
                </font></td>
              <td width="73" height="15"><font color="#804040"><strong>N&ordm; 
                Factura</strong> <b></b></font></td>
              <td height="15"> <font color="#804040"><font color="#804040"><? echo $rs[num_factura];?></font> 
                </font></td>
            </tr>
            <tr> 
              <td width="130" height="18"><font color="#804040"><b>Fecha Factura<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></font></td>
              <td width="169" height="18"> <font color="#804040"> 
                <?php $fec_doc=strtotime($rs["fecha_factura"]);
		             $fech_doc=date("d/m/Y",$fec_doc);
     				echo $fech_doc;?>
                </font></td>
              <td width="73" height="18"><font color="#804040"><b>Proveedor<font size="4" face="Arial"> 
                </font></b></font></td>
              <td width="251" height="18"> <font color="#804040"><?php echo $rs[razon_social];?> 
                </font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr valign="middle"> 
              <td width="130" height="34"><font color="#804040"> <b>Estado del 
                Tr&aacute;mite</b> </font></td>
              <td width="170" height="34"><font color="#804040"><? echo $rs[desc_estado_tramite];?><b></b></font></td>
              <td width="71"><font color="#804040"><b>Monto</b></font></td>
              <td width="252"><font color="#804040"><font color="#804040"><? echo $rs[num_factura];?></font></font></td>
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
              <td width="128" height="22"><font color="#804040"><b>Descripción</b> 
                </font></td>
              <td width="505"> <font color="#804040"> <? echo $rs[descripcion];?> 
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
					 <? // se cambia para que no se toque la observacion que viene //?>
					 <textarea name ="observacion" cols="70" rows="4"  readonly onFocus="cambia_observacion(this);"><? echo $rs[observaciones]?></textarea>
					 </td>
                  </tr>
                </table></td>
            </tr>
          </table>
          <br> 
          <table border="0">
            <tr> 
              <td width="75" rowspan="2"><div align="center"> 
                  <input type="hidden" name="cusuario" value="<? echo $usua;?>">
                  <input type="hidden" name="idusuario" value="<? echo $xx;?>" >
                  <input type="hidden" name="idfuncionario" value="<? echo $idfuncionario;?>" >
                  <input type="hidden" name="iddocum" value="<? echo $iddoc;?>" >
                  <input type="hidden" name="idseguim" value="<? echo $idseg;?>" >
                  <input type="hidden" name="compromiso">
                  <input type="hidden" name="t">
                  
                  <input type="hidden" name="temafac"   value ="<?php echo 0 ;?>">
                  <input type="hidden" name="origen"   value="<? echo $dedonde;?>" >
                  <input type="hidden" name="nom"	   value ="<? echo $txtnomina;?>">
                  <input type="hidden" name="txtagnor" value="<? echo $txtagnor;?>">
                </div></td>
              <td width="182"> <div align="center"> 
                  <input name="cmd_grabar2" type="button" class="botones" onClick="recepcion();" value="Recepcionar">
                </div></td>
              <td width="190"><input name="cmd_deriva" type="button" class="boton_grande" onClick="deriva_docto();" value="Deriva sin documento"></td>
              <td width="179"> <div align="center"> 
                  <input name="cmd_grabar" type="button" class="botones" onClick="abre_observacion_archivar();" value="Archivar">
                </div></td>
            </tr>
            <tr> 
              <td colspan="2"> <div align="center"> </div></td>
              <td> <div align="center"> </div></td>
            </tr>
          </table></td>
      </tr>
    </table>
  	<div id="Layer1" style="position:absolute; width:355px; height:123px; z-index:1; left: 289px; top: 135px; visibility: hidden; overflow: auto; background-color: #E6EEFF; layer-background-color: #E6EEFF; border: 1px none #000000;" class="texto"> 
      <td><strong>Observación de archiva</strong>r </td> 
      <table width="93%" align="left"   border="1" bgcolor="#E6EEFF">
        <tr>
          <td height="20">
			  <div onClick="MM_showHideLayers('Layer1','','hide');MM_showHideLayers('Layer1','','hide');enviar_datos()">
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
	
  <div id="tema_factura" style="position:absolute; width:384px; height:100px; z-index:1; left: 359px; top: 68px; visibility: hidden; overflow: auto; background-color: #E6EEFF; layer-background-color: "#3399ff"; border: 1px none #000000;" class="texto"> 
    <form name="form3">
      Tema de Factura 
      <select name="Cbo_tema_facturas" class="combo" id="Cbo_tema_facturas" onChange="busca_destino()";>
                            <?
						   while($reg=mssql_fetch_array($rs_tema_factura)){
							?>
                            <option value=<? echo $reg[id_tema] ?> ><? echo $reg[desc_tema] ?></option>
                            <?
							}
						  ?>
                          </select>
      <td  width="20" height="39" ><font color="#000000">
        <input name="cancelar" type="button" id="cancelar" value="Cancelar"  onclick="salir_tema()">
        </font></td>						  
    </form>
  </div>
  </center>
</body>
</html>
