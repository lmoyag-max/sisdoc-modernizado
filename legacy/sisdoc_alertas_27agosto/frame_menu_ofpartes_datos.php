<?php
	include("conexion_bd.php");
	$txtagno =date("Y");
	$id_funcionario=$idfuncionario;

	if ($id_funcionario!=1419){
	 $rs ="SELECT *,convert(varchar,d.fecha_documento,103) as fecha_dco, REPLACE(materia, CHAR(13) + CHAR(10), '') AS materia2,
	 		convert(varchar,d.fecha_sistema,112) as fecha_timbre_recepcion2 , 0 as desc_dependencia_externa
			FROM tramite t
			INNER JOIN documento d ON t.id_documento= d.id_documento 
			INNER JOIN tipo_documento c ON d.id_tipo_documento= c.id_tipo_documento
			WHERE (t.id_destino = '".$id_dependencia."' AND t.tipo_destinatario='I') AND (t.id_estado_tramite = 2 OR t.id_estado_tramite = 3) AND (YEAR(t.fecha_despacho) = ".$txtagno.")
			ORDER BY d.fecha_documento,d.id_documento"; 
	}else{
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
	//echo $rs;

	$rs0 =mssql_query($rs);
	$rs1=mssql_num_rows($rs0);
	$array_doc=array();$array_fecha=array();$array_id_compromiso=array();$array_dias_compromiso=array();
	while ($rs2=mssql_fetch_array($rs0, MYSQL_ASSOC)){
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
		$array_origen[]=$rs2["desc_dependencia_externa"];
		
		if ($rs2["id_compromiso"]=="")
			$array_id_compromiso[]=9999;
		else
			$array_id_compromiso[]=$rs2["id_compromiso"];
			
		$array_dias_compromiso[]=$rs2["dias_compromiso"];
	}
	
	$array_verde=array();$array_amarillo=array();$array_rojo=array();
	for ($i=0;$i<$rs1;$i++){
		$rs="";
		if ($array_id_compromiso[$i]==9999){
			$plazo_alerta=10;
			$plazo_limite_dias=20;
		}
		else{
			$rs="SELECT * FROM tipo_compromiso2 WHERE id_compromiso=".$array_id_compromiso[$i]."";
			$rs0 =mssql_query($rs);
			$rs3=mssql_fetch_array($rs0, MYSQL_ASSOC);
			$plazo_alerta=$rs3["plazo_alerta"];
			$plazo_limite_dias=$rs3["plazo_limite_dias"];
		}
		
		//echo "<br>".$plazo_alerta."<br>";
		//echo "<br>".$plazo_limite_dias."<br>\n";
		$rs="SELECT COUNT(tipo_dia) AS cantidad FROM calendario where fecha BETWEEN ".$array_fecha[$i]." AND GETDATE() AND (tipo_dia = 'H')";
		$rs0 =@mssql_query($rs);
		$rs4=@mssql_fetch_array($rs0, MYSQL_ASSOC);
		$cantidad=$rs4["cantidad"];
		//echo "<br>".$rs."<br>";
		
		//echo $array_doc[$i]."<br>";
		if ($cantidad<$plazo_alerta){
			//if (!in_array($array_doc[$i],$array_verde)){
				$array_verde[]=$array_doc[$i];
				$verde_idseg[]=$array_idseg[$i];
				$verde_nomina[]=$array_nomina[$i];
				$verde_desc_tipo_doc[]=$array_desc_tipo_doc[$i];
				$verde_num_externo[]=$array_num_externo[$i];
				$verde_fecha_documento[]=$array_fecha_documento[$i];
				$verde_materia[]=$array_materia[$i];
				$verde_num_interno[]=$array_num_interno[$i];
				$verde_origen[]=$array_origen[$i];
			//}
			//echo "verde<br>";
		}
		if (($cantidad>=$plazo_alerta) && ($cantidad<$plazo_limite_dias-2)){
			//if (!in_array($array_doc[$i],$array_amarillo)){
				$array_amarillo[]=$array_doc[$i];
				$amarillo_idseg[]=$array_idseg[$i];
				$amarillo_nomina[]=$array_nomina[$i];
				$amarillo_desc_tipo_doc[]=$array_desc_tipo_doc[$i];
				$amarillo_num_externo[]=$array_num_externo[$i];
				$amarillo_fecha_documento[]=$array_fecha_documento[$i];
				$amarillo_materia[]=$array_materia[$i];
				$amarillo_num_interno[]=$array_num_interno[$i];
				$amarillo_origen[]=$array_origen[$i];
			//}
			//echo "amarillo<br>";
		}
		if ($cantidad>=$plazo_limite_dias-2){
			//if (!in_array($array_doc[$i],$array_rojo)){
				$array_rojo[]=$array_doc[$i];
				$rojo_idseg[]=$array_idseg[$i];
				$rojo_nomina[]=$array_nomina[$i];
				$rojo_desc_tipo_doc[]=$array_desc_tipo_doc[$i];
				$rojo_num_externo[]=$array_num_externo[$i];
				$rojo_fecha_documento[]=$array_fecha_documento[$i];
				$rojo_materia[]=$array_materia[$i];
				$rojo_num_interno[]=$array_num_interno[$i];
				$rojo_origen[]=$array_origen[$i];
			//}
			//echo "rojo<br>";
		}
	}
	
	//echo "<pre>"; print_r($array_verde); echo "</pre>";
 	//echo "<pre>"; print_r($array_amarillo); echo "</pre>";
	/* echo "<pre>array_rojo:"; print_r($array_rojo); echo "</pre>";
	echo "<pre>rojo_idseg"; print_r($rojo_idseg); echo "</pre>";
	echo "<pre>rojo_nomina"; print_r($rojo_nomina); echo "</pre>";
	echo "<pre>rojo_desc_tipo_doc"; print_r($rojo_desc_tipo_doc); echo "</pre>";
	echo "<pre>rojo_num_externo"; print_r($rojo_num_externo); echo "</pre>";
	echo "<pre>rojo_fecha_documento"; print_r($rojo_fecha_documento); echo "</pre>";
	echo "<pre>rojo_materia"; print_r($rojo_materia); echo "</pre>";
	echo "<pre>rojo_num_interno"; print_r($rojo_num_interno); echo "</pre>"; */
?>
 