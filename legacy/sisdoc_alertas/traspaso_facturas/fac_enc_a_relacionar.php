<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;

$usua=$cusuario;
$xx=$idusuario;
$i =0; 
$dia = substr($Txt_fecha_ini,0,2);
$mes = substr($Txt_fecha_ini,3,2);
$año = substr($Txt_fecha_ini,6,4);
//se agrega la i para diferenciar las fechas 06/02/2009
$diai = substr($Txt_fecha_ini,0,2);
$mesi = substr($Txt_fecha_ini,3,2);
$añoi = substr($Txt_fecha_ini,6,4);
$Fechaini = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

$dia = substr($Txt_fecha_fin,0,2);
$mes = substr($Txt_fecha_fin,3,2);
$año = substr($Txt_fecha_fin,6,4);
// se agrega la f para difernciat las fechas 06/02/2009
$diaf = substr($Txt_fecha_fin,0,2);
$mesf = substr($Txt_fecha_fin,3,2);
$añof = substr($Txt_fecha_fin,6,4);
$Fechafin = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

$vectorint = split ("@",$arregloint);
$largoint=0;
$largoint= $vectorint[0];
$vectorext = split ("@",$arregloext);
$largoext=0;
$largoext= $vectorext[0];
$proced="";
/*$consulta="select distinct a.id_factura,a.rut_prov,a.fecha_factura,a.num_factura,a.monto,a.descripcion ,a.rut_prov,b.id_detalle,b.id_procedencia,b.id_destino,b.tipo_destinatario,b.tipo_procedencia,b.id_nomina_despacho".
          " from facturas a , detalle_facturas b   where     a.id_factura =b.id_factura ";

//		   and a.id_estado_documento = 1 se saca para dpoder relacionar abiertos y cerrados  15/04/2010
if ($Txt_fecha_ini =='')
 {$rangofec ='';}
else 
  {$rangofec="  and  (a.fecha_factura between '$Fechaini' and '$Fechafin')";
  $consulta=$consulta  . $rangofec ;}
if ( $num_fact== "" ){
 $numfactura= "";
 }
else{
  $numfactura = " and a.num_factura=" . $num_fact ;
  $consulta=$consulta  . $numfactura ;}
  
if ( $nomina== "" ){
 $numnom= "";
 }
else{
  $numnom = " and b.id_nomina_despacho=" . $nomina ;
  $consulta=$consulta  . $numnom ;}


//  buscando por descripcion //

$len = strlen($TxtMateria);
$mat = substr(trim($descripcion),-1);
if ($mat==","){
$descripcion=substr($descripcion,0,$len - 1);}
else
{$descripcion=$descripcion;}

$largo=0;
$largo= substr_count($descripcion ,"," );
$largo=$largo+1; 
if($descripcion==""){
$largo=0;}
$descripcion=$largo . "," . $descripcion;
$vector = split (",",$descripcion);
$largo= $vector[0];$x=1;
$sw_ok=0;
$mat1="";
if ($largo!=0){
for($x=1;$x <=$largo;$x++){
   //     $mat1 = $mat1 . "and a.materia  like '%" . trim($vector[$x]) . "%'" ;
        $mat1 =  "a.descripcion  like '%" . trim($vector[$x]) . "%'" ;
//	  echo "mat3" . $mat3. "<br>";
}
}
//$consulta = $consulta .  $mat1 ;
$consulta = $consulta . $mat1; 

// buscando destino ,origen //
 $procedencia="";
// if ($avanzada !=1 )
 //{
 If ($Cbo_Procedencia != 0){
     $procedencia =" and (b.tipo_procedencia=" . "'" . $tipo_procedencia . "'" . " and b.id_procedencia= " . $Cbo_Procedencia . ")";
 }
// }	  
 $destinatario="";
 If ($Cbo_Destinatario != 0){
     $destinatario =" and (b.tipo_destinatario=" . "'" . $tipo_destino . "'" . " and b.id_destino= " . $Cbo_Destinatario . ")";
 }	  
 $consulta=$consulta . $procedencia . $destinatario;


//$consulta=$consulta;
//echo "consulta" . $consulta ;
*/
	if ($nomina == '')
	$consulta="select distinct a.id_factura,c.id_detalle,a.fecha_recepcion,a.id_tema_fact,a.fecha_factura,a.descripcion,a.num_factura, a.monto ,b.desc_tema,d.razon_social,a.rut_prov,c.id_destino,c.id_procedencia,c.id_nomina_despacho,c.tipo_procedencia,c.tipo_destinatario
from facturas a,temas_facturas b, detalle_facturas c , proveedores d
where a.id_tema_fact= b.id_tema and a.id_estado_fact = 1 
and a.id_factura =c.id_factura
 and  a.rut_prov=d.rut_prov";
     else 
	$consulta="select distinct a.id_factura,c.id_detalle,a.fecha_recepcion,a.id_tema_fact,a.fecha_factura,a.descripcion,a.num_factura, a.monto ,b.desc_tema,d.razon_social,a.rut_prov,c.id_destino,c.id_procedencia,c.id_nomina_despacho,c.tipo_procedencia,c.tipo_destinatario
