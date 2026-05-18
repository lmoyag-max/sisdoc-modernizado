<?php
include("conexion_bd.php");
//global $cn, $varSql;

$nom_dep=$cod;

//echo "cod " . $cod_dep . "sw " . $sw . "prod " . $prod . "numero" . $numero;
$id_dep = $cod_dep;
$desd=$des_d;
$desf=$des_f;
$prod=$pro_d;
$prof=$pro_f;

// busca descriptor para cargar en la modificacion del documento los que tiene asociado el documento //
if ($sw=="carga_desc")
  {
     if ($registro=='')
      {	 $reg=0;   }
      else
        {   $reg=$registro;  }
  //busca los descriptores del documento //
     $relacion_query= "exec consulta_descriptores'". $reg . "'" ;
     $rs_relacion=mssql_query($relacion_query,$cn);
     $tot_registros=mssql_num_rows($rs_relacion);
     if ($tot_registros != 0)
      {	
      	$aux_arreglo ="";
      	$aux_arreglo=$tot_registros . "@";
      	while ($reg_desc=mssql_fetch_array($rs_relacion))
      	{  
      	  $aux_arreglo=$aux_arreglo . $reg_desc[id_descriptor] . "@";      
       	}
        echo "<script> \n";
        echo "parent.mainFrame.document.form1.arreglo.value='". $aux_arreglo . "' ;\n";
        echo "</script>\n";
      }
      $reg_desc.close;
      $rs_relacion.close;
   }           		

// Busca documentos similares al ingresado
if ($sw=="S")
{
$tdoc=$tipo_doc;
$pro=$proc;
$tp=$tipo_p;
$des=$dest;
$td=$tipo_d;
$nof=$num_ofi;
if ($fec_doc <> "")
{
$dia = substr($fec_doc,0,2);
$mes = substr($fec_doc,3,2);
$año = substr($fec_doc,6,4);
$Fec_doc= date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));
}
$fd=$Fec_doc;
//echo "fecha " . $fd . "  nof  " . $nof;
//echo "t_docto " . $tdoc . "pro " . $pro . "t_pro "  . $tp . "des " . $des . "t_des " . $td;
if ($nof != null)
{
$rs_rep="exec busca_doc_similares '" . $tdoc . "','" . $pro . "','" . $tp . "','" . 
$des . "','" . $td . "','" . $nof . "','" . $fd . "'";
$rs_similar=mssql_query($rs_rep,$cn);  
$reg_sim=mssql_fetch_array($rs_similar);
$tot_sim=mssql_num_rows($rs_similar);
}
else { $tot_sim=0;}
if ($tot_sim > 0)
{
 echo "<html>\n";
 echo '<body onLoad="parent.mainFrame.similar(5);">';
 echo '</body></html>'  . "\n";
}
else
{
echo "<html>\n";
echo '<body onLoad="parent.mainFrame.similar(0);">';
echo '</body></html>'  . "\n";
}


$reg_sim.close;
$rs_similar.close;
}


// Busca expediente 
if ($sw==0 and $cod_dep =="X"  )
 {

$rs_query ="select * from expediente where  id_expediente =$numero"  ;
//echo "consulta " . $rs_query . " " ;
$reg_query = mssql_query($rs_query);
$reg_exp = mssql_fetch_array($reg_query);
$tot = mssql_num_rows($reg_query);
//echo "total " . $tot  ;

echo "<script>\n";
if ($tot > 0)
	{
	$total=1;
	$descripcion =$reg_exp["desc_expediente"];
    }
else 
	{
	 $total=0 ;
     $descripcion="";
	 echo  "alert('Expediente  no existe');";
     echo " parent.mainFrame.document.form1.txtexped.value='0';\n";
     echo ' parent.mainFrame.document.form1.txtexped.focus();' . ";\n";
	}
     echo " parent.mainFrame.document.form1.totexped.value='" . $total ."';\n";
     echo " parent.mainFrame.document.form1.txtdescrip.disabled='True';\n";
     echo " parent.mainFrame.document.form1.txtdescrip.value='" . $descripcion ."';\n";
      
echo "</script>\n";
$reg_exp.close;
$reg_query.close;
}

// fin de busqueda expediente 

// busca datos del documento de referencia 

