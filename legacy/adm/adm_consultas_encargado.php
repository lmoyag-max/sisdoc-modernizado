<?php
if ($sw=="BUS"){
	include("../conex_usuario.php");
	$rut="select * from funcionario where rut_fun ='" . $rutx."'";
	$rs=mssql_query($rut,$cn);
	$t=mssql_num_rows($rs);
	$registro=mssql_fetch_array($rs);
	if ($t>0){    
		echo "<script>\n";
		echo " parent.mainFrame.document.form1.nombre.value='" .     $registro[nombres_fun] ."';\n";	 
		echo " parent.mainFrame.document.form1.ap_pat_fun.value='" . $registro[ap_pat_fun] ."';\n";	 
		echo " parent.mainFrame.document.form1.ap_mat_fun.value='" . $registro[ap_mat_fun] ."';\n";	 
		echo " parent.mainFrame.document.form1.correo.value='" .     $registro[email_fun] ."';\n";	 
		echo " parent.mainFrame.document.form1.clave.value='" .      $registro[marcacion_fun] ."';\n";	 
		if ($registro[sexo_fun]=='M'){
			$d=true;
			$v='S';
			echo " parent.mainFrame.document.form1.sexo[0].checked='" . $d . "';\n";
			echo " parent.mainFrame.document.form1.sexox.value= '" . $v ."';\n";
		}
		if ($registro[sexo_fun]=='F'){
			$d=true;
			$v='F';
			echo " parent.mainFrame.document.form1.sexo[1].checked='" . $d . "';\n";
			echo " parent.mainFrame.document.form1.sexox.value= '" . $v ."';\n";
		}
		echo "</script>\n";
		mssql_close($cn);
		include("../conexion_bd.php");
		$busca_fun="select * from funcionario where rut='" . $rutx."' and vigencia is null ";				  
		$r_fun=mssql_query($busca_fun,$cn);
		$registro_fun=mssql_fetch_array($r_fun);
		$i=1;
		$dep="select * from dependencia where vigencia is null  order by desc_dependencia ";
		$rper=mssql_query($dep,$cn);
		echo "<script>\n";
		while($reg_per=mssql_fetch_array($rper)){  
			if($registro_fun["id_dependencia"]==$reg_per["id_dependencia"]) 
				echo " parent.mainFrame.form1.cbo_dependencia.selectedIndex='" . $i ."';\n";
			echo " parent.mainFrame.form1.cbo_dependencia.options[" . $i . "].selected;\n";
			$i = $i + 1;	
		}
		echo "</script>\n";
		if ($registro_fun["vigencia"]==NULL){
			$d=true;
			$v='S';
			echo "<script>\n"; 
			echo " parent.mainFrame.document.form1.vigencia[0].checked='" . $d . "';\n";
			echo " parent.mainFrame.document.form1.vigenciax.value= '" . $v ."';\n";
			echo "</script>\n";
		}
		if ($registro_fun["vigencia"]=='N'){
			$d=true;	
			$v='N';
			echo "<script>\n";
			echo " parent.mainFrame.document.form1.vigencia[1].checked='" . $d . "';\n";
			echo " parent.mainFrame.document.form1.vigenciax.value= '" . $v ."';\n";
			echo "</script>\n";
		}
	}
}
if ($sw=="BUSM"){
	include("../conex_usuario.php");
	$rut="select * from funcionario where rut_fun ='" . $rutx."'";
	$rs=mssql_query($rut,$cn);
	$t=mssql_num_rows($rs);
	$registro=mssql_fetch_array($rs);
	if ($t>0){    
		echo "<script>\n";
		echo " parent.mainFrame.document.form1.nombre.value='".$registro[nombres_fun]."';\n";	 
		echo " parent.mainFrame.document.form1.ap_pat_fun.value='".$registro[ap_pat_fun]."';\n";	 
		echo " parent.mainFrame.document.form1.ap_mat_fun.value='".$registro[ap_mat_fun]."';\n";	 
		echo " parent.mainFrame.document.form1.correo.value='".$registro[email_fun]."';\n";	 
		echo " parent.mainFrame.document.form1.clave.value='".$registro[marcacion_fun]."';\n";
		echo " parent.mainFrame.document.form1.guarda_rut.value='".$registro[rut_fun]."-".$registro[dv_fun]."';\n";
		if ($registro[sexo_fun]=='M'){
			$d=true;
			$v='S';
			echo " parent.mainFrame.document.form1.sexo[0].checked='" . $d . "';\n";
			echo " parent.mainFrame.document.form1.sexox.value= '" . $v ."';\n";
		}
		if ($registro[sexo_fun]=='F'){
			$d=true;
			$v='F';
			echo " parent.mainFrame.document.form1.sexo[1].checked='" . $d . "';\n";
			echo " parent.mainFrame.document.form1.sexox.value= '" . $v ."';\n";
		}
		echo "</script>\n";
	}
}
if ($sw=="BDXU"){
	include("../conexion_bd.php");
	$rut="SELECT * FROM funcionario WHERE rut='".$rutx."'";
	$rs=mssql_query($rut,$cn);
	$t=mssql_num_rows($rs);
	$registro=mssql_fetch_array($rs);
	if ($t>0){
		echo "<script>\n";
		echo "	parent.mainFrame.document.form1.nombre.value='".$registro[nombres]." ".$registro[apellidos]."';\n";
		$dep= mssql_query("SELECT id_dependencia, desc_dependencia, vigencia FROM dependencia WHERE id_dependencia IN (SELECT DISTINCT id_dependencia FROM funcionario WHERE rut='".$rutx."') ORDER BY desc_dependencia ");
		$filas=mssql_num_rows($dep) - 1;
		$reg_dep=mssql_fetch_row($dep);
		for ($i = 0; $i <= $filas;  $i++){ 
			echo '	var x = parent.mainFrame.document.getElementById("select");
	var c = document.createElement("option");
	c.text = "'.$reg_dep[1].'";
	c.value = "'.$reg_dep[0].'";
	x.options.add(c,'.$i.');'."\n";
			$reg_dep = mssql_fetch_row($dep);
		}
		echo "</script>\n";
		$rper=mssql_query($dep,$cn);
	}
}
if ($sw=="BVXD"){
	echo "<script>\n";
	if($depx==0){
		echo " parent.mainFrame.document.form1.vigencia[0].checked=false;\n";
		echo " parent.mainFrame.document.form1.vigencia[1].checked=false;\n";
		echo " parent.mainFrame.document.form1.vigenciax.value='';\n";
	}else{
		include("../conexion_bd.php");
		$rs=mssql_query("SELECT vigencia FROM funcionario WHERE rut='".$rutx."' AND id_dependencia='".$depx."'" ,$cn);
		$registro=mssql_fetch_array($rs);
		if ($registro["vigencia"]=='N'){
			echo " parent.mainFrame.document.form1.vigencia[1].checked=true;\n";
			echo " parent.mainFrame.document.form1.vigenciax.value='N';\n";
		}else{
			echo " parent.mainFrame.document.form1.vigencia[0].checked=true;\n";
			echo " parent.mainFrame.document.form1.vigenciax.value='Y';\n";
		}
	}
	echo "</script>\n";
}

