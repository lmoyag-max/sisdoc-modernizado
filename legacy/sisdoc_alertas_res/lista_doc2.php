<?php
	//phpinfo();
/***************************************************************************************************************/
	$txtann = date("Y");
	//include("frame_menu_ofpartes_datos.php");
	$num_array=count($array_verde);
	$num_array=count($array_amarillo);
	$num_array=count($array_rojo);

	$valor=$temp;
	//echo $valor;
	$nombre_array=array();
	$num_array=0;
	if ($valor==1){
		$nombre_array="array_verde";
		$nombre_idseg="verde_idseg";
		$nombre_nomina="verde_nomina";
		$nombre_desc_tipo_doc="verde_desc_tipo_doc";
		$nombre_num_externo="verde_num_externo";
		$nombre_fecha_documento="verde_fecha_documento";
		$nombre_materia="verde_materia";
		$nombre_num_interno="verde_num_interno";
		$num_array=count($array_verde);
		$titulo="#66FF00";
	}
	if ($valor==2){
		$nombre_array="array_amarillo";
		$nombre_idseg="amarillo_idseg";
		$nombre_nomina="amarillo_nomina";
		$nombre_desc_tipo_doc="amarillo_desc_tipo_doc";
		$nombre_num_externo="amarillo_num_externo";
		$nombre_fecha_documento="amarillo_fecha_documento";
		$nombre_materia="amarillo_materia";
		$nombre_num_interno="amarillo_num_interno";
		$num_array=count($array_amarillo);
		$titulo="#FFFF00";
	}
	if ($valor==3){
		$nombre_array="array_rojo";
		$nombre_idseg="rojo_idseg";
		$nombre_nomina="rojo_nomina";
		$nombre_desc_tipo_doc="rojo_desc_tipo_doc";
		$nombre_num_externo="rojo_num_externo";
		$nombre_fecha_documento="rojo_fecha_documento";
		$nombre_materia="rojo_materia";
		$nombre_num_interno="rojo_num_interno";
		$num_array=count($array_rojo);
		$titulo="#FF0000";
	}
	/********************************************/
	$nombre_frame="";
	if ($tipo_frame=="1"){
		$nombre_frame="frame_menuvars";
	}
	if ($tipo_frame=="2"){
		$nombre_frame="frame_menu_ofpartes";
	}
	
	
	//echo "nombre_frame: ".$nombre_frame."<br>";
	//echo "nombre_array: ".$nombre_array."<br>";
	//echo "***funcionario:" . $idfuncionario . " ***cusuario:" . $cusuario . " **** idusuario:" . $idusuario . " **** id_dependencia:" . $id_dependencia."<br>";
/***************************************************************************************************************/
/***************************************************************************************************************/

?> 

<html>
<head>
<META Http-Equiv="Cache-Control" Content="no-cache">
<META Http-Equiv="Pragma" Content="no-cache">
<META Http-Equiv="Expires" Content="0">
<title>Documentos</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript">
<!--
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
// -->

function MM_findObj(n, d) { //v4.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && document.getElementById) x=document.getElementById(n); return x;
}

function MM_showHideLayers() { //v3.0
  var a,i,p,v,obj,args=MM_showHideLayers.arguments;
   ocultalayer(args[3],args[4]);
  for (i=0; i<(args.length-4); i+=3) 
  if ((obj=MM_findObj(args[i]))!=null) 
      { v=args[i+2];
    if (obj.style) 
	    { obj=obj.style; v=(v=='show')?'visible':(v='hide')?'hidden':v; }
    obj.visibility=v; }		
  }
//-->
function ocultalayer(idlay,totlay){
var idlay, a;
var start=totlay-20;
	if (start<0){
		start=1;
	}else{
		if (((start+20)%20)!=0){
			start=(Math.floor((start+20)/20)*20)+1;
		}else{
			start++;
		}
	}
	
		//alert(' totlay:'+totlay+' start:'+start);
	for (a=start; (a<=totlay); a++){
		nomlay = "layer" + a;
		document.all[nomlay].style.visibility="hidden";
		//	queda pendiente esta consulta --	if (navigator.appName == "Microsoft Internet Explorer") 
			
         }
	}

