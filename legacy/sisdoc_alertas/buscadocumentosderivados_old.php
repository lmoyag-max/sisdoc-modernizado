<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");

$usuario=$cusuario;
$xx = $idusuario;
$fun=$idfuncionario;

$cbotiporig=$cbotiporig;
$flujo1=flujook;
$dia = substr($Txt_fecha_ini,0,2);
$mes = substr($Txt_fecha_ini,3,2);
$añoi = substr($Txt_fecha_ini,6,4);
$Fechaini = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $añoi));
// destino trae el valor de Txtdestino ingresado anteriormente y cbo_esc_dest el codigo de dependencia
$dia = substr($Txt_fecha_fin,0,2);
$mes = substr($Txt_fecha_fin,3,2);
$año = substr($Txt_fecha_fin,6,4);
$Fechafin = date("Y/m/d H:i", mktime(23, 59,59, $mes, $dia, $año));

if ($txtnom == 0 && $txtnom=='')
{
if ($cbotiporig ==0)
{  $Cbo_Tipo_Docto= 0;}
// busca los documetnos que han sido derivados a oficina de partes o hacia fuera y  oficina de partes no puede derivar desde 
// la consulta 
$consulta="select  distinct a.*,b.desc_tipo_documento,e.desc_dependencia,c.id_procedencia,c.id_seguimiento
from documento a, tipo_documento b,tramite c,dependencia e
where
      a.id_documento=c.id_documento 

and   c.tipo_procedencia ='I' 	  
and   c.id_procedencia =e.id_dependencia
and   a.id_tipo_documento= b.id_tipo_documento
and  (((c.id_destino=6 and c.tipo_destinatario='I')
 and c.id_estado_tramite =4) or  c.tipo_destinatario='E')
and   (a.fecha_documento between '$Fechaini' and '$Fechafin')";

//era parte del select 
//and   c.id_seguimiento in (select min(id_seguimiento) from tramite where id_documento=a.id_documento)

//if  ( $cbo_esc_dest ==0){
if  ( $destino =="")
{
 $cbo_proc="";
}
else
{
  $cbo_proc = " and c.id_procedencia =" . $cbo_esc_dest;
  $consulta=$consulta  . $cbo_proc ;
}
if ( $Cbo_Tipo_Docto==0  )
{
 $cbo_tipo= "";
}
else
{
  $cbo_tipo = " and a.id_tipo_documento =" . $Cbo_Tipo_Docto;
  $consulta=$consulta  . $cbo_tipo ;
}
  
if ( $TxtInterno== "" )
{
 $numinterno= "";
}
else
{
  $numinterno = " and a.num_interno=" . $TxtInterno ;
  $consulta=$consulta  . $numinterno ;
  }
  
if ( $TxtOficial== "" ){
 $numoficial= "";
 }
else{
  $numoficial = " and a.num_oficial=" . $TxtOficial ;
  $consulta=$consulta . $numoficial;}
 
 if ( $TxtExterno== "" ){
 $numexterno= "";
 }
else{
  $numexterno = " and a.num_externo=" . $TxtExterno ;
  $consulta=$consulta . $numexterno;} 

$orden = " order by a.id_tipo_documento,e.desc_dependencia" ;
$consulta =$consulta . $orden;

}
if ($txtnom <> 0 && $txtnom<>'')
{
$consulta="select distinct a.*,b.desc_tipo_documento,e.desc_dependencia,c.id_procedencia,c.id_seguimiento
from documento a, tipo_documento b,tramite c,dependencia e
where
      a.id_documento=c.id_documento 
and   a.num_oficial <> 0	  
and   c.tipo_procedencia ='I' 	  
and   c.id_procedencia =e.id_dependencia
and   a.id_tipo_documento= b.id_tipo_documento 
and  (((c.id_destino=6 and c.tipo_destinatario='I')
 and c.id_estado_tramite =4) or  c.tipo_destinatario='E')
and   (a.fecha_documento between '$Fechaini' and '$Fechafin')";
$orden = "  order by a.id_tipo_documento,e.desc_dependencia" ;
$nom =" and c.id_nomina_despacho=" . $txtnom ;
$consulta =$consulta . $nom . $orden ;
}
// echo "consulta" . $consulta ; 
$rs_documento=mssql_query($consulta);
$Totreg = mssql_num_rows($rs_documento);
if($Totreg==0)
 {
    	$txtmensaje="0"; 
	echo '<html><body onload="document.form1.submit();">';
  echo '<form name="form1" method="post" action="busca_documento_a_ofpartes.php">';
	echo '<input type="hidden" name="idusuario" 	value="' . $xx . '">';
	echo '<input type="hidden" name="cusuario" 	value="' . $usuario . '">';
	echo '<input type="hidden" name="idfuncionario" 	value="' . $fun . '">';
	echo '<input type="hidden" name="mensaje" 	value="' . $txtmensaje . '">';
	echo '<input type="hidden" name="flujook" 		value="' . 1 . '">';
	echo '<input type="hidden" name="Cbo_Tipo_Docto" 	value="' . $Cbo_Tipo_Docto . '">';
	echo '<input type="hidden" name="cbotiporig" 	value="' . $cbotiporig. '">';
	echo '<input type="hidden" name="Cbo_Destinatario" 	value="' . $cbo_esc_dest . '">';
	echo '<input type="hidden" name="cbo_esc_dest" 	value="' . $cbo_esc_dest . '">';
	echo '<input type="hidden" name="TxtInterno" 	value="' . $TxtInterno . '">';
	echo '<input type="hidden" name="TxtOficial" 	value="' . 0 . '" >';
	echo '<input type="hidden" name="TxtExterno" 	value="' . $TxtExterno . '">';
	echo '<input type="hidden" name="TxtOficial" 	value="' . $TxtOficial . '">';
	echo '<input type="hidden" name="Txt_fecha_ini" 	value="' . $Fechaini . '">';
	echo '<input type="hidden" name="Txt_fecha_fin" 	value="' . $Fechafin . '">';
	echo '<input type="hidden" name="destino" 		value="' . $destino . '">';
	echo '<input type="hidden" name="txtnom" 	value="' . $txtnom . '">';
	
	echo "</form></body></html>";
}	

