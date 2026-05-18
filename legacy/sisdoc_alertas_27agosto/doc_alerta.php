<?
 // buscando los documentos que hay que mostrar y dar la opción de imprimir 
include("conexion_bd.php");
$cusuario=$cusuario;
 //echo "usuario  " . $cusuario . "idfunc" .  $id_funcionario . "dep" . $id_dependencia;
// verificando cual viene chequeado como tipo de alerta a imprimir  
if ($tipo <> 1 && $tipo <> 2)
 {$tipo=3;}

$txtagno =date("Y");
 // buscando en tabla dependencias_alerta  la dependencia y los usuarios que les llegará el correo (se  rescata , id_dependencia,usuario,id_usuario)
// $rs_query="exec busca_dependencia_alerta ";
// $rs_dependencia =mssql_query($rs_query,$cn);
// while ($registro=mssql_fetch_array($rs_dependencia))
// { 		// llamar al procedimiento que crea los arreglos que traen los datos 
     ////////////////////////////////////////////////////////////// Antes ///////////////////////////////////////////////////////////////////////////////
	/* $rs ="SELECT *,convert(varchar,d.fecha_documento,103) as fecha_dco, REPLACE(materia, CHAR(13) + CHAR(10), '') AS materia2,convert(varchar,d.fecha_sistema,112) as fecha_timbre_recepcion2 FROM tramite t
			INNER JOIN documento d ON t.id_documento= d.id_documento 
			INNER JOIN tipo_documento c ON d.id_tipo_documento= c.id_tipo_documento
			WHERE (t.id_destino = '".$id_dependencia."' AND t.tipo_destinatario='I') AND (t.id_estado_tramite = 2 OR t.id_estado_tramite = 3) AND (YEAR(t.fecha_despacho) = ".$txtagno.")
			ORDER BY d.fecha_documento,d.id_documento"; */
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			
		if ($id_funcionario!=1419){ 
	 $rs ="SELECT *,convert(varchar,d.fecha_documento,103) as fecha_dco, REPLACE(materia, CHAR(13) + CHAR(10), '') AS materia2,
	 		convert(varchar,d.fecha_sistema,112) as fecha_timbre_recepcion2 , 0 as desc_dependencia_externa
			FROM tramite t
			INNER JOIN documento d ON t.id_documento= d.id_documento 
			INNER JOIN tipo_documento c ON d.id_tipo_documento= c.id_tipo_documento
			WHERE (t.id_destino = '".$id_dependencia."' AND t.tipo_destinatario='I') AND (t.id_estado_tramite = 2 OR t.id_estado_tramite = 3) AND (YEAR(t.fecha_despacho) = ".$txtagno.")
			ORDER BY d.fecha_documento,d.id_documento"; 
	}else{   ;
	 $rs ="SELECT *,convert(varchar,d.fecha_documento,103) as fecha_dco, REPLACE(materia, CHAR(13) + CHAR(10), '') AS materia2,
	 		convert(varchar,d.fecha_sistema,112) as fecha_timbre_recepcion2 , e.desc_dependencia_externa
	 		FROM tramite t
			INNER JOIN documento d ON t.id_documento= d.id_documento 
			INNER JOIN tipo_documento c ON d.id_tipo_documento= c.id_tipo_documento 
			INNER JOIN dependencia_externa e ON t.id_procedencia = e.id_dependencia_externa
			WHERE (t.tipo_destinatario='I') AND (t.id_estado_tramite = 2 OR t.id_estado_tramite = 3) AND (YEAR(t.fecha_despacho) = ".$txtagno.") AND (
	      e.id_dependencia_externa = '4' OR
                      e.id_dependencia_externa = '5' OR
                      e.id_dependencia_externa = '6' OR
                      e.id_dependencia_externa = '53' OR
                      e.id_dependencia_externa = '54' OR
                      e.id_dependencia_externa = '81' OR
                      e.id_dependencia_externa = '89' OR
                      e.id_dependencia_externa = '92' OR
                      e.id_dependencia_externa = '147') AND (t.tipo_procedencia='E')
			ORDER BY d.fecha_documento,d.id_documento"; 
		}
	$rs0 =mssql_query($rs);
		$rs1=mssql_num_rows($rs0);
		$array_doc=array();$array_fecha=array();$array_id_compromiso=array();$array_dias_compromiso=array();
		while ($rs2=mssql_fetch_array($rs0, MYSQL_ASSOC))
		{
			$array_doc[]=$rs2["id_documento"];
			if ($rs2["fecha_timbre_recepcion"]==NULL)
			$array_fecha[]=$rs2["fecha_timbre_recepcion2"];
			else
			$array_fecha[]=$rs2["fecha_timbre_recepcion"];			
			$array_idseg[]=$rs2["id_seguimiento"];
			$array_nomina[]=$rs2["id_nomina_despacho"];
			$array_desc_tipo_doc[]=$rs2["desc_tipo_documento"];
			$array_num_externo[]=$rs2["num_externo"];
			$array_fecha_documento[]=$rs2["fecha_dco"];
			$array_materia[]=$rs2["materia2"];
			$array_num_interno[]=$rs2["num_interno"];
		
			if ($rs2["id_compromiso"]=="")
				$array_id_compromiso[]=9999;
			else
				$array_id_compromiso[]=$rs2["id_compromiso"];			
				$array_dias_compromiso[]=$rs2["dias_compromiso"];
		}		
		$array_verde=array();$array_amarillo=array();$array_rojo=array();
		for ($i=0;$i<$rs1;$i++)
		{
		$rs="";
		if ($array_id_compromiso[$i]==9999)
		{
			$plazo_alerta=10;
			$plazo_limite_dias=20;
		}
		else
		{
			$rs="SELECT * FROM tipo_compromiso2 WHERE id_compromiso=".$array_id_compromiso[$i]."";
			$rs0 =mssql_query($rs);
			$rs3=mssql_fetch_array($rs0, MYSQL_ASSOC);
			$plazo_alerta=$rs3["plazo_alerta"];
			$plazo_limite_dias=$rs3["plazo_limite_dias"];
		}
		$rs="SELECT COUNT(tipo_dia) AS cantidad FROM calendario where fecha BETWEEN ".$array_fecha[$i]." AND GETDATE() AND (tipo_dia = 'H')";
		$rs0 =@mssql_query($rs);
		$rs4=@mssql_fetch_array($rs0, MYSQL_ASSOC);
		$cantidad=$rs4["cantidad"];
		if ($cantidad<$plazo_alerta)
		 {
				$array_verde[]=$array_doc[$i];
				$verde_idseg[]=$array_idseg[$i];
				$verde_nomina[]=$array_nomina[$i];
				$verde_desc_tipo_doc[]=$array_desc_tipo_doc[$i];
				$verde_num_externo[]=$array_num_externo[$i];
				$verde_fecha_documento[]=$array_fecha_documento[$i];
				$verde_materia[]=$array_materia[$i];
				$verde_num_interno[]=$array_num_interno[$i];
		  }
		if (($cantidad>=$plazo_alerta) && ($cantidad<$plazo_limite_dias-2))
		 {
				$array_amarillo[]=$array_doc[$i];
				$amarillo_idseg[]=$array_idseg[$i];
				$amarillo_nomina[]=$array_nomina[$i];
				$amarillo_desc_tipo_doc[]=$array_desc_tipo_doc[$i];
				$amarillo_num_externo[]=$array_num_externo[$i];
				$amarillo_fecha_documento[]=$array_fecha_documento[$i];
				$amarillo_materia[]=$array_materia[$i];
				$amarillo_num_interno[]=$array_num_interno[$i];
		}
		if ($cantidad>=$plazo_limite_dias-2)
		{
				$array_rojo[]=$array_doc[$i];
				$rojo_idseg[]=$array_idseg[$i];
				$rojo_nomina[]=$array_nomina[$i];
				$rojo_desc_tipo_doc[]=$array_desc_tipo_doc[$i];
				$rojo_num_externo[]=$array_num_externo[$i];
				$rojo_fecha_documento[]=$array_fecha_documento[$i];
				$rojo_materia[]=$array_materia[$i];
				$rojo_num_interno[]=$array_num_interno[$i];
		}
	  }
	  
	// Desde aca se empieza a  armar el arreglo para poder sacar en listado e impresion los documentos 
	  // documentos en verde 
	  
	  $largo_verde= count($array_verde);
		$arreglo =array();
		$arreglo= $array_verde;
		$segdoc =$verde_idseg;
		 $arreglo_verde='' ;
		  $seg_verde='';
		 if ($largo_verde==0)
		     $arreglo_verde='' ;
		 else 
		  {  $arreglo_verde=$largo_verde . '@' . $arreglo[0];	 
		    $seg_verde=$largo_verde . '@' . $segdoc[0];
			}
		for ($i=1;$i<$largo_verde;$i++)
	     {  
		   $arreglo_verde=$arreglo_verde. '@' . $arreglo[$i];		 
  		   $seg_verde =$seg_verde . '@' . $segdoc[$i];

		 }	
	  // documentos en amarillo 
	  $largo_amarillo= count($array_amarillo);
		$arreglo =array();
		$arreglo= $array_amarillo;
		$segdoc =$amarillo_idseg;
 	     $arreglo_amarillo='' ;
		  $seg_amarillo='';
		 if ($largo_amarillo==0)
		    { $arreglo_amarillo='' ;
			  $seg_amarillo='';}
		 else 
		  {  $arreglo_amarillo=$largo_amarillo . '@' . $arreglo[0];	 
  			 $seg_amarillo=$largo_amarillo . '@' . $segdoc[0];

		 }
		for ($i=1;$i<$largo_amarillo;$i++)
	     {  
		   $arreglo_amarillo=$arreglo_amarillo. '@' . $arreglo[$i];		 
 		   $seg_amarillo =$seg_amarillo . '@' . $segdoc[$i];
          }	
	  // documentos en  rojo
	     
	    $largo_rojo= count($array_rojo);
		$arreglo =array();
		$arreglo= $array_rojo;
		$segdoc =$rojo_idseg;
 	    $arreglo_rojo='' ;
		 $seg_rojo='';
		 if ($largo_rojo==0)
		    { $arreglo_rojo='' ;
			 $seg_rojo='';}
		 else 
		    {$arreglo_rojo=$largo_rojo . '@' . $arreglo[0];	 
			$seg_rojo=$largo_rojo . '@' . $segdoc[0];
		   }
		for ($i=1;$i<$largo_rojo;$i++)
	     {  
		   $arreglo_rojo=$arreglo_rojo. '@' . $arreglo[$i];		 
		   $seg_rojo =$seg_rojo . '@' . $segdoc[$i];
		 }	