function buscar(){

/*if (document.formulario1.txtnomina.value != ""){*/
	document.formulario1.action="multi_recep.php";
	//document.formulario1.action="cambio_recep.php";
	document.formulario1.submit();
	
 	/* }*/

}
 function validarentero(formu){ 
      //intento convertir a entero. 
	  var formu;
     //si era un entero no le afecta, si no lo era lo intenta convertir 
     formu.txtnomina.value = parseInt(formu.txtnomina.value);
	 if (formu.txtnomina.value=="") {
	     formu.txtnomina.value =0;
	}	 
	 //Compruebo si es un valor numérico 
      if (isNaN(formu.txtnomina.value)) { 
            //entonces (no es numero) devuelvo el valor cadena vacia 
			formu.txtnomina.value ="";
			alert ("Debe ingresar solamente numeros");
		    return formu.txtnomina.value 
      }else{ 
            //En caso contrario (Si era un número) devuelvo el valor 
            return formu.txtnomina.value
      } 
} 
	
</script>

<script language="JavaScript">
<!--

function revisa_check() 	
  {
	var sicheck = 0;
  
for (var n=0; n < formulario1.elements.length; n++) {
     if (formulario1.elements[n].checked) {
	 
	     sicheck = 1; }
	         	 
}
	 if (sicheck == 0)  {
	           alert("Debe seleccionar un documento");
			   return false; }
			  else 
			   return true; 	
  }
	
function chequea_todos(formu)

  {
    for (var i=0;i<formu.elements.length;i++)
    {
	  	
      var elemento = formu.elements[i]; //(e.name != 'chektodos') && (
      if (elemento.type=='checkbox')
      {
        elemento.checked = formu.chektodos.checked;
        if (formu.chektodos.checked)
        {
		   
          cambia_color(elemento);
        }
        else
        {
          cambia_color(elemento);
        }
      }
    }
	 if (formu.chektodos.checked){
	alert("Se recepcionarán todos los documentos");
	}
  }       	
	
function cambia_color(esto) 
  {
  var est_check=1;
  var ie = document.all?1:0;
  var ns4 = document.layers?1:0;
  
     var estacheck=esto.checked;
     if (ie)
      {
        while (esto.tagName!="TR")
        {
           esto=esto.parentElement;
	    }
      }
     else
      {
        while (esto.tagName!="TR")
        {
       	  esto=esto.parentNode;
        }
      }
     if(estacheck)
	 
       esto.className = "columna1"
      else
       esto.className = "columna2";
       }   
	
	
