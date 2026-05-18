
<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;

$usua=$cusuario;
$xx=$idusuario;
$iddoc=$iddocum;

//$ps_busca_padre = "select * from historia5($iddocum)";
//$ps_busca_padre = "select * from llama_historia($iddocum) order by id_documento,id_seguimiento ";
$ps_busca_padre = "select * from facturas order by id_factura ";
$rs_p = mssql_query($ps_busca_padre,$cn); 
$rs_p2 = mssql_query($ps_busca_padre,$cn); 

$tot_padre = mssql_num_rows($rs_p);

// fin de sacar el total de tramites 
$total=$tot_padre;
$Totreg =$total;
$NumPag= intval($Totreg/100);
if(fmod($Totreg,100)==0) 
  { 
  $NumPag = $NumPag;
  }
else
  {
  $NumPag=$NumPag + 1;
  }		  
 
 ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
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
<form name="form1" method="post" >
   
    <table width="651" border="1" cellpadding="1" cellspacing="0">
      <tr>
        <td width="645" height="20" bgcolor="#6699FF"><div align="center"><font color="#FFFFFF" size="4"><strong>TRAMITES 
            DEL FACTURAS</strong></font></div></td>
      </tr>
    </table>	
    <table width="657" border="0">
      <tr> 
          <td width="651"><div align="right"><strong><font color="#0000A0" size="2"><?echo "Usuario : " . $usua;?></font></strong></div></td>
      </tr>
    </table>
    <table width="651" border="0">
      <tr> 
 	    <td><div align="left"><strong><?php echo "Total de Páginas : " . $NumPag ?></strong></div></td>
                 <td><div align="right"><strong><?php echo "Total Trámites : " . $Totreg ?></strong></div></td>
      </tr>
    </table>
    <table width="650"  border="0">
      <tr> 
        <td height="23" > 
          <?php
   	       echo "<div align='left'><b>";
	        for ($i = 1; $i <= $NumPag; $i++)
			 {	echo "<img src='botones/boton100_" . $i . ".gif' width='44' height='16'". "onClick=\"MM_showHideLayers('layer" . $i . "','','show',$i, $NumPag)\">"; } 
			echo "</b></div>";
          ?>
          <input type="hidden" name="Totreg22" value="<?php echo $Totreg; ?>"> 
          <input type="hidden" name="NumLayer22" value="<?php echo $NumLayer; ?>"> 
        </td>
      </tr>
    </table>

	  <?php
		// Se agregan estas lineas para que solo el ultimo documento tenga opcion de derivar 
    	$iddocumento ="";
         while ($rs_pad=mssql_fetch_array($rs_p))
		 {
		     if ($iddocumento <>$rs_pad[id_documento])
			 {
			     $p_docto=$rs_pad[id_documento];
			 }
		 }   
		  $rs_p = mssql_query($ps_busca_padre,$cn);
          $Corre = 0;
	      $NumLayer =0;  
		  $valor = 120;
		  $reg = 0;
		  //$Totreg;
		  while ($rs_padre=mssql_fetch_array($rs_p))
		  { 
		  // echo "doc" . $iddocumento . "registro" . $rs_padre[id_documento]."<br>"; 
			if(fmod($Corre,100)==0)  
		      {  
		       	 $NumLayer = $NumLayer + 1;				  
			      if($NumLayer==1) // cuando pasa a otra pantalla 
		    		{  //echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:8px; top:120px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility:visible">';   }
					  echo '<div id="layer' . $NumLayer .'"  style="position:absolute; left:8px; top:'.$valor .'px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility:visible">';  
		      	    }
				  else
		        	{   
					  echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:8px; top:120px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: hidden">';   } 
					  if ($iddocumento <> $rs_padre[id_documento])
					}
			           ?>	<table width="627" height="172" border="1" >
					        <tr>                 
				            <td width="617" height="166" bgcolor="#9CCBED"> 
                            <table width="100%" border="0">
                  		        		<tr> 
		                   		  	    <td bgcolor="#6699FF"><font color="#FFFFFF"><strong> INFORMACION DOCUMENTO DE REFERENCIA</strong></font></td>
			                  		  	</tr>
					                   </table>
						           <table width="100%" border="0" cellpadding="2" cellspacing="2" bgcolor="#C3D6E6">
						 			<tr> 
			      		  			<td width="121"><strong>Tipo de Docto</strong></td>
                    	  	      	<td width="131"><? echo  $rs_padre[desc_tipo_documento];?> </td>
                    	  	      	<td width="107"><b>Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></td>
                    	  	      	<td width="90"> 
									<?
                                     $fec_doc=strtotime($rs_padre[fecha_documento]);
				            		$fech_doc=date("d/m/Y",$fec_doc);
					     			 echo $fech_doc;
			    				     ?>	
            		      	         </td>
                 		      	    
   		     	      				 </table>
									 <table width="100%" border="0"  cellpadding="1" cellspacing="0" bgcolor="#C3D6E6">
						            	<tr valign="middle"> 
						              	<td width="97"><b><i>Numeros: Interno<font size="4" face="Arial"></font></i></b></td>
              							<td width="66"> <? echo $rs_padre[num_interno]; ?> </td>
              							<td width="39"> <b>Fecha Recepcion </b></td>
              							<td width="43"> 
                					<? 
			        				$fec_doc=strtotime($rs_padre[fecha_recepcion]);
		                            $fech_doc=date("d/m/Y",$fec_doc);
		                    	    if ($rs_padre[fecha_recepcion] <>NULL)
		                			 	{  echo $fech_doc;}     			      	
			                   		     ?>
				              </td>
            				</tr>
				          </table>       
        						  <table width="100%" border="1" cellpadding="1" cellspacing="0" bgcolor="#C3D6E6">
			    			      <tr> 
				            	  <td width="17%"><b>Descripcion</b></td>
              					  <td width="83%"> <? echo $rs_padre[descripcion];			
				                  $i=i+1;
				               ?> </td>
                                  </tr>
                        </table></tr>
            	</table> 		                         
              
	<table width="674" height="20" border="0" cellpadding="1" cellspacing="1">
      <tr> 
        <td width="670" > <div align="center"><font color="#800000"><strong>TRAMITES 
            ASOCIADOS</strong></font></div></td>
      </tr>
    </table>
   <table width="831" border="1" cellpadding="1" cellspacing="0" bgcolor="#D1D7DC">
      <tr> 
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
        <td width="52"><font color="#02392D"><strong>Ingresado por</strong></font></td>
        <td width="52"><strong><font color="#02392D">Recepci&oacute;n por</font></strong></td>
        <td width="52"><font color="#02392D"><strong>Estado Trámite</strong></font></td>
      </tr>     
        <?php
		 $busca_detalle ="select * from detalle_facturas where id_factura =" . $rs_padre[id_factura];
		 $rs_det=mssql_query($busca_detalle,$cn);
		  
       while (	$rg_detalle=mssql_fetch_array($rs_det))
	   
        {
		?><tr> 
        <td width="45" height="25"> <?php echo  $rg_detalle["id_nomina_despacho"];?></td>
        <td width="78"> 
          <?php  
				if ($rg_detalle["id_procedencia"]=="") {
			    $rg_detalle["id_procedencia"]="&nbsp";} 
				echo $rg_detalle["id_procedencia"]; ?>
        </td>
        <td width="71"> 
          <?php 
			/*  if ($rs_padre["nombre_procedencia"]=="") {
			    $rs_padre["nombre_procedencia"]="&nbsp";} 
			  echo $rs_padre["nombre_procedencia"]; 
			  */ ?>
        </td>
        <td width="58"><?php echo $rg_detalle["id_destino"]; ?> </td>
        <td width="76"> 
          <?php
			  /*if ($rs_padre["nombre_destino"]=="") {
			    $rs_padre["nombre_destino"]="&nbsp";}
				 echo $rs_padre["nombre_destino"]; 
				*/ ?>
        </td>
        <td width="72"><?php echo $rg_detalle["desc_tipo_distribucion"]; ?> </td>
        <td width="57"> 
          <?php 
			        $fec_reg=strtotime($rg_detalle["fecha_sistema"]);
		             $fec_reg=date("d/m/Y",$fec_reg);
				 echo $fec_reg; ?>
        </td>
        <td width="28"> 
          <?php 
                 if ($rg_detalle["fecha_despacho"]==NULL)
				{		
						 $fec_tra="&nbsp";
				   echo $fec_tra;}
				else 
				 {
				 $fec_tra=strtotime($rg_detalle["fecha_despacho"]);
		         $fec_tra=date("d/m/Y",$fec_tra);
				 echo $fec_tra; }?>
        </td>
        <td width="29"> 
          <?php 
                  if ($rg_detalle["fecha_recepcion"]==NULL)
				{		
						 $fec_rec="&nbsp";
				   echo $fec_rec;}
				else 
				 {
				 $fec_rec=strtotime($rs_padre["fecha_recepcion"]);
		         $fec_rec=date("d/m/Y",$fec_rec);
				 echo $fec_rec; }?>
        </td>
        <td width="43"> 
          <?php
			  if ($rg_detalle["observaciones"]=="") {
			    $rg_detalle["observaciones"]="&nbsp";}
			   echo $rg_detalle["observaciones"]; ?>
        </td>
        <td width="43"> 
          <?php
			  if ($rg_detalle["nombre_usuario"]=="") {
			    $rg_detalle["nombre_usuario"]="&nbsp";}
			   echo $rg_detalle["nombre_usuario"]; ?>
        </td>
        <td width="43"> 
          <?php
			  if ($rg_detalle["usuario_recepcion"]=="") {
			     $nombre_recepciona="&nbsp";}
				else 
				{$query="select usuario from usuario where id_usuario =". $rg_detalle["usuario_recepcion"];
				 $regq=mssql_query($query,$cn);
				 $reg=mssql_fetch_array($regq);
				 $nombre_recepciona=$reg["usuario"];
				}
			  echo $nombre_recepciona; ?>
        </td>
        <td width="52"> 
          <?php
				  if ($rg_detalle["desc_estado_tramite"]=="") {
				    $rg_detalle["desc_estado_tramite"]="&nbsp";}
				   echo $rg_detalle["desc_estado_tramite"]; ?>
        </td>
        <?php /*if ($rs_padre["id_documento"]==$p_docto){?>
        <!--td width="59"><!--?php echo '<a href="derivar_tramites.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario .  '&iddocum=' . $rs_padre["id_documento"] . '&idseguim=' . $rs_padre["id_seguimiento"] . '&idfuncionario=' . $idfuncionario . '">Derivar</a>'; ?> 
              </td-->
        <!-- se agrega año y nomina -->
        <td width="59"><?php echo '<a href="derivar_tramites.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario .  '&iddocum=' . $rs_padre["id_documento"] . '&idseguim=' . $rs_padre["id_seguimiento"] . '&idfuncionario=' . $idfuncionario . '&txtagno=' . $txtagno . '&txtnomina=' . $txtnomina . '">Derivar</a>'; ?> 
        </td>
        <? }*/?>
      </tr>
				        <td width="59"><?php /*echo '<a href="derivar_tramites.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario .  '&iddocum=' . $rs_padre["id_documento"] . '&idseguim=' . $rs_padre["id_seguimiento"] . '&idfuncionario=' . $idfuncionario . '&txtagno=' . $txtagno . '&txtnomina=' . $txtnomina . '">Derivar</a>'; */?> 
				        </td>
						 </tr>
						 
				        <?  $iddocumento= $rs_padre[id_documento]; 
						} // while detalle 
						 }
						}
						
		 
	    

	  
	 $Corre =$Corre +1; 
	 if(fmod($Corre,100)==0) 
	   { 
	     echo "</table>";
	     $valor =$valor + 164;
		 //echo "doc" . $iddocumento . "regdoc" . $rs_padre[id_documento];
		 echo "</div>";
        }
	}// while ($rs_padre=mssql_fetch_array($rs_p))
	?>	  </table> 
  </form>
</center>
</body>
</html>
