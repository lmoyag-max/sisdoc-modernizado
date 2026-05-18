<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
$usua=$cusuario;
$xx=$idusuario;
$iddoc=$iddocum;


//$ps_busca_padre = "select * from historia5($iddocum)";
$ps_busca_padre = "select * from llama_historia($iddocum)";

$rs_p = mssql_query($ps_busca_padre,$cn); 

$tot_padre = mssql_num_rows($rs_p);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?php $iddocum; ?>< Edicion></title>
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
        <td width="645" height="20" bgcolor="#6699FF"><div align="center"><font color="#FFFFFF" size="4"><strong> 
            TRAMITES DEL DOCUMENTO</strong></font></div></td>
      </tr>
    </table>
    <table width="657" border="0">
      <tr> 
          <td width="651"><div align="right"><strong><font color="#0000A0" size="2">
		  <?echo "Usuario : " . $usua?></font></strong></div></td>
      </tr>
    </table>	  
	  <?php
	  	$iddocumento ="";	  
		  while ($rs_padre=mssql_fetch_array($rs_p)){ 
		    if ($iddocumento <> $rs_padre[id_documento]){ 
		   ?>
	    
    <table width="867" border="0" cellpadding="1" cellspacing="0" bgcolor="#ECE9D8">
      <tr> 
        	
        <td width="865" height="263"  align="center" bgcolor="#9CCBED" > 
          <table width="99%" height="148" border="1" >
            <tr> 
                 
              <td width="633" height="142" bgcolor="#9CCBED"> 
                <table width="99%" border="0" align="center">
                  <tr> 
                    <td bgcolor="#6699FF"><font color="#FFFFFF"><strong>INFORMACION 
                      DOCUMENTO DE REFERENCIA</strong></font>
				 </td>
               </tr>
             </table>
                <table width="99%" border="0" align="center" cellpadding="2" cellspacing="2" bgcolor="#C3D6E6">
                  <tr> 
                    <td width="98"><strong>Tipo de Docto</strong></td>
                    <td width="106"> <? echo $rs_padre[desc_tipo_documento]; ?> 
                    </td>
                    <td width="86"><b>Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></td>
                    <td width="72"> 
                      <?php $fec_doc=strtotime($rs_padre[fecha_documento]);
		             $fech_doc=date("d/m/Y",$fec_doc);
     				echo $fech_doc;?>
                    </td>
                    <td width="35"><b>Medio</b></td>
                    <td width="50"> 
                      <? If ($rs_padre["medio"]=="P"){
					    echo "Papel";}
						else{
						if ($rs_padre["medio"]=="C"){
						   echo "Copia";}
						else {
						   echo "Video";}
						}   
				    ?>
                    </td>
                    <td width="46"><b>Original</b></td>
                    <td width="171"><? echo $rs_padre[original];?></td>
                  </tr>
		      </table>
          
                <table width="99%" border="0" align="center"  cellpadding="1" cellspacing="0" bgcolor="#C3D6E6">
                  <tr valign="middle"> 
                    <td width="110"><b><i>N&uacute;meros : Interno<font size="4" face="Arial"> 
                      </font></i></b></td>
                    <td width="72"> <? echo $rs_padre[num_interno];?> </td>
                    <td width="61"><b><i>Oficial</i></b><font size="4" face="Arial">&nbsp; 
                      </font></td>
                    <td width="92"> <? echo $rs_padre[num_oficial];?> </td>
                    <td width="85"> <b>Fecha Oficial </b></td>
                    <td width="76"> 
                      <?php $fec_doc=strtotime($rs_padre[fecha_num_oficial]);
		             $fech_doc=date("d/m/Y",$fec_doc);
		             if ($rs_padre[fecha_num_oficial] <>NULL)
     			{ echo $fech_doc;}
     			?>
                    </td>
                    <td width="58"><b><i>Externo<font size="4" face="Arial"> </font></i></b></td>
                    <td width="51"> <? echo $rs_padre[num_externo]; ?> </td>
                    <td width="107"><b>Fecha timbre recep.</b></td>
                    <td width="175"><? echo substr($rs_padre[fecha_timbre_recepcion],6,2) . '/' .substr($rs_padre[fecha_timbre_recepcion],4,2).'/' .substr($rs_padre[fecha_timbre_recepcion],0,4);?> </td>

                  </tr>
                </table>
                <table width="99%" border="1" align="center" cellpadding="1" cellspacing="0" bgcolor="#C3D6E6">
                  <tr> 
                    <td width="17%"><b>Materia</b></td>
                    <td width="83%"> 
                      <? echo $rs_padre[materia];?> 
                    </td>
                  </tr>
              </table>
                <p>&nbsp;</p></td>
            </tr>
          </table>
          <table width="97%" height="19" border="0" align="center" cellpadding="1" cellspacing="1">
            <tr> 
              <td width="634" > 
                <div align="center"><font color="#800000"><strong>TRAMITES 
                  ASOCIADOS</strong></font></div></td>
            </tr>
          </table>
		  
          <table width="98%" border="1" align="center" cellpadding="1" cellspacing="0" bgcolor="#D1D7DC">
            <tr> 
              <td width="46"><font color="#02392D"><strong>N&oacute;mina</strong></font></td>
              <td width="77"><font color="#02392D"><strong>Procedencia</strong></font></td>
              <td width="71"><font color="#02392D"><strong>Funcionario</strong></font></td>
              <td width="71"><font color="#02392D"><strong>Destino</strong></font></td>
              <td width="71"><font color="#02392D"><strong>Funcionario</strong></font></td>
              <td width="73"><font color="#02392D"><strong>Tipo Distribucion</strong></font></td>
              <td width="51"><font color="#02392D"><strong>Fecha Registro</strong></font></td>
              <td width="61"><font color="#02392D"><strong>Fecha Despacho</strong></font></td>
              <td width="64"><font color="#02392D"><strong>Fecha Recepcion</strong></font></td>
			  <td width="95"><font color="#02392D"><strong>Observaciones</strong></font></td>
			  <td width="95"><font color="#02392D"><strong>Ingresado por </strong></font></td>
			  
            </tr>
          <? }?>
            <?php
			 
		    //  while ($rs_padre=mssql_fetch_array($tra)){   ?>
            <tr> 
              <td width="46" height="27"> <?php echo $rs_padre["id_nomina_despacho"];?></td>
              <td width="77"> 
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
              <td width="71"><?php echo $rs_padre["destino"]; ?> </td>
              <td width="71"> 
                <?php
			  if ($rs_padre["nombre_destino"]=="") {
			    $rs_padre["nombre_destino"]="&nbsp";}
				 echo $rs_padre["nombre_destino"]; 
				 ?>
              </td>
              <td width="73"><?php echo $rs_padre["desc_tipo_distribucion"]; ?> 
              </td>
               <td width="51">
			       <?php 
			        $fec_reg=strtotime($rs_padre["fecha_sistema"]);
		             $fec_reg=date("d/m/Y",$fec_reg);
				 echo $fec_reg; ?></td>
              <td width="61"> 
                <?php 
                		  if ($rs_padre["fecha_despacho"]==NULL)
				{		
						 $fec_tra="&nbsp";
				   echo $fec_tra;}
				else 
				 {
				 $fec_tra=strtotime($rs_padre["fecha_despacho"]);
		         $fec_tra=date("d/m/Y",$fec_tra);
				echo $fec_tra;}?>
              </td>
			   <td width="64"> <?php 
                  if ($rs_padre["fecha_recepcion"]==NULL)
				{		
						 $fec_rec="&nbsp";
				   echo $fec_rec;}
				else 
				 {
				 $fec_rec=strtotime($rs_padre["fecha_recepcion"]);
		         $fec_rec=date("d/m/Y",$fec_rec);
				 echo $fec_rec; }?></td>
             
              <td width="95"> 
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
            </tr>
             
           <? $iddocumento= $rs_padre[id_documento]; 
		   } ?>
			
		  </table>
          </tr>

  </table>
  </form>
  </center>
</body>
</html>