if ($sw=="G" and $cod=="D")
{
   $rs_documento="exec documento_referencia '" . $docu . "','" . $segu . "'";
   $qq = mssql_query($rs_documento,$cn); 
   $rs=mssql_fetch_array($qq);
   $totd =mssql_num_rows($qq);
   $xtipodoc =$rs[desc_tipo_documento];
   $xnuminterno=$rs[num_interno];
   $xnumoficial=$rs[num_oficial]; 
   $xnumexterno=$rs[num_externo]; 
   $xoriginal= $rs[original];
   $xprocedencia=$rs[procedencia];
   $xfuncproced=$rs[funcproced];
   
   $fec_doc=strtotime($rs[fecha_documento]);
   $xfech_doc=date("d/m/Y",$fec_doc);
   
   $xmateria =$rs[materia];
   if (substr(trim($rs[materia]),-1)==".")
   {  
    $xmateria = substr(trim($rs[materia]),0,strlen(trim($rs[materia]))-1);
   }
   /* echo $xtipodoc . "<br>";
	echo $xnuminterno . "<br>";

	echo $xnumoficial . "<br>";
	echo $xnumexterno . "<br>";
*/
	echo $original ;
/*	echo $xprocedencia . "<br>";
	echo $xfuncprocd . "<br>";
	 echo $fec_doc . "<br>";
	echo $xmateria . "<br>";
	*/
 echo "<script>\n";
   if ($totd >0)
   { 
   
     echo " parent.mainFrame.document.form_layer.txttipodoc.disabled='True';\n";
     echo " parent.mainFrame.document.form_layer.txttipodoc.value='" . $xtipodoc. " ';\n";
	 
	 echo " parent.mainFrame.document.form_layer.txtnuminterno.disabled='True';\n";
     echo " parent.mainFrame.document.form_layer.txtnuminterno.value='" . $xnuminterno. " ';\n";
     
	 echo " parent.mainFrame.document.form_layer.txtnumoficial.disabled='True';\n";
     echo " parent.mainFrame.document.form_layer.txtnumoficial.value='" . $xnumoficial. " ';\n";
     
	 echo " parent.mainFrame.document.form_layer.txtnumexterno.disabled='True';\n";
     echo " parent.mainFrame.document.form_layer.txtnumexterno.value='" . $xnumexterno. " ';\n";
     
	 echo " parent.mainFrame.document.form_layer.txtoriginal.disabled='True';\n";
     echo " parent.mainFrame.document.form_layer.txtoriginal.value='" . $xoriginal. " ';\n";
     
	 echo " parent.mainFrame.document.form_layer.txtfecha.disabled='True';\n";
     echo " parent.mainFrame.document.form_layer.txtfecha.value='" . $xfech_doc. " ';\n";
     
	 echo " parent.mainFrame.document.form_layer.txtprocedencia.disabled='True';\n";
     echo " parent.mainFrame.document.form_layer.txtprocedencia.value='" . $xprocedencia. " ';\n";
     
	 echo " parent.mainFrame.document.form_layer.txtfuncproced.disabled='True';\n";
     echo " parent.mainFrame.document.form_layer.txtfuncproced.value='" . $xfuncproced. " ';\n";
	 
	 echo " parent.mainFrame.document.form_layer.txtmateria.disabled='True';\n";
     echo " parent.mainFrame.document.form_layer.txtmateria.value='" . $xmateria ."';\n";
	}	 
   echo "</script>\n";
   
   
$qq.close;
$rs.close;
}
// fin busqueda 

