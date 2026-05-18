<?php
include("variables.php");
include("conexion_bd.php");
include("funciones.php");
$cusuario='Ximena';
$idusuario=3;
$idfuncionario=3;
$usuario=$cusuario;
$xx = $idusuario;
$fun=$idfuncionario;
//echo "idusu" . $idusuario . "*** usu " . $cusuario . "** fun " . $idfuncionario ;
$rs_doc="exec busca_nomina_recib '" . $xx. "','" . $txtnomina. "'";
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


?> 

<html>
<head>
<META Http-Equiv="Cache-Control" Content="no-cache">
<META Http-Equiv="Pragma" Content="no-cache">
<META Http-Equiv="Expires" Content="0"> 
<title>Busqueda de documentos por nomina</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript">

/*function carga() {
  document.form1.txtnomina.value=$txtnomina;
}*/
<!--
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
// -->

function MM_findObj(n, d) { //v4.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && document.getElementById) x=document.getElementById(n); return x;
}

function MM_showHideLayers() { //v3.0
  var a,i,p,v,obj,args=MM_showHideLayers.arguments;
   ocultalayer(args[3],args[4]);
  for (i=0; i<(args.length-4); i+=3) 
  if ((obj=MM_findObj(args[i]))!=null) 
      { v=args[i+2];
    if (obj.style) 
	    { obj=obj.style; v=(v=='show')?'visible':(v='hide')?'hidden':v; }
    obj.visibility=v; }		
  }
//-->
function ocultalayer(idlay,totlay){
var idlay, a;

	for (a=1; (a<=totlay); a++){
		nomlay = "layer" + a;
		document.all[nomlay].style.visibility="hidden";
		//	queda pendiente esta consulta --	if (navigator.appName == "Microsoft Internet Explorer") 
			
         }
	}
	
	
</script>

<script language="JavaScript">
<!--

	
	
//-->

function buscar(){

/*if (document.formulario1.txtnomina.value != ""){
	document.formulario1.action="multi_recib.php";
	document.formulario1.submit();
 	 }*/
	document.formulario1.action="tramites.php";
	document.formulario1.submit();

}
 function validarentero(formu){ 
      //intento convertir a entero. 
	  var formu;
     //si era un entero no le afecta, si no lo era lo intenta convertir 
     formu.txtnomina.value = parseInt(formu.txtnomina.value);
	 //Compruebo si es un valor numérico 
      if (isNaN(formu.txtnomina.value)) { 
            //entonces (no es numero) devuelvo el valor cadena vacia 
			formu.txtnomina.value ="";
			alert ("Debe ingresar solamente numeros");
            return formu.txtnomina.value 
      }else{ 
            //En caso contrario (Si era un número) devuelvo el valor 
            return formu.txtnomina.value
      } 
} 

</script>

<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
<STYLE type=text/css>

A { COLOR: blue; FONT-FAMILY: verdana,arial,helvetica,sans-serif; FONT-SIZE: 11px; TEXT-DECORATION: none
}