if ($sw=="DEP"){
	include("../conexion_bd.php");
	$busca_dep="SELECT desc_dependencia, vigencia FROM dependencia WHERE id_dependencia='".$dep."'";
	$rs=mssql_query($busca_dep,$cn);
	$t=mssql_num_rows($rs);
	$registro=mssql_fetch_array($rs);
	if ($t>0){    
		echo "<script>\n";
		echo " parent.mainFrame.document.form1.nombre.value='" . $registro[desc_dependencia] ."';\n";
		if ($registro["vigencia"]==NULL){
			$d=true;
			$v='S';
			echo " parent.mainFrame.document.form1.vigencia[0].checked='" . $d . "';\n";
			echo " parent.mainFrame.document.form1.vigenciax.value= '" . $v ."';\n";
		}
		if ($registro["vigencia"]=='N'){
			$d=true;
			$v='N';
			echo " parent.mainFrame.document.form1.vigencia[1].checked='" . $d . "';\n";
			echo " parent.mainFrame.document.form1.vigenciax.value= '" . $v ."';\n";
		}
		echo "</script>\n";
	}
}

if ($sw=="BADXU"){
	include("../conexion_bd.php");
	$rut="SELECT * FROM funcionario WHERE rut='".$rutx."'";
	$rs=mssql_query($rut,$cn);
	$t=mssql_num_rows($rs);
	$registro=mssql_fetch_array($rs);
	if ($t>0){
		echo "<script>\n";
		echo "	parent.mainFrame.document.form1.nombre.value='".$registro[nombres]." ".$registro[apellidos]."';\n";
		$dep= mssql_query("SELECT id_dependencia, desc_dependencia FROM dependencia WHERE id_dependencia NOT IN (SELECT DISTINCT id_dependencia FROM funcionario WHERE rut='".$rutx."' AND vigencia IS null) AND vigencia IS null ORDER BY desc_dependencia");
		$filas=mssql_num_rows($dep) - 1;
		$reg_dep=mssql_fetch_row($dep);
		for ($i = 0; $i <= $filas;  $i++){ 
			echo '	var x = parent.mainFrame.document.getElementById("select");
	var c = document.createElement("option");
	c.text = "'.$reg_dep[1].'";
	c.value = "'.$reg_dep[0].'";
	x.options.add(c,'.$i.');'."\n";
			$reg_dep = mssql_fetch_row($dep);
		}
		echo "</script>\n";
		$rper=mssql_query($dep,$cn);
	}
}

