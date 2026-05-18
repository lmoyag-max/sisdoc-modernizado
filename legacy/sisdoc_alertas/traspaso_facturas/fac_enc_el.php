<?php
include("variables.php");
include("conexion_bd.php");
include("funciones.php");
$tema_fact = $Cbo_tema_fact;
//echo "tipo " . $Tipo_Docto .   "  cbo tipo " . $Cbo_Tipo_Docto . "<br>";
$Txt_fecha_ini = $Txt_fecha_ini; 
$Txt_fecha_fin = $Txt_fecha_fin;        
$numfact= $numfact;
$rut= $rut;
$TxtMateria= $TxtMateria;
$Procedencia=$Procedencia;
$tipo_procedencia=$tipo_procedencia;
$tipo_destino=$tipo_destino;
$Usuario=$cusuario;
$xx = $idusuario;
$fun= $idfuncionario;
$cons=$sw_cons;
$sw_elim=$sw_elim;
$desc =$arreglo;
$Cbo_Destinatario = $Cbo_Destinatario;
$Cbo_Procedencia = $Cbo_Procedencia;
//echo "cbodes " . $Cbo_Destinatario . "<br>";
//echo "usuario " . $idusuario . "<br>";

if(!isset($sw_grabado))
{
$grabado=10;
}
else
{
$grabado=$sw_grabado;
}

$dia = substr($Txt_fecha_ini,0,2);
$mes = substr($Txt_fecha_ini,3,2);
$año = substr($Txt_fecha_ini,6,4);
$fechaini = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

$dia = substr($Txt_fecha_fin,0,2);
$mes = substr($Txt_fecha_fin,3,2);
$año = substr($Txt_fecha_fin,6,4);
$fechafin = date("Y/m/d H:i", mktime(23, 59,59, $mes, $dia, $año));
$desc  =$arreglo;
//echo "interno " . $TxtInterno . "<br>";
//echo "tipo doc " . $Cbo_Tipo_Docto . "<br>";

$consulta="select a.*,b.desc_tema,c.id_detalle, c.rut_procedencia, c.rut_destino, 
  		   c.fecha_sistema as fecha2, c.observaciones,
  		   procedencia= 
           case  c.tipo_procedencia 
            when 'I' then 
               (select desc_dependencia from dependencia where  c.id_procedencia=id_dependencia)
             else
               (select desc_dependencia_externa  from dependencia_externa where  c.id_procedencia=id_dependencia_externa)
             end, 
			 funcionario=
             case c.tipo_procedencia
            	When 'I' then
				(select max(rtrim(funcionario.nombres)+ ' ' + rtrim(funcionario.apellidos))
					from funcionario where 
					c.rut_procedencia =funcionario.rut )
			 end		
           from facturas a,temas_facturas b , detalle_facturas c 
           where a.id_tema_fact=b.id_tema 
		   and   a.id_factura =c.id_factura 
		   and  (c.id_usuario = $idusuario or a.id_usuario = $idusuario)
		   and  c.id_detalle in (select max(id_detalle) from detalle_facturas where id_factura=a.id_factura)
		   and  (a.fecha_factura between '$fechaini' and '$fechafin')";

if ( $Cbo_temas_fact== 0 ){
 $cbo_tema= "";
 }
else{
  $cbo_tema= " and a.id_tema_fact=" . $Cbo_temas_fact ;
  $consulta=$consulta  . $cbo_tema ;
  }
             
if ( $numfact== "" ){
 $nf= "";
 }
else{
  $nf = " and a.num_factura=" . $numfact ;
  $consulta=$consulta  . $nf ;}

if ( $rut== "" ){
 $nr= "";
 }
else{
  $nr = " and a.rut_prov=" . $rut;
  $consulta=$consulta  . $nr ;}
  

//  buscando por materia //

$len = strlen($TxtMateria);
$mat = substr(trim($TxtMateria),-1);
if ($mat==","){
$materia=substr($TxtMateria,0,$len - 1);}
else
{$materia=$TxtMateria;}

$largo=0;
$largo= substr_count($materia ,"," );
$largo=$largo+1; 
if($materia==""){
$largo=0;}
$materia=$largo . "," . $materia;
$vector = split (",",$materia);

