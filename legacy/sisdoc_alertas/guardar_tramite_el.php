<?PHP
include("conexion_bd.php");
// Programa que elimina los documentos que estan generados
$fechasistema = date("Y/m/d H:i"); 
$id_usu = $idusuario;
$id_doc = $iddocum;
$id_seg = $idseguim;
$tipo_doc=$Cbo_Tipo_Docto;
$xx=$idusuario;
$c_usuario=$cusuario;
$cons=sw_cons;
$id_funcionario=$idfuncionario;
$dia = substr($Txt_fecha_Doc,0,2);
$mes = substr($Txt_fecha_Doc,3,2);
$año = substr($Txt_fecha_Doc,6,4);
$Fecha_Docto = date("Y/m/d H:i", mktime(0, 0,0, $mes, $dia, $año));
$fechaini=$Txt_fecha_ini;
$fechafin=$Txt_fecha_fin;
$TxtInterno=$TxtInterno ;
$TxtOficial=$TxtOficial;
$TxtExterno= $TxtExterno ;
$TxtMateria=ltrim($TxtMateria) ;
$Cbo_Destinatario=$Cbo_Destinatario ;  
$Cbo_Procedencia=$Cbo_Procedencia ;
$tipo_procedencia=$tipo_procedencia ; 
$tipo_destino=$tipo_destino ; 
$rut_procedencia=$rut_procedencia; 
$rut_destino=$rut_destino; 
$obs  =$obs;
$fecha_sis1 =$fecha_sis1; 
$fecha_sis2 =$fecha_sis2 ; 
$sw_el=$sw_elim;

$sss=0;    // para controlar si pasa por la condicion cuando el tramite esta entremedio o al final , entremedio  cambia a  1 
      //principal 
	    $principal ="select  id_seguimiento, id_estado_tramite from tramite where id_documento =" . $id_doc;  
		$principal_r= mssql_query($principal);
       // cuenta los tramites que tiene el documento 
       $contar ="select id_documento     from tramite where id_documento =". $id_doc;
	   // echo "contar". $contar ;
	   $contar_r =mssql_query($contar);
	   $total =mssql_num_rows($contar_r);
	   // saca el  minimo de los tramites 
	    $minimo_c="select min(id_seguimiento)  from tramite where id_documento=". $id_doc;
		$minimo_r=mssql_query($minimo_c);
		$minimo_reg =mssql_fetch_array($minimo_r);
		$minimo =$minimo_reg[0];


