<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
$usua=$cusuario;
$xx=$idusuario;
$if=$idfuncionario;
//echo "num_doc". $num_doc ;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>muestra documento a relacionar</title>


<script language="JavaScript" type="text/javascript">

var usuario ="<?php echo $usua;?>";
var funcionario=<?php echo $if;?>;
var d = <?php echo $xx;?>;
function busca_docto1(num_doc)
{
// busca el documento 1 para la relacion  con otro 
  
    MM_showHideLayers('Layer_busca','','show'); 
	
   // document.frames['frm'].location="busca_docto_a_relacionar.php?cusuario="<?php echo $cusuario;?>+"&idusuario="+<?php echo $idusuario;?>+ "&idfuncionario="+<?php echo $idfuncionario;?>+"&flujook="+<?php echo $flujook;?>+"&num_int="+<?php echo $num_int?>+"&sw_cons="+<?php echo 1;?>+ "&avanza="+<?php echo 1;?>;
    var c =usuario;
    var f= funcionario;
	var fl =8;
	var id =d;
	var num_int=0;
	var sw=1;
	var av =1;
if (num_doc==1)
{
    document.frames['frm'].location="busca_docto_a_relacionar.php?cusuario="+c+"&idfuncionario="+f+"&idusuario="+id+"&flujook="+fl+"&num_int="+num_int+"&sw_cons="+sw+"&avanza="+av+"&num_doc="+num_doc;
}
else 
 {   document.frames['frm2'].location="busca_factura_a_relacionar.php?cusuario="+c+"&idfuncionario="+f+"&idusuario="+id+"&flujook="+fl+"&num_int="+num_int+"&sw_cons="+sw+"&avanza="+av+"&num_doc="+num_doc;
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
function muestra_documento_a_relacionar(doc,seg,num_doc)   
// viene el llamado desde doc_enc_a_relacionar al momento de dar aceptar una vez seleccionado el documento
{   MM_showHideLayers('Layer_busca','','hidden'); 
	// va a buscar los datos a la tabla para desplegar los datos en pantalla 
 
    var valor="BF";
	top.window.frame_consultas.location.href="frame_consultas.php?sw="+valor+"&docu="+doc+"&segu=" + seg+"&num_doc="+num_doc;
 
}
function salir()
{
    MM_showHideLayers('Layer_busca','','hidden'); 
}
 function validar_doctos()
 {  
 // validando fechas de los documentos 
 var fec_doc1 = devuelve_fecha(document.form1.fecha_documento1.value);
 var fec_doc2 = devuelve_fecha(document.form1.fecha_documento2.value);
 
alert("fecha1" + fec_doc1 + "fecha 2 " + fec_doc2 );
 if (fec_doc1 > fec_doc2)
   {alert("documento 2 debe  ser de fecha  posterior al del documento 1");}
 else   // validando que no estén en blanco 
  if (document.form1.doc1.value=='' && document.form1.doc2.value=='' )
   { alert("Para relacionar debe existir datos de documento 1 y documento 2 ");}
  else   // validando que los documentos sean distintos 
  if (document.form1.doc1.value==document.form1.doc2.value )
 {
    alert("Documento 1 no debe ser igual a Documento 2 ");
	document.form1.desc_tipo_documento2.value='';
	document.form1.num_interno2.value='';
	document.form1.medio2.value='';
	document.form1.fecha_documento2.value='';
	document.form1.num_oficial2.value='';
	document.form1.original2.value='';
	document.form1.num_externo2.value='';
	document.form1.procedencia2.value='';
	document.form1.materia2.value='';
	document.form1.doc2.value='';
 } 
else 
    if (document.form1.doc1.value!=document.form1.doc2.value )
    {  
	 // relacionando documentos previa validacion que el primero tenga algun destino que el usuario tiene acceso 
   var valor="BR";
 	 var doc1 =document.form1.doc1.value;
	 var doc2 =document.form1.doc2.value;
	     var idusuario=<?php echo $xx;?>;

	 top.window.frame_consultas.location.href="frame_consultas.php?sw="+valor+"&doc1="+doc1+"&doc2=" + doc2+"&idusuario=" + idusuario;
    }
  
}

function devuelve_fecha(fecha){

fecha = fecha.replace(/[-]/g, "/");
fecha = new Date(fecha);
return fecha
}

</script>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC" topmargin="0" >
<center>

<form name="form1" method="post" >
    <table width="700" border="1" cellpadding="2" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="692"><div align="center"><font color="#FFFFFF" size="4"><b>Documentos a 
            relacionar </b></font></div></td>
      </tr>
    </table>
    <table width="801" border="1" cellpadding="1" cellspacing="0" bgcolor="#e6eeff">
      <tr> 
        <td width="795" bgcolor="#cadbff"> 
          <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr bgcolor="#e6eeff"> 
              <td height="15" colspan="5"><font color="#7777FF"><strong>INFORMACION 
                DOCUMENTO DE REFERENCIA (documento 1 )</strong></font></td>
              <td height="15"><div align="right"><strong><font color="#0000A0" size="1"><? echo "Usuario : " . $cusuario?></font></strong></div></td>
            </tr>
            <tr> 
              <td width="90" height="15"><font color="#804040"><b>Tipo de Docto</b> 
                </font></td>
              <td width="376" height="15" > <font color="#804040"> 
                <input name="desc_tipo_documento1" type="text" id="desc_tipo_documento1"  readonly size="60" maxlength="60">
                </font></td>
              <td width="64" height="15"><font color="#804040"><strong>N&ordm; 
                Interno</strong> <b></b></font></td>
              <td height="15"> <font color="#804040">
                <input name="num_interno1" type="text" id="num_interno1" readonly size="12" maxlength="12">
                </font></td>
              <td height="15"><font color="#804040"><b>Medio</b></font></td>
              <td height="15"><font color="#804040"> 
                <input name="medio1" type="text" id="medio1" size="15" readonly maxlength="15">
                </font> </td>
            </tr>
            <tr> 
              <td width="90" height="18"><font color="#804040"><b>Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></font></td>
              <td width="376" height="18"> <font color="#804040">
                <input name="fecha_documento1" type="text" id="fecha_documento1" readonly size="12" maxlength="12">
                </font></td>
              <td width="64" height="18"><font color="#804040"><b>N&ordm; Oficial<font size="4" face="Arial"> 
                </font></b></font></td>
              <td width="78" height="18"> <font color="#804040">
                <input name="num_oficial1" type="text" id="num_oficial1" readonly size="12" maxlength="12">
                </font></td>
              <td width="53"><font color="#804040"><b>Original</b></font></td>
              <td width="103"><font color="#804040">
                <input name="original1" type="text" id="original1" readonly size="2" maxlength="2">
                </font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr valign="middle"> 
              <td width="82" height="18"><font color="#804040"><b>Procedencia</b> 
                </font></td>
              <td width="363" height="18"><font color="#804040">
                <input name="procedencia1" type="text" id="procedencia1"  readonly size="60" maxlength="60">
                <b></b></font></td>
              <td width="62" height="18"> <font color="#804040"><strong>N&ordm; 
                Externo </strong></font></td>
              <td width="194" height="18"><font color="#804040"><font size="4" face="Arial"> 
                </font><font color="#804040"> </font>
                <input name="num_externo1" type="text" id="num_externo1" readonly size="12" maxlength="12">
                <font size="4" face="Arial"> </font></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr> 
              <td width="90" height="22"><font color="#804040"><b>Materia</b> 
                </font></td>
              <td width="549"> <font color="#804040"> 
                <textarea name="materia1" readonly cols="100" rows="3" id="materia1"></textarea>
                </font></td>
              <td width="140"><input type="button" name="busca1" value="Buscar Docto 1 " onclick ='busca_docto1(1)'></td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td height="200"> 
          <table width="100%" border="0">
            <tr> 
              <td><table width="100%" border="0" cellspacing="1" cellpadding="2">
                  <tr bgcolor="#e6eeff"> 
                    <td height="15" colspan="6"><font color="#7777FF"><strong>INFORMACION 
                      DOCUMENTO DE REFERENCIA (documento 2)</strong></font></td>
                  </tr>
                  <tr> 
                    <td width="87" height="15"><font color="#804040"><b>Tipo 
                      de Docto</b> </font></td>
                    <td width="370" height="15" > <font color="#804040">
                      <input name="desc_tipo_documento2" type="text" id="desc_tipo_documento2"  readonly size="60" maxlength="60">
                      </font></td>
                    <td width="68" height="15"><font color="#804040"><strong>N&ordm; 
                      Interno</strong> <b></b></font></td>
                    <td height="15"> <font color="#804040">
                      <input name="num_interno2" type="text" id="num_interno2" readonly size="12" maxlength="12">
                      </font></td>
                    <td><font color="#804040"><b>Medio</b></font></td>
                    <td height="15"><font color="#804040">
                      <input name="medio2" type="text" id="medio2" size="15" readonly maxlength="15">
                      </font></td>
                  </tr>
                  <tr> 
                    <td width="87" height="18"><font color="#804040"><b>Fecha 
                      Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></font></td>
                    <td width="370" height="18"> <font color="#804040">
                      <input name="fecha_documento2" type="text" id="fecha_documento2" readonly size="12" maxlength="12">
                      </font></td>
                    <td width="68" height="18"><font color="#804040"><b>N&ordm; 
                      Oficial<font size="4" face="Arial"> </font></b></font></td>
                    <td width="81" height="18"> <font color="#804040">
                      <input name="num_oficial2" type="text" id="num_oficial2" readonly size="12" maxlength="12">
                      </font></td>
                    <td width="49"><font color="#804040"><b>Original</b></font></td>
                    <td width="103"><font color="#804040">
                      <input name="original2" type="text" id="original2" readonly size="2" maxlength="2">
                      </font></td>
                  </tr>
                </table>
                <table width="100%" border="0" cellpadding="2" cellspacing="1">
                  <tr valign="middle"> 
                    <td width="85" height="25"><font color="#804040"><b>Procedencia</b> 
                      </font></td>
                    <td width="403" height="25"><font color="#804040">
                      <input name="procedencia2" type="text" id="procedencia2"  readonly size="60" maxlength="60">
                      <b></b></font></td>
                    <td width="80" height="25"> <font color="#804040"><strong>N&ordm; 
                      Externo </strong></font></td>
                    <td width="200" height="25"><font color="#804040"><font size="4" face="Arial"> 
                      </font><font color="#804040"> </font><font color="#804040">
                      <input name="num_externo2" type="text" id="num_externo2" readonly size="12" maxlength="12">
                      </font><font size="4" face="Arial"> </font></font></td>
                  </tr>
                </table>
                <table width="100%" border="0" cellpadding="2" cellspacing="1">
                  <tr> 
                    <td width="85" height="22"><font color="#804040"><b>Materia</b> 
                      </font></td>
                    <td width="557"> <font color="#804040">
                      <textarea name="materia2" readonly cols="100" rows="3" id="materia2"></textarea>
                      </font></td>
                    <td width="131"><input type="button" name="busca2" value="Buscar Docto 2" onclick ="busca_docto1(2)"></td>
                  </tr>
                </table>
                <font color="#7777FF">&nbsp;</font></td>
            </tr>
          </table>
          <br>
        </td>
      </tr>
    </table>
  	<table width="65%" border="1">
      <tr>
        <td  bgcolor="#e6eeff"><div align="center"> 
            <input name="cmd_grabar2" type="button" class="boton_grande" value="Relacionar documentos" onClick="validar_doctos()">
          </div></td>
      </tr>
    </table>
   <input type="hidden"  name="doc1">
   <input type="hidden"  name="doc2">
   <input type="hidden" name="destino_ok"  value =<?php echo 0;?>>
   <input type="hidden" name="procedencia_ok"  value =<?php echo 0;?>>
   
  </form>
  <div id="Layer_busca" style="position:absolute; width:830px; height:440px; z-index:1; left: 157px; top: 76px; visibility: hidden; overflow: auto; background-color: #3399CC; layer-background-color: #3399CC; border: 1px none #000000;" class="texto"> 
    <form name="form2">
      <iframe name="frm" border="1" width="810px" height="420px" ></iframe>
      <iframe name="frm2" border="1" width="810px" height="420px" ></iframe>
    </form>
  </div>
</center>
</body>
</html>
