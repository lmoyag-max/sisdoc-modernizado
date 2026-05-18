<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
$usua=$cusuario;
$xx=$idusuario;
$i =0; 

//$Txt_fecha_ini ='01/01/2006';
//$Txt_fecha_fin ='13/07/2006';

$dia = substr($Txt_fecha_ini,0,2);
$mes = substr($Txt_fecha_ini,3,2);
$año = substr($Txt_fecha_ini,6,4);
$Fechaini = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

$dia = substr($Txt_fecha_fin,0,2);
$mes = substr($Txt_fecha_fin,3,2);
$año = substr($Txt_fecha_fin,6,4);
$Fechafin = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

//
$vectorint = split ("@",$arregloint);
$largoint=0;
$largoint= $vectorint[0];
$vectorext = split ("@",$arregloext);
$largoext=0;
$largoext= $vectorext[0];
$proced="";
// Multi procedencia  Interno
	if ($largoint!=0)
	{
		$x=1;		
		$proced="";
		for($x=1;$x <=$largoint;$x++)
		{
		if ($x==1)
		 $proced= $proced. " and  (b.id_procedencia=" . $vectorint[$x] ; 
		 else 
		   $proced= $proced. " or  b.id_procedencia=" . $vectorint[$x] ; 
		}
	    $proced =$proced. ')';
	}
	else
// MUlti procedencia Externo
	if ($largoext!=0)
	{
		$x=1;		
		$proced ="";
		for($x=1;$x <=$largoext;$x++)
		{
		 if ($x==1)
		 $proced= $proced. " and  (b.id_procedencia=" . $vectorext[$x] ; 
		 else 
		   $proced= $proced. " or  b.id_procedencia=" . $vectorext[$x] ; 
		}
	    $proced =$proced. ')';		
		
	}
	else
// procedencia normal
		{
        $proced ="and  b.id_procedencia=" . $Cbo_Procedencia; 
		}

//
 //echo "consulta  procedencia " .  $proced . "<br>";

$consulta = "select a.id_documento,a.id_tipo_documento,a.num_interno,a.num_oficial,a.num_externo,a.materia ,a.fecha_documento,
a.fecha_sistema,b.id_procedencia,b.id_destino,b.tipo_destinatario,b.tipo_procedencia,b.id_nomina_despacho
 from  documento a , tramite b  
where a.id_documento = b.id_documento 
 
  and  (a.fecha_documento between '$Fechaini' and '$Fechafin')";
  
if ( $Cbo_Tipo_Docto== 0 ){
 $cbo_tipo= "";
 }
else{
  $cbo_tipo = " and a.id_tipo_documento=" . $Cbo_Tipo_Docto  ;
  $consulta=$consulta  . $cbo_tipo ;}
 
$procedencia="";
 If ($Cbo_Procedencia != 0)
 
       $procedencia =" and (b.tipo_procedencia=" . "'" . $tipo_procedencia . "'" . " and b.id_procedencia= " . $Cbo_Procedencia . ")";
	 else 
	  If (($Cbo_Procedencia == 0) && ($proced !=""))
	     $procedencia =" and (b.tipo_procedencia=" . "'" . $tipo_procedencia . "'" .$proced . ")";
  
 $destinatario="";
 If ($Cbo_Destinatario != 0){
     $destinatario =" and (b.tipo_destinatario=" . "'" . $tipo_destino . "'" . " and b.id_destino= " . $Cbo_Destinatario . ")";
 }	  
 $consulta=$consulta . $procedencia . $destinatario;

$consulta = $consulta . "order by a.id_documento";

//echo "consulta" . $consulta ;
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
 
    echo '<script>' ; 
	echo 'alert("No hay documentos en este Rango de Fechas ")';
	echo '</script>' ;
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
function imp_informe()
{
  document.form1.action="imp_informe.php";
  document.form1.submit();
}
	

//-->
</script>

<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body>
<table width="647" height="37" border="0" align="center" cellpadding="1" cellspacing="0" bgcolor="#FFFFFF">
  <tr bgcolor="#6699FF"> 
    <td width="200" height="31"><font color="#0000FF">&nbsp;</font></strong></div></font></td>
    <td width="332" bordercolor="#FFFFFF"><div align="center"><font color="#FFFFFF"><strong>INFORME DE DOCUMENTOS 
         </strong></font></div></td>
    <td width="109"> 
      <div align="right"><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font color="#FFFFFF"><strong><font size="2"><? echo "Usuario : " . $cusuario?></font></strong></font></strong></font></strong></font></strong></font></div></td>
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
        <td width="580">
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
		
		<td width="70"> <input name="cmd_imp" type="button" class="botones" onClick="imp_informe();" value="Imprimir"></td>
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
	echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Ver Tramites</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Tipo doc. </font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha doc.</font></strong></td>';
    //echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Interno</font></strong></td>';
    //echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Oficial</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Externo</font></strong></td>';
    echo '<td width="100%" height="33"><strong><font color="#FFFFFF" size="2">Materia</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha Ingreso</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Procedencia</font></strong></td>';
    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Destino</font></strong></td>';
	echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nómina</font></strong></td>';
	echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Marca</font></strong></td>';
	echo '</tr>';
		 	 
		  }
		  $Corre =  $Corre + 1;
		
		   ?>
			<tr>  
			 <td><?php echo $Corre;?></font></td>             
			 <td><?php echo '<a href="tramites.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . 
	  	'&iddocum=' . $rsp["id_documento"] . '&idseguim=' . $rsp["id_seguimiento"] .
		'&idfuncionario=' . $idfuncionario . '">Ver trámites</a>';  ;?></font></td>             
			 
			  <td width="8%" height="33"> 
			  <? $rs_tipo="exec busca_tipo_documento '" . $rsp[id_tipo_documento]. "'"; 
			  $rs_tipdoc=mssql_query($rs_tipo); 
              $reg = mssql_fetch_array($rs_tipdoc);
			  echo $reg[desc_tipo_documento]; ?> </td>
              <td width="8%" height="33">
			  <? $fec_doc=strtotime($rsp[fecha_documento]);
		            		$fech_doc=date("d/m/Y",$fec_doc);
		     				echo $fech_doc;
			  ?> </td>
			  <!--td width="8%" height="33"> <?  echo $rsp[num_interno];?> </td-->
			  <!--td width="8%" height="33"> <?  echo $rsp[num_oficial];?> </td-->
			  <td width="8%" height="33"> <? echo $rsp[num_externo];?> </td>
			  <td width="100%" height="33"><? echo $rsp[materia]; ?></td>
			   <td width="8%" height="33"> 
			   <? 
			   $fec_doc=strtotime($rsp[fecha_sistema]);
		            		$fech_doc=date("d/m/Y",$fec_doc);
		     				echo $fech_doc;
			  ?> </td>
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
  </form>
  </center>
</body>
</html>
