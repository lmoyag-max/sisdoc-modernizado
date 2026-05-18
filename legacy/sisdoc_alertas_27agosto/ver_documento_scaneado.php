<?
global $REMOTE_ADDR;
include("conexion_bd.php");
// buscando el documento en la tabla de documentos //
// buscando el documento en latabla de archivo_digital//
$sub_ip = substr($REMOTE_ADDR,0,6);

if ($sub_ip=="172.16") 
{
  $procedencia = "";
 }
 else
 {
  $procedencia = "http://163.247.51.38";
 }



/*echo ' ver_documento' ."<br>";
echo  'cusuario=' . $cusuario . 'idusuario=' . $idusuario ."<br>"; 
echo 'iddocum=' . $iddocum . '&idseguim=' . $idseguim ."<br>";
echo "avanza " . $si_avanza .   "  xx  " . $xx   .  "fecha ini " . $Txt_fecha_ini . "fecha fin " . $Txt_fecha_fin  . "Cbo_Tipo_Docto" . $Cbo_Tipo_Docto . "<br>";
echo "Txtinterno " . $TxtInterno . "Txtexterno " . $TxtExterno . "Txtoficial " . $TxtOficial . "<br>";
echo "materia " .  $TxtMateria . "<br>";
echo "Cbo_Procedencia" .$Cbo_Procedencia ."Cbo_Destinatario" . $Cbo_Destinatario ;
*/	
// busca documentos en archivo_digital //
  $rs_docum ="select * from archivo_digital where id_documento = $iddocum ";
  $rs_doc =mssql_query($rs_docum);
  $tot_doc =mssql_num_rows($rs_doc);
  $con_archivo="0";
