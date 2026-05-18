<?php
include("variables.php");
include("conexion_bd.php");

$Usuario=$cusuario;
$xx = $idusuario;
$fun =$idfuncionario;
echo "usua" . $cusuario . " *** idusu" . $idusuario . " *** fun" . $idfuncionario;
//echo "<br>";

// ---------- Busca los documentos que estan en estado 1 ---------------------
$rs_doc="exec busca_despachos '" . $xx . "'";

$rs_documento=mssql_query($rs_doc);   
$Totreg = mssql_num_rows($rs_documento);
 
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
<title>Despacho documentos</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript">
<!--
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

<script language="JavaScript">
<!--

function revisa_check() 	
  {
	var sicheck = 0;
  
for (var n=0; n < formulario1.elements.length; n++) {
     if (formulario1.elements[n].checked) {
	 
	     sicheck = 1; }
	         	 
}
	 if (sicheck == 0)  {
	           alert("Debe seleccionar un documento");
			   return false; }
			  else 
			   return true; 	
  }
	
	
function chequea_todos(formu)

  {
      for (var i=0;i<formu.elements.length;i++)
    {
	  	
      var elemento = formu.elements[i]; //(e.name != 'chektodos') && (
      if (elemento.type=='checkbox')
      {
        elemento.checked = formu.chektodos.checked;
        if (formu.chektodos.checked)
        {
          cambia_color(elemento);
        }
        else
        {
          cambia_color(elemento);
        }
      }
    }
  alert("Se despacharán todos los documentos");
  }       	
	
function cambia_color(esto) 
  {
  var est_check=1;
  var ie = document.all?1:0;
  var ns4 = document.layers?1:0;
  
     var estacheck=esto.checked;
     if (ie)
      {
        while (esto.tagName!="TR")
        {
           esto=esto.parentElement;
	    }
      }
     else
      {
        while (esto.tagName!="TR")
        {
       	  esto=esto.parentNode;
        }
      }
     if(estacheck)
	 
       esto.className = "columna1"
      else
       esto.className = "columna2";
       }   
	
	
//-->
</script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

<STYLE type=text/css>

A { COLOR: blue; FONT-FAMILY: verdana,arial,helvetica,sans-serif; FONT-SIZE: 11px; TEXT-DECORATION: none
}

A:hover { COLOR: red; TEXT-DECORATION: none
}
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000" topmargin="0">
<center >
    <form name="formulario1" 
        method="post" 
		action="muestra_check.php" 
		onsubmit="return revisa_check(this.form);">
    <table width="650" border="0">
      <tr> 
        <td> <p align="center"><font face="Verdana, Arial, Helvetica, sans-serif"><b><font size="3" color="#0000A0">DESPACHO 
            DE DOCUMENTOS</font></b></font></p></td>
      </tr>
      <tr> 
        <td height="21"><div align="right"><strong><font color="#0000A0" size="1"><? echo "Usuario : " . $Usuario?></font></strong></div></td>
      </tr>
    </table>
    <table width="650" border="0" cellpadding="1" cellspacing="0">
      <tr> 
        <td width="245" height="23" align="left"><?php echo "Total Registros : " . $Totreg ?></td>
        <td width="246" height="23" align="left"><?php echo "Total de Páginas : " . $NumPag ?></td>
        <td width="145" align="center"><div align="right"><strong>
		<?php echo '<a href="ingreso_docto1.php?cusuario=' . $cusuario .
			'&idusuario=' . $xx . '&idfuncionario=' . $fun .
			'&tipo_destino=' . 0 . '&tipo_procedencia=' . 0 .
			'&val_destino=' . 0 . '&val_procedencia=' . 0 .
			'&val_funcionario=' . 0 . '&val_funcionario1=' . 0 .
			'&flujook=' . 3 . '&num_int=' . 0 .			  
			'">Volver</a>'; ?></strong></div></td>
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
  		   echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:160px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility:visible">';
		   }
		   else
		   {
		   echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:160px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: hidden">';
		   }
		   
		   

