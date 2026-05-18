<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
/*$cusuario ='ximena';
$idusuario=3;
$iddocum =662;*/

$usua=$cusuario;
$xx=$idusuario;
$iddoc=$iddocum;
$opc=$opcion;

//echo "idusu" . $idusuario . "*** usu " . $cusuario . "** acc " . $accion . "** fun " . $idfuncionario . "** docu " . $iddocum . "** seg " . $idseguim ;

$dia = substr($Txt_fecha_ini,0,2);
$mes = substr($Txt_fecha_ini,3,2);
$año = substr($Txt_fecha_ini,6,4);
$Fechaini = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));

$dia = substr($Txt_fecha_fin,0,2);
$mes = substr($Txt_fecha_fin,3,2);
$año = substr($Txt_fecha_fin,6,4);
$Fechafin = date("Y/m/d H:i", mktime(23, 59,59, $mes, $dia, $año));
$desc  =$arreglo;

$consulta="select a.*,b.desc_expediente,c.desc_tipo_documento
           from documento a,expediente b  , tipo_documento c 
           where (b.fecha_expediente between '$Fechaini' and '$Fechafin')
		   and a.id_expediente =b.id_expediente 
		   and a.id_tipo_documento=c.id_tipo_documento";
if ($opc==1 && $txtexped <>"")
{
$consulta=$consulta . " and b.id_expediente =" . $txtexped;
}

//  buscando por descripcion de expediente//
$len = strlen($txtdesc);
$desc = substr(trim($txtdesc),-1);

if ($desc==",")
{
$descrip=substr($txtdesc,0,$len - 1);
}
else
 {$descrip=$txtdesc;}

$largo=0;
$largo= substr_count($descrip ,"," );
$largo=$largo+1; 
if($descrip==""){
$largo=0;}
$descrip=$largo . "," . $descrip;
$vector = split (",",$descrip);

$largo= $vector[0];$x=1;
$sw_ok=0;
$desc1="";
if ($largo!=0)
{
    for($x=1;$x <=$largo;$x++)
   {
    $desc1 = $desc1 . " and b.desc_expediente like '%" . trim($vector[$x]) . "%'" ;
   }
}
$consulta =$consulta  . $desc1;
$consulta = $consulta . " order by   b.id_expediente";

$rs_doc=$consulta;
$rs_documento=mssql_query($rs_doc);   
$Totreg = mssql_num_rows($rs_documento);

  if ($Totreg ==0 ){
echo "<script>\n";
 echo  "alert('No hay expedientes asociados');";
echo "</script>\n";
/*echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="busca_expediente2.php">';
	echo '<input type="hidden" name="idusuario" value="' . $xx . '">';
	echo '<input type="hidden" name="cusuario" value="' . $usua . '">';
	echo '<input type="hidden" name="iddocum" value="' . $iddoc . '">';
	echo '<input type="hidden" name="opcion" value="' . $opc . '">';
		echo '<input type="hidden" name="fun" value="' . $fun . '">';
	echo "</form></body></html>";*/

}		
else
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><informe de expedientes?>< Edicion></title>
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
for (a=1; (a<=totlay); a++)
  {
    nohay = "layer" + a;
    document.all[nomlay].style.visibility="hidden";
//	queda pendiente esta consulta --	if (navigator.appName == "Microsoft Internet Explorer") 
	
    }
}

//-->
</script>

<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body>
<!--bgcolor="#FFFFFF" text="#000000" link="#CCCCCC" topmargin="0"  onLoad="carga()"!-->

