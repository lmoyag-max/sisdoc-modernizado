<?php
include("variables.php");
include("conexion_bd.php");
include("funciones.php");


// el parametro $solocons es para efectos de distinguir si el usuario tiene l menu de consulta el menu general para indicar reenviar .  //
// usa el valor 1 para el caso que sea solo usuario de consulta , menu consulta.  //

$solocons =$solocons;
//echo " 2 consulta" . $solocons ." menu cons " . $menucons;

$Usuario=$cusuario;
$xx = $idusuario;
$fun= $idfuncionario;
$cons=$sw_cons;
$mi_dependencia = $dependencia_usuario;
$si_avanza=$avanza;
$dia = substr($Txt_fecha_ini,0,2);
$mes = substr($Txt_fecha_ini,3,2);
$año = substr($Txt_fecha_ini,6,4);
$Fechaini = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

$dia = substr($Txt_fecha_fin,0,2);
$mes = substr($Txt_fecha_fin,3,2);
$año = substr($Txt_fecha_fin,6,4);
$Fechafin = date("Y/m/d H:i", mktime(23, 59,59, $mes, $dia, $año));

// cambio que se agrega fecha oficial 
$dia = substr($Txt_fecha_recep,0,2);
$mes = substr($Txt_fecha_recep,3,2);
$año = substr($Txt_fecha_recep,6,4);
$Fecharecep = date("Y/m/d H:i", mktime(23, 59,59, $mes, $dia, $año));
//echo "fecha oficial " .$Txt_fecha_ofi. "Txt_fecha ini" . $Fechaini . "fecha fin" . $Fechafin;
$Fecharecep1 = date("Y/m/d H:i", mktime(0,0,0, $mes, $dia, $año));
$Fecharecep2 = date("Y/m/d H:i", mktime(23, 59,59, $mes, $dia, $año));
$ferecep=$dia ."/" .$mes."/" . $año ;
$ferecep1=date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

$ferecep2=date("Y/m/d H:i", mktime(23, 59,59, $mes, $dia, $año));


// fin fecha oficial
$desc  =$arreglo;

//echo "avanza" . $si_avanza . "cons" . $cons , "avanzada " . $avanzada . "<br>";
//echo "fecha ini " . $Fechaini . "fecha fin " . $Fechafin  . "usuario" . $xx . "tipo docto" . $Cbo_Tipo_Docto  . "<br>";

//if($si_avanza==0) {
//echo "busqueda" . $avanzada ;
if($avanzada==1) {// busqueda avanzada 
   If ($Cbo_Procedencia != 0  && $Cbo_Destinatario ==0)
   {
     
    $consulta=" select distinct a.id_documento,a.id_tipo_documento,a.fecha_documento,a.materia,a.num_interno,
	            a.num_oficial,a.num_externo,b.desc_tipo_documento
                   from documento a,tipo_documento b, tramite c
			    where 
				
                  (a.id_tipo_documento=b.id_tipo_documento  and   a.id_documento =c.id_documento 
		                 and c.tipo_procedencia = 'I'  
                         and c.id_procedencia= $Cbo_Procedencia 
						 and  c.id_seguimiento in  (select min(id_seguimiento) from tramite where a.id_documento=id_documento )
				        
				  )";
	
   }
    else
	{   	   
	$consulta=" select distinct a.id_documento,a.id_tipo_documento,a.fecha_documento,a.materia,a.num_interno,
	            a.num_oficial,a.num_externo,b.desc_tipo_documento
                   from documento a,tipo_documento b, tramite c
			    where (
            		   (a.id_tipo_documento=b.id_tipo_documento  and   a.id_documento =c.id_documento 
		                 and ((c.tipo_procedencia = 'I'  
                                 and c.id_procedencia in (select id_dependencia from acceso where id_usuario = $xx)) 
			                  or (c.id_destino in (select id_dependencia from acceso where id_usuario = $xx)
							       and  c.tipo_destinatario = 'I')
							 )
					   )
                      OR
					   (
                        (a.id_documento IN (Select id_documento_hijo FROM relacion_documento) and    a.id_tipo_documento=b.id_tipo_documento 
						 AND A.ID_DOCUMENTO=C.ID_DOCUMENTO 
            	         and  ((c.tipo_procedencia = 'I'  
                         and c.id_procedencia in  (select id_dependencia from acceso where id_usuario =$xx))
                            or (c.id_destino in (select id_dependencia from acceso where id_usuario = $xx) 
						      and  c.tipo_destinatario = 'I' )
                       	   )
						)
					)
				)";
	}			
     
}
else
{ // busqueda global
  
	if ($nomina == '')
	$consulta="select distinct a.id_factura,c.id_detalle,a.fecha_recepcion,a.id_tema_fact,a.fecha_factura,a.descripcion,a.num_factura, a.monto ,b.desc_tema,d.razon_social
from facturas a,temas_facturas b, detalle_facturas c , proveedores d
where a.id_tema_fact= b.id_tema and a.id_estado_fact = 1 
and a.id_factura =c.id_factura
and  a.rut_prov=d.rut_prov";
     else 
	$consulta="select distinct a.id_factura,c.id_detalle,a.fecha_recepcion,a.id_tema_fact,a.fecha_factura,a.descripcion,a.num_factura, a.monto ,b.desc_tema,d.razon_social
from facturas a,temas_facturas b, detalle_facturas c , proveedores d
where a.id_tema_fact= b.id_tema and a.id_estado_fact = 1 
and a.id_factura =c.id_factura
and  a.rut_prov=d.rut_prov
and c.id_nomina_despacho=".$nomina;
	 
}