else
{ 
$txtmensaje="1";
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

<html>
<head>
<META Http-Equiv="Cache-Control" Content="no-cache">
<META Http-Equiv="Pragma" Content="no-cache">
<META Http-Equiv="Expires" Content="0"> 
<title>Busca documentos a derivar desde oficina de partes </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript">

<!--
<!--
var grabado= "<? echo $grabaok ;?>";

function carga() {
  if (grabado=="1"){
  alert(" Documento derivado");}
     
}


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
<STYLE type=text/css>

A { COLOR: blue; FONT-FAMILY: verdana,arial,helvetica,sans-serif; FONT-SIZE: 11px; TEXT-DECORATION: none
}

A:hover { COLOR: red; TEXT-DECORATION: none
}
</style>
</head>
<body   bgcolor="#FFFFFF" text="#000000" onload="carga()">
<center >
    <form name="formulario1"         method="post" 	 >
    <table width="650" border="1" cellpadding="2" cellspacing="0" bgcolor="#3399FF">
      <tr>  <td> <p align="center"><b><font size="4" color="#FFFFFF">DOCUMENTOS PARA DERIVAR DESDE OF. DE PARTES </font></b></p></td>   </tr>
    </table>
    <table width="650" border="0">
    </table>
    <table width="650" border="0">
      <tr>
        <td><div align="left"><strong><?php echo "Total de Páginas : " . $NumPag ?></strong></div></td>
        <td><div align="right"><strong><?php echo "Total Registros : " . $Totreg ?></strong></div></td>
      </tr>
    </table>
    <table width="650" border="0">
      <tr> 
        <td> 
          <?php
	echo "<div align='left'><b>";
     	for ($i = 1; $i <= $NumPag; $i++)
	 {
	 echo "<img src='botones/boton" . $i . ".gif' width='44' height='16'".  "onClick=\"MM_showHideLayers('layer" . $i . "','','show',$i, $NumPag)\">"; 
            	 } 
	echo "</b></div>";
          ?>
        </td>
        
      </tr>
    </table>
     	<?php 
	      $Corre = 0;
	      $NumLayer = 0;
	       while($reg_documento = mssql_fetch_array($rs_documento)) { 
	            if(fmod($Corre,10)==0) 
		  { 
		  $NumLayer = $NumLayer + 1;
		  if($NumLayer==1){
  		 echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:85px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: visible">';
		   }
		   else
		   {
		 echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:85px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: hidden">';
		   }
	echo "<table width='650' border='1' cellpadding='1' cellspacing='0' bgcolor='#E6EEFF'>"; 
	echo '<tr bgcolor="#6699FF">';
	echo '<td width="5%" height="33"><strong><font color="#FFFFFF" size="2">Num</font></strong></td>';
    	echo '<td width="6%" height="33"><strong><font color="#FFFFFF" size="2">Ver trámites </font></strong></td>';
    	echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Interno</font></strong></td>';
    	echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Oficial</font></strong></td>';
    	echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Externo</font></strong></td>';
    	echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Fecha Documento</font></strong></td>';
    	echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Tipo Documento</font></strong></td>';
    	echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Procedencia</font></strong></td>';
    	echo '<td width="20%" height="33"><strong><font color="#FFFFFF" size="2">Materia</font></strong></td>';
    	echo '</tr>';
	 }
	 $Corre =  $Corre + 1;
	 ?>
        <tr>
        	<td align="left" valign="middle" width="5%"><font size="2"><?php echo $Corre;?></font></td>
         	<td align="left" valign="middle" width="6%"> 
			<?php //echo '<a href="cambia_estado3.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . '&destino=' . $destino.
		 //'&iddocum=' . $reg_documento["id_documento"] . '&idfuncionario=' . $idfuncionario . '&flujook=' . 0 . '&txtnom=' . $txtnom .'&TxtInterno=' . $TxtInterno .
		 //'&TxtOficial=' . $TxtOficial  .	 '&TxtExterno=' . $TxtExterno . '&Fechaini=' . $Txt_fecha_ini .'&Fechafin=' . $Txt_fecha_fin .
		  //'&cbo_esc_dest=' .$cbo_esc_dest . ' &cbotiporig=' . $cbotiporig. ' &Cbo_Tipo_Docto=' .  $reg_documento["id_tipo_documento"] . '&mensaje=' . 2 .  '">Deriva</a>'; 
         echo '<a href="tramites_deriva_ofpartes.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . 
	 '&iddocum=' . $reg_documento["id_documento"] . '&idseguim=' . $reg_documento["id_seguimiento"] .
	 '&idfuncionario=' . $idfuncionario . '">Ver trámites</a>'; 	 
		  ?> 
       	 </td>
        	<td align="left" valign="middle" width="8%"><font size="2">
         	 <?php echo $reg_documento["num_interno"];?></font>
        	</td>    
        	<td align="left" valign="middle" width="8%"><font size="2">
	   <?php if ($reg_documento["num_oficial"]==0)
		{ echo "&nbsp";}
	            else {echo $reg_documento["num_oficial"];}?></font> </td>
	  <td align="left" valign="middle" width="8%"><font size="2">
	  <?php if ($reg_documento["num_externo"]==0)
		 {echo "&nbsp";}
	            else {echo $reg_documento["num_externo"];}?></font>
	   </td>		
      	  <td align="left" valign="middle" width="8%"><font size="2">
                  <?php $fec_doc=strtotime($reg_documento["fecha_documento"]);
                            $fech_doc=date("d/m/Y",$fec_doc);
                    echo $fech_doc;?></font>
        	</td>
      	<td align="left" valign="middle" width="20%"><font size="2">
          	<?php echo $reg_documento["desc_tipo_documento"];?></font>
        	</td>
      	<td align="left" valign="middle" width="20%"><font size="2">
          	<?php echo $reg_documento["desc_dependencia"];?></font>
        	</td>
	<td align="left" valign="middle" width="20%"><font size="2">
          	<?php echo trim($reg_documento["materia"]);?></font>
      	</td>
		
       </tr>
     <?php if(fmod($Corre,10)==0) { 
	 echo "</table>";
	 echo "</div>";  } ?>
    <?php } ?></table>
    </div> 
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <table width="650"  border="0">
      <tr> 
        <td height="23" > 
          <div align="left"></div>
          <div align="left"> 
            <input type="hidden" name="Totreg2" value="<?php echo $Totreg; ?>">
            <input type="hidden" name="NumLayer2" value="<?php echo $NumLayer; ?>">
            <input type="hidden" name="idusuario" value="<? echo $xx;?>">
            <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
            <input type="hidden" name="idfuncionario" value="<? echo $idfuncionario;?>">
			<input type="hidden" name="txtnom" value="<? echo $txtnom;?>">
            
			
          </div></td>
      </tr>
    </table>
    <br>
    <p>&nbsp; </p>
  </form>
  <?php
  }
  ?>	    
  <p>&nbsp; </p>
</center>  

</body>
</html>
