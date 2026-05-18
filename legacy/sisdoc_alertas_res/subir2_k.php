<?php
include("conexion_bd.php");
$nombre=$HTTP_POST_FILES['UploadedFile']['name'];
$direccion = '/var/www/htdocs/subidos/reforma';
$destino="$direccion" . "/" . $nombre;
$posicion = strpos($destino,".doc");
$ubicacion = '/subidos/reforma/';

// buscando el documento en la tabla de documentos //
$ref_query1="Select num_version from documento where (nom_documento)='" . $nombre . "'  and  id_comision = '" . $comision . "' ";
$reg_doc  = mssql_query($ref_query1);
$rs_doc   = mssql_fetch_array($reg_doc);
$tot_nom  = mssql_num_rows($reg_doc);


// buscando el documento en distintas comisiones // 
$ref_query2=" select nom_comision  from documento, comision where (nom_documento)='" . $nombre . "'  and  documento.id_comision = comision.id_comision";
$reg_com  = mssql_query($ref_query2);
$rs_com   = mssql_fetch_array($reg_com);
$tot_com  = mssql_num_rows($reg_com);

$op= 'I';
$fechasistema = date("Y/m/d H:i"); 
echo "<script>\n";
If ($tot_nom ==0)
	{
   /* dejo archivo con version 1  
	$nombrenuevo= substr($destino,0,$posicion) . ".doc";
	$destino=$nombrenuevo;*/
	
	// grabando en la base de datos por primera vez el documento // 

	$num_version='1';
	$documento_query = "exec ingreso_documento '" . $nombre . "','" . $num_version . "','" . 
	                $fechasistema . "','"  . $txtdocumento . "','" . $ubicacion .  "','" . $Cbo_comision .  "','" . $op. "'"; 
	$rs_documento = mssql_query($documento_query,$cn); 
	$reg_docum    = mssql_fetch_array($rs_documento);
	$resultado    = $reg_docum[1];
    	
	if ($resultado <> 0)
	{
	echo "alert('Error en traspaso de archivo');";
	}
	else
	 {
	 // graba documento en directorio
	  if ($tot_com <> 0){
	      echo "alert('Ya existe documento en otra comisión ');";
     	  }
	  else
	    if (file_exists($destino))
		 {
		 }
		else 
	     {copy($UploadedFile,$destino);}
	 }
	 
	}
else
	 {
	
    /*
	//  Esto es  para el caso de agregar nuevas versiones   /// 
	 $ref_query2 ="select max(num_version) as version from documento where nom_documento='" . $nombre . "'";
     $reg_doc2  = mssql_query($ref_query2);
	 $rs_doc2   = mssql_fetch_array($reg_doc2);
     $tot_reg= mssql_num_rows($reg_doc2);
	 $registro=$rs_doc2[version];
	 $max=  $registro + 1;

	 // graba en documento// 
	 $documento_query = "exec ingreso_documento '" . $nombre . "','" . $max . "','" . 
	                $fechasistema . "','"  . $txtdocumento . "','" . $ubicacion . "','" . $Cbo_comision .  "','" . $op. "'"; 
	 $rs_documento = mssql_query($documento_query,$cn); 
	 $reg_docum    = mssql_fetch_array($rs_documento);
	 $resultado    = $reg_docum[1];
     	
	 if ($resultado <> 0)
	 {
	 echo 'alert("Error al traspasar el archivo")';
	 }
	 else
	 {
	 // graba documento en directorio
  	   $nombrenuevo= substr($destino,0,$posicion) .  ".doc";
       $destino=$nombrenuevo;
        copy($UploadedFile,$destino);
	 }*/
     echo "alert('Ya existe archivo con ese nombre');";
     }
echo "</script>\n";

mssql_close($cn);
 echo '<html><body onload="document.form1.submit();">';
 echo '<form name="form1" method="post" action="datos3.php">' . "\n";
 echo '<input type="hidden" name="comision" value="' . 1 . '">' . "\n";
 echo '<input type="hidden" name="rut" value="' . $rut . '">' . "\n";
 echo '</form></body></html>'  . "\n";
?>
