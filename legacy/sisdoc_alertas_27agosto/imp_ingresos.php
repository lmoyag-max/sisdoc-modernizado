<?php
include("variables.php");
include("conexion_bd.php");
$Usuario=$cusuario;
$xx=$idusuario;
$fun=$idfuncionario;
// echo "tipo destino" . $tipodest. "<br>". "procedencia " . $tipo_procedencia ;
/*if ($Cbo_Procedencia > 0){
	if ($tipo_procedencia=="I"){
	  $rs_proc = mssql_query("select (desc_dependencia)descproc from dependencia  where id_dependencia =$Cbo_Procedencia",$cn);
	
	}
	else{
		  $rs_proc = mssql_query("select (desc_dependencia_externa)descproc from dependencia_externa  where id_dependencia_externa =$Cbo_Procedencia",$cn);
	}
	$reg_proc = mssql_fetch_array($rs_proc);
}*/

// ------------ Busca documentos pendientes por fecha_ini y fecha_fin  -------------------
//-- SON LOS QUE HAN LLEGADO Y ESTAN  con DESTINO CON ESTADO DESPACHADO Y/O RECEPCIONADO

$dia = substr($fecha_ini,0,2);
$mes = substr($fecha_ini,3,2);
$año = substr($fecha_ini,6,4);
$fechaini = date("Y/m/d H:i", mktime(0,0,0, $mes, $dia, $año));
$dia = substr($fecha_fin,0,2);
$mes = substr($fecha_fin,3,2);
$año = substr($fecha_fin,6,4);
$fechafin = date("Y/m/d H:i", mktime(23,59,59, $mes, $dia, $año));

$consulta ="select a.id_documento,a.num_interno,a.num_oficial,a.num_externo,  
        f.desc_tipo_documento,a.materia,a.fecha_documento,b.fecha_despacho,          
       b.id_seguimiento,b.id_procedencia,b.id_destino, g.desc_estado_tramite,b.dias_compromiso,c.desc_tipo_compromiso,
       procedencia=             
       case  b.tipo_procedencia             
          when 'I' then             
               (select desc_dependencia from dependencia where  b.id_procedencia=id_dependencia)            
          else            
               (select desc_dependencia_externa  from dependencia_externa where  b.id_procedencia=id_dependencia_externa)            
          end,
       funcprocedencia=
          case when b.rut_procedencia =' ' or b.rut_procedencia='0' then     ''    
             else 
		(select max(rtrim(funcionario.nombres)+ ' ' + rtrim(funcionario.apellidos))
		from funcionario where 
		b.rut_procedencia =funcionario.rut )
       End,
	   destino=           
       case  b.tipo_destinatario    
          when 'I' then             
               (select desc_dependencia from dependencia where  b.id_destino=id_dependencia)            
          else            
               (select desc_dependencia_externa  from dependencia_externa where  b.id_destino=id_dependencia_externa)            
          end,
       funcdestino=
          case when b.rut_destino =' ' or b.rut_destino='0' then     ''    
             else 
		(select max(rtrim(funcionario.nombres)+ ' ' + rtrim(funcionario.apellidos))
		from funcionario where 
		b.rut_destino =funcionario.rut )
       End             
       
          from documento  a,  tramite b,  tipo_documento f , estado_tramite g ,tipo_compromiso c 
          where  a.id_documento= b.id_documento   
                     
                and  f.id_tipo_documento= a.id_tipo_documento            
                and  (b.id_estado_tramite=2 or b.id_estado_tramite=3)
		and b.id_estado_tramite=g.id_estado_tramite 
		 and c.id_tipo_compromiso=b.id_tipo_compromiso
		and (b.fecha_despacho between '$fechaini' and '$fechafin')";


if ($Cbo_Procedencia==0)
{ $Cbo_proc="";   }
else{
   $Cbo_proc=" and b.id_procedencia=" . $Cbo_Procedencia ;
   $Cbo_proc= $Cbo_proc  . " and b.tipo_procedencia =" . "'" .$tipo_procedencia . "'"  ;
   $consulta =$consulta . $Cbo_proc;}
 
if ($Cbo_Func_Procedencia==0)
{ $Cbo_fproc="";   }
else{
   $Cbo_fproc=" and b.rut_procedencia=" . $Cbo_Func_Procedencia;
   $consulta =$consulta . $Cbo_fproc;}

if ($Cbo_Destinatario==0)
{ $Cbo_dest="";   }
else{
   $Cbo_dest=" and b.id_destino=" . $Cbo_Destinatario;
   $Cbo_dest= $Cbo_dest  . " and b.tipo_destinatario =" ."'" . $tipodest . "'"  ;
   $consulta =$consulta . $Cbo_dest;}
   		