// Busca descriptor 
if ($sw=="D"  and $cod !="")
{
   $rs_desc = mssql_query("SELECT * FROM descriptor  where id_descriptor='" . $cod . "'", $cn);
   $reg_desc = mssql_fetch_array($rs_desc);
   $totdesc = mssql_num_rows($rs_desc);
   $descrip=$reg_desc["desc_descriptor"];
   echo "<script>\n";
   if ($totdesc>0)
   {
     if($cual==1) 
     {
       echo " parent.mainFrame.document.form1.Descriptor1.value='" . $descrip ."';\n";
       echo " parent.mainFrame.document.form1.Descriptor1.disabled='True';\n";
     } 
     else if($cual==2) 
     {
       echo " parent.mainFrame.document.form1.Descriptor2.value='" . $descrip ."';\n";
       echo " parent.mainFrame.document.form1.Descriptor2.disabled='True';\n";
     } 
     else if($cual==3) 
     {
       echo " parent.mainFrame.document.form1.Descriptor3.value='" . $descrip ."';\n";
       echo " parent.mainFrame.document.form1.Descriptor3.disabled='True';\n";
     } 
   }
   else
   {
     if($cual==1) 
     {
       echo ' parent.mainFrame.document.form1.Descriptor1.value="";' . ";\n";
	   echo ' parent.mainFrame.document.form1.Descriptor1.focus();' . ";\n";
     } 
     else if($cual==2) 
     {
       echo ' parent.mainFrame.document.form1.Descriptor2.value="";' . ";\n";
	   echo ' parent.mainFrame.document.form1.Descriptor2.focus();' . ";\n";
     } 
     else if($cual==3) 
     {
       echo ' parent.mainFrame.document.form1.Descriptor3.value="";' . ";\n";
	   echo ' parent.mainFrame.document.form1.Descriptor3.focus();' . ";\n";
     } 
   
     echo  "alert('Descriptor no existe');";
   }
   echo "</script>\n";
   $reg_desc.close;
   $rs_desc.close;
}
// Termina descriptor 

// Busca si existe un documento proveniente del mismo lugar con el mismo nro externo
if ($sw=="BP")
 {
 if ($fec <> "")
{
$dia = substr($fec,0,2);
$mes = substr($fec,3,2);
$año = substr($fec,6,4);
$Fec_doc= date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));
}
$fe=$Fec_doc;

//echo "ext" .  $desd . ",proc" .  $prod . ",tipo doc" . $id_dep  . "tipo proc" . $tip . "fecha " . $fe ;
if ($desd != 0)
{
//$rs_doc="exec busca_externo '" . $desd . "','" . $prod . "','" . $id_dep . "','". $tip  ."', '". $fec  ."'";
//$rs_doc="exec busca_externo_prueba '" . $desd . "','" . $prod . "','" . $id_dep . "','". $tip  ."', '". $fe  ."'";
$rs_doc="exec busca_externo '" . $desd . "','" . $prod . "','" . $id_dep . "','". $tip  ."', '". $fe  ."'";
$rs_documento=mssql_query($rs_doc,$cn);  
$Totreg = mssql_num_rows($rs_documento);
}
else
{ $Totreg = 0;}

if($Totreg==0)
{
    echo "<html>\n";
    echo '<body onLoad="parent.mainFrame.grabando(true);">';
	echo '</body></html>'  . "\n";

}
else
{
    echo "<html>\n";
    echo '<body onLoad="parent.mainFrame.grabando(false);">';
	echo '</body></html>'  . "\n";
}

$rs_documento.close;
$rs_doc.close;
}

// ***************** Busca Procedencia para oficina de partes ******************************
if ($sw=="TP" and $nom_dep !="")
 {
$rs_dep = mssql_query("select id_dependencia_externa, SUBSTRING(desc_dependencia_externa, 1, 35) AS desc_dependencia_externa, cod_dependencia_externa
from dependencia_externa order by desc_dependencia_externa", $cn);
$rs_dep1= mssql_query("select id_dependencia_externa from dependencia_externa where cod_dependencia_externa = '" . $nom_dep . "'", $cn);
$Totreg = mssql_num_rows($rs_dep1);
$reg_dep1 = mssql_fetch_array($rs_dep1);
$iddep=$reg_dep1["id_dependencia_externa"];
//echo "depen" . $iddep;
echo "<script>\n";
$i=0;

while($reg_dep = mssql_fetch_array($rs_dep))
{
    
 if($iddep==$reg_dep[id_dependencia_externa]) 
 	echo " parent.mainFrame.document.form1.Cbo_Procedencia.selectedIndex='" . $i ."';\n";
	echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].selected;\n";
  	$i = $i + 1;
}
if($iddep =="")
{
echo "alert('Código no existe');";
echo ' parent.mainFrame.document.form1.Txtprocedencia.value="";' . ";\n";
}

echo "</script>\n";
$reg_dep1.close;
$rs_dep1.close;
$reg_dep.close;
$rs_dep.close;

}
else

