<?php
include("variables.php");
include("conexion_bd.php");
$Usuario=$cusuario;
$xx=$idusuario;
$fun=$idfuncionario;
// documentos seleccionados 
$casilla =$casilla_documento;    // documentos seleccionados de pagina anterior 
$Tot =$Totreg;
$doctos="";
$doc=0; 
// solamente cuenta  cuantos documentos hay seleccionados 
for ($i =0 ; ($i <= $Tot-1); $i++) 
   {
     if($casilla[$i]!=null) 
   	  {
		$doc=$doc+1;
	  }
   }
// guarda el string con los documentos seleccionados    
$segdoc=1;
for ($i =0 ; ($i <= $Tot-1); $i++) 
   {
     if($casilla[$i]!=null) 
   	  {
	    $seg=$casilla[$i];
	    if ($doc == 1)  // cuando es uno solo el seleccionado
			{  $doctos= $doctos . "and b.id_seguimiento=" . $seg ." " ;}
		else if (($doc >1) && ($segdoc==1))  // cuando es mas de uno seleccionado y es el primero de estos
    			{  $doctos= $doctos . "and (b.id_seguimiento=" . $seg . " or " ;}
				else if (($doc >1) && ($segdoc<>$doc)) // cuando es mas de uno seleccionado pero no es el ultimo
				   {  $doctos= $doctos . "b.id_seguimiento=" . $seg . " or " ;}
				else  if (($doc >1) && ($segdoc==$doc))  // cuando es mas de un seleccionado y es el ultimo 
				   {  $doctos= $doctos . "b.id_seguimiento=" . $seg . ") " ;}
				  
	     $segdoc=$segdoc+1;	
	  }
   }
//echo "Tot" . $Totreg . "doctos" . $doctos ;
$dia = substr($Txt_fecha_ini,0,2);
$mes = substr($Txt_fecha_ini,3,2);
$año = substr($Txt_fecha_ini,6,4);
$Fechaini = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));
$feci =$dia.'/'.$mes. '/'. $año;  

$dia = substr($Txt_fecha_fin,0,2);
$mes = substr($Txt_fecha_fin,3,2);
$año = substr($Txt_fecha_fin,6,4);
$Fechafin = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));
$fecf =$dia.'/'.$mes. '/'. $año;    

//-- busca los documentos ingresados en la fecha y que vienen de distintos lados 
$vectorint = split ("@",$arregloint);
$largoint=0;
$largoint=$vectorint[0];
$vectorext=split ("@",$arregloext);
$largoext=0;
$largoext=$vectorext[0];
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
a.fecha_sistema,b.id_procedencia,b.id_destino,b.tipo_destinatario,b.tipo_procedencia,b.id_nomina_despacho,b.id_seguimiento
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
 $consulta=$consulta . $procedencia . $destinatario . $doctos ;

$consulta = $consulta . "order by a.id_documento";

//echo "consulta" . $consulta ;
$qx= mssql_query($consulta,$cn);
$num= mssql_num_rows($qx);

//  obteniendo la  cantidad de páginas que se van a imprimir 
$num_pag =($num/3);
if (is_int($num_pag))
  {  $num_pag=intval($num_pag)+1; }
 else 
   { $num_pag=intval($num_pag)+1; }
$cont=0;
 ?>
<html>
<head>
<META Http-Equiv="Cache-Control" Content="no-cache">
<META Http-Equiv="Pragma" Content="no-cache">
<META Http-Equiv="Expires" Content="0">

<title>Imprime documentos</title>

 <script language="JavaScript" type="text/javascript">
 </script>
<style>div.break {page-break-before:always}</style> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="/css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#FFFFFF" topmargin="0">

<table width="611" height="37" border="0" align="center" cellpadding="1" cellspacing="0" bgcolor="#FFFFFF">
  <tr bgcolor="#6699FF"> 
    <td width="179" height="31"><font color="#0000FF">&nbsp;</font></strong></div></font></td>
    <td width="278" bordercolor="#FFFFFF"><div align="left"><font color="#FFFFFF" size="3"><strong>Documentos 
        </strong></font></div></td>  
  </tr>
</table>
<table width="695" height="26" border="0" align="center">
  <tr> 
    <td width="669" height="22" align="right" ><strong><font color="#0000FF"><strong></strong></font><font color="#000000" > 
      </font></strong></td>
  </tr>
</table>
<table align="center" width="631" height="38" border="0">
  <tr> 
    <td width="192" height="34">
     <div align="right"><strong><font size="3">Fecha inicio</font></strong></div></td>
    <td width="148"><font color="#000000" > <?php echo $feci; ?></font></td>
    <td width="97"><strong><font size="3">Fecha t&eacute;rmino</font></strong></td>
    <td width="176"><font color="#000000" > <?php  echo $fecf; ?> </font></td>
  
  </tr>
</table>
<table width="695" height="26" border="0" align="center">
  <tr> 
    <td width="528" height="22" align="right" ><strong></strong></td>
    <td width="157" align="right" ><div align="left"><strong><font color="#000000" >Total 
        p&aacute;ginas : </font></strong><?php  echo $num_pag ?></div></td>
  </tr>
