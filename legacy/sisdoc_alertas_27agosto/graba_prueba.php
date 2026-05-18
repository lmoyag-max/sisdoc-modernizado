<?PHP
include("conexion_bd.php");
##--- viene ademas el parametro Totreg que es el total de registro del formulario donde se escogen los documentos 
##--- a responder , y casilla_despacho que es un arreglo que trae los id_seguimiento de cada documento seleccionado 
##-- en  el formulario donde se escogen que documentos se responderan con uno solo 

$fechasistema = date("Y/m/d H:i"); 
$cbo_proc= $val_procedencia;
$cbo_func_proc= $val_funcionario;
$cbo_destino= $val_destino;
$cbo_func_destino= $val_funcionario1;
$td=$tipo_destino;
$flujo=0;
$tp=$tiproc;

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
if ($checkofpartes=="")
	{
	$tipo_despacho ="D";
	}
Else
	{		
	$tipo_despacho ="";
	}	
if ($Original=="") 
	{
	$Original = "N"; 
	}
	
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
$txtexped='N';
$txtexp=0;
$txtdescrip='';
$codibm='';


//graba documento de respuesta //
$documento_query ="exec ingreso_expediente2 '"  . $Cbo_Tipo_Docto . "','" . $Cbo_Estado_Docto . "','" . 
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

if ($reg_doc[0]==0)
{
#-------- Guardar descriptores----------
$vector = split ("@",$arreglo);
$largo=0;
$largo= $vector[0];
$x=1;
$sw_ok=0;
for($x=1;$x <=$largo;$x++)
	{
	$descriptor_query="exec ingreso_descriptor '" . $vector[$x] . "','" . $Id_Documento . "','" . $op . "'";
	$rs_desc = mssql_query($descriptor_query,$cn); 
	$reg_desc = mssql_fetch_array($rs_desc);
	$tot_desc = mssql_num_rows($rs_desc);
	}
// Tramite Normal
		{
		$tramite_query = "exec ingreso_tramite '" . $Cbo_Tipo_Distribucion . "','" . $Cbo_Tipo_Compromiso . "','" .
		$Cbo_Estado_Compromiso . "','" . $xx . "','" . $Id_Documento . "','" . $cbo_proc . "','" .
		$Cbo_Destinatario . "','" . $estado_tramite . "','" . $id_nomina . "','" . $tp . "','" . 
		$tipo_destino . "','" . $Original . "','" . $Cbo_Func_Procedencia . "','" . $Cbo_Func_Destino . "','" .
		$TxtDias . "','" . $fechasistema . "','" . ' ' . "','" . $fechasistema . "','" . $fechasistema . "','" .
		ltrim($TxtObservacion) . "','" . $op . "'";
	
		$rs_tram = mssql_query($tramite_query,$cn); 
		$reg_tram = mssql_fetch_array($rs_tram);
		}


$opc="E";
$comp=0;
$op = 4;
$estado = 4;
#-------- Genera la relación entre el documento antiguo y el que se genera como respuesta ----------
$id_doc=$Id_Documento;

for ($i =0 ; ($i < $Totreg); $i++) 
   { 
	#-------- Modifica el estado del tramite con 4  ----------
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