// ***************** Busca Destino para ingreso en oficina de partes ******************************
if ($sw=="TD" and $nom_dep !="")
 {
/*$rs_destino = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia
from dependencia order by desc_dependencia", $cn);
*/
// se  cambia para que tome solo los que se requieren para oficina de partes y no todo el listado 
//$query = "select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia from dependencia where ofpartes='S' order by desc_dependencia";

// se cambia para que tome a los departamentos que se requieren para oficina de partes y  no todo el listado pero ademas se considera el codigo nuevo 
$query = "select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia_nuevo from dependencia where ofpartes='S' order by desc_dependencia";

$rs_destino =mssql_query($query, $cn);

//$query = "select id_dependencia from dependencia where cod_dependencia = '" . $nom_dep . "'" . " and ofpartes=" . "'S'" ;

// con codigo nuevo 
$query = "select id_dependencia from dependencia where cod_dependencia_nuevo = '" . $nom_dep . "'" . " and ofpartes=" . "'S'" ;

//echo $query ;
$rs_dep1= mssql_query($query, $cn);
$Totreg = mssql_num_rows($rs_dep1);
$reg_dep1 = mssql_fetch_array($rs_dep1);
$iddes=$reg_dep1["id_dependencia"];
echo "<script>\n";
$i=0;
while($reg_destino = mssql_fetch_array($rs_destino))
 {
  
  if($iddes==$reg_destino[id_dependencia]) 
   
 	echo " parent.mainFrame.document.form1.Cbo_Destinatario.selectedIndex='" . $i ."';\n";
	echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].selected;\n";
  	$i = $i + 1;
 }
if($iddes =="")
{
echo "alert('No tiene acceso a derivar a este código ');";
echo ' parent.mainFrame.document.form1.Txtdestino.value="";' . ";\n";
}

echo "</script>\n";
$reg_dep1.close;
$rs_dep1.close;
$reg_destino.close;
$rs_destino.close;

}

else
// busca  procedencia para ingresar numero oficial desde oficina de partes //
// ***************** Busca Destino para ingreso en oficina de partes ******************************
if ($sw=="PI" and $nom_dep !="")
 {
// echo "entra" . "nom   " . $nom_dep ;
/*$rs_destino = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia
from dependencia order by desc_dependencia", $cn);
*/

// se  cambia para que tome solo los que se requieren para oficina de partes y no todo el listado 
//$query = "select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia from dependencia order by desc_dependencia";

// se  cambia para que tome solo los que se requieren para oficina de partes y no todo el listado  y solo los que estan  vigentes 
$query = "select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia_nuevo from dependencia  where vigencia is null order by desc_dependencia";

$rs_destino =mssql_query($query, $cn);

//$query = "select id_dependencia from dependencia where cod_dependencia = '" . $nom_dep . "'" . " and ofpartes=" . "'S'" ;
$query = "select id_dependencia from dependencia where cod_dependencia_nuevo = '" . $nom_dep . "'"  ;
$rs_dep1= mssql_query($query, $cn);
$Totreg = mssql_num_rows($rs_dep1);
$reg_dep1 = mssql_fetch_array($rs_dep1);
$iddes=$reg_dep1["id_dependencia"];
echo "<script>\n";
$i=0;
while($reg_destino = mssql_fetch_array($rs_destino))
 {
  
  if($iddes==$reg_destino[id_dependencia]) 
   
 	echo " parent.mainFrame.document.form1.Cbo_Destinatario.selectedIndex='" . $i ."';\n";
	echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].selected;\n";
  	$i = $i + 1;
 }
if($iddes =="")
{
echo "alert('Código no existe ');";
echo ' parent.mainFrame.document.form1.Txtdestino.value="";' . ";\n";
}

echo "</script>\n";
$reg_dep1.close;
$rs_dep1.close;
$reg_destino.close;
$rs_destino.close;

}
///****************************DESTINO INTERNO PARA CONSULTAS ****************/
if ($sw=="I" and $cod_dep==2) 
 {
$rs_dep = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia_nuevo
from dependencia order by desc_dependencia", $cn);
$Totreg = mssql_num_rows($rs_dep);
$i=0;
echo "<script>\n";
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].text='-------------';\n";

while($reg_dep_ext = mssql_fetch_array($rs_dep))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].value='" . $reg_dep_ext[id_dependencia] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].text='" . $reg_dep_ext[desc_dependencia] . "';\n";
	}
echo "</script>\n";
$reg_dep_ext.close;
$rs_dep.close;
}