$largo= $vector[0];$x=1;
$sw_ok=0;
$mat1="";
if ($largo!=0){
for($x=1;$x <=$largo;$x++){
    $mat1 = $mat1 . " and a.descripcion like '%" . trim($vector[$x]) . "%'" ;}
}
$consulta = $consulta . $mat1;

// fin busqueda //

// buscando destino ,origen //
 $procedencia="";
 If ($Cbo_Procedencia != 0){
     $procedencia =" and (c.tipo_procedencia=" . "'" . $tipo_procedencia . "'" . " and c.id_procedencia= " . $Cbo_Procedencia . ")";
 }	  
 $destinatario="";
 If ($Cbo_Destinatario != 0){
     $destinatario =" and (c.tipo_destinatario=" . "'" . $tipo_destino . "'" . " and c.id_destino= " . $Cbo_Destinatario . ")";
 }	  
 $consulta=$consulta . $procedencia . $destinatario;

//echo "consulta " . $consulta . "<br>"; 
$rs_doc=$consulta;
//$rs_documento=mssql_query($rs_doc);   
$rs_factura=mssql_query($rs_doc);
$Totreg = mssql_num_rows($rs_factura);
if($Totreg==0 )
 {
//echo $Totreg . "grabado " . $grabado . "<br>";
 	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="busca_factura_el.php">';
	echo '<input type="hidden" name="idusuario" value="' . $xx . '">';
	echo '<input type="hidden" name="cusuario" value="' . $Usuario . '">';
	echo '<input type="hidden" name="idfuncionario" value="' . $fun . '">';
	echo '<input type="hidden" name="flujook" value="' . 1 . '">';
	echo '<input type="hidden" name="sw_cons" value="' . $cons . '">';
	echo "</form></body></html>";
	
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

<title>Formulario N&ordm; 1</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript">
<!--
<!--

var grabaok="<?php echo $grabado; ?>";

function carga() {
  if (grabaok=="0"){
  alert(" El Trámite ha sido Eliminado");
  }
  if (grabaok=="1"){
  alert(" El estado del trámite no permite eliminarlo ");
  }
  if (grabaok=="2")
  {
  alert(" No se puede borrar el trámite, por que esta asociado al documento ");
  }
}




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
<link href="pruebas_facturas/css/estilo_doc.css" rel="stylesheet" type="text/css">
<STYLE type=text/css>

A { COLOR: blue; FONT-FAMILY: verdana,arial,helvetica,sans-serif; FONT-SIZE: 11px; TEXT-DECORATION: none
}

A:hover { COLOR: red; TEXT-DECORATION: none
}
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000" topmargin="0" onload="carga()">
<center >
    <form name="formulario1">
        
    <table width="75%" border="0">
      <tr> 
        <td> <p align="center"><b><font size="4" color="#0000FF">RESULTADO DE 
            BUSQUEDA</font></b></font><br>
            <font color="#0000FF"><strong>(Eliminaci&oacute;n de Tr&aacute;mites)</strong></font></p>
        </td>
      </tr>
     
    </table>
    <table width="650" border="0">
      <tr> 
	  <td><div align="left"><strong><?php echo "Total de Páginas : " . $NumPag ?></strong></div></td>
        <td><div align="right"><strong><?php echo "Total Registros : " . $Totreg ?></strong></div></td>
		
        <!--td width="325" height="23" align="left"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <?php echo "Total Registros : " . $Totreg ?></font></strong></td>
        <td width="325" height="23" align="right"><strong><font face="Verdana, Arial, Helvetica, sans-serif"><?php echo "Total de Páginas : " . $NumPag ?></font></strong></td!-->
      </tr>
    </table>
    
    <table width="650"  border="0">
      <tr> 
        <td height="23" > 
          <?php
		  echo "<div align='left'><b>";
     		        for ($i = 1; $i <= $NumPag; $i++)
			 {
			
		 echo "<img src='botones/boton" . $i . ".gif' width='44' height='16'". 
 "onClick=\"MM_showHideLayers('layer" . $i . "','','show',$i, $NumPag)\">"; 
            
			 } 
			 echo "</b></div>";
		    ?>
          <input type="hidden" name="Totreg22" value="<?php echo $Totreg; ?>"> 
          <input type="hidden" name="NumLayer22" value="<?php echo $NumLayer; ?>"> 
	      <input type="hidden" name="idusuario" value="<? echo $xx;?>"> <input type="hidden" name="cusuario22" value="<? echo $cusuario;?>"> 
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
  		   echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:8px; top:92px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility:visible">';
		   
		   }
		   else
		   {
		   echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:8px; top:92px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: hidden">';
		   }
		   	  
	      
	echo "<table width='700' border='1' cellpadding='1' cellspacing='0' bgcolor='#E6EEFF' >"; 
	echo '<tr bgcolor="#6699FF" >';
    echo '<td width="5%" height="33"><strong><font color="#FFFFFF" size="2">Num</font></strong></td>';
   // echo '<td width="6%" height="33"><strong><font color="#FFFFFF" size="2">Trámites</font></strong></td>';
	echo '<td width="7%" height="33"><strong><font color="#FFFFFF" size="2">Nro Factura</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Tema </font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Proveedor</font></strong></td>';
	echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha factura</font></strong></td>';
    echo '<td width="100%" height="33"><strong><font color="#FFFFFF" size="2">Descripción</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Procedencia</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Funcionario</font></strong></td>';
    echo '<td width="6%" height="33"><strong><font color="#FFFFFF" size="2">Eliminar</font></strong></td>';
    echo '</tr>';
		 
		  
		  }
		  $Corre =  $Corre + 1;
		  ?>
    
	<tr>
        <td align="left" valign="middle" width="3%"><font size="2"><?php echo $Corre;?></font></td>
		<td align="left" valign="middle" width="5%">
      	<?php  echo $reg_factura["num_factura"]; ?>		
		</td>
		<td align="left" valign="middle" width="8%"><font size="2">
          <?php echo $reg_factura["desc_tema"];?></font>
        </td>
        <td align="left" valign="middle" width="5%"><font size="2">
          <?php echo $reg_factura["rut_prov"];?></font>
        </td>
		 <td align="left" valign="middle" width="5%"><font size="2">
          <?php $fec_doc=strtotime($reg_factura["fecha_factura"]);
		        $fech_doc=date("d/m/y",$fec_doc);
				echo $fech_doc;?></font>
        </font>
        </td>
       
      <td align="left" valign="middle" width="20%"><font size="2"> 
        <?php if ($reg_factura["descripcion"]=="")
		           echo "&nbsp";
				   else echo $reg_factura["descripcion"];?>
        </font> </td>
        
      <td align="left" valign="middle" width="11%"><font size="2"> 
	  <?php if ($reg_factura["procedencia"]=="")
		           echo "&nbsp";
			  else echo $reg_factura["procedencia"];?></font> 
      </td>
        <td align="left" valign="middle" width="11%"><font size="2">
          <?php 
		  if ($reg_factura["funcionario"]=="") {
			    $reg_factura["funcionario"]="&nbsp";} 
		  echo $reg_factura["funcionario"];?></font>
        </td>
		<td align="left" valign="middle" width="6%"> 
		
         <?php
		 
		 
		 echo '<a href="guardar_fact_el.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . 
	     '&idfuncionario=' . $idfuncionario . '&desc=' . $desc .
		 '&Txt_fecha_ini=' . $Txt_fecha_ini . '&Txt_fecha_fin=' . $Txt_fecha_fin .
		 '&idfact=' . $reg_factura["id_factura"] .
		 '&idseguim=' . $reg_factura["id_detalle"] .
		 '&Cbo_tema_fact=' .  $tema_fact  .
		 '&numfact=' . $num_fact .
		 '&Txtdescripcion=' . ltrim($TxtMateria) .
		 '&Cbo_Destinatario=' . $Cbo_Destinatario .  
		 '&Cbo_Procedencia=' . $Cbo_Procedencia .
		 '&tipo_procedencia=' .  $tipo_procedencia . 
		 '&tipo_destino=' .  $tipo_destino . 
		 '&rut_procedencia=' . $reg_documento["rut_procedencia"] . 
		 '&rut_destino=' . $reg_documento["rut_destino"] . 
		 '& obs  =' .  $reg_documento["observaciones"] .
		 '&fecha_sis1 =' . $reg_documento["fecha_sistema"] . 
		 '$fecha_sis2 =' .  $reg_documento["fecha2"] . '">Eliminar</a>';
		  ?> 
		 
		
	  
		 
	    </td>
	    </tr>
    <?php if(fmod($Corre,10)==0) { 
	 echo "</table>";
	 echo "</div>";  } ?>
    <?php } ?></table>
    </div> 
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
