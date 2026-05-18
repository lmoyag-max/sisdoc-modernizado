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

// verificando cual viene chequeado como tipo de alerta a imprimir  
if ($tipo <> 1 && $tipo <> 2)
 {$tipo=3;}

$txtagno =date("Y");

$num= $doc;
$Totreg =$doc;
//echo "tot" . $Totreg ;
//  obteniendo la  cantidad de páginas que se van a imprimir 
$num_pag =($num/7);
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

<title></title>

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
    <td width="278" bordercolor="#FFFFFF"><div align="left"><font color="#FFFFFF" size="3"><strong>Documentos en Alerta
        </strong></font></div></td>  
  </tr>
</table>
<table width="695" height="26" border="0" align="center">
  <tr> 
    <td width="669" height="22" align="right" ><strong><font color="#0000FF"><strong></strong></font><font color="#000000" > 
      </font></strong></td>
  </tr>
</table>
<table width="695" height="26" border="0" align="center">
  <tr> 
    <td width="528" height="22" align="right" ><strong></strong></td>
    <td width="157" align="right" ><div align="left"><strong><font color="#000000" >Total 
        p&aacute;ginas : </font></strong><?php  echo $num_pag ?></div></td>
  </tr>
</table>
  <tr bgcolor="#3399FF"> 
      <td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Tipo tema </font></strong></td>
	  <td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nómina</font></strong></td>
<table width="659"  border="1" align="center"  cellspacing="1" cellpadding="1" bgcolor="#E6EEFF">
	  <td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Factura</font></strong></td>
	  <td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha factura</font></strong></td>
	  <td width="100%" height="33"><strong><font color="#FFFFFF" size="2">Descripción</font></strong></td>
      <td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Proveedor</font></strong></td>
      <td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Origen</font></strong></td>
	  <?php if ($tipo==3)?>
	   <td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Dias</font></strong></td>
	   
				   
  </tr>
<?php 
$p=1;
for ($i =0 ; ($i <= $Totreg); $i++) 
   {
     if($casilla[$i]!=null) 
   	  {
	    $seg=$casilla[$i];  // id_seguimiento del tramite que se ha seleccionado
		
		$rs_dep="exec busca_segdoc_alerta_facturas'" . $seg . "'";
		$rsdep=mssql_query($rs_dep); 
        $rsp = mssql_fetch_array($rsdep);

		$cont=$cont + 1;
		if($cont ==7)
			{
			 	$p =$p+1;
				echo '</table>';
				echo '<div class="break"/>';
				echo '<br>';
				echo '<table width="659" align="center" border="1" cellspacing="1" cellpadding="1" bgcolor="#E6EEFF">';
				echo '<tr bgcolor="#3399FF">';
		        echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Tipo tema </font></strong></td>';
				echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nómina</font></strong></td>';
			    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Factura</font></strong></td>';
			    echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha factura</font></strong></td>';
			    echo '<td width="100%" height="33"><strong><font color="#FFFFFF" size="2">Descripción</font></strong></td>';
			    echo '<td width="100%" height="33"><strong><font color="#FFFFFF" size="2">Proveedor</font></strong></td>';
    			echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Origen</font></strong></td>';
		        echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Dias</font></strong></td>'; 
		
				echo '</tr>';
				$cont=0;
			}?>
		  <tr> 
              <td width="8%" height="33"> 
			  <? echo $rsp[desc_tema]; ?> </td>
			  <td width="8%" height="33"> 
		      <? echo  $rsp[id_nomina_despacho];?>
			  </td>
			  <td width="8%" height="33"> <?  echo $rsp[num_factura];?> </td>
              <td width="8%" height="33">
			  <? $fec_doc=strtotime($rsp[fecha_factura]);
		  		 $fech_doc=date("d/m/Y",$fec_doc);
		      	 echo $fech_doc;
			  ?> </td>
			  <td width="100%" height="33"><? echo $rsp[descripcion]; ?></td>
			  <td width="100%" height="33"><? echo $rsp[razon_social]; ?></td>
			   <td width="8%" height="33"> 
			   <?  // desplegando nombre de procedencia 
			  echo $rsp[procedencia]; ?>
              </td>
			  
			   <td width="8%" height="33"> 
			 <font color="#804040">
        <?  // desplegando cantidad de dias  desde la recepcion en el minsal al dia actual 
		       $fec_doc=strtotime($rsp[fecha_recepcion]);
			   $fech_doc=date("d/m/Y",$fec_doc);
			  // echo $fech_doc;  // formato dd/mm/yyyy;
                $dia_a_buscar=substr($fech_doc,6,4).substr($fech_doc,3,2). substr($fech_doc,0,2);
                $hoy =date("d/m/Y");
				$fec_hoy = substr($hoy,6,4).substr($hoy,3,2). substr($hoy,0,2);
				$dias ="exec obtiene_dias '" . $dia_a_buscar ."','" .$fec_hoy ."'"; 
				$rs_dias = mssql_query($dias,$cn);
				$rdias = mssql_fetch_array($rs_dias);
				echo $rdias["total"];
			   ?>
        </font> 
			  </td>
			  
    	 </tr>
      <?
  	 }
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
