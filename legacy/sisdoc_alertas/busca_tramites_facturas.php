<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
//include("funciones.php");

$usuario=$cusuario;
$xx = $idusuario;
$fun=$idfuncionario;
$nomina=$txtnomina;
$flujo1=flujook;
//echo "idusu" . $idusuario . "*** usu " . $cusuario . "** fun " . $idfuncionario ;
$rs_doc="exec busca_tramite_factura_mod'" . $xx .  "'";
$rs_factura=mssql_query($rs_doc);   
$Totreg = mssql_num_rows($rs_factura);
 if($Totreg==0)
 {
 	echo "<script>\n";

	echo " alert('No Existen Registros');\n";
 	echo "</script>\n";
}
else
{
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
<title>Busca tramites </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript">

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


</script>

<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
<STYLE type=text/css>

A { COLOR: blue; FONT-FAMILY: verdana,arial,helvetica,sans-serif; FONT-SIZE: 11px; TEXT-DECORATION: none
}

A:hover { COLOR: red; TEXT-DECORATION: none
}
</style>
</head>
<body   bgcolor="#FFFFFF" text="#000000" >
<center >
    <form name="formulario1" 
        method="post" 
		action="modifica_tramite_factura.php">
    <table width="650" border="1" cellpadding="2" cellspacing="0" bgcolor="#3399FF">
      <tr> 
        <td> <p align="center"><b><font size="4" color="#FFFFFF">MODIFICACION 
            DETALLE FACTURA </font></b></p></td>
      </tr>
    </table>
    <table width="650" border="0">
      <tr>
       
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
   
	  
    <?php 
	      $Corre = 0;
		  $NumLayer = 0;
		  while($reg_factura = mssql_fetch_array($rs_factura)) { 
		  
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
		   
		   
	echo "<table width='650' border='1' cellpadding='1' cellspacing='0' bgcolor='#E6EEFF'>"; 
	echo '<tr bgcolor="#6699ff">';
    echo '<td width="5%" height="33"><strong><font color="#FFFFFF" size="2">Num</font></strong></td>';
    echo '<td width="6%" height="33"><strong><font color="#FFFFFF" size="2">Trámites</font></strong></td>';
   echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Fecha Recepción</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Factura</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Fecha factura</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Tema factura</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Destino</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Func. destino</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Descripcion</font></strong></td>';
	

	echo '</tr>';
		 
		  }
		  $Corre =  $Corre + 1;
		  ?>
    
	<tr>
        <td align="left" valign="middle" width="5%"><font size="2"><?php echo $Corre;?></font></td>
		
      <td align="left" valign="middle" width="6%"> 
    <?php echo '<a href="modifica_tramite_factura.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . 
	 '&iddocum=' . $reg_factura["id_factura"] . '&idseguim=' . $reg_factura["id_detalle"] .
	 '&idfuncionario=' . $idfuncionario . '&flujook=' . 0 . '&num_fac=' .  $reg_factura["num_factura"] .  
	 '">Trámite</a>'; ?> 
	   </td>
      <td align="left" valign="middle" width="8%"><font size="2">
          <?php
		   if ($reg_factura["fecha_recepcion"]=="" || $reg_factura["fecha_recepcion"] == NULL )
		        {     echo "&nbsp";}
			else
			    {
		  $fec_doc=strtotime($reg_factura["fecha_recepcion"]);
		        $fech_doc=date("d/m/Y",$fec_doc);
				echo $fech_doc;}?></font>
        </td>
        <td align="left" valign="middle" width="8%"><font size="2">
          <?php echo $reg_factura["num_factura"];?></font>
        </td>    
      <td align="left" valign="middle" width="8%"><font size="2">
          <?php
		  $fec_doc=strtotime($reg_factura["fecha_factura"]);
		        $fech_doc=date("d/m/Y",$fec_doc);
				echo $fech_doc;?></font>
        </td>
      <td align="left" valign="middle" width="8%"><font size="2"><?php echo $reg_factura["desc_tema"];?> </font> </td>
		
       
        <td align="left" valign="middle" width="20%"><font size="2">
          <?php echo $reg_factura["destino"];?></font>
        </td>
        <td align="left" valign="middle" width="30%"><font size="2">
        <?php if ($reg_factura["funcdestino"]=="")
		           echo "&nbsp";
				   else echo $reg_factura["funcdestino"];?></font>
        </td>
		<td align="left" valign="middle" width="40%"><font size="2">
        <?php if ($reg_factura["descripcion"]=="")
		           echo "&nbsp";
				   else echo $reg_factura["descripcion"];?></font>
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
            <!--input type="submit" name="cmd_aceptar" value="aceptar" -->
            <!--?php echo $NumLayer; ?-->
          </div></td>
      </tr>
    </table>
    <br>
    <p>&nbsp; </p>
  </form>
 <?php
 }
 ?>
  <p>&nbsp; </p>
</center>  

</body>

</html>