if ($Cbo_Func_Destino==0)
{ $Cbo_fdest="";   }
else{
   $Cbo_fdest=" and b.rut_destino=" . $Cbo_Func_Destino;
   $consulta =$consulta . $Cbo_fdest;}
   
//$orden =" order by b.fecha_despacho,f.desc_tipo_documento   ";
$orden =" order by f.desc_tipo_documento, b.rut_destino,b.fecha_despacho";

$consulta = $consulta .  $orden;   
			
echo "consulta" . $consulta ;			

$reg_doc = mssql_query($consulta);				
$Totreg = mssql_num_rows($reg_doc);
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

	//echo '<html><body onload="document.form1.submit();">';
	//echo '<form name="form1" method="post" action="busca_ingresos.php">';
	echo '<input type="hidden" name="idusuario" value="' . $xx . '">';
	echo '<input type="hidden" name="cusuario" value="' . $Usuario . '">';
	echo '<input type="hidden" name="idfuncionario" value="' . $fun . '">';
	echo '<input type="hidden" name="flujook" value="' . 1 . '">';
	echo "</form></body></html>";

}
else
{
 if ((sw==0) and ($Totreg <>0))
 {
?> 
<html>
<head>
<title> </title>
<style>div.break {page-break-before:always}</style> 
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
/*function imprimeingreso()
{
  document.formulario1.submit();
 
<?php echo 'location.href="imprimeingreso.php?idusuario=' . $idusuario . "&fecha_ini=" . $fecha_ini . 
 "&cusuario=" . $cusuario .  "&fecha_fin=" . $fecha_fin .   
 "&idfuncionario=" . $idfuncionario .'";';?> 

}*/
	
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
          <p align="right"></p><div align="right"><strong><font color="#000000" ><?php echo '<a href="busca_ingresos.php?cusuario=' . $cusuario .
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
			<!--<td width="70"> <input name="cmd_imp" type="button" class="botones" onClick="imprimeingreso();" value="Imprimir"></td>-->
      </tr>
    </table>
     <input type="hidden" name="Totreg22" value="<?php echo $Totreg; ?>"> 
      <input type="hidden" name="NumLayer22" value="<?php echo $NumLayer; ?>"> 
	      <input type="hidden" name="idusuario" value="<? echo $xx;?>">
      <input type="hidden" name="docum">
		   <input type="hidden" name="seguim">
	</td> 
    <?php 
	      $Corre = 0;
		  $NumLayer = 0;
		  while($reg_documento = mssql_fetch_array($reg_doc)) { 
		  
		  
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
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Interno</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Oficial</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Tipo Documento</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Materia</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Procedencia</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Funcionario</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Destino</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Funcionario</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha despacho</font></strong></td>';
	echo '<td width="5%" height="33"><strong><font color="#FFFFFF" size="2">Estado Compromiso</font></strong></td>';
	echo '<td width="5%" height="33"><strong><font color="#FFFFFF" size="2">Dias Compromiso</font></strong></td>';
    echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Estado Trámite</font></strong></td>';
    echo '</tr>';
		 
		  
		  }
		  $Corre =  $Corre + 1;
		  ?>
    
	<tr>
        <!--<td align="left" valign="middle" width="3%"><font size="2"><!--?php echo $Corre;?></font></td>-->
		<td><?php echo $Corre;?></font></td>
		
        <td align="left" valign="middle" width="5%"><font size="2">
          <?php echo $reg_documento["num_interno"];?></font>
        </td>
		 <td align="left" valign="middle" width="5%"><font size="2">
          <?php echo $reg_documento["num_oficial"];?></font>
        </td>
	    <td align="left" valign="middle" width="9%"><font size="2">
          <?php echo $reg_documento["desc_tipo_documento"];?></font>
        </td>
	    <td align="left" valign="middle" width="20%"><font size="2">
          <?php echo $reg_documento["materia"];?></font>
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
        <td align="left" valign="middle" width="8%"><font size="2">
          <?php $fec_doc=strtotime($reg_documento["fecha_despacho"]);
		        $fech_doc=date("d/m/Y",$fec_doc);
				echo $fech_doc;?></font>
        </td>

	    <td align="left" valign="middle" width="9%"><font size="2">
          <?php echo $reg_documento["desc_tipo_compromiso"];?></font>
        </td> <td align="left" valign="middle" width="9%"><font size="2">
          <?php echo $reg_documento["dias_compromiso"];?></font>
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
<?php }} ?></form>
<p>&nbsp; </p>
</center>  

 <?php mssql_close($cn);?>

</body>
</html>
