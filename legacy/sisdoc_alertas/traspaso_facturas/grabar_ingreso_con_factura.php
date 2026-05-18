<?PHP
include("conexion_bd.php");
$fechasistema = date("Y/m/d H:i"); 
$cbo_proc= $val_procedencia;
$cbo_func_proc= $val_funcionario;
$cbo_destino= $val_destino;
$cbo_func_destino= $val_funcionario1;
$td=$tipo_destino;
$tp=$tipo_procedencia; 
$flujo=0;
//$txtnomina=$txtnomina;
$id_doc=$iddocum;
$id_tra=$idseguim;
$Cbo_Estado_Compromiso=2;
if($Cbo_Func_Procedencia==0)
{
 $Cbo_Func_Procedencia="";
}
if($Cbo_Func_Destino==0)
{
 $Cbo_Func_Destino="";
}

$acc=$accion;
//echo "*** usu " . $cusuario . "** acc " . $accion . "**titulo " . $nombre_p . "** proc " . $val_procedencia . "** des" . $val_destino . "** doc " . $iddocum . "** seg" . $idseguim ;
if ($checkofpartes=="")
	{
	$tipo_despacho ="D";
	}
Else
	{		
	$tipo_despacho ="";
	}	
if ($Original=="") {
	$Original = "N"; }
	
$xx=$idusuario;
$c_usuario=$cusuario;
$id_funcionario=$idfuncionario;
$dia = substr($Txt_fecha_doc,0,2);
$mes = substr($Txt_fecha_doc,3,2);
$año = substr($Txt_fecha_doc,6,4);
$Fecha_Docto = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));
if ($Txt_fecha_inv <> "")
{
$dia = substr($Txt_fecha_inv,0,2);
$mes = substr($Txt_fecha_inv,3,2);
$año = substr($Txt_fecha_inv,6,4);
$Fecha_Inv = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));
}


$op="I";
$flujo=0;

#-------- Guarda los datos en tabla de Documento ----------
/*$documento_query = "exec ingreso_documentos '" . $Cbo_Tipo_Docto . "','" . $Cbo_Estado_Docto . "','" . 
$xx . "','"  . $TxtInterno . "','" . $TxtOficial . "','" . $TxtExterno . "','" . $Original . "','" . 
$Cbo_Medio . "','" . $tipo_despacho. "','" . $resuelto . "','" . $Fecha_Docto . "','" . 
$fechasistema . "','" . $fechasistema . "','" . $Fecha_Inv . "','" .
ltrim($TxtMateria) . "','" . $op . "'"; 
*/
// graba con numero interno sacado de numero_interno1// 
// version  para que considere tabla de numero_interno1 y rescate numero interno


$txtexped='N';
$txtexp=0;
$txtdescrip='';
$codibm='';
// para que no escriba cremillas //
$TxtMateria= str_replace("\'","",$TxtMateria);
$TxtMateria =str_replace(",",", ", $TxtMateria);


// En caso que entre nuevamente a grabar pero de la 1era pantalla no hay problema //
/// buscar los tramites que se han hecho en  caso que sean para ambos destinos ///
if ($flag==1)
{
$consulta=" select * from tramite where id_usuario=$xx  and tipo_destinatario =". "'$tipo_destino'" .  " and tipo_procedencia =". "'$tipo_procedencia'" . " and id_documento = " . $iddocum . " and  id_procedencia = " . $Cbo_Procedencia . " and id_estado_tramite =" . 1   ;
//echo $consulta ;
$rs_doc=$consulta;
$rs_documento=mssql_query($rs_doc);
$Totreg = mssql_num_rows($rs_documento);
}
if ($Totreg <>0)
{
	echo '<html><body onload="document.form1.submit();">';
	//echo '<form name="form1" method="post" action="derivar_con_docto.php">' . "\n";
	echo '<form name="form1" method="post" action="multi_recib.php">' . "\n";
}