//  ***************** DESTINO EXTERNO ********
if ($sw=="E")
{
if(isset($id_dep))
 {
$rs_dep_ext = mssql_query("select id_dependencia_externa, SUBSTRING(desc_dependencia_externa, 1, 35) AS desc_dependencia_externa, cod_dependencia_externa
from dependencia_externa order by desc_dependencia_externa", $cn);
$Totreg = mssql_num_rows($rs_dep_ext);

// inicializa en -1 para cuando sume 1 quede en Cero y agregar datos  a la combo del otro frame (mainFrame)
// posteriormente define el tamaño o largo del combo según el total de registros encontrados
$i=0;
echo "<script>\n";

$total_rec = $Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].text='-------------';\n";

while($reg_dep_ext = mssql_fetch_array($rs_dep_ext))
   {
        $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].value='" . $reg_dep_ext[id_dependencia_externa] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].text='" . $reg_dep_ext[desc_dependencia_externa] . "';\n";
	}
echo " parent.mainFrame.document.form1.Cbo_Destinatario.selectedIndex='" . $desd ."';\n";	
echo "</script>\n";
$reg_dep_ext.close;
$rs_dep_ext.close;
}
}
else
// ********************** PROCEDENCIA EXTERNA **************
if ($sw=="PE")
{
if(isset($id_dep)) 
 {
   $rs_dep_ext = mssql_query("select id_dependencia_externa, SUBSTRING(desc_dependencia_externa, 1, 35) AS desc_dependencia_externa, cod_dependencia_externa
	from dependencia_externa order by desc_dependencia_externa", $cn);
	$Totreg = mssql_num_rows($rs_dep_ext);
	$i=0;
	echo "<script>\n";
	
	$total_rec=$Totreg+1;
	echo " parent.mainFrame.document.form1.Cbo_Procedencia.options.length= " . $total_rec . ";\n";
	echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].value='0';\n";
	echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].text='-------------';\n";

	while($reg_dep_ext = mssql_fetch_array($rs_dep_ext))
   	{
        	$i = $i + 1;
       	echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].value='" . $reg_dep_ext[id_dependencia_externa] . "';\n";
       	echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].text='" . $reg_dep_ext[desc_dependencia_externa] . "';\n";
  	}

	echo "</script>\n";  
	$reg_dep_ext.close;
	$rs_dep_ext.close;
}
}
else
// ********************** PROCEDENCIA INTERNA **********************

if (($sw=="I" and  $cod_dep==1) or ($sw =="I" and $cod_dep==2) or ($sw =="I" and $cod_dep==3))
 {
//  interno pero solo muestra accesos del usuario 
 if ($cod_dep==1)
 {	

//	 $rs_dep= mssql_query("select dependencia.id_dependencia, SUBSTRING(dependencia.desc_dependencia, 1, 35) AS desc_dependencia, dependencia.cod_dependencia
	//from dependencia, acceso where acceso.id_dependencia = dependencia.id_dependencia and acceso.id_usuario = " . $prod, $cn);
	// considera solo los del usuario pero que ademas esten vigentes 
	 $rs_dep= mssql_query("select dependencia.id_dependencia, SUBSTRING(dependencia.desc_dependencia, 1, 35) AS desc_dependencia, dependencia.cod_dependencia_nuevo
	from dependencia, acceso where acceso.id_dependencia = dependencia.id_dependencia  and dependencia.vigencia is null and acceso.id_usuario = " . $prod, $cn);
 }
 else 
  if ($cod_dep==2)
{
	// interno pero  que muestra todos las dependencias del ministerio 
	//$rs_dep= mssql_query("select dependencia.id_dependencia, SUBSTRING(dependencia.desc_dependencia, 1, 35) AS desc_dependencia, dependencia.cod_dependencia
	//from dependencia order by desc_dependencia ", $cn);
   
   // muestra todos los del ministerio pero que esten vigentes 

	$rs_dep= mssql_query("select dependencia.id_dependencia, SUBSTRING(dependencia.desc_dependencia, 1, 35) AS desc_dependencia, dependencia.cod_dependencia_nuevo
	from dependencia  where dependencia.vigencia is null order by dependencia.desc_dependencia ", $cn);
}	
 else 
   if ($cod_dep==3)
   {
// interno pero  que muestra todos las dependencias del ministerio 
	$rs_dep= mssql_query("select dependencia.id_dependencia, SUBSTRING(dependencia.desc_dependencia, 1, 35) AS desc_dependencia, dependencia.cod_dependencia
	from dependencia order by desc_dependencia ", $cn);
   
   
	}	
$Totreg = mssql_num_rows($rs_dep);
$i=0;
echo "<script>\n"; 
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].text='-------------';\n";

while($reg_dep = mssql_fetch_array($rs_dep))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].value='" . $reg_dep[id_dependencia] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].text='" . $reg_dep[desc_dependencia] . "';\n";
   }
echo "</script>\n"; 
$reg_dep.close;
$rs_dep.close;

}
else
// ******************* DESTINO INTERNO EN INGRESOS ************************