if ($sw="BDG"){
	include("../conexion_bd.php");
	
	$documento = "SELECT TOP 10
		a.id_documento,
		a.num_interno,
		b.desc_tipo_documento,
		c.id_nomina_despacho,
		c.observaciones,
		c.id_tipo_distribucion,
		d.desc_tipo_distribucion,
		c.id_procedencia,
		e.desc_dependencia,
		c.id_destino,
		c.id_estado_tramite,
		c.tipo_procedencia,
		c.tipo_destinatario,
		c.rut_procedencia,
		c.rut_destino,
		c.id_usuario,
		f.usuario,
		c.fecha_recepcion,
		c.fecha_despacho,
		c.fecha_sistema,
		c.fecha_update,
		c.observaciones
	FROM
		documento a,
		tipo_documento b,
		tramite c,
		tipo_distribucion d,
		dependencia e,
		usuario f
	WHERE
		a.id_documento=c.id_documento 
	AND
		a.id_tipo_documento=b.id_tipo_documento
	AND
		c.id_tipo_distribucion=d.id_tipo_distribucion
	AND
		c.id_procedencia=e.id_dependencia
	AND
		c.id_usuario=f.id_usuario  
	AND
		c.fecha_sistema > '08/01/2013'
	AND
		c.id_destino = 9";
	mssql_query($documento, $cn);
	$t=mssql_num_rows($rs);
	$registro=mssql_fetch_array($rs);
	if ($t>0){
		echo "<script>\n";
		echo "	parent.mainFrame.document.form1.nombre.value='".$registro[nombres]." ".$registro[apellidos]."';\n";
		$dep= mssql_query("SELECT id_dependencia, desc_dependencia FROM dependencia WHERE id_dependencia NOT IN (SELECT DISTINCT id_dependencia FROM funcionario WHERE rut='".$rutx."' AND vigencia IS null) AND vigencia IS null ORDER BY desc_dependencia");
		$filas=mssql_num_rows($dep) - 1;
		$reg_dep=mssql_fetch_row($dep);
		for ($i = 0; $i <= $filas;  $i++){ 
			echo '	var x = parent.mainFrame.document.getElementById("select");
	var c = document.createElement("option");
	c.text = "'.$reg_dep[1].'";
	c.value = "'.$reg_dep[0].'";
	x.options.add(c,'.$i.');'."\n";
			$reg_dep = mssql_fetch_row($dep);
		}
		echo "</script>\n";
	}
}
mssql_close($cn);
?>   