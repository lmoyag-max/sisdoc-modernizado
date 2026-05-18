<?php
include("conexion_bd.php");
//include("carga_tablas.php");
$ok2=$ok;
$rut_fun=$rut;
$rut_c=$rut_c;
$clave = 1000;
//echo "rutfun" . $rut_fun . "rut_c " . $rut_c;
$query_carga="exec busca_carga '" . $rut_fun . "'";
$rs_carga=mssql_query($query_carga,$cc); 
$filas_carga=mssql_num_rows($rs_carga);
 
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
var flujo2= <?php echo $ok2; ?>;  

function muestra_cuadro()
 { 
  if (flujo2==0) {
  alert("La Carga Familiar ha sido ingresada correctamente");
  document.form1.el_rut.focus();
  }
  else 
  {
  //alert("nada ");
  }	
  }
function enviar_bienestar()
{
	document.form1.action="mail_bienestar.php";
	document.form1.submit();
}

function imprime_solicitud()
{
	document.form1.action="imprime_respuesta.php";
	document.form1.submit();
}

function existe_rut(cod)
{
var valor= "er";
parent.frames[0].location.href="frame_consultas.php?cod_dep="+cod+"&sw="+valor;
document.form1.txtpaterno.focus();
}

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

function digitoVerificador(strRut) {
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
function soloNumeros(strIn) {
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
function enviarRut(strRut, tipo){
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
   // document.form1.clave.value = "";
    document.form1.rut.value="" + straux + digVerifIn;
    document.form1.tipo.value=tipo;
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
	document.form1.el_rut.value = document.form1.el_rut.value;
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
			existe_rut(document.form1.el_rut.value);
			return true;
			
			}
	   else 
	   {
		   alert("RUT incorrecto.");
		   submitcount=0;
		   //document.form1.el_rut.value="";
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
		//document.form1.clave.blur();
		//document.form1.el_rut.focus();
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
	//document.form1.clave.focus();
	//document.form1.clave.select();
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
function verificarRutGeneralEnter(strRut, tipo, action) {
		if (document.form1.clave.value!=""){
			if (conta()){		
			 	verificarRutGeneral(strRut, tipo, action);
			}else{
				alert("Complete todos los datos, por favor.");
			}				
		}else{
			if (document.form1.el_rut.value!=""){
				if (accionInterna==1){
		//			document.form1.clave.focus();
			//		document.form1.clave.select();
					alert("Debe ingresar la clave");
				}else{
				//	document.form1.clave.blur();
					document.form1.el_rut.focus();
					document.form1.el_rut.select();
					alert("Debe ingresar el RUT.");				
				}

			}

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

function verificarRutGeneral(strRut, tipo, action){
	if (strRut==""){
		alert("Debe ingresar el RUT.");
		submitcount=0;
		//document.form1.clave.blur();
		document.form1.el_rut.focus();
		document.form1.el_rut.select();
		}
	else{	
		strRut=limpiarRut(strRut);
		if (verificarRut(strRut)){			
			if (action==1){ //envia
				if (document.form1.clave.value!=""){
					if (document.form1.clave.value.length>1){						
						enviarRut(strRut, tipo);
					}else{
						alert("La clave debe poseer un largo de al menos dos dgitos");
						submitcount=0;
			//			document.form1.clave.value="";
				//		document.form1.clave.focus();
				//		document.form1.clave.select();
						return;
					}
				}else{
					alert("Debe ingresar la clave.");
					submitcount=0;
					document.form1.el_rut.blur();
				//	document.form1.clave.focus();
				//	document.form1.clave.select();
				}
			}else{ //formatea
				formatearRut(strRut);
		//		document.form1.clave.select();				
			}
		}
	}
}


  
 function ingresa() 
   {

    if (document.form1.rut.value=="" || document.form1.clave.value=="" )
    {
       alert("Debe ingrear Rut y Clave");
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

<body bgcolor="#FFFFFF" topmargin="0" onLoad="muestra_cuadro()">
<center>
<form name="form1" method="Post" action="guardar_cargas.php">
    <table width="660" height="30" border="1" cellpadding="1" cellspacing="0" bgcolor="#009900">
      <tr>
        <td width="610" bgcolor="#009900"> 
          <div align="center"><font color="#FFFFFF" size="4"><strong>CARGAS FAMILIARES</strong></font></div></td>
      </tr>
    </table>
    <table width="660" border="1" cellpadding="1" cellspacing="0" bgcolor="#FFFFCC">
      <tr> 
        <td width="656"  align="center"> 
          <table width="650" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="317" class="texto"><strong><font color="#A03D4B"></font></strong></td>
              <td width="233"><div align="right"><strong><font color="#0000A0" size="2">Rut 
                  Beneficiario </font></strong></div></td>
              <td width="90"><font color="#0000A0"><strong><? echo $rut_c;?></strong></font></td>
            </tr>
          </table>
          <table width="650" align="center" border="1" cellspacing="1" cellpadding="0" bgcolor="#FFFFCC">
            <?php
$cont=0;
if($filas_carga>0)
{
while($reg_carga=mssql_fetch_array($rs_carga))
{
$cont=$cont + 1;
$nom_carga="";
$rut_carga="";
$nom_carga=rtrim($reg_carga[nombres_carga]) . " " . rtrim($reg_carga[ape_pat_carga]) . " " . rtrim($reg_carga[ape_mat_carga]);
$rut_carga=$reg_carga[rut_carga] . "-" . $reg_carga[dv_carga];
$fec_carga=$reg_carga[fecha_nac];
//substr($reg_bienestar[fecha_nac],0,2) . "-" . substr($reg_bienestar[fecha_nac],3,2)  . "-" . substr($reg_bienestar[fecha_nac],6,4);
if($cont==1)
{
?>
            <tr bgcolor="#8BE686"> 
              <td width="30"  valign="middle"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Nº</font></strong></td>
              <td width="80"  valign="middle"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">RUT</font></strong></td>
              <td width="350"  valign="middle"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">NOMBRE</font></strong></td>
              <td width="40"  valign="middle"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">SEXO</font></strong></td>
              <td width="80"  valign="middle"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">RELACION</font></strong></td>
              <td width="70"  valign="middle"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">FEC. 
                NAC</font></strong></td>
            </tr>
            <? } ?>
            <tr bgcolor="#F8FCDA"> 
              <td width="30"  valign="middle"><font size="2" face="Arial, Helvetica, sans-serif"><? echo $cont;?></font></td>
              <td width="80"  valign="middle"><font size="2" face="Arial, Helvetica, sans-serif"><? echo $rut_carga;?></font></td>
              <td width="350"  valign="middle"><font size="2" face="Arial, Helvetica, sans-serif"><? echo $nom_carga;?></font></td>
              <td width="40"  valign="middle"><font size="2" face="Arial, Helvetica, sans-serif"><? echo $reg_carga[sexo_carga];?></font></td>
              <td width="80"  valign="middle"><font size="2" face="Arial, Helvetica, sans-serif"><? echo $reg_carga[desc_carga];?></font></td>
              <td width="70"  valign="middle"><font size="2" face="Arial, Helvetica, sans-serif"><? echo $fec_carga;?></font></td>
            </tr>
            <?  } 
			}?>
          </table>
          <table width="650" border="0"  align="center" cellspacing="1" cellpadding="1">
            <tr> 
              <td width="317" class="texto"><strong><font color="#A03D4B">INGRESO 
                NUEVAS CARGAS</font></strong></td>
              <td width="90"><font color="#0000A0">&nbsp;</font></td>
            </tr>
          </table>
          <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#F8FCDA">
            <tr> 
              <td width="651"  align="center"> 
                <div align="center"> 
                  <table width="645" border="0" cellpadding="1" cellspacing="1">
                    <tr> 
                      <td height="32"><font size="2" face="Arial, Helvetica, sans-serif">Ru</font>t</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><strong> 
                        <input type="text" name="el_rut" maxlength="12" size="10" onChange="verificarRutGeneral(document.form1.el_rut.value,0,0);" >
                        <input type="hidden" name="rut2" maxlength="12">
                        <input type="hidden" name="clave" value="<? echo $clave;?>">
                        </strong></font></td>
                      <td>&nbsp; </td>
                    </tr>
                    <tr> 
                      <td width="113" height="32"><font size="2" face="Arial, Helvetica, sans-serif">Apellido 
                        Paterno </font></td>
                      <td width="153"><font size="2" face="Arial, Helvetica, sans-serif"><strong> 
                        <input name="txtpaterno" type="text" id="txtpaterno3" size="25" maxlength="30">
                        </strong> </font></td>
                      <td width="112"><font size="2" face="Arial, Helvetica, sans-serif">Apellido 
                        Materno </font></td>
                      <td width="219"><font size="2" face="Arial, Helvetica, sans-serif"><strong> 
                        <input name="txtmaterno" type="text" id="txtmaterno3" size="25" maxlength="30">
                        </strong></font></td>
                    </tr>
                    <tr> 
                      <td height="32"><font size="2" face="Arial, Helvetica, sans-serif">Nombres</font></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="txtnombres" type="text" id="txtnombres4" size="25" maxlength="30">
                        <strong> </strong> </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><strong> 
                        </strong></font></td>
                    </tr>
                  </table>
                  <table width="645" border="0" cellspacing="1" cellpadding="1">
                    <tr> 
                      <td width="111"><font size="2" face="Arial, Helvetica, sans-serif">Fecha 
                        Nacimiento </font></td>
                      <td width="492"><font color="#000000" size="2" face="Arial, Helvetica, sans-serif"> 
                        <input name="Txt_fecha_doc" type="text" class="entradas" id="Txt_fecha_doc2" value="<?=$fecha_x?>" size="10" maxlength="10">
                        </font><font size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font><font color="#000000" size="2" face="Arial, Helvetica, sans-serif"> 
                        <a href="javascript:show_Calendario('form1.Txt_fecha_doc');"><img src="imagen/icon-calen_f2.gif" width="20" height="21" border="0" name="calenda"></a></font><font size="2" face="Arial, Helvetica, sans-serif">&nbsp; 
                        </font></td>
                    </tr>
                  </table>
                  <table width="645" border="0" cellspacing="1" cellpadding="1">
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
		  <table width="645" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr> 
              <td height="37" width="205"> <div align="center"> 
                  <input type="hidden" name="rut_fun" value="<? echo $rut_fun;?>">
				  <input type="hidden" name="rut" value="<? echo $rut_fun;?>">
				  <input type="hidden" name="rut_c" value="<? echo $rut_c;?>">             
				   <input type="hidden" name="ok" value="<? echo $ok2;?>">
                  <input name="cmd_grabar" type="button" class="botones" onClick="validar_datos();" value="Guardar Carga ">
                </div></td>
              <td width="212"><div align="center">
                  <input name="cmd_envia" type="button" class="botones" id="cmd_envia2" value="Enviar a Bienestar" onClick="enviar_bienestar();">
                </div></td>
              <td width="228"><div align="center">
                  <input type="submit" name="cmd_imprimir" value="Imprmir" onClick="imprime_solicitud();">
                </div></td>
            </tr>
          </table>
      </td>
    </tr>
  </table>
  </form>
  
<?php mssql_close($cc);?>
</center>
</body>
</html>