//obteniendo el total de  documentos de cada tipo de alerta 		 
if ($largo_rojo<>0)
{$arreglorojo = split ("@",$arreglo_rojo);
  $segrojo    = split ("@",$seg_rojo);
$largo_rojo= $arreglorojo[0];}

if ($largo_verde <>0)
{$arregloverde = split ("@",$arreglo_verde);
 $segverde    = split ("@",$seg_verde);
 $largo_verde= $arregloverde[0];
}

if($largo_rojo<>0)
{$arregloamarillo= split ("@",$arreglo_amarillo);
 $segamarillo    = split ("@",$seg_amarillo);
 $largo_amarillo= $arregloamarillo[0];
}

$Totreg =0;
if ($tipo ==1) // Verdes 
 { 
    if ($largo_verde<>0)
	 {$Totreg = $largo_verde;}
 }	 
else 
if ($tipo==2) // Amarillos
{ if ($largo_amarillo <>0)
	 {$Totreg = $largo_amarillo;}
}
else 
  if ($tipo==3) // rojos
	{ if ($largo_rojo <>0)
	 {	 $Totreg = $largo_rojo;	 }
	}

$NumPag= intval($Totreg/10);
if(fmod($Totreg,10)==0) 
  { 
  $NumPag = $NumPag;
  }