echo '<table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#ECE9D8">';
echo '<tr bgcolor="#6699FF">'; 
echo '<td width="5%"><strong><font color="#FFFFFF" size="2">Num</font></strong></td>';
echo '<td width="5%"><strong><font color="#FFFFFF" size="2">Check</font></strong></td>';
echo '<td width="10%"><strong><font color="#FFFFFF" size="2">Nro Interno</font></strong></td>';
echo '<td width="10%"><strong><font color="#FFFFFF" size="2">Nro Oficial</font></strong></td>';
echo '<td width="10%"><strong><font color="#FFFFFF" size="2">Tipo Documento</font></strong></td>';
echo '<td width="30%"><strong><font color="#FFFFFF" size="2">Materia</font></strong></td>';
echo '<td width="10%"><strong><font color="#FFFFFF" size="2">Fecha Documento</font></strong></td>';
echo '<td width="30%"><strong><font color="#FFFFFF" size="2">Destinatario</font></strong></td>';
echo '</tr>';
		  }
		  $Corre =  $Corre + 1;
		  ?>
    <table width="650"  border="0" cellpadding="1" cellspacing="0">
      <tr> 
        <td width="495" height="23" > <div align="left"></div>
          <div align="right">Despachar Todos 
            <input type="checkbox" name="chektodos" onClick="chequea_todos(document.formulario1);revisa_check();" value="t">
          </div></td>
        <td width="145" ><div align="right"> 
            <input type="hidden" name="Totreg" value="<?php echo $Totreg; ?>">
            <input type="hidden" name="NumLayer" value="<?php echo $NumPag; ?>">
            <input type="hidden" name="idusuario" value="<? echo $xx;?>">
            <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
            <input type="hidden" name="idfuncionario" value="<? echo $idfuncionario;?>">
            <input name="cmd_aceptar" type="submit" class="botones" value="aceptar" >
          </div></td>
      </tr>
    </table>
    <table>
      <tr> 
        <td align="left" valign="middle" width="495"> <font size="1"> <strong> 
          <?php
		  echo "<div align='left'><b>";
     		        for ($i = 1; $i <= $NumPag; $i++)
			 {
			
		 echo "<img src='botones/boton" . $i . ".gif' width='44' height='16'". 
 "onClick=\"MM_showHideLayers('layer" . $i . "','','show',$i, $NumPag)\">"; 
            
			 } 
			 $NumLayer=$NumPag;
			 echo "</b></div>";
		    ?>
          </strong></font></td>
        <td align="left" valign="middle" width="145">&nbsp; </td>
      </tr>
    </table>
    <tr>
        <td align="left" valign="middle" width="5%"><font size="1"><font size="2"><?php echo $Corre;?></font></td>
		<td align="left" valign="middle" width="5%"><font size="2">
        
        <input type="checkbox" name="casilla_despacho[]" value="<?php echo $reg_documento["id_seguimiento"];?>" onClick="cambia_color(this)"></font>
        </td>
        <td align="left" valign="middle" width="10%"><font size="2">
          <?php echo $reg_documento["num_interno"];?></font>
        </td>
		 <td align="left" valign="middle" width="10%"><font size="2">
          <?php echo $reg_documento["num_oficial"];?></font>
        </td>
	    <td align="left" valign="middle" width="10%"><font size="2">
          <?php echo $reg_documento["desc_tipo_documento"];?></font>
        </td>
       <td align="left" valign="middle" width="30%"><font size="2">
          <?php if ($reg_documento["materia"]=="")
		           echo "&nbsp";
				   else echo $reg_documento["materia"];?></font>
        </td>
        <td align="left" valign="middle" width="10%"><font size="2">
          <?php $fec_doc=strtotime($reg_documento["fecha_documento"]);
		        $fech_doc=date("d/m/Y",$fec_doc);
				echo $fech_doc;?></font>
        </td>
        <td align="left" valign="middle" width="30%"><font size="2">
          <?php echo $reg_documento["destino"];?></font>
        </td>
      </tr>
    <?php if(fmod($Corre,10)==0) { 
	 echo "</table>";
	 echo "</div>";  } ?>
    <?php } ?></table>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    </form>	    
  <p>&nbsp; </p>
</center>  

</body>
</html>
