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

$consulta = "select a.id_documento,a.id_tipo_documento,c.desc_tipo_documento,a.num_interno,a.num_oficial,a.num_externo,a.materia ,a.fecha_documento,
b.id_procedencia,b.id_destino,b.fecha_despacho,b.fecha_recepcion,b.observaciones
 from  documento a , tramite b , tipo_documento c 
where a.id_documento = b.id_documento 
 and a.id_tipo_documento=c.id_tipo_documento
  and  (a.fecha_documento between '2010/01/01 00:00' and '2010/08/31 00:00')
 
   and b.id_seguimiento in (select min(id_seguimiento) from tramite where id_documento=a.id_documento and tipo_procedencia ='I' and id_procedencia =8)";

$consulta = $consulta . " order by a.id_documento";
// echo "consulta" . $consulta ;
$qx= mssql_query($consulta,$cn);
$num= mssql_num_rows($qx);

while($cons=mssql_fetch_array($qx))
{
 
    
$doc =$cons["id_documento"];
echo "doc" . $cons["id_documento"];

$ps_busca_padre = "select * from llama_historia($doc)";

$rs_p = mssql_query($ps_busca_padre,$cn); 
$rs_reg=mssql_query($ps_busca_padre,$cn);  // se usa para sacar el primer tramite y el ultimo 
$rs_reg1=mssql_query($ps_busca_padre,$cn);  // se usa para sacar el tramite que tiene fecha de despacho mayor al resto 
$tot_padre = mssql_num_rows($rs_p); // total de trámites que tiene el doc 
//echo "tot" . $tot_padre;

$i=0;
$i=$i+1;
while ($rsp=mssql_fetch_array($rs_reg))
{
  if ($i==1 ) // primer tramite 
  { 
    if ($rsp[id_documento]==$cons[id_documento])
    {
    $idseg1=$rsp[id_seguimiento];
    $fechadespacho=$rsp[fecha_despacho];
    }
    
  }
  if ($i==$tot_padre)
  { $idseg2= $rsp[id_seguimiento];}
  
     $i=$i+1;
   
} 
// buscando el utlimo tramite pero en base a la ultima fecha de despacho
$i=1 ;
while ($rsp1=mssql_fetch_array($rs_reg1))
{
    $fecha1=strtotime($rsp1[fecha_despacho]);
	$fecha2=strtotime($fechadespacho);
    if ($fecha1> $fecha2)
	   {
	   $fechadespacho =$rsp1[fecha_despacho];
	   $idseg3 = $rsp1[id_seguimiento];
	   $fecha3= $fechadespacho;
	   }
	 $i=$i+1;   
}   
$idseg2=$idseg3;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Informe dos  </title>
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
<!-- Se   busca el documento original que llega , y se va a mostrar el primer trámite del documento y el último trámite del mimso -->
<form name="form1" method="post" >
  	   <?php
		 $iddocumento="";		
		  while ($rs_padre=mssql_fetch_array($rs_p))
		  { 
		    
    		 if  (($iddocumento <> $rs_padre[id_documento]) 
     		   && (($idseg1==$rs_padre[id_seguimiento]) || ($idseg2==$rs_padre[id_seguimiento]))) 
		    { ?>	    
          <table width="743" border="1" cellpadding="1" cellspacing="0" bgcolor="#D1D7DC">
            <tr> 
              <td width="70"><font color="#02392D"><strong>Tipo doc.</strong></font></td>
			  <td width="45"><font color="#02392D"><strong>Fecha_doc</strong></font></td>
			  <td width="45"><font color="#02392D"><strong>Num_interno</strong></font></td>
			  <td width="45"><font color="#02392D"><strong>Num_oficial</strong></font></td>
			  <td width="45"><font color="#02392D"><strong>Num_externo</strong></font></td>
			  <td width="70"><font color="#02392D"><strong>Materia</strong></font></td>
			  <td width="45"><font color="#02392D"><strong>N&oacute;mina</strong></font></td>
              <td width="78"><font color="#02392D"><strong>Procedencia</strong></font></td>
              <td width="71"><font color="#02392D"><strong>Funcionario</strong></font></td>
              <td width="58"><font color="#02392D"><strong>Destino</strong></font></td>
              <td width="76"><font color="#02392D"><strong>Funcionario</strong></font></td>
              <td width="72"><font color="#02392D"><strong>Tipo Distribucion</strong></font></td>
              <td width="57"><font color="#02392D"><strong>Fecha Registro</strong></font></td>
              <td width="57"><font color="#02392D"><strong>Fecha Despacho</strong></font></td>
              <td width="57"><font color="#02392D"><strong>Fecha Recepcion</strong></font></td>
              <td width="43"><font color="#02392D"><strong>Observaciones</strong></font></td>
              <td width="52"><font color="#02392D"><strong>Estado Trámite</strong></font></td>
            </tr>
            <? }
			if (($idseg1==$rs_padre[id_seguimiento])|| ($idseg2==$rs_padre[id_seguimiento]))			
			{
			?>
            
			<tr>               
			  <td width="70" height="25"> <? echo $rs_padre[desc_tipo_documento]; ?> </td>
              <td width="45" height="25">
			  <? $fec_doc=strtotime($rs_padre[fecha_documento]);
		            		$fech_doc=date("d/m/Y",$fec_doc);
		     				echo $fech_doc;
			  ?> </td>
			  <td width="45" height="25"> <?  echo $rs_padre[num_interno];?> </td>
			  <td width="45" height="25"> <?  echo $rs_padre[num_oficial];?> </td>
			  <td width="45" height="25"> <? echo $rs_padre[num_externo];?> </td>
			  <td width="70" height="25"> <? echo $rs_padre[materia]; ?> </td>
              <td width="45" height="25"> <? echo $rs_padre[id_nomina_despacho];?></td>
              <td width="78"> 
                <?php  
				if ($rs_padre["procedencia"]=="") {
			    $rs_padre["procedencia"]="&nbsp";} 
				echo $rs_padre["procedencia"]; ?>
              </td>
              <td width="71"> 
                <?php 
			  if ($rs_padre["nombre_procedencia"]=="") {
			    $rs_padre["nombre_procedencia"]="&nbsp";} 
			  echo $rs_padre["nombre_procedencia"]; 
			   ?>
              </td>
              <td width="58"><?php echo $rs_padre["destino"]; ?> </td>
              <td width="76"> 
                <?php
			  if ($rs_padre["nombre_destino"]=="") {
			    $rs_padre["nombre_destino"]="&nbsp";}
				 echo $rs_padre["nombre_destino"]; 
				 ?>
              </td>
              <td width="72"><?php echo $rs_padre["desc_tipo_distribucion"]; ?> 
              </td>
              <td width="57"> 
                <?php 
			        $fec_reg=strtotime($rs_padre["fecha_sistema"]);
		             $fec_reg=date("d/m/Y",$fec_reg);
				 echo $fec_reg; ?>
              </td>
              <td width="28"> 
                <?php 
        	    if ($rs_padre["fecha_despacho"]==NULL)
					{$fec_tra="&nbsp";
				   echo   $fec_tra;}
				else 
				 {
				 $fec_tra=strtotime($rs_padre["fecha_despacho"]);
		         $fec_tra=date("d/m/Y",$fec_tra);
				 echo $fec_tra; }?>
              </td>
              <td width="29"> <?php 
                  if ($rs_padre["fecha_recepcion"]==NULL)
				{$fec_rec="&nbsp";
				   echo $fec_rec;}
				else 
				 {
				 $fec_rec=strtotime($rs_padre["fecha_recepcion"]);
		         $fec_rec=date("d/m/Y",$fec_rec);
				 echo $fec_rec; }?></td>
              <td width="43"> 
                <?php
			  if ($rs_padre["observaciones"]=="") 
			  {$rs_padre["observaciones"]="&nbsp";}
			   echo $rs_padre["observaciones"]; ?>
              </td>
              <td width="52"> 
                <?php
				  if ($rs_padre["desc_estado_tramite"]=="")
				   { $rs_padre["desc_estado_tramite"]="&nbsp";}
				   echo $rs_padre["desc_estado_tramite"]; ?>
              </td>
            </tr>
            <? 
			  }
			   $iddocumento= $rs_padre[id_documento];
			   } ?>
          </table>
          </tr>
  </table>
  </form>
  </center>
</body>
</html>
<? } ?>