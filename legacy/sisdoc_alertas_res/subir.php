<?php
include("conexion_bd.php");
$idseguim=$idseguim;
//$host = 'http://sisdoc.minsal.cl';
$host ='http://163.247.51.38';
//$host ='http://sidoc.minsal.gov.cl';
$nombre=$HTTP_POST_FILES['UploadedFile']['name'];
//$direccion = '/var/www/htdocs/subidos/sisdoc';
$direccion = '/opt/lampp/htdocs/subidos/sisdoc';

$destino_ruta="$direccion" . "/" . $nombre;
$ruta = '/subidos/sisdoc/';
// buscando el documento en la tabla archivo digital //
//$ref_query1="Select archivo from archivo_digital where archivo='" . $nombre . "'";
$ref_query1="Select archivo from archivo_digital where archivo like  '%" . trim($nombre) . "%'";
$reg_doc  = mssql_query($ref_query1);
$rs_doc   = mssql_fetch_array($reg_doc);
$tot_nom  = mssql_num_rows($reg_doc);
$fechasistema = date("Y/m/d H:i"); 
echo "<script>\n";
$sw=0;

If ($tot_nom ==0)
	{

	// grabando en archivo digital 
    $archivo=$nombre;	
	//$documento_query = "exec ingreso_archivo '" . $id_documento . "','" . $host . "','" . $ruta . "','" . 
	 // $archivo . "','" . $fechasistema . "'";
	$documento_query = "exec ingreso_scanner '" . $id_documento . "','" . $host . "','" . $ruta . "','" .  $archivo . "','" . $fechasistema . "'";
	$rs_documento = mssql_query($documento_query,$cn); 
	$reg_docum    = mssql_fetch_array($rs_documento);
	$resultado    = $reg_docum[0];
    
	if ($resultado <> 0)
	  {
    	echo "alert('Error en traspaso de archivo');";
	    $sw= 1;
      }
	}
// graba documento en directorio
else
  { 
  echo "alert('Archivo ya esta asociado a un documento ');";
  $sw=1;
  }
 
if (file_exists($destino_ruta))
	 {
	 echo "alert('Archivo ya  existe en directorio');";
	 }
else 
    if ($sw==0)
     {
	   copy($UploadedFile,$destino_ruta);
	 }
	 
if ($sw==0)
{
  echo "alert('El documento fue ingresado con exito ');";
}
echo "</script>\n";

mssql_close($cn);
//echo "nombre " . $nombre . "resultado   " . $resultado . "sw   " . $sw . "destino_ruta   " . $destino_ruta ;
echo '<html><body onload="document.form1.submit();">';
echo '<form  name="form1"  method="post"     action="asociar_docto_scaneado.php">'."\n";
echo '<input type="hidden" name="idusuario"     	value="' .$idusuario . '">'."\n";
echo '<input type="hidden" name="cusuario"      	value="' .$cusuario .   '">'."\n";
echo '<input type="hidden" name="idfuncionario" 	value="' .$idfuncionario . '">'. "\n";

echo '<input type="hidden" name="iddocum"           value="'. $id_documento .'">' . "\n";
echo '<input type="hidden" name="idseguim"          value="' .$idseguim .'">' . "\n";

echo '<input type="hidden" name="Txt_fecha_fin"   	value="'. $Txt_fecha_fin .'">'. "\n";
echo '<input type="hidden" name="Txt_fecha_ini"   	value="'. $Txt_fecha_ini .'">'. "\n";
echo '<input type="hidden" name="Cbo_Tipo_Docto"  	value="'. $Cbo_Tipo_Docto .'">'. "\n";
echo '<input type="hidden" name="TxtInterno"      	value="'. $TxtInterno .'">'. "\n";
echo '<input type="hidden" name="TxtExterno"      	value="'. $TxtExterno .'">'. "\n";
echo '<input type="hidden" name="TxtOficial"      	value="'. $TxtOficial .'">'. "\n";
echo '<input type="hidden" name="cbo_esc_dest"    	value="'. $cbo_esc_dest .'">'. "\n";
echo '<input type="hidden" name="destino"         	value="'. $destino .'">'. "\n";

echo '</form></body></html>'  . "\n";
?>
