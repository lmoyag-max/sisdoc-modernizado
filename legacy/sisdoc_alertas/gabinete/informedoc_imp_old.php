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
 

$consulta = "select a.id_documento,a.id_tipo_documento,a.num_interno,a.num_oficial,a.num_externo,a.materia ,a.fecha_documento,
a.fecha_sistema,b.id_procedencia,b.id_destino,b.tipo_destinatario,b.tipo_procedencia
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
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Informe tres </title>
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
	
	

//-->
</script>

<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body>
<center>
<!-- Se   busca el documento original que llega  con sus datos de referencia y el dia que fue ingresado al sistema -->
<form name="form1" method="post" >
  	   
    <table width="826" border="1" cellpadding="1" cellspacing="0" bgcolor="#D1D7DC">
      <tr> 
              <td width="106"><font color="#02392D"><strong>Tipo doc.</strong></font></td>
			  <td width="83" ><font color="#02392D"><strong>Fecha doc.</strong></font></td>
			  <td width="80" height="20"><font color="#02392D"><strong>Nº Interno</strong></font></td>
			  <td width="78" height="20"><font color="#02392D"><strong>Nº Oficial</strong></font></td>
			  <td width="77" height="20"><font color="#02392D"><strong>Nº Externo</strong></font></td>
			  <td width="269"><font color="#02392D"><strong>Materia</strong></font></td>
			  <td width="103"><font color="#02392D"><strong>Fecha Ingreso</strong></font></td>
			  <td width="103"><font color="#02392D"><strong>Procedencia</strong></font></td>
			   <td width="103"><font color="#02392D"><strong>Destino</strong></font></td>
			  
            </tr>
			<? while ($rsp=mssql_fetch_array($qx))
			{?>
			<tr>               
			  <td width="106" height="25"> 
			  <? $rs_tipo="exec busca_tipo_documento '" . $rsp[id_tipo_documento]. "'"; 
			  $rs_tipdoc=mssql_query($rs_tipo); 
              $reg = mssql_fetch_array($rs_tipdoc);
			  echo $reg[desc_tipo_documento]; ?> </td>
              <td width="83" height="25">
			  <? $fec_doc=strtotime($rsp[fecha_documento]);
		            		$fech_doc=date("d/m/Y",$fec_doc);
		     				echo $fech_doc;
			  ?> </td>
			  <td width="80" height="20"> <?  echo $rsp[num_interno];?> </td>
			  <td width="78" height="20"> <?  echo $rsp[num_oficial];?> </td>
			  <td width="77" height="20"> <? echo $rsp[num_externo];?> </td>
			  <td width="269" height="25"><font size="2"> <? echo $rsp[materia]; ?> </font></td>
			   <td width="103" height="25"> 
			   <? 
			   $fec_doc=strtotime($rsp[fecha_sistema]);
		            		$fech_doc=date("d/m/Y",$fec_doc);
		     				echo $fech_doc;
			  ?> </td>
			   <td width="103" height="25"> 
			   <? $rs_dep="exec busca_dependecexternas '" . $rsp[id_procedencia]. "'"; 
			  $rsdep=mssql_query($rs_dep); 
              $reg = mssql_fetch_array($rsdep);
			  echo $reg[desc_dependencia_externa]; ?>
              </td>
			    <td width="103" height="25"> 
			   <? // desplegando nombre del destino dependendiedo del tipo es la atabla ca la cual se va a buscar dependencia o dependencia_externa 
			      If ($rsp[tipo_destinatario]=="E")
				  	 {	$rs_dep="exec busca_dependecexternas '" . $rsp[id_destino]. "'"; 
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
				  	 {	$rs_dep="exec busca_dependecinternas '" . $rsp[id_destino]. "'"; 
						  $rsdep=mssql_query($rs_dep); 
			              $reg = mssql_fetch_array($rsdep);
						   
					   if ($reg["desc_dependencia"]==NULL)
						{$interna="&nbsp";}
						 else 
						 {$interna=$reg[desc_dependencia];}
					 echo $interna; 
					 }?>
					 
              </td>
            </tr>
			<? }?>
          </table>
		 
		 
  </form>
  </center>
</body>
</html>
