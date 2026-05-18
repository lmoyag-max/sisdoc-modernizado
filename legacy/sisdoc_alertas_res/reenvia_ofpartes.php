<?php 
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;

$usua=$cusuario;
$xx=$idusuario;
$iddoc=$iddocum;
$sw_existe =0;
// busca si la dependencia del usuario es de oficina de partes        
$depusuario ="select a.id_usuario,b.id_dependencia from usuario a , funcionario b where b.id_funcionario=a.id_funcionario 
			 and b.vigencia is null and a.id_usuario=" . $xx;
$regdep =mssql_query($depusuario);
$regdep = mssql_fetch_array($regdep);
$dep_func =$regdep[id_dependencia];  
   
// busca accesos para poder derivar desde la opcion derivar  de las busquedas de tramites //
$proc_min = "select id_procedencia,tipo_procedencia  from tramite where  id_documento =" . $iddoc . " and id_seguimiento=" . $idseguim ;
$reg_proc = mssql_query($proc_min);
$reg = mssql_fetch_array($reg_proc);
$proc_min = $reg[id_procedencia];
$tipo_min =$reg[tipo_procedencia];

if ($tipo_min=='I')
{
	$rs_tramite = "select * from tramite where id_documento=" . $iddoc .  " and ".  "id_destino=" . $proc_min . " and tipo_destinatario=".  "'" . $tipo_min . "'" ;
	$reg_estado = mssql_query($rs_tramite);
	$reg_est = mssql_fetch_array($reg_estado);
	$tot = mssql_num_rows($reg_estado);
}
	if ($tot >0)
	{
	echo "<script>\n";
	echo  "alert('Ya existe un trámite hacia su dependencia, debe derivar por Gestionar');";
	echo "</script>\n";
	$sw_existe =1 ;
	$avanzada =0;
	}
	if ($sw_existe==1)
	{
	if (($materiaold) =="0,")
	{
	 $materiaold='';
	}
	//echo '<form name="form1" method="post" action="doc_enc_archivo.php">';
	echo '<html><body onload="javascript:document.form1.submit()">';
	echo '<form name="form1" method="post" action="doc_enc.php">';
	echo '<input type="hidden" name="idusuario"   	  value="' .$idusuario . '">'."\n";
	echo '<input type="hidden" name="cusuario"      	  value="' . $cusuario . '">'."\n";
	echo '<input type="hidden" name="idfuncionario"    	  value="' . $idfuncionario . '">'."\n";
	echo '<input type="hidden" name="xx" 		  value="' . $idusuario . '">'."\n";
	echo '<input type="hidden" name="TxtInterno"    	  value="' . $TxtInterno . '">'."\n";
	echo '<input type="hidden" name="TxtOficial "        	  value="' . $TxtOficial . '">'."\n";
	echo '<input type="hidden" name="TxtExterno "              value="' . $TxtExterno. '">'."\n";
	echo '<input type="hidden" name="arreglo"             	   value="' . $arreglo. '">'."\n";
	echo '<input type="hidden" name="tipo_procedencia"      value="' . $tipo_procedencia . '">'."\n";
	echo '<input type="hidden" name="tipo_destino"             value="' . $tipo_destino . '">'."\n";
	echo '<input type="hidden" name="Cbo_Procedencia"     value="' . $Cbo_Procedencia . '">'."\n";
	echo '<input type="hidden" name="Cbo_Destinatario"      value="' . $CboDestinatario . '">'."\n";
	echo '<input type="hidden" name="Cbo_Tipo_Docto"       value="' . $Cbo_Tipo_Docto . '">'."\n";
	echo '<input type="hidden" name="TxtMateria"                value="' . $materiaold. '">'."\n";
    echo '<input type="hidden" name="Txt_fecha_ini"            value="' . $fechaini. '">'."\n";
	echo '<input type="hidden" name="Txt_fecha_fin"            value="' . $fechafin. '">'."\n";
	echo '<input type="hidden" name="con_archivo"              value="' . 1 . '">'."\n";
	echo '<input type="hidden" name="si_avanza"                 value="' . $avanza. '">'."\n";
	echo '<input type="hidden" name="avanzada"                  value="' . $avanzada. '">'."\n";
    	echo '<input type="hidden" name=" $dependencia_usuario" value="' . $mi_dependencia . '">'."\n";
	echo '<input type="hidden" name=" $sw_cons"                value="' . $sw_cons . '">'."\n";
//	echo '<input type="hidden" name=" $con_archivo"            value="' . $con_archivo . '">'."\n";
	}
	if ($sw_existe ==0)
	{
      if ($dep_func==6)  // busca usuario con dependencia oficina de partes 
		{
		echo '<html><body onload="javascript:document.form1.submit()">';
	   	echo '<form name="form1" method="post" action="cambia_estado3_k3_ofpartes.php">';
	   	echo '<input type="hidden" name="idusuario"     	value="' . $idusuario . '">'."\n";
		echo '<input type="hidden" name="cusuario"      	value="' . $cusuario . '">'."\n";
		echo '<input type="hidden" name="iddocum"    	value="' . $iddocum . '">'."\n";
		echo '<input type="hidden" name="idseguim"    	value="' . $idseguim . '">'."\n";
		echo '<input type="hidden" name="idfuncionario" 	value="' . $idfuncionario . '">'."\n";
		echo '<input type="hidden" name="fecha_ini"      	value="' . $fechaini. '">'."\n";
		echo '<input type="hidden" name="fecha_fin"      	value="' . $fechafin. '">'."\n";
		echo '<input type="hidden" name="Cbo_Tipo_Docto"    value="' . $Cbo_Tipo_Docto . '">'."\n";
		echo '<input type="hidden" name="TxtInterno"    	value="' . $TxtInterno . '">'."\n";
		echo '<input type="hidden" name="TxtOficial"           	value="' . $TxtOficial . '">'."\n";
		echo '<input type="hidden" name="TxtExterno"            value="' . $TxtExterno . '">'."\n";
		echo '<input type="hidden" name="tipo_procedencia"    value="' . $tipo_procedencia . '">'."\n";
		echo '<input type="hidden" name="tipo_destino"          	 value="' . $tipo_destino . '">'."\n";
		echo '<input type="hidden" name="Cbo_Procedencia"   value="' . $Cbo_Procedencia . '">'."\n";
		echo '<input type="hidden" name="CboDestinatario"     value="' . $CboDestinatario . '">'."\n";
		echo '<input type="hidden" name="TxtMateria"             value="' . $materiaold. '">'."\n";
    	echo '<input type="hidden" name="avanzada"     	 value="' . $avanzada. '">'."\n";
		}
       else 
		{   	   	
		echo '<html><body onload="javascript:document.form1.submit()">';
	   	echo '<form name="form1" method="post" action="cambia_estado3_k3.php">';
	   	echo '<input type="hidden" name="idusuario"     	value="' . $idusuario . '">'."\n";
		echo '<input type="hidden" name="cusuario"      	value="' . $cusuario . '">'."\n";
		echo '<input type="hidden" name="iddocum"    	value="' . $iddocum . '">'."\n";
		echo '<input type="hidden" name="idseguim"    	value="' . $idseguim . '">'."\n";
		echo '<input type="hidden" name="idfuncionario" 	value="' . $idfuncionario . '">'."\n";
		echo '<input type="hidden" name="fecha_ini"      	value="' . $fechaini. '">'."\n";
		echo '<input type="hidden" name="fecha_fin"      	value="' . $fechafin. '">'."\n";
		echo '<input type="hidden" name="Cbo_Tipo_Docto"    value="' . $Cbo_Tipo_Docto . '">'."\n";
		echo '<input type="hidden" name="TxtInterno"    	value="' . $TxtInterno . '">'."\n";
		echo '<input type="hidden" name="TxtOficial"           	value="' . $TxtOficial . '">'."\n";
		echo '<input type="hidden" name="TxtExterno"            value="' . $TxtExterno . '">'."\n";
		echo '<input type="hidden" name="tipo_procedencia"    value="' . $tipo_procedencia . '">'."\n";
		echo '<input type="hidden" name="tipo_destino"          	 value="' . $tipo_destino . '">'."\n";
		echo '<input type="hidden" name="Cbo_Procedencia"   value="' . $Cbo_Procedencia . '">'."\n";
		echo '<input type="hidden" name="CboDestinatario"     value="' . $CboDestinatario . '">'."\n";
		echo '<input type="hidden" name="TxtMateria"             value="' . $materiaold. '">'."\n";
	    echo '<input type="hidden" name="avanzada"     	 value="' . $avanzada. '">'."\n";
		}
		
	}
	
?>
	