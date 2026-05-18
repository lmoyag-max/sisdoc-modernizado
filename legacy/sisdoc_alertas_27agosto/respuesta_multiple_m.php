<?php
include("variables.php");
include("conexion_bd.php");
include("funciones.php");

$flujo = 8;
$numint=0;
$Usuario=$cusuario;
$xx=$idusuario;

$rs_doc="exec busca_nomina_recib '" . $xx. "','" . $txtnomina. "'";
$rs_documento=mssql_query($rs_doc);   
$Totreg = mssql_num_rows($rs_documento);
if($Totreg==0)
{
echo '<script>';
echo 'alert("No Existen Documentos Recepcionados")';
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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<META Http-Equiv="Cache-Control" Content="no-cache">
<META Http-Equiv="Pragma" Content="no-cache">
<META Http-Equiv="Expires" Content="0">
<title>Responder facturas con un solo docto</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript">
<!--
<!--
var variables=new Array(<?php echo $Totreg; ?>);
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

function buscar(){

/*if (document.formulario1.txtnomina.value != ""){*/
	document.formulario1.action="respuesta_multiple.php";
	document.formulario1.submit();
	
 	/* }*/

}
 function validarentero(formu){ 
      //intento convertir a entero. 
	  var formu;
     //si era un entero no le afecta, si no lo era lo intenta convertir 
     formu.txtnomina.value = parseInt(formu.txtnomina.value);
	 if (formu.txtnomina.value=="") {
	     formu.txtnomina.value =0;
	}	 
	 //Compruebo si es un valor numérico 
      if (isNaN(formu.txtnomina.value)) { 
            //entonces (no es numero) devuelvo el valor cadena vacia 
			formu.txtnomina.value ="";
			alert ("Debe ingresar solamente numeros");
		    return formu.txtnomina.value 
      }else{ 
            //En caso contrario (Si era un número) devuelvo el valor 
            return formu.txtnomina.value
      } 
} 

function nombre_funcion() 
   {
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
	 if (formu.chektodos.checked){
	alert("Se responderan todos los documentos");
	}
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
 
	
function responder_docto()
{
/*  for(k=0;k<document.formulario1.elements.length-1;k++)	
  {
    var elemento=document.formulario1.elements[k];
	if (elemento.name=='casilla_despacho[]'  && elemento.checked)
    {
      alert (elemento.value);
	}
   }*/
	if (revisa_check())
     {document.formulario1.action = 'derivar_multiple_con_docto.php';
     document.formulario1.submit();
	 }
}
function valida_destino()
{
for(k=0;k<document.formulario1.elements.length-1;k++)	
  {
    var elemento=document.formulario1.elements[k];
	if (elemento.name=='casilla_despacho[]'  && elemento.checked)
    {
      alert (elemento.value);
	}
   }
}
function asociar_docto()
{
	if (revisa_check())
     {
       document.formulario1.action = 'asociar_docto_respuesta.php';
     document.formulario1.submit();
     }
}

//-->
</script>

<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>
<body bgcolor="#FFFFFF" text="#000000" topmargin="0" >
<center >
<!--form name="formulario1" method="post" action="derivar_multiple_con_docto.php" onsubmit="return revisa_check(this.form);"-->
<form name="formulario1" method="post" onsubmit="return revisa_check(this.form);">
<table width="664" border="1" cellpadding="2" cellspacing="0" bgcolor="#3399FF">
  <tr> 
    <td width="649"> <b><font color="#FFFFFF" size="4"> </font></b> 
     <p align="center"><b><font size="4" color="#FFFFFF">DERIVAR FACTURAS CON DOCUMENTO</font></b></p></td>
  </tr>
</table>
<table width="652" border="0">
    <tr>
      <td width="321"><strong><?php echo "Total de Páginas : " . $NumPag ?></strong></td>
      <td width="321"><div align="right"><strong><?php echo "Total Registros : " . $Totreg ?></strong></div></td>
    </tr>
</table>
    <table width="650" border="0">
      <tr>
        <td>
          <?php
		  echo "<div align='left'><b>";
     		        for ($i = 1; $i <= $NumPag; $i++)
			 {
			
		 echo "<img src='botones/boton" . $i . ".gif' width='44' height='16'". 
 "onClick=\"MM_showHideLayers('layer" . $i . "','','show',$i, $NumPag)\">"; 
            
			 } 
			 echo "</b></div>";
		    ?>
        </td>
      </tr>
    </table>
    <table width="650" border="0">
      <tr> 
        <td width="324">&nbsp;</td>
        <td width="326"><div align="right">Seleccionar por n&oacute;mina 
            <!--input type="text" name="txtnomina" size="8" maxlength="8" ;"-->
            <input name="txtnomina" type="text" class="entradas" onBlur="validarentero(formulario1);" size="8" maxlength="8">
            <font size="2" face="Arial"> 
            <input name="cmd_busca" type="button" class="botones" onClick="buscar();" value="Buscar">
            </font></div></td>
      </tr>
    </table>
          
    <table width="649" border="0">
      <tr> 
        <td width="315" height="27"> <div align="right"> 
            <p>
              <input type="hidden" name="Totreg" value="<?php echo $Totreg; ?>">
              <input type="hidden" name="NumLayer" value="<?php echo $NumLayer; ?>">
              <input type="hidden" name="idusuario" value="<? echo $xx;?>">
              <input type="hidden" name="cusuario" value="<? echo $cusuario;?>">
              <input type="hidden" name="idfuncionario" value="<? echo $idfuncionario;?>">
              <input type="hidden" name="nomina" value="<? echo '';?>">
              <input type="hidden" name="flujook" value="<? echo $flujo;?>">
            </p>
          </div></td>
        <td width="207"> <div align="right"> Responder Todos 
            <input type="checkbox" name="chektodos" onClick="chequea_todos(document.formulario1);revisa_check();" value="t">
          </div></td>
        <td width="113">&nbsp;</td>
      </tr>
    </table>
    <table width="649" border="0">
      <tr> 
        <td width="315">&nbsp;</td>
        <td width="202"><div align="right"> <font size="2" face="Arial">
            <input name="cmd_aceptar" type="button"  onclick="responder_docto() " class="boton grande" value="Docto Respuesta">
            </font></div></td>
        <td width="118"><input name="cmd_aceptar2" type="button"  onclick="asociar_docto() " class="boton grande" value="Asociar a Docto"></td>
      </tr>
    </table>
  
    <?php 
	      $Corre = 0;
		  $NumLayer = 0;
		 $k = -1;
		  while($reg_documento = mssql_fetch_array($rs_documento)) { 
		  
		  if(fmod($Corre,10)==0) 
		  { 
		  $NumLayer = $NumLayer + 1;
		  if($NumLayer==1){
  		    echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:200px; width:100%; height:130px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: visible">';
		   }
		   else
		   {
		  		    echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:200px; width:100%; height:130px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: hidden">';
		   }
		   
	$k++;	   
                  echo '<script>variables['  . $k  .  ']=' . $reg_documento["id_destino"]  . ';</script>';
	echo "<table width='650' border=1 cellpadding='1' cellspacing='0' bgcolor='#E6EEFF'>"; 

	echo '<tr bgcolor="#6699FF"> ';
        echo '<td width="30"><strong><font color="#FFFFFF" size="2">Num</font></strong></td>';
        echo '<td width="30"><strong><font color="#FFFFFF" size="1">Check</font></strong></td>';
        echo '<td width="45"><strong><font color="#FFFFFF" size="2">Nro Interno</font></strong></td>';
        echo '<td width="40"><strong><font color="#FFFFFF" size="2">Nro Oficial</font></strong></td>';
        echo '<td width="40"><strong><font color="#FFFFFF" size="2">Nro Externo</font></strong></td>';
        echo '<td width="45"><strong><font color="#FFFFFF" size="2">Tipo Documento</font></strong></td>';
        echo '<td width="200"><strong><font color="#FFFFFF" size="2">Materia</font></strong></td>';
        echo '<td width="70"><strong><font color="#FFFFFF" size="2">Fecha Documento</font></strong></td>';
        echo '<td width="130"><strong><font color="#FFFFFF" size="2">Procedencia</font></strong></td>';
        echo '<td width="130"><strong><font color="#FFFFFF" size="2">Destino</font></strong></td>';
        echo '<td width="60"><strong><font color="#FFFFFF" size="2">Nómina</font></strong></td>';
         echo '</tr>';

		  }
		  $Corre =  $Corre + 1;
		  ?>
    <tr>
	    <td align="left" valign="middle" width="30"><font size="2"><?php echo $Corre;?></font></td>
		<td align="left" valign="middle" width="30"><font size="2">
        
        <input type="checkbox" name="casilla_despacho[]" value="<?php echo $reg_documento["id_seguimiento"] ;?>" onClick="cambia_color(this);validar_destino()"></font>
        </td>
        <td align="left" valign="middle" width="45"><font size="2">
          <?php echo $reg_documento["num_interno"];?></font>
        </td>
		 <td align="left" valign="middle" width="40"><font size="2">
          <?php echo $reg_documento["num_oficial"];?></font>
        </td>
		 <td align="left" valign="middle" width="40"><font size="2">
          <?php echo $reg_documento["num_externo"];?></font>
        </td>
	    <td align="left" valign="middle" width="45"><font size="2">
          <?php echo $reg_documento["desc_tipo_documento"];?></font>
        </td>
       
      <td align="left" valign="middle" width="200">
	  <!--font size="2"--> 
	  <font size="2">
      <?php if ($reg_documento["materia"]=="")
		           echo "&nbsp";
				   else echo $reg_documento["materia"];?>
		</font>
		</td>
        <td align="left" valign="middle" width="70"><font size="2">		   
	      <?php $fec_doc=strtotime($reg_documento["fecha_documento"]);
		        $fech_doc=date("d/m/Y",$fec_doc);
				echo $fech_doc;?></font>
        </td>
        <td align="left" valign="middle" width="130"><font size="2">
          <?php echo $reg_documento["procedencia"];?></font>
        </td>
        <td align="left" valign="middle" width="130"><font size="2">
          <?php echo $reg_documento["destino"];?></font>
        </td>
        <td align="left" valign="middle" width="60"><font size="2">
          <?php echo $reg_documento["id_nomina_despacho"];?></font>
        </td>
      </tr>
    <?php if(fmod($Corre,10)==0) { 
	 echo "</table>";
	 echo "</div>";  } ?>
    <?php } 
	?></table>
    </div> 
    <p>&nbsp;</p>
      </form>
	<?php
	} 
	?>  	    
  <p>&nbsp; </p>
</center>  

</body>
</html>