if ($sw=="I" and $cod_dep==0) 
 {
//$rs_dep = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia
//from dependencia order by desc_dependencia", $cn);
// Para que muestre todos los del ministerio vigentes 
$rs_dep = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia_nuevo
from dependencia  where vigencia is NULL order by desc_dependencia", $cn);
$Totreg = mssql_num_rows($rs_dep);
$i=0;
echo "<script>\n";
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].text='-------------';\n";

while($reg_dep_ext = mssql_fetch_array($rs_dep))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].value='" . $reg_dep_ext[id_dependencia] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].text='" . $reg_dep_ext[desc_dependencia] . "';\n";
	}
echo "</script>\n";
$reg_dep_ext.close;
$rs_dep.close;
}
else
//proc_interno
if ($sw=="PI" and $cod_dep==1)
 {

$rs_dep = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia
from dependencia order by desc_dependencia", $cn);
$Totreg = mssql_num_rows($rs_dep);
$i=0;
echo "<script>\n";
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].text='-------------';\n";

while($reg_dep_ext = mssql_fetch_array($rs_dep))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].value='" . $reg_dep_ext[id_dependencia] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].text='" . $reg_dep_ext[desc_dependencia] . "';\n";
	}
echo "</script>\n";
$reg_dep_ext.close;
$rs_dep.close;
}

else
//  **************** COMBO PROCEDENCIA *********************

if ($sw=="")
{

if(isset($id_dep)) {
$op="I";
//$query="SELECT * FROM funcionario where id_dependencia = " . $id_dep . "order by nombres,apellidos";
// para  que muestre solo funcionarios vigentes // 
$query="SELECT * FROM funcionario where vigencia is NULL  and id_dependencia = " . $id_dep .  "order by nombres,apellidos";

$rs_funcionario = mssql_query($query, $cn);
//$descriptor_query="exec ingreso_descriptor '" . $vector[$x] . "','" . $Id_Documento . "','" . $op . "'";
//$rs_funcionario2 = mssql_query("exec busca_funcionario " . $id_dep, $cn);

$Totreg = mssql_num_rows($rs_funcionario);


$i=0;
echo "<script>\n";
$total_rec = $Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options.length=" . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[0].text='-------';\n";

while($reg_funcionario = mssql_fetch_array($rs_funcionario))
   {
       $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[" . $i . "].value='" . $reg_funcionario[rut] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[" . $i . "].text='" . $reg_funcionario[nombres] . ' ' . $reg_funcionario[apellidos] .  "';\n";
	  
	}
echo "</script>\n";	
$reg_funcionario.close;
$rs_funcionario.close;
}
}

else
//  ******** COMBO FUNCIONARIO PROCEDENCIA Y DESTINO QUEDA MARCADO EL QUE VENIA SELECCIONADO ***********

if ($sw=="II")
{
$rs_funcionario = mssql_query("SELECT * FROM funcionario where id_dependencia = " . $prod, $cn);
$Totreg = mssql_num_rows($rs_funcionario);
$i=0;
echo "<script>\n";

$total_rec = $Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options.length=" . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[0].text='-------';\n";

while($reg_funcionario = mssql_fetch_array($rs_funcionario))
   {
       $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[" . $i . "].value='" . $reg_funcionario[rut] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[" . $i . "].text='" . $reg_funcionario[nombres] . ' ' . $reg_funcionario[apellidos] . "';\n";
	   
	}


echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.selectedIndex='" . $prof ."';\n";
echo "</script>\n";	
$reg_funcionario.close;
$rs_funcionario.close;

$rs_funcionario2 = mssql_query("SELECT * FROM funcionario where id_dependencia = " . $desd . "order by nombres,apellidos" , $cn);
$Totreg2 = mssql_num_rows($rs_funcionario2);
$i=0;
echo "<script>\n";

$total_rec2 = $Totreg2+1;
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options.length=" . $total_rec2 . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[0].text='-------';\n";

while($reg_funcionario2 = mssql_fetch_array($rs_funcionario2))
   {
       $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[" . $i . "].value='" . $reg_funcionario2[rut] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[" . $i . "].text='" . $reg_funcionario2[nombres] . ' '  . $reg_funcionario2[apellidos] .  "';\n";
      
	}


echo " parent.mainFrame.document.form1.Cbo_Func_Destino.selectedIndex='" . $desf ."';\n";
echo "</script>\n";	
$reg_funcionario2.close;
$rs_funcionario2.close;
}
else
//  ********** COMBO PROCEDENCIA Y DESTINO EXTERNO SACA EL QUE VENIA SELECCIONADO **************