from facturas a,temas_facturas b, detalle_facturas c , proveedores d
where a.id_tema_fact= b.id_tema and a.id_estado_fact = 1 
and a.id_factura =c.id_factura
and  a.rut_prov=d.rut_prov
and c.id_nomina_despacho=".$nomina;
	 

if ($nomina =='')
{
if (($Cbo_Destinatario == 0) || ($Cbo_Procedencia ==0))
     $min= " and c.id_detalle in (select min(id_detalle) from detalle_facturas where id_factura =a.id_factura) ";
else 
    $min ='';	 

 $consulta =$consulta . $min ;
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

if ( $cbo_tipo_fact== 0 ){
 $cbo_tipo= "";
 }
else{
  $cbo_tipo = " and a.id_tipo_fact=" . $cbo_tipo_fact;
  $consulta=$consulta  . $cbo_tipo ;}
             
 
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
// echo "destino ". $tipo_destino;
 If ($Cbo_Destinatario != 0){
     $destinatario =" and (c.tipo_destinatario=" . "'" . $tipo_destino . "'" . " and c.id_destino= " . $Cbo_Destinatario . ")";
 }	  
 $consulta=$consulta . $procedencia . $destinatario;

$qx= mssql_query($consulta,$cn);
$num= mssql_num_rows($qx);
$Totreg = $num;
$NumPag= intval($Totreg/10);
if(fmod($Totreg,10)==0) 
  { 
  $NumPag = $NumPag;
  }
else
  {
  $NumPag=$NumPag + 1;
  }
if ($Totreg ==0 ) 
{
 
    /*echo '<script>' ; 
	echo 'alert("No hay documentos en este Rango de Fechas ")';
	echo '</script>' ;*/
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Informe  documentos que llegan de varios lugares a lugar especificio o a todos  </title>
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
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

function rescata_docto(idseg,iddoc)
{
document.form1.iddocum.value=iddoc;
document.form1.idseguim.value=idseg;

}
//-->
</script>

<link href="../../sisdoc/css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body>
<table width="647" height="37" border="0" align="center" cellpadding="1" cellspacing="0" bgcolor="#FFFFFF">
  <tr bgcolor="#6699FF"> 
    <td width="216" height="31" bordercolor="#FFFFFF"><font color="#FFFFFF">&nbsp;</font></td>
    <td width="353" bordercolor="#FFFFFF"><font color="#FFFFFF"><strong><?php echo "Seleccionar documento " .$num_doc . " para relacionar";?></strong></font></td>
    <td width="72" bordercolor="#FFFFFF"><input name="salir" type="button" id="salir3" value="Cancelar" onClick="parent.salir()"></td>
    <!--td width="109"> 
      <div align="right"><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font size="2"><? echo "Usuario : " . $cusuario?></font></strong></font></strong></font></strong></font></strong></font></div></td-->
  </tr>
</table>
<center>
<!-- Se   busca el documento original que llega  con sus datos de referencia y el dia que fue ingresado al sistema -->
<form name="form1" method="post" >
    <table width="650" border="0">
      <tr>
        <td><div align="left"><strong><?php echo "Total de Páginas : " . $NumPag ?></strong></div></td>
        <td><div align="right"><strong><?php echo "Total Registros : " . $Totreg ?></strong></div></td>
      </tr>
    </table>
    <table width="650" border="0">
      <tr> 
        <td width="560"> 
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
        <td width="80"><input name="aceptar" type="button" class="botones" onClick="parent.muestra_documento_a_relacionar(document.form1.iddocum.value,document.form1.idseguim.value,document.form1.num_doc.value);" value="Aceptar"></td>
      </tr>
    </table>
   
   
			<? 
			  $Corre = 0;
		      $NumLayer = 0;
     		  while ($rsp=mssql_fetch_array($qx))
			{
			 if(fmod($Corre,10)==0) 
		  { 
		  $NumLayer = $NumLayer + 1;
		  if($NumLayer==1){
  		 echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:130px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: visible">';
		   }
		   else
		   {
		 echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:130px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: hidden">';
		   }
		   
 
	echo "<table width='650' border='1' cellpadding='1' cellspacing='0' bgcolor='#E6EEFF'>"; 
	
	echo '<tr bgcolor="#6699FF">';
			  
    echo '<td width="5%" height="33"><strong><font color="#FFFFFF" size="2">Num</font></strong></td>';
	//echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Ver Tramites</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Num. factura </font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha  factura.</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Monto</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Proveedor</font></strong></td>';
    echo '<td width="100%" height="33"><strong><font color="#FFFFFF" size="2">Descripcion</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Procedencia</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Destino</font></strong></td>';
	echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nómina</font></strong></td>';
	echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Selecciona</font></strong></td>';
	
	echo '</tr>';
		 	 
		  }
		  $Corre =  $Corre + 1;
		
		   ?>
			<tr>  
			 <td><?php echo $Corre;  ?></font></td>             
			  <!--td><?php echo '<a href="tramites.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . 
	  	'&iddocum=' . $rsp["id_factura"] . '&idseguim=' . $rsp["id_detalle"] .
		'&idfuncionario=' . $idfuncionario . '">Ver trámites</a>';  ;?></font></td-->    
		
		 
			 
			  <td width="8%" height="33"> <?  echo $rsp[num_factura];?> </td>
			  
              <td width="8%" height="33">
			  <? $fec_doc=strtotime($rsp[fecha_factura]);
		            		$fech_doc=date("d/m/Y",$fec_doc);
		     				echo $fech_doc;
			  ?> </td>
			  <td width="8%" height="33"> 
			  <? 	  echo $rsp[monto]; ?> </td>
			   <td width="8%" height="33"> <?  echo $rsp[rut_prov];?> </td>
			  <td width="100%" height="33"><? echo $rsp[descripcion]; ?></td>
			   <td width="8%" height="33"> 
			   <?  // desplegando nombre de procedencia 
			    If ($rsp[tipo_procedencia]=="E")
				{$rs_dep="exec busca_dependecexternas '" . $rsp[id_procedencia]. "'"; 
			  $rsdep=mssql_query($rs_dep); 
              $reg = mssql_fetch_array($rsdep);			  
			  echo $reg[desc_dependencia_externa];
			    }
				else If ($rsp[tipo_procedencia]=="I")
				{$rs_dep="exec busca_dependecinternas '" . $rsp[id_procedencia]. "'"; 
			  $rsdep=mssql_query($rs_dep); 
              $reg = mssql_fetch_array($rsdep);			  
			  echo $reg[desc_dependencia];
               }				 ?>
              </td>
			    <td width="8%" height="33"> 
			   <? // desplegando nombre del destino dependendiedo del tipo es la tabla a la cual se va a buscar dependencia o dependencia_externa 
			      If ($rsp[tipo_destinatario]=="E")
				  	 {$rs_dep="exec busca_dependecexternas '" . $rsp[id_destino]. "'"; 
						  $rsdep=mssql_query($rs_dep); 
			              $reg = mssql_fetch_array($rsdep);
					   if ($reg["desc_dependencia_externa"]==NULL)
						{$externa="&nbsp";} 
						else 
						{$externa =$reg[desc_dependencia_externa];}
						echo $externa; 
					 }	
				   else 
    			      If ($rsp[tipo_destinatario]=="I")
					   
				  	  { 
					  $rs_dep="exec busca_dependecinternas '" . $rsp[id_destino]. "'"; 
						  $rsdep=mssql_query($rs_dep); 
			              $reg = mssql_fetch_array($rsdep);
						   
					   if ($reg["desc_dependencia"]==NULL)
						{$interna="&nbsp";}
						 else 
						 {$interna=$reg[desc_dependencia];}
					 echo $interna; 
					 }?>
					 
              </td>
			    <td width="8%" height="33"> 
				    <? echo  $rsp[id_nomina_despacho];?>
				</td>
			    <td width="8%" height="33"> 
			   <input name="selecciona" type="radio" value=""  onclick="rescata_docto(<?php echo $rsp["id_detalle"];?>,<?php echo $rsp["id_factura"];?>)"></font>
			    </td> 
            </tr>
		 <?php if(fmod($Corre,10)==0) { 
	 echo "</table>";
	 echo "</div>";  } ?>
    <?php } ?></table>
    </div> 
		 
	<input type="hidden" name="Txt_fecha_ini" value="<? echo $Txt_fecha_ini;?>">
	<input type="hidden" name="Txt_fecha_fin" value="<? echo $Txt_fecha_fin;?>">
  	<input type="hidden" name="arregloint" value="<? echo $arregloint;?>">
	<input type="hidden" name="arregloext" value="<? echo $arregloext;?>">
    <input type="hidden" name="Cbo_Tipo_Docto" value="<? echo $Cbo_Tipo_Docto;?>">
    <input type="hidden" name="Cbo_Procedencia" value="<? echo $Cbo_Procedencia;?>">
    <input type="hidden" name="Cbo_Destinatario" value="<? echo $Cbo_Destinatario;?>">
	<input type="hidden" name="proced" value="<? echo $proced;?>">
	<input type="hidden" name="tipo_procedencia" value="<? echo $tipo_procedencia;?>">
	<input type="hidden" name="tipo_destino" value="<? echo $tipo_destino;?>">
	<input type="hidden" name="Totreg" value="<?php echo $Totreg; ?>">
	<input type="hidden" name="cusuario" value ="<?php echo $cusuario;?>">
	<input type ="hidden" name="iddocum">
	<input type ="hidden" name="idseguim">
	<input type ="hidden" name="num_doc" value="<?php echo $num_doc;?>">
	
  </form>
  </center>
</body>
</html>