A:hover { COLOR: red; TEXT-DECORATION: none
}
</style>
</head>
<body   bgcolor="#FFFFFF" text="#000000" topmargin="0" onload="carga()">
<center >
    <form name="formulario1" 
        method="post" >
    <table width="650" border="1" cellpadding="2" cellspacing="0" bgcolor="#3399FF">
      <tr> 
        <td> <p align="center"><b><font size="4" color="#FFFFFF">BUSQUEDA POR 
            NOMINA </font></b></p></td>
      </tr>
    </table>
    <table width="650" border="0">
      <tr>
        <td><div align="right"><strong><font color="#0000A0" size="2"><? echo  '<a href="multi_recep.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario .
		 '&idfuncionario=' . $idfuncionario .
		  '">Volver</a>'; ?></font></strong></div></td>
      </tr>
    </table>
    <table width="650" border="0">
      <tr>
        <td><div align="left"><strong><?php echo "Total de Páginas : " . $NumPag ?></strong></div></td>
        <td><div align="right"><strong><?php echo "Total Registros : " . $Totreg ?></strong></div></td>
      </tr>
    </table>
    <table width="650" border="0">
      <tr>
        <td>
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
    <table width="650" border="0">
      <tr>
        <td>&nbsp;</td>
        <td><div align="right">N&oacute;mina 
            <!--input type="text" name="txtnomina" size="8" maxlength="8" ;"-->
            <input name="txtnomina" type="text" class="entradas" onBlur="validarentero(formulario1);" size="8" maxlength="8">
            <font size="2" face="Arial"> 
            <input name="cmd_busca" type="button" class="botones" onClick="buscar();" value="Buscar">
            </font></div></td>
      </tr>
    </table>
    <p><font size="2"><?php echo $reg_documento["desc_tipo_documento"];?></font></p>
	  
    <?php 
	      $Corre = 0;
		  $NumLayer = 0;
		  while($reg_documento = mssql_fetch_array($rs_documento)) { 
		  
		  if(fmod($Corre,10)==0) 
		  { 
		  $NumLayer = $NumLayer + 1;
		  if($NumLayer==1){
  		 echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:120px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: visible">';
		   }
		   else
		   {
		 echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:120px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: hidden">';
		   }
		   
		   
		  echo "<table width='650' border='1' cellpadding='1' cellspacing='0' bgcolor='#ECE9D8'>"; 
	echo '<tr bgcolor="#6699FF">';
    echo '<td width="5%" height="33"><strong><font color="#FFFFFF" size="2">Num</font></strong></td>';
    echo '<td width="6%" height="33"><strong><font color="#FFFFFF" size="2">Trámites</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Interno</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Oficial</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Tipo Documento</font></strong></td>';
    echo '<td width="100%" height="33"><strong><font color="#FFFFFF" size="2">Materia</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha Documento</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha Recepcion</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Procedencia</font></strong></td>';
	echo '<td width="7%" height="33"><strong><font color="#FFFFFF" size="2">Nómina</font></st?????
???rong></td>';
    echo '</tr>';
		 
		  }
		  $Corre =  $Corre + 1;
		  ?>
    
	<tr>
        <td align="left" valign="middle" width="5%"><font size="2"><?php echo $Corre;?></font></td>
		
      <td align="left" valign="middle" width="6%"> 
     <?php echo '<a href="tramites.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . 
	 '&iddocum=' . $reg_documento["id_documento"] . '&idseguim=' . $reg_documento["id_seguimiento"] .
	 '&idfuncionario=' . $idfuncionario . '">Trámite</a>'; ?> 
	   </td>
        <td align="left" valign="middle" width="8%"><font size="2">
          <?php echo $reg_documento["num_interno"];?></font>
        </td>
		 <td align="left" valign="middle" width="8%"><font size="2">
          <?php echo $reg_documento["num_oficial"];?></font>
        </td>
	    
      <td align="left" valign="middle" width="8%"><font size="2"><?php echo $reg_documento["desc_tipo_documento"];?> </font> </td>
		
      <td align="left" valign="middle" width="100%"><font size="2"> 
        <?php if ($reg_documento["materia"]=="")
		           echo "&nbsp";
				   else echo $reg_documento["materia"];?>
        </font> </td>
        <td align="left" valign="middle" width="8%"><font size="2">
          <?php $fec_doc=strtotime($reg_documento["fecha_documento"]);
		        $fech_doc=date("d/m/Y",$fec_doc);
				echo $fech_doc;?></font>
        </td>
        <td align="left" valign="middle" width="8%"><font size="2">
          <?php $fec_rec=strtotime($reg_documento["fecha_recepcion"]);
		        $fech_rec=date("d/m/Y",$fec_rec);
				echo $fech_rec;?></font>
        </td>
        <td align="left" valign="middle" width="20%"><font size="2">
          <?php echo $reg_documento["procedencia"];?></font>
        </td>
         <td align="left" valign="middle" width="7%"><font size="2">
         <?php echo $reg_documento["id_nomina_despacho"];?></font>
        </td>

      </tr>
     <?php if(fmod($Corre,10)==0) { 
	 echo "</table>";
	 echo "</div>";  } ?>
    <?php } ?></table>
    </div> 
	<!--?php echo $NumLayer ?-->
	<p>&nbsp;</p>
    <p>&nbsp;</p>
    <table width="650"  border="0">
      <tr> 
        <td height="23" > 
          <div align="left"></div>
          <div align="left"> 
            <input type="hidden" name="Totreg2" value="<?php echo $Totreg; ?>">
            <input type="hidden" name="NumLayer2" value="<?php echo $NumLayer; ?>">
            <input type="hidden" name="idusuario" value="<? echo $xx;?>">
            <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
			<input type="hidden" name="idfuncionario" value="<? echo $idfuncionario;?>">
			<input type="hidden" name="iddocum" value ="<? echo $reg_documento["id_documento"];?>">
			<input type="hidden" name="idseguim" value="<? echo $reg_documento["id_seguimiento"];?>">
            
          </div></td>
      </tr>
    </table>
    <br>
    <p>&nbsp; </p>
  </form>	    
  <p>&nbsp; </p>
</center>  
  </form>
  </center>
</body>
</html>
