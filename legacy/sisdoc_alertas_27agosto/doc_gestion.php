<?php
include("variables.php");
include("conexion_bd.php");
include("funciones.php");

$Usuario=$cusuario;
$xx = $idusuario;
$fun= $idfuncionario;
$desc  =$arreglo;
$sw = 0;
//txtdias dias solicitados en pagina anterior (busca_gestion.php)//

$dia = substr($fecha_ini,0,2);
$mes = substr($fecha_ini,3,2);
$año = substr($fecha_ini,6,4);
$fechaini = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

$dia = substr($fecha_fin,0,2);
$mes = substr($fecha_fin,3,2);
$año = substr($fecha_fin,6,4);
$fechafin = date("Y/m/d H:i", mktime(23, 59,59, $mes, $dia, $año));

$consulta="exec consulta_gestion2 '" . $txtdias.  "','" . $fechaini. "','" . $fechafin. "'";
$rs_doc=$consulta;
$rs_documento=mssql_query($rs_doc);   
$Totreg = mssql_num_rows($rs_documento);
$NumPag= intval($Totreg/10);

if(fmod($Totreg,10)==0) 
  { 
  $NumPag = $NumPag;
  }
else
  {
  $NumPag=$NumPag + 1;
  }		  
  
if (($Totreg==0) and ($sw==0))
 {
echo '<script>';
echo 'alert("No Existen Documentos")';
echo '</script>';

	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="busca_gestion.php">';
	echo '<input type="hidden" name="idusuario" value="' . $xx . '">';
	echo '<input type="hidden" name="cusuario" value="' . $Usuario . '">';
	echo '<input type="hidden" name="idfuncionario" value="' . $fun . '">';
	echo '<input type="hidden" name="flujook" value="' . 1 . '">';
	//echo '<input type="hidden" name="sw_cons" value="' . $cons . '">';
	echo "</form></body></html>";

}
else
{
 if ((sw==0) and ($Totreg <>0))
 {
?> 
<html>
<head>
<title>Formulario gestion </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript">
<!--

var layer_activo=-1;

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

function MM_showHideLayers() 
{ //v3.0
  var a,i,p,v,obj,args=MM_showHideLayers.arguments;
   ocultalayer(args[3],args[4]);
  for (i=0; i<(args.length-4); i+=3) 
  if ((obj=MM_findObj(args[i]))!=null) 
    {
	v=args[i+2];
    if (obj.style) 
	    { obj=obj.style; v=(v=='show')?'visible':(v='hide')?'hidden':v; }
    obj.visibility=v; 
	}		
  }
  
function showHideLayer_doc() 
{
  var i,p,v,obj,args=showHideLayer_doc.arguments;
  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
    if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
    obj.visibility=v; }
}
  
  
//-->
function ocultalayer(idlay,totlay){
var idlay, a;


	for (a=1; (a<=totlay); a++){
		nomlay = "layer" + a;		document.all[nomlay].style.visibility="hidden";
		//	queda pendiente esta consulta --	if (navigator.appName == "Microsoft Internet Explorer") 
			
         }
	}

  function docreferencia(doc,seg)
  {
    
   	var nuevasel = "D";
	var valor ="G";	
    var dd =doc;
	var sg=seg; 
	//alert('documento' + doc + 'seguimiento' + seg);
	top.window.frame_consultas.location.href="frame_consultas.php?cod="+nuevasel+"&sw="+valor+"&docu="+ dd + "&segu="+ sg ;
	
   showHideLayer_doc('layer_doc','','show');
  }
	
//-->
</script>
<!-- link rel="stylesheet" href="subSilver.css" type="text/css" -->
<style type="text/css">
<!--
/* Main table cell colours and backgrounds */
td.row1	{ background-color: #EFEFEF; }
td.row2	{ background-color: #DEE3E7; }
td.row3	{ background-color: #D1D7DC; }

/* row cells - the blue and silver gradient backgrounds */

tr.columna1	{
	font-weight : bold;
	background-color: #C3D6E6;
}

tr.columna2	{
	background-color: #EFEFEF;
}

-->
</style>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
<STYLE type=text/css>

A { COLOR: blue; FONT-FAMILY: verdana,arial,helvetica,sans-serif; FONT-SIZE: 11px; TEXT-DECORATION: none
}

A:hover { COLOR: red; TEXT-DECORATION: none
}
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000" topmargin="0">
<center >
    <form name="formulario1" method="post" >
	<table width="66%" border="0">
      <tr> 
        <td> 
          <p align="center"><b><font size="4" color="#0000FF">RESULTADO DE BUSQUEDA</font></b></font></p>
        </td>
      </tr>
      <tr> 
         
        <td height="39" > 
          <p align="right"></p><div align="right"><strong><font color="#000000" ><?php echo '<a href="busca_gestion.php?cusuario=' . $cusuario .
			'&idusuario=' . $xx . '&idfuncionario=' . $fun . '"><u>Volver</u></a>'; ?></font></strong></div></p></td>
      </tr>
    </table>
    <table width="650" border="0">
      <tr> 
	    <td><div align="left"><strong><?php echo "Total de Páginas : " . $NumPag ?></strong></div></td>
        <td><div align="right"><strong><?php echo "Total Registros : " . $Totreg ?></strong></div></td>
      </tr>
    </table>
    
    <table width="650"  border="0">
      <tr> 
        <td height="25" > 
          <?php
		  echo "<div align='left'><b>";
     		        for ($i = 1; $i <= $NumPag; $i++)
			 {
			
		 echo "<img src='botones/boton" . $i . ".gif' width='44' height='16'". 
 "onClick=\"MM_showHideLayers('layer" . $i . "','','show',$i, $NumPag)\">"; 
            
			 } 
			 echo "</b></div>";
		    ?>
        </td>
      </tr>
    </table>
    <td> <input type="hidden" name="Totreg22" value="<?php echo $Totreg; ?>"> 
      <input type="hidden" name="NumLayer22" value="<?php echo $NumLayer; ?>"> 
	      <input type="hidden" name="idusuario" value="<? echo $xx;?>">
      <input type="hidden" name="docum">
		   <input type="hidden" name="seguim">
	</td> 
    <?php 
	      $Corre = 0;
		  $NumLayer = 0;
		  while($reg_documento = mssql_fetch_array($rs_documento)) { 
		  
		  
		  if(fmod($Corre,10)==0) 
		  { 
		  $NumLayer = $NumLayer + 1;
		  if($NumLayer==1){
  		   echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:8px; top:120px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility:visible">';
		   
		   }
		   else
		   {
		   echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:8px; top:120px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: hidden">';
		   }
		   	  
	      
	echo "<table width='670' border='1' cellpadding='1' cellspacing='0' bgcolor='#E6EEFF' >"; 
	echo '<tr bgcolor="#6699FF" >';
    echo '<td width="5%" height="33"><strong><font color="#FFFFFF" size="2">Num</font></strong></td>';
    echo '<td width="6%" height="33"><strong><font color="#FFFFFF" size="2">Ver doc. </font></strong></td>';
	echo '<td width="7%" height="33"><strong><font color="#FFFFFF" size="2">Id. Documento</font></strong></td>';
    echo '<td width="5%" height="33"><strong><font color="#FFFFFF" size="2">Días</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Interno</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Oficial</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Tipo Documento</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Procedencia</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Funcionario</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Destino</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Funcionario</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Estado Trámite</font></strong></td>';
    echo '</tr>';
		 
		  
		  }
		  $Corre =  $Corre + 1;
		  ?>
    
	<tr>
        <!--<td align="left" valign="middle" width="3%"><font size="2"><!--?php echo $Corre;?></font></td>-->
		<td><?php echo $Corre;?></font></td>
		
      <td align="left" valign="middle" width="5%"> <input type="hidden" name="cusuario22" value="<? echo $cusuario;?>"> 
        <!--?php echo '<a href="docreferencia.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . 
	 '&iddocum=' . $reg_documento["id_documento"] . '&idseguim=' . $reg_documento["id_seguimiento"] . '&xdias=' . $txtdias .'">Ver</a>'; ?-->
        <?php 
	    $doc = $reg_documento["id_documento"];
		$seg = $reg_documento["idseguimiento"];?>
        <a href="#" onClick="docreferencia(<?php  echo $doc;?>,<?php  echo $seg; ?>)">Ver</a> 
      </td>
		
		<td align="left" valign="middle" width="8%"><font size="2">
          <?php echo $reg_documento["id_documento"];?></font>
        </td>
        <td align="left" valign="middle" width="8%"><font size="2">
          <?php echo $reg_documento["dias"];?></font>
        </td><td align="left" valign="middle" width="5%"><font size="2">
          <?php echo $reg_documento["num_interno"];?></font>
        </td>
		 <td align="left" valign="middle" width="5%"><font size="2">
          <?php echo $reg_documento["num_oficial"];?></font>
        </td>
	    <td align="left" valign="middle" width="9%"><font size="2">
          <?php echo $reg_documento["desc_tipo_documento"];?></font>
        </td>
         
      <td align="left" valign="middle" width="11%"><font size="2"> 
	  <?php 
	    if ($reg_documento["procedencia"]=="") {
			    $reg_documento["procedencia"]="&nbsp";} 
	  echo $reg_documento["procedencia"];?></font> 
      </td>
        <td align="left" valign="middle" width="11%"><font size="2">
          <?php 
		  if ($reg_documento["funcproced"]=="") {
			    $reg_documento["funcproced"]="&nbsp";} 
		  echo $reg_documento["funcproced"];?></font>
        </td>
		<td align="left" valign="middle" width="11%"><font size="2"> 
	  <?php 
	    if ($reg_documento["destino"]=="") {
			    $reg_documento["destino"]="&nbsp";} 
	  echo $reg_documento["destino"];?></font> 
      </td>
        <td align="left" valign="middle" width="11%"><font size="2">
          <?php 
		  if ($reg_documento["funcdestino"]=="") {
			    $reg_documento["funcdestino"]="&nbsp";} 
		  echo $reg_documento["funcdestino"];?></font>
        </td>
	    <td align="left" valign="middle" width="9%"><font size="2">
          <?php echo $reg_documento["desc_estado_tramite"];?></font>
        </td>
      </tr>
    <?php if(fmod($Corre,10)==0) { 
	 echo "</table>";
	 echo "</div>";  } ?>
    <?php } ?></table>
    </div> 
   <?php }} ?>	    
  </form>
 
  <div id="layer_doc" style="position:absolute; width:723px; height:336px; z-index:1; left:171px; top:150px; visibility: hidden; overflow: auto; background-color: #A6C4E1; layer-background-color: #A6C4E1; border: 1px none #000000;" class="texto"> 
    <form name="form_layer">
   
    <font color="#000000" face="Arial, Helvetica, sans-serif"> 
	  <table width="699" border="1" cellpadding="1" cellspacing="0" bgcolor="#e6eeff" dwcopytype="CopyTableRow">
        <tr> 
          <td width="710" bgcolor="#cadbff"> <table width="100%" border="0" cellspacing="1" cellpadding="2">
              <tr bgcolor="#e6eeff"> 
                <td height="15" colspan ="10" ><font color="#7777FF"><strong>INFORMACION 
                  DOCUMENTO </strong></font></td>
              </tr>
              <tr> 
                <td height="15"><font color="#804040"><b>Tipo de Docto</b> </font></td>
                <td height="15" colspan="3" > <font color="#804040"> 
                  <input name="txttipodoc" type="text" size="30" maxlength="30" value="<?php echo $xtipodoc ;?>">
                  </font></td>
                <td width="92"><font color="#804040"><b>Original</b></font></td>
                <td height="15"> <font color="#804040"><font color="#804040"> 
                  <input name="txtoriginal" type="text" size="2" maxlength="2" value="<?php echo $xoriginal;?>">
                  </font> </font></td>
                <td width="46" height="15"><font color="#804040"><b>Medio</b></font></td>
                <td width="86" height="15"><font color="#804040"> 
                  <? 
                If($rs["medio"]=="P")
                {echo "Papel";	}
				else
				if ($rs["medio"]=="C")
				{echo "Copia";	}
				else
				if ($rs["medio"]=="F")
		    	{  	echo "Fax";   	}   
				else
		 		{   echo "Video";	}
				?>
                  </font> </td>
              </tr>
              <tr> 
                <td width="113" height="44"><font color="#804040"><b>N&ordm; Oficial<font size="4" face="Arial"> 
                  </font></b></font></td>
                <td width="97" height="44"><font color="#804040"> 
                  <input name="txtnumoficial" type="text" size="10" maxlength="10" value="<?php echo $xnumoficial;?>">
                  </font> </td>
                <td width="94"><font color="#804040"><strong>N&ordm; Interno</strong> 
                  </font></td>
                <td width="76"><font color="#804040"><font color="#804040"> 
                  <input name="txtnuminterno" type="text" size="10" maxlength="10" value="<?php echo $xnuminterno ;?>">
                  </font></font></td>
                <td width="92"><font color="#804040"><strong>N&ordm; <font face="Arial, Helvetica, sans-serif">Externo</font></strong></font></td>
                <td width="60"><font color="#804040"> 
                  <input name="txtnumexterno" type="text" size="10" maxlength="10" value="<?php echo $xnumexterno ;?>">
                  </font></td>
              </tr>
            </table>
            <table width="100%" border="0" cellspacing="1" cellpadding="2">
              <tr> 
                <td height="30"><b><font color="#804040" face="Arial, Helvetica, sans-serif">Fecha 
                  Doc</font></b></td>
                <td height="30"> <input name="txtfecha" type="text" size="10" maxlength="10" value="<?php echo $xfech_proc;?>"> 
                </td>
                <td>&nbsp;</td>
                <td height="30">&nbsp;</td>
              </tr>
              <tr> 
                <td width="103" height="30"> <font color="#804040"><b>Procedencia</b></font></td>
                <td width="277" height="30"> <font color="#804040"> 
                  <input name="txtprocedencia" type="text" size="30" maxlength="30" value="<?php echo $xprocedencia;?>">
                  </font></td>
                <td width="92"><font color="#804040"><b>Funcionario</b></font></td>
                <td width="212" height="30"> <font color="#804040"> 
                  <input name="txtfuncproced" type="text" size="30" maxlength="30" value="<?php echo $xfuncproced;?>">
                  </font></td>
              </tr>
            </table>
            <table width="98%" border="0" cellpadding="2" cellspacing="1">
              <tr> 
                <td width="59" height="113"><font color="#804040"><b>Materia</b> 
                  </font></td>
                <td width="637"> <font color="#804040"> 
                  <textarea name="txtmateria" cols="100" rows="3" ><?php echo $xmat; ?></textarea>
                  </font></td>
              </tr>
            </table></td>
        </tr>
      </table>
      </font> 
	   <input type="hidden" name="xtipodoc" >
	   <input type="hidden" name="xnuminterno" >
	   <input type="hidden" name="xnumoficial" >
	   <input type="hidden" name="xnumexterno" >
	   <input type="hidden" name="xoriginal" >
	   <input type="hidden" name="xprocedencia" >
	   <input type="hidden" name="xfuncproced">
	   <input type="hidden" name="xfech_doc" >
	   <input type="hidden" name="xmat" >
	   
      </form>
	  
  <div align ="center">
      
      <input  type="button" name="submit"class="botones" onClick="showHideLayer_doc('layer_doc','','hide')" value="Cancelar">
  </div>
</div>

  <p>&nbsp; </p>
</center>  

 <?php mssql_close($cn);?>

</body>
</html>