else
{
//echo "numero oficial" . $TxtOficial . " numero externo " . $TxtExterno;
/*$documento_query ="exec ingreso_expediente2 '"  . $Cbo_Tipo_Docto . "','" . $Cbo_Estado_Docto . "','" . 
$xx . "','"  . $TxtInterno . "','" . $TxtOficial2 . "','" . $TxtExterno2 . "','" . $Original . "','" .
$Cbo_Medio . "','" . $tipo_despacho. "','" . $resuelto . "','" . $Fecha_Docto . "','" .
$fechasistema . "','" . $fechasistema . "','" .  $Fecha_Inv . "','" .
ltrim($TxtMateria) . "','" . $cbo_proc  .  "','" . $tp . "','" . $txtexped . "','" .
$txtexp . "','" . $txtdescrip. "','" . $codibm . "','" . $op . "'";
*/
$documento_query ="exec ingreso_expediente2_nuevo '"  . $Cbo_Tipo_Docto . "','" . $Cbo_Estado_Docto . "','" . 
$xx . "','"  . $TxtInterno . "','" . $TxtOficial2 . "','" . $TxtExterno2 . "','" . $Original . "','" .
$Cbo_Medio . "','" . $tipo_despacho. "','" . $resuelto . "','" . $Fecha_Docto . "','" .
$fechasistema . "','" . $fechasistema . "','" .  $Fecha_Inv . "','" .
ltrim($TxtMateria) . "','" . $cbo_proc  .  "','" . $tp . "','" . $txtexped . "','" .
$txtexp . "','" . $txtdescrip. "','" . $codibm . "','" . $op . "'";

$rs_doc = mssql_query($documento_query,$cn); 
$reg_doc = mssql_fetch_array($rs_doc);
$tot_doc = mssql_num_rows($rs_doc);
$Id_Documento=$reg_doc[1];
$num_interno=$reg_doc[2];
if ($reg_doc[0]==0){
#-------- Guardar descriptores----------
$vector = split ("@",$arreglo);
$largo=0;
$largo= $vector[0];
$x=1;
$sw_ok=0;
for($x=1;$x <=$largo;$x++)

{$descriptor_query="exec ingreso_descriptor '" . $vector[$x] . "','" . $Id_Documento . "','" . $op . "'";

$rs_desc = mssql_query($descriptor_query,$cn); 
$reg_desc = mssql_fetch_array($rs_desc);
$tot_desc = mssql_num_rows($rs_desc);
if ($reg_desc[0]!=0){
	$sw_ok=$sw_ok+1;}
}

if ($sw_ok==0){

$id_nomina=0;
if($acc==2)
{
$estado=5;
}
else
{
$estado=4;
}
#-------- Genera un tramite al responder con documento ----------
/*$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
$Cbo_Estado_Compromiso . "','" . $xx . "','" . $Id_Documento . "','" . $cbo_proc . "','" .
$cbo_proc . "','" . $estado_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
$tipo_destino . "','" . $Original . "','" . $cbo_func_proc . "','" . $cbo_func_proc . "','" .
$TxtDias . "','" . ' ' .  "','"  . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
ltrim($TxtObservacion) . "','" . $op . "'";
*/


#-------- Genera un tramite al derivar o responder con documento ----------
$vectorint = split ("@",$arregloint);
$largoint=0;
$largoint= $vectorint[0];
$vectorext = split ("@",$arregloext);
$largoext=0;
$largoext= $vectorext[0];
// para que no escriba comillas en observacion del tramite  //
$TxtObservacion= str_replace("\'","",$TxtObservacion);
$TxtObservacion=str_replace(",",", ", $TxtObservacion);

// Multi destino Interno
if ($largoint!=0)
{

$x=1;
$sw_ok=0;
for($x=1;$x <=$largoint;$x++)

{
	
$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
$Cbo_Estado_Compromiso . "','" . $xx . "','" . $Id_Documento . "','" . $cbo_proc . "','" .
$vectorint[$x] . "','" . $estado_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
$tipo_destino . "','" . $Original . "','" . $cbo_func_proc . "','" . $Cbo_Func_Destino . "','" .
$TxtDias . "','" . ' ' .  "','"  . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
ltrim($TxtObservacion) . "','" . $op . "'";
$rs_tram = mssql_query($tramite_query,$cn); 
$reg_tram = mssql_fetch_array($rs_tram);}
}
else
// Multi destino Externo
if ($largoext!=0)
{
$x=1;
$sw_ok=0;

for($x=1;$x <=$largoext;$x++)

{
$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
$Cbo_Estado_Compromiso . "','" . $xx . "','" . $Id_Documento . "','" . $cbo_proc . "','" .
$vectorext[$x] . "','" . $estado_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
$tipo_destino . "','" . $Original . "','" . $cbo_func_proc . "','" . $Cbo_Func_Destino . "','" .
$TxtDias . "','" . ' ' .  "','"  . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
ltrim($TxtObservacion) . "','" . $op . "'";
$rs_tram = mssql_query($tramite_query,$cn); 
$reg_tram = mssql_fetch_array($rs_tram);}
}
else
{
// Destino Normal
$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
$Cbo_Estado_Compromiso . "','" . $xx . "','" . $Id_Documento . "','" . $cbo_proc . "','" .
$Cbo_Destinatario . "','" . $estado_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
$tipo_destino . "','" . $Original . "','" . $cbo_func_proc . "','" . $Cbo_Func_Destino . "','" .
$TxtDias . "','" . ' ' .  "','"  . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
ltrim($TxtObservacion) . "','" . $op . "'";
$rs_tram = mssql_query($tramite_query,$cn); 
$reg_tram = mssql_fetch_array($rs_tram);

}
$tot_tram = mssql_num_rows($rs_tram);

}

$opc="E";
$comp=0;
$op = 1;
#-------- Modifica el estado del tramite con 4 si es derivación, con 5 si es respuesta ----------
$tram_query = "exec mod_estado_tramite '" . $id_tra . "','" . $fechasistema . "','" .
$estado . "','" . $opc . "','" .  $op .  "','" . $comp . "'";
$rs_tram.close;
$reg_tram.close;
$rs_tram = mssql_query($tram_query,$cn); 
$reg_tram = mssql_fetch_array($rs_tram);



#-------- Cierra el Documento  ----------

if($acc==2)
{
	$doc_query = "exec cierra_documento '" . $id_doc .  "'" ;
	$rs_doc = mssql_query($doc_query,$cn); 
}


#-------- Genera la relación entre el documento antiguo y el que se genera como respuesta ----------
$hijo_query = "exec relacion_doc '" . $id_doc . "','" . $Id_Documento . "'";
$rs_hijo = mssql_query($hijo_query,$cn); 
$reg_hijo = mssql_fetch_array($rs_hijo);


#-------derivacion que incluye asociar las facturas -------
//////// grabando en factura el tramite que  viene del documento ingresado

// buscando documento anterior para ver la factura sociada de antes y poder grabar el detalle en la misma factura 
$busca_doc1="select id_documento, id_factura from relacion_documento_factura where  id_documento="  . $id_doc;
$r_busca_doc =mssql_query($busca_doc1,$cn);
$reg_d=mssql_fetch_array($r_busca_doc);

$xfactura= $reg_d[1];
// buscando tipo de documento en tabla 
   $tipo_d="select c.desc_tipo_documento,a.num_interno from  documento  a ,tipo_documento c where  a.id_tipo_documento= c.id_tipo_documento and  id_documento=" . $Id_Documento;
   $r_tipo_d=mssql_query($tipo_d,$cn);
   $reg_tipo = mssql_fetch_array($r_tipo_d);
   		 
        // agregando tramite factura nueva 
		$op='I';
		$id_nomina =0;
		if ($TxtObservacion=="")
		   {     $TxtObservacion = "Se adjunta con ". $reg_tipo[desc_tipo_documento] .  "  N° " . $reg_tipo[num_interno] . ", nómina ". $id_nomina; }
        else 
		      {  $TxtObservacion = "Se adjunta con ". $reg_tipo[desc_tipo_documento] .  "  N° " . $reg_tipo[num_interno] . ", nómina ". $id_nomina . " , ". $TxtObservacion  ;} 
        
	    
		 $tramite_fact_query= "exec ingreso_detalle_factura '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
		$Cbo_Estado_Compromiso . "','" . $xx . "','" . $xfactura . "','" . $cbo_proc . "','" .
		$Cbo_Destinatario . "','" . $estado_tramite . "','" . $id_nomina . "','" . $tipo_procedencia . "','" . 
		$tipo_destino . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
		$TxtDias . "','" . '' . "','" . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
		ltrim($TxtObservacion) . "','" . $op . "'";
	    
		$rs_tram_fact=mssql_query($tramite_fact_query,$cn); 
		 $busca_rel_doc_fact="select id_factura from relacion_documento_factura  where id_documento= " . $id_doc ." order by id_documento";
		 $r_busca_rel=mssql_query($busca_rel_doc_fact);
		 $reg_busca=mssql_fetch_array($r_busca_rel);
		 	
		 // cambiando estado al tramite anterior , el cual se toma para generar refrendacion 
		 $busca_doc ="select id_documento,id_procedencia,id_destino from tramite where id_seguimiento = " . $id_tra ;
		  $doc=mssql_query($busca_doc,$cn);
		  $doc_reg= mssql_fetch_array($doc);
		  $doc =$doc_reg[0];
		  $proc =$doc_reg[1];
		  $dest=$doc_reg[2];
		$origen = $proc;
   		 $estado_tramite = 4;    // valor a cambiar en estado 
		 $op=1 ;    // valor que viene el estado 
		$doc_query="exec mod_estado_det_factura '".$reg_busca[id_factura]  ."','". $doc."','". $fechasistema ."','" .$origen ."','".	$xx . "','".$estado_tramite .  "','".$dest. "','". $op . "'";
		 $r_busca_doc=mssql_query($doc_query,$cn);
		
		//// deja relacionado documento con factura  para poder  sacar consultas iddocum es el numero de factura 
	     $relacion_factura_documento = "exec relacion_factura_documento '" . $Id_Documento ."','" . $iddocum . "'";
		 $rs_rel_fac_doc = mssql_query($relacion_factura_documento,$cn); 
         $rs_relac_fact_doc =mssql_fetch_array($rs_rel_fac_doc);
		 



////////////////////////////

if ($reg_desc[0]!=0){
	$flujo=1;
	}
}
?>
<SCRIPT  language="JavaScript">
<!--
var numint= <?php echo $num_interno; ?>;  

alert("El Documento ha sido grabado con el Nro Interno : " + numint);

//-->
</script>

<?php
if ($flujo==0){
 if ($responde==1 )
	{
	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="responder_con_docto.php">' . "\n";
	}	
else
	{
	    echo '<html><body onload="document.form1.submit();">';
	//echo '<form name="form1" method="post" action="derivar_con_docto.php">' . "\n";
     	echo '<form name="form1" method="post" action="multi_recib.php">' . "\n";
	//echo '<form name="form1" method="post" action="multi_recib_prueba.php">' . "\n";
	}

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
echo '<input type="hidden" name="txtnomina" value="' . $txtnomina . '">' . "\n";
echo '<input type="hidden" name="txtagnor" value="' . $txtagnor . '">' . "\n";
echo '<input type="hidden" name="flag" value="' . $flag . '">'  . "\n";
echo '</form></body></html>' . "\n";

}

mssql_close($cn);
?>
