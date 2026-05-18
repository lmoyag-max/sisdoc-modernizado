  <? // permite cargar la busqueda y desplegar documentos que  han sido i ngresados al sistema y permite seleccionar desde aca para poder 
  // enlazar el documento encontrado con el documento que se ingresa en estos momentos (se considera oara el ingreso desde oficina de partes)
  
   include("conexion_bd.php");
// fecha 
$dia = substr($fechadoc,0,2);
$mes = substr($fechadoc,3,2);
$año = substr($fechadoc,6,4);
$txtfecha1 = date("Y/m/d H:i", mktime(00, 00,00, $mes, $dia, $año));
$txtfecha2 = date("Y/m/d H:i", mktime(23, 59,59, $mes, $dia, $año));

   $consulta="Select a.materia,a.id_documento,a.num_interno,a.num_externo,a.fecha_documento,c.desc_tipo_documento  from documento  a, tramite b ,tipo_documento c,dependencia_externa d  where a.id_documento=b.id_documento and b.id_seguimiento in  (select min(id_seguimiento) from tramite  where id_documento=a.id_documento ) and a.id_tipo_documento=c.id_tipo_documento  and b.id_procedencia =d.id_dependencia_externa and d.cod_dependencia_externa=" . "'" .$proc ."'"  ;

     // agregando tipo documento 
	 if ($tipodoc=='')
	   $tipod='';
     else
      { $tipod ="  and a.id_tipo_documento =" . $tipodoc;}
      $consulta =$consulta. $tipod;     
  //agregando numero externo 
  if ($numexterno =='')
   $numext='';
   else
  {  $numext =" and a.num_externo =" . $numexterno;}
  
  $consulta =$consulta. $numext;
  //agregando fecha 
  if ($fechadoc== '')
   $fecha ='';
  else
    //{$fecha = " and a.fecha_documento =" . "'" . $txtfecha . "'" ; }
	 {$fecha = " and a.fecha_documento between ". "'" .$txtfecha1 . "'" . " and " .  "'" .$txtfecha2 . "'";}
  $consulta =$consulta. $fecha;
// agregando materia 
  //  buscando por materia //

$len = strlen($txtmateria);
$mat = substr(trim($txtmateria),-1);
if ($mat==","){
$materia=substr($txtmateria,0,$len - 1);}
else
{$materia=$txtmateria;}

$largo=0;
$largo= substr_count($materia ,"," );
$largo=$largo+1; 
if($materia==""){
$largo=0;}
$materia=$largo . "," . $materia;
$vector = split (",",$materia);

$largo= $vector[0];$x=1;
$sw_ok=0;

	$mat1="";
	$mat ="";
	if ($largo!=0){
	for($x=1;$x <=$largo;$x++)
	{  	 		  	 
      $mat1 =  " a.materia  like '%" . trim($vector[$x]) . "%'" ;
	  $mat = $mat . " and (" . $mat1 . " )" ;
    }		   
}
$consulta =$consulta . $mat;
//echo "consulta" . $consulta;
$rs_documento =mssql_query($consulta,$cn);
$Totreg = mssql_num_rows($rs_documento);
if($Totreg==0 )
{
echo '<script>';
echo 'alert("No hay documentos")';
echo '</script>';
}
else
{
$NumPag= intval($Totreg/10);
if(fmod($Totreg,10)==0) 
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
<title>Datos de despliegue </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
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
</head>

<body>
 
<table width="500" border="0">
  <tr> 
    <td> <p align="left"><b><font size="4" color="#0000FF">RESULTADO DE BUSQUEDA</font></b></font></p></td>
  </tr>
</table>
    <table width="501" border="0">
      <tr> 
	    <td width="495"><div align="left"><strong><?php echo "Total Registros : " . $Totreg ?></strong></div></td>
	  </tr>
    </table>
    <table width="500" border="0">
      <tr>
        <td>
          <?php
		     echo "<div align='left'><b>";
     		 for ($i = 1; $i <= $NumPag; $i++)
			 {			
		       echo "<img src='botones/boton" . $i . ".gif' width='44' height='16'". "onClick=\"MM_showHideLayers('layer" . $i . "','','show',$i, $NumPag)\">"; 
          	 } 
			 echo "</b></div>";
		    ?>
        </td>
      </tr>
    </table>
    <? $Corre = 0;
	   $NumLayer = 0;
	   while($reg_documento = mssql_fetch_array($rs_documento)) 
	   { 
		  
		  if(fmod($Corre,10)==0) 
		  { 
		  $NumLayer = $NumLayer + 1;
		  if($NumLayer==1)
		   {
  		    echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:120px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: visible">';
		   }
		   else
		   {
		    echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:120px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: hidden">';
		   }  
		   echo "<table width='500' border='1' cellpadding='1' cellspacing='0' bgcolor='#E6EEFF'>"; 
		   echo '<tr bgcolor="#6699FF">';
    	   echo '<td width="5%" height="33"><strong><font color="#FFFFFF" size="2">Num</font></strong></td>';
		   echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Interno</font></strong></td>';
    	   echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Externo</font></strong></td>';
    	   echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Tipo Documento</font></strong></td>';
    	   echo '<td width="50%" height="33"><strong><font color="#FFFFFF" size="2">Materia</font></strong></td>';
   		   echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha Documento</font></strong></td>';
   		   echo '<td width="6%" height="33"><strong><font color="#FFFFFF" size="2">Acepta</font></strong></td>';
           echo '</tr>';
		 } //if
		  $Corre =  $Corre + 1;
		  ?>
    
		<tr>
        <td align="left" valign="middle" width="5%"><font size="2"><?php echo $Corre;?></font></td>
	     <td align="left" valign="middle" width="8%"><font size="2">
          <?php echo $reg_documento["num_interno"];?></font>
        </td>
		 <td align="left" valign="middle" width="8%"><font size="2">
          <?php echo $reg_documento["num_externo"];?></font>
        </td>
	    
      <td align="left" valign="middle" width="8%"><font size="2"><?php  echo $reg_documento["desc_tipo_documento"]; $desc= '\''.$reg_documento["desc_tipo_documento"] .'\'';?> </font> </td>
		
      <td align="left" valign="middle" width="50%"><font size="2"> 
        <?php if ($reg_documento["materia"]=="")
		           echo "&nbsp";
				   else echo $reg_documento["materia"];
				        $mat ='\'' . $reg_documento["materia"] . '\'';?>
        </font> </td>
        <td align="left" valign="middle" width="8%"><font size="2">
          <?php $fec_doc=strtotime($reg_documento["fecha_documento"]);
		        $fech_doc=date("d/m/Y",$fec_doc);
				echo $fech_doc;
				$fecha= '\''.$fech_doc .'\''?></font>
        </td>
	     <td align="left" valign="middle" width="6%"> 
             <a href="#" onclick="parent.volver(<?php echo $reg_documento["id_documento"];?>);parent.despliegue_docto(<? echo $reg_documento["num_externo"];?>,<? echo $desc;?>,<? echo $fecha;?>,<? echo $mat;?>);">Acepta</a></td>
     
	   </tr>
     <?php if(fmod($Corre,10)==0) 
	 
	 { 
	 echo "</table>";
	 echo "</div>";
	 } ?>
    <?php } // while 
	} // else ?>

			  
</body>
</html>