if ($Txt_fecha_ini =='')
 {$rangofec ='';}
else 
  {$rangofec="  and  (a.fecha_factura between '$Fechaini' and '$Fechafin')";
  $consulta=$consulta  . $rangofec ;
  }
if ($Txt_fecha_recep =='')
{ $fecofi= "";}
else

 {   
  $fecofi= " and (a.fecha_recepcion between '$ferecep1' and '$ferecep2')";
 $consulta=$consulta  . $fecofi ;
 }
 
if ( $Cbo_temas_fact== 0 ){
 $cbo_tipo= "";
 }
else{
  $cbo_tipo = " and a.id_tema_fact=" . $Cbo_temas_fact ;
  $consulta=$consulta  . $cbo_tipo ;}
             
if ( $num_fact== "" ){
 $numfact= "";
 }
else{
  $numfact = " and a.num_factura=" . $num_fact ;
  $consulta=$consulta  . $numfact ;}
  
if ( $rut== "" ){
 $rt= "";
 }
else{
  $rt = " and a.rut_prov=" . $rut ;
  $consulta=$consulta  . $rt ;}

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
$mat2="";
$mat3 = "" ;
if ($largo!=0){
for($x=1;$x <=$largo;$x++){
   //     $mat1 = $mat1 . "and a.materia  like '%" . trim($vector[$x]) . "%'" ;
        $mat1 =  "a.descripcion  like  '%" . trim($vector[$x]) . "%'"  ;
	  $mat3 = $mat3 . " and (" . $mat1 . " )" ;  
//	  echo "mat3" . $mat3. "<br>";
}
}
//$consulta = $consulta .  $mat1 ;
$consulta = $consulta . $mat3; 
//echo "consulta" . $consulta ; 

// buscando destino ,origen //
 $procedencia="";
// if ($avanzada !=1 )
 //{
 If ($Cbo_Procedencia != 0){
     $procedencia =" and (c.tipo_procedencia=" . "'" . $tipo_procedencia . "'" . " and c.id_procedencia= " . $Cbo_Procedencia . ")";
 }
// }	  
 $destinatario="";
 If ($Cbo_Destinatario != 0){
     $destinatario =" and (c.tipo_destinatario=" . "'" . $tipo_destino . "'" . " and c.id_destino= " . $Cbo_Destinatario . ")";
 }	  
 $consulta=$consulta . $procedencia . $destinatario;


