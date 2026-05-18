<?php include("../conexion_bd.php"); ?>
<html>
<head>
<title>Busqueda de Documento</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript1.2">
	function carga() {
	  ok ="<?php echo $ok_graba; ?>";
	  if (ok=="1")
		alert("Se realizo el cambio de estado");
	  else if (ok=="0")
		alert("No se realizaron cambios");
	  else if (ok=="2")
		alert("Ha ocurrido un error durante el cambio");		
	}
</script>
<style type="text/css">
body {border: 0;background:	White;font-family: Arial, Helvetica, sans-serif;font-size: 10px;}
TD {font-family:Arial, Helvetica, sans-serif;font-size:x-small;color:black}
H1,H2 {font-family:Arial, Helvetica, sans-serif}
H1 {font-size:15px;font-weight:600}
H2 {font-size:13px;font-weight:400;color:black}
TH {font-family:Arial, Helvetica, sans-serif;font-size:x-small;color:white;background-color:#0080C0;text-align:center}
</style>
</head>
<body onLoad="carga()">
	<form name="form1" method="post" action="">
	<table width="97%" border="1" align="center">
		<tr bgcolor="#C9DEEF"> 
			<th height="38" colspan="4" valign="top">Buscar Documentos</th>
		</tr>
		<tr>
			<td height="26" valign="top">Dependencia de Origen</td>
			<td valign="top"><select name="cbo_dependencia1" class="combo" id="select" ><option value="0" >--Seleccione Dependencia-- </option><?php
$rs_dependencia = mssql_query("SELECT id_dependencia, desc_dependencia FROM dependencia WHERE vigencia IS NULL ORDER BY desc_dependencia");
$filas = mssql_num_rows($rs_dependencia) - 1;
$reg_dep = mssql_fetch_row($rs_dependencia);
$dependencias1 = '';
$dependencias2 = '';
for ($i = 0; $i <= $filas;$i++){
	if(isset($_POST['cbo_dependencia1']) && $_POST['cbo_dependencia1']==$reg_dep[0]){
		$dependencias1 .= '<option value=' . $reg_dep[0] . ' SELECTED>';
	}else{
		$dependencias1 .= '<option value=' . $reg_dep[0] . '>';
	}
	if(isset($_POST['cbo_dependencia2']) && $_POST['cbo_dependencia2']==$reg_dep[0]){
		$dependencias2 .= '<option value=' . $reg_dep[0] . ' SELECTED>';
	}else{
		$dependencias2 .= '<option value=' . $reg_dep[0] . '>';
	}
	$dependencias1 .= $reg_dep[1];
	$dependencias2 .= $reg_dep[1];
	$dependencias1 .= '</option>';
	$dependencias2 .= '</option>';
	$reg_dep = mssql_fetch_row($rs_dependencia);
}
echo $dependencias1;
?></select></td>
			<td height="26" valign="top">Dependencia de Destino</td>
			<td valign="top"><select name="cbo_dependencia2" class="combo" id="select" ><option value="0" >--Seleccione Dependencia-- </option><?php
echo $dependencias2;
?></select></td>
		</tr>
		<tr> 
			<td height="26" valign="top">Fecha Inicial</td>
			<td valign="top"><input name="fechainicial" type="text" <?PHP if(isset($_POST['fechainicial']) && $_POST['fechainicial']!='DD/MM/AAAA' && strlen($_POST['fechainicial'])==10){echo 'value="'.$_POST['fechainicial'].'"';}else{echo "value='DD/MM/AAAA' onclick=\"document.form1.fechainicial.value=''\"";}?>></td>
			<td valign="top">Fecha Final</td>
			<td valign="top"><input name="fechafinal" type="text" <?PHP if(isset($_POST['fechafinal']) && $_POST['fechafinal']!='DD/MM/AAAA' && strlen($_POST['fechafinal'])==10){echo 'value="'.$_POST['fechafinal'].'"';}else{echo "value='DD/MM/AAAA' onclick=\"document.form1.fechafinal.value=''\"";}?>></td>
		</tr>
	</table>
	<table width="71%" height="50" border="0" align="center">
		<td width="99%" height="44" align="center" valign="middle"> 
		<input type="hidden" name="idusuario" value="<?php echo $idusuario;?>">
		<input type="hidden" name="cusuario" value="<?php echo $cusuario;?>">
		<input type="submit" name="Buscar" value="Buscar">
		</td>
	</table>
</form>
<?PHP
if(isset($_POST['Buscar']) && $_POST['Buscar']=='Buscar'){
	if(isset($_POST['cbo_dependencia1']) && $_POST['cbo_dependencia1']>0)
		$dependencia_envio="and c.id_procedencia = ".$_POST['cbo_dependencia1'];
	else
		$dependencia_envio='';
	if(isset($_POST['cbo_dependencia2']) && $_POST['cbo_dependencia2']>0)
		$dependencia_despacho="and c.id_destino = ".$_POST['cbo_dependencia2'];
	else
		$dependencia_despacho='';
	if(isset($_POST['fechainicial']) && $_POST['fechainicial']!='DD/MM/AAAA' && strlen($_POST['fechainicial'])==10){
		$fechainicial = explode('/', $_POST['fechainicial']);
		$fecha_inicial="and c.fecha_recepcion >= '".$fechainicial[1].'/'.$fechainicial[0].'/'.$fechainicial[2]."'";
	} else
		$fecha_inicial="and c.fecha_recepcion >= '".date("m/d/Y")." ".(date("H")-2).date(":i:s")."'";
	if(isset($_POST['fechafinal']) && $_POST['fechafinal']!='DD/MM/AAAA' && strlen($_POST['fechafinal'])==10){
		$fechafinal = explode('/', $_POST['fechafinal']);
		$fecha_final="and c.fecha_recepcion <= '".$fechafinal[1].'/'.$fechafinal[0].'/'.$fechafinal[2]." 23:59:59'";
	} else
		$fecha_final="and c.fecha_recepcion <= '".date("m/d/Y")." ".date("H:i:s")."'";
	$query = mssql_query("SELECT
c.id_nomina_despacho AS id_nomina,
a.id_documento AS id_documento,
b.desc_tipo_documento AS tipo_documento,
a.materia AS documento_materia,
(SELECT desc_dependencia FROM dependencia WHERE id_dependencia=c.id_procedencia) AS documento_procedencia,
(SELECT desc_dependencia FROM dependencia WHERE id_dependencia=c.id_destino) AS documento_destino,
c.fecha_recepcion,
id_estado_tramite
FROM documento a , tipo_documento b , tramite c , tipo_distribucion d , dependencia e, usuario f
WHERE a.id_documento=c.id_documento
and  a.id_tipo_documento=b.id_tipo_documento
and c.id_tipo_distribucion=d.id_tipo_distribucion
and c.id_procedencia=e.id_dependencia
and c.id_usuario=f.id_usuario
".$fecha_inicial."
".$fecha_final."
".$dependencia_envio."
".$dependencia_despacho."
and (id_estado_tramite = 3 OR id_estado_tramite = 5)
ORDER BY c.fecha_recepcion");
$filas='';
$fila = mssql_num_rows($query) - 1;
for ($i = 0; $i<=$fila; $i++){ 
	$reg_doc = mssql_fetch_row($query);
	if($reg_doc[7]==3){
		$reg_doc[7]='R';
		$stilo = 'yellow';
	}else if($reg_doc[7]==5){
		$reg_doc[7]='C';
		$stilo = 'red';
	}
	$filas .= '<tr><td style="background-color:'.$stilo.'"><center>'.$reg_doc[7].'</center></td><td>'.$reg_doc[0].'</td><td><a href="adm_graba_estado_documento.php?ndoc='.$reg_doc[1].'" onclick="return confirm(\'Desea modificar el estado del documento?.\')">'.$reg_doc[1].'</a></td><td>'.$reg_doc[2].'</td><td>'.$reg_doc[3].'</td><td>'.$reg_doc[4].'</td><td>'.$reg_doc[5].'</td><td>'.str_replace(":00:000", "",$reg_doc[6]).'</td></tr>';
}
echo '<p>&nbsp;</p>
	<table width="97%" border="1" align="center">
		<tr bgcolor="#C9DEEF"> 
			<th colspan="8" valign="top">Documentos Encontrados</th>
		</tr>
		<tr>
			<td>Estado</td><td>Nº Nomina</td><td>Nº Documento</td><td>Tipo Documento</td><td>Materia</td><td>Emite</td><td>Destino</td><td>Fecha Recepcion</td>
		</tr>
		'.$filas.'
	</table>';}
?>
</body>
</html>
<?php mssql_close($cn); ?>