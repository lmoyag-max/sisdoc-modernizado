<?php
	include("../conexion_bd.php"); 
?>
<html>
<head>
<title>Modificacion de Usuarios</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript1.2">
function valida_digito(cadena,objeto,largo){
	var i;
	var allowedac;
	var retorno;
	retorno = true;
	allowedac = "0123456789-/";
	for( i=0; i < cadena.length; i++ )
		if (allowedac.indexOf(cadena.charAt(i)) < 0){
			retorno = false;
		}
		if (!retorno){
			objeto.value = "";
			alert("Solo se aceptan números enteros")
			objeto.focus();
		}
	return retorno;
}
function graba(){
	sw=true;
	if (document.form1.el_rut.value == ""){ 
		alert ("Debe ingresar rut");
		sw=false;
		document.form1.el_rut.focus();
	}else if (document.form1.nombre.value == ""){ 
		alert ("Debe ingresar nombre");
		sw=false;
		document.form1.nombre.focus();
	}else if (document.form1.ap_pat_fun.value == "" ){ 
		alert ("Debe ingresar al menos apellido paterno funcionario");
		sw=false;
		document.form1.ap_pat_fun.focus();
	}else if (document.form1.clave.value == ""){ 
		alert ("Debe ingresar clave");
		sw=false;
		document.form1.clave.focus();
	}else if (document.form1.sexox.value == ""){ 
		alert ("Debe ingresar sexo del usuario");
		sw=false;
		document.form1.sexo.focus();
	}else if (document.form1.correo.value == ""){ 
		alert ("Debe ingresar e-mail del usuario");
		sw=false;
		document.form1.correo.focus();
	}if (sw)
		document.form1.submit();
}
function carga(){
	ok ="<?php echo $ok_graba;?>";
	if (ok=="2")
		alert("No se encontro el usuario");
	else if (ok=="1")
		alert("Usuario ha sido modificado");	
}
function CheckLength(length){
	if (window.event.srcElement.value.length >= length){
		alert('El Máximo de caracteres es '+length);
		return false;
	}
}
function verificarRutGeneral(strRut, tipo, action){
	if (strRut==""){
		alert("Debe ingresar el RUT.");
		submitcount=0;
		document.form1.el_rut.focus();
		document.form1.el_rut.select();
	}else{	
		strRut=limpiarRut(strRut);
		if (verificarRut(strRut)){			
			if (action==1){
			}else{
				formatearRut(strRut);
			}
		}
	}
}
function limpiarRut(strRut){	
	document.form1.rut.value = document.form1.el_rut.value;
	var digVerif ="";
	var digVerifIn ="";
	var straux ="";
	var rutsgnp = "";
	while((new Number(strRut.charAt(0))==0)&&(strRut!="")){
		strRut=strRut.substring(1,strRut.length);		
	}
	for (i=0; i < strRut.length; i++){
		if ((strRut.charAt(i) != ".") && (strRut.charAt(i) != "-") && (strRut.charAt(i)!=" "))
			rutsgnp= rutsgnp + strRut.charAt(i);
	}
	return rutsgnp;
}
function soloNumeros(strIn){
	var Nros="1234567890";
	var CrtrAux;
	var iaux=0;
	for (var i=0; i < strIn.length; i++){
		CrtrAux = strIn.charAt(i);
		if (Nros.indexOf(CrtrAux)!= -1)
			iaux++;
	}
	if ((iaux != strIn.length) || (strIn.length==0)){
		return 0
	}else
		return 1;
}
function digitoVerificador(strRut){
	var Largo, LargoN, i, Total;
	var Numero="", Verif, Carac, CaracVal;
	var tmpRut,intTmp;
	tmpRut = strRut;
	Largo = tmpRut.length;
	LargoN = 0;
	for(i=0;i<Largo;i++){
		Carac = parseInt(tmpRut.charAt(i),10);
		if(Carac >=0 && Carac <=9){
			Numero+=tmpRut.charAt(i);
			LargoN++;
		}
	}
	Total=0;
	for(i=LargoN-1;i>=0;i--){
		if((LargoN - i) < 7){
			intTmp=LargoN - i + 1;
		}else{
			intTmp=LargoN - i - 5;
		}
		Total+= parseInt(Numero.charAt(i),10) * intTmp 
	}
	CaracVal = 11 - (Total % 11)
	if(CaracVal==10){
		return('K');
	}
	if(CaracVal >=0 && CaracVal <=9){
		return(CaracVal);
	}
	if(CaracVal==11){
		return(0);
	}
}
function verificarRut(strRut){	
	if (strRut != ""){
		straux = strRut.substring(strRut.length-1,strRut.length);
		if (straux == "k") 
		digVerifIn = straux.toUpperCase()
		else
			digVerifIn = straux;
		straux = strRut.substring(0,strRut.length-1);
		if (soloNumeros(straux) == 0)
			digVerif = "KX"
		else
			digVerif = digitoVerificador(straux);
		if(digVerif == digVerifIn){
			accionInterna=1;
			return true;
		}else{
			alert("RUT incorrecto.");
			submitcount=0;
			document.form1.el_rut.value="";
			document.form1.el_rut.select();
			document.form1.el_rut.focus();
			accionInterna=0;
			return false;
		}
	}else{
		alert("Debe ingresar el RUT.");
		submitcount=0;
		document.form1.el_rut.value="";
		document.form1.el_rut.select();
		document.form1.el_rut.focus();
		return false;
	}
}
function formatearRut(strRut){
	straux = strRut.substring(strRut.length-1,strRut.length);
	rutsgnp= strRut.substring(0,strRut.length-1);
	strAuxArray = new Array(0,0,0);
	strAuxArray[0]=rutsgnp.substring(rutsgnp.length-3,rutsgnp.length);
	strAuxArray[1]=rutsgnp.substring(rutsgnp.length-6,rutsgnp.length-3);
	strAuxArray[2]=rutsgnp.substring(0,rutsgnp.length-6);
	i=0;
	rutsgnp="-"+straux;
	for (i=0; i < 3; i++){
		if (strAuxArray[i]==""){
			i=3;
		}else{
			if (i>0){
				rutsgnp="."+rutsgnp;
			}
			rutsgnp=strAuxArray[i]+rutsgnp;
		}
	}
	document.form1.el_rut.value=rutsgnp;
	document.form1.el_rut.blur();
}
function busca_rut(rut){
	if (rut !=''){
		var rut ;
		var colecta ;
		var punto = rut.replace('.',''); 
		var punto =punto.replace('.','');
		var posicion = punto.indexOf('-');
		var guion =punto.replace('-','');
		var rut= guion.substring(0,posicion);
		var valor = rut;
		var sw='BUSM';
		top.window.fr_escon.location.href="adm_consultas_encargado.php?sw="+sw+ "&rutx=" + valor
	}
} 
</script>
<style type="text/css">
	body	{border:0;background:White;font-family:Arial, Helvetica, sans-serif;font-size:10px;}
	TD		{font-family:Arial, Helvetica, sans-serif;font-size:x-small;color:black}
	H1,H2	{font-family:Arial, Helvetica, sans-serif}
	H1		{font-size:15px;font-weight:600}
	H2		{font-size:13px;font-weight:400;color:black}
	TH		{font-family:Arial, Helvetica, sans-serif;font-size:x-small; color:white;background-color:#0080C0; text-align:center}
</style>
</head>
<body onLoad="carga()">
<form name="form1" method="post" action="adm_grabamod_usuario.php">
	<table width="97%" border="1" align="center">
	<tr bgcolor="#C9DEEF">
		<th height="38" colspan="6" valign="top">Modificar usuarios</th>
	</tr>
	<tr>
		<td height="26" valign="top"><font size="2" face="Arial">Rut </font></td>
		<td valign="top"><input name="el_rut" onChange="verificarRutGeneral(document.form1.el_rut.value,0,0);" onBlur="busca_rut(document.form1.el_rut.value);" type="text" id="rut5" size="9" maxlength="9" max="9"><strong>(sin puntos ni gui&oacute;n)</strong></td>
		<td valign="top">E- mail </td>
		<td valign="top"><label><input name="correo" type="text" id="correo" size="60" maxlength="60"> </label></td>
	</tr>
	<tr>
		<td height="26" valign="top">Nombre</td>
		<td width="40%" valign="top"><input name="nombre" onKeyPress="CheckLength(60)" type="text" id="nombre" size="60" maxlength="60" max="60"></td>
		<td width="18%" valign="top">Apellido Paterno </td>
		<td width="33%" valign="top"><label><input name="ap_pat_fun" type="text" id="ap_pat_fun" onKeyPress="CheckLength(30)" size="30" maxlength="30"></label></td>
	</tr>
	<tr>
		<td height="26" valign="top">Apellido materno </td>
		<td valign="top"><label><input name="ap_mat_fun" type="text" id="ap_pat_mat" onKeyPress="CheckLength(30)" size="30" maxlength="30"></label></td>
		<td valign="top">Sexo </td>
		<td valign="top"><label>Masculino <input name="sexo" type="radio" value="radiobutton" onClick="document.form1.sexox.value='M'">Femenino <input name="sexo" type="radio" value="radiobutton" onClick="document.form1.sexox.value='F'"></label></td>
	</tr>
	<tr>
		<td valign="top">Clave</td>
		<td valign="top" colspan="3"><input name="clave" type="text" id="rut4" size="4" maxlength="4" max="4"></td>
		<input type="hidden" name="acceso" value="<?php echo $acceso;?>">
	</tr>
	</table>
	<table width="50%" height="50" border="0" align="center">
		<td width="99%" height="44" align="center" valign="middle">
			<input type="hidden" name="idusuario" value="<?php echo $idusuario;?>">
			<input type="hidden" name="cusuario" value="<?php echo $cusuario;?>">
			<input type="hidden" name="rut" >
			<input type="hidden" name="rutx" >
			<input type="hidden" name="guarda_rut" >
			<input type="hidden" name="sexox" >
			<input type="button" name="Graba" value="Graba" onClick="graba();">
		</td>
	</table>
</form>
</body>
</html>