<center>
<form name="form1" method="post"  >
    <table width="667" border="1"  align="center" cellpadding="1" cellspacing="0">
     <tr>
      <td width="661" height="20" bgcolor="#6699FF"><div align="center"><font color="#FFFFFF" size="4"><strong> 
       EXPEDIENTES</strong></font></div></td>
     </tr>
    </table>
    <table width="662" border="0">
      <tr> 
        <td width="656" height="21"><div align="right"><strong><font color="#0000A0" size="2">
	 <? echo "Usuario : " . $usua ?></font></strong></div></td>
      </tr>
    </table>	  
   		
    <?php
  		$idexpediente ="";	  
        while($reg_documento = mssql_fetch_array($rs_documento)) { 
	    if ($idexpediente <> $reg_documento[id_expediente]){ 
	     ?>
    <table width="652" height="111" border="1">
      <tr> 
        <td width="648" height="105" bgcolor="#9CCBED"> 
          <table width="642" border="0" align="center">
            <tr> 
              <td width = "642" height="21" bgcolor="#6699FF"><font color="#FFFFFF"><strong>INFORMACION 
                EXPEDIENTE</strong></font></td>
            </tr>
          </table>
          <table width="644"  align="center" border="1" cellpadding="2" cellspacing="2" bgcolor="#C3D6E6">
            <tr>
              <td height="24"><b>N&ordm; Expediente</b></td>
              <td>
                <? if ($opcion==1)
					    {
			   			 echo $reg_documento[id_expediente];
					    }
						   else
					   {
		   				  echo "<a href=\"ingreso_docto2.php?cusuario=".$cusuario."&idusuario=".$idusuario ."&idfuncionario=".$idfuncionario."&flujook=". 8 ."&num_int=".$num_int."&num_exp=".$reg_documento[id_expediente]."&descexped=". $reg_documento[desc_expediente]."&bloquea=1\">".$reg_documento[id_expediente]."</a>";
					   }
						?>
            </tr>
            <tr> 
              <td width="112" height="24"><b>Descripci&oacute;n</b></td>
              <td width="512"><? echo $reg_documento[desc_expediente];?> 
            </tr>
          </table>
          
        </td>
      </tr>
    </table>
    <table width="655" height="26" border="1">
      <tr> 
        <td width="673" height="20" > <div align="center"><font color="#800000"><strong>DOCUMENTOS 
            ASOCIADOS</strong></font></div></td>
      </tr>
    </table>
    <table  width = "655" align="center" border="1" cellpadding="1" cellspacing="0" bgcolor="#D1D7DC">
      <tr> 
        <td width="65" ><font color="#02392D"><strong>N&ordm; Interno</strong></font></td>
        <td width="58" ><font color="#02392D"><strong>N&ordm; Oficial</strong></font></td>
        <td width="64" ><font color="#02392D"><strong>N&ordm; Externo</strong></font></td>
        <td width="97" ><font color="#02392D"><strong>Tipo Documento</strong></font></td>
        <td width="230"><font color="#02392D"><strong>Materia</strong></font></td>
        <td width="115"><font color="#02392D"><strong>Fecha Documento</strong></font></td>
      </tr>
      <!--</table>-->
      <? }?>
      <!-- <table width="740" border="1" align="center" cellpadding="2" cellspacing="2" bgcolor="#D1D7DC">-->
      <!--?php
			 
		    //  while ($rs_padre=mssql_fetch_array($tra)){   ?-->
      <tr> 
        <td width="65" height="24"><font size="2"> <?php echo $reg_documento["num_interno"];?></font></td>
        <td width="58"><font size="2"> <?php echo $reg_documento["num_oficial"];?></font></td>
        <td width="64"><font size="2"> <?php echo $reg_documento["num_externo"];?></font></td>
        <td width="97"><font size="2"> <?php echo $reg_documento["desc_tipo_documento"];?></font></td>
        <td width="230"><font size="2"> 
          <?php if ($reg_documento["materia"]=="")
		           echo "&nbsp";
				   else echo $reg_documento["materia"];?>
          </font></td>
        <td width="115"><font size="2"> 
          <?php $fec_doc=strtotime($reg_documento["fecha_documento"]);
		        $fech_doc=date("d/m/Y",$fec_doc);
		         echo $fech_doc;?>
          </font></td>
      </tr>
      <? $idexpediente= $reg_documento[id_expediente]; } ?>
    </table>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    </form>
  </center>
</body>
</html>
<? } ?>