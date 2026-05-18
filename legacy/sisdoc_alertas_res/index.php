<?php
if(!isset($cont))
{
$cont=0;
}
//echo "cont: ".$cont;
?>
<HTML>
<HEAD>
<TITLE>Sistema de Documentos</TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">

  <script language="javascript">
  //<!--

//--------------------------------------
//function digitoVerificador
//Objetivo: Retornar el Digito verificador de un RUT.
//Parametro(s):(input)String de ingreso de rut (incluyendo el DV).
//(output) DV obtenido
//Uso: desde fnc. verificarRut
//Requiere:  
//--------------------------------------

function muestra_cuadro() { 
var flujo2= <?php echo $cont; ?>;  
   if (flujo2==2) {
  
  alert("Datos incorrectos");
  }
 
asample1()
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
    document.formurut.el_rut.value = "";
    document.formurut.clave.value = "";
    document.formurut.rut.value="" + straux + digVerifIn;
    document.formurut.tipo.value=tipo;
    document.formurut.submit();
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
	document.formurut.rut.value = document.formurut.el_rut.value;
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
		   document.formurut.el_rut.value="";
		   document.formurut.clave.value="";
		   document.formurut.clave.blur();
		   //document.formurut.el_rut.focus();
		   document.formurut.el_rut.select();
		   document.formurut.el_rut.focus();
		   accionInterna=0;		   
		   return false;
		}
	} 
	else
	{	alert("Debe ingresar el RUT.");
		submitcount=0;
		document.formurut.el_rut.value="";
		document.formurut.clave.blur();
		//document.formurut.el_rut.focus();
		document.formurut.el_rut.select();
		document.formurut.el_rut.focus();
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
	document.formurut.el_rut.value=rutsgnp;
	document.formurut.el_rut.blur();
	document.formurut.clave.focus();
	document.formurut.clave.select();
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
		if (document.formurut.clave.value!=""){
			if (conta()){		
			 	verificarRutGeneral(strRut, tipo, action);
			}else{
				alert("Complete todos los datos, por favor.");
			}				
		}else{
			if (document.formurut.el_rut.value!=""){
				if (accionInterna==1){
					document.formurut.clave.focus();
					document.formurut.clave.select();
					alert("Debe ingresar la clave");
				}else{
					document.formurut.clave.blur();
					document.formurut.el_rut.focus();
					document.formurut.el_rut.select();
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
		document.formurut.clave.blur();
		document.formurut.el_rut.focus();
		document.formurut.el_rut.select();
		}
	else{	
		strRut=limpiarRut(strRut);
		if (verificarRut(strRut)){			
			if (action==1){ //envia
				if (document.formurut.clave.value!=""){
					if (document.formurut.clave.value.length>1){						
						enviarRut(strRut, tipo);
					}else{
						alert("La clave debe poseer un largo de al menos dos dgitos");
						submitcount=0;
						document.formurut.clave.value="";
						document.formurut.clave.focus();
						document.formurut.clave.select();
						return;
					}
				}else{
					alert("Debe ingresar la clave.");
					submitcount=0;
					document.formurut.el_rut.blur();
					document.formurut.clave.focus();
					document.formurut.clave.select();
				}
			}else{ //formatea
				formatearRut(strRut);
				document.formurut.clave.select();				
			}
		}
	}
}


  
  function ingresa() 
   {

    if (document.formurut.rut.value=="" || document.formurut.clave.value=="" )
    {
       alert("Debe ingrear Rut y Clave");
       document.formurut.el_rut.focus();
    }
    else
    {
      document.formurut.submit();
    }
     
   }
   
  
  //-->

  
  </script>
<script>
	/* if (window.XMLHttpRequest) { 
	   // IE 7, mozilla, safari, opera 9 typeof document.body.style.maxHeight != "undefined"
	   alert ("IE 7, mozilla, safari, opera 9 ");
	} else { 
	   // IE6, older browsers 
	   alert (" IE6, older browsers ");   
	} */
</script>

</head>  
<BODY marginwidth="0" marginheight="0" onLoad="javascript:muestra_cuadro();">
<p>&nbsp;</p>
<table width="390" align="center" cellpadding="1" cellspacing="1">
  <tr> 
    <td width="384"><div align="center"> 
        <p><b><font color="#0000A0" size="5" >Sistema de Registro y Seguimiento 
          de Correspondencia(desarrollo)<br>
          </font></b><b><font size="5" ><img src="imagen/logo_chico.gif" width="96" height="95"></font></b></p>
        </div>
      </td>
  </tr>
</table>
<table width="390" align="center" cellpadding="1" cellspacing="1">
  <tr>
    <td height="233"> 
      <div id=isample1b style="VISIBILITY: hidden; OVERFLOW: hidden; POSITION: absolute; TOP: -1000px; HEIGHT: 10px; left: 19px;"> 
        <div id=isample1j style="LEFT: 0px; VISIBILITY: hidden; POSITION: absolute; TOP: 0px; HEIGHT: 17px"> 
          <span id=isample1k> 
          <table cellspacing=0 cellpadding=0 border=0>
            <tbody>
              <tr> 
                <td valign=center width=5 height=15> </td>
                <td style="FONT-SIZE: 11px; COLOR: white; FONT-FAMILY: verdana,arial" valign=center nowrap height=15> 
                  <!--img height=6 src="file://///Linux80/ximena/images/arrow_test.gif" width=6 border=0>&nbsp;&nbsp;Ingrese 
                  su RUT y contrase&ntilde;a </td-->
                <img height=6 src="images/arrow_test.gif" width=6 border=0>&nbsp;&nbsp;Ingrese 
                  su RUT y contrase&ntilde;a </td>
              </tr>
            </tbody>
          </table>
          </span> <span id=lsample1 style="LEFT: 230px; WIDTH: 11px; CURSOR: hand; POSITION: absolute; TOP: 2px; HEIGHT: 11px"> 
          <!--img height=11 src="file://///Linux80/ximena/images/closebox_small.gif" width=11> </span> </div-->
          <img height=11 src="images/closebox_small.gif" width=11> </span> </div>
        <div id=isample1m style="VISIBILITY: hidden; POSITION: absolute; bgcolor: 'Color [A=255, R=239, G=243, B=247]'"> 
          <form action="verifica.php" name="formurut" method="post">
            <table cellspacing=0 cellpadding=0 border=0>
              <tbody>
                <tr> 
                  <td valign=top width=8><img height=3 hspace=2 src="" width=3 vspace=6> 
                    &nbsp; </td>
                  <td nowrap><font style="FONT-SIZE: 10px; FONT-FAMILY: verdana,arial"> 
                    RUT :&nbsp;</font> </td>
                  <td nowrap> <input type="text" name="el_rut" maxlength="12" size="10" onChange="verificarRutGeneral(document.formurut.el_rut.value,0,0);" > 
                    <input type="hidden" name="rut" maxlength="12"> </td>
                </tr>
                <tr> 
                  <td>&nbsp; </td>
                  <td nowrap> <font style="FONT-SIZE: 10px; FONT-FAMILY: verdana,arial"> 
                    Contrase&ntilde;a :&nbsp;</font> </td>
                  <td nowrap> <input type="password" name="clave" maxlength="4" size="4"> 
                    <input type="hidden" name="tipo"> <br> &nbsp; </td>
                </tr>
                <tr align="center"> 
                  <td colspan="3"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="hidden" name="cont" value="<? echo $cont;?>"> 
                    <input type="button" style="color:#FFFFFF;background-color:#6B8EC6;" name="inGRESAR" value="Ingresar" onClick="ingresa()" /> 
                    <br> </td>
                </tr>
              </tbody>
            </table>
          </form>
        </div>
        <div id=isample1n style="VISIBILITY: hidden; OVERFLOW: hidden; POSITION: absolute; TOP: -1000px"> 
          <span id=isample1o> 
          <table cellspacing=0 cellpadding=0 border=0>
            <tbody>
              <tr> 
                <td valign=center width=5 height=15> </td>
                <td style="FONT-SIZE: 9px; COLOR: black; FONT-FAMILY: verdana,arial" valign=center nowrap height=7> 
                  <!--img height=7 src="file://///Linux80/ximena/images/arr_silver.gif" width=9 border=0--> 
                  <img height=7 src="images/arr_silver.gif" width=9 border=0> 
                  &nbsp;<b>Departamento de Inform&aacute;tica<BR><center>Clave: Marcación Reloj control</center></B></td>
            </tbody>
          </table>
          </span> <span id=isample1p style="LEFT: 100px; WIDTH: 15px; CURSOR: nw-resize; POSITION: absolute; TOP: 10px; HEIGHT: 15px"> 
          <!--img height=15 src="file://///Linux80/ximena/images/resize_blue.gif" width=15> </span> </div-->
          <img height=15 src="images/resize_blue.gif" width=15> </span> </div>
      </div>
      <SCRIPT language=javascript>
<!--

 var q	= '';
 var r = '';  
 var s	= 'no';  
 var t	= 'no';  
 var psample1 = null;  
 var nsample1 = null;  
 var fprvShadowsample1 = null;  
 var bsample1;  
 var jsample1;  
 var msample1 = null;
 var usample1 = null;
 var v;   
 wsample1=0; 
 xsample1=0;   
 ysample1=0; 
 zsample1=0;  
 aa=1; 
 ab = 0; 
 ac=0; 
 function dsample1(ad)
 {
    document.getElementById('isample1k').innerHTML = "<table border=0 cellpadding=0 cellspacing=0><tr><td valign=middle height=15 width=5></td><td style='FONT-SIZE: 11px; COLOR: white; FONT-FAMILY: verdana,arial' nowrap valign=middle height=15>" + ad + "</td></tr></table>";
 }
 function esample1(ae)
 {
   document.getElementById('isample1k').innerHTML =  ae ;
 }
 function fsample1(af)
 {
   document.getElementById('isample1m').innerHTML =  af ;
 }
 function gsample1(StatusBarText)
 {
   document.getElementById('isample1o').innerHTML = "<table border=0 cellpadding=0 cellspacing=0><tr><td valign=middle height=15 width=5></td><td style='FONT-SIZE: 9px; COLOR: black; FONT-FAMILY: verdana,arial' nowrap valign=middle height=15>" + StatusBarText + "</td></tr></table>";
 }
 function hsample1(StatusBarHTML)
 {
   document.getElementById('isample1o').innerHTML =  StatusBarHTML ;
 }
 function ag(ah)
 {
   if(document.getElementById('i' + ah +'fprvShadow') == null) 
     return;
   document.getElementById('i' + ah +'b').style.left = parseInt(document.getElementById('i' + ah +'b').style.left) +2  + 'px'; 
   document.getElementById('i' + ah +'b').style.top = parseInt(document.getElementById('i' + ah +'b').style.top) +2 + 'px';
   document.getElementById('i' + ah +'fprvShadow').style.left = parseInt(document.getElementById('i' + ah +'fprvShadow').style.left) +2 +'px';
   document.getElementById('i' + ah +'fprvShadow').style.top =  parseInt(document.getElementById('i' + ah +'fprvShadow' ).style.top) +2 + 'px';
	document.getElementById('i' + ah +'fprvShadow').style.visibility='hidden';
 }
 function ai(ah)
 {
   if(document.getElementById('i' + ah +'fprvShadow') == null) 
     return;
   document.getElementById('i' + ah +'b').style.left = parseInt(document.getElementById('i' + ah +'b').style.left) -2  + 'px';
   document.getElementById('i' + ah +'b').style.top = parseInt(document.getElementById('i' + ah +'b').style.top) -2 + 'px';
   document.getElementById('i' + ah +'fprvShadow').style.left = parseInt(document.getElementById('i' + ah +'fprvShadow' ).style.left) -2 +'px';
   document.getElementById('i' + ah +'fprvShadow').style.top = parseInt(document.getElementById('i' + ah +'fprvShadow' ).style.top) -2 + 'px';
   document.getElementById('i' + ah +'fprvShadow').style.visibility='visible';
 }
 function ajsample1()
 { 
    jsample1.style.backgroundColor="#6B8EC6";
    bsample1.style.borderColor="#6B8EC6";
    jsample1.style.borderColor="#6B8EC6";
    nsample1.style.borderColor="#6B8EC6";
    nsample1.style.backgroundColor='#CCCCCC';
    msample1.style.borderColor="#6B8EC6";
    msample1.style.backgroundColor="#EFF3F7"; 
 }
 function aksample1()
 { 
 }
 function csample1() 
 {
   bsample1.style.visibility='hidden';
   msample1.style.visibility='hidden';
   jsample1.style.visibility='hidden';
   psample1.style.visibility='hidden';
   nsample1.style.visibility='hidden';
   r = ''; 
 }
 function alsample1() 
 {
   bsample1.style.visibility='visible';
   msample1.style.visibility='visible';
   jsample1.style.visibility='visible';
   psample1.style.visibility='visible';
   nsample1.style.visibility='visible';
   if(q!='sample1') 
   {
     r = q;
     q = 'sample1';
     ajsample1();
     window.setTimeout('ak' + r + '()',1);
     bsample1.style.zIndex=++aa;
     psample1.style.zIndex=aa;
   }
 }
 function amsample1(x,y,m)
 {
    var an = m;
    an = true;
    var ao = m;
    ao = true;
    if(an) bsample1.style.left	= x + 'px';
    if(ao) bsample1.style.top	= y + 'px';
  }
  function apsample1(w,h,r)
  {
    w = Math.max(w , 15);
    h = Math.max(h ,33);
    //h = 54;
    var aq = r;
    aq = true;
    var ar = r;
    ar = true;
    if(ar) bsample1.style.width=w + 'px';
    if(aq) bsample1.style.height=h + 'px';
    if(ar) usample1.style.left=(w-15)+'px';
    if(aq) usample1.style.top=2+'px';
    if(ar) jsample1.style.width=w+'px'; 
    if(ar) msample1.style.width=w+'px';
    if(aq) msample1.style.height=h-32+'px';
    if(ar) nsample1.style.width = w+'px';
    if(aq) nsample1.style.top = h - 27 + 'px';
    if(ar) psample1.style.left	= w - 17 +'px';
  }
  function assample1()
  {
    atsample1();
    if(t == 'yes' || s == 'yes' ) return false;
  }
  function atsample1()
  {
    if(q!='sample1') return false;
    if(s == 'yes')
    {
      var x= ab+wsample1;
      var y= ac+xsample1;
      amsample1(x,y,false);
    }
    if(t == 'yes')
    {
      var au=ab + ysample1;
      var av=ac + zsample1;
      apsample1(au,av,false);
    }
  }
  function awsample1()
  {
    var ax = event.clientX+document.body.scrollLeft;
    var ay = event.clientY+document.body.scrollTop;
    ysample1=parseInt(bsample1.style.width)-ax;
    zsample1=parseInt(bsample1.style.height)-ay;
    if(q!='sample1')
    {
      r = q;
      q = 'sample1';
      bsample1.style.zIndex=++aa;
      psample1.style.zIndex=aa;
    }
    q = 'sample1';
    if(r!='')
    {
      ajsample1();
      window.setTimeout('ak' + r + '()',1);
    }
    t = 'yes';
    return false;
  }
  function azsample1()
  {
    var ax = event.clientX + document.body.scrollLeft;
    var ay = event.clientY + document.body.scrollTop;
    wsample1 = parseInt(bsample1.style.left)-ax;
    xsample1 = parseInt(bsample1.style.top)-ay;
    if(q!='sample1')
    {
      r = q;
      q = 'sample1';
      ajsample1();
      window.setTimeout('ak' + r + '()',1);
      bsample1.style.zIndex=++aa;
      psample1.style.zIndex=aa;
    }
    s = 'yes';
    return false;
  }
  function basample1()
  { 
    if(q!='sample1')
    {
      r = q;
      q = 'sample1';
      ajsample1();
      window.setTimeout('ak' + r + '()',1);
      bsample1.style.zIndex=++aa;
      psample1.style.zIndex=aa;
    }
  }
  function asample1()
  {
    var x=0;
    var y=0;
    var h=190;
    var w=230;
    y = document.body.offsetHeight/2 - 210/2 + document.body.scrollTop+100;
    x = document.body.offsetWidth/2 - 250/2 + document.body.scrollLeft;
    if(msample1 != null)
    {  
      alsample1();
      apsample1(w,h,true);
      amsample1(x,y,true);
      return; 
    }
    psample1 = document.getElementById('isample1p');
    psample1.style.cursor='nw-resize';
    psample1.style.visibility='visible';
    bsample1 = document.getElementById('isample1b');
    bsample1.style.borderStyle='solid';
    bsample1.style.borderWidth='0px';
    bsample1.style.borderColor='#6B8EC6';
    bsample1.style.visibility='visible';
    bsample1.style.zIndex=++aa;
    jsample1 = document.getElementById('isample1j');
    jsample1.style.visibility='visible';
    jsample1.style.backgroundColor='#6B8EC6';
    jsample1.style.cursor='default';
    jsample1.style.borderColor = '#6B8EC6';
    jsample1.style.borderStyle='solid';
    jsample1.style.borderWidth='1px';
    jsample1.style.overflow="hidden";
    msample1 = document.getElementById('isample1m');
    msample1.style.visibility='visible';
    msample1.style.left=0+'px';
    msample1.style.top=16+'px';
    msample1.style.backgroundColor='#EFF3F7';
    msample1.style.borderColor = '#6B8EC6';
    msample1.style.borderStyle='solid';
    msample1.style.borderWidth='1px';
    msample1.style.overflow='auto';
    msample1.style.padding='13px 13px 13px 13px';
    nsample1 = document.getElementById('isample1n');
    nsample1.style.left=0+'px';
    nsample1.style.backgroundColor='#CCCCCC';
    nsample1.style.visibility='visible';
    nsample1.style.height=27;
    nsample1.style.borderColor = '#6B8EC6';
    nsample1.style.borderStyle='solid';
    nsample1.style.borderWidth='1px';
    nsample1.style.zIndex=++aa;
    usample1 = document.getElementById('lsample1');
    if(q != '' && q !='sample1')
    {
      r = q;
    }
    q = 'sample1';
    if(r != '')
    { 
      ajsample1();
      window.setTimeout('ak' + r + '()',1);
    }
    usample1.onclick = csample1;
    psample1.onmousedown = awsample1;
    bsample1.onmousedown = basample1;
    jsample1.onmousedown= azsample1; apsample1(w,h,true);
    amsample1(x,y,true);
    psample1.style.zIndex=++aa;
 }
 document.onmousemove = new Function("if(q!=''){if(event.button !=1 ){if (s == 'yes') ai(q); if (t == 'yes') ai(q); t = 'no' ; s = 'no' ; return true;}ab = event.clientX+document.body.scrollLeft;ac= event.clientY+document.body.scrollTop;window.setTimeout('as' + q + '()',1);if(t == 'yes' || s == 'yes' ){event.returnValue = false; return false;} }") 
 document.onmouseup = new Function("if (s == 'yes') ai(q); if (t == 'yes') ai(q); t = 'no';  s = 'no'; ");
// --> 
</SCRIPT></td>
  </tr>
</table>
<p align="center"><b><font size="5"></font></b></p>
<p>&nbsp; </p>
</BODY></HTML>