if ($tot_doc ==0)
{
$con_archivo="1" ;
/*echo "antes de ir a doc_enc.php" . "<br>";
echo  'cusuario=' . $cusuario . 'idusuario=' . $idusuario ."<br>"; 
echo 'iddocum=' . $iddocum . '&idseguim=' . $idseguim ."<br>";
echo "avanza " . $si_avanza .   "  xx  " . $xx   .  "fecha ini " . $Txt_fecha_ini . "fecha fin " . $Txt_fecha_fin  . "Cbo_Tipo_Docto" . $Cbo_Tipo_Docto . "<br>";
echo "Txtinterno " . $TxtInterno . "Txtexterno " . $TxtExterno . "Txtoficial " . $TxtOficial . "<br>";
echo "materia " .  $TxtMateria . "<br>";
echo 'Cbo_Procedencia'  . $Cbo_Procedencia . "Cbo_Destinatario" . $Cbo_Destinatario;
*/	
               echo '<html><body onload="javascript:document.form1.submit()">';
               echo '<form name="form1" method="post" action="doc_enc_archivo.php">';
	
	echo '<input type="hidden" name="idusuario"     value="' .$idusuario . '">'."\n";
	echo '<input type="hidden" name="cusuario"      value="' . $cusuario . '">'."\n";
	echo '<input type="hidden" name="idfuncionario" value="' . $idfuncionario . '">'."\n";
	echo '<input type="hidden" name="xx" value="' . $xx . '">'."\n";

	echo '<input type="hidden" name="TxtInterno"    		value="' . $TxtInterno . '">'."\n";
	echo '<input type="hidden" name="TxtOficial "           value="' . $TxtOficial . '">'."\n";
	echo '<input type="hidden" name="TxtExterno "           value="' . $TxtExterno. '">'."\n";
	echo '<input type="hidden" name="arreglo"               value="' . $arreglo. '">'."\n";
	echo '<input type="hidden" name="tipo_procedencia"      value="' . $tipo_procedencia . '">'."\n";
	echo '<input type="hidden" name="tipo_destino"          value="' . $tipo_destino . '">'."\n";
	echo '<input type="hidden" name="Cbo_Procedencia"       value="' . $Cbo_Procedencia . '">'."\n";
	echo '<input type="hidden" name="Cbo_Destinatario"      value="' . $Cbo_Destinatario . '">'."\n";
	echo '<input type="hidden" name="Cbo_Tipo_Docto"        value="' . $Cbo_Tipo_Docto . '">'."\n";
	echo '<input type="hidden" name="TxtMateria"            value="' . $TxtMateria. '">'."\n";
    echo '<input type="hidden" name="Txt_fecha_ini"         value="' . $Txt_fecha_ini. '">'."\n";
	echo '<input type="hidden" name="Txt_fecha_fin"         value="' . $Txt_fecha_fin. '">'."\n";
	echo '<input type="hidden" name="con_archivo"           value="' . 1 . '">'."\n";
	echo '<input type="hidden" name="si_avanza"             value="' . $si_avanza. '">'."\n";
    echo '<input type="hidden" name=" $dependencia_usuario" value="' . $dependencia_usuario . '">'."\n";
	echo '<input type="hidden" name=" $sw_cons"             value="' . $sw_cons . '">'."\n";
	echo '<input type="hidden" name=" $con_archivo"         value="' . $con_archivo . '">'."\n";

}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Ver documento scaneado </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
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
function ver_archivo(doc)
{
iddoc = doc;
MM_showHideLayers('layer_archivo,'','show');
}

/*function CheckLength(length) {
if (window.event.srcElement.value.length >= length) {
   alert('El Máximo de caracteres es 2500');
   return false;                         
}
}

function ver_archivo(doc)
{
  document.form1.documento.value = doc;
  //document.form1.action="ver_comentarios.php";
  document.form1.action="ver.php";
  document.form1.submit();
}
*/

//-->
</script>

</head>
<body>
<form name="form1" method="POST" >
 <!-- <input type ="hidden" name="iddocum">-->
  
  <? if ($tot_doc <> 0){?>
    <?php while ($reg_doc= mssql_fetch_array($rs_doc)){?>
    <tr> 
      <?php 
/*	    $fec_doc=strtotime($reg_doc["fec_documento"]);
		$fech_doc = date("d/m/Y",$fec_doc);
	
//	    $nombre = $reg_doc[nom_documento] . ' '. $reg_doc[num_version]. ' '.$fech_doc;
	    $nombre = $reg_doc[nom_documento] . ' '.$fech_doc;
		//$posicion = strpos($reg_doc[nom_documento],".doc");
		//$archivo  = substr($reg_doc[nom_documento],0,$posicion) . "_" . $reg_doc[num_version] . ".doc";
		$archivo  = $reg_doc[nom_documento] ;
        $ruta = $procedencia . $reg_doc[ubi_documento] . $archivo;
		$documento=$reg_doc[id_documento];
	*/
        $nombre = $reg_doc[archivo];
	    $ruta = $procedencia . $reg_doc[ruta];
		$documento=$reg_doc[id_documento];
	
	   ?>
      <td width="204" height="46"><?php echo '<a href="' . $ruta .  '" target="_blank">' . $nombre . "</a></td>\n"; ?> 
      <td width="134"> <a href="#" onClick="ver_documento(<?php echo $documento;?>)">Ingresar  Comentarios</a>>
    </tr>
    <?php }?>
  </table>
  <?php }
 else
 // no existe la comision  
  {?>
  <td width="96">&nbsp;</td>
  
  <?php } ?>
   </form>
  
<?php mssql_close($cn);?>
<div id="layer_archivo"  style="position:absolute; width:671px; height:291px; z-index:1; left: 18px; top:118px; visibility: hidden; overflow: auto; background-color: #95B8DB; layer-background-color: #95B8DB; border: 1px none #000000;" class="texto"><em><strong>Comentarios</strong></em> 
  <form name="form_layer">
    <font color="#000000" face="Arial, Helvetica, sans-serif"> 
    <textarea name="txt_comentario" cols="80" rows="10"  class="entradas" onKeyPress="return CheckLength(2500)" ></textarea>
    </font> 
  </form>
  <div align ="center"> 
    <input  type="button" name="submit"class="botones" onClick="MM_showHideLayers('layer_archivo,'','hide');imprimir_archivo()"  value="Aceptar">
    <input  type="button" name="submit"class="botones" onClick="MM_showHideLayers('layer_archivo','','hide')" value="Cancelar">
  </div>
</div>
</body>
</html>
  	
