<?php
include("conexion_bd.php");
//include("carga_tablas.php");
$ok2=$ok;
$rut=$rut;
$rut_c=$rut_c;
echo "rut" . $rut . "   " . $rut_c;
/*if(!isset($txtrutsol))
{
echo '<script language="javascript"> location.href="index.php"; </script>';
}
*/
$rs_tipo = mssql_query("select * from tipo_carga order by desc_carga",$cc);
$fecha_x = date("d-m-Y");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>formulario ingreso</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript" type="text/javascript">
var sw_ok;
var cont_arreglo;
var z=0;




 function busca_nombre_ant(r,d,pos)
{
var valor=pos;
var rut = r;
var dv=d;
parent.frames[0].location.href="frame_consultas.php?rut="+rut+"&sw="+valor+"&dv="+dv;
}

function valida_digito(cadena,objeto,largo)
{	//-----------------------------

	var i;
    var allowedac;
    var retorno;
    retorno = true;
    allowedac = "0123456789";
    for ( i=0; i < cadena.length; i++) { 
    if (allowedac.indexOf(cadena.charAt(i)) < 0) {
	    retorno = false; }
	}  
        
if (!retorno)
 {
   objeto.value = "0";
   alert("Solo se aceptan números enteros");
   objeto.focus();
 }
return retorno;
} 

 function busca_nombre(nom,filas) 
{
       
	   document.form1.arreglo.value=document.form1.arreglo.value+nom + "@" ;
		z=z+1
	   
  }

// Valida los datos antes de grabar en las tablas
function validar_datos()
{
	sw_ok=true;
 if(document.form1.Txt_fecha_doc.value == "")
  {
 	sw_ok=false;
	alert("Falta Ingresar la Fecha de Solicitud");
	document.form1.Txt_fecha_doc.focus();
  }

if (sw_ok)
{
//	document.form1.arreglo.value=z+"@" +document.form1.arreglo.value ;
  	document.form1.submit();
}
} 

function CheckLength(length) {
if (window.event.srcElement.value.length >= length) {
   alert('El Máximo de caracteres es  250');
   return false; }          
}
 
function despachar_datos() 
{
	document.form1.action="multi_pages.php";
   	document.form1.submit();
} 


//--------------------------------------
//function digitoVerificador
//Objetivo: Retornar el Digito verificador de un RUT.
//Parametro(s):(input)String de ingreso de rut (incluyendo el DV).
//(output) DV obtenido
//Uso: desde fnc. verificarRut
//Requiere:  
//--------------------------------------
function digitoVerificador(strRut)
 {
    var Largo, LargoN, i, Total;
    var Numero="", Verif, Carac, CaracVal;
    var tmpRut,intTmp;
    
    tmpRut = strRut;
    Largo = tmpRut.length;
    LargoN = 0;
    for(i=0;i<Largo;i++) {
        Carac = parseInt(tmpRut.charAt(i),10);
        if(Carac >=0 && Carac <=9) {
			Numero+=tmpRut.charAt(i);
            LargoN++;
	 	}
    }
	Total=0;
    for(i=LargoN-1;i>=0;i--) {
		if((LargoN - i) < 7) {
		   intTmp=LargoN - i + 1;
		} else {
		   intTmp=LargoN - i - 5;
		}
        Total+= parseInt(Numero.charAt(i),10) * intTmp 
    }
    
    CaracVal = 11 - (Total % 11)
    
    if(CaracVal==10) {
       return('K');
	}
	
	if(CaracVal >=0 && CaracVal <=9) {
       return(CaracVal);
	}
	
	if(CaracVal==11) {
	   return(0);
    }
}

//--------------------------------------
//function soloNumeros
//Objetivo: Verifica que existan solo numeros.
//Parametro(s):(input)String 
//(output) 1 sin son solo numeros, 0 en caso contrario
//Uso: desde fnc. verificarRut y verificarRutGeneral
//Requiere:  
//--------------------------------------
function soloNumeros(strIn)
{
  var Nros="1234567890";
  var CrtrAux;
  var iaux=0;
  for (var i=0; i < strIn.length; i++)
  {
    CrtrAux = strIn.charAt(i);
    if (Nros.indexOf(CrtrAux) != -1)
      iaux++;
  }
  if ((iaux != strIn.length) || (strIn.length==0)){
   	return 0
	}
  else
    return 1;
}

//--------------------------------------
//function enviarRut
//Objetivo: Envio de formulario
//Parametro(s):(input)String con Rut y entero tipo
//(output) submit
//Uso: desde fnc. verificarRutGeneral
//Requiere:  
//--------------------------------------
function enviarRut(strRut, tipo)
{
var navegador = navigator.appName;
var version = navigator.appVersion;
var punto = navigator.appVersion;
var total;

version = parseInt(version);
punto = "" + parseInt(punto.substring(punto.indexOf(".")+1,punto.length - 1)) + "00";
punto = punto.substring(0,2);
total = (version * 100) + parseInt(punto);
    straux = strRut.substring(0,strRut.length-1);	
    document.form1.el_rut.value = "";
    document.form1.rut.value="" + straux + digVerifIn;
    //document.form1.tipo.value=tipo;
    document.form1.submit();
    return true;
}