if ($sw=="EE")
{
$rs_dep =mssql_query("select id_dependencia_externa, SUBSTRING(desc_dependencia_externa, 1, 35) AS desc_dependencia_externa, cod_dependencia_externa
from dependencia_externa order by desc_dependencia_externa", $cn);
$Totreg = mssql_num_rows($rs_dep);
$i=0;
echo "<script>\n"; 
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].text='-------------';\n";

while($reg_dep = mssql_fetch_array($rs_dep))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].value='" . $reg_dep[id_dependencia_externa] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].text='" . $reg_dep[desc_dependencia_externa] . "';\n";
	}
echo " parent.mainFrame.document.form1.Cbo_Procedencia.selectedIndex='" . $prod."';\n";	
echo "</script>\n"; 
$reg_dep.close;
$rs_dep.close;

$rs_dep1 =mssql_query("select id_dependencia_externa, SUBSTRING(desc_dependencia_externa, 1, 35) AS desc_dependencia_externa, cod_dependencia_externa
from dependencia_externa order by desc_dependencia_externa", $cn);
$Totreg=0;
$Totreg = mssql_num_rows($rs_dep1);
$i=0;
echo "<script>\n";
$total_rec=0; 
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].text='-------------';\n";

while($reg_dep1 = mssql_fetch_array($rs_dep1))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].value='" . $reg_dep1[id_dependencia_externa] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].text='" . $reg_dep1[desc_dependencia_externa] . "';\n";
	}
echo " parent.mainFrame.document.form1.Cbo_Destinatario.selectedIndex='" . $desf."';\n";	
echo "</script>\n"; 
$reg_dep1.close;
$rs_dep1.close;
}
else
// ********* COMBO PROCEDENCIA EXTERNA Y DESTINO INTERNO SACA EL QUE VENIA SELECCIONADO ****************

if ($sw=="EI")
{
$rs_dep =mssql_query("select id_dependencia_externa, SUBSTRING(desc_dependencia_externa, 1, 35) AS desc_dependencia_externa, cod_dependencia_externa
from dependencia_externa order by desc_dependencia_externa", $cn);
$Totreg = mssql_num_rows($rs_dep);
$i=0;
echo "<script>\n"; 
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].text='-------------';\n";

while($reg_dep = mssql_fetch_array($rs_dep))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].value='" . $reg_dep[id_dependencia_externa] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].text='" . $reg_dep[desc_dependencia_externa] . "';\n";
	}
echo " parent.mainFrame.document.form1.Cbo_Procedencia.selectedIndex='" . $desd."';\n";	
echo "</script>\n"; 
$reg_dep.close;
$rs_dep.close;

$rs_dep1 = mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia
from dependencia order by desc_dependencia", $cn);
$Totreg=0;
$Totreg = mssql_num_rows($rs_dep1);
$i=0;
echo "<script>\n"; 
$total_rec=0;
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].text='-------------';\n";

while($reg_dep1 = mssql_fetch_array($rs_dep1))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].value='" . $reg_dep1[id_dependencia] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].text='" . $reg_dep1[desc_dependencia] . "';\n";
	}

echo " parent.mainFrame.document.form1.Cbo_Destinatario.selectedIndex='" . $prod ."';\n";	
echo "</script>\n";
$reg_dep1.close;
$rs_dep1.close;

$rs_funcionario = mssql_query("SELECT * FROM funcionario where id_dependencia ="  . $prof . "order by nombres,apellidos", $cn);

//$rs_dep1= mssql_query("select id_dependencia_externa from dependencia_externa where cod_dependencia_externa = '" . $nom_dep . "'", $cn);