if($sw_el==0)
{
   $grabado=0;
   
   //para borrar el ultimo tramite 
   
   $query_doc="select max(id_seguimiento) as doc from tramite where id_documento =" . $id_doc;
   $rs_doc= mssql_query($query_doc, $cn);
   $filas = mssql_num_rows($rs_doc);
   $reg_doc= mssql_fetch_array($rs_doc);
   if($reg_doc["doc"]==$id_seg)
   {
	
		// Busca la cantidad de tramites que tiene el documento
		$rs_tram="exec busca_tramite_el '" . $id_seg . "'";
		$rs_tramite=mssql_query($rs_tram);  
		$reg_tram = mssql_fetch_array($rs_tramite);
		$Totreg = mssql_num_rows($rs_tramite);
	
		if ($reg_tram["id_estado_tramite"]==1 || $reg_tram["id_estado_tramite"]==2) 
		{
			$grabado =0;
			
		}
		else
		{
			$grabado=1;
				
		}
		
  }
  else 
     
     if(($reg_doc["doc"]<>$id_seg) && ($id_seg <> $minimo ))
    { 
  // cambios para considerar borrar tramites que están entremedio 23/03/2010
	   // saca el  maximo de los tramites 
	    
	    $maximo_c="select max(id_seguimiento)  from tramite where id_documento=". $id_doc;
		$maximo_r=mssql_query($maximo_c);
		$maximo_reg =mssql_fetch_array($maximo_r);
		$maximo =$maximo_reg[0];
		 if ($total >2)
		   {
		   while ($regprincipal=mssql_fetch_array($principal_r))
		    {    if ($minimo <> $regprincipal[id_seguimiento]  && $maximo <> $regprincipal[id_seguimiento])
			 {  
			    if ($regprincipal[id_estado_tramite] ==1 && $regprincipal[id_seguimiento]==$id_seg)
				     {  
                       // buscando el tramite para obtener  que destino debe tener el anterior para cambiar el estado de este tramite 
					    $buscareg ="select id_procedencia, tipo_procedencia  from tramite where id_seguimiento=". $regprincipal[id_seguimiento];
						$busca=mssql_query($buscareg);
						$busca_reg=mssql_fetch_array($busca);						
					   // verifica si hay tramites anteriores con  destino al departametno del usuario y que este en estado  desrivado , 
					   // debe quedar como recepcionado					    
                        $s='I'; 
					    $a= "select max(id_seguimiento) from tramite where id_documento=". $id_doc . " and id_seguimiento  between ". $minimo . " and " .  $regprincipal[id_seguimiento] . " and id_destino =". $busca_reg[id_procedencia]. " and tipo_destinatario='". $s . "' and id_estado_tramite =4 ";
						 $a_r =mssql_query($a);
						$reg_a_r =mssql_fetch_array($a_r);
	                    // cuenta si  hay tramites de la misma procedencia despues del tramite a borrar , para cambiar el estado del anterior  
						//  en caso que eexista algun tramite con procedencia igual a la procedencia del  tramite que se esta borrando
						$bs= "select count(*) as toseg from tramite where id_procedencia =". $busca_reg[id_procedencia] . " and tipo_procedencia ='". $s . "' and id_documento=". $id_doc . " and id_seguimiento > " . $regprincipal[id_seguimiento];
						 $b_r =mssql_query($bs);						
						$b_t=mssql_fetch_array($b_r);
						$ret_doc=0;
						if ($b_t[0]	 < 1)
						   {   // cambia estado de tramite anterior 
      						$cambia ="update tramite set id_estado_tramite =3 where id_seguimiento = ". $reg_a_r[0];
							$cambia_r =mssql_query($cambia);
							// elimina tramite de ahora 
                           }
				         if ($ret_doc==0)
							   {
								  $tipo_elim ='T';
								  $op="I";
										$flujo=0;
										#-------- Guarda los datos en tabla de Respaldo_Documento ----------
				
										// Busca los datos del documento y tramite para llenar la tabla respaldo_documento
										$datos_query = "exec busca_datos '" . $id_seg . "'" ; 
										$rs_datos = mssql_query($datos_query,$cn); 
										$reg_datos = mssql_fetch_array($rs_datos);
										$Totdatos = mssql_num_rows($rs_datos);
	
										// Guarda los datos en la tabla respaldo_documento
										$documento_query = "exec ing_respaldo_docto '" . $reg_datos["id_documento"] . "','" . $reg_datos["id_tipo_documento"] . "','" . 
										$reg_datos["id_usuario"] . "','"  . $reg_datos["num_interno"] . "','" . $reg_datos["num_oficial"] . "','" .
										$reg_datos["num_externo"] . "','" . $reg_datos["fecha_documento"] . "','" .  $reg_datos["fecha_sistema"] . "','" .
										ltrim($reg_datos["materia"]) . "','" . $reg_datos["id_seguimiento"] . "','" . $reg_datos["id_procedencia"] . "','" .
										$reg_datos["id_destino"]  . "','" . $reg_datos["tipo_procedencia"] . "','" .
										$reg_datos["tipo_destinatario"] . "','" . $reg_datos["rut_procedencia"]  . "','" . 
										$reg_datos["rut_destino"] . "','" . $reg_datos["fecha2"] . "','" . 
										ltrim($reg_datos["observaciones"]) . "','" .
										$tipo_elim . "','" . $fechasistema . "'";
	 									$reg_datos.close;
										$rs_datos.close;
										$rs_doc = mssql_query($documento_query,$cn);
                                        $eliminacion_query = "exec elimina_documentos_tramites '" . $id_doc . "','" . $id_seg . "','" . $tipo_elim .  "'"; 
								 		$rs_elim = mssql_query($eliminacion_query,$cn); 
								        $reg_elim = mssql_fetch_array($rs_elim);
   								        $ret_elim=$reg_elim["Ret"];
		    	 						  if($ret_elim==0)
											{
											 $sw_grabado =0;
											 $sss=1;
											}
									}
					 }// fin de    if ($regprincipal[id_estado_tramite] ==1)
				 else 
				 { $grabado=1 ;}
				 
			 }//  fin de if ($minimo <> $regprincipal[id_seguimiento]  && $maximo <> $regprincipal[id_seguimiento])
		    } // fin de while ($regprincipal=mssql_fetch_array($principal_r))
	       } // fin de  total >2
   }// if(($reg_doc["doc"]<>$id_seg) && ($id_seg <> $minimo ))
   //fin cambios 
 //  } 
  //
	
	else
	{ 
	$grabado=1;
	}
// graba datos como respaldo
	//if($grabado==0)
	
	if(($sss==0) && ($grabado==0))
	{ 
	$op="I";
	$flujo=0;
	$tipo_elim="T";
	$sw_grabado = 0;
	
	
	#-------- Guarda los datos en tabla de Respaldo_Documento ----------
	
	// Busca los datos del documento y tramite para llenar la tabla respaldo_documento
	$datos_query = "exec busca_datos '" . $id_seg . "'" ; 
	$rs_datos = mssql_query($datos_query,$cn); 
	$reg_datos = mssql_fetch_array($rs_datos);
	$Totdatos = mssql_num_rows($rs_datos);
	
	// Guarda los datos en la tabla respaldo_documento
	$documento_query = "exec ing_respaldo_docto '" . $reg_datos["id_documento"] . "','" . $reg_datos["id_tipo_documento"] . "','" . 
	$reg_datos["id_usuario"] . "','"  . $reg_datos["num_interno"] . "','" . $reg_datos["num_oficial"] . "','" .
	$reg_datos["num_externo"] . "','" . $reg_datos["fecha_documento"] . "','" .  $reg_datos["fecha_sistema"] . "','" .
	ltrim($reg_datos["materia"]) . "','" . $reg_datos["id_seguimiento"] . "','" . $reg_datos["id_procedencia"] . "','" .
	$reg_datos["id_destino"]  . "','" . $reg_datos["tipo_procedencia"] . "','" .
	$reg_datos["tipo_destinatario"] . "','" . $reg_datos["rut_procedencia"]  . "','" . 
	$reg_datos["rut_destino"] . "','" . $reg_datos["fecha2"] . "','" . 
	ltrim($reg_datos["observaciones"]) . "','" .
	$tipo_elim . "','" . $fechasistema . "'";
	 
	$reg_datos.close;
	$rs_datos.close;
	$rs_doc = mssql_query($documento_query,$cn);
	$reg_doc = mssql_fetch_array($rs_doc);
	$ret_doc=$reg_doc["ret"];
	   // si se respaldo bien va a eliminar los descriptores,tramites y documento 
		
		if ($ret_doc==0)
		{
		$eliminacion_query = "exec elimina_documentos_tramites '" . $id_doc . "','" . $id_seg . "','" . $tipo_elim .  "'"; 
		
		$rs_elim = mssql_query($eliminacion_query,$cn); 
		$reg_elim = mssql_fetch_array($rs_elim);
		$ret_elim=$reg_elim["Ret"];
		
		if($ret_elim==0)
		{
			$sw_grabado =0;
		
		}
		}
	
	}
	else
	{ 
	if ($sss==0)
	$sw_grabado = 1;
	}


}
else
{
$sw_grabado = 2;
}

