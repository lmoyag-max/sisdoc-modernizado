<?php  include("../conexion_bd.php"); ?>
<html>
<head>
<title>Agregar Dependenci</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript1.2">
	function busca_Dependencia(dependencia) {
		if (dependencia != '0'){
			var depx=document.form1.cbo_dependencia.value;
			var sw='DEP';
			top.window.fr_escon.location.href="adm_consultas_encargado.php?sw="+sw+"&dep="+depx
		}
	}
	function verificarDependencia(strDependencia, tipo, action){
		if (strDependencia=="0"){
			alert("Debe seleccionar una Dependencia.");
			submitcount=0;
			document.form1.cbo_dependencia.focus();
			document.form1.cbo_dependencia.select();
		}
		else{	
			busca_Dependencia();
		}
	}
	function graba() {
		sw=true ; 
		if (document.form1.nombre.value == "") { 
			alert ("Debe ingresar nombre");
			sw=false;
			document.form1.nombre.focus();
		}
		else if (document.form1.vigenciax.value == "") { 
			alert ("Debe ingresar vigencia");
			sw=false;
			document.form1.vigencia.focus();
		}
		if (sw)
			document.form1.submit();
	}
	function carga() {
	  ok ="<?php echo $ok_graba; ?>";
	  if (ok=="1")
		alert("Dependencia modificada");
	  else if (ok=="2")
		alert("No se realizaron cambios");
	  else if (ok=="3")
		alert("Dependencia no existe");		
	}
  	function CheckLength(length) {
		if (window.event.srcElement.value.length >= length) {
		   alert('El Máximo de caracteres es '+  length);
		   return false;                         
		}
	}
  	function showDiv(elem){
		if(elem.value == 1)
		document.getElementById('table2').style.display = "block";
	}
</script>
<style type="text/css">
body {
	border:		0;
	background:	White;
    font-family:    Arial, Helvetica, sans-serif;
	font-size: 10px;
}
TD      {font-family:Arial, Helvetica, sans-serif;font-size:x-small;color:black}
H1,H2   {font-family:Arial, Helvetica, sans-serif}
H1      {font-size:15px;font-weight:600}
H2      {font-size:13px;font-weight:400;color:black}
TH      {font-family:Arial, Helvetica, sans-serif;font-size:x-small;
         color:white;background-color:#0080C0;
         text-align:center
        }
}
</style>
</head>
<body onLoad="carga()">
	<form name="form1" method="post"  action="adm_grabamod_dependencia.php">
	<table width="50%" border="1" align="center">
		<tr bgcolor="#C9DEEF"> 
			<th height="38" colspan="2" valign="top">Buscar Dependencia</th>
		</tr>
		<tr> 
			<td height="26" width="30%" valign="top">Nombre Dependencia</td>
			<td valign="top"><select name="cbo_dependencia" class="combo" id="select" onChange="verificarDependencia(document.form1.cbo_dependencia.value,0,0)">
				<option value="0" >--Seleccione dependencia--</option>
				<?php
					$rs_dependencia = mssql_query("SELECT id_dependencia, desc_dependencia FROM dependencia ORDER BY desc_dependencia");
					$filas=mssql_num_rows($rs_dependencia) - 1;
					$reg_dep=mssql_fetch_row($rs_dependencia);
					for ($i = 0; $i <= $filas; $i++) { 
						echo '<option value=' . $reg_dep[0] . '>'; 
						echo $reg_dep[1];
						echo '</option>';
						$reg_dep = mssql_fetch_row($rs_dependencia);
					}  
				?>
			</select></td>
		</tr>
	</table>
<br/>
	<table width="50%" border="1" align="center">
        <tr bgcolor="#C9DEEF"> 
          <th height="38" colspan="2" valign="top">Modificar Dependencia</th>
        </tr>
        <tr> 
          <td height="26" width="30%" valign="top">Nombre Dependencia</td>
          <td valign="top"><input name="nombre"  onKeyPress="CheckLength(60)" type="text" id="nombre" size="60" maxlength="60" max="60"></td>
        <tr> 
          <td height="26"  valign="top">Vigencia</td>
          <td valign="top">
            Si <input type="radio" name="vigencia" value="radiobutton" onClick="document.form1.vigenciax.value='S'">
            No <input type="radio" name="vigencia" value="radiobutton"  onClick="document.form1.vigenciax.value='N'"></td>
        </tr>
	</table>
	<table width="50%" height="50" border="0" align="center">
        <td width="100%" height="44" align="center" valign="middle"> 
            <input type="hidden" name="vigenciax" >
            <input type="button" name="Graba" value="Graba" onClick="graba();">
        </td>
	</table>
	</form>
<p>&nbsp;</p>
</body>
</html>
<?php mssql_close($cn); ?>