//--------------------------------------
//function limpiarRut
//Objetivo: Solo Limpieza de RUT de ceros a la izquierda, guiones y puntos(deja digitos y k)
//Parametro(s):(input)String con Rut
//(output) String con rut limpio
//Uso: desde fnc. verificarRutGeneral
//Requiere:  
//--------------------------------------
function limpiarRut(strRut){	
	document.form1.rut.value = document.form1.el_rut.value;
	var digVerif ="";
	var digVerifIn ="";
	var straux ="";
	var rutsgnp = "";
	while((new Number(strRut.charAt(0))==0)&&(strRut!="")){
		strRut=strRut.substring(1,strRut.length);		
	}
	for (i=0; i < strRut.length; i++)
	{
		if ((strRut.charAt(i) != ".") && (strRut.charAt(i) != "-") && (strRut.charAt(i)!=" "))
			rutsgnp= rutsgnp + strRut.charAt(i);
	   }
	return rutsgnp;
}

//--------------------------------------
//function verificarRut
//Objetivo: Solo Verificacion del string RUT
//Parametro(s):(input)String con Rut
//(output) true si el rut es correcto, false si no
//Uso: desde fnc. verificarRutGeneral
//Requiere:  fnc. solonumeros, digitoVerificador
//--------------------------------------
function verificarRut(strRut)
{	
	if (strRut != "")  
	{
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
			}
	   else 
	   {
		   alert("RUT incorrecto.");
		   submitcount=0;
		   document.form1.el_rut.value="";
		   document.form1.el_rut.select();
		   document.form1.el_rut.focus();
		   accionInterna=0;		   
		   return false;
		}
	} 
	else
	{	alert("Debe ingresar el RUT.");
		submitcount=0;
		document.form1.el_rut.value="";
		document.form1.el_rut.select();
		document.form1.el_rut.focus();
		return false;
	}
}

//--------------------------------------
//function formatearRut
//Objetivo: Formateo del RUT
//Parametro(s):(input)String con Rut
//(output) imprime rut formateado en cuadro de texto
//Uso: desde fnc. verificarRutGeneral
//Requiere:  
//--------------------------------------
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

//--------------------------------------
//function verificarRutAGeneralEnter
//Objetivo: Verificacion del rut ingresado cuando se teclea ENTER.
//va a verificarRutGeneral solo si el cuadro de clave no es vacio.
//Parametro(s):(input)String con Rut, int tipo, int action
//Uso: desde pagina
//Requiere:  verificarRutGeneral
//--------------------------------------


//--------------------------------------
//--------- Verificacion (Enter) -------
//--------------------------------------
function verificarRutGeneralEnter(strRut, tipo, action) 
{
	if (document.form1.el_rut.value!="")
		{
		verificarRutGeneral(strRut, tipo, action);
		}
	else
		{
		document.form1.el_rut.focus();
		document.form1.el_rut.select();
		alert("Debe ingresar el RUT.");				
		}
	
 return false;
}
//--------------------------------------
//function verificarRutGeneral
//Objetivo: Verificacion del rut ingresado. 
// si la accion no es envio solo formatea 
//Parametro(s):(input)String con Rut, int tipo, int action
//Uso: desde pagina
//Requiere:  limpiarRut, verificarRut, soloNumeros, enviarRut, formatearRut
//--------------------------------------

function verificarRutGeneral(strRut, tipo, action)
{
	if (strRut==""){
		alert("Debe ingresar el RUT.");
		submitcount=0;
		document.form1.el_rut.focus();
		document.form1.el_rut.select();
		}
	else{	
		//strRut=limpiarRut(strRut);
		if (verificarRut(strRut))
			{			
			enviarRut(strRut, tipo);
		
			}
		else
		{ //formatea
				formatearRut(strRut);
		}
	}
}

  
  function ingresa() 
   {

    if (document.form1.rut.value=="")
    {
       alert("Debe ingrear Rut");
       document.form1.el_rut.focus();
    }
    else
    {
      document.form1.submit();
    }
     
   }
   
  
  //-->






</script>




<script language="JavaScript" type="text/JavaScript">
<!--


function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_showHideLayers() { //v6.0
  var i,p,v,obj,args=MM_showHideLayers.arguments;
  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
    if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
    obj.visibility=v; }
}

//-->
</script>
<script src="js/calendario.js"></script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">

</head>