//-->
</script>
<script>
		var arreglo=parent.<?php echo $nombre_frame; ?>.<?php echo $nombre_array; ?>;
		var idseg=parent.<?php echo $nombre_frame; ?>.<?php echo $nombre_idseg; ?>; 
		var nomina=parent.<?php echo $nombre_frame; ?>.<?php echo $nombre_nomina; ?>; 
		var desc_tipo_doc=parent.<?php echo $nombre_frame; ?>.<?php echo $nombre_desc_tipo_doc; ?>; 
		var num_externo=parent.<?php echo $nombre_frame; ?>.<?php echo $nombre_num_externo; ?>; 
		var fecha_documento=parent.<?php echo $nombre_frame; ?>.<?php echo $nombre_fecha_documento; ?>; 
		var materia=parent.<?php echo $nombre_frame; ?>.<?php echo $nombre_materia; ?>; 
		var num_interno=parent.<?php echo $nombre_frame; ?>.<?php echo $nombre_num_interno; ?>; 
		/* alert('arreglo:'+arreglo[0]);
		alert('idseg:'+idseg.length);
		alert('nomina:'+nomina.length);
		alert('desc_tipo_doc:'+desc_tipo_doc.length);
		alert('num_externo:'+num_externo.length);
		alert('fecha_documento:'+fecha_documento.length);
		alert('materia:'+materia.length);
		alert('num_interno:'+num_interno.length);  */
		var cusuario=parent.<?php echo $nombre_frame; ?>.cusuario;
		var idusuario=parent.<?php echo $nombre_frame; ?>.idusuario;
		var idfuncionario=parent.<?php echo $nombre_frame; ?>.idfuncionario;
		
		var Totreg=arreglo.length;
		if (Totreg==0){
			alert("No Existen Documentos");
		}
		else{
			var NumPag=Math.floor(Totreg/10);
			var NumPag2=Math.floor(Totreg/200);
			var num_pag=<?php echo $num_pag; ?>;
			
			if ((Totreg%10)!=0){
				NumPag++;
			}
			if ((Totreg%200)!=0){
				NumPag2++;
			}
			var inicio=(num_pag*200)-199;
			var calculo=Totreg-(num_pag-1)*200;
			var fin=num_pag*200;
			
			var flag=0;
			var num_layer=NumPag%20;
			var NumLayer = (num_pag-1)*20;
			
			if (calculo<=200){// si es la última página
				fin=Totreg;
				limite=Totreg;
				flag=1;
				num_layer=NumPag;
			}
			else{
				flag=1;
				limite=fin;
				num_layer=(num_pag*20);
			}
			if(num_layer==0){
				num_layer=20;
			}
		
		//alert('inicio:'+inicio+' fin:'+fin+' NumPag:'+NumPag+' NumPag2:'+NumPag2+' calculo:'+calculo+' Totreg:'+Totreg+' num_layer:'+num_layer);
    //document.write('<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">');
document.write('<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">'+
'</head>'+
'<body bgcolor="#FFFFFF" text="#000000" topmargin="0">'+
'<center >'+
    '<table width="650" border="1" cellpadding="2" cellspacing="0" bgcolor="#3399FF">'+
      '<tr> '+
        '<td> <p align="center"><b><font size="4" color="#FFFFFF">DOCUMENTOS</font></b></p></td>'+
      '</tr>'+
    '</table>'+
    '<br>'+
    '<table width="650" border="0">'+
      '<tr>'+
        '<td width="400"><strong>Total de Páginas: '+NumPag+
		'</strong></td>'+
        '<td width="150"><div align="right"><strong>Total Registros: '+
		'</strong></div></td>'+
        '<td width="100" bgcolor="<?php echo $titulo ?>" align="center"><strong>'+Totreg+
		'</strong></td>'+
      '</tr>'+
    '</table>'+
    '<table width="650" border="0">'+
      '<tr> '+
        '<td><div align="left"><b>'); 
		/************************************** antes **************************************************
		for (i = 1; i <= NumPag; i++) {
			numero=(i*10-9)+'-'+(i*10);
			document.write('<td background="botones/boton_n.GIF" style=cursor:hand width=\'44\' height=\'16\' align="center" nowrap onClick="MM_showHideLayers(\'layer'+i+'\',\'\',\'show\','+i+', '+NumPag+
			')"><strong><font color="#FFFFFF" size="1" face="Arial">'+numero+'</font></strong></td> ');
			//document.write('<img src=\'botones/boton'+i+'.gif\' width=\'44\' height=\'16\' onClick="MM_showHideLayers(\'layer'+i+'\',\'\',\'show\','+i+', '+NumPag+')">');
			//alert(numero);
		}*/

			var anterior='<a href="lista_doc2.php?temp=<?php echo $valor ?>&cusuario='+cusuario+'&idusuario='+idusuario+'&idfuncionario='+idfuncionario+
			'&txtagno=<?php echo $txtann ?>&tipo_frame=<?php echo $tipo_frame ?>&num_pag='+(num_pag-1)+'">Anterior</a>';
			var siguiente='<a href="lista_doc2.php?temp=<?php echo $valor ?>&cusuario='+cusuario+'&idusuario='+idusuario+'&idfuncionario='+idfuncionario+
			'&txtagno=<?php echo $txtann ?>&tipo_frame=<?php echo $tipo_frame ?>&num_pag='+(num_pag+1)+'">Siguiente</a>';
			if (num_pag<=1){
				anterior='Anterior';
			}
			if (num_pag==NumPag2){
				siguiente='Siguiente';
			}
			
			document.write('<td align="right">'+anterior+'</td>');
			
		for (i = inicio; i <= fin; i=i+10) {
			var num_foto="";
			numero=(i)+'-'+(i+9);
			if (i>=801){
				num_foto="2";
			}
			document.write('<td background="botones/boton_n'+num_foto+'.GIF" style="cursor:hand; background-position: 50% 0%" width=\'44\' height=\'16\' align="center" nowrap '+
			'onClick="MM_showHideLayers(\'layer'+((i+9)/10)+'\',\'\',\'show\','+((i+9)/10)+', '+num_layer+
			')"><strong><font color="#FFFFFF" size="1" face="Arial">'+numero+'</font></strong></td> ');
			//document.write('<img src=\'botones/boton'+i+'.gif\' width=\'44\' height=\'16\' onClick="MM_showHideLayers(\'layer'+i+'\',\'\',\'show\','+i+', '+NumPag+')">');
			//alert(numero);
		}
			document.write('<td align="left">'+siguiente+'</td>');

		document.write('</b></div>'+
          '<div align="left"></div></td>'+
        '<td>&nbsp;</td>'+
      '</tr>'+
    '</table>'+
    '<p>&nbsp;</p>');
		var Corre = inicio-1;
		var primero=1;
		
		/* for(i = 0; i < Totreg; i++){ // antes                                    */
		for(i = inicio-1; i <= limite-1; i++){
			//alert('Corre:'+Corre+' NumLayer:'+NumLayer);
			if ((Corre%10)==0){
				NumLayer++;
				if (primero==1){
					document.write('<div id="layer' +NumLayer+ '" style="position:absolute; left:10px; top:140px; width:100%; height:130px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: visible">');
				}
				else{
					document.write('<div id="layer' +NumLayer+ '" style="position:absolute; left:10px; top:140px; width:100%; height:130px; z-index:1; background-color: #FFFFFF; layer-background-color:#C3D6E6; border: 1px none #000000; visibility: hidden">');
				}
				
				document.write("<table width='750' border=1 cellpadding='1' cellspacing='0' bgcolor='#E6EEFF'>"); 
				document.write('<tr bgcolor="#6699FF"> ');
				document.write('<td width="30" align="center"><strong><font color="#FFFFFF" size="2">Num</font></strong></td>');
				document.write('<td width="95" align="center"><strong><font color="#FFFFFF" size="2">N° Documento</font></strong></td>');
				document.write('<td width="100" align="center"><strong><font color="#FFFFFF" size="2">Tipo Documento</font></strong></td>');
				document.write('<td width="50" align="center"><strong><font color="#FFFFFF" size="2">Nomina</font></strong></td>');
				document.write('<td width="65" align="center"><strong><font color="#FFFFFF" size="2">N° Interno</font></strong></td>');
				document.write('<td width="80" align="center"><strong><font color="#FFFFFF" size="2">N° Externo</font></strong></td>');
				document.write('<td width="80" align="center"><strong><font color="#FFFFFF" size="2">Fecha Documento</font></strong></td>');
				document.write('<td width="250" align="center"><strong><font color="#FFFFFF" size="2">Materia</font></strong></td>');
				document.write('</tr>');
			}
			Corre++;
			primero++;
			document.write('<tr>');
			document.write('<td align="right" valign="middle"><font size="2">'+Corre+'</font></td>');
			document.write("<td align=\"center\"><a href=\"tramites_deriva.php?cusuario=<?php echo $cusuario; ?>&idusuario=<?php echo $idusuario; 
			?>&iddocum="+arreglo[i]+"&idseguim="+idseg[i]+"&idfuncionario=<?php echo $idfuncionario; ?>&txtnomina="+nomina[i]+"&txtagno=<?php echo $txtagno; 
			?>\">"+arreglo[i]+"</a></td> \n");
			document.write("<td align=\"left\">"+desc_tipo_doc[i]+"</td>\n");
			document.write("<td align=\"right\">"+nomina[i]+"</td>\n");
			document.write("<td align=\"right\">"+num_interno[i]+"</td>\n");
			document.write("<td align=\"right\">"+num_externo[i]+"</td>\n");
			document.write("<td align=\"right\">"+fecha_documento[i]+"</td>\n");
			document.write("<td align=\"left\">"+materia[i]+"</td>\n");
			document.write('</tr>');
			if ((Corre%10)==0){
				document.write('</table>');
				document.write('</div>');
			}
			//alert(i);
		}
		document.write('</table>');
		document.write('</div>');
	}
		if (arreglo.length!=""){
			//alert('sip!!!');
			parent.frames["<?php echo $nombre_frame; ?>"].location.reload();
		}
</script>
 <p>&nbsp; </p>
</center>
</body>
</html>