$Totreg = mssql_num_rows($rs_funcionario);
if($Totreg==0)
{
$i=0;
echo "<script>\n"; 
$total_rec = $Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options.length=" . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[0].text='-------';\n";

while($reg_funcionario = mssql_fetch_array($rs_funcionario))
   {
  $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[" . $i . "].value='" . $reg_funcionario[rut] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[" . $i . "].text='" . $reg_funcionario[nombres] . ' ' . $reg_funcionario[apellidos] . "';\n";
      
	}

if($desf >0){
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.selectedIndex='" . $desf ."';\n";
}
echo "</script>\n";	
$reg_funcionario.close;
$rs_funcionario.close;
}
}
else
// ********* COMBO PROCEDENCIA INTERNA Y DESTINO EXTERNO SACA EL QUE VENIA SELECCIONADO ***********

if ($sw=="IE")
{
$rs_dep =mssql_query("select id_dependencia, SUBSTRING(desc_dependencia, 1, 35) AS desc_dependencia, cod_dependencia
 from dependencia, acceso
where acceso.id_dependencia = dependencia.id_dependencia and acceso.id_usuario =$id_dep",$cn);
$Totreg = mssql_num_rows($rs_dep);
$i=0;
echo "<script>\n"; 
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].text='-------------';\n";

while($reg_dep = mssql_fetch_array($rs_dep))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].value='" . $reg_dep[id_dependencia] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].text='" . $reg_dep[desc_dependencia] . "';\n";
	}
echo " parent.mainFrame.document.form1.Cbo_Procedencia.selectedIndex='" . $prod ."';\n";	
echo "</script>\n"; 
$reg_dep.close;
$rs_dep.close;

$rs_funcionario = mssql_query("SELECT * FROM funcionario where id_dependencia = " . $prof . "order by nombres,apellidos", $cn);
$Totreg = mssql_num_rows($rs_funcionario);
$i=0;
echo "<script>\n";

$total_rec = $Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options.length=" . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[0].text='-------';\n";

while($reg_funcionario = mssql_fetch_array($rs_funcionario))
   {
       $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[" . $i . "].value='" . $reg_funcionario[rut] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[" . $i . "].text='" . $reg_funcionario[nombres] . ' ' . $reg_funcionario[apellidos] .  "';\n";
      	}

if($desf >0){
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.selectedIndex='" . $desf ."';\n";
}
echo "</script>\n";	
$reg_funcionario.close;
$rs_funcionario.close;

$rs_dep1 = mssql_query("select id_dependencia_externa, SUBSTRING(desc_dependencia_externa, 1, 35) AS desc_dependencia_externa, cod_dependencia_externa
from dependencia_externa order by desc_dependencia_externa", $cn);
$Totreg=0;
$Totreg = mssql_num_rows($rs_dep1);
$i=0;
echo "<script>\n"; 
$total_rec=0;
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].text='-------------';\n";

while($reg_dep1 = mssql_fetch_array($rs_dep1))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].value='" . $reg_dep1[id_dependencia_externa] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].text='" . $reg_dep1[desc_dependencia_externa] . "';\n";
	}
echo " parent.mainFrame.document.form1.Cbo_Destinatario.selectedIndex='" . $desd ."';\n";	
echo "</script>\n"; 
$reg_dep1.close;
$rs_dep1.close;
}
else
// ************* LLENA COMBO DESTINATARIO **********************

if ($sw=="F")
{
if(isset($id_dep)) {
$vigente ='N';
//$rs_funcionario = mssql_query("SELECT * FROM funcionario where id_dependencia = " . $id_dep . "order by nombres,apellidos",  $cn);
// para  que muestre solo funcionarios vigentes // 
$rs_funcionario=mssql_query("SELECT * FROM funcionario where vigencia is NULL  and id_dependencia = " . $id_dep .  "order by nombres,apellidos", $cn);
$Totreg = mssql_num_rows($rs_funcionario);
$i=0;
echo "<script>\n";

$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[0].text='-------------';\n";

while($reg_funcionario = mssql_fetch_array($rs_funcionario))
   {
        $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[" . $i . "].value='" . $reg_funcionario[rut] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[" . $i . "].text='" . $reg_funcionario[nombres] .     ' ' . $reg_funcionario[apellidos] .  "';\n";
     
	}
echo "</script>\n";	
$reg_funcionario.close;
$rs_funcionario.close;
}
} 
?>
<script>
document.bgColor="#00EC00";
</script>