<body bgcolor="#FFFFFF" >
<center>
<form name="form1" method="Post" action="guardar_cargas.php">
    <table width="623" height="30" border="1" cellpadding="1" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td width="610" bgcolor="#009900"> 
          <div align="center"><font color="#FFFFFF" size="4"><strong>CARGAS FAMILIARES</strong></font></div></td>
      </tr>
    </table>
    <table width="610" border="1" cellpadding="1" cellspacing="0">
      <tr> 
        <td width="656"  align="center"> 
          <table width="610" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="320" class="texto"><strong><font color="#000000">DATOS 
                DE LA CARGA</font></strong></td>
            <td width="322"><div align="right"><strong><font color="#0000A0" size="2"> 
                  </font></strong></div></td>
          </tr>
        </table>
          <table width="65%" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td width="651"  align="center"> 
                <div align="center"> 
                  <table width="610" border="0" cellpadding="1" cellspacing="1">
                    <tr> 
                      <td height="32"><font size="2" face="Arial, Helvetica, sans-serif">Ru</font>t</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><strong>
                        <input name="txtrut" type="text" id="txtrut" size="12" maxlength="12">
                        </strong></font></td>
                      <td> <input type="text" name="el_rut" maxlength="12" size="10" onChange="verificarRutGeneral(document.form1.el_rut.value,0,0);" > 
	                       <input type="hidden" name="rut" maxlength="12"> </td>
                      
                    </tr>
                    <tr> 
                      <td width="113" height="32"><font size="2" face="Arial, Helvetica, sans-serif">Apellido 
                        Paterno </font></td>
                      <td width="153"><font size="2" face="Arial, Helvetica, sans-serif"><strong> 
                        <input name="txtpaterno" type="text" id="txtpaterno" size="25" maxlength="30">
                        </strong> </font></td>
                      <td width="112"><font size="2" face="Arial, Helvetica, sans-serif">Apellido 
                        Materno </font></td>
                      <td width="219"><font size="2" face="Arial, Helvetica, sans-serif"><strong> 
                        <input name="txtmaterno" type="text" id="txtmaterno" size="25" maxlength="30">
                        </strong></font></td>
                    </tr>
                    <tr> 
                      <td height="32"><font size="2" face="Arial, Helvetica, sans-serif">Nombres</font></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"> 
                        <input name="txtnombres" type="text" id="txtnombres" size="25" maxlength="30">
                        </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><strong> 
                        </strong></font></td>
                    </tr>
                  </table>
                  <table width="610" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="111"><font size="2" face="Arial, Helvetica, sans-serif">Fecha 
                        Nacimiento </font></td>
                      <td width="492"><font color="#000000" size="2" face="Arial, Helvetica, sans-serif">
                        <input name="Txt_fecha_doc" type="text" class="entradas" id="Txt_fecha_doc2" value="<?=$fecha_x?>" size="10" maxlength="10">
                        <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="imagen/icon-calen_f2.gif" width="20" height="21" border="0" name="calenda"></a></font><font size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font></td>
                    </tr>
                  </table>
                  <table width="610" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td><font size="2" face="Arial, Helvetica, sans-serif">Tipo 
                        Carga </font></td>
                      <td width="261"><font size="2" face="Arial, Helvetica, sans-serif"> 
                        <select name="cbo_tipo" id="cbo_tipo">
						<?php	
							while($reg_tipo=mssql_fetch_array($rs_tipo))
						{
			    		echo "<option value=" . $reg_tipo[cod_tipo_carga];
						echo ">" . $reg_tipo[desc_carga] . "</option>\n";
            			}
						?>
                        </select>
                        </font></td>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr> 
                      <td height="26"><font size="2" face="Arial, Helvetica, sans-serif">Sexo</font></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"> 
                        Femenino 
                        <input type="radio" name="radiosexo" value="F">
                        Masculino 
                        <input type="radio" name="radiosexo" value="M">
                        </font></td>
                      <td colspan="2"><font size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                    </tr>
                    <tr> 
                      <td width="110">&nbsp;</td>
                      <td colspan="2"> <font color="#000000" size="2" face="Arial, Helvetica, sans-serif"> 
                        &nbsp; </font></td>
                      <td width="228"> <div align="left"></div></td>
                    </tr>
                  </table>
                  
                </div></td>
            </tr>
          </table>
		  <table width="614" border="1" cellspacing="0" cellpadding="1">
            <tr> 
              <td width="75%">&nbsp;</td>
            </tr>
          </table>
          <table width="610" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr> 
              <td height="37" width="194"> <div align="center"> 
                  <input type="hidden" name="rut" value="<? echo $rut;?>">
				  <input type="hidden" name="rut_carga" value="<? echo $rut;?>">
                 
				                   
                </div></td>
              <td width="146"><div align="center">
                  <input name="cmd_grabar" type="button" class="botones" onClick="validar_datos();" value="Guardar">
                </div></td>
              <td width="270"><input name="cmd_salir" type="button" class="botones" id="cmd_salir" onClick="validar_datos();" value="Salir"></td>
            </tr>
          </table>
      </td>
    </tr>
  </table>
  </form>
  
<?php mssql_close($cn);?>
</center>
</body>
</html>