//echo "grabado " . $grabado . "<br>";
//echo "ret_doc " . $ret_doc . "<br>";


	echo '<html><body onload="document.form1.submit();">';
	echo '<form name="form1" method="post" action="tra_enc_el.php">' . "\n";
	echo '<input type="hidden" name="idusuario" value="' . $id_usu . '">' . "\n";
	echo '<input type="hidden" name="cusuario" value="' . $c_usuario . '">' . "\n";
	echo '<input type="hidden" name="idfuncionario" value="' . $id_funcionario . '">' . "\n";
	echo '<input type="hidden" name="flujook" value="' . $flujo . '">' . "\n";
	echo '<input type="hidden" name="sw_grabado" value="' . $sw_grabado . '">' . "\n";
	echo '<input type="hidden" name="sw_cons" value="' . 2 . '">' . "\n";
	echo '<input type="hidden" name="Cbo_Procedencia" value="' . $Cbo_Procedencia . '">' . "\n";
	echo '<input type="hidden" name="Cbo_Destinatario" value="' . $Cbo_Destinatario . '">' . "\n";
	echo '<input type="hidden" name="TxtMateria" value="' . $TxtMateria . '">' . "\n";
	echo '<input type="hidden" name="tipo_destino" value="' . $tipo_destino . '">' . "\n";
	echo '<input type="hidden" name="tipo_procedencia" value="' . $tipo_procedencia . '">' . "\n";
	echo '<input type="hidden" name="Txt_fecha_ini" value="' . $fechaini . '">' . "\n";
	echo '<input type="hidden" name="Txt_fecha_fin" value="' . $fechafin . '">' . "\n";
	echo '<input type="hidden" name="Cbo_Tipo_Docto" value="' . $tipo_doc . '">' . "\n";
	echo '<input type="hidden" name="TxtOficial" value="' . $TxtOficial . '">' . "\n";
	echo '<input type="hidden" name="TxtInterno" value="' . $TxtInterno . '">' . "\n";
	echo '<input type="hidden" name="TxtExterno" value="' . $TxtExterno . '">' . "\n";
	
	echo '</form></body></html>'  . "\n";



mssql_close($cn);
?>