$rs_doc=$consulta;
//$rs_documento=mssql_query($rs_doc);   
$rs_documento=mssql_query($rs_doc,$cn);
$rs1_documento=mssql_query($rs_doc,$cn);
$Totreg = mssql_num_rows($rs_documento);
//echo $consulta . "totreg". $Totreg;
$totx= $Totreg;
 //echo "alerta". $alerta ;
 while($reg_documento1 = mssql_fetch_array($rs1_documento)) 
		{
		  // buscando la cantidad de dias que lleva la fuactura en el sistema 
		       $fec_doc=strtotime($reg_documento1[fecha_recepcion]);
			   $fech_doc=date("d/m/Y",$fec_doc);
                $dia_a_buscar=substr($fech_doc,6,4).substr($fech_doc,3,2). substr($fech_doc,0,2);
                $hoy =date("d/m/Y");
				$fec_hoy = substr($hoy,6,4).substr($hoy,3,2). substr($hoy,0,2);
				$dias ="exec obtiene_dias '" . $dia_a_buscar ."','" .$fec_hoy ."'"; 
				$rs_dias = mssql_query($dias,$cn);
				$rd = mssql_fetch_array($rs_dias);
				$diasx= $rd["total"];
				
		    	if (($alerta=='R' && $diasx >31) || ($alerta=='A' && $diasx==15 ) || ($alerta =='V' && $diasx <31) || ($alerta=='')  )
		        { if ( is_null($alerta) ==true  ) $totx=$Totreg; else 
				$totx=$totx+1;}
         }
		  
//echo "consulta " . "<br>". $consulta . "tot" . $Totreg . "<br>"; 

