<?php  include("../conexion_bd.php"); ?>
<html>
<head>
<title>Agregar Dependencia</title>
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
		if (sw)
			document.form1.submit();
	}
	function carga() {
		ok ="<?php echo $ok_graba; ?>";
		if (ok=="1")
			alert("Se ha agregado la dependencia al usuario");
		else if (ok=="3")
			alert("Se re-ingreso el usuario a la dependencia");
		else if (ok=="0")
			alert("No se realizaron cambios");
		}
  	function CheckLength(length) {
		if (window.event.srcElement.value.length >= length) {
		   alert('El Máximo de caracteres es '+  length);
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
  	function showDiv(elem) {
		if(elem.value == 1)
		document.getElementById('table2').style.display = "block";
	}
	function verificarRut(strRut) {
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
	function soloNumeros(strIn) {
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
	function digitoVerificador(strRut) {
		var Largo, LargoN, i, Total;
		var Numero="", Verif, Carac, CaracVal;
		var tmpRut,intTmp;
		tmpRut = strRut;
		Largo = tmpRut.length;
		LargoN = 0;
		for(i=0;i<Largo;i++) {
			Carac = parseInt(tmpRut.charAt(i),10);
			if(Carac >=0 && Carac <=9){
				Numero+=tmpRut.charAt(i);
				LargoN++;
			}
		}
		Total=0;
		for(i=LargoN-1;i>=0;i--) {
			if((LargoN - i) < 7) {
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
	function formatearRut(strRut) {
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
	function busca_rut(rut) {
 		if (rut !=''){
			var rut ;
			var colecta ;
			var punto = rut.replace('.',''); 
			var punto =punto.replace('.','');
			var posicion = punto.indexOf('-');
			var guion =punto.replace('-','');
			var rut= guion.substring(0,posicion);
			var valor = rut;
			document.form1.rutx.value=valor;
			var sw='BADXU';
			top.window.fr_escon.location.href="adm_consultas_encargado.php?sw="+sw+ "&rutx=" + valor
		}
	}
</script>
<style type="text/css">
body {border: 0;background:	White;font-family:    Arial, Helvetica, sans-serif;font-size: 10px;}
TD {font-family:Arial, Helvetica, sans-serif;font-size:x-small;color:black}
H1,H2 {font-family:Arial, Helvetica, sans-serif}
H1 {font-size:15px;font-weight:600}
H2 {font-size:13px;font-weight:400;color:black}
TH {font-family:Arial, Helvetica, sans-serif;font-size:x-small;color:white;background-color:#0080C0;text-align:center}
</style>
</head>
<body onLoad="carga()">
	<form name="form1" method="post"  action="adm_graba_usuario_dependencia.php">
	<table width="50%" border="1" align="center" style="min-width:500px">
		<tr bgcolor="#C9DEEF"> 
			<th height="38" colspan="2" valign="top">Buscar Funcionario</th>
		</tr>
		<tr>
			<td>Rut</td>
			<td><input name="el_rut" onChange="verificarRutGeneral(document.form1.el_rut.value,0,0);"  onBlur="busca_rut(document.form1.el_rut.value);" type="text" id="rut5" size="15" maxlength="9" max="9">
        <strong>(sin puntos ni gui&oacute;n)</strong></td>
		</tr>
		<tr> 
			<td height="26" width="30%" valign="top">Nombre</td>
			<td valign="top"><input name="nombre" type="text" id="nombre" size="60" maxlength="60" max="60" disabled></td>
		</tr>
	</table>
<br/>
	<table width="50%" border="1" align="center" style="min-width:500px">
        <tr bgcolor="#C9DEEF"> 
          <th height="38" colspan="2" valign="top">Agregar Dependencia</th>
        </tr>
        <tr> 
          <td height="26" width="30%" valign="top">Nombre Dependencia</td>
          <td valign="top"><select name="cbo_dependencia" class="combo" id="select" ><option value="0" >--Seleccione dependencia-- </option></select></td>

	</table>
	<table width="50%" height="50" border="0" align="center">
        <td width="100%" height="44" align="center" valign="middle"> 
			<input type="hidden" name="depx" >
			<input type="hidden" name="rut" >
			<input type="hidden" name="rutx" >
			<input type="hidden" name="idfuncionario" >
			<input type="button" name="Graba" value="Guardar" onClick="graba();">
        </td>
	</table>
	</form>
<p>&nbsp;</p>
</body>
</html>
<?php mssql_close($cn); ?>