</table>
<table width="657" align="center" border="1" cellspacing="1" cellpadding="1">
  <tr bgcolor="#3399FF"> 
    <td width="68"  height="33"><strong><font color="#FFFFFF" size="2" >Tipo doc.</font></strong></td>
    <td width="85" height="33"><strong><font color="#FFFFFF" size="2">Fecha doc.</font></strong></td>
    <!--td width="42"  height="33"><strong><font color="#FFFFFF" size="2">Nro Interno</font></strong></td>
    <td width="46"  height="33"><strong><font color="#FFFFFF" size="2">Nro Oficial</font></strong></td-->
    <td width="59"  height="33"><strong><font color="#FFFFFF" size="2">Nro Externo</font></strong></td>
    <td width="90"  height="33"><strong><font color="#FFFFFF" size="2">Materia</font></strong></td>
    <td width="82"  height="33"><strong><font color="#FFFFFF" size="2">Fecha Ingreso</font></strong></td>
    <td width="71"  height="33"><strong><font color="#FFFFFF" size="2">Procedencia</font></strong></td>
    <td width="105"  height="33"><strong><font color="#FFFFFF" size="2">Destino</font></strong></td>
    <td width="54"><strong><font color="#FFFFFF" size="2">N&oacute;mina</font></strong></td>
  </tr>
</table>
<?php 
$p=1;
echo '<table width="657" align="center" border="1" cellspacing="1" cellpadding="1" bgcolor="#E6EEFF">';
while($rsp=mssql_fetch_array($qx))
{
$cont=$cont + 1;
if($cont ==3)
{
 $p =$p+1;
echo '</table>';
echo '<div class="break"/>';
echo '<br>';
echo '<table width="657" align="center" border="1" cellspacing="1" cellpadding="1" bgcolor="#3399FF">';
echo '<tr>';
  
    echo ' <td    width="68" height="33"><strong><font color="#FFFFFF" size="2">Tipo doc.  </font></strong></td>';
    echo '<td width="85" height="33"><strong><font color="#FFFFFF" size="2">Fecha doc.</font></strong></td>';
   // echo '<td width="42"  height="33"><strong><font color="#FFFFFF" size="2">Nro Interno</font></strong></td>';
    //echo '<td width="46"  height="33"><strong><font color="#FFFFFF" size="2">Nro Oficial</font></strong></td>';
    echo '<td width="50"  height="33"><strong><font color="#FFFFFF" size="2">Nro Externo</font></strong></td>';
    echo '<td width="90"  height="33"><strong><font color="#FFFFFF" size="2">Materia</font></strong></td>';
    echo '<td width="82"  height="33"><strong><font color="#FFFFFF" size="2">Fecha Ingreso</font></strong></td>';
    echo '<td width="71"  height="33"><strong><font color="#FFFFFF" size="2">Procedencia</font></strong></td>';
    echo '<td width="105"  height="33"><strong><font color="#FFFFFF" size="2">Destino</font></strong></td>';
    echo '<td width="54"  height="33"><strong><font color="#FFFFFF" size="2">Nómina</font></strong></td>';
echo '</tr>';
echo '</table>';

echo '<table width="657" align="center" border="1" cellspacing="1" cellpadding="1" bgcolor="#E6EEFF">';
$cont=0;
}?>
<tr> 
            <td width="25" height="33"><font size="2">
			  <? 
			  $rs_tipo="exec busca_tipo_documento '" . $rsp[id_tipo_documento]. "'"; 
			  $rs_tipdoc=mssql_query($rs_tipo); 
              $reg = mssql_fetch_array($rs_tipdoc);
			  echo $reg[desc_tipo_documento]; ?> </font></td>
              <td width="10" height="33"><font size="2">
			  <? $fec_doc=strtotime($rsp[fecha_documento]);
		            		$fech_doc=date("d/m/Y",$fec_doc);
		     				echo $fech_doc;
			  ?> </font></td>
			  <!--td width="40" height="33"> <?  echo $rsp[num_interno];?> </td-->
			  <!--td width="40" height="33"> <?  echo $rsp[num_oficial];?> </td-->
			  <td width="30" height="33"><font size="2"> <? echo $rsp[num_externo];?></font> </td>
			  <td width="50" height="33"><font size="2"><? echo $rsp[materia]; ?></font></td>
			   <td width="10" height="33"> <font size="2">
			   <? 
			   $fec_doc=strtotime($rsp[fecha_sistema]);
		            		$fech_doc=date("d/m/Y",$fec_doc);
		     				echo $fech_doc;
			  ?> </font></td>
			   <td width="50" height="33"> <font size="2">
			   <? 
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
			  }?>
              </font></td>
			    <td width="30" height="33"> <font size="2">
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
					 
              </font></td>
		    <td width="10" height="33"> <font size="2">
			<? echo $rsp[id_nomina_despacho];?> </font></td>

    </tr>
   
  
  <?
  	}
	mssql_close($cn);
  	?>
</table>

<table width="653" height="51" border ="0" align="center" cellpadding="0"  cellspacing="0">
  <tr> 
 <td width="653" height="24"> <div align="center"> 
        <input name="cmd_aceptar" type="button" class="botones" onClick="javascript:window.print();" value="Imprimir">
  </div>
  <strong></strong></td>
</tr>
</table>
</body>
</html>