//if($Totreg==0 )
if ($totx==0 )
 {
 $cons=1;
	echo '<html><body onload="javascript:document.form1.submit()">';
	echo '<form name="form1" method="post" action="busca_factura.php">';
	$fin='';
	$ini='';
	echo '<input type="hidden" name="idusuario" value="' . $xx . '">';
	echo '<input type="hidden" name="cusuario" value="' . $Usuario . '">';
	echo '<input type="hidden" name="idfuncionario" value="' . $fun . '">';
	echo '<input type="hidden" name="flujook" value="' . 1 . '">';
	echo '<input type="hidden" name="flujo1" value= "' . 1 . '">';
	echo '<input type="hidden" name="sw_cons" value="' . $cons . '">';
	echo '<input type="hidden" name="avanza" value="' . $si_avanza . '">';
	echo '<input type="hidden" name="avanzada" value="' . $avanzada . '">';
	echo '<input type="hidden" name="solocons" value="' . $solocons . '">';
	echo '<input type="hidden" name="soloconsulta" value="' . $solocons . '">';
	echo '<input type="hidden" name="menucons" value="' . $solocons . '">';
	echo '<input type="hidden" name="Txt_fecha_fin" value="' . $fin . '">';
	echo '<input type="hidden" name="Txt_fecha_ini" value="' . $ini . '">';
	echo "</form></body></html>";
	
}
else
{
$NumPag= intval($totx/10);
if(fmod($totx,10)==0) 
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
  if (grabaok=="1"){
  alert(" Trámite Grabado");}
  
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

function revisa_check(f) 	
  {
	var sicheck = 0;
  
for (var n=0; n < formulario1.elements.length; n++) {
     if (formulario1.elements[n].checked) {
	 
	     sicheck = 1; }
	         	 
}
	 if (sicheck == 0)  {
	           alert("Debe seleccionar una casilla");
			   return false; }
			  else 
			   return true; 	
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
<body bgcolor="#FFFFFF" text="#000000" topmargin="0" onload="carga()">
<center >
    <form name="formulario1" 
        method="post" 
		action="muestra_recep.php" 
		onsubmit="return revisa_check(this.form);">
    <table width="75%" border="0">
      <tr> 
        <td> 
          <p align="center"><b><font size="4" color="#0000FF">RESULTADO DE BUSQUEDA</font></b></font></p>
        </td>
      </tr>
      <tr> 
        <td height="21">&nbsp; </td>
      </tr>
    </table>
    <table width="650" border="0">
      <tr> 
	  <td><div align="left"><strong><?php echo "Total de Páginas : " . $NumPag ?></strong></div></td>
        <td><div align="right"><strong><?php echo "Total Registros : " . $totx ?></strong></div></td>
		
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
		  while($reg_documento = mssql_fetch_array($rs_documento)) 
		  {
		  // buscando la cantidad de dias que lleva la fuactura en el sistema 
		       $fec_doc=strtotime($reg_documento[fecha_recepcion]);
			   $fech_doc=date("d/m/Y",$fec_doc);
                $dia_a_buscar=substr($fech_doc,6,4).substr($fech_doc,3,2). substr($fech_doc,0,2);
                $hoy =date("d/m/Y");
				$fec_hoy = substr($hoy,6,4).substr($hoy,3,2). substr($hoy,0,2);
				$dias ="exec obtiene_dias '" . $dia_a_buscar ."','" .$fec_hoy ."'"; 
				$rs_dias = mssql_query($dias,$cn);
				$rd = mssql_fetch_array($rs_dias);
				$diasx= $rd["total"];
				//echo $diasx . "alerta".$alerta ; 
		 //				
			if (($alerta=='R' && $diasx >30) || ($alerta=='A' && $diasx==15 ) || ($alerta =='V' && $diasx <31)  || ($alerta==''))
	  { 
		   //  echo "alerta". $alerta . "$diasx" .$diasx;}
			$doc =$reg_documento["id_factura"];
			$rs_tramite="exec busca_tramite_minimo_facturas'" . $doc .  "'";
			$rs_proc=mssql_query($rs_tramite);
			$rs_min =mssql_fetch_array($rs_proc);
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
		   	  
	      
	echo "<table width='650' border='1' cellpadding='1' cellspacing='0' bgcolor='#E6EEFF' >"; 
	echo '<tr bgcolor="#6699FF" >';
    	echo '<td width="5%" height="33"><strong><font color="#FFFFFF" size="2">Num </font></strong></td>';
    	echo '<td width="6%" height="33"><strong><font color="#FFFFFF" size="2">Trámites</font></strong></td>';
    	echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Factura</font></strong></td>';
    	echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Monto</font></strong></td>';
    	echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Proveedor</font></strong></td>';
    	echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Tema Factura</font></strong></td>';
    	echo '<td width="100%" height="33"><strong><font color="#FFFFFF" size="2">Descripción</font></strong></td>';
    	echo '<td width="120" height="33"><strong><font color="#FFFFFF" size="2">Fecha Factura</font></strong></td>';
    	echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Procedencia</font></strong></td>';
    	echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Funcionario</font></strong></td>';
    	echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Destino</font></strong></td>';
    	echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Funcionario</font></strong></td>';
    	if ($solocons <> 1)
       	{  echo '<td width="6%" height="33"><strong><font color="#FFFFFF" size="2">Acción</font></strong></td>';   }	
    	echo '</tr>';
		
		  }
		  $Corre =  $Corre + 1;
		  ?>
    
	<tr>
        <td align="left" valign="middle" width="3%"><font size="2"><?php echo $Corre;?></font></td>
        <td align="left" valign="middle" width="5%">
        <?php 
echo '<a href="tramites_facturas.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . 
	  	'&iddocum=' . $reg_documento["id_factura"] . '&idseguim=' . $reg_documento["id_detalle"] .
		'&idfuncionario=' . $idfuncionario . '">Ver trámites</a>';  
      	?> 
    </td>
    	<td align="left" valign="middle" width="8%"><font size="2">
          <?php   echo $reg_documento["num_factura"];?></font>
        </td>
        <td align="left" valign="middle" width="5%"><font size="2">
          <?php echo $reg_documento["monto"];?></font>
        </td>
		 <td align="left" valign="middle" width="5%"><font size="2">
          <?php 
			echo $reg_documento["razon_social"];?></font>
        </td>
		 <td align="left" valign="middle" width="5%"><font size="2">
          <?php 
			  echo $reg_documento["desc_tema"];?></font>
        </td>
      <td align="left" valign="middle" width="20%"><font size="2"> 
        <?php if ($reg_documento["descripcion"]=="")
		           echo "&nbsp";
				   else echo $reg_documento["descripcion"];?>
        </font> </td>
        <td align="left" valign="middle" width="120%"><font size="2">
          <?php  $fec_doc=strtotime($reg_documento["fecha_factura"]);
		        $fech_doc=date("d/m/Y",$fec_doc);
                 echo $fech_doc;
          ?></font>
          
        </td>
        
      <td align="left" valign="middle" width="11%"><font size="2"> 
	  <?php // if ($reg_documento["procedencia"]=="")
	   if ($rs_min["procedencia"]=="")
		           echo "&nbsp";
			  else 
//			  echo $reg_documento["procedencia"];
			  echo $rs_min["procedencia"];?> </font> 
      </td>
        <td align="left" valign="middle" width="11%"><font size="2">
          <?php 
			  if ($rs_min["funcionario"]=="") {
			    $rs_min["funcionario"]="&nbsp";} 
	         
    	  	  echo $rs_min["funcionario"];?></font>
        </td>
      <td align="left" valign="middle" width="11%"><font size="2"> 
	  <?php 
	   if ($rs_min["destino"]=="")
		           echo "&nbsp";
			  else 
			  echo $rs_min["destino"];?> </font> 
      </td>
        <td align="left" valign="middle" width="11%"><font size="2">
          <?php 
			  if ($rs_min["funcionariodest"]=="") {
			    $rs_min["funcionariodest"]="&nbsp";} 
	         
    	  	  echo $rs_min["funcionariodest"];?></font>
        </td>
	    <td align="left" valign="middle" width="6%"> 
		
         <?php
           $acceso=0;
           if ($rs_min[tipo_procedencia] =='I')
			{
			$cons_query ="select * from acceso where id_usuario =". $xx . " and id_dependencia =" . $rs_min[id_procedencia];
 			$cons = mssql_query($cons_query,$cn);
 			$rs_cons =mssql_fetch_array($cons);
 			$tot_cons=mssql_num_rows($cons);
        	if ( $tot_cons== 1)
        	{  $acceso =1;}
           }
        else
        {  $acceso=1;}     
        if ($acceso==1)
        {   
 		  // con parametros desde el inicio 
 		if ($solocons <> 1)
 		{
		//corresponde reenvia e, reenvia_ofpartes es para la pantalla de oficina de partes , pruebas  rrenvi_ofpartes.php/
 
		  				echo '<a href="reenvia_factura.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario .  '&materiaold=' . $TxtMateria .  '&avanzada=' . $avanzada .
	      '&iddocum=' . $reg_documento["id_factura"] . '&idseguim=' . $rs_min["id_detalle"] .
	      '&idfuncionario=' . $idfuncionario . '&fechaini=' . $Txt_fecha_ini . '&fechafin=' . $Txt_fecha_fin .
	      '&TxtMateria=' . $reg_documento["desccripcion"] . '&avanza=' . $avanza. '&arreglo=' . $arreglo . '&mi_dependencia =' . $mi_dependencia . '&sw_cons =' . $sw_cons . 
	      '&Cbo_Tipo_Docto=' . $Cbo_Tipo_Docto .'&TxtInterno=' . $TxtInterno .
	      '&desc=' . $desc .'&tipo_procedencia=' . $tipo_procedencia . ' &tipo_destino=' . $tipo_destino . ' &CboDestinatario=' . $Cbo_Destinatario . ' &Cbo_Procedencia='. $Cbo_Procedencia . '">Reenviar</a>';
    		}
        }
        else
         {	 
        if ($solocons <>1)
         { 	
             echo '<img src="images/no_reenvia.gif" >';
          }
         }  //        if ($acceso==1)
   	
         //echo '<img src="images/Doc-043.jpg" >';
	         ?>
             </td>
      </tr>
    <?php if(fmod($Corre,10)==0) { 
	 echo "</table>";
	 echo "</div>";  } ?>
    <?php }
	} //while  ?></table>
    </div> 
    <br>
    <p>&nbsp; </p>
  </form>
   <?php
//}
} //else principal
 ?>	    
  <p>&nbsp; </p>
</center>  

</body>
</html>
