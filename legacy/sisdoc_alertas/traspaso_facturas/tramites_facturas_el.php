<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
$TxtMateria=ltrim($TxtMateria) ;
$Cbo_temas_fact=$Cbo_temas_fact;
$Cbo_Destinatario=$Cbo_Destinatario ;  
$Cbo_Procedencia=$Cbo_Procedencia ;
$tipo_procedencia=$tipo_procedencia ; 
$tipo_destino=$tipo_destino ; 
$rut_procedencia=$rut_procedencia; 
$rut_destino=$rut_destino; 
$obs  =$obs;
$fecha_sis1 =$fecha_sis1; 
$fecha_sis2 =$fecha_sis2 ; 
$cons=$sw_cons;
$usua=$cusuario;
$xx=$idusuario;
$iddoc=$iddocum;
//echo "iddocum" .  $iddoc . "<br>";
//echo "idusu" . $idusuario . "*** usu " . $cusuario . "** acc " . $accion . "** fun " . $idfuncionario . "** docu " . $iddocum . "** seg " . $idseguim ;

//$ps_busca_padre = "select * from historia5($iddocum)";
$ps_busca_padre = "select * from llama_historia_factura($iddocum)";
$rs_p = mssql_query($ps_busca_padre,$cn); 
//$reg_busca_padre = mssql_fetch_array($rs_busca_padre);
$tot_padre = mssql_num_rows($rs_p);
if($tot_padre==1)
{
$sw_elim=1;
}
else
{
$sw_elim=0;
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?php $iddocum; ?>< Edicion></title>
</script>
<!--meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"-->
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
</script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body>
<center>
<form name="form1" method="post" >
    <table width="651" border="1" cellpadding="1" cellspacing="0">
      <tr>
        <td width="645" height="20" bgcolor="#6699FF"><div align="center"><font color="#FFFFFF" size="4"><strong> 
            TRAMITES DEL DOCUMENTO</strong></font></div></td>
      </tr>
    </table>
	
    <table width="657" border="0">
      <tr> 
          <td width="651"><div align="right"><strong><font color="#0000A0" size="2">
		  <? echo "Usuario : " . $usua?></font></strong></div></td>
      </tr>
    </table>	  
	  <?php
	  	$iddocumento ="";	  
		  while ($rs_padre=mssql_fetch_array($rs_p)){ 
		    if ($iddocumento <> $rs_padre[id_factura]){ 
		   ?>
	    
    <table width="670" border="0" cellpadding="1" cellspacing="0" bgcolor="#ECE9D8">
      <tr> 
        	
        <td width="681" height="263"  align="center" bgcolor="#9CCBED" > 
          <table width="639" height="145" border="1" >
            <tr> 
                 
              <td width="643" height="133" bgcolor="#9CCBED"> 
                <table width="97%" border="0">
                  <tr> 
                    <td bgcolor="#6699FF"><font color="#FFFFFF"><strong>INFORMACION 
                      DOCUMENTO DE REFERENCIA</strong></font>
				 </td>
               </tr>
             </table>
                <table width="97%" border="0" cellpadding="2" cellspacing="2" bgcolor="#C3D6E6">
                  <tr> 
                    <td width="104"><div align="left"><strong> Tema Factura</strong></div></td>
                    <td width="115"> <? echo $rs_padre[desc_tema]; ?> </td>
                    <td width="93"><b>Fecha Factura<font face="Arial, Helvetica, sans-serif"></font></b></td>
                    <td width="78"> 
                      <?php $fec_doc=strtotime($rs_padre[fecha_factura]);
		             $fech_doc=date("d/m/Y",$fec_doc);
     				echo $fech_doc;?>
                    </td>
                    <td width="188">&nbsp;</td>
                  </tr>
                </table>
          
                <table width="97%" border="0"  cellpadding="1" cellspacing="0" bgcolor="#C3D6E6">
                  <tr valign="middle"> 
                    <td width="110"><b> Numero Factura<font size="4" face="Arial"></font></b></td>
                    <td width="496"> <? echo $rs_padre[num_factura];?> </td>
                  </tr>
                </table>
                <table width="97%" border="1" cellpadding="1" cellspacing="0" bgcolor="#C3D6E6">
                  <tr> 
                    <td width="17%"><b>Descripci&oacute;n</b></td>
                    <td width="83%"> 
                      <? echo $rs_padre[descripcion];?> 
                    </td>
                  </tr>
              </table>
                <p>&nbsp;</p></td>
            </tr>
          </table>
          <table width="627" height="20" border="0" cellpadding="1" cellspacing="1">
            <tr> 
              <td width="623" > 
                <div align="center"><font color="#800000"><strong>TRAMITES 
                  ASOCIADOS</strong></font></div></td>
            </tr>
          </table>
		  
          <table width="639" border="1" cellpadding="1" cellspacing="0" bgcolor="#D1D7DC">
            <tr> 
              <td width="43"><font color="#02392D"><strong>N&oacute;mina</strong></font></td>
              <td width="81"><font color="#02392D"><strong>Procedencia</strong></font></td>
              <td width="74"><font color="#02392D"><strong>Funcionario</strong></font></td>
              <td width="77"><font color="#02392D"><strong>Destino</strong></font></td>
              <td width="87"><font color="#02392D"><strong>Funcionario</strong></font></td>
              <td width="93"><font color="#02392D"><strong>Tipo Distribucion</strong></font></td>
              <td width="67"><font color="#02392D"><strong>Fecha Despacho</strong></font></td>
              <td width="98"><font color="#02392D"><strong>Observaciones</strong></font></td>
              <td width="98"><font color="#02392D"><strong>Ingresado por</strong></font></td>
			  <td width="30"><font color="#02392D"><strong>Id_Seg</strong></font></td>
			  <td width="30"><font color="#02392D"><strong>Eliminar</strong></font></td>
            </tr>
          <? }?>
            <?php
			 
		    //  while ($rs_padre=mssql_fetch_array($tra)){   ?>
            <tr> 
              <td width="43" height="27"> <?php echo $rs_padre["id_nomina_despacho"];?></td>
              <td width="81"> 
                <?php  
				if ($rs_padre["procedencia"]=="") {
			    $rs_padre["procedencia"]="&nbsp";} 
				echo $rs_padre["procedencia"]; ?>
              </td>
              <td width="74"> 
                <?php 
			  if ($rs_padre["nombre_procedencia"]=="") {
			    $rs_padre["nombre_procedencia"]="&nbsp";} 
			  echo $rs_padre["nombre_procedencia"]; 
			   ?>
              </td>
              <td width="77"><?php echo $rs_padre["destino"]; ?> </td>
              <td width="87"> 
                <?php
			  if ($rs_padre["nombre_destino"]=="") {
			    $rs_padre["nombre_destino"]="&nbsp";}
				 echo $rs_padre["nombre_destino"]; 
				 ?>
              </td>
              <td width="93"><?php echo $rs_padre["desc_tipo_distribucion"]; ?> 
              </td>
              <td width="67"> 
                <?php
 				 if ($rs_padre["fecha_despacho"]<> NULL)
				 {$fec_tra=strtotime($rs_padre["fecha_despacho"]);
		         $fec_tra=date("d/m/Y",$fec_tra);
				 }
				 else 
				       $fec_tra ="&nbsp";
				 echo $fec_tra;?>
              </td>
              <td width="98"> 
                <?php
			  if ($rs_padre["observaciones"]=="") {
			    $rs_padre["observaciones"]="&nbsp";}
			   echo $rs_padre["observaciones"]; ?>
              </td>
			   <td width="43"> 
                <?php
			  if ($rs_padre["nombre_usuario"]=="") {
			    $rs_padre["nombre_usuario"]="&nbsp";}
			   echo $rs_padre["nombre_usuario"]; ?>
              </td>
			  <td width="43" height="27"> <?php echo $rs_padre["id_detalle"];?>
			  </td>
			  <td>
			  <?php
		 
		 
		 echo '<a href="guardar_tramite_factura_el.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . 
	     '&idfuncionario=' . $idfuncionario . '&desc=' . $desc . '&sw_cons=' . $cons .
		 '&Txt_fecha_ini=' . $Txt_fecha_ini . '&Txt_fecha_fin=' . $Txt_fecha_fin .
		 '&iddocum=' . $iddoc .
		 '&idseguim=' . $rs_padre["id_detalle"] .
		 '&Cbo_temas_fact=' .  $Cbo_temas_fact  .
		 '&num_factura=' . $num_factura .
		 '&TxtMateria=' . ltrim($TxtMateria) .
		 '&Cbo_Destinatario=' . $Cbo_Destinatario .  
		 '&Cbo_Procedencia=' . $Cbo_Procedencia .
		 '&tipo_procedencia=' .  $tipo_procedencia . 
		 '&tipo_destino=' .  $tipo_destino . 
		 '&rut_procedencia=' . $rut_procedencia . 
		 '&rut_destino=' . $rut_destino . 
		 '& obs  =' .  $obs .
		 '&fecha_sis1 =' . $fecha_sis1 . 
		 '&sw_elim=' . $sw_elim .
		 '$fecha_sis2 =' .  $fecha_sis2 . '">Eliminar</a>';
		  ?> 
		 </td>
        </tr>             
           <? $iddocumento= $rs_padre[id_factura]; 
		   } ?>	
	  </table>
     </tr>
  </table>
  </form>
  </center>
</body>
</html>
