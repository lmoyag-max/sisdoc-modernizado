<?PHP
include("conexion_bd.php");
##--- viene ademas el parametro Totreg que es el total de registro del formulario donde se escogen los documentos 
##--- a responder , y casilla_despacho que es un arreglo que trae los id_seguimiento de cada documento seleccionado 
##-- en  el formulario donde se escogen que documentos se responderan con uno solo 
// variable $Totreg toma el total de los registros que se van a  responder 
// variable $Tot    toma el total de los registros  de los documentos que se pueden asociar  
$flujo=0;
$xx=$idusuario;
$c_usuario=$cusuario;
$id_funcionario=$idfuncionario;
$id_doc =$radiodocumento;
$opc="E";
$comp=0;
$op = 4;
$estado = 4;
$fechasistema = date("Y/m/d H:i"); 
//echo "Total " . $Totreg . "<br>";
//echo "Tot   " . $Tot  . "<br>";
#-------- Genera la relación entre el documento antiguo y el que se genera como respuesta ----------
//echo "documento " . $id_doc . "<br>" ;
for ($i =0 ; ($i < $Totreg); $i++) 
   { 
	#-------- Modifica el estado del tramite con 4  ----------
//	  echo "casilla " . $casilla_despacho[$i];
$tram_query = "exec mod_estado_tramite '" . $casilla_despacho[$i] . "','" . $fechasistema . "','" .$estado . "','" . $opc . "','" .  $op .  "','" . $comp . "'";
	$rs_tram.close;
	$reg_tram.close;
	$rs_tram = mssql_query($tram_query,$cn); 
	$reg_tram = mssql_fetch_array($rs_tram);
     #--relacion de documento---#
 	$rs_tram=mssql_query("select id_documento from tramite where  id_seguimiento ='" . $casilla_despacho[$i] . "'", $cn);
    while ($reg = mssql_fetch_array($rs_tram))
	 {
      	$hijo_query = "exec relacion_doc '" . $reg[id_documento] . "','" . $id_doc . "'";
		$rs_hijo = mssql_query($hijo_query,$cn); 
		$reg_hijo = mssql_fetch_array($rs_hijo);
		if ($reg_desc[0]!=0)
		   {	$flujo=1;	}
	 }  
   
	}
if ($flujo==0){
 if ($responde==0 )
	{
	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="respuesta_multiple.php">' . "\n";
	}
	
echo '<input type="hidden" name="idusuario" value="' . $xx . '">' . "\n";
echo '<input type="hidden" name="cusuario" value="' . $cusuario . '">' . "\n";
echo '<input type="hidden" name="idfuncionario" value="' . $idfuncionario . '">' . "\n";
echo '<input type="hidden" name="flujook" value="' . $flujo . '">' . "\n";
echo '<input type="hidden" name="val_procedencia" value="' . 0 . '">' . "\n";
echo '<input type="hidden" name="val_destino" value="' . 0 . '">' . "\n";
echo '<input type="hidden" name="val_funcionario" value="' . 0 . '">' . "\n";
echo '<input type="hidden" name="val_funcionario1" value="' . 0 . '">' . "\n";
echo '<input type="hidden" name="iddocum" value="' . $iddocum . '">' . "\n";
echo '<input type="hidden" name="idseguim" value="' . $idseguim . '">' . "\n";
echo '<input type="hidden" name="num_int" value="' . $num_interno . '">' . "\n";
echo '<input type="hidden" name="accion" value="' . $acc . '">' . "\n";
echo '</form></body></html>' . "\n";
}

mssql_close($cn);
?>