else
  {
  $NumPag=$NumPag + 1;
  }
if ($Totreg ==0) 
{
 
    echo '<script>' ; 
	echo 'alert("No hay documentos en alerta con ese estado  ")';
	echo '</script>' ;
}
else
{		 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Informe  documentos en alerta  </title>
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
function imp_informe()
{

  document.form1.action="imp_docalerta.php";
  document.form1.submit();
}
function revisa_check() 	
{
	var sicheck = 0;
  	for (var n=0; n < form1.elements.length; n++) 
	{
     if (form1.elements[n].checked) 
	  {	     sicheck = 1; 	}	         	 
	}
	 if (sicheck == 0) 
	  {  alert("Debe seleccionar un documento");
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
  alert("Se imprimirán todos los documentos");
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
<!--generando reporte para mostrar documentos y poder imprimirlos -->
<? if ($tipo ==1)
   $alerta ='Verde';
   else
     if ($tipo==2)
	   $alerta ='Amarillo';
	  else  $alerta ='Rojo'; ?>
	   
<form name="form1" method="post" onsubmit="return revisa_check(this.form);" >
 <table width="650" border="0" align="center">
      <tr>
        <td><div align="center"><strong><?php echo "Documentos en Alerta " . $alerta; ?></strong></div></td>
      </tr>
    </table>
    
  <table width="652" border="0" align="center">
    <tr> 
      <td width="459"><div align="left"><strong><?php echo "Total de Páginas : " . $NumPag ?></strong></div></td>
      <td width="183"><div align="right"><strong><?php echo "Total Registros : " . $Totreg ?></strong></div></td>
    </tr>
    <tr> 
      <td> <div align="left"><strong>Selecciona Todos 
          <input type="checkbox" name="chektodos" onClick="chequea_todos(document.form1);revisa_check();" value="t">
          </strong></div></td>
    </tr>
  </table>
	
  <table width="650"  border="0" cellpadding="1" cellspacing="0" >
    <tr> 
    
      <table width="650" border="0" align="center">
        <tr> 
          <td width="580"> 
            <?php
		        echo "<div align='left'><b>";
		        for ($i = 1; $i <= $NumPag; $i++)
				 {	 echo "<img src='botones/boton" . $i . ".gif' width='44' height='16'". 
					 "onClick=\"MM_showHideLayers('layer" . $i . "','','show',$i, $NumPag)\">";            
				 } 
			     echo "</b></div>";
		    ?>
          </td>
          <td width="70"> <input name="cmd_imp" type="button" class="botones" onClick="imp_informe();" value="Imprimir"></td>
        </tr>
      </table>
      <? 
			  $Corre = 0;
		      $NumLayer = 0;
			  if ($tipo==3)
			     { $largo=$largo_rojo;
				    $segui=$segrojo;
				 }
			  else 
  				  if ($tipo==2)
			     { $largo=$largo_amarillo;
				    $segui=$segamarillo;
				 }
			  else 
  				  if ($tipo==1)
			      { $largo=$largo_verde;
				    $segui=$segverde;
				  }
			  for($k=1;$k <=$largo;$k++) 
				 {
				   $rs_dep="exec despliegue_datos_alerta'" . $segui[$k]. "'";			  
				   $rsdep=mssql_query($rs_dep); 
	               $rsp = mssql_fetch_array($rsdep);			  
	   			   if(fmod($Corre,10)==0) 
   		           { 
     			     $NumLayer = $NumLayer + 1;
				     if($NumLayer==1)
     				  {
			  		    echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:130px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: visible">';
				      }
			          else
			          {
				      echo '<div id="layer' . $NumLayer . '" style="position:absolute; left:10px; top:130px; width:100%; height:164px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: hidden">';
			          }
				      echo "<table width='650' border='1' cellpadding='1' cellspacing='0' bgcolor='#E6EEFF' align='center'>"; 
			          echo '<tr bgcolor="#6699FF">';
	   		          echo '<td width="5%" height="33"><strong><font color="#FFFFFF" size="2">Num</font></strong></td>';
				      echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nº Documento</font></strong></td>';
			          echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Tipo doc. </font></strong></td>';
				      echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nómina</font></strong></td>';
			          echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Interno</font></strong></td>';
			          echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Nro Externo</font></strong></td>';
			          echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Fecha doc.</font></strong></td>';
			          echo '<td width="100%" height="33"><strong><font color="#FFFFFF" size="2">Materia</font></strong></td>';
    			      echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Origen</font></strong></td>';
				      echo '<td width="8%" height="33"><strong><font color="#FFFFFF" size="2">Marca</font></strong></td>';
			          echo '</tr>';		 	 
			      }
		         $Corre =  $Corre + 1;	?>
    <tr> 
      <td><?php echo $Corre;?></font></td>
      <td> 
        <?php  
	             if ($tipo==1)  //verdes
				 {	
				 echo '<a href="tramites_deriva.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . 
		       	'&iddocum=' . $arregloverde[$k] . '&idseguim=' . $segui[$k] .
		        '&idfuncionario=' . $idfuncionario . '">Ver trámites</a>';  
                  }
				  else
	             if ($tipo==2) // amarillos
				 {	
				 echo '<a href="tramites_deriva.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . 
		       	'&iddocum=' . $arregloamarillo[$k] . '&idseguim=' . $segui[$k] .
		        '&idfuncionario=' . $idfuncionario . '">Ver trámites</a>';  
                  }
				  else  //rojo 
			     {
				 echo '<a href="tramites_deriva.php?cusuario=' . $cusuario . '&idusuario=' . $idusuario . 
		       	'&iddocum=' . $arreglorojo[$k] . '&idseguim=' . $segui[$k] .
		        '&idfuncionario=' . $idfuncionario . '">Ver trámites</a>';  
				 }?></font>
        </td>
      <td width="8%" height="33"> <? echo $rsp[desc_tipo_documento]; ?> </td>
      <td width="8%" height="33"> <? echo  $rsp[id_nomina_despacho];?> </td>
      <td width="8%" height="33"> 
        <?  echo $rsp[num_interno];?>
      </td>
      <td width="8%" height="33"> <? echo $rsp[num_externo];?> </td>
      <td width="8%" height="33"> 
        <? $fec_doc=strtotime($rsp[fecha_documento]);
		  		 $fech_doc=date("d/m/Y",$fec_doc);
		      	 echo $fech_doc;
			  ?>
      </td>
      <td width="100%" height="33"><? echo $rsp[materia]; ?></td>
      <td width="8%" height="33"> 
        <?  // desplegando nombre de procedencia 
			  echo $rsp[procedencia]; ?>
      </td>
      <td width="8%" height="33"> <input type="checkbox" name="casilla_documento[]" value="<?php echo $rsp["id_seguimiento"];?>" onClick="cambia_color(this)"></font> 
      </td>
    </tr>
    <?php if(fmod($Corre,10)==0) { 
	 echo "</table>";
	 echo "</div>";  } ?>
    <?php } ?>
  </table>
    </div> 
	<input type="hidden" name="tipo" >
	<input type="hidden" name="Totreg" value ="<? echo $Totreg ;?>">
    <input type="hidden" name="id_dependencia" value ="<? echo $id_dependencia ;?>" >
  </form>
  </center>
</body>
</html>
<? }?>	 

