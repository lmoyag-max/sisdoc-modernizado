<?php  include("../conexion_bd.php"); ?>
<html>
<head>
<title>Agregar Dependencias</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript1.2">
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
		alert("Dependencia ingresada");
	  else if (ok=="2")
		alert("Error de variables en el script");
	  else if (ok=="3")
		alert("Dependencia ya existe");		
	}
  	function CheckLength(length) {
		if (window.event.srcElement.value.length >= length) {
		   alert('El Máximo de caracteres es '+  length);
		   return false;                         
		}
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
	<form name="form1" method="post"  action="adm_graba_dependencia.php">
	<table width="50%" border="1" align="center">
        <tr bgcolor="#C9DEEF"> 
          <th height="38" colspan="2" valign="top">Agregar Dependencias</th